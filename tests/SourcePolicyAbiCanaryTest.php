<?php
use PHPUnit\Framework\TestCase;

final class SourcePolicyAbiCanaryTest extends TestCase {
 public function test_owned_canary_contract_without_native_execution() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-B','-W','error::SyntaxWarning',__DIR__.'/support/source_policy_abi_canary_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('redirect',1)),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out='';$overflow=false;
  while(!feof($pipes[1])){$chunk=fread($pipes[1],8192);$remaining=65536-strlen($out);if(strlen($chunk)>$remaining)$overflow=true;$out.=substr($chunk,0,$remaining);}
  fclose($pipes[1]);$this->assertSame(0,proc_close($p),$out);$this->assertFalse($overflow,'Bounded child diagnostics overflowed.');
  $this->assertSame(array('tests'=>21,'failures'=>0,'errors'=>0,'network_calls'=>0),json_decode($out,true));
 }
}
