# Count-only observation; target PID lookup is not ownership or kill authority.
param([string]$Run,[int]$TargetId,[long]$TargetTick,[string]$Image)
$ErrorActionPreference='Stop'
$target=$null;$self=$null
try {
    if ($args.Count -ne 0 -or $Run -cnotmatch '\A[a-f0-9]{32}\z' -or [IO.Path]::GetFileName($PSScriptRoot) -cne ('adbtool-'+$Run) -or $TargetId -le 0 -or $TargetTick -le 0 -or $Image -cne [Environment]::ProcessPath) { throw 'Count scope refused' }
    $self=[Diagnostics.Process]::GetCurrentProcess()
    $tick=$self.StartTime.ToUniversalTime().Ticks.ToString([Globalization.CultureInfo]::InvariantCulture)
    $source=[Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([IO.File]::ReadAllBytes($PSCommandPath))).ToLowerInvariant()
    [Console]::Out.Write('ADBT1|'+$Run+'|observer|'+$PID+'|'+$tick+'|'+$source+"|ready`n");[Console]::Out.Flush()
    $target=[Diagnostics.Process]::GetProcessById($TargetId)
    [void]$target.Handle
    $target.Refresh()
    if ($target.HasExited -or $target.StartTime.ToUniversalTime().Ticks -ne $TargetTick -or $target.MainModule.FileName -cne $Image) { throw 'Count identity refused' }
    # Getter acquisition may materialize native data; no constant-cost claim.
    $modules=$target.Modules
    $count=$modules.Count
    if ($count -isnot [int] -or $count -lt 2) { throw 'Count value refused' }
    $target.Refresh()
    if ($target.HasExited -or $target.StartTime.ToUniversalTime().Ticks -ne $TargetTick -or $target.MainModule.FileName -cne $Image) { throw 'Count identity changed' }
    [Console]::Out.Write('ADBTC1|'+$Run+'|count-only|'+$PID+'|'+$tick+'|'+$source+'|'+$TargetId+'|'+$TargetTick+'|'+$count+"|not-admission`n");[Console]::Out.Flush()
} catch { [Console]::Error.Write("tool-count-observer-refused`n");exit 1 }
finally { if ($null -ne $target) { $target.Dispose() };if ($null -ne $self) { $self.Dispose() } }
