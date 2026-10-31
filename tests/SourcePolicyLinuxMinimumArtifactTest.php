<?php
use PHPUnit\Framework\TestCase;

/** Trusted stdlib analysis only. Does not authorize or invoke acquisition. */
final class SourcePolicyLinuxMinimumArtifactTest extends TestCase {
	public function test_offline_static_reader_transport_and_owned_cleanup_contracts() {
		$root=dirname(__DIR__);$python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
		$this->assertFileExists($python,'Use the configured existing trusted Python; no install or skip.');
		$profiles=require $root.'/scripts/manual/source-policy-supported-core-pins.php';$pins=array();
		foreach($profiles['6.0']['pins'] as $name=>$hash)$pins['wp-includes/'.$name]=$hash;
		$process=proc_open(array($python,'-I','-B',__DIR__.'/support/source_policy_linux_minimum_static_test.py','--offline-static-unit'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fwrite($pipes[0],json_encode($pins));fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),substr($err,0,4000));$this->assertSame('',$err);
		$this->assertSame(array('tests'=>14,'failures'=>0,'errors'=>0,'network_calls'=>0),json_decode($out,true));
	}
	public function test_acquisition_is_explicit_and_runtime_imports_unchanged() {
		$root=dirname(__DIR__);$source=file_get_contents($root.'/scripts/manual/source-policy-linux-minimum-acquire.py');
		$this->assertStringContainsString("sys.argv[1:] != ['--approved-static-artifacts']",$source);
		foreach(array('subprocess','extractall(','os.system','pickle','ctypes') as $forbidden)$this->assertStringNotContainsString($forbidden,$source);
		$this->assertStringNotContainsString('linux-minimum-acquire',file_get_contents($root.'/alynt-drime-backups-dashboard.php'));
	}
}
