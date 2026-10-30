# Approved one-attempt count calibration only; never a full snapshot or runtime admission.
param([switch]$RunOwnedCountCalibration)
$ErrorActionPreference='Stop'
if (-not $RunOwnedCountCalibration -or $args.Count -ne 0 -or -not $IsWindows) { throw 'Exact owned count calibration approval required' }
. "$PSScriptRoot/source-policy-tool-observer-transport.ps1"
. "$PSScriptRoot/source-policy-tool-count-receipt.ps1"
$checks=0;$stage='preflight';$run=[guid]::NewGuid().ToString('N')
$root=$null;$clean=$false;$failure=$false;$observerDone=$false;$workerDone=$false;$readbacks=$false;$receipt=$null
$script:ToolProof=$null
$sourceFiles=@{worker="$PSScriptRoot/source-policy-tool-observer-worker.ps1";observer="$PSScriptRoot/source-policy-tool-count-child.ps1";transport="$PSScriptRoot/source-policy-tool-observer-transport.ps1";receipt="$PSScriptRoot/source-policy-tool-count-receipt.ps1";controller=$PSCommandPath}
$sourceHashes=@{}
function Get-ToolHash([string]$Path) { return [Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([IO.File]::ReadAllBytes($Path))).ToLowerInvariant() }
function Assert-ToolProof([bool]$Condition) { if (-not $Condition) { throw 'tool-proof-assertion' };$script:checks++ }
function Assert-CountWork { if ($script:ToolProof.Clock.ElapsedMilliseconds -ge 20000) { throw 'tool-work-deadline' } }
function Read-CountReady($Child,[string]$State,[int]$Limit) {
    $expected='ADBT1|'+$run+'|'+$Child.Role+'|'+$Child.Id+'|'+$Child.Tick+'|'+$Child.Source+'|'+$State+"`n"
    Assert-ToolLine (Read-ToolLine $Child $Limit) $expected;Assert-ToolProof $true
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
    $stage='count-worker';Assert-CountWork;$w=New-ToolProcess 'worker';$s.Worker=$w
    Read-CountReady $w 'held' 20000;Assert-ToolIdentity $w $true;Assert-ToolProof $true
    $stage='count-observer';Assert-CountWork;$observerLimit=[Math]::Min(20000,[int]$s.Clock.ElapsedMilliseconds+2000)
    $o=New-ToolProcess 'observer';Read-CountReady $o 'ready' $observerLimit
    $receipt=Read-ToolCountReceipt (Read-ToolLine $o $observerLimit) $w $o $run
    Complete-ToolProcess $o $observerLimit $true;Assert-ToolIdentity $w $true;Assert-ToolProof ($s.Children.Count -eq 2);$observerDone=$true
    $stage='count-release';Assert-CountWork
    $ack='ADBT1-ACK|'+$run+'|'+$w.Id+'|'+$w.Tick+"`n"
    $write=$w.Process.StandardInput.WriteAsync($ack);$remaining=20000-[int]$s.Clock.ElapsedMilliseconds
    if ($remaining -le 0 -or -not $write.Wait($remaining)) { throw 'tool-ack-deadline' };$write.GetAwaiter().GetResult()
    $flush=$w.Process.StandardInput.FlushAsync();$remaining=20000-[int]$s.Clock.ElapsedMilliseconds
    if ($remaining -le 0 -or -not $flush.Wait($remaining)) { throw 'tool-ack-deadline' };$flush.GetAwaiter().GetResult()
    Read-CountReady $w 'released' 20000;Complete-ToolProcess $w 20000 $true;$workerDone=$true
    $stage='count-reconcile'
    foreach ($child in $s.Children) { Assert-ToolIdentity $child $false;Assert-ToolProof ($child.Process.HasExited -and $null -eq $child.OutRead -and $null -eq $child.ErrRead -and $child.Out.Length -eq 0 -and $child.ErrBytes -eq 0) }
    Assert-ToolProof ((Get-ToolHash $canary) -ceq $canaryHash)
    foreach ($role in $sourceFiles.Keys) { Assert-ToolProof ((Get-ToolHash $sourceFiles[$role]) -ceq $sourceHashes[$role]) }
    Assert-ToolProof ((Get-ToolHash $image) -ceq '362a356ce7f0940ec74f73a8fc2c990a2cc24a38a11c90bbd8eca947110ad139')
    foreach ($role in @('worker','observer')) { Assert-ToolProof ((Get-ToolHash $files[$role]) -ceq $hashes[$role]) }
    Assert-ToolProof ($s.Clock.ElapsedMilliseconds -lt 20000);$readbacks=$true
} catch {
    $failure=$true
    $reason=if ($_.Exception.Message -cmatch '\Atool-[a-z-]+\z') { $_.Exception.Message } else { 'api-refused' }
    [Console]::Error.WriteLine('tool-count failure stage:'+$stage+' reason:'+$reason+' checks:'+$checks)
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
                if ($name -cnotin @('worker.ps1','observer.ps1','canary.txt','tmp','work') -or ([IO.File]::GetAttributes($entry) -band [IO.FileAttributes]::ReparsePoint) -ne 0) { throw 'unknown owned entry' }
                if ([IO.Directory]::Exists($entry) -and [IO.Directory]::GetFileSystemEntries($entry).Length -ne 0) { throw 'nonempty owned scratch directory' }
            }
            foreach ($entry in $entries) { if ([IO.Directory]::Exists($entry)) { [IO.Directory]::Delete($entry,$false) } else { [IO.File]::Delete($entry) } }
            [IO.Directory]::Delete($root,$false);$clean=-not [IO.Directory]::Exists($root)
        } catch { $clean=$false }
    }
    if ($null -ne $script:ToolProof) { foreach ($child in $script:ToolProof.Children) { if ($allExited) { $child.Process.StandardInput.Dispose();$child.Process.StandardOutput.Dispose();$child.Process.StandardError.Dispose();$child.Process.Dispose() } } }
    if (-not $clean) { [Console]::Error.WriteLine('tool-count-retained run:'+$run);$failure=$true }
}
if ($null -ne $script:ToolProof -and $script:ToolProof.Clock.ElapsedMilliseconds -ge 25000) { [Console]::Error.WriteLine('tool-count-cleanup-deadline');$failure=$true }
if ($failure) { [Console]::Error.WriteLine('tool-count failure_cleanup_confirmed:'+[int]($clean -and $allExited)+' fixtures_absent:'+[int]$clean+' owned_absence_confirmed:'+[int]$allExited);exit 1 }
$elapsed=[int]$script:ToolProof.Clock.ElapsedMilliseconds
$result=Confirm-ToolCountCompletion $receipt $observerDone $workerDone $readbacks ($clean -and $allExited) $elapsed
[Console]::Out.WriteLine('tool-count checks:'+$checks+' count_only_observed:'+[int]$result.CountOnlyObserved+' modules:'+$result.Count+' elapsed_ms:'+$elapsed+' source_canary_readbacks:1 fixtures:0 owned_processes:0 full_snapshot:0 observer_qualified:0 runtime_load_authorized:0 native_qualified:0')
