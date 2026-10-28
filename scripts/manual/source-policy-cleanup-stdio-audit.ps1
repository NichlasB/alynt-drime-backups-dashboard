param([switch]$ConfirmedOwnedStdioAudit)
$ErrorActionPreference='Stop'
if (-not $ConfirmedOwnedStdioAudit -or $args.Count -ne 0 -or -not $IsWindows) { throw 'Confirmed owned socket-free scope required' }
$script:AdbcScope=$true
. "$PSScriptRoot/source-policy-cleanup-process.ps1"
$php='C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/php.exe'
$worker=Join-Path $PSScriptRoot '../../tests/support/source-policy-cleanup-stdio-worker.php'
$checks=0
function Assert-AdbcAudit([bool]$Condition) { $script:checks++; if (-not $Condition) { throw ('Owned stdio assertion:'+$script:checks) } }
$clock=[System.Diagnostics.Stopwatch]::StartNew()
$child=New-AdbcProcess $php @('-n',$worker,'--private-socket-free-stdio','normal')
try {
    Assert-AdbcAudit ((Read-AdbcLine $child $clock 10000) -ceq "ready`n")
    Assert-AdbcAudit (-not $child.Process.HasExited)
    $unexpected=@(Write-AdbcLine $child 'ACK' $clock 10000)
    Assert-AdbcAudit ($unexpected.Count -eq 0)
    Assert-AdbcAudit ((Read-AdbcLine $child $clock 10000) -ceq "done`n")
    Complete-AdbcProcess $child $clock 10000
    Assert-AdbcAudit ($child.OutBytes -eq 11 -and $child.ErrBytes -eq 0)
} finally { Stop-AdbcProcess $child }
foreach ($mode in @('overflow','stderr','exit1','hold')) {
    $child=New-AdbcProcess $php @('-n',$worker,'--private-socket-free-stdio',$mode)
    $caught=$false
    try {
        if ($mode -ceq 'hold') {
            Assert-AdbcAudit ((Read-AdbcLine $child $clock 10000) -ceq "ready`n")
            $holdClock=[System.Diagnostics.Stopwatch]::StartNew()
            [void](Read-AdbcLine $child $holdClock 40)
        } else { [void](Read-AdbcLine $child $clock 10000) }
    } catch {
        $expected=switch ($mode) { 'overflow' {'Owned stdout bound exceeded'} 'stderr' {'Private stderr rejected'} 'exit1' {'Owned child exited without a complete record'} 'hold' {'Original proof deadline exhausted'} }
        Assert-AdbcAudit ($_.Exception.Message.StartsWith($expected))
        Assert-AdbcAudit (-not $_.Exception.Message.Contains('private-secret'))
        $caught=$true
    } finally { Stop-AdbcProcess $child }
    Assert-AdbcAudit $caught
}
Write-Output ('stdio-audit checks:'+$checks+' groups:5 native_connections:0 sql_queries:0 fixtures:0 owned_processes_cleaned:5')
