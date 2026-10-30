# Extract actual private definitions only; never execute the observer entry point.
$ErrorActionPreference='Stop'
. "$PSScriptRoot/../../scripts/manual/source-policy-tool-observer-transport.ps1"
$checks=0;$definitions=@{};$originalBytes=@{}
function Assert-SnapshotUnit([bool]$Condition) { if (-not $Condition) { throw 'snapshot-unit-failed' };$script:checks++ }
function Assert-SnapshotRefused([scriptblock]$Action) { $caught=$false;try { [void](& $Action) } catch { $caught=$true };Assert-SnapshotUnit $caught }
foreach ($file in @('child','transport')) {
    $path="$PSScriptRoot/../../scripts/manual/source-policy-tool-observer-$file.ps1";$originalBytes[$path]=[IO.File]::ReadAllBytes($path)
    $tokens=$null;$errors=$null;$tree=[Management.Automation.Language.Parser]::ParseFile($path,[ref]$tokens,[ref]$errors)
    Assert-SnapshotUnit ($errors.Count -eq 0)
    foreach ($name in @('Get-ToolSnapshot','Add-ToolCatalogRow','Assert-ToolSnapshotPair','Read-ToolSummary','Assert-ToolCatalogReceipt','Complete-ToolProcess')) {
        $node=$tree.Find({param($n) $n -is [Management.Automation.Language.FunctionDefinitionAst] -and $n.Name -ceq $name},$true)
        if ($null -ne $node) { $definitions[$name]=$node.Extent.Text;. ([scriptblock]::Create($node.Extent.Text)) }
    }
}
class SnapshotUnitCollection : System.Collections.IEnumerable {
    [int]$Count
    [object[]]$Rows
    SnapshotUnitCollection([int]$count,[object[]]$rows) { $this.Count=$count;$this.Rows=$rows }
    [System.Collections.IEnumerator] GetEnumerator() { return $this.Rows.GetEnumerator() }
}
$script:Image='C:\fixed\pwsh.exe';$script:TargetTick=638000000000000000L
function New-SnapshotUnitRows([int]$Count) {
    $items=[Collections.Generic.List[object]]::new()
    for ($i=0;$i -lt $Count;$i++) {
        $name=if ($i -eq 0) { 'pwsh.exe' } elseif ($i -eq 1) { 'kernel32.dll' } else { 'module'+$i+'.dll' }
        $items.Add([pscustomobject]@{ModuleName=$name;FileName=('C:\fixed\'+$name);ModuleMemorySize=1024})
    }
    return ,$items.ToArray()
}
function Invoke-UnitSnapshot([int]$Count,[object[]]$Rows) {
    $script:target=[pscustomobject]@{HasExited=$false;StartTime=[DateTime]::new($script:TargetTick,[DateTimeKind]::Utc);MainModule=[pscustomobject]@{FileName=$script:Image};Modules=[SnapshotUnitCollection]::new($Count,$Rows)}
    $script:target | Add-Member -MemberType ScriptMethod -Name Refresh -Value {}
    return Get-ToolSnapshot
}
function Assert-RowRefusal([int]$Count,[object[]]$Rows,[string]$Reason) {
    Assert-SnapshotRefused { Invoke-UnitSnapshot $Count $Rows };Assert-SnapshotUnit ($script:DiagReason -ceq $Reason)
}
function Assert-SnapshotRowsContract {
    foreach ($n in @(2,128,129,135)) {
        $r=Invoke-UnitSnapshot $n (New-SnapshotUnitRows $n)
        Assert-SnapshotUnit ($null -ne $r -and $r.Count -eq $n -and $r.Bytes -eq 1024*$n -and $r.Max -eq 1024 -and [Text.Encoding]::UTF8.GetByteCount($r.Text) -le 131072)
    }
    foreach ($n in @(0,1,136)) { Assert-RowRefusal $n (New-SnapshotUnitRows 2) 'module-count';Assert-SnapshotUnit ($script:DiagIndex -eq 0) }
    $unread=[pscustomobject]@{};$unread | Add-Member -MemberType ScriptProperty -Name ModuleName -Value { throw 'protected-extra-row-read' }
    Assert-RowRefusal 136 @($unread) 'module-count';Assert-SnapshotUnit ($script:DiagIndex -eq 0 -and $script:DiagRows -eq 136)
    $rows=New-SnapshotUnitRows 135;$rows+=$unread
    Assert-RowRefusal 135 $rows 'module-count';Assert-SnapshotUnit ($script:DiagIndex -eq 0 -and $script:DiagRows -eq 136)
    Assert-RowRefusal 3 (New-SnapshotUnitRows 2) 'module-count'
    Assert-RowRefusal 2 (New-SnapshotUnitRows 3) 'module-count'
    foreach ($pair in @(@('ModuleName','bad|name.dll','module-name'),@('FileName','relative.dll','module-path'),@('FileName',("C:\bad`npath"),'module-path'),@('ModuleMemorySize',0,'module-size'),@('ModuleMemorySize',33554433,'module-size'))) {
        $rows=New-SnapshotUnitRows 2;$rows[0].($pair[0])=$pair[1];Assert-RowRefusal 2 $rows $pair[2]
    }
    $rows=New-SnapshotUnitRows 2;$rows[1].ModuleName='PWSH.EXE';Assert-RowRefusal 2 $rows 'module-duplicate'
    $rows=New-SnapshotUnitRows 2;$rows[0].ModuleName='other.exe';Assert-RowRefusal 2 $rows 'module-required'
    $rows=New-SnapshotUnitRows 9;foreach ($row in $rows) { $row.ModuleMemorySize=33554432 };Assert-RowRefusal 9 $rows 'module-total'
    $rows=New-SnapshotUnitRows 8;foreach ($row in $rows) { $row.ModuleMemorySize=33554432 };$r=Invoke-UnitSnapshot 8 $rows;Assert-SnapshotUnit ($r.Bytes -eq 268435456)
    $rows=New-SnapshotUnitRows 2;$rows[0].FileName='C:\'+([string][char]0x00e9)*20+'\pwsh.exe';$r=Invoke-UnitSnapshot 2 $rows
    $hex=[Convert]::ToHexString([Text.Encoding]::UTF8.GetBytes($rows[0].FileName));Assert-SnapshotUnit ($r.Text.Contains('|'+$hex+'|'))
    $rows=New-SnapshotUnitRows 135;foreach ($row in $rows) { $row.FileName='C:\'+([string][char]0x00e9)*237 };Assert-RowRefusal 135 $rows 'catalog-bound'
}
function Assert-CatalogBudgetContract {
    $rows=[Collections.Generic.SortedDictionary[string,string]]::new([StringComparer]::OrdinalIgnoreCase);$used=0
    $row=('x'*131069)+[char]0x00e9
    Add-ToolCatalogRow $rows 'exact' $row ([ref]$used);Assert-SnapshotUnit ($used -eq 131072 -and $rows.Count -eq 1)
    Assert-SnapshotRefused { Add-ToolCatalogRow $rows 'extra' 'x' ([ref]$used) };Assert-SnapshotUnit ($rows.Count -eq 1 -and $used -eq 131072)
    $empty=[Collections.Generic.SortedDictionary[string,string]]::new([StringComparer]::OrdinalIgnoreCase);$bytes=0
    Assert-SnapshotRefused { Add-ToolCatalogRow $empty 'over' ($row+'x') ([ref]$bytes) };Assert-SnapshotUnit ($empty.Count -eq 0 -and $bytes -eq 0)
    Assert-SnapshotPairContract
}
function Assert-SnapshotPairContract {
    $first=Invoke-UnitSnapshot 2 (New-SnapshotUnitRows 2);$second=Invoke-UnitSnapshot 2 (New-SnapshotUnitRows 2)
    Assert-ToolSnapshotPair $first $second;Assert-SnapshotUnit $true
    $second.Text+='x';Assert-SnapshotRefused { Assert-ToolSnapshotPair $first $second };Assert-SnapshotUnit ($script:DiagReason -ceq 'snapshots-changed')
}
$run='a'*32;$source='b'*64
$worker=[pscustomobject]@{Id=101;Tick=123L};$observer=[pscustomobject]@{Id=102;Tick=124L;Source=$source;Role='observer';Run=$run;TargetId=101;TargetTick=123L}
function Assert-SnapshotSummaryContract {
    foreach ($n in @(2,128,129,135)) {
        $line='ADBTS1|'+$run+'|observer|102|124|'+$source+'|modules|101|123|'+$n+'|'+(1024*$n)+'|1024|'+('c'*64)+"|stable`n"
        $r=Read-ToolSummary $line $worker $observer $run;Assert-SnapshotUnit ($null -ne $r -and $r.Count -eq $n)
        foreach ($tag in @('ADBT1','ADBTD1','ADBTC1','ADBMP1')) { Assert-SnapshotRefused { Read-ToolSummary ($line.Replace('ADBTS1',$tag)) $worker $observer $run } }
        Assert-SnapshotRefused { Read-ToolSummary ($line.Replace('|'+$n+'|','|136|')) $worker $observer $run }
        Assert-SnapshotRefused { Read-ToolSummary ($line.Replace('|123|','|125|')) $worker $observer $run }
    }
    $digest=[Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([Text.Encoding]::UTF8.GetBytes("catalog`n"))).ToLowerInvariant()
    $snapshot=[pscustomobject]@{Digest=$digest}
    Assert-ToolCatalogReceipt $snapshot 131072L $digest;Assert-SnapshotUnit $true
    foreach ($length in @(0L,131073L,'100',100)) { Assert-SnapshotRefused { Assert-ToolCatalogReceipt $snapshot $length $digest } }
    Assert-SnapshotRefused { Assert-ToolCatalogReceipt $snapshot 100L ('d'*64) }
}
function New-UnitTerminal {
    $p=[pscustomobject]@{Id=101;StartTime=[DateTime]::new($script:TargetTick,[DateTimeKind]::Utc);Handle=[IntPtr]::new(1);HasExited=$true;ExitCode=0;Killed=$false}
    $p | Add-Member -MemberType ScriptMethod -Name Kill -Value {$this.Killed=$true;$this.HasExited=$true}
    return [pscustomobject]@{Process=$p;Id=101;Tick=$script:TargetTick;Handle=[IntPtr]::new(1);Out='';OutBytes=0;ErrBytes=0;OutRead=$null;ErrRead=$null}
}
function Assert-SnapshotTerminalContract {
    $script:ToolProof=[pscustomobject]@{Clock=[pscustomobject]@{ElapsedMilliseconds=0}}
    $c=New-UnitTerminal;Complete-ToolProcess $c 1 $true;Assert-SnapshotUnit $true
    foreach ($kind in @('tail','stderr','exit')) {
        $c=New-UnitTerminal
        switch ($kind) { 'tail' {$c.Out="extra`n"} 'stderr' {$c.ErrBytes=1} 'exit' {$c.Process.ExitCode=1} }
        Assert-SnapshotRefused { Complete-ToolProcess $c 1 $true }
    }
    $c=New-UnitTerminal;$c.Out="first`nsecond`n";$first=Read-ToolLine $c 1;Assert-SnapshotUnit ($first -ceq "first`n")
    Assert-SnapshotRefused { Complete-ToolProcess $c 1 $true }
    $c=New-UnitTerminal;$c.Handle=[IntPtr]::new(2);Assert-SnapshotRefused { Complete-ToolProcess $c 1 $true }
    $c=New-UnitTerminal;Assert-SnapshotRefused { Complete-ToolProcess $c 0 $true }
    $c=New-UnitTerminal;$c.ErrBytes=1;Assert-SnapshotRefused { Read-ToolLine $c 1 }
    $c=New-UnitTerminal;Assert-SnapshotRefused { Read-ToolLine $c 1 }
    $c=New-UnitTerminal;$c.Process.HasExited=$false;Stop-ToolProcess $c 1;Assert-SnapshotUnit ($c.Process.HasExited -and $c.Process.Killed)
}
Assert-SnapshotRowsContract;Assert-CatalogBudgetContract;Assert-SnapshotSummaryContract;Assert-SnapshotTerminalContract
$controls=@(
    @('Get-ToolSnapshot','$modules.Count -gt 135','$false','rows'),
    @('Get-ToolSnapshot','$script:DiagIndex -ge 135','$false','rows'),
    @('Get-ToolSnapshot','$rows.Count -ne $modules.Count','$false','rows'),
    @('Get-ToolSnapshot','return [pscustomobject]@{Text=$text;Count=$rows.Count;Bytes=$total;Max=$max}','return $null','rows'),
    @('Add-ToolCatalogRow','$length -gt 131072-$Used.Value','$false','catalog'),
    @('Add-ToolCatalogRow','GetByteCount($Row)+1','GetByteCount($Row)','catalog'),
    @('Assert-ToolSnapshotPair','$first.Text -cne $second.Text','$false','catalog'),
    @('Read-ToolSummary','$count -gt 135','$false','summary'),
    @('Read-ToolSummary','-not $Line.StartsWith($prefix,[StringComparison]::Ordinal)','$false','summary'),
    @('Assert-ToolCatalogReceipt','$Snapshot.Digest -cne $Digest','$false','summary'),
    @('Complete-ToolProcess','$Clean -and ($Child.Out.Length -ne 0 -or $Child.ErrBytes -ne 0 -or $Child.Process.ExitCode -ne 0)','$false','terminal')
)
$killed=0
foreach ($edit in $controls) {
    $original=$definitions[$edit[0]];if (($original.Split($edit[1],[StringSplitOptions]::None)).Count -ne 2) { throw 'snapshot-control-locator-refused' }
    $caught=$false
    try {
        . ([scriptblock]::Create($original.Replace($edit[1],$edit[2])))
        try { switch ($edit[3]) { 'rows' {Assert-SnapshotRowsContract} 'catalog' {Assert-CatalogBudgetContract} 'summary' {Assert-SnapshotSummaryContract} 'terminal' {Assert-SnapshotTerminalContract} } }
        catch { if ($_.Exception.Message -cne 'snapshot-unit-failed') { throw };$caught=$true }
    } finally { . ([scriptblock]::Create($original)) }
    Assert-SnapshotUnit $caught;$killed++
}
Assert-SnapshotRowsContract;Assert-CatalogBudgetContract;Assert-SnapshotSummaryContract;Assert-SnapshotTerminalContract
foreach ($path in $originalBytes.Keys) { Assert-SnapshotUnit ([Convert]::ToHexString([Security.Cryptography.SHA256]::HashData($originalBytes[$path])) -ceq [Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([IO.File]::ReadAllBytes($path)))) }
[Console]::Out.WriteLine('tool-snapshot-unit checks:'+$checks+' killed_controls:'+$killed+' processes:0 fixtures:0')
