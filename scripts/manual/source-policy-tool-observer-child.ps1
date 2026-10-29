# Fixed observation-only child. A PID lookup is observation, never ownership authority.
param([string]$Run,[int]$TargetId,[long]$TargetTick,[string]$Image,[switch]$Hold)
$ErrorActionPreference='Stop'
$target=$null
try {
    if ($args.Count -ne 0 -or $Run -cnotmatch '\A[a-f0-9]{32}\z' -or [IO.Path]::GetFileName($PSScriptRoot) -cne ('adbtool-'+$Run) -or $TargetId -le 0 -or $TargetTick -le 0 -or $Image -cne [Environment]::ProcessPath) { throw 'Owned observation scope required' }
    $self=[Diagnostics.Process]::GetCurrentProcess()
    $tick=$self.StartTime.ToUniversalTime().Ticks.ToString([Globalization.CultureInfo]::InvariantCulture)
    $source=[Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([IO.File]::ReadAllBytes($PSCommandPath))).ToLowerInvariant()
    $prefix='ADBT1|'+$Run+'|observer|'+$PID+'|'+$tick+'|'+$source+'|'
    [Console]::Out.Write($prefix+"ready`n");[Console]::Out.Flush()
    if ($Hold) { [void][Console]::In.ReadLine();throw 'Controlled hold released unexpectedly' }
    $target=[Diagnostics.Process]::GetProcessById($TargetId)
    [void]$target.Handle
    if ($target.HasExited -or $target.StartTime.ToUniversalTime().Ticks -ne $TargetTick -or $target.MainModule.FileName -cne $Image) { throw 'Target observation identity refused' }
    function Get-ToolSnapshot {
        $target.Refresh()
        if ($target.HasExited -or $target.StartTime.ToUniversalTime().Ticks -ne $TargetTick) { throw 'Target changed' }
        $modules=$target.Modules
        if ($modules.Count -lt 2 -or $modules.Count -gt 128) { throw 'Module count refused' }
        $rows=[Collections.Generic.SortedDictionary[string,string]]::new([StringComparer]::OrdinalIgnoreCase)
        $total=0L;$max=0
        foreach ($module in $modules) {
            $name=$module.ModuleName;$path=$module.FileName;$size=$module.ModuleMemorySize
            if ($name.Length -gt 100 -or $name -cnotmatch '\A[A-Za-z0-9._-]+\.(?:[dD][lL][lL]|[eE][xX][eE])\z' -or $path.Length -gt 240 -or $path.Contains('|') -or $path.Contains("`n") -or $path.Contains("`r") -or $path -cnotmatch '\A[A-Za-z]:\\' -or $size -le 0 -or $size -gt 33554432 -or $rows.ContainsKey($name)) { throw 'Module metadata refused' }
            $total+=$size;if ($total -gt 268435456) { throw 'Module image bound refused' };$max=[Math]::Max($max,$size)
            $rows.Add($name,($name+'|'+[Convert]::ToHexString([Text.Encoding]::UTF8.GetBytes($path))+'|'+$size))
        }
        if (-not $rows.ContainsKey('pwsh.exe') -or -not $rows.ContainsKey('kernel32.dll')) { throw 'Required tool modules absent' }
        if ($target.HasExited -or $target.StartTime.ToUniversalTime().Ticks -ne $TargetTick -or $target.MainModule.FileName -cne $Image) { throw 'Target changed during observation' }
        $text=[string]::Join("`n",$rows.Values)+"`n"
        if ([Text.Encoding]::UTF8.GetByteCount($text) -gt 131072) { throw 'Private catalog bound refused' }
        return [pscustomobject]@{Text=$text;Count=$rows.Count;Bytes=$total;Max=$max}
    }
    $first=Get-ToolSnapshot;$second=Get-ToolSnapshot
    if ($first.Text -cne $second.Text) { throw 'Held snapshots changed' }
    $catalog=[IO.Path]::Combine($PSScriptRoot,'modules.txt')
    $bytes=[Text.Encoding]::UTF8.GetBytes($first.Text)
    $file=[IO.File]::Open($catalog,[IO.FileMode]::CreateNew,[IO.FileAccess]::Write,[IO.FileShare]::None)
    try { $file.Write($bytes,0,$bytes.Length);$file.Flush() } finally { $file.Dispose() }
    $digest=[Convert]::ToHexString([Security.Cryptography.SHA256]::HashData($bytes)).ToLowerInvariant()
    [Console]::Out.Write($prefix+'modules|'+$TargetId+'|'+$TargetTick+'|'+$first.Count+'|'+$first.Bytes+'|'+$first.Max+'|'+$digest+"|stable`n");[Console]::Out.Flush()
    $self.Dispose();exit 0
} catch { [Console]::Error.Write("tool-observer-refused`n");exit 1 }
finally { if ($null -ne $target) { $target.Dispose() } }
