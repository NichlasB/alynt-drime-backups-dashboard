param([switch]$ConfirmedPluginTesterPerCaseCleanup,[string]$Prefix)
$ErrorActionPreference='Stop'
if (-not $ConfirmedPluginTesterPerCaseCleanup -or $Prefix -cnotmatch '\Aadbb_[a-f0-9]{12}_\z' -or $args.Count -ne 0 -or -not $IsWindows) { throw 'Exact confirmed Plugin Tester scope required' }
if ([string]::IsNullOrEmpty($env:MYSQL_PWD)) { throw 'Inherited private credential environment required' }
$script:AdbcScope=$true
. "$PSScriptRoot/source-policy-cleanup-process.ps1"
$php='C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/php.exe'
$mysql='C:/Users/Captain/AppData/Roaming/Local/lightning-services/mariadb-10.6.23+0/bin/win32/bin/mysql.exe'
$extension='extension_dir=C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/ext'
$nativeArgs=@('-n','-d',$extension,'-d','extension=mysqli',"$PSScriptRoot/source-policy-cleanup-native-worker.php",'--confirmed-plugin-tester-per-case-cleanup',$Prefix)
$run=[guid]::NewGuid().ToString('N')
$zero='0'*64
$children=[System.Collections.Generic.List[object]]::new()
$stage='metadata'
$completed=0
$totalOut=0

function Invoke-AdbcSQL([string]$SQL,$Clock,[int]$Limit) {
    $observer=New-AdbcProcess $mysql @('--no-defaults','-h','127.0.0.1','-P','10004','-u','root','-D','local','--connect-timeout=2','-N','-B','-r','-e',$SQL)
    try {
        $line=Read-AdbcLine $observer $Clock $Limit
        Complete-AdbcProcess $observer $Clock $Limit
        return $line.Replace("`r`n","`n") # Bundled Windows mysql text output only; native evidence is never normalized.
    } finally { Stop-AdbcProcess $observer }
}

function Assert-AdbcClear($Clock,[int]$Limit) {
    # No DB filter: includes NULL and foreign selected databases. Never reads SQL/user/host payloads.
    $line=Invoke-AdbcSQL "SELECT (SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);" $Clock $Limit
    if ($line -cne "0`t0`n") { throw 'All-server sessions/transactions not clear' }
}

function Invoke-AdbcBridge([string]$Command,$Clock) {
    Write-AdbcLine $script:bridge $Command $Clock 25000
    return (Read-AdbcLine $script:bridge $Clock 25000)
}

function Get-AdbcFixtureAuditSql([string]$OwnedPrefix) {
    if ($OwnedPrefix -cnotmatch '\Aadbb_[a-f0-9]{12}_\z') { throw 'Exact owned fixture prefix required' }
    return "SELECT (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='local' AND TABLE_NAME='$($OwnedPrefix)options'),(SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='local' AND TABLE_NAME='$($OwnedPrefix)missing_options'),(SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='$($OwnedPrefix)absent'),(SELECT COUNT(*) FROM information_schema.USER_PRIVILEGES WHERE PRIVILEGE_TYPE='PROCESS' AND GRANTEE=CONCAT(QUOTE(SUBSTRING_INDEX(CURRENT_USER(),'@',1)),'@',QUOTE(SUBSTRING_INDEX(CURRENT_USER(),'@',-1))));"
}

try {
    $sites=Get-Content -Raw 'C:/Users/Captain/AppData/Roaming/Local/sites.json' | ConvertFrom-Json
    $site=@($sites.PSObject.Properties.Value | Where-Object { $_.domain -ceq 'plugin-tester.local' })
    if ($site.Count -ne 1 -or $site[0].name -cne 'Plugin Tester' -or [System.IO.Path]::GetFullPath([string]$site[0].path).TrimEnd('\','/') -ine 'C:\Users\Captain\Local Sites\plugin-tester' -or $site[0].services.mariadb.version -cne '10.6.23' -or $site[0].services.php.version -cne '8.5.12' -or $site[0].services.mariadb.ports.MYSQL[0] -ne 10004) { throw 'Frozen local metadata drift' }
    $clock=[System.Diagnostics.Stopwatch]::StartNew()
    $manifest=New-AdbcProcess $php ($nativeArgs+@($run,$zero,'manifest'));$children.Add($manifest)
    $source=(Read-AdbcLine $manifest $clock 10000).TrimEnd("`n")
    Complete-AdbcProcess $manifest $clock 10000
    if ($source -cnotmatch '^[a-f0-9]{64}$') { throw 'Source correlation refused' }
    $stage='fixture-audit'
    $clock.Restart()
    $sql=Get-AdbcFixtureAuditSql $Prefix
    if ((Invoke-AdbcSQL $sql $clock 10000) -cne "1`t0`t0`t1`n") { throw 'Owned empty fixture/absent controls/observer privilege required' }
    if ((Invoke-AdbcSQL "SELECT COUNT(*) FROM $($Prefix)options;" $clock 10000) -cne "0`n") { throw 'Fixture must be schema-only and empty' }
    Assert-AdbcClear $clock 10000
    $stage='raw-reference'
    $clock.Restart()
    $raw=New-AdbcProcess $php ($nativeArgs+@($run,$source,'raw'));$children.Add($raw)
    $wire=Read-AdbcLine $raw $clock 10000
    $fields=$wire.TrimEnd("`n").Split('|')
    if ($fields.Count -ne 9 -or $fields[0] -cne 'ADBR1' -or $fields[1] -cne $run -or $fields[2] -cne $source -or $fields[3] -cne [string]$raw.Id -or $fields[4] -cne '1049' -or $fields[5] -cne '1' -or $fields[6] -cne '1' -or $fields[7] -cne (('0,'*10)+'0') -or $fields[8] -cnotmatch '^0,1,0,0,0,0,0,0,0,0,(0|[1-9][0-9]{0,19})$' -or $raw.Process.HasExited) { throw 'Actual failure-aware raw release evidence refused' }
    Assert-AdbcClear $clock 10000
    if ($raw.Process.HasExited) { throw 'Raw child not alive during reconciliation' }
    Write-AdbcLine $raw ('ADBC-ACK|'+$run+'|raw|'+$raw.Id) $clock 10000
    Complete-AdbcProcess $raw $clock 10000
    Assert-AdbcClear $clock 10000
    Write-Output ('raw-reference complete code:1049 close_ack:true active_counter:'+($fields[8].Split(',')[-1]))
    $stage='owner-cohort'
    $clock.Restart() # One original 25s deadline for all seven cases, startup/hold/observer/exit included.
    $script:bridge=New-AdbcProcess $php @('-n',"$PSScriptRoot/source-policy-cleanup-bridge.php",'--private-cleanup-data-bridge',$run,$source);$children.Add($script:bridge)
    foreach ($caseName in @('default','wrong-source','all','incompatible_sql_modes','missing-database','missing-table','positive')) {
        $stage=$caseName
        $child=New-AdbcProcess $php ($nativeArgs+@($run,$source,$caseName));$children.Add($child)
        if ((Invoke-AdbcBridge ('launch|'+$clock.ElapsedMilliseconds+'|'+$child.Id) $clock) -cne "OK|output`n") { throw 'Fixed case launch refused' }
        $wire=Read-AdbcLine $child $clock 25000
        $totalOut += $child.OutBytes
        if ($totalOut -gt 131072 -or $child.Process.HasExited) { throw 'Cohort bound or held-child liveness failed' }
        if ((Invoke-AdbcBridge ('feed|'+$clock.ElapsedMilliseconds+'|'+$wire.TrimEnd("`n")) $clock) -cne "OK|held`n") { throw 'Terminal evidence refused' }
        Assert-AdbcClear $clock 25000
        if ($child.Process.HasExited) { throw 'Child not alive during all-session reconciliation' }
        $ack=Invoke-AdbcBridge ('live|'+$clock.ElapsedMilliseconds+'|'+$child.Id+',1,0,0') $clock
        if ($ack -cne ('ADBC-ACK|'+$run+'|'+$caseName+'|'+$child.Id+"`n")) { throw 'Live receipt refused' }
        Write-AdbcLine $child $ack.TrimEnd("`n") $clock 25000
        Complete-AdbcProcess $child $clock 25000
        Assert-AdbcClear $clock 25000
        $expected=if ($caseName -ceq 'positive') { "OK|done`n" } else { "OK|launch`n" }
        if ((Invoke-AdbcBridge ('finish|'+$clock.ElapsedMilliseconds+'|'+$child.Id+',0,0,0,0') $clock) -cne $expected) { throw 'Post-exit receipt refused' }
        $completed++
        Write-Output ('case:'+ $caseName+' complete live/post:true elapsed_ms:'+$clock.ElapsedMilliseconds+' terminal:'+ $wire.TrimEnd("`n"))
    }
    Complete-AdbcProcess $script:bridge $clock 25000
    Write-Output ('cohort complete cases:'+$completed+' elapsed_ms:'+$clock.ElapsedMilliseconds+' source:'+$source+' native_wiring:disabled')
} catch {
    Write-Output ('UNCONFIRMED stage:'+$stage+' completed:'+$completed+' reason:'+$_.Exception.Message)
    throw
} finally {
    $cleanupFailed=$false
    foreach ($child in $children) {
        try { Stop-AdbcProcess $child }
        catch { $cleanupFailed=$true; Write-Output ('OWNED_CLEANUP_UNCONFIRMED id:'+$child.Id) }
    }
    if ($cleanupFailed) { throw 'Owned process cleanup unconfirmed; any primary failure remains reported above' }
}
