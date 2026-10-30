# Actual pure diagnostic/terminal tests. No processes, host lookup or fixture writes.
$ErrorActionPreference='Stop'
. "$PSScriptRoot/../../scripts/manual/source-policy-tool-count-receipt.ps1"
. "$PSScriptRoot/../../scripts/manual/source-policy-tool-observer-transport.ps1"
$checks=0
function Assert-CountUnit([bool]$Condition) { if (-not $Condition) { throw 'count-unit-assertion' };$script:checks++ }
function Assert-CountRefused([scriptblock]$Action) { $caught=$false;try { [void](& $Action) } catch { $caught=$true };Assert-CountUnit $caught }
$run='a'*32;$source='b'*64
function New-CountWorker { return [pscustomobject]@{Id=101;Tick=123L} }
function New-CountObserver { return [pscustomobject]@{Id=102;Tick=124L;Source=$source;Role='observer';Run=$run;TargetId=101;TargetTick=123L} }
$worker=New-CountWorker;$observer=New-CountObserver
$line='ADBTC1|'+$run+'|count-only|102|124|'+$source+"|101|123|129|not-admission`n"
function Assert-CountParserContract {
    $w=New-CountWorker;$o=New-CountObserver
    foreach ($n in @(2,128,129,2147483647)) {
        $text=$line.Replace('|129|','|'+$n+'|');$r=Read-ToolCountReceipt $text $w $o $run
        Assert-CountUnit ($null -ne $r -and $r.Count -eq $n -and $r.Count -is [int] -and -not $r.CountOnlyObserved -and -not $r.FullSnapshot -and -not $r.ObserverQualified -and -not $r.LoadAuthorized -and -not $r.RuntimeQualified -and -not $r.NativeQualified)
    }
    $f=$line.TrimEnd("`n").Split('|')
    foreach ($pair in @(@(0,'ADBT1'),@(0,'ADBTD1'),@(0,'ADBMP1'),@(1,('c'*32)),@(2,'modules'),@(2,'Count-only'),@(3,'103'),@(4,'125'),@(5,('c'*64)),@(6,'103'),@(7,'125'),@(8,'0'),@(8,'1'),@(8,'-1'),@(8,'+129'),@(8,'0129'),@(8,' 129'),@(8,'129 '),@(8,'1e2'),@(8,'1.0'),@(8,'2147483648'),@(8,'999999999999'),@(9,'admission'))) {
        $bad=$f.Clone();$bad[$pair[0]]=$pair[1];$record=[string]::Join('|',$bad)+"`n";Assert-CountRefused { Read-ToolCountReceipt $record $w $o $run }
    }
    foreach ($bad in @($null,129,$line.TrimEnd("`n"),$line.Replace("`n","`r`n"),($line+"extra`n"),($line+$line),$line.Replace('|not-admission','|extra|not-admission'),('x'*513),("`0"+$line))) { Assert-CountRefused { Read-ToolCountReceipt $bad $w $o $run } }
    foreach ($pair in @(@('Id','102'),@('Tick',124),@('Source',('B'*64)),@('Source',('c'*64)),@('Role','worker'),@('Run',('c'*32)),@('TargetId',103),@('TargetId','101'),@('TargetTick',125L),@('TargetTick',123))) {
        $bad=New-CountObserver;$bad.($pair[0])=$pair[1];Assert-CountRefused { Read-ToolCountReceipt $line $w $bad $run }
    }
    foreach ($pair in @(@('Id','101'),@('Id',0),@('Tick',123),@('Tick',0L))) { $bad=New-CountWorker;$bad.($pair[0])=$pair[1];Assert-CountRefused { Read-ToolCountReceipt $line $bad $o $run } }
    foreach ($bad in @($null,123,('A'*32),('a'*31))) { Assert-CountRefused { Read-ToolCountReceipt $line $w $o $bad } }
}
function Assert-CountTerminalContract {
    $r=Read-ToolCountReceipt $line (New-CountWorker) (New-CountObserver) $run
    foreach ($elapsed in @(0,24999)) {
        $done=Confirm-ToolCountCompletion $r $true $true $true $true $elapsed
        Assert-CountUnit ($done.Count -eq 129 -and $done.CountOnlyObserved -and -not $r.CountOnlyObserved -and -not $done.FullSnapshot -and -not $done.ObserverQualified -and -not $done.LoadAuthorized -and -not $done.RuntimeQualified -and -not $done.NativeQualified)
    }
    for ($index=0;$index -lt 4;$index++) {
        foreach ($bad in @($false,$null,1,'true')) {
            $facts=@($true,$true,$true,$true);$facts[$index]=$bad
            Assert-CountRefused { Confirm-ToolCountCompletion $r $facts[0] $facts[1] $facts[2] $facts[3] 100 }
        }
    }
    foreach ($bad in @(-1,25000,25001,'100',100L,$null)) { Assert-CountRefused { Confirm-ToolCountCompletion $r $true $true $true $true $bad } }
    foreach ($key in @('CountOnlyObserved','FullSnapshot','ObserverQualified','LoadAuthorized','RuntimeQualified','NativeQualified')) {
        $r.$key=$true;Assert-CountRefused { Confirm-ToolCountCompletion $r $true $true $true $true 100 };$r.$key=$false
        $r.$key=0;Assert-CountRefused { Confirm-ToolCountCompletion $r $true $true $true $true 100 };$r.$key=$false
    }
    foreach ($bad in @(0,1,'129',129L,$null)) { $r.Count=$bad;Assert-CountRefused { Confirm-ToolCountCompletion $r $true $true $true $true 100 } };$r.Count=129
    $r | Add-Member -NotePropertyName Extra -NotePropertyValue $false
    Assert-CountRefused { Confirm-ToolCountCompletion $r $true $true $true $true 100 }
}
Assert-CountParserContract;Assert-CountTerminalContract
# Count-only can never satisfy existing summary or error-fact decoders.
Assert-CountRefused { Read-ToolSummary $line $worker $observer $run }
Assert-CountUnit ($null -eq (Read-ToolDiagnostic $line $observer))
$file="$PSScriptRoot/../../scripts/manual/source-policy-tool-count-receipt.ps1"
$before=[IO.File]::ReadAllBytes($file);$tokens=$null;$errors=$null
$ast=[Management.Automation.Language.Parser]::ParseFile($file,[ref]$tokens,[ref]$errors)
Assert-CountUnit ($errors.Count -eq 0)
$parser=$ast.Find({param($node) $node -is [Management.Automation.Language.FunctionDefinitionAst] -and $node.Name -ceq 'Read-ToolCountReceipt'},$true).Extent.Text
$terminal=$ast.Find({param($node) $node -is [Management.Automation.Language.FunctionDefinitionAst] -and $node.Name -ceq 'Confirm-ToolCountCompletion'},$true).Extent.Text
$controls=@(
    @($parser,'-not $Line.StartsWith($prefix,[StringComparison]::Ordinal)','$false','parser'),
    @($parser,"`$f[9] -cne 'not-admission'",'$false','parser'),
    @($parser,'$count -lt 2','$false','parser'),
    @($parser,'$Observer.TargetId -ne $Worker.Id','$false','parser'),
    @($parser,'Count=$count','Count=0','parser'),
    @($parser,'return [pscustomobject]@{Count=$count;CountOnlyObserved=$false;FullSnapshot=$false;ObserverQualified=$false;LoadAuthorized=$false;RuntimeQualified=$false;NativeQualified=$false}','return $null','parser'),
    @($terminal,'$fact -isnot [bool] -or -not $fact','$false','terminal'),
    @($terminal,'$Elapsed -ge 25000','$false','terminal'),
    @($terminal,'$Receipt.$key -isnot [bool] -or $Receipt.$key','$false','terminal'),
    @($terminal,'CountOnlyObserved=$true','CountOnlyObserved=$false','terminal')
)
$killed=0
foreach ($edit in $controls) {
    if (($edit[0].Split($edit[1],[StringSplitOptions]::None)).Count -ne 2) { throw 'count-control-locator-refused' }
    $mutant=$edit[0].Replace($edit[1],$edit[2]);$caught=$false
    try {
        . ([scriptblock]::Create($mutant))
        try { if ($edit[3] -ceq 'parser') { Assert-CountParserContract } else { Assert-CountTerminalContract } }
        catch { if ($_.Exception.Message -cne 'count-unit-assertion') { throw };$caught=$true }
    } finally { . ([scriptblock]::Create($edit[0])) }
    Assert-CountUnit $caught;$killed++
}
Assert-CountParserContract;Assert-CountTerminalContract
Assert-CountUnit ([Convert]::ToHexString([Security.Cryptography.SHA256]::HashData($before)) -ceq [Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([IO.File]::ReadAllBytes($file))))
[Console]::Out.WriteLine('tool-count-unit checks:'+$checks+' killed_controls:'+$killed+' processes:0 fixtures:0')
