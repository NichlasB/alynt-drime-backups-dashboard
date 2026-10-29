# Pure actual parser tests only. No process creation, host reads or fixture writes.
$ErrorActionPreference='Stop'
. "$PSScriptRoot/../../scripts/manual/source-policy-tool-observer-transport.ps1"
$checks=0
function Assert-Unit([bool]$Condition) { if (-not $Condition) { throw 'tool-unit-failed' };$script:checks++ }
function Assert-Refused([scriptblock]$Action) { $caught=$false;try { & $Action } catch { $caught=$true };Assert-Unit $caught }
$run='a'*32;$source='b'*64
$worker=[pscustomobject]@{Id=101;Tick=123L};$observer=[pscustomobject]@{Id=102;Tick=124L;Source=$source}
$prefix='ADBT1|'+$run+'|observer|102|124|'+$source+'|modules|101|123|'
$line=$prefix+'3|3072|1024|'+('c'*64)+"|stable`n"
$result=Read-ToolSummary $line $worker $observer $run
Assert-Unit ($result.Count -eq 3 -and $result.Bytes -eq 3072 -and $result.Max -eq 1024 -and $result.Digest -ceq ('c'*64))
Assert-ToolLine "fixed`n" "fixed`n";Assert-Unit $true
foreach ($bad in @('fixed',"fixed`r`n","Fixed`n","fixed`nextra`n",(('x'*513)+"`n"),("fixed`0`n"))) { Assert-Refused { Assert-ToolLine $bad "fixed`n" } }
$parts=$line.TrimEnd("`n").Split('|')
foreach ($pair in @(@(0,'ADBT2'),@(1,('d'*32)),@(2,'worker'),@(3,'103'),@(4,'125'),@(5,('d'*64)),@(6,'ready'),@(7,'103'),@(8,'125'),@(9,'1'),@(9,'129'),@(9,'03'),@(10,'268435457'),@(10,'0'),@(10,'1023'),@(10,'3073'),@(11,'33554433'),@(11,'0'),@(12,('C'*64)),@(13,'partial'))) {
    $bad=$parts.Clone();$bad[$pair[0]]=$pair[1];$record=[string]::Join('|',$bad)+"`n";Assert-Refused { Read-ToolSummary $record $worker $observer $run }
}
foreach ($bad in @($line.TrimEnd("`n"),($line.Replace("`n","`r`n")),($line+"x`n"),($line.Replace('|stable', '|stable|extra')))) { Assert-Refused { Read-ToolSummary $bad $worker $observer $run } }
[Console]::Out.WriteLine('tool-unit checks:'+$checks+' processes:0 fixtures:0')
