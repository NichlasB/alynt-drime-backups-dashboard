# Private bounded process transport. No executable, SQL or credentials are accepted from end users.
if (-not $script:AdbcScope) { throw 'Private cleanup scope required' }

function New-AdbcProcess([string]$Executable, [string[]]$Arguments) {
    $info = [System.Diagnostics.ProcessStartInfo]::new()
    $info.FileName = $Executable
    $info.UseShellExecute = $false
    $info.CreateNoWindow = $true
    $info.RedirectStandardInput = $true
    $info.RedirectStandardOutput = $true
    $info.RedirectStandardError = $true
    foreach ($item in $Arguments) { [void]$info.ArgumentList.Add($item) }
    $process = [System.Diagnostics.Process]::new()
    $process.StartInfo = $info
    if (-not $process.Start()) { throw 'Owned process start refused' }
    $process.StandardInput.NewLine = "`n"
    $state = [pscustomobject]@{ Process=$process; Id=$process.Id; Out=''; OutBytes=0; ErrBytes=0; ErrorFact=''; ErrPrefix=''; OutBuffer=[byte[]]::new(4096); ErrBuffer=[byte[]]::new(1024); OutRead=$null; ErrRead=$null }
    $state.OutRead = $process.StandardOutput.BaseStream.ReadAsync($state.OutBuffer,0,4096)
    $state.ErrRead = $process.StandardError.BaseStream.ReadAsync($state.ErrBuffer,0,1024)
    return $state
}

function Get-AdbcRemaining($Clock, [int]$Limit) {
    $remaining = $Limit - [int]$Clock.ElapsedMilliseconds
    if ($remaining -le 0) { throw 'Original proof deadline exhausted' }
    return $remaining
}

function Get-AdbcErrorFact([string]$Prefix) {
    if ($Prefix.Length -gt 256) { return '' }
    if ($Prefix -cmatch '^ERROR ([1-9][0-9]{0,4}) \(([A-Z0-9]{5})\)') { return ('mysql_code:'+$Matches[1]+' sqlstate:'+$Matches[2]) }
    if ($Prefix -cmatch '^native-cleanup failure stage:(manifest|native-case|hold) class:(RuntimeException|Error|mysqli_sql_exception|Throwable) checks:(0|[1-9][0-9]{0,4})(?: |$)') { return ('worker_stage:'+$Matches[1]+' worker_class:'+$Matches[2]+' checks:'+$Matches[3]) }
    return ''
}

function Deny-AdbcError($Child) {
    $Child.ErrPrefix='' # Never expose or retain a partial raw error/SQL/message after rejection.
    throw ('Private stderr rejected ('+$Child.ErrBytes+' bytes) '+$Child.ErrorFact)
}

function Update-AdbcProcess($Child) {
    foreach ($stream in @('Out','Err')) {
        $read = $Child.($stream+'Read')
        if ($null -eq $read -or -not $read.IsCompleted) { continue }
        $size = $read.GetAwaiter().GetResult()
        if ($size -eq 0) { $Child.($stream+'Read')=$null; if ($stream -ceq 'Err' -and $Child.ErrBytes -gt 0) { Deny-AdbcError $Child }; continue }
        $buffer = $Child.($stream+'Buffer')
        if ($stream -eq 'Err') {
            $Child.ErrBytes += $size
            $keep=[Math]::Min($size,256-$Child.ErrPrefix.Length)
            $Child.ErrPrefix += [System.Text.Encoding]::ASCII.GetString($buffer,0,$keep)
            $Child.ErrorFact=Get-AdbcErrorFact $Child.ErrPrefix
            if ($Child.ErrBytes -gt 4096 -or $Child.ErrorFact -ne '' -or $Child.ErrPrefix.Contains("`n") -or $Child.ErrPrefix.Length -eq 256) { Deny-AdbcError $Child }
            $Child.ErrRead=$Child.Process.StandardError.BaseStream.ReadAsync($Child.ErrBuffer,0,1024)
            continue
        }
        if ($Child.OutBytes + $size -gt 16384) { throw 'Owned stdout bound exceeded' }
        for ($i=0;$i -lt $size;$i++) { if ($buffer[$i] -gt 127 -or ($buffer[$i] -lt 32 -and $buffer[$i] -notin @(9,10,13))) { throw 'Non-ASCII private output rejected' } }
        $Child.Out += [System.Text.Encoding]::ASCII.GetString($buffer,0,$size)
        $Child.OutBytes += $size
        $Child.OutRead=$Child.Process.StandardOutput.BaseStream.ReadAsync($Child.OutBuffer,0,4096)
    }
}

function Read-AdbcLine($Child, $Clock, [int]$Limit) {
    while ($true) {
        [void](Get-AdbcRemaining $Clock $Limit)
        Update-AdbcProcess $Child
        $index=$Child.Out.IndexOf("`n")
        if ($index -ge 0 -and $Child.ErrBytes -eq 0) {
            $line=$Child.Out.Substring(0,$index+1)
            $Child.Out=$Child.Out.Substring($index+1)
            return $line
        }
        if ($Child.Process.HasExited -and $null -eq $Child.OutRead -and $null -eq $Child.ErrRead) { throw 'Owned child exited without a complete record' }
        Start-Sleep -Milliseconds 5
    }
}

function Write-AdbcLine($Child, [string]$Line, $Clock, [int]$Limit) {
    if ($Line.Length -gt 16999 -or $Line.Contains("`r") -or $Line.Contains("`n")) { throw 'Bounded one-line private input required' }
    $write=$Child.Process.StandardInput.WriteLineAsync($Line)
    if (-not $write.Wait((Get-AdbcRemaining $Clock $Limit))) { throw 'Private write deadline exhausted' }
    [void]$write.GetAwaiter().GetResult()
    $flush=$Child.Process.StandardInput.FlushAsync()
    if (-not $flush.Wait((Get-AdbcRemaining $Clock $Limit))) { throw 'Private flush deadline exhausted' }
    [void]$flush.GetAwaiter().GetResult()
}

function Complete-AdbcProcess($Child, $Clock, [int]$Limit) {
    $Child.Process.StandardInput.Close()
    while (-not $Child.Process.HasExited -or $null -ne $Child.OutRead -or $null -ne $Child.ErrRead) {
        [void](Get-AdbcRemaining $Clock $Limit)
        Update-AdbcProcess $Child
        Start-Sleep -Milliseconds 5
    }
    if ($Child.Process.ExitCode -ne 0 -or $Child.Out.Length -ne 0 -or $Child.ErrBytes -ne 0) { throw 'Clean exact child exit required' }
}

function Stop-AdbcProcess($Child) {
    if ($null -eq $Child) { return }
    try {
        if ($Child.Process.Id -ne $Child.Id) { throw 'Owned process identity lost' }
        $Child.Process.StandardInput.Close()
        if (-not $Child.Process.HasExited -and -not $Child.Process.WaitForExit(250)) {
            $Child.Process.Kill() # Exact owned Process object only, never name/PID search or foreign cleanup.
            if (-not $Child.Process.WaitForExit(1000)) { throw 'Owned process cleanup unconfirmed' }
        }
    } finally { $Child.ErrPrefix=''; $Child.Process.Dispose() }
}
