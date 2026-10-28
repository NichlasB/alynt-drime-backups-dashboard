<?php
use PHPUnit\Framework\TestCase;

final class SourcePolicyAmbientObserverBindingTest extends TestCase {
 public function test_actual_fixed_binding_matches_declaration_and_strict_runtime_hook_admission(){
  $script=__DIR__.'/support/source-policy-ambient-observer-worker.php';
  $process=proc_open(array(PHP_BINARY,'-n',$script,'--isolated-observer-binding'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($process),$err);$this->assertSame('',$err);$this->assertSame("checks:7 native_connections:0 installed_reads:0\n",$out);
 }
}
