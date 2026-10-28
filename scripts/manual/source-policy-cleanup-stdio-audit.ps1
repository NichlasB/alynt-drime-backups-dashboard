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
foreach ($mode in @('overflow','stderr','exit1','hold','mysql-error-fragments','mysql-error-eof','stderr-prefix-eof','stdout-partial-stderr','evidence-error')) {
    $child=New-AdbcProcess $php @('-n',$worker,'--private-socket-free-stdio',$mode)
    $caught=$false
    try {
        if ($mode -ceq 'hold') {
            Assert-AdbcAudit ((Read-AdbcLine $child $clock 10000) -ceq "ready`n")
            $holdClock=[System.Diagnostics.Stopwatch]::StartNew()
            [void](Read-AdbcLine $child $holdClock 40)
        } else { [void](Read-AdbcLine $child $clock 10000) }
    } catch {
        $expected=switch ($mode) { 'overflow' {'Owned stdout bound exceeded'} 'stderr' {'Private stderr rejected'} 'exit1' {'Owned child exited without a complete record'} 'hold' {'Original proof deadline exhausted'} default {'Private stderr rejected'} }
        Assert-AdbcAudit ($_.Exception.Message.StartsWith($expected))
        Assert-AdbcAudit (-not $_.Exception.Message.Contains('private-secret'))
        if ($mode -in @('mysql-error-fragments','stdout-partial-stderr')) { Assert-AdbcAudit ($_.Exception.Message.Contains('mysql_code:1064 sqlstate:42000')) }
        if ($mode -ceq 'mysql-error-eof') { Assert-AdbcAudit ($_.Exception.Message.Contains('mysql_code:1045 sqlstate:28000')) }
        if ($mode -ceq 'stderr-prefix-eof') { Assert-AdbcAudit (-not $_.Exception.Message.Contains('mysql_code:')) }
        if ($mode -ceq 'evidence-error') {
            Assert-AdbcAudit ($_.Exception.Message.Contains('evidence_case:missing-table pid:'+$child.Id+' baseline_zero:1'))
            Assert-AdbcAudit ($_.Exception.Message.Contains('bootstrap:1,0,4,2,2,0,0,0,0,0,0'))
            Assert-AdbcAudit ($_.Exception.Message.Contains('terminal:1,unavailable,0,1,1,1,1,0,1,1,1,1,1,1,closed,0,4,0,0,0,12'))
        }
        if ($mode -like '*error*' -or $mode -in @('stderr-prefix-eof','stderr','stdout-partial-stderr')) { Assert-AdbcAudit ($child.ErrPrefix -ceq '') }
        $caught=$true
    } finally { Stop-AdbcProcess $child }
    Assert-AdbcAudit $caught
}
Write-Output ('stdio-audit checks:'+$checks+' groups:10 native_connections:0 sql_queries:0 fixtures:0 owned_processes_cleaned:10')
