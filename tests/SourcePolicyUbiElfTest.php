<?php
use PHPUnit\Framework\TestCase;

final class SourcePolicyUbiElfTest extends TestCase {
 public function test_actual_static_elf_and_bound_graph_contracts() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-B',__DIR__.'/support/source_policy_ubi_elf_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('redirect',1)),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out='';$overflow=false;
  while(!feof($pipes[1])){$chunk=fread($pipes[1],8192);$remaining=65536-strlen($out);if(strlen($chunk)>$remaining)$overflow=true;$out.=substr($chunk,0,$remaining);}
  fclose($pipes[1]);$this->assertSame(0,proc_close($p),$out);$this->assertFalse($overflow,'Child output exceeded the bounded diagnostic capture.');
  $this->assertSame(array('tests'=>20,'failures'=>0,'errors'=>0,'network_calls'=>0),json_decode($out,true));
 }
 public function test_elf_mode_is_explicit_and_never_confers_runtime_permission() {
  $root=dirname(__DIR__);$entry=file_get_contents($root.'/scripts/ci/source-policy-legacy-ubi-static.py');
  $workflow=file_get_contents($root.'/.github/workflows/source-policy-legacy-ubi-static.yml');
  $this->assertStringContainsString('elf_policy=False',$entry);
  $this->assertStringContainsString('elf.admit_selected(roles)',$entry);
  $this->assertStringContainsString('retain_string_table_diagnostic(report, error.elf_string_table_facts, elf)',$entry);
  $this->assertStringContainsString('ELF_POLICY:',$workflow);
  $this->assertStringContainsString('default: false',$workflow);
  foreach(array('subprocess','ctypes','extractall(','os.system','docker pull') as $bad)$this->assertStringNotContainsString($bad,$entry);
 }
}
