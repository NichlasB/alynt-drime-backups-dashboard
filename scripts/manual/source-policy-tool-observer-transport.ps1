# Private functions only. Loading this file never starts a process or reads a host.
function Assert-ToolLine([string]$Actual,[string]$Expected) {
    if ($Actual.Length -gt 512 -or $Actual -cnotmatch '\A[\x20-\x7e]+\n\z' -or $Actual -cne $Expected) { throw 'tool-frame-refused' }
}
function Read-ToolSummary([string]$Line,$Worker,$Observer,[string]$Run) {
    if ($Line.Length -gt 512 -or $Line -cnotmatch '\A[\x20-\x7e]+\n\z') { throw 'tool-summary-refused' }
    if ($Run -cnotmatch '\A[a-f0-9]{32}\z' -or $Worker.Id -isnot [int] -or $Worker.Id -le 0 -or $Worker.Tick -isnot [long] -or $Worker.Tick -le 0 -or $Observer.Id -isnot [int] -or $Observer.Id -le 0 -or $Observer.Tick -isnot [long] -or $Observer.Tick -le 0 -or $Observer.Source -cnotmatch '\A[a-f0-9]{64}\z' -or $Observer.Role -cne 'observer' -or $Observer.Run -cne $Run -or $Observer.TargetId -isnot [int] -or $Observer.TargetTick -isnot [long] -or $Observer.TargetId -ne $Worker.Id -or $Observer.TargetTick -ne $Worker.Tick) { throw 'tool-summary-context-refused' }
    $fields=$Line.TrimEnd("`n").Split('|')
    $prefix='ADBTS1|'+$Run+'|observer|'+$Observer.Id+'|'+$Observer.Tick+'|'+$Observer.Source+'|modules|'+$Worker.Id+'|'+$Worker.Tick+'|'
    if (-not $Line.StartsWith($prefix,[StringComparison]::Ordinal) -or $fields.Count -ne 14 -or $fields[9] -cnotmatch '\A[1-9][0-9]{0,2}\z' -or $fields[10] -cnotmatch '\A[1-9][0-9]{0,8}\z' -or $fields[11] -cnotmatch '\A[1-9][0-9]{0,7}\z' -or $fields[12] -cnotmatch '\A[a-f0-9]{64}\z' -or $fields[13] -cne 'stable') { throw 'tool-summary-refused' }
    $count=[int]$fields[9];$bytes=[long]$fields[10];$max=[int]$fields[11]
    if ($count -lt 2 -or $count -gt 135 -or $bytes -gt 268435456 -or $max -gt 33554432 -or $bytes -lt $max -or $bytes -gt $count*$max) { throw 'tool-summary-refused' }
    return [pscustomobject]@{Count=$count;Bytes=$bytes;Max=$max;Digest=$fields[12]}
}
function Assert-ToolCatalogReceipt($Snapshot,$Length,$Digest) {
    if ($Length -isnot [long] -or $Length -lt 1 -or $Length -gt 131072 -or $Digest -isnot [string] -or $Digest -cnotmatch '\A[a-f0-9]{64}\z' -or $Snapshot.Digest -cne $Digest) { throw 'tool-catalog-refused' }
}
function Read-ToolDiagnostic([string]$Line,$Child) {
    # A validated error fact never authorizes positive completion.
    if ($Child.Role -cne 'observer' -or $Child.Run -cnotmatch '\A[a-f0-9]{32}\z' -or $Child.Source -cnotmatch '\A[a-f0-9]{64}\z' -or $Child.Id -isnot [int] -or $Child.TargetId -isnot [int] -or $Child.Tick -isnot [long] -or $Child.TargetTick -isnot [long] -or $Child.Id -le 0 -or $Child.TargetId -le 0 -or $Child.Tick -le 0 -or $Child.TargetTick -le 0 -or $Line.Length -gt 512 -or $Line -cnotmatch '\A[\x20-\x7e]+\n\z') { return $null }
    $prefix='ADBTSD1|'+$Child.Run+'|observer|'+$Child.Id+'|'+$Child.Tick+'|'+$Child.Source+'|'+$Child.TargetId+'|'+$Child.TargetTick+'|'
    $f=$Line.TrimEnd("`n").Split('|')
    if (-not $Line.StartsWith($prefix,[StringComparison]::Ordinal) -or $f.Count -ne 13 -or $f[8] -cnotin @('scope','self','lookup','identity','snapshot-one','snapshot-two','compare','catalog','emit','finish','hold') -or $f[9] -cnotin @('api','scope','identity','module-count','module-name','module-path','module-size','module-duplicate','module-total','module-required','catalog-bound','snapshots-changed','hold-released')) { return $null }
    foreach ($i in @(10,11,12)) { if ($f[$i] -cnotmatch '\A(?:0|[1-9][0-9]{0,2})\z') { return $null } }
    $rows=[int]$f[10];$index=[int]$f[11];$mask=[int]$f[12]
    if ($rows -gt 136 -or $index -gt 135 -or $index -gt $rows -or $mask -gt 15 -or ($f[9] -ceq 'module-count' -and $index -ne 0)) { return $null }
    $snapshot=$f[8] -cin @('snapshot-one','snapshot-two')
    if ((($f[9].StartsWith('module-',[StringComparison]::Ordinal) -or $f[9] -ceq 'catalog-bound') -and -not $snapshot) -or ($f[9] -ceq 'scope' -and $f[8] -cne 'scope') -or ($f[9] -ceq 'snapshots-changed' -and $f[8] -cne 'compare') -or ($f[9] -ceq 'hold-released' -and $f[8] -cne 'hold') -or ($f[9] -ceq 'identity' -and $f[8] -cne 'identity' -and -not $snapshot)) { return $null }
    $bits=@{'module-name'=1;'module-path'=2;'module-size'=4;'module-duplicate'=8}
    if (($bits.ContainsKey($f[9]) -and ($mask -band $bits[$f[9]]) -eq 0) -or (-not $bits.ContainsKey($f[9]) -and $mask -ne 0)) { return $null }
    return [pscustomobject]@{Phase=$f[8];Reason=$f[9];Rows=$rows;Index=$index;Mask=$mask}
}
function Add-ToolDiagnosticBytes($Child,[byte[]]$Buffer,[int]$Count,[bool]$Eof) {
    # Only a complete bounded stderr stream at EOF may leave a safe fact behind.
    $Child.ErrorFact=$null
    if ($Count -lt 0 -or $Count -gt 1024 -or $Count -gt $Buffer.Length -or ($Eof -and $Count -ne 0) -or $Child.ErrEof) {
        $Child.ErrInvalid=$true;$Child.ErrPrefix='';$Child.ErrBytes=4097;return
    }
    if ($Eof) {
        $Child.ErrEof=$true
        if (-not $Child.ErrInvalid -and $Child.ErrBytes -gt 0 -and $Child.ErrBytes -le 512 -and $Child.ErrPrefix.Length -eq $Child.ErrBytes) { $Child.ErrorFact=Read-ToolDiagnostic $Child.ErrPrefix $Child }
        $Child.ErrPrefix='';return
    }
    $Child.ErrBytes=[Math]::Min(4097,$Child.ErrBytes+$Count)
    if ($Child.ErrInvalid -or $Child.ErrBytes -gt 512) { $Child.ErrInvalid=$true;$Child.ErrPrefix='';return }
    for ($i=0;$i -lt $Count;$i++) { if ($Buffer[$i] -gt 126 -or ($Buffer[$i] -lt 32 -and $Buffer[$i] -ne 10)) { $Child.ErrInvalid=$true;$Child.ErrPrefix='';return } }
    $Child.ErrPrefix+=[Text.Encoding]::ASCII.GetString($Buffer,0,$Count)
}
function Assert-ToolIdentity($Child,[bool]$Live) {
    if ($Child.Process.Id -ne $Child.Id -or $Child.Process.StartTime.ToUniversalTime().Ticks -ne $Child.Tick -or $Child.Process.Handle -ne $Child.Handle -or ($Live -and $Child.Process.HasExited)) { throw 'tool-identity-refused' }
}
function New-ToolProcess([string]$Role,[switch]$Hold) {
    if (-not $script:ToolProof -or $Role -cnotin @('worker','observer')) { throw 'tool-private-scope-required' }
    $s=$script:ToolProof
    $info=[Diagnostics.ProcessStartInfo]::new();$info.FileName=$s.Image;$info.WorkingDirectory=$s.Root
    $info.UseShellExecute=$false;$info.CreateNoWindow=$true
    $info.RedirectStandardInput=$true;$info.RedirectStandardOutput=$true;$info.RedirectStandardError=$true
    $info.Environment.Clear()
    foreach ($key in $s.Environment.Keys) { $info.Environment.Add($key,$s.Environment[$key]) }
    foreach ($arg in @('-NoLogo','-NoProfile','-NonInteractive','-File',$s.Files[$Role],'-Run',$s.Run)) { [void]$info.ArgumentList.Add($arg) }
    if ($Role -ceq 'observer') {
        Assert-ToolIdentity $s.Worker $true
        foreach ($arg in @('-TargetId',[string]$s.Worker.Id,'-TargetTick',[string]$s.Worker.Tick,'-Image',$s.Image)) { [void]$info.ArgumentList.Add($arg) }
        if ($Hold) { [void]$info.ArgumentList.Add('-Hold') }
    }
    $p=[Diagnostics.Process]::new();$p.StartInfo=$info
    if (-not $p.Start()) { throw 'tool-start-refused' }
    # Register the original object before any fallible post-start property access.
    $c=[pscustomobject]@{Process=$p;Id=0;Tick=0L;Handle=[IntPtr]::Zero;Source=$s.Hashes[$Role];Out='';OutBytes=0;ErrBytes=0;OutBuffer=[byte[]]::new(1024);ErrBuffer=[byte[]]::new(1024);OutRead=$null;ErrRead=$null;Role=$Role;Run=$s.Run;TargetId=0;TargetTick=0L;ErrPrefix='';ErrInvalid=$false;ErrEof=$false;ErrorFact=$null}
    $s.Children.Add($c);$c.Id=$p.Id;$c.Tick=$p.StartTime.ToUniversalTime().Ticks;$c.Handle=$p.Handle
    if ($Role -ceq 'observer') { $c.TargetId=$s.Worker.Id;$c.TargetTick=$s.Worker.Tick }
    # Target module/image inspection is confined to the separate observer child.
    $c.OutRead=$p.StandardOutput.BaseStream.ReadAsync($c.OutBuffer,0,1024);$c.ErrRead=$p.StandardError.BaseStream.ReadAsync($c.ErrBuffer,0,1024)
    return $c
}
function Update-ToolProcess($Child) {
    foreach ($stream in @('Out','Err')) {
        $task=$Child.($stream+'Read');if ($null -eq $task -or -not $task.IsCompleted) { continue }
        $n=$task.GetAwaiter().GetResult()
        if ($n -eq 0) { $Child.($stream+'Read')=$null;if ($stream -ceq 'Err') { Add-ToolDiagnosticBytes $Child $Child.ErrBuffer 0 $true };continue }
        if ($stream -ceq 'Err') { Add-ToolDiagnosticBytes $Child $Child.ErrBuffer $n $false }
        else {
            if ($Child.OutBytes+$n -gt 16384) { throw 'tool-stdout-bound' }
            foreach ($b in $Child.OutBuffer[0..($n-1)]) { if ($b -gt 126 -or ($b -lt 32 -and $b -ne 10)) { throw 'tool-output-refused' } }
            $Child.Out+=[Text.Encoding]::ASCII.GetString($Child.OutBuffer,0,$n);$Child.OutBytes+=$n
        }
        if ($stream -ceq 'Out') { $Child.OutRead=$Child.Process.StandardOutput.BaseStream.ReadAsync($Child.OutBuffer,0,1024) }
        else { $Child.ErrRead=$Child.Process.StandardError.BaseStream.ReadAsync($Child.ErrBuffer,0,1024) }
    }
}
function Read-ToolLine($Child,[int]$Limit) {
    while ($script:ToolProof.Clock.ElapsedMilliseconds -lt $Limit) {
        Assert-ToolIdentity $Child $false;Update-ToolProcess $Child
        if ($Child.ErrBytes -gt 0) { throw 'tool-stderr-refused' }
        $index=$Child.Out.IndexOf("`n")
        if ($index -ge 0) { $line=$Child.Out.Substring(0,$index+1);$Child.Out=$Child.Out.Substring($index+1);return $line }
        if ($Child.Out.Length -gt 512) { throw 'tool-frame-bound' }
        if ($Child.Process.HasExited -and $null -eq $Child.OutRead -and $null -eq $Child.ErrRead) { throw 'tool-early-eof' }
        [Threading.Thread]::Sleep(2)
    }
    throw 'tool-deadline'
}
function Complete-ToolProcess($Child,[int]$Limit,[bool]$Clean) {
    Assert-ToolIdentity $Child $false
    while ($script:ToolProof.Clock.ElapsedMilliseconds -lt $Limit) {
        Update-ToolProcess $Child
        if ($Child.Process.HasExited -and $null -eq $Child.OutRead -and $null -eq $Child.ErrRead) {
            if ($Clean -and ($Child.Out.Length -ne 0 -or $Child.ErrBytes -ne 0 -or $Child.Process.ExitCode -ne 0)) { throw 'tool-exit-refused' }
            if (-not $Clean) { $Child.Out='' } # Failure cleanup drains/discards, never launders a clean result.
            Assert-ToolIdentity $Child $false;return
        }
        [Threading.Thread]::Sleep(2)
    }
    throw 'tool-drain-deadline'
}
function Stop-ToolProcess($Child,[int]$Limit) {
    # Never kill by process name, rediscovered PID or a guessed descendant tree.
    Assert-ToolIdentity $Child $false
    if (-not $Child.Process.HasExited) { $Child.Process.Kill() }
    Complete-ToolProcess $Child $Limit $false
    if (-not $Child.Process.HasExited) { throw 'tool-owned-absence-unconfirmed' }
}
