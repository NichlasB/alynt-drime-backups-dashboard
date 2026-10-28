<?php
use PHPUnit\Framework\TestCase;

/** Executes the real private operation with API doubles in fresh PHP -n children. */
final class SourcePolicyConnectedRawOperationTest extends TestCase {
 private function run_operation($mode,$token_mode='normal'){
  $run=str_repeat('a',32);$source=hash('sha256','isolated-raw-connected');
  $p=proc_open(array(PHP_BINARY,'-n',__DIR__.'/support/source-policy-connected-raw-worker.php','--isolated-raw-connected',$run,$mode),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);$first=fgets($pipes[1]);$out=$first;
  if(strpos($first,'ADCR1|')===0){$parts=explode('|',$first);$pid=$parts[3];$close='ADCR-CLOSE|'.$run.'|'.$source.'|'.$pid."\n";
   if($token_mode==='wrong-close')$close='ADCR-EXIT|'.$run.'|'.$source.'|'.$pid."\n";
   fwrite($pipes[0],$close);fflush($pipes[0]);$second=fgets($pipes[1]);$out.=$second;
   if(strpos($second,'ADCR1|')===0){$exit='ADCR-EXIT|'.$run.'|'.$source.'|'.$pid."\n";if($token_mode==='replay-close')$exit=$close;fwrite($pipes[0],$exit);fflush($pipes[0]);}
  }
  fclose($pipes[0]);$out.=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);return array(proc_close($p),$out,$err);
 }
 public function test_genuine_operation_reaches_both_holds_and_samples_every_stage_once(){
  list($exit,$out,$err)=$this->run_operation('normal');$this->assertSame(0,$exit,$err);$this->assertSame('',$err);
  $lines=explode("\n",trim($out));$this->assertCount(3,$lines);$first=explode('|',$lines[0]);$last=explode('|',$lines[1]);
  $this->assertSame('connected',$first[5]);$this->assertSame('closed',$last[5]);$this->assertSame(implode(',',array_fill(0,15,'0')),$first[6]);$this->assertSame($first[6],$first[7]);
  $this->assertSame('1,0,0,0,0,0,0,0,0,1,1,1,0,0,0',$first[8]);$this->assertSame('1,0,0,0,0,0,0,0,0,1,18446744073709551615,18446744073709551615,1,0,0',$last[6]);
  $this->assertSame('CHECKS|1|1|1|4',$lines[2]);
 }
 /** @dataProvider faults */
 public function test_actual_primitive_failures_preserve_once_only_cleanup_and_redaction($mode,$counts){
  list($exit,$out,$err)=$this->run_operation($mode);$this->assertSame(1,$exit);$this->assertStringContainsString($counts,$out);$this->assertStringNotContainsString('private-secret',$out.$err);$this->assertStringNotContainsString('|closed|',$out);
 }
 public static function faults(){return array(array('dirty','CHECKS|0|0|0|1'),array('bad-type','CHECKS|0|0|0|1'),array('ambient','CHECKS|0|0|0|0'),array('pre-source-drift','CHECKS|0|0|0|0'),array('allocation-false','CHECKS|1|0|0|1'),array('option-false','CHECKS|1|0|1|2'),array('dirty-init','CHECKS|1|0|1|2'),array('unavailable','CHECKS|1|1|1|2'),array('connect-throw','CHECKS|1|1|1|2'),array('paired','CHECKS|1|1|1|2'),array('always-closed','CHECKS|1|1|1|3'),array('extra-sql','CHECKS|1|1|1|3'),array('close-false','CHECKS|1|1|1|4'),array('close-throw','CHECKS|1|1|1|4'),array('source-drift','CHECKS|1|1|1|4'));}
 public function test_paired_failure_retains_primary_and_cleanup_kinds(){list($exit,$out,$err)=$this->run_operation('paired');$this->assertSame(1,$exit);$this->assertSame("isolated primary:RuntimeException cleanup:Throwable\n",$err);}
 public function test_phase_tokens_cannot_be_interchanged_or_replayed(){foreach(array('wrong-close','replay-close') as $token){list($exit,$out,$err)=$this->run_operation('normal',$token);$this->assertSame(1,$exit);$this->assertStringContainsString('CHECKS|1|1|1|',$out);$this->assertStringNotContainsString('private-secret',$err);}}
 public function test_native_entry_refuses_before_site_or_native_access_without_exact_scope(){
  $p=proc_open(array(PHP_BINARY,'-n',dirname(__DIR__).'/scripts/manual/source-policy-connected-raw-worker.php'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);$this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame(1,proc_close($p));$this->assertSame('',$out);$this->assertSame("Requires confirmed fixed raw connected scope\n",$err);
 }
}
