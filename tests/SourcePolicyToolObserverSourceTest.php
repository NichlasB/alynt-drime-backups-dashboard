<?php
use PHPUnit\Framework\TestCase;

/** Static source regressions supplement, never replace, owned Windows evidence. */
final class SourcePolicyToolObserverSourceTest extends TestCase {
	private function source($name){return file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-tool-observer-'.$name.'.ps1');}
	public function test_dispatch_is_explicit_fixed_and_sterile_without_native_or_foreign_cleanup() {
		$c=$this->source('owned-proof');$p=$this->source('transport');
		foreach(array('param([switch]$RunOwnedProof)','-not $RunOwnedProof','$args.Count -ne 0','-not $IsWindows',"'7.6.5'","'.NET 10.0.11'",'362a356ce7f0940ec74f73a8fc2c990a2cc24a38a11c90bbd8eca947110ad139') as $text)$this->assertStringContainsString($text,$c);
		foreach(array('$info.Environment.Clear()','$info.FileName=$s.Image','$info.UseShellExecute=$false','$info.CreateNoWindow=$true',"'-NoProfile'","'-NonInteractive'",'$s.Children.Add($c)','$c.Handle=$p.Handle','$Child.Process.Kill()') as $text)$this->assertStringContainsString($text,$p);
		foreach(array($c,$p,$this->source('worker'),$this->source('child')) as $source)foreach(array('Invoke-Expression','Add-Type','Stop-Process','Get-CimInstance','Get-WmiObject','Kill($true)','::GetProcesses(','.php','MYSQL_PWD','wp-load','New-PSSession','Invoke-WebRequest','System.Net.Sockets') as $forbidden)$this->assertStringNotContainsString($forbidden,$source);
		$this->assertLessThan(strpos($p,'$c.Id=$p.Id'),strpos($p,'$s.Children.Add($c)'));
	}
	public function test_module_apis_are_isolated_and_identity_precedes_observation() {
		$c=$this->source('owned-proof');$p=$this->source('transport');$o=$this->source('child');
		foreach(array($c,$p) as $source){$this->assertStringNotContainsString('.Modules',$source);$this->assertStringNotContainsString('.MainModule',$source);$this->assertStringNotContainsString('::GetProcessById',$source);}
		foreach(array('::GetProcessById($TargetId)','$target.StartTime.ToUniversalTime().Ticks -ne $TargetTick','$target.MainModule.FileName -cne $Image','$target.Refresh()','$modules=$target.Modules','$first.Text -cne $second.Text','33554432','268435456','131072') as $text)$this->assertStringContainsString($text,$o);
		$this->assertLessThan(strpos($o,'$modules=$target.Modules'),strpos($o,'$target.StartTime.ToUniversalTime().Ticks -ne $TargetTick'));
		$this->assertStringContainsString('metadata',$o);
	}
	public function test_real_deadlines_own_absence_and_validated_nonrecursive_cleanup_remain_required() {
		$c=$this->source('owned-proof');$p=$this->source('transport');
		foreach(array('20000','2000','25000','5000','WaitForExitAsync($cancel.Token)','$cancelAlive=-not $w.Process.HasExited',"'tool-deadline'",'Assert-ToolIdentity $w $true','Complete-ToolProcess $o $observerLimit $true','[IO.Directory]::Delete($root,$false)','[IO.Path]::GetFullPath($root)','[IO.FileAttributes]::ReparsePoint',"'nonempty owned scratch directory'",'failure_cleanup_confirmed:') as $text)$this->assertStringContainsString($text,$c);
		foreach(array('$Child.Process.StartTime.ToUniversalTime().Ticks -ne $Child.Tick','$Child.Process.Handle -ne $Child.Handle','16384','4097','512','$null -eq $Child.OutRead -and $null -eq $Child.ErrRead','$Clean -and') as $text)$this->assertStringContainsString($text,$p);
		$this->assertStringNotContainsString('Remove-Item',$c);$this->assertStringNotContainsString('$true)',$c);
		$this->assertStringNotContainsString('source-policy-tool-observer',file_get_contents(dirname(__DIR__).'/alynt-drime-backups-dashboard.php'));
	}
	public function test_diagnostics_are_correlated_finite_eof_only_and_cannot_admit_stderr() {
		$p=$this->source('transport');$o=$this->source('child');$c=$this->source('owned-proof');
		foreach(array('function Read-ToolDiagnostic','function Add-ToolDiagnosticBytes',"'ADBTSD1|'",'$Child.TargetId','$Child.TargetTick','$f.Count -ne 13','$Line.Length -gt 512','$rows -gt 136','$index -gt 135','$mask -gt 15','$Child.ErrEof','$Child.ErrPrefix.Length -eq $Child.ErrBytes',"$".'Child.ErrPrefix=\'\';return',"if (\$Child.ErrBytes -gt 0) { throw 'tool-stderr-refused' }",'$Child.ErrBytes -ne 0') as $text)$this->assertStringContainsString($text,$p);
		foreach(array("'snapshot-one'","'snapshot-two'","'module-count'","'module-name'","'module-path'","'module-size'","'module-duplicate'",'$script:DiagMask=[int]$badName+2*[int]$badPath+4*[int]$badSize+8*[int]$duplicate','$badName -or $badPath -or $badSize -or $duplicate') as $text)$this->assertStringContainsString($text,$o);
		foreach(array('if (-not $badName) {','if (-not $badName -and -not $badPath) {','if (-not $badName -and -not $badPath -and -not $badSize) {') as $text)$this->assertStringContainsString($text,$o);
		foreach(array('Exception.Message','Exception.ToString','Format-List','Write-Error') as $text)$this->assertStringNotContainsString($text,$o);
		$this->assertStringContainsString('if ($failure) {',$c);$this->assertStringContainsString('observer_phase:',$c);$this->assertStringNotContainsString('ErrPrefix',$c);
	}
	public function test_fixed_snapshot_profile_is_versioned_and_bounds_retention_before_insertion() {
		$o=$this->source('child');$p=$this->source('transport');$c=$this->source('owned-proof');
		foreach(array("'ADBTS1|'","'ADBTSD1|'",'$modules.Count -gt 135','$script:DiagIndex -ge 135','$rows.Count -ne $modules.Count','function Add-ToolCatalogRow','$length -gt 131072-$Used.Value','Add-ToolCatalogRow $rows $name $row ([ref]$used)','function Assert-ToolSnapshotPair') as $text)$this->assertStringContainsString($text,$o);
		$this->assertLessThan(strpos($o,'$Rows.Add($Name,$Row)'),strpos($o,'$length -gt 131072-$Used.Value'));
		foreach(array("'ADBTS1|'",'$count -gt 135','function Assert-ToolCatalogReceipt') as $text)$this->assertStringContainsString($text,$p);
		foreach(array("'tool-snapshot-135-v1'","'ADBTS1'","'ADBT1'",'Assert-ToolCatalogReceipt $snapshot','$groups -eq 3 -and $s.Children.Count -eq 5') as $text)$this->assertStringContainsString($text,$c);
		$this->assertStringNotContainsString("'ADBTD1|'",$p);$this->assertStringNotContainsString("'ADBTD1|'",$o);
	}
}
