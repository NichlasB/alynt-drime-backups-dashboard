<?php
use PHPUnit\Framework\TestCase;

final class SourcePolicyUbiFixedOwnerTest extends TestCase {
 public function test_actual_synthetic_fixed_owner_contracts() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-B','-W','error::SyntaxWarning',__DIR__.'/support/source_policy_ubi_fixed_owner_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('redirect',1)),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out='';$overflow=false;
  while(!feof($pipes[1])){$chunk=fread($pipes[1],8192);$remaining=65536-strlen($out);if(strlen($chunk)>$remaining)$overflow=true;$out.=substr($chunk,0,$remaining);}
  fclose($pipes[1]);$this->assertSame(0,proc_close($p),$out);$this->assertFalse($overflow,'Bounded child diagnostics overflowed.');
  $this->assertSame(array('tests'=>14,'failures'=>0,'errors'=>0,'network_calls'=>0),json_decode($out,true));
 }
 public function test_opt_in_owner_remains_disconnected_from_actual_entry_and_workflow() {
  $root=dirname(__DIR__);
  foreach(array('/scripts/ci/source-policy-legacy-ubi-static.py','/.github/workflows/source-policy-legacy-ubi-static.yml') as $path){
   $source=file_get_contents($root.$path);
   foreach(array('work_owner','_owned_model','_owned_graph','bounded_strings','ADBUOWN1') as $marker)$this->assertStringNotContainsString($marker,$source);
  }
 }
}
