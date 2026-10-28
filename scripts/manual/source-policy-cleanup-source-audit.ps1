param([switch]$RepositoryOnly)
$ErrorActionPreference='Stop'
if (-not $RepositoryOnly -or $args.Count -ne 0) { throw 'Repository-only source audit scope required' }
$errors=$null
$ast=[System.Management.Automation.Language.Parser]::ParseFile("$PSScriptRoot/source-policy-cleanup-controller.ps1",[ref]$null,[ref]$errors)
if ($errors.Count -ne 0) { throw 'Controller syntax failed' }
$definitions=@($ast.FindAll({param($node) $node -is [System.Management.Automation.Language.FunctionDefinitionAst] -and $node.Name -ceq 'Get-AdbcFixtureAuditSql'},$true))
if ($definitions.Count -ne 1) { throw 'One exact pure fixture projection required' }
# Execute only this reviewed pure function definition, never the controller body/site operations.
. ([scriptblock]::Create($definitions[0].Extent.Text))
$checks=0
function Assert-AdbcSource([bool]$Condition) { $script:checks++;if (-not $Condition) { throw ('Source assertion:'+$script:checks) } }
function Test-AdbcSqlShape([string]$Text) {
    $unquoted=[regex]::Replace($Text,"'(?:[^']|'')*'",'')
    $balance=0
    foreach ($character in $unquoted.ToCharArray()) { if ($character -eq '(') { $balance++ };if ($character -eq ')') { $balance-- };if ($balance -lt 0) { return $false } }
    return $balance -eq 0
}
$prefix='adbb_0123456789ab_'
$sql=Get-AdbcFixtureAuditSql $prefix
Assert-AdbcSource (Test-AdbcSqlShape $sql)
Assert-AdbcSource (-not (Test-AdbcSqlShape ($sql+')')))
$expected="SELECT (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='local' AND TABLE_NAME='adbb_0123456789ab_options'),(SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='local' AND TABLE_NAME='adbb_0123456789ab_missing_options'),(SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='adbb_0123456789ab_absent'),(SELECT COUNT(*) FROM information_schema.USER_PRIVILEGES WHERE PRIVILEGE_TYPE='PROCESS' AND GRANTEE=CONCAT(QUOTE(SUBSTRING_INDEX(CURRENT_USER(),'@',1)),'@',QUOTE(SUBSTRING_INDEX(CURRENT_USER(),'@',-1))));"
Assert-AdbcSource ($sql -ceq $expected)
foreach ($bad in @('wp_','adbb_0123456789AB_',"adbb_0123456789ab_';DROP","adbb_0123456789ab_`n")) { $refused=$false;try { [void](Get-AdbcFixtureAuditSql $bad) } catch { $refused=$_.Exception.Message -ceq 'Exact owned fixture prefix required' };Assert-AdbcSource $refused }
$script:AdbcScope=$true
. "$PSScriptRoot/source-policy-cleanup-process.ps1" # Definitions only; no processes, networking or site reads.
Assert-AdbcSource ((Get-AdbcErrorFact 'ERROR 1064 (42000) private-secret SQL') -ceq 'mysql_code:1064 sqlstate:42000')
Assert-AdbcSource ((Get-AdbcErrorFact 'native-cleanup failure stage:native-case class:RuntimeException checks:123 assertion:private-secret') -ceq 'worker_stage:native-case worker_class:RuntimeException checks:123')
Assert-AdbcSource ((Get-AdbcErrorFact 'native-cleanup failure stage:private-secret class:RuntimeException checks:123') -ceq '')
Assert-AdbcSource ((Get-AdbcErrorFact 'native-cleanup failure stage:native-case class:private-secret checks:123') -ceq '')
foreach ($text in @('ERROR 10','private ERROR 1064 (42000)','ERROR 000000 (42000)','ERROR 1064 (private)',('ERROR 1064 (42000)'+('x'*256)))) { Assert-AdbcSource ((Get-AdbcErrorFact $text) -ceq '') }
$child=[pscustomobject]@{ErrPrefix='private-secret SQL';ErrBytes=14;ErrorFact='mysql_code:1064 sqlstate:42000'}
$refused=$false
try { Deny-AdbcError $child } catch { $refused=$_.Exception.Message -ceq 'Private stderr rejected (14 bytes) mysql_code:1064 sqlstate:42000' }
Assert-AdbcSource $refused
Assert-AdbcSource ($child.ErrPrefix -ceq '')
Write-Output ('source-audit checks:'+$checks+' SQL_executed:0 site_reads:0 native_connections:0')
