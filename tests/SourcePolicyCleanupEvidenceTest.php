<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-cleanup-evidence.php';
require_once __DIR__.'/support/source-policy-cleanup-fixtures.php';

/** Scalar verifier exercised with actual dormant-owner execution in synthetic workers. */
final class SourcePolicyCleanupEvidenceTest extends TestCase {
	public function test_all_seven_actual_isolated_owner_paths_satisfy_the_contract() {
		foreach(cleanup_fixture_rows() as $case=>$row)$this->assertTrue($this->evidence_matches($case,$row));
	}
	/** @dataProvider scalar_faults */
	public function test_independent_release_and_correlation_guards($case,$field,$value) {
		$row=cleanup_fixture_rows()[$case];$row[$field]=$value;$this->assertFalse($this->evidence_matches($case,$row));
	}
	public static function scalar_faults() {
		$rows=array();
		foreach(array('identity'=>'0','ambient'=>'1','ack'=>'0','close_calls'=>'2','cached'=>'0','dbh_null'=>'0','qualification_null'=>'0','frame_clean'=>'0','callback_clean'=>'0','results_clean'=>'0','closed_probe'=>'0','state'=>'unknown','observer_calls'=>'1','source'=>str_repeat('b',64),'run'=>str_repeat('b',32),'profile'=>'unknown','pid'=>'999','elapsed'=>'20001','outcome'=>'unavailable','active'=>'0','roundtrip'=>'0','seal'=>'0') as $key=>$value)$rows[]=array('positive',$key,$value);
		foreach(array('ack'=>'0','identity'=>'0','close_calls'=>'0','callback_clean'=>'0','state'=>'unknown','queries'=>'1','outcome'=>'active') as $key=>$value)$rows[]=array('missing-database',$key,$value);
		foreach(array('allocations'=>'1','ack'=>'1','state'=>'closed','close_calls'=>'1') as $key=>$value)$rows[]=array('default',$key,$value);
		return $rows;
	}
	/** @dataProvider counter_faults */
	public function test_counter_bytes_are_case_specific_and_never_normalized($case,$field,$key,$value) {
		$row=cleanup_fixture_rows()[$case];cleanup_fixture_counter($row,$field,$key,$value);$this->assertFalse($this->evidence_matches($case,$row));
	}
	public static function counter_faults() {
		$rows=array();
		foreach(array('connect_success','connect_failure','com_query','result_set_queries','explicit_free_result','implicit_free_result','unbuffered_sets','reconnect','connection_reused','pconnect_success','active_connections') as $key)$rows[]=array('default','before',$key,'1');
		foreach(array('0','-1','1.844674407371E+19','184467440737095516150','01') as $value)$rows[]=array('missing-database','after','active_connections',$value);
		foreach(array('positive','missing-table','default') as $case)$rows[]=array($case,'after','active_connections','18446744073709551615');
		foreach(array('connect_success'=>'1','connect_failure'=>'0','com_query'=>'1','reconnect'=>'1','connection_reused'=>'1','pconnect_success'=>'1','result_set_queries'=>'1') as $key=>$value)$rows[]=array('missing-database','after',$key,$value);
		foreach(array('com_query'=>'17','result_set_queries'=>'5','explicit_free_result'=>'5','implicit_free_result'=>'1','unbuffered_sets'=>'1') as $key=>$value)$rows[]=array('positive','bootstrap',$key,$value);
		$rows[]=array('positive','after','com_query','7');return $rows;
	}
	public function test_malformed_wire_and_non_scalar_data_reject_without_coercion() {
		$row=cleanup_fixture_rows()['positive'];$wire=cleanup_fixture_wire($row);
		foreach(array('',rtrim($wire),$wire.$wire,$wire.'noise',str_replace("\n","\r\n",$wire),str_replace('|',"\0",$wire),str_repeat('x',16385)) as $bad)$this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::parse($bad));
		$row['ack']=new class {public function __toString(){throw new RuntimeException('No coercion');}};
		$this->assertFalse($this->evidence_matches('positive',$row));$row=cleanup_fixture_rows()['positive'];$row['extra']='1';$this->assertFalse($this->evidence_matches('positive',$row));
	}
	public function test_expected_correlation_must_itself_be_well_formed() {
		$row=cleanup_fixture_rows()['positive'];$row['run']='bad';
		$this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches($row,'bad','positive',hash('sha256','isolated-cleanup-fixture'),106));
		$row=cleanup_fixture_rows()['positive'];$row['source']='bad';
		$this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches($row,str_repeat('a',32),'positive','bad',106));
	}
	private function evidence_matches($case,$row) {return Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches($row,str_repeat('a',32),$case,hash('sha256','isolated-cleanup-fixture'),100+array_search($case,Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::cases(),true));}
}
