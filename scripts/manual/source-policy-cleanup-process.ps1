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
    $state = [pscustomobject]@{ Process=$process; Id=$process.Id; Out=''; OutBytes=0; ErrBytes=0; ErrorFact=''; OutBuffer=[byte[]]::new(4096); ErrBuffer=[byte[]]::new(1024); OutRead=$null; ErrRead=$null }
    $state.OutRead = $process.StandardOutput.BaseStream.ReadAsync($state.OutBuffer,0,4096)
    $state.ErrRead = $process.StandardError.BaseStream.ReadAsync($state.ErrBuffer,0,1024)
    return $state
}

function Get-AdbcRemaining($Clock, [int]$Limit) {
    $remaining = $Limit - [int]$Clock.ElapsedMilliseconds
    if ($remaining -le 0) { throw 'Original proof deadline exhausted' }
    return $remaining
}

function Update-AdbcProcess($Child) {
    foreach ($stream in @('Out','Err')) {
        $read = $Child.($stream+'Read')
        if ($null -eq $read -or -not $read.IsCompleted) { continue }
        $size = $read.GetAwaiter().GetResult()
        if ($size -eq 0) { $Child.($stream+'Read')=$null; continue }
        $buffer = $Child.($stream+'Buffer')
        if ($stream -eq 'Err') {
            $Child.ErrBytes += $size
            $text = [System.Text.Encoding]::ASCII.GetString($buffer,0,$size)
            if ($text -match 'native-cleanup failure stage:[a-z-]+ class:[A-Za-z_]+ checks:[0-9]+ assertion:[a-zA-Z0-9 :_-]+') { $Child.ErrorFact=$Matches[0] }
            throw ('Private stderr rejected ('+$Child.ErrBytes+' bytes) '+$Child.ErrorFact)
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
        if ($index -ge 0) {
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
    } finally { $Child.Process.Dispose() }
}
