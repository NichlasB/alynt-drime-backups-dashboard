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
function New-UnitDiagnosticChild { return [pscustomobject]@{Role='observer';Run=$run;Id=102;Tick=124L;Source=$source;TargetId=101;TargetTick=123L;ErrBytes=0;ErrPrefix='';ErrInvalid=$false;ErrEof=$false;ErrorFact=$null} }
$diagChild=New-UnitDiagnosticChild
$diag='ADBTD1|'+$run+'|observer|102|124|'+$source+"|101|123|snapshot-one|module-size|3|2|4`n"
$fact=Read-ToolDiagnostic $diag $diagChild
Assert-Unit ($null -ne $fact -and $fact.Phase -ceq 'snapshot-one' -and $fact.Reason -ceq 'module-size' -and $fact.Rows -eq 3 -and $fact.Index -eq 2 -and $fact.Mask -eq 4)
function Assert-DiagnosticContract {
    $context=New-UnitDiagnosticChild
    Assert-Unit ($null -ne (Read-ToolDiagnostic $diag $context))
    $f=$diag.TrimEnd("`n").Split('|')
    foreach ($pair in @(@(0,'ADBT1'),@(1,('d'*32)),@(2,'worker'),@(3,'103'),@(4,'125'),@(5,('d'*64)),@(6,'103'),@(7,'125'),@(8,'unsafe'),@(9,'private-secret'),@(10,'130'),@(10,'03'),@(11,'129'),@(11,'4'),@(12,'20'),@(12,'0'))) {
        $bad=$f.Clone();$bad[$pair[0]]=$pair[1];Assert-Unit ($null -eq (Read-ToolDiagnostic ([string]::Join('|',$bad)+"`n") $context))
    }
    foreach ($bad in @($diag.TrimEnd("`n"),$diag.Replace("`n","`r`n"),($diag+"extra`n"),$diag.Replace('|snapshot-one|','|catalog|'))) { Assert-Unit ($null -eq (Read-ToolDiagnostic $bad $context)) }
    $unknown=$f.Clone();$unknown[8]='unsafe';$unknown[9]='api';$unknown[10]='0';$unknown[11]='0';$unknown[12]='0'
    Assert-Unit ($null -eq (Read-ToolDiagnostic ([string]::Join('|',$unknown)+"`n") $context))
    foreach ($pair in @(@('Role','worker'),@('Id','102'),@('Tick',124),@('Source',('d'*64)),@('Run',('d'*32)),@('TargetId',103),@('TargetTick',125L))) {
        $badContext=New-UnitDiagnosticChild;$badContext.($pair[0])=$pair[1];Assert-Unit ($null -eq (Read-ToolDiagnostic $diag $badContext))
    }
}
Assert-DiagnosticContract
foreach ($shape in @(@('lookup','api',0,0,0),@('identity','identity',0,0,0),@('snapshot-one','module-count',129,0,0),@('snapshot-two','module-duplicate',5,3,8),@('compare','snapshots-changed',3,3,0),@('hold','hold-released',0,0,0))) {
    $f=$diag.TrimEnd("`n").Split('|');for($i=0;$i -lt 5;$i++){$f[8+$i]=[string]$shape[$i]};Assert-Unit ($null -ne (Read-ToolDiagnostic ([string]::Join('|',$f)+"`n") (New-UnitDiagnosticChild)))
}
$diagBytes=[Text.Encoding]::ASCII.GetBytes($diag)
foreach ($chunkSize in @(1,3,17,1024)) {
    $c=New-UnitDiagnosticChild
    for ($offset=0;$offset -lt $diagBytes.Length;$offset+=$chunkSize) {
        $n=[Math]::Min($chunkSize,$diagBytes.Length-$offset);$chunk=[byte[]]$diagBytes[$offset..($offset+$n-1)]
        Add-ToolDiagnosticBytes $c $chunk $n $false;Assert-Unit ($null -eq $c.ErrorFact -and $c.ErrPrefix.Length -le 512)
    }
    Add-ToolDiagnosticBytes $c ([byte[]]::new(0)) 0 $true
    Assert-Unit ($null -ne $c.ErrorFact -and $c.ErrPrefix -ceq '' -and $c.ErrEof -and $c.ErrBytes -eq $diagBytes.Length)
    Add-ToolDiagnosticBytes $c ([byte[]]@(65)) 1 $false;Assert-Unit ($null -eq $c.ErrorFact -and $c.ErrInvalid -and $c.ErrPrefix -ceq '')
}
foreach ($badText in @($diag.TrimEnd("`n"),($diag+'private-secret'),($diag+"extra`n"),$diag.Replace("`n","`r`n"),("`e[31m"+$diag),('x'*4098))) {
    $c=New-UnitDiagnosticChild;$bytes=[Text.Encoding]::UTF8.GetBytes($badText)
    for($offset=0;$offset -lt $bytes.Length;$offset+=1024){$n=[Math]::Min(1024,$bytes.Length-$offset);Add-ToolDiagnosticBytes $c ([byte[]]$bytes[$offset..($offset+$n-1)]) $n $false}
    Add-ToolDiagnosticBytes $c ([byte[]]::new(0)) 0 $true;Assert-Unit ($null -eq $c.ErrorFact -and $c.ErrPrefix -ceq '' -and $c.ErrBytes -le 4097)
}
foreach ($n in @(-1,1025)) {$c=New-UnitDiagnosticChild;Add-ToolDiagnosticBytes $c ([byte[]]@(65)) $n $false;Assert-Unit ($c.ErrInvalid -and $c.ErrBytes -eq 4097 -and $null -eq $c.ErrorFact)}
# Mutate only the actual pure decoder definition in memory, never its disk source.
$transportFile="$PSScriptRoot/../../scripts/manual/source-policy-tool-observer-transport.ps1"
$diskBefore=[IO.File]::ReadAllBytes($transportFile);$parseTokens=$null;$parseErrors=$null
$ast=[Management.Automation.Language.Parser]::ParseFile($transportFile,[ref]$parseTokens,[ref]$parseErrors)
$definition=$ast.Find({param($node) $node -is [Management.Automation.Language.FunctionDefinitionAst] -and $node.Name -ceq 'Read-ToolDiagnostic'},$true).Extent.Text
$controls=@(
    @('-not $Line.StartsWith($prefix,[StringComparison]::Ordinal)','$false'),
    @("'scope','self','lookup','identity','snapshot-one','snapshot-two','compare','catalog','emit','finish','hold'","'scope','self','lookup','identity','snapshot-one','snapshot-two','compare','catalog','emit','finish','hold','unsafe'"),
    @('$rows -gt 129','$false'),@('$Child.Id -isnot [int]','$false'),@('$mask -gt 15','$false'),
    @('return [pscustomobject]@{Phase=$f[8];Reason=$f[9];Rows=$rows;Index=$index;Mask=$mask}','return $null')
)
$killed=0
foreach($edit in $controls){
    if(($definition.Split($edit[0],[StringSplitOptions]::None)).Count -ne 2){throw 'tool-control-locator-refused'}
    $mutant=$definition.Replace($edit[0],$edit[1]);$caught=$false
    try {. ([scriptblock]::Create($mutant));try {Assert-DiagnosticContract} catch {if($_.Exception.Message -cne 'tool-unit-failed'){throw};$caught=$true}}
    finally {. ([scriptblock]::Create($definition))}
    Assert-Unit $caught;$killed++
}
Assert-DiagnosticContract
Assert-Unit ([Convert]::ToHexString([Security.Cryptography.SHA256]::HashData($diskBefore)) -ceq [Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([IO.File]::ReadAllBytes($transportFile))))
[Console]::Out.WriteLine('tool-unit checks:'+$checks+' killed_controls:'+$killed+' processes:0 fixtures:0')
