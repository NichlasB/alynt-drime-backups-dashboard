# Private pure diagnostic grammar. No IO, discovery, admission or execution.
function Read-ToolCountReceipt($Line,$Worker,$Observer,$Run) {
    if ($Line -isnot [string] -or $Run -isnot [string] -or $Run -cnotmatch '\A[a-f0-9]{32}\z' -or $Line.Length -gt 512 -or $Line -cnotmatch '\A[\x20-\x7e]+\n\z') { throw 'tool-count-frame-refused' }
    if ($Worker.Id -isnot [int] -or $Worker.Id -le 0 -or $Worker.Tick -isnot [long] -or $Worker.Tick -le 0 -or $Observer.Id -isnot [int] -or $Observer.Id -le 0 -or $Observer.Tick -isnot [long] -or $Observer.Tick -le 0 -or $Observer.Source -isnot [string] -or $Observer.Source -cnotmatch '\A[a-f0-9]{64}\z' -or $Observer.Role -cne 'observer' -or $Observer.Run -cne $Run -or $Observer.TargetId -isnot [int] -or $Observer.TargetTick -isnot [long] -or $Observer.TargetId -ne $Worker.Id -or $Observer.TargetTick -ne $Worker.Tick) { throw 'tool-count-context-refused' }
    $prefix='ADBTC1|'+$Run+'|count-only|'+$Observer.Id+'|'+$Observer.Tick+'|'+$Observer.Source+'|'+$Worker.Id+'|'+$Worker.Tick+'|'
    $f=$Line.TrimEnd("`n").Split('|')
    if (-not $Line.StartsWith($prefix,[StringComparison]::Ordinal) -or $f.Count -ne 10 -or $f[8] -cnotmatch '\A[1-9][0-9]{0,9}\z' -or $f[9] -cne 'not-admission') { throw 'tool-count-frame-refused' }
    $count=0
    if (-not [int]::TryParse($f[8],[Globalization.NumberStyles]::None,[Globalization.CultureInfo]::InvariantCulture,[ref]$count) -or $count -lt 2 -or $count -gt 2147483647) { throw 'tool-count-value-refused' }
    return [pscustomobject]@{Count=$count;CountOnlyObserved=$false;FullSnapshot=$false;ObserverQualified=$false;LoadAuthorized=$false;RuntimeQualified=$false;NativeQualified=$false}
}
function Confirm-ToolCountCompletion($Receipt,$ObserverDone,$WorkerDone,$Readbacks,$Clean,$Elapsed) {
    if ($null -eq $Receipt -or $Receipt.Count -isnot [int] -or $Receipt.Count -lt 2 -or $Receipt.Count -gt 2147483647 -or $Elapsed -isnot [int] -or $Elapsed -lt 0 -or $Elapsed -ge 25000) { throw 'tool-count-completion-refused' }
    foreach ($fact in @($ObserverDone,$WorkerDone,$Readbacks,$Clean)) { if ($fact -isnot [bool] -or -not $fact) { throw 'tool-count-completion-refused' } }
    $keys=@('Count','CountOnlyObserved','FullSnapshot','ObserverQualified','LoadAuthorized','RuntimeQualified','NativeQualified')
    if ($Receipt -isnot [pscustomobject] -or @($Receipt.PSObject.Properties).Count -ne $keys.Count) { throw 'tool-count-completion-refused' }
    foreach ($key in $keys[1..6]) { if ($Receipt.$key -isnot [bool] -or $Receipt.$key) { throw 'tool-count-completion-refused' } }
    return [pscustomobject]@{Count=$Receipt.Count;CountOnlyObserved=$true;FullSnapshot=$false;ObserverQualified=$false;LoadAuthorized=$false;RuntimeQualified=$false;NativeQualified=$false}
}
