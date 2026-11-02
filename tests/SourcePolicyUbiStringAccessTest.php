<?php
use PHPUnit\Framework\TestCase;

final class SourcePolicyUbiStringAccessTest extends TestCase {
 public function test_actual_socket_free_string_access_contracts() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-B','-W','error::SyntaxWarning',__DIR__.'/support/source_policy_ubi_string_access_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('redirect',1)),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out='';$overflow=false;
  while(!feof($pipes[1])){$chunk=fread($pipes[1],8192);$remaining=65536-strlen($out);if(strlen($chunk)>$remaining)$overflow=true;$out.=substr($chunk,0,$remaining);}
  fclose($pipes[1]);$this->assertSame(0,proc_close($p),$out);$this->assertFalse($overflow,'Bounded child diagnostics overflowed.');
  $this->assertSame(array('tests'=>12,'failures'=>0,'errors'=>0,'network_calls'=>0),json_decode($out,true));
 }
 public function test_default_collector_and_actual_entry_workflow_do_not_select_the_new_profile() {
  $root=dirname(__DIR__);
  $collector=file_get_contents($root.'/scripts/manual/source_policy_ubi_alias_static.py');
  $this->assertStringContainsString("parsed = elf.parse(body, budget['deadline'])",$collector);
  // The separately approved dormant owner adapter may opt in; the real entry/workflow may not.
  foreach(array('/scripts/ci/source-policy-legacy-ubi-static.py','/.github/workflows/source-policy-legacy-ubi-static.yml') as $path){
   $source=file_get_contents($root.$path);$this->assertStringNotContainsString('bounded_strings',$source);$this->assertStringNotContainsString('ADBUELFSA1',$source);
   $this->assertStringNotContainsString('work_owner',$source);
  }
 }
}
