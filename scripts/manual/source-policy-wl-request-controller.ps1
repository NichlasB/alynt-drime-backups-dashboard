# Exact local-only owned request proof. This script never creates/drops fixture storage.
param([switch]$ConfirmedPluginTesterOwnedGenuineRequest,[string]$Prefix,[string]$ExpectedSha,[string]$ExpectedImage,[switch]$AuditOnly)
$ErrorActionPreference='Stop'
if(-not $ConfirmedPluginTesterOwnedGenuineRequest -or -not $IsWindows -or $args.Count -ne 0 -or $Prefix -cnotmatch '\Aadbm_[a-f0-9]{12}_\z' -or $ExpectedSha -cnotmatch '\A[a-f0-9]{40}\z' -or (-not $AuditOnly -and ($ExpectedImage -cnotmatch '\A[a-f0-9]{64}\z' -or -not $env:MYSQL_PWD))){throw 'Exact confirmed request target/source/fixture scope required'}
$root=[IO.Path]::GetFullPath("$PSScriptRoot/../..");$head=git -C $root rev-parse HEAD
if($LASTEXITCODE -ne 0 -or $head -cne $ExpectedSha){throw 'Exact published cohort required'}
$dirty=git -C $root status --porcelain=v1
if($LASTEXITCODE -ne 0 -or $dirty){throw 'Clean source cohort required'}
$sites=Get-Content -Raw 'C:/Users/Captain/AppData/Roaming/Local/sites.json'|ConvertFrom-Json
$site=@($sites.PSObject.Properties.Value|Where-Object{$_.domain -ceq 'plugin-tester.local'})
if($site.Count -ne 1 -or $site[0].name -cne 'Plugin Tester' -or [IO.Path]::GetFullPath([string]$site[0].path).TrimEnd('\','/') -ine 'C:\Users\Captain\Local Sites\plugin-tester' -or $site[0].services.mariadb.version -cne '10.6.23' -or $site[0].services.php.version -cne '8.5.12' -or $site[0].services.mariadb.ports.MYSQL[0] -ne 10004){throw 'Exact confirmed LocalWP metadata refused'}
$script:AdbcScope=$true
. "$PSScriptRoot/source-policy-cleanup-process.ps1"
$php='C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/php.exe'
$mysql='C:/Users/Captain/AppData/Roaming/Local/lightning-services/mariadb-10.6.23+0/bin/win32/bin/mysql.exe'
$argsBase=@('-n','-d','default_socket_timeout=3','-d','memory_limit=64M','-d','mysqli.allow_persistent=0','-d','mysqlnd.net_read_timeout=3','-d','extension_dir=C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/ext','-d','extension=mysqli',"$root/scripts/ci/source-policy-wl-request-child.php",'--owned-wl-request-child')
$run=$Prefix.Substring(5,12)+[guid]::NewGuid().ToString('N').Substring(0,20)
$values=[ordered]@{WL_REQUEST_PROFILE='w';WL_REQUEST_APPROVED='true';WL_REQUEST_TARGET='plugin-tester-local-only';WL_REQUEST_HEAD=$ExpectedSha;WL_REQUEST_RUN=$run;WL_REQUEST_PREFIX=$Prefix;WL_REQUEST_IMAGE=('0'*64);WL_REQUEST_MODULES=('0'*64);WL_REQUEST_MODE='audit'}
$saved=@{};$child=$null;$originalFailure=$null

function Read-AdbwlRecord([string]$Wire,[string]$Keys){
 if($Wire -cnotmatch '\A\{[^\r\n]+\}\n\z' -or $Wire.Length -gt 4096){throw 'Bounded private request record required'}
 $parsed=ConvertFrom-Json -Depth 8 $Wire;$row=[ordered]@{}
 foreach($property in $parsed.PSObject.Properties){$row.Add($property.Name,$property.Value)}
 if(($row.Keys -join ',') -cne $Keys -or (ConvertTo-Json -Compress -Depth 8 $row) -cne $Wire.TrimEnd("`n")){throw 'Exact ordered canonical request record required'}
 return $row
}
function Test-AdbwlNumber($Value,[int]$Max){return ($Value -is [int] -or $Value -is [long]) -and $Value -ge 0 -and $Value -le $Max}
function Assert-AdbwlAudit($Audit,[string]$Run){
 foreach($key in @('event','profile','run','image','modules')){if($Audit[$key] -isnot [string]){throw 'Exact audit string DATA required'}}
 if($Audit.event -cne 'audit' -or $Audit.profile -cne 'w' -or $Audit.run -cne $Run -or $Audit.image -cnotmatch '\A[a-f0-9]{64}\z' -or $Audit.modules -cnotmatch '\A[a-f0-9]{64}\z' -or -not (Test-AdbwlNumber $Audit.checks 99999) -or $Audit.checks -eq 0 -or -not (Test-AdbwlNumber $Audit.native_connections 0) -or -not (Test-AdbwlNumber $Audit.sql_queries 0)){throw 'Exact connection-free request audit refused'}
}
function Assert-AdbwlCase($Row,[int]$Index,[string]$Run,[string]$Image,[int]$PidValue){
 foreach($key in @('event','profile','run','image')){if($Row[$key] -isnot [string]){throw 'Exact request string DATA required'}}
 if($Row.event -cne 'request-closed' -or $Row.profile -cne 'w' -or $Row.run -cne $Run -or $Row.image -cne $Image -or -not (Test-AdbwlNumber $Row.pid ([int]::MaxValue)) -or $Row.pid -eq 0 -or $Row.pid -ne $PidValue -or -not (Test-AdbwlNumber $Row.index 22) -or $Row.index -ne $Index -or $Row.closed -isnot [bool] -or -not $Row.closed){throw 'Exact held request identity refused'}
 foreach($key in @('checks','setup','auth','records','connects','commands')){if(-not (Test-AdbwlNumber $Row[$key] 99999)){throw 'Exact request count DATA required'}}
 if($Row.checks -eq 0 -or $Row.setup -gt 256 -or $Row.auth -gt 256 -or $Row.records -gt 2 -or $Row.connects -gt 2 -or $Row.commands -gt 9999){throw 'Request budget refused'}
 if($Index -lt 4){$n=if($Index -eq 0){1}else{2};if($Row.connects -ne $n -or $Row.records -ne $n -or $Row.commands -le $Row.records){throw 'Non-vacuous positive availability required'}}
 else{$n=if($Index -ge 21){1}else{0};if($Row.connects -ne 0 -or $Row.records -ne $n -or $Row.commands -ne $n){throw 'Denial effect ordering refused'}}
}
function Assert-AdbwlClear($Clock,[int]$Limit){
 $observer=New-AdbcProcess $mysql @('--no-defaults','-h','127.0.0.1','-P','10004','-u','root','-D','local','--connect-timeout=2','-N','-B','-r','-e','SELECT (SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);')
 try{$line=Read-AdbcLine $observer $Clock $Limit;Complete-AdbcProcess $observer $Clock $Limit;if($line.Replace("`r`n","`n") -cne "0`t0`n"){throw 'Independent all-server release refused'}}finally{Stop-AdbcProcess $observer}
}
try{
 foreach($key in $values.Keys){$saved[$key]=[Environment]::GetEnvironmentVariable($key,'Process');[Environment]::SetEnvironmentVariable($key,$values[$key],'Process')}
 $clock=[Diagnostics.Stopwatch]::StartNew()
 $child=New-AdbcProcess $php ($argsBase+@('0'))
 $audit=Read-AdbwlRecord (Read-AdbcLine $child $clock 20000) 'event,profile,run,image,modules,checks,native_connections,sql_queries'
 Complete-AdbcProcess $child $clock 20000;Stop-AdbcProcess $child;$child=$null
 Assert-AdbwlAudit $audit $run
 if($AuditOnly){Write-Output ($audit|ConvertTo-Json -Compress);return}
 if($audit.image -cne $ExpectedImage){throw 'Previously audited cohort changed before proof'}
 # Old fixed adapter/ambient body is a fresh prerequisite, never relabeled as auth.
 & "$PSScriptRoot/source-policy-ambient-proof-controller.ps1" -ConfirmedPluginTesterOwnedAmbient -Prefix ('adbb_'+$Prefix.Substring(5,12)+'_')
 if(-not $?){throw 'Fresh unchanged ambient prerequisite failed'}
 $env:WL_REQUEST_IMAGE=$audit.image;$env:WL_REQUEST_MODULES=$audit.modules;$env:WL_REQUEST_MODE='proof'
 $clock.Restart();Assert-AdbwlClear $clock 20000
 $checks=0
 for($index=0;$index -lt 23;$index++){
  [void](Get-AdbcRemaining $clock 20000)
  $child=New-AdbcProcess $php ($argsBase+@([string]$index))
  try{
   $row=Read-AdbwlRecord (Read-AdbcLine $child $clock 20000) 'event,profile,run,image,pid,index,checks,setup,auth,records,connects,commands,closed'
   if($child.Process.HasExited){throw 'Live owned request child required'}
   Assert-AdbwlCase $row $index $run $audit.image $child.Id
   Assert-AdbwlClear $clock 20000
   if($child.Process.HasExited){throw 'Request child exited during held-live check'}
   Write-AdbcLine $child ('ACK:'+$run+':'+$audit.image+':'+$index) $clock 20000
   Complete-AdbcProcess $child $clock 20000
   Assert-AdbwlClear $clock 20000
   $checks+=$row.checks;if($checks -gt 99999){throw 'Aggregate count bound refused'}
  }finally{Stop-AdbcProcess $child;$child=$null}
 }
 [void](Get-AdbcRemaining $clock 20000)
 Write-Output ([ordered]@{event='request-batch-closed';profile='w';run=$run;image=$audit.image;cases=23;checks=$checks;groups=9;independent_live='zero';independent_post_exit='zero';production_ready=$false;fixture_cleanup='external-owned-scope-required'}|ConvertTo-Json -Compress)
}catch{$originalFailure=$_;throw [InvalidOperationException]::new('Owned W request qualification refused; no automatic retry')}
finally{
 $cleanupFailed=$false
 try{Stop-AdbcProcess $child}catch{$cleanupFailed=$true}
 foreach($key in $saved.Keys){[Environment]::SetEnvironmentVariable($key,$saved[$key],'Process')}
 if($cleanupFailed){if($null -ne $originalFailure){Write-Output 'Owned process cleanup unconfirmed; original failure preserved'}else{throw 'Owned process cleanup unconfirmed'}}
}
