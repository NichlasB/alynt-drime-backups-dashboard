# Explicitly approved Windows owned-tool proof only. No candidate PHP/site/database work.
param([switch]$RunOwnedProof)
$ErrorActionPreference='Stop'
if (-not $RunOwnedProof -or $args.Count -ne 0 -or -not $IsWindows) { throw 'Exact owned-tool proof approval required' }
. "$PSScriptRoot/source-policy-tool-observer-transport.ps1"
$checks=0;$groups=0;$stage='preflight';$run=[guid]::NewGuid().ToString('N')
$root=$null;$clean=$false;$failure=$false;$cancelAlive=$false;$snapshot=$null
$script:ToolProof=$null
$sourceFiles=@{worker="$PSScriptRoot/source-policy-tool-observer-worker.ps1";observer="$PSScriptRoot/source-policy-tool-observer-child.ps1";transport="$PSScriptRoot/source-policy-tool-observer-transport.ps1";controller=$PSCommandPath}
$sourceHashes=@{}
function Get-ToolHash([string]$Path) { return [Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([IO.File]::ReadAllBytes($Path))).ToLowerInvariant() }
function Assert-ToolProof([bool]$Condition) { if (-not $Condition) { throw 'tool-proof-assertion' };$script:checks++ }
function Assert-ToolWork { if ($script:ToolProof.Clock.ElapsedMilliseconds -ge 20000) { throw 'tool-work-deadline' } }
function Read-ToolReady($Child,[string]$State,[int]$Limit) {
    $expected='ADBT1|'+$run+'|'+$Child.Role+'|'+$Child.Id+'|'+$Child.Tick+'|'+$Child.Source+'|'+$State+"`n"
    Assert-ToolLine (Read-ToolLine $Child $Limit) $expected;Assert-ToolProof $true
}
function Start-ToolWorker {
    Assert-ToolWork
    $w=New-ToolProcess 'worker';$script:ToolProof.Worker=$w
    Read-ToolReady $w 'held' 20000;Assert-ToolIdentity $w $true;Assert-ToolProof $true
    return $w
}
try {
    $image=[Environment]::ProcessPath
    $expectedImage=[IO.Path]::GetFullPath([IO.Path]::Combine([Environment]::GetFolderPath('UserProfile'),'.cache/codex-runtimes/codex-primary-runtime/dependencies/native/powershell/pwsh.exe'))
    Assert-ToolProof ($image -ceq $expectedImage -and $PSVersionTable.PSVersion.ToString() -ceq '7.6.5' -and [Runtime.InteropServices.RuntimeInformation]::FrameworkDescription -ceq '.NET 10.0.11' -and [Environment]::Is64BitProcess -and [Environment]::Is64BitOperatingSystem)
    Assert-ToolProof ((Get-ToolHash $image) -ceq '362a356ce7f0940ec74f73a8fc2c990a2cc24a38a11c90bbd8eca947110ad139')
    foreach ($role in $sourceFiles.Keys) { Assert-ToolProof (([IO.FileInfo]::new($sourceFiles[$role])).Length -lt 32768);$sourceHashes[$role]=Get-ToolHash $sourceFiles[$role] }
    $parent=[IO.Path]::GetFullPath([IO.Path]::Combine($PSScriptRoot,'../../build'))
    Assert-ToolProof ([IO.Directory]::Exists($parent) -and (([IO.File]::GetAttributes($parent) -band [IO.FileAttributes]::ReparsePoint) -eq 0))
    $root=[IO.Path]::Combine($parent,'adbtool-'+$run)
    Assert-ToolProof (-not [IO.Directory]::Exists($root) -and [IO.Path]::GetDirectoryName($root) -ceq $parent)
    [void][IO.Directory]::CreateDirectory($root)
    foreach ($name in @('tmp','work')) { [void][IO.Directory]::CreateDirectory([IO.Path]::Combine($root,$name)) }
    $files=@{};$hashes=@{}
    foreach ($role in @('worker','observer')) { $files[$role]=[IO.Path]::Combine($root,$role+'.ps1');[IO.File]::Copy($sourceFiles[$role],$files[$role],$false);$hashes[$role]=Get-ToolHash $files[$role];Assert-ToolProof ($hashes[$role] -ceq $sourceHashes[$role]) }
    $canary=[IO.Path]::Combine($root,'canary.txt');[IO.File]::WriteAllText($canary,$run,[Text.UTF8Encoding]::new($false));$canaryHash=Get-ToolHash $canary
    $windows=[Environment]::GetFolderPath('Windows');$system=[Environment]::SystemDirectory
    $environment=@{SystemRoot=$windows;WINDIR=$windows;PATH=$system;TEMP=[IO.Path]::Combine($root,'tmp');TMP=[IO.Path]::Combine($root,'tmp');PSModulePath='';PSModuleAnalysisCachePath=[IO.Path]::Combine($root,'tmp/module-cache');POWERSHELL_TELEMETRY_OPTOUT='1';POWERSHELL_UPDATECHECK='Off';DOTNET_CLI_TELEMETRY_OPTOUT='1'}
    $script:ToolProof=[pscustomobject]@{Image=$image;Root=$root;Run=$run;Files=$files;Hashes=$hashes;Environment=$environment;Children=[Collections.Generic.List[object]]::new();Worker=$null;Clock=[Diagnostics.Stopwatch]::StartNew()}
    $s=$script:ToolProof
    $stage='positive-worker';$w=Start-ToolWorker
    $stage='positive-observer';Assert-ToolWork;$observerLimit=[Math]::Min(20000,[int]$s.Clock.ElapsedMilliseconds+2000)
    $o=New-ToolProcess 'observer';Read-ToolReady $o 'ready' $observerLimit
    $snapshot=Read-ToolSummary (Read-ToolLine $o $observerLimit) $w $o $run
    Complete-ToolProcess $o $observerLimit $true;Assert-ToolIdentity $w $true;Assert-ToolProof $true
    $catalog=[IO.Path]::Combine($root,'modules.txt');Assert-ToolProof ([IO.File]::Exists($catalog) -and ([IO.FileInfo]::new($catalog)).Length -le 131072 -and (Get-ToolHash $catalog) -ceq $snapshot.Digest)
    $stage='positive-release';Assert-ToolWork
    $ack='ADBT1-ACK|'+$run+'|'+$w.Id+'|'+$w.Tick+"`n"
    $write=$w.Process.StandardInput.WriteAsync($ack);$remaining=20000-[int]$s.Clock.ElapsedMilliseconds
    if ($remaining -le 0 -or -not $write.Wait($remaining)) { throw 'tool-ack-deadline' };$write.GetAwaiter().GetResult()
    $flush=$w.Process.StandardInput.FlushAsync();$remaining=20000-[int]$s.Clock.ElapsedMilliseconds
    if ($remaining -le 0 -or -not $flush.Wait($remaining)) { throw 'tool-ack-deadline' };$flush.GetAwaiter().GetResult()
    Read-ToolReady $w 'released' 20000;Complete-ToolProcess $w 20000 $true;Assert-ToolProof ($w.Process.HasExited -and $o.Process.HasExited);$groups++
    $stage='timeout-worker';$w=Start-ToolWorker
    $stage='timeout-observer';Assert-ToolWork;$observerLimit=[Math]::Min(20000,[int]$s.Clock.ElapsedMilliseconds+2000)
    $o=New-ToolProcess 'observer' -Hold;Read-ToolReady $o 'ready' $observerLimit
    $caught=$false
    try { [void](Read-ToolLine $o $observerLimit) } catch { if ($_.Exception.Message -cne 'tool-deadline') { throw };$caught=$true }
    Assert-ToolProof $caught;Assert-ToolIdentity $w $true;Assert-ToolIdentity $o $true;Assert-ToolProof $true
    Stop-ToolProcess $o ([Math]::Min(25000,[int]$s.Clock.ElapsedMilliseconds+5000));Stop-ToolProcess $w ([Math]::Min(25000,[int]$s.Clock.ElapsedMilliseconds+5000))
    Assert-ToolProof ($w.Process.HasExited -and $o.Process.HasExited);$groups++
    $stage='cancel-worker';$w=Start-ToolWorker
    $cancel=[Threading.CancellationTokenSource]::new(50)
    try { $caught=$false;try {$w.Process.WaitForExitAsync($cancel.Token).GetAwaiter().GetResult()} catch {if ($_.Exception -is [OperationCanceledException] -or $_.Exception.InnerException -is [OperationCanceledException]) {$caught=$true} else {throw}};Assert-ToolProof $caught } finally { $cancel.Dispose() }
    Assert-ToolIdentity $w $true;$cancelAlive=-not $w.Process.HasExited;Assert-ToolProof $cancelAlive
    Stop-ToolProcess $w ([Math]::Min(25000,[int]$s.Clock.ElapsedMilliseconds+5000));Assert-ToolProof $w.Process.HasExited;$groups++
    $stage='reconcile'
    foreach ($child in $s.Children) { Assert-ToolIdentity $child $false;Assert-ToolProof ($child.Process.HasExited -and $null -eq $child.OutRead -and $null -eq $child.ErrRead -and $child.Out.Length -eq 0 -and $child.ErrBytes -eq 0) }
    Assert-ToolProof ((Get-ToolHash $canary) -ceq $canaryHash)
    foreach ($role in $sourceFiles.Keys) { Assert-ToolProof ((Get-ToolHash $sourceFiles[$role]) -ceq $sourceHashes[$role]) }
    Assert-ToolProof ((Get-ToolHash $image) -ceq '362a356ce7f0940ec74f73a8fc2c990a2cc24a38a11c90bbd8eca947110ad139')
    foreach ($role in @('worker','observer')) { Assert-ToolProof ((Get-ToolHash $files[$role]) -ceq $hashes[$role]) }
    Assert-ToolProof ($s.Clock.ElapsedMilliseconds -lt 25000)
} catch {
    $failure=$true
    $reason=if ($_.Exception.Message -cmatch '\Atool-[a-z-]+\z') { $_.Exception.Message } else { 'api-refused' }
    [Console]::Error.WriteLine('tool-proof failure stage:'+$stage+' reason:'+$reason+' checks:'+$checks)
} finally {
    $allExited=$true
    if ($null -ne $script:ToolProof) {
        foreach ($child in $script:ToolProof.Children) {
            try { Stop-ToolProcess $child 25000;Assert-ToolIdentity $child $false;if (-not $child.Process.HasExited) { throw 'not exited' } }
            catch { $allExited=$false }
        }
    }
    if ($null -eq $root -or -not [IO.Directory]::Exists($root)) { $clean=$true }
    if ($null -ne $root -and [IO.Directory]::Exists($root) -and $allExited) {
        try {
            # Validate exact absolute scope and every entry before any owned deletion.
            if ([IO.Path]::GetFullPath($root) -cne [IO.Path]::Combine($parent,'adbtool-'+$run) -or ([IO.File]::GetAttributes($root) -band [IO.FileAttributes]::ReparsePoint) -ne 0) { throw 'cleanup scope refused' }
            $entries=@([IO.Directory]::GetFileSystemEntries($root))
            foreach ($entry in $entries) {
                $name=[IO.Path]::GetFileName($entry)
                if ($name -cnotin @('worker.ps1','observer.ps1','canary.txt','modules.txt','tmp','work') -or ([IO.File]::GetAttributes($entry) -band [IO.FileAttributes]::ReparsePoint) -ne 0) { throw 'unknown owned entry' }
                if ([IO.Directory]::Exists($entry) -and [IO.Directory]::GetFileSystemEntries($entry).Length -ne 0) { throw 'nonempty owned scratch directory' }
            }
            foreach ($entry in $entries) { if ([IO.Directory]::Exists($entry)) { [IO.Directory]::Delete($entry,$false) } else { [IO.File]::Delete($entry) } }
            [IO.Directory]::Delete($root,$false);$clean=-not [IO.Directory]::Exists($root)
        } catch { $clean=$false }
    }
    if ($null -ne $script:ToolProof) { foreach ($child in $script:ToolProof.Children) { if ($allExited) { $child.Process.StandardInput.Dispose();$child.Process.StandardOutput.Dispose();$child.Process.StandardError.Dispose();$child.Process.Dispose() } } }
    if (-not $clean) { [Console]::Error.WriteLine('tool-proof-retained run:'+$run);$failure=$true }
}
if ($null -ne $script:ToolProof -and $script:ToolProof.Clock.ElapsedMilliseconds -ge 25000) { [Console]::Error.WriteLine('tool-proof-cleanup-deadline');$failure=$true }
if ($failure) {
    if ($null -ne $script:ToolProof) { foreach ($child in $script:ToolProof.Children) { if ($null -ne $child.ErrorFact) { $fact=$child.ErrorFact;[Console]::Error.WriteLine('tool-proof observer_phase:'+$fact.Phase+' observer_reason:'+$fact.Reason+' rows:'+$fact.Rows+' row:'+$fact.Index+' mask:'+$fact.Mask) } } }
    [Console]::Error.WriteLine('tool-proof failure_cleanup_confirmed:'+[int]($clean -and $allExited)+' fixtures_absent:'+[int]$clean+' owned_absence_confirmed:'+[int]$allExited);exit 1
}
[Console]::Out.WriteLine('tool-proof checks:'+$checks+' groups:'+$groups+' modules:'+$snapshot.Count+' image_bytes:'+$snapshot.Bytes+' max_image_bytes:'+$snapshot.Max+' catalog_sha256:'+$snapshot.Digest+' elapsed_ms:'+$script:ToolProof.Clock.ElapsedMilliseconds+' cancellation_not_exit:'+[int]$cancelAlive+' fixtures:0 owned_processes:0 runtime_load_authorized:0')
