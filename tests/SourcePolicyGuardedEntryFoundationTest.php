<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyGuardedEntryFoundationTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	public function test_capability_precedes_nonce_types_records_and_owner() {
		list($entry,$owner,$sites)=$this->entry(); $GLOBALS['policy_capable']=false;
		$this->error_code($entry->submit(new stdClass()),'source_policy_forbidden');
		$this->assertSame(array('capability'),$GLOBALS['policy_trace']); $this->assertSame(array(),$sites->calls); $this->assertSame(array(),$owner->calls);
		$this->error_code($entry->view(array(),new stdClass()),'source_policy_forbidden');
		$this->assertSame(array(),$owner->calls);
	}
	/** @dataProvider invalid_requests */
	public function test_malformed_nonce_or_command_never_reaches_record_or_owner($field,$value) {
		list($entry,$owner,$sites)=$this->entry(); $request=$this->request(); $request[$field]=$value;
		$this->error_code($entry->submit($request),'source_policy_invalid');
		$this->assertSame('capability',$GLOBALS['policy_trace'][0]); $this->assertSame(array(),$sites->calls); $this->assertSame(array(),$owner->calls);
		if('_wpnonce'!==$field) $this->assertSame('nonce',$GLOBALS['policy_trace'][1]);
	}
	public static function invalid_requests() {
		$cases=array(array('_wpnonce','wrong-action'),array('_wpnonce',array()),array('_wpnonce',null),array('_wpnonce',str_repeat('x',129)),array('dashboard_site_id',array()),array('dashboard_site_id',new stdClass()),array('dashboard_site_id',true),array('dashboard_site_id','0'),array('source_key',array()),array('source_key','other'),array('source_mode',array()),array('source_mode','unknown'),array('source_policy_revision',null),array('source_policy_revision',array()),array('source_policy_revision',str_repeat('A',64)),array('source_policy_revision',str_repeat('a',63)),array('source_policy_revision',str_repeat('a',64).' '));
		return $cases;
	}
	public function test_nonce_rejection_precedes_bad_command_fields() {
		list($entry,$owner,$sites)=$this->entry(); $request=$this->request(); $request['_wpnonce']='wrong'; $request['dashboard_site_id']=new stdClass();
		$this->error_code($entry->submit($request),'source_policy_invalid');
		$this->assertSame(array('capability','nonce'),$GLOBALS['policy_trace']); $this->assertSame(array(),$owner->calls); $this->assertSame(array(),$sites->calls);
	}
	/** @dataProvider statuses */
	public function test_exact_literal_statuses_follow_guards_and_record_resolution($status) {
		list($entry,$owner,$sites)=$this->entry(); $owner->result=$status;
		$this->assertSame($status,$entry->submit($this->request()));
		$this->assertSame(array('capability','nonce','record'),$GLOBALS['policy_trace']); $this->assertSame(array(7),$sites->calls);
		$this->assertSame(array(array(str_repeat('a',64),7,'wpvivid','external_optional')),$owner->calls);
	}
	public static function statuses(){return array(array('saved'),array('unchanged'));}
	/** @dataProvider unknown_results */
	public function test_booleans_model_arrays_and_unknown_results_are_never_success($value) {
		list($entry,$owner)=$this->entry(); $owner->result=$value;
		$this->error_code($entry->submit($this->request()),'source_policy_save_failed');
	}
	public static function unknown_results(){return array(array(true),array(false),array(1),array('true'),array('saved '),array(array('status'=>'saved')),array(new stdClass()),array(null));}
	/** @dataProvider collaborator_errors */
	public function test_error_projection_never_exposes_collaborator_message_or_data($code,$expected) {
		list($entry,$owner)=$this->entry(); $owner->result=new WP_Error($code,'owned-private-message',array('row'=>'owned-private-map'));
		$this->error_code($entry->submit($this->request()),$expected);
	}
	public static function collaborator_errors(){return array_map(function($reason){return array('source_policy_'.$reason,'source_policy_'.$reason);},array('conflict','invalid','unavailable','storage_error','write_uncertain'))+array(9=>array('unknown','source_policy_save_failed'));}
	public function test_missing_record_never_invokes_owner() {
		list($entry,$owner,$sites)=$this->entry(); $sites->exists=false;
		$this->error_code($entry->submit($this->request()),'source_policy_unavailable'); $this->assertSame(array(),$owner->calls);
	}
	/** @dataProvider wrong_records */
	public function test_record_identity_must_match_before_view_or_command($record) {
		list($entry,$owner,$sites)=$this->entry(); $sites->record_override=$record;
		$this->error_code($entry->submit($this->request()),'source_policy_unavailable');
		$this->error_code($entry->view(7,'wpvivid'),'source_policy_unavailable'); $this->assertSame(array(),$owner->calls);
	}
	public static function wrong_records(){return array(array(array()),array(array('id'=>8)),array(array('id'=>array(7))),array(array('id'=>new stdClass())),array(array('id'=>true)),array(array('id'=>'007')),array(array('id'=>null)));}
	public function test_form_view_rejects_raw_extra_fields_and_unknown_consistency_values() {
		list($entry,$owner)=$this->entry(); $owner->view=array('mode'=>'required','revision'=>str_repeat('a',64),'raw'=>'owned-private-map');
		$this->error_code($entry->view('7','wpvivid'),'source_policy_unavailable');
		$owner->view=array('mode'=>'required','revision'=>str_repeat('A',64)); $this->error_code($entry->view('7','wpvivid'),'source_policy_unavailable');
		$owner->view=array('mode'=>'required','revision'=>str_repeat('a',64)); $this->assertSame($owner->view,$entry->view('7','wpvivid'));
	}
	public function test_guarded_entry_drives_the_actual_adapter_for_positive_save() {
		list($adapter,$connection,$store)=$this->owner(); list($entry)=$this->entry($adapter);
		$request=$this->request(); $request['source_policy_revision']=$this->revision($store);
		$this->assertSame('saved',$entry->submit($request)); $this->assertSame(1,$store->reads); $this->assertSame(1,$store->mutations); $this->assertSame(1,$connection->closes);
	}
}
