param([switch]$ConfirmedOwnedSocketFreeConnectedCleanupAudit)
$ErrorActionPreference='Stop'
if (-not $ConfirmedOwnedSocketFreeConnectedCleanupAudit -or $args.Count -ne 0 -or -not $IsWindows) { throw 'Confirmed socket-free owned connected scope required' }
$script:AdbcScope=$true
. "$PSScriptRoot/source-policy-cleanup-process.ps1"
$php='C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/php.exe'
$children=[Collections.Generic.List[object]]::new()
$clock=[Diagnostics.Stopwatch]::StartNew();$checks=0
function Assert-Adbc2Stdio([bool]$Condition) { $script:checks++;if (-not $Condition) { throw ('Connected stdio assertion:'+$script:checks) } }
function Send-Adbc2Virtual([string]$Command) { Write-AdbcLine $script:virtualBridge $Command $clock 25000;return (Read-AdbcLine $script:virtualBridge $clock 25000) }
try {
    $run='a'*32;$source='b'*64
    $script:virtualBridge=New-AdbcProcess $php @('-n',"$PSScriptRoot/source-policy-connected-cleanup-bridge.php",'--private-connected-cleanup-data',$run,$source);$children.Add($script:virtualBridge)
    foreach ($caseName in @('missing-table','positive')) {
        $child=New-AdbcProcess $php @('-n',"$PSScriptRoot/../../tests/support/source-policy-connected-cleanup-owner-worker.php",$caseName,'hold');$children.Add($child)
        Assert-Adbc2Stdio ((Send-Adbc2Virtual ('launch|'+$clock.ElapsedMilliseconds+'|'+$child.Id)) -ceq "OK|output`n")
        $wire=Read-AdbcLine $child $clock 25000
        Assert-Adbc2Stdio ($wire.StartsWith('ADBC2|') -and $wire.Contains('18446744073709551615'))
        Assert-Adbc2Stdio ((Send-Adbc2Virtual ('feed|'+$clock.ElapsedMilliseconds+'|'+$wire.TrimEnd("`n"))) -ceq "OK|held`n")
        Assert-Adbc2Stdio (-not $child.Process.HasExited)
        # Supplied virtual observation only; no server/SQL, never a native live-release claim.
        $ack=Send-Adbc2Virtual ('live|'+$clock.ElapsedMilliseconds+'|'+$child.Id+',1,0,0')
        Assert-Adbc2Stdio ($ack -ceq ('ADBC2-ACK|'+$run+'|'+$source+'|'+$caseName+'|'+$child.Id+"`n"))
        Write-AdbcLine $child $ack.TrimEnd("`n") $clock 25000
        Complete-AdbcProcess $child $clock 25000
        $expected=if($caseName -ceq 'positive'){"OK|done`n"}else{"OK|launch`n"}
        Assert-Adbc2Stdio ((Send-Adbc2Virtual ('finish|'+$clock.ElapsedMilliseconds+'|'+$child.Id+',0,0,0,0')) -ceq $expected)
    }
    Complete-AdbcProcess $script:virtualBridge $clock 25000
    Write-Output ('connected-stdio-audit checks:'+$checks+' processes:3 native_connections:0 SQL:0 fixtures:0')
} finally {
    $failed=$false
    foreach ($child in $children) {try{Stop-AdbcProcess $child}catch{$failed=$true}}
    if($failed){throw 'Owned socket-free cleanup unconfirmed'}
}
