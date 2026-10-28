param([switch]$ConfirmedPluginTesterConnectedCleanup,[string]$Prefix)
$ErrorActionPreference='Stop'
if (-not $ConfirmedPluginTesterConnectedCleanup -or $Prefix -cnotmatch '\Aadbb_[a-f0-9]{12}_\z' -or $args.Count -ne 0 -or -not $IsWindows -or -not $env:MYSQL_PWD) { throw 'Exact confirmed connected-owner scope required' }
$script:AdbcScope=$true
. "$PSScriptRoot/source-policy-cleanup-process.ps1"
$php='C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/php.exe'
$mysql='C:/Users/Captain/AppData/Roaming/Local/lightning-services/mariadb-10.6.23+0/bin/win32/bin/mysql.exe'
$extension='extension_dir=C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/ext'
$run=[guid]::NewGuid().ToString('N')
$nativeArgs=@('-n','-d',$extension,'-d','extension=mysqli',"$PSScriptRoot/source-policy-connected-cleanup-worker.php",'--confirmed-plugin-tester-connected-cleanup',$Prefix,$run)
$children=[Collections.Generic.List[object]]::new()
$stage='metadata';$completed=0;$totalOut=0

function Invoke-Adbc2Sql([string]$Sql,$Clock,[int]$Limit) {
    $observer=New-AdbcProcess $mysql @('--no-defaults','-h','127.0.0.1','-P','10004','-u','root','-D','local','--connect-timeout=2','-N','-B','-r','-e',$Sql)
    try { $line=Read-AdbcLine $observer $Clock $Limit;Complete-AdbcProcess $observer $Clock $Limit;return $line.Replace("`r`n","`n") } finally { Stop-AdbcProcess $observer }
}
function Assert-Adbc2Clear($Clock,[int]$Limit) {
    # All sessions, including DB=NULL/foreign databases; exclude only this observer.
    if ((Invoke-Adbc2Sql 'SELECT (SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);' $Clock $Limit) -cne "0`t0`n") { throw 'All-server reconciliation refused' }
}
function Get-Adbc2FixtureSql([string]$OwnedPrefix) {
    if ($OwnedPrefix -cnotmatch '\Aadbb_[a-f0-9]{12}_\z') { throw 'Exact empty fixture prefix required' }
    return "SELECT (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='local' AND TABLE_NAME='$($OwnedPrefix)options'),(SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='local' AND TABLE_NAME='$($OwnedPrefix)missing_options'),(SELECT COUNT(*) FROM information_schema.USER_PRIVILEGES WHERE PRIVILEGE_TYPE='PROCESS' AND GRANTEE=CONCAT(QUOTE(SUBSTRING_INDEX(CURRENT_USER(),'@',1)),'@',QUOTE(SUBSTRING_INDEX(CURRENT_USER(),'@',-1))));"
}
function Send-Adbc2Data([string]$Command,$Clock) {
    Write-AdbcLine $script:connectedBridge $Command $Clock 25000
    return (Read-AdbcLine $script:connectedBridge $Clock 25000)
}
try {
    $sites=Get-Content -Raw 'C:/Users/Captain/AppData/Roaming/Local/sites.json' | ConvertFrom-Json
    $site=@($sites.PSObject.Properties.Value | Where-Object { $_.domain -ceq 'plugin-tester.local' })
    if ($site.Count -ne 1 -or $site[0].name -cne 'Plugin Tester' -or [IO.Path]::GetFullPath([string]$site[0].path).TrimEnd('\','/') -ine 'C:\Users\Captain\Local Sites\plugin-tester' -or $site[0].services.mariadb.version -cne '10.6.23' -or $site[0].services.php.version -cne '8.5.12' -or $site[0].services.mariadb.ports.MYSQL[0] -ne 10004) { throw 'Frozen local metadata refused' }
    $clock=[Diagnostics.Stopwatch]::StartNew()
    if ((Invoke-Adbc2Sql (Get-Adbc2FixtureSql $Prefix) $clock 10000) -cne "1`t0`t1`n" -or (Invoke-Adbc2Sql "SELECT COUNT(*) FROM $($Prefix)options;" $clock 10000) -cne "0`n") { throw 'Empty owned schema/observer privilege refused' }
    Assert-Adbc2Clear $clock 10000
    $stage='manifest'
    $manifest=New-AdbcProcess $php ($nativeArgs+@(('0'*64),'manifest'));$children.Add($manifest)
    $source=(Read-AdbcLine $manifest $clock 10000).TrimEnd("`n");Complete-AdbcProcess $manifest $clock 10000
    if ($source -cnotmatch '\A[a-f0-9]{64}\z') { throw 'Frozen connected image refused' }
    $clock.Restart() # One original25s deadline for both cases and every launch/hold/observer/exit.
    $script:connectedBridge=New-AdbcProcess $php @('-n',"$PSScriptRoot/source-policy-connected-cleanup-bridge.php",'--private-connected-cleanup-data',$run,$source);$children.Add($script:connectedBridge)
    foreach ($caseName in @('missing-table','positive')) {
        $stage=$caseName
        $child=New-AdbcProcess $php ($nativeArgs+@($source,$caseName));$children.Add($child)
        if ((Send-Adbc2Data ('launch|'+$clock.ElapsedMilliseconds+'|'+$child.Id) $clock) -cne "OK|output`n") { throw 'Fresh fixed case refused' }
        $wire=Read-AdbcLine $child $clock 25000
        $totalOut+=$child.OutBytes
        if ($totalOut -gt 131072 -or $child.Process.HasExited) { throw 'Output/liveness refused' }
        if ((Send-Adbc2Data ('feed|'+$clock.ElapsedMilliseconds+'|'+$wire.TrimEnd("`n")) $clock) -cne "OK|held`n") { throw 'Fresh terminal DATA refused' }
        Assert-Adbc2Clear $clock 25000
        if ($child.Process.HasExited) { throw 'Child exited during live reconciliation' }
        $ack=Send-Adbc2Data ('live|'+$clock.ElapsedMilliseconds+'|'+$child.Id+',1,0,0') $clock
        if ($ack -cne ('ADBC2-ACK|'+$run+'|'+$source+'|'+$caseName+'|'+$child.Id+"`n")) { throw 'New live token refused' }
        Write-AdbcLine $child $ack.TrimEnd("`n") $clock 25000
        Complete-AdbcProcess $child $clock 25000
        Assert-Adbc2Clear $clock 25000
        $expected=if ($caseName -ceq 'positive') { "OK|done`n" } else { "OK|launch`n" }
        if ((Send-Adbc2Data ('finish|'+$clock.ElapsedMilliseconds+'|'+$child.Id+',0,0,0,0') $clock) -cne $expected) { throw 'Post-exit DATA refused' }
        $completed++
        Write-Output ('ADBC2_CASE_COMPLETE case:'+$caseName+' live/post:true elapsed_ms:'+$clock.ElapsedMilliseconds+' terminal:'+$wire.TrimEnd("`n"))
    }
    Complete-AdbcProcess $script:connectedBridge $clock 25000
    Write-Output ('ADBC2_RELEASE_COMPLETE cases:'+$completed+' elapsed_ms:'+$clock.ElapsedMilliseconds+' source:'+$source+' run:'+$run+' native_qualification:not-established wiring:disabled')
} catch {
    Write-Output ('ADBC2_UNCONFIRMED stage:'+$stage+' completed:'+$completed+' native_qualification:not-established')
    throw [InvalidOperationException]::new('Bounded connected proof stopped; no retry authorized')
} finally {
    $cleanupFailed=$false
    foreach ($owned in $children) { try {Stop-AdbcProcess $owned} catch {$cleanupFailed=$true;Write-Output ('OWNED_PROCESS_UNCONFIRMED id:'+$owned.Id)} }
    if ($cleanupFailed) { throw 'Exact owned process cleanup unconfirmed' }
}
