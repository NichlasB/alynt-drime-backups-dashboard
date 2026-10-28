<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-ambient-proof-contract.php';

final class SourcePolicyAmbientProofContractTest extends TestCase {
 public function test_exact_positive_and_every_field_binding(){
  $r=str_repeat('a',32);$s=str_repeat('b',64);$wire="ADBO1|$r|$s|123|300|9|0|closed\n";
  $this->assertTrue(adb0_terminal($wire,$r,$s,123));
  foreach(array(str_replace('ADBO1','ADBA1',$wire),str_replace($r,str_repeat('c',32),$wire),str_replace($s,str_repeat('c',64),$wire),str_replace('|123|','|124|',$wire),str_replace('|300|','|0|',$wire),str_replace('|9|','|8|',$wire),str_replace('|0|','|3|',$wire),str_replace('closed','active',$wire),$wire."x",str_replace("\n","\r\n",$wire),new stdClass(),str_repeat('x',257)) as $bad){$this->assertFalse(adb0_terminal($bad,$r,$s,123));}
  $this->assertFalse(adb0_terminal($wire,$r,$s,'123'));
  $this->assertFalse(adb0_terminal($wire,'bad',$s,123));
  $this->assertFalse(adb0_terminal($wire,$r,'bad',123));
 }
 /** @dataProvider invocations */
 public function test_manual_gate_before_installed_source_or_native_access($args){
  $p=proc_open(array_merge(array(PHP_BINARY,'-n',dirname(__DIR__).'/scripts/manual/source-policy-ambient-owned-proof.php'),$args),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame(1,proc_close($p));$this->assertSame('',$out);$this->assertSame('',$err);
 }
 public static function invocations(){
  $a=array('--confirmed-plugin-tester-owned-ambient','adbb_0123456789ab_',str_repeat('a',32),str_repeat('b',64),'audit');
  $rows=array(array(array()),array(array('unapproved')),array(array_merge($a,array('extra'))));
  foreach(array(0=>'--unapproved',1=>'wp_',2=>'bad',3=>'bad',4=>'grant') as $i=>$bad){$b=$a;$b[$i]=$bad;$rows[]=array($b);}return $rows;
 }
}
