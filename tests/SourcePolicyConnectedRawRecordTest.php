<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-connected-raw-record.php';
require_once dirname(__DIR__).'/scripts/manual/source-policy-cleanup-evidence.php';

/** Virtual DATA evidence only, never actual native release or qualification. */
final class SourcePolicyConnectedRawRecordTest extends TestCase {
 private function binding(){return array(str_repeat('a',32),hash('sha256','isolated-raw-connected'),105);}
 private function frames(){
  list($run,$source,$pid)=$this->binding();$zero=implode(',',array_fill(0,15,'0'));
  return array(implode('|',array('ADCR1',$run,$source,$pid,Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::PROFILE,'connected',$zero,$zero,'1,0,0,0,0,0,0,0,0,1,1,1,0,0,0','1','1','0'))."\n",implode('|',array('ADCR1',$run,$source,$pid,Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::PROFILE,'closed','1,0,0,0,0,0,0,0,0,1,18446744073709551615,18446744073709551615,1,0,0','1','1','1','none'))."\n");
 }
 private function model(){return new Alynt_Drime_Backups_Dashboard_Raw_Connected_Record(...$this->binding());}
 public function test_complete_virtual_diagnostic_requires_both_distinct_live_phases_and_post_exit(){
  list($run,$source,$pid)=$this->binding();list($connected,$closed)=$this->frames();$model=$this->model();
  $this->assertTrue($model->feed($connected,1));$this->assertSame('ADCR-CLOSE|'.$run.'|'.$source.'|105'."\n",$model->observe(105,true,1,0,2));
  $this->assertTrue($model->feed($closed,3));$this->assertSame('ADCR-EXIT|'.$run.'|'.$source.'|105'."\n",$model->observe(105,true,0,0,4));
  $this->assertTrue($model->finish(0,105,false,0,0,5));$this->assertSame('done',$model->phase());
  $map=Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::frame($closed,'closed',$run,$source,$pid);
  $this->assertSame('18446744073709551615',$map['active_connections']);$this->assertSame('1',$map['pconnect_success']);
  $this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::parse($connected));$this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::parse($closed));
 }
 /** @dataProvider corruptions */
 public function test_bad_stage_correlations_and_counter_effects_are_permanent_refusals($index,$replacement){
  list($connected)=$this->frames();$parts=explode('|',rtrim($connected,"\n"));$parts[$index]=$replacement;$model=$this->model();
  $this->assertFalse($model->feed(implode('|',$parts)."\n",0));$this->assertSame('unconfirmed',$model->phase());$this->assertFalse($model->feed($connected,1));
 }
 public static function corruptions(){
  $zero=implode(',',array_fill(0,15,'0'));$sets=array(array(0,'ADBC1'),array(1,'wrong'),array(2,str_repeat('b',64)),array(3,'106'),array(4,'other-profile'),array(5,'closed'),array(6,'1'.substr($zero,1)),array(7,'1'.substr($zero,1)),array(9,'0'),array(10,'0'),array(11,'1'));
  foreach(array(0=>'0',1=>'1',2=>'1',3=>'1',4=>'1',5=>'1',6=>'1',7=>'1',8=>'1',10=>'01',11=>'-1') as $key=>$value){$vector=explode(',','1,0,0,0,0,0,0,0,0,1,1,1,0,0,0');$vector[$key]=$value;$sets[]=array(8,implode(',',$vector));}return $sets;
 }
 public function test_missing_live_or_connected_stage_duplicate_frames_and_deadlines_fail(){
  list($connected,$closed)=$this->frames();
  foreach(array($closed,$connected.$connected,str_replace("\n","\r\n",$connected),rtrim($connected),str_repeat('x',16385)) as $bad){$model=$this->model();$this->assertFalse($model->feed($bad,0));}
  foreach(array(-1,25000,'1') as $now){$model=$this->model();$this->assertFalse($model->feed($connected,$now));}
  $model=$this->model();$this->assertTrue($model->feed($connected,2));$this->assertFalse($model->observe(105,true,1,0,1));
  $model=$this->model();$this->assertFalse($model->finish(0,105,false,0,0,0));
  $model=$this->model();$this->assertTrue($model->feed($connected,0));$this->assertFalse($model->feed($connected,1));
 }
 /** @dataProvider observations */
 public function test_wrong_live_or_exit_observations_never_issue_tokens($closed_stage,$pid,$alive,$sessions,$transactions){
  list($connected,$closed)=$this->frames();$model=$this->model();$model->feed($connected,0);
  if($closed_stage){$model->observe(105,true,1,0,1);$model->feed($closed,2);}
  $this->assertFalse($model->observe($pid,$alive,$sessions,$transactions,3));$this->assertSame('unconfirmed',$model->phase());
 }
 public static function observations(){return array(array(false,105,true,0,0),array(false,105,true,2,0),array(false,106,true,1,0),array(false,105,false,1,0),array(false,105,true,1,1),array(true,105,true,1,0),array(true,105,false,0,0),array(true,105,true,0,1),array(true,105,true,'0',0));}
 /** @dataProvider terminal_faults */
 public function test_missing_false_repeated_or_throwing_close_cannot_complete($index,$value){
  list($connected,$closed)=$this->frames();$model=$this->model();$model->feed($connected,0);$model->observe(105,true,1,0,1);
  $parts=explode('|',rtrim($closed,"\n"));$parts[$index]=$value;$this->assertFalse($model->feed(implode('|',$parts)."\n",2));$this->assertSame('unconfirmed',$model->phase());
 }
 public static function terminal_faults(){return array(array(7,'0'),array(8,'0'),array(8,'2'),array(9,'0'),array(10,'Error'));}
 public function test_post_exit_cannot_replace_live_reconciliation_or_forged_exit(){
  list($connected,$closed)=$this->frames();
  foreach(array(array(1,105,false,0,0,5),array(0,106,false,0,0,5),array(0,105,true,0,0,5),array(0,105,false,1,0,5),array(0,105,false,0,1,5),array(0,105,false,0,0,25000)) as $bad){
   $model=$this->model();$model->feed($connected,0);$model->observe(105,true,1,0,1);$model->feed($closed,2);$model->observe(105,true,0,0,3);
   $this->assertFalse($model->finish(...$bad));$this->assertSame('unconfirmed',$model->phase());
  }
  $model=$this->model();$model->feed($connected,0);$model->observe(105,true,1,0,1);$model->feed($closed,2);$this->assertFalse($model->finish(0,105,false,0,0,3));
 }
 public function test_expected_context_and_native_counter_types_do_not_coerce(){
  list($connected)=$this->frames();$this->assertFalse(Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::frame($connected,'connected','bad','bad',105));
  foreach(array(0,array(),new stdClass(),'0,0') as $bad)$this->assertFalse(Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::vector($bad));
  $model=new Alynt_Drime_Backups_Dashboard_Raw_Connected_Record(new stdClass(),'bad',105);$this->assertFalse($model->feed($connected,0));
 }
 public function test_bridge_executes_the_same_two_phase_record_model(){
  list($run,$source,$pid)=$this->binding();list($connected,$closed)=$this->frames();
  $input='feed|0|'.rtrim($connected,"\n")."\nlive|1|105,1,1,0\nfeed|2|".rtrim($closed,"\n")."\nlive|3|105,1,0,0\nfinish|4|0,105,0,0,0\n";
  $p=proc_open(array(PHP_BINARY,'-n',dirname(__DIR__).'/scripts/manual/source-policy-connected-raw-bridge.php','--private-raw-connected-data',$run,$source,'105'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fwrite($pipes[0],$input);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($p),$err);$this->assertSame('',$err);$this->assertSame("OK|connected_hold\nADCR-CLOSE|$run|$source|105\nOK|closed_hold\nADCR-EXIT|$run|$source|105\nOK|done\n",$out);
 }
}
