<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-failed-connect-observation.php';

/** API doubles only, with no shipped native observation backend. */
final class SourcePolicyFailedConnectObservationTest extends TestCase {
 /** @dataProvider cases */
 public function test_failure_stage_cleanup_once_and_no_failed_object_getter($case) {
  $p=proc_open(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode',__DIR__.'/support/source-policy-cleanup-observation-worker.php',$case),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($p),$err);$this->assertSame('',$err);$this->assertMatchesRegularExpression('/\Achecks:[1-9][0-9]*\n\z/',$out);
 }
 public static function cases(){return array_map(static function($case){return array($case);},array('expected','wrong-code','wrong-class','false','true','allocate-false','allocate-throw','alias','lost-identity','close-false','close-throw','reentry','close-one','owns-one','owns-throw','cleanup-owns-throw','double-failure'));}
 public function test_observation_seam_is_unavailable_outside_extension_free_isolation() {
  $io=new class implements Alynt_Drime_Backups_Dashboard_Cleanup_Observation_IO {
   public function allocate(){throw new RuntimeException('must not allocate');}
   public function owns($h){throw new RuntimeException('must not inspect');}
   public function connect($h){throw new RuntimeException('must not connect');}
   public function close($h){throw new RuntimeException('must not close');}
   public function errno($h){throw new RuntimeException('must not read');}
  };
  $owner=new Alynt_Drime_Backups_Dashboard_Failed_Connect_Observation();
  $expected=array('observed'=>false,'code'=>null,'primary'=>'unconfirmed','cleanup'=>'unconfirmed');
  $this->assertSame($expected,$owner->run($io));$this->assertSame($expected,$owner->run($io));
 }
}
