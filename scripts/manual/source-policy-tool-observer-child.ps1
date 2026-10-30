# Fixed observation-only child. A PID lookup is observation, never ownership authority.
param([string]$Run,[int]$TargetId,[long]$TargetTick,[string]$Image,[switch]$Hold)
$ErrorActionPreference='Stop'
$target=$null
$diagPrefix=$null;$script:DiagPhase='scope';$script:DiagReason='api';$script:DiagRows=0;$script:DiagIndex=0;$script:DiagMask=0
function Add-ToolCatalogRow($Rows,[string]$Name,[string]$Row,[ref]$Used) {
    $length=[Text.Encoding]::UTF8.GetByteCount($Row)+1
    if ($length -gt 131072-$Used.Value) { $script:DiagReason='catalog-bound';throw 'Private catalog bound refused' }
    $Rows.Add($Name,$Row);$Used.Value+=$length
}
function Assert-ToolSnapshotPair($first,$second) {
    if ($first.Text -cne $second.Text) { $script:DiagReason='snapshots-changed';throw 'Held snapshots changed' }
}
try {
    if ($args.Count -ne 0 -or $Run -cnotmatch '\A[a-f0-9]{32}\z' -or [IO.Path]::GetFileName($PSScriptRoot) -cne ('adbtool-'+$Run) -or $TargetId -le 0 -or $TargetTick -le 0 -or $Image -cne [Environment]::ProcessPath) { $script:DiagReason='scope';throw 'Owned observation scope required' }
    $script:DiagPhase='self'
    $self=[Diagnostics.Process]::GetCurrentProcess()
    $tick=$self.StartTime.ToUniversalTime().Ticks.ToString([Globalization.CultureInfo]::InvariantCulture)
    $source=[Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([IO.File]::ReadAllBytes($PSCommandPath))).ToLowerInvariant()
    $prefix='ADBTS1|'+$Run+'|observer|'+$PID+'|'+$tick+'|'+$source+'|'
    $diagPrefix='ADBTSD1|'+$Run+'|observer|'+$PID+'|'+$tick+'|'+$source+'|'+$TargetId+'|'+$TargetTick+'|'
    [Console]::Out.Write($prefix+"ready`n");[Console]::Out.Flush()
    if ($Hold) { $script:DiagPhase='hold';[void][Console]::In.ReadLine();$script:DiagReason='hold-released';throw 'Controlled hold released unexpectedly' }
    $script:DiagPhase='lookup'
    $target=[Diagnostics.Process]::GetProcessById($TargetId)
    [void]$target.Handle
    $script:DiagPhase='identity'
    if ($target.HasExited -or $target.StartTime.ToUniversalTime().Ticks -ne $TargetTick -or $target.MainModule.FileName -cne $Image) { $script:DiagReason='identity';throw 'Target observation identity refused' }
    function Get-ToolSnapshot {
        $script:DiagReason='api';$script:DiagRows=0;$script:DiagIndex=0;$script:DiagMask=0
        $target.Refresh()
        if ($target.HasExited -or $target.StartTime.ToUniversalTime().Ticks -ne $TargetTick) { $script:DiagReason='identity';throw 'Target changed' }
        $modules=$target.Modules
        $script:DiagRows=[Math]::Min(136,$modules.Count)
        if ($modules.Count -lt 2 -or $modules.Count -gt 135) { $script:DiagReason='module-count';throw 'Module count refused' }
        $rows=[Collections.Generic.SortedDictionary[string,string]]::new([StringComparer]::OrdinalIgnoreCase)
        $total=0L;$max=0;$used=0
        foreach ($module in $modules) {
            if ($script:DiagIndex -ge 135) { $script:DiagRows=136;$script:DiagIndex=0;$script:DiagMask=0;$script:DiagReason='module-count';throw 'Module enumeration bound refused' }
            $script:DiagIndex++;$script:DiagMask=0
            $name=$module.ModuleName;$path=$module.FileName;$size=$module.ModuleMemorySize
            $badName=$name.Length -gt 100 -or $name -cnotmatch '\A[A-Za-z0-9._-]+\.(?:[dD][lL][lL]|[eE][xX][eE])\z'
            $badPath=$false;$badSize=$false;$duplicate=$false
            # Preserve the original short-circuit guard order, including malformed metadata.
            if (-not $badName) { $badPath=$path.Length -gt 240 -or $path.Contains('|') -or $path.Contains("`n") -or $path.Contains("`r") -or $path -cnotmatch '\A[A-Za-z]:\\' }
            if (-not $badName -and -not $badPath) { $badSize=$size -le 0 -or $size -gt 33554432 }
            if (-not $badName -and -not $badPath -and -not $badSize) { $duplicate=$rows.ContainsKey($name) }
            $script:DiagMask=[int]$badName+2*[int]$badPath+4*[int]$badSize+8*[int]$duplicate
            if ($badName -or $badPath -or $badSize -or $duplicate) { $script:DiagReason=if ($badName) {'module-name'} elseif ($badPath) {'module-path'} elseif ($badSize) {'module-size'} else {'module-duplicate'};throw 'Module metadata refused' }
            $total+=$size;if ($total -gt 268435456) { $script:DiagReason='module-total';throw 'Module image bound refused' };$max=[Math]::Max($max,$size)
            $row=$name+'|'+[Convert]::ToHexString([Text.Encoding]::UTF8.GetBytes($path))+'|'+$size
            Add-ToolCatalogRow $rows $name $row ([ref]$used)
        }
        if ($rows.Count -ne $modules.Count) { $script:DiagRows=$rows.Count;$script:DiagIndex=0;$script:DiagReason='module-count';throw 'Module enumeration mismatch' }
        if (-not $rows.ContainsKey('pwsh.exe') -or -not $rows.ContainsKey('kernel32.dll')) { $script:DiagReason='module-required';throw 'Required tool modules absent' }
        if ($target.HasExited -or $target.StartTime.ToUniversalTime().Ticks -ne $TargetTick -or $target.MainModule.FileName -cne $Image) { $script:DiagReason='identity';throw 'Target changed during observation' }
        $text=[string]::Join("`n",$rows.Values)+"`n"
        if ([Text.Encoding]::UTF8.GetByteCount($text) -gt 131072 -or [Text.Encoding]::UTF8.GetByteCount($text) -ne $used) { $script:DiagReason='catalog-bound';throw 'Private catalog bound refused' }
        return [pscustomobject]@{Text=$text;Count=$rows.Count;Bytes=$total;Max=$max}
    }
    $script:DiagPhase='snapshot-one';$first=Get-ToolSnapshot;$script:DiagPhase='snapshot-two';$second=Get-ToolSnapshot
    $script:DiagPhase='compare'
    Assert-ToolSnapshotPair $first $second
    $script:DiagPhase='catalog'
    $catalog=[IO.Path]::Combine($PSScriptRoot,'modules.txt')
    $bytes=[Text.Encoding]::UTF8.GetBytes($first.Text)
    $file=[IO.File]::Open($catalog,[IO.FileMode]::CreateNew,[IO.FileAccess]::Write,[IO.FileShare]::None)
    try { $file.Write($bytes,0,$bytes.Length);$file.Flush() } finally { $file.Dispose() }
    $digest=[Convert]::ToHexString([Security.Cryptography.SHA256]::HashData($bytes)).ToLowerInvariant()
    $script:DiagPhase='emit'
    [Console]::Out.Write($prefix+'modules|'+$TargetId+'|'+$TargetTick+'|'+$first.Count+'|'+$first.Bytes+'|'+$first.Max+'|'+$digest+"|stable`n");[Console]::Out.Flush()
    $script:DiagPhase='finish';$self.Dispose();exit 0
} catch {
    if ($null -ne $diagPrefix) { [Console]::Error.Write($diagPrefix+$script:DiagPhase+'|'+$script:DiagReason+'|'+$script:DiagRows+'|'+$script:DiagIndex+'|'+$script:DiagMask+"`n") }
    else { [Console]::Error.Write("tool-observer-refused`n") }
    exit 1
}
finally { if ($null -ne $target) { $target.Dispose() } }
