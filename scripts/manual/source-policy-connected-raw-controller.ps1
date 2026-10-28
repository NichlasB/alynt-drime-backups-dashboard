param([switch]$ConfirmedPluginTesterRawConnected)
$ErrorActionPreference='Stop'
if (-not $ConfirmedPluginTesterRawConnected -or $args.Count -ne 0 -or -not $IsWindows -or -not $env:MYSQL_PWD) { throw 'Exact confirmed private raw connected scope required' }
$script:AdbcScope=$true
. "$PSScriptRoot/source-policy-cleanup-process.ps1"
$php='C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/php.exe'
$mysql='C:/Users/Captain/AppData/Roaming/Local/lightning-services/mariadb-10.6.23+0/bin/win32/bin/mysql.exe'
$extension='extension_dir=C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/ext'
$run=[guid]::NewGuid().ToString('N')
$nativeArgs=@('-n','-d',$extension,'-d','extension=mysqli',"$PSScriptRoot/source-policy-connected-raw-worker.php",'--confirmed-plugin-tester-raw-connected',$run)
$children=[Collections.Generic.List[object]]::new()
$stage='metadata'
function Get-AdcrObservation($Clock,[int]$Limit) {
    # All server sessions, including DB=NULL/foreign databases. Never inspect their SQL/user/host.
    $observer=New-AdbcProcess $mysql @('--no-defaults','-h','127.0.0.1','-P','10004','-u','root','-D','local','--connect-timeout=2','-N','-B','-r','-e','SELECT (SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);')
    try { $line=Read-AdbcLine $observer $Clock $Limit;Complete-AdbcProcess $observer $Clock $Limit;return $line.Replace("`r`n","`n") } finally { Stop-AdbcProcess $observer }
}
function Send-AdcrData([string]$Command,$Clock) {
    Write-AdbcLine $script:rawBridge $Command $Clock 25000
    return (Read-AdbcLine $script:rawBridge $Clock 25000)
}
try {
    $sites=Get-Content -Raw 'C:/Users/Captain/AppData/Roaming/Local/sites.json' | ConvertFrom-Json
    $site=@($sites.PSObject.Properties.Value | Where-Object { $_.domain -ceq 'plugin-tester.local' })
    if ($site.Count -ne 1 -or $site[0].name -cne 'Plugin Tester' -or [IO.Path]::GetFullPath([string]$site[0].path).TrimEnd('\','/') -ine 'C:\Users\Captain\Local Sites\plugin-tester' -or $site[0].services.mariadb.version -cne '10.6.23' -or $site[0].services.php.version -cne '8.5.12' -or $site[0].services.mariadb.ports.MYSQL[0] -ne 10004) { throw 'Fixed metadata refused' }
    $clock=[Diagnostics.Stopwatch]::StartNew()
    if ((Get-AdcrObservation $clock 10000) -cne "0`t0`n") { throw 'Baseline refused' }
    $stage='manifest'
    $manifest=New-AdbcProcess $php ($nativeArgs+@(('0'*64),'manifest'));$children.Add($manifest)
    $source=(Read-AdbcLine $manifest $clock 10000).TrimEnd("`n");Complete-AdbcProcess $manifest $clock 10000
    if ($source -cnotmatch '\A[a-f0-9]{64}\z') { throw 'Manifest refused' }
    $clock.Restart() # One original25s budget, child/model launch and every hold/observer/exit included.
    $stage='connected-frame'
    $child=New-AdbcProcess $php ($nativeArgs+@($source,'proof'));$children.Add($child)
    $script:rawBridge=New-AdbcProcess $php @('-n',"$PSScriptRoot/source-policy-connected-raw-bridge.php",'--private-raw-connected-data',$run,$source,[string]$child.Id);$children.Add($script:rawBridge)
    $connected=Read-AdbcLine $child $clock 25000
    if ((Send-AdcrData ('feed|'+$clock.ElapsedMilliseconds+'|'+$connected.TrimEnd("`n")) $clock) -cne "OK|connected_hold`n") { throw 'Connected DATA refused' }
    $stage='connected-live'
    if ($child.Process.HasExited -or (Get-AdcrObservation $clock 25000) -cne "1`t0`n" -or $child.Process.HasExited) { throw 'Connected live observation refused' }
    $close=Send-AdcrData ('live|'+$clock.ElapsedMilliseconds+'|'+$child.Id+',1,1,0') $clock
    if ($close -cne ('ADCR-CLOSE|'+$run+'|'+$source+'|'+$child.Id+"`n")) { throw 'Close token refused' }
    Write-AdbcLine $child $close.TrimEnd("`n") $clock 25000
    $stage='closed-frame'
    $closed=Read-AdbcLine $child $clock 25000
    if ((Send-AdcrData ('feed|'+$clock.ElapsedMilliseconds+'|'+$closed.TrimEnd("`n")) $clock) -cne "OK|closed_hold`n") { throw 'Closed DATA refused' }
    $stage='closed-live'
    if ($child.Process.HasExited -or (Get-AdcrObservation $clock 25000) -cne "0`t0`n" -or $child.Process.HasExited) { throw 'Closed live observation refused' }
    $exit=Send-AdcrData ('live|'+$clock.ElapsedMilliseconds+'|'+$child.Id+',1,0,0') $clock
    if ($exit -cne ('ADCR-EXIT|'+$run+'|'+$source+'|'+$child.Id+"`n")) { throw 'Exit token refused' }
    Write-AdbcLine $child $exit.TrimEnd("`n") $clock 25000
    Complete-AdbcProcess $child $clock 25000
    $stage='post-exit'
    if ((Get-AdcrObservation $clock 25000) -cne "0`t0`n") { throw 'Post-exit observation refused' }
    if ((Send-AdcrData ('finish|'+$clock.ElapsedMilliseconds+'|0,'+$child.Id+',0,0,0') $clock) -cne "OK|done`n") { throw 'Final DATA refused' }
    Complete-AdbcProcess $script:rawBridge $clock 25000
    Write-Output ('RAW_DIAGNOSTIC_COMPLETE elapsed_ms:'+$clock.ElapsedMilliseconds+' source:'+$source+' native_qualification:not-run')
    Write-Output $connected.TrimEnd("`n")
    Write-Output $closed.TrimEnd("`n")
} catch {
    # Never copy collaborator/SQL/native exception messages into diagnostic records.
    Write-Output ('RAW_DIAGNOSTIC_UNCONFIRMED stage:'+$stage+' native_qualification:not-run')
    throw [InvalidOperationException]::new('Bounded raw diagnostic stopped; no retry authorized')
} finally {
    $cleanupFailed=$false
    foreach ($owned in $children) { try {Stop-AdbcProcess $owned} catch {$cleanupFailed=$true;Write-Output ('OWNED_PROCESS_UNCONFIRMED id:'+$owned.Id)} }
    if ($cleanupFailed) { throw 'Exact owned process cleanup unconfirmed' }
}
