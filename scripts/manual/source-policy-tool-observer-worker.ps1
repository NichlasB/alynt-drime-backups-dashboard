# Fixed existing-tool held worker. No child creation, module loading or site access.
param([string]$Run)
$ErrorActionPreference='Stop'
try {
    if ($args.Count -ne 0 -or $Run -cnotmatch '\A[a-f0-9]{32}\z' -or [IO.Path]::GetFileName($PSScriptRoot) -cne ('adbtool-'+$Run)) { throw 'Owned scope required' }
    $self=[Diagnostics.Process]::GetCurrentProcess()
    $tick=$self.StartTime.ToUniversalTime().Ticks.ToString([Globalization.CultureInfo]::InvariantCulture)
    $source=[Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([IO.File]::ReadAllBytes($PSCommandPath))).ToLowerInvariant()
    $prefix='ADBT1|'+$Run+'|worker|'+$PID+'|'+$tick+'|'+$source+'|'
    [Console]::Out.Write($prefix+"held`n");[Console]::Out.Flush()
    if ([Console]::In.ReadLine() -cne ('ADBT1-ACK|'+$Run+'|'+$PID+'|'+$tick)) { throw 'Exact ACK required' }
    [Console]::Out.Write($prefix+"released`n");[Console]::Out.Flush()
    $self.Dispose()
    exit 0
} catch { [Console]::Error.Write("tool-worker-refused`n");exit 1 }
