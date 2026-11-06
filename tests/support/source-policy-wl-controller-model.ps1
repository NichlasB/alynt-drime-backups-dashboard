# Isolated protocol DATA tests only. Parse/extract pure definitions; never run a controller.
$ErrorActionPreference='Stop';$checks=0
function Assert-Model($Condition,[string]$Label){$script:checks++;if(-not $Condition){throw ('Model assertion: '+$Label)}}
$root=[IO.Path]::GetFullPath("$PSScriptRoot/../..");$tokens=$null;$errors=$null
$ast=[System.Management.Automation.Language.Parser]::ParseFile("$root/scripts/manual/source-policy-wl-request-controller.ps1",[ref]$tokens,[ref]$errors)
Assert-Model (-not $errors) 'controller parses'
foreach($name in @('Read-AdbwlRecord','Test-AdbwlNumber','Assert-AdbwlAudit','Assert-AdbwlCase')){
 $definitions=@($ast.FindAll({param($n)$n -is [System.Management.Automation.Language.FunctionDefinitionAst] -and $n.Name -ceq $name},$true))
 Assert-Model ($definitions.Count -eq 1) 'one pure definition'
 . ([scriptblock]::Create($definitions[0].Extent.Text))
}
$run='a'*32;$image='b'*64
$audit=[ordered]@{event='audit';profile='w';run=$run;image=$image;modules=('c'*64);checks=1;native_connections=0;sql_queries=0}
Assert-AdbwlAudit $audit $run;$checks++
foreach($key in $audit.Keys){$bad=[ordered]@{};foreach($copy in $audit.Keys){$bad[$copy]=$audit[$copy]};$bad[$key]=@($audit[$key]);$failed=$false;try{Assert-AdbwlAudit $bad $run}catch{$failed=$true};Assert-Model $failed ('compound audit '+$key)}
for($index=0;$index -lt 23;$index++){
 $n=if($index -lt 4){if($index -eq 0){1}else{2}}elseif($index -ge 21){1}else{0}
 $row=[ordered]@{event='request-closed';profile='w';run=$run;image=$image;pid=123;index=$index;checks=10;setup=1;auth=1;records=$n;connects=$(if($index -lt 4){$n}else{0});commands=$(if($index -lt 4){20}else{$n});closed=$true}
 $keys=$row.Keys -join ',';$wire=(ConvertTo-Json -Compress $row)+"`n"
 $parsed=Read-AdbwlRecord $wire $keys;Assert-AdbwlCase $parsed $index $run $image 123;$checks++
 foreach($key in $row.Keys){
  $bad=[ordered]@{};foreach($copy in $row.Keys){$bad[$copy]=$row[$copy]};$bad[$key]=@($row[$key])
  $failed=$false;try{Assert-AdbwlCase $bad $index $run $image 123}catch{$failed=$true};Assert-Model $failed ('compound '+$key)
 }
 if($index -lt 4){$bad=[ordered]@{};foreach($key in $row.Keys){$bad[$key]=$row[$key]};$bad.connects=0;$failed=$false;try{Assert-AdbwlCase $bad $index $run $image 123}catch{$failed=$true};Assert-Model $failed 'all-unavailable cannot pass'}
 foreach($badWire in @($wire.TrimEnd("`n")+',secret'+"`n",$wire.Replace('"profile":"w"','"profile":"w","profile":"w"'),$wire.Replace('"event":','"extra":0,"event":'))){
  $failed=$false;try{Read-AdbwlRecord $badWire $keys|Out-Null}catch{$failed=$true};Assert-Model $failed 'noncanonical/duplicate/extra wire rejected'
 }
}
foreach($bad in @($null,$false,$true,'1',1.5,-1,100000,@(1),@{value=1})){Assert-Model (-not (Test-AdbwlNumber $bad 99999)) 'typed integer bounds'}
$definition=@($ast.FindAll({param($n)$n -is [System.Management.Automation.Language.FunctionDefinitionAst] -and $n.Name -ceq 'Assert-AdbwlCase'},$true))[0].Extent.Text
$controls=@(
 @('$Row.profile -cne ''w''','$false','profile','l'),
 @('$Row.run -cne $Run','$false','run',('f'*32)),
 @('$Row.image -cne $Image','$false','image',('f'*64)),
 @('$Row.pid -ne $PidValue','$false','pid',124),
 @('$Row.closed -isnot [bool] -or -not $Row.closed','$false','closed',$false),
 @('$Row.connects -ne $n','$false','connects',0)
)
foreach($control in $controls){
 Assert-Model ($definition.Contains($control[0])) 'source mutation target exists'
 try{
  . ([scriptblock]::Create($definition.Replace($control[0],$control[1])))
  $row=[ordered]@{event='request-closed';profile='w';run=$run;image=$image;pid=123;index=0;checks=10;setup=1;auth=1;records=1;connects=1;commands=20;closed=$true};$row[$control[2]]=$control[3]
  $refused=$false;try{Assert-AdbwlCase $row 0 $run $image 123}catch{$refused=$true}
  $killed=$false;try{Assert-Model $refused 'required negative assertion'}catch{if($_.Exception.Message -notlike 'Model assertion:*'){throw};$killed=$true}
  Assert-Model $killed 'semantic control is assertion-killed, not a parser/launcher failure'
 }finally{. ([scriptblock]::Create($definition))}
}
$script:AdbcScope=$true
. "$root/scripts/manual/source-policy-cleanup-process.ps1"
$wire='ADBWLRF1|w|child|22|auth|RuntimeException|'+('c'*64)+"`n"
Assert-Model ((Get-AdbcErrorFact $wire) -ceq ('request_profile:w case:22 stage:auth class:RuntimeException assertion:'+('c'*64))) 'safe diagnostic facts'
Assert-Model ((Get-AdbcErrorFact ($wire+'secret')) -ceq '') 'trailing raw data rejected'
Write-Output ('checks:'+$checks+' isolated_protocol_only:true')
