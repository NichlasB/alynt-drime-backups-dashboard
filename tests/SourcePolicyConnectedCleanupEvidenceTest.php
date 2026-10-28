<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-connected-cleanup-evidence.php';
require_once __DIR__.'/support/source-policy-connected-cleanup-fixtures.php';

/** Counter classification is DATA, not a release acknowledgement or native grant. */
final class SourcePolicyConnectedCleanupEvidenceTest extends TestCase {
 public function test_actual_isolated_owner_paths_supply_terminal_facts_without_legacy_upgrade(){
  foreach(connected_cleanup_fixture_rows() as $case=>$row){
   $original=$row;$wire=cleanup_fixture_wire($row);$parsed=Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::parse($wire);
   $this->assertSame($row,$parsed);$summary=connected_cleanup_inspect($case,$parsed);$this->assertIsArray($summary);
   $this->assertSame('nominal',$summary['counter_accounting']);$this->assertFalse($summary['counter_review_required']);
   $this->assertSame('pending-live-reconciliation',$summary['release_evidence']);$this->assertSame('not-established',$summary['native_qualification']);
   $this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::parse($wire));$this->assertSame($original,$row);
   $legacy=cleanup_fixture_rows()[$case];$this->assertFalse(Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::parse(cleanup_fixture_wire($legacy)));
   $this->assertTrue(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches($legacy,str_repeat('a',32),$case,hash('sha256','isolated-cleanup-fixture'),(int)$legacy['pid']));
  }
 }
 public function test_accounting_anomalies_stay_exact_review_required_data_not_a_wrap_allowlist(){
  foreach(connected_cleanup_fixture_rows(true) as $case=>$row){
   $original=$row;$summary=connected_cleanup_inspect($case,$row);$this->assertIsArray($summary);$this->assertSame('divergent',$summary['counter_accounting']);
   $this->assertTrue($summary['counter_review_required']);$this->assertSame($row['bootstrap'],$summary['bootstrap']);$this->assertSame($row['after'],$summary['after']);
   $this->assertSame('pending-live-reconciliation',$summary['release_evidence']);$this->assertSame('not-established',$summary['native_qualification']);$this->assertSame($original,$row);
   $legacy=cleanup_fixture_rows()[$case];foreach(array('bootstrap','after') as $field)$legacy[$field]=$row[$field];
   $this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches($legacy,str_repeat('a',32),$case,hash('sha256','isolated-cleanup-fixture'),(int)$legacy['pid']));
  }
  $row=connected_cleanup_fixture_rows()['positive'];cleanup_fixture_counter($row,'after','active_connections','123');cleanup_fixture_counter($row,'bootstrap','pconnect_success','2');
  $summary=connected_cleanup_inspect('positive',$row);$this->assertSame('divergent',$summary['counter_accounting']);$this->assertTrue($summary['counter_review_required']);
  $this->assertSame($row['after'],$summary['after']);$this->assertSame('not-established',$summary['native_qualification']);
 }
 /** @dataProvider scalar_faults */
 public function test_terminal_release_and_positive_guards_are_not_replaced_by_counter_diagnostics($field,$value){
  $row=connected_cleanup_fixture_rows(true)['positive'];$row[$field]=$value;$this->assertFalse(connected_cleanup_inspect('positive',$row));
 }
 public static function scalar_faults(){
  $faults=array('version'=>'ADBC1','run'=>str_repeat('b',32),'case'=>'missing-table','profile'=>'other','source'=>str_repeat('b',64),'pid'=>'105','allocations'=>'0','outcome'=>'unavailable','active'=>'0','ack'=>'0','close_calls'=>'2','cached'=>'0','identity'=>'0','ambient'=>'1','dbh_null'=>'0','qualification_null'=>'0','frame_clean'=>'0','callback_clean'=>'0','results_clean'=>'0','closed_probe'=>'0','state'=>'closure_unknown','observer_calls'=>'1','steps'=>'7','queries'=>'0','roundtrip'=>'0','seal'=>'0','elapsed'=>'20001','counter_policy'=>'legacy');
  $rows=array();foreach($faults as $key=>$value)$rows[]=array($key,$value);return $rows;
 }
 /** @dataProvider counter_faults */
 public function test_essential_effects_results_budgets_and_canonical_shapes_still_refuse($field,$key,$value){
  $row=connected_cleanup_fixture_rows(true)['positive'];cleanup_fixture_counter($row,$field,$key,$value);$this->assertFalse(connected_cleanup_inspect('positive',$row));
 }
 public static function counter_faults(){
  $rows=array();foreach(array('bootstrap','after') as $field)foreach(array('connect_success'=>'2','connect_failure'=>'1','reconnect'=>'1','connection_reused'=>'1','implicit_free_result'=>'1','unbuffered_sets'=>'1','result_set_queries'=>'5','explicit_free_result'=>'5') as $key=>$value)$rows[]=array($field,$key,$value);
  $rows[]=array('bootstrap','com_query','17');$rows[]=array('bootstrap','com_query','6');$rows[]=array('after','com_query','999');$rows[]=array('before','active_connections','1');
  foreach(array('-1','01','1.844674e19','184467440737095516150') as $value)$rows[]=array('after','active_connections',$value);return $rows;
 }
 public function test_refusal_is_not_positive_and_other_legacy_cases_cannot_enter_v2(){
  $row=connected_cleanup_fixture_rows(true)['missing-table'];$row['case']='positive';$row['pid']='106';$this->assertFalse(connected_cleanup_inspect('positive',$row));
  foreach(array('default','missing-database','wrong-source') as $case){$row=connected_cleanup_fixture_rows()['missing-table'];$row['case']=$case;$this->assertFalse(connected_cleanup_inspect($case,$row));}
  $row=connected_cleanup_fixture_rows(true)['missing-table'];$row['ack']='0';$this->assertFalse(connected_cleanup_inspect('missing-table',$row));
  $row=connected_cleanup_fixture_rows()['positive'];$row['bootstrap']=implode(',',array_fill(0,11,'0'));$row['after']=$row['bootstrap'];$this->assertFalse(connected_cleanup_inspect('positive',$row));
 }
 public function test_objects_extra_fields_invalid_context_and_malformed_wire_never_coerce(){
  $row=connected_cleanup_fixture_rows()['positive'];$wire=cleanup_fixture_wire($row);
  foreach(array('',rtrim($wire),$wire.$wire,$wire.'extra',str_replace("\n","\r\n",$wire),str_repeat('x',16385),str_replace('ADBC2','ADBC3',$wire)) as $bad)$this->assertFalse(Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::parse($bad));
  $row['ack']=new class {public function __toString(){throw new RuntimeException('no coercion');}};$this->assertFalse(connected_cleanup_inspect('positive',$row));
  $row=connected_cleanup_fixture_rows()['positive'];$row['extra']='1';$this->assertFalse(connected_cleanup_inspect('positive',$row));
  $row=connected_cleanup_fixture_rows()['positive'];foreach(array(array(new stdClass(),'positive',str_repeat('b',64),106),array(str_repeat('a',32),'positive',new stdClass(),106),array(str_repeat('a',32),'positive',hash('sha256','isolated-cleanup-fixture'),'106')) as $args)$this->assertFalse(Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::inspect($row,...$args));
 }
}
