param([switch]$ConfirmedOwnedSocketFreeRawAudit)
$ErrorActionPreference='Stop'
if (-not $ConfirmedOwnedSocketFreeRawAudit -or $args.Count -ne 0) { throw 'Explicit socket-free owned raw audit required' }
$script:AdbcScope=$true
. "$PSScriptRoot/source-policy-cleanup-process.ps1"
$php='C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/php.exe'
$run='a'*32
$source=[Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([Text.Encoding]::UTF8.GetBytes('isolated-raw-connected'))).ToLowerInvariant()
$clock=[Diagnostics.Stopwatch]::StartNew()
$checks=0
function Assert-AdcrStdio([bool]$Condition) { $script:checks++;if (-not $Condition){throw ('Raw socket-free assertion:'+$script:checks)} }
$child=$null;$bridge=$null
try {
    $child=New-AdbcProcess $php @('-n',"$PSScriptRoot/../../tests/support/source-policy-connected-raw-worker.php",'--isolated-raw-connected',$run,'normal')
    $bridge=New-AdbcProcess $php @('-n',"$PSScriptRoot/source-policy-connected-raw-bridge.php",'--private-raw-connected-data',$run,$source,[string]$child.Id)
    $connected=Read-AdbcLine $child $clock 25000
    Write-AdbcLine $bridge ('feed|'+$clock.ElapsedMilliseconds+'|'+$connected.TrimEnd("`n")) $clock 25000
    Assert-AdcrStdio ((Read-AdbcLine $bridge $clock 25000) -ceq "OK|connected_hold`n")
    Assert-AdcrStdio (-not $child.Process.HasExited)
    # Virtual observer DATA only, explicitly NOT an actual server observation.
    Write-AdbcLine $bridge ('live|'+$clock.ElapsedMilliseconds+'|'+$child.Id+',1,1,0') $clock 25000
    $close=Read-AdbcLine $bridge $clock 25000
    Assert-AdcrStdio ($close -ceq ('ADCR-CLOSE|'+$run+'|'+$source+'|'+$child.Id+"`n"))
    Write-AdbcLine $child $close.TrimEnd("`n") $clock 25000
    $closed=Read-AdbcLine $child $clock 25000
    Assert-AdcrStdio ($closed.Contains('18446744073709551615'))
    Write-AdbcLine $bridge ('feed|'+$clock.ElapsedMilliseconds+'|'+$closed.TrimEnd("`n")) $clock 25000
    Assert-AdcrStdio ((Read-AdbcLine $bridge $clock 25000) -ceq "OK|closed_hold`n")
    Assert-AdcrStdio (-not $child.Process.HasExited)
    Write-AdbcLine $bridge ('live|'+$clock.ElapsedMilliseconds+'|'+$child.Id+',1,0,0') $clock 25000
    $exit=Read-AdbcLine $bridge $clock 25000
    Assert-AdcrStdio ($exit -ceq ('ADCR-EXIT|'+$run+'|'+$source+'|'+$child.Id+"`n"))
    Assert-AdcrStdio ($exit -cne $close)
    Write-AdbcLine $child $exit.TrimEnd("`n") $clock 25000
    Assert-AdcrStdio ((Read-AdbcLine $child $clock 25000) -ceq "CHECKS|1|1|1|4`n")
    Complete-AdbcProcess $child $clock 25000
    Write-AdbcLine $bridge ('finish|'+$clock.ElapsedMilliseconds+'|0,'+$child.Id+',0,0,0') $clock 25000
    Assert-AdcrStdio ((Read-AdbcLine $bridge $clock 25000) -ceq "OK|done`n")
    Complete-AdbcProcess $bridge $clock 25000
    Write-Output ('raw-stdio-audit checks:'+$checks+' processes:2 native_connections:0 SQL:0 fixtures:0')
} finally { try {Stop-AdbcProcess $child} finally {Stop-AdbcProcess $bridge} }
