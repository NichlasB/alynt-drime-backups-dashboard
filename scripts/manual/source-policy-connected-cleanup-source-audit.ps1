param([switch]$RepositoryOnly)
$ErrorActionPreference='Stop'
if (-not $RepositoryOnly -or $args.Count -ne 0) { throw 'Repository-only source scope required' }
$checks=0
function Assert-Adbc2Source([bool]$Condition) { $script:checks++;if (-not $Condition) { throw ('Connected source assertion:'+$script:checks) } }
$errors=$null
$ast=[Management.Automation.Language.Parser]::ParseFile("$PSScriptRoot/source-policy-connected-cleanup-controller.ps1",[ref]$null,[ref]$errors)
Assert-Adbc2Source ($errors.Count -eq 0)
$defs=@($ast.FindAll({param($node) $node -is [Management.Automation.Language.FunctionDefinitionAst] -and $node.Name -ceq 'Get-Adbc2FixtureSql'},$true))
Assert-Adbc2Source ($defs.Count -eq 1)
. ([scriptblock]::Create($defs[0].Extent.Text)) # Only this reviewed pure SQL projection; never the controller/site body.
$sql=Get-Adbc2FixtureSql 'adbb_0123456789ab_'
$expected="SELECT (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='local' AND TABLE_NAME='adbb_0123456789ab_options'),(SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='local' AND TABLE_NAME='adbb_0123456789ab_missing_options'),(SELECT COUNT(*) FROM information_schema.USER_PRIVILEGES WHERE PRIVILEGE_TYPE='PROCESS' AND GRANTEE=CONCAT(QUOTE(SUBSTRING_INDEX(CURRENT_USER(),'@',1)),'@',QUOTE(SUBSTRING_INDEX(CURRENT_USER(),'@',-1))));"
Assert-Adbc2Source ($sql -ceq $expected)
foreach ($bad in @('wp_','adbb_0123456789AB_',"adbb_0123456789ab_';DROP","adbb_0123456789ab_`n")) { $caught=$false;try {[void](Get-Adbc2FixtureSql $bad)}catch{$caught=$true};Assert-Adbc2Source $caught }
$controller=Get-Content -Raw "$PSScriptRoot/source-policy-connected-cleanup-controller.ps1"
Assert-Adbc2Source ($controller.Contains("foreach (`$caseName in @('missing-table','positive'))"))
Assert-Adbc2Source ($controller.Contains('WHERE ID<>CONNECTION_ID()'))
Assert-Adbc2Source (-not $controller.Contains('WHERE DB='))
Assert-Adbc2Source ($controller.Contains('ADBC2-ACK|'))
Assert-Adbc2Source ($controller.Contains('native_qualification:not-established wiring:disabled'))
$operation=Get-Content -Raw "$PSScriptRoot/source-policy-connected-cleanup-operation.php"
Assert-Adbc2Source (-not $operation.Contains('adbc_owner('))
Assert-Adbc2Source ($operation.Contains("'actual connected positive or refusal outcome'"))
Assert-Adbc2Source ($operation.Contains('->flush()'))
$worker=Get-Content -Raw "$PSScriptRoot/source-policy-connected-cleanup-worker.php"
foreach ($file in @('source-policy-connected-cleanup-controller.ps1','source-policy-connected-cleanup-bridge.php','source-policy-connected-cleanup-cohort.php','source-policy-cleanup-process.ps1')) { Assert-Adbc2Source ($worker.Contains($file)) }
Assert-Adbc2Source ($worker.Contains('initial frozen connected image') -and $worker.Contains('unchanged image after owned release'))
Write-Output ('connected-source-audit checks:'+$checks+' SQL_executed:0 site_reads:0 native_connections:0')
