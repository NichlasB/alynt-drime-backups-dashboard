<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyNativeAdapterConcurrencyTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	/** @dataProvider interleavings */
	public function test_contender_between_observation_and_cas_preserves_exact_winner($case) {
		$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store('first'===$case?null:array(9=>array('wpvivid'=>'external_optional')));
		list($owner,$connection)=$this->owner($store); $revision=$this->revision($store);
		$winner=$store->row;
		if('first'===$case || 'different'===$case) $winner=array('option_value'=>serialize(array(8=>array('wpvivid'=>'external_optional'))),'autoload'=>'off');
		if('value-case'===$case) $winner['option_value']=str_replace('external_optional','EXTERNAL_OPTIONAL',$winner['option_value']);
		if('value-space'===$case) $winner['option_value'].=' ';
		if('autoload-case'===$case) $winner['autoload']='OFF';
		if('autoload-space'===$case) $winner['autoload']='off ';
		$store->before_write=function()use($store,$winner){$store->row=$winner;};
		$this->error_code($owner->save_source_mode_if_current($revision,7,'wpvivid','external_optional'),'source_policy_conflict');
		$this->assertSame($winner,$store->row); $this->assertSame(0,$store->mutations); $this->assertSame(1,$store->attempts);
		$this->assertSame('first'===$case?2:1,$store->reads); $this->assertSame(0,$connection->reconnects);
		$this->assertNotContains('updated_option',$GLOBALS['policy_trace']);
	}
	public static function interleavings(){return array_map(function($case){return array($case);},array('first','different','value-case','value-space','autoload-case','autoload-space'));}
	public function test_stale_already_satisfied_command_is_conflict_not_noop() {
		list($owner,$connection,$store)=$this->owner(); $revision=$this->revision($store);
		$store->row['option_value']=serialize(array(7=>array('wpvivid'=>'external_optional')));
		$this->error_code($owner->save_source_mode_if_current($revision,7,'wpvivid','external_optional'),'source_policy_conflict');
		$this->assertSame(1,$store->reads); $this->assertSame(0,$store->attempts); $this->assertSame(1,$connection->closes);
	}
	public function test_positive_qualified_observer_and_reentry_block_before_a_second_read() {
		$owner=null; $store=null; $revision=null; $observed=array();
		policy_isolated_hook('update_option',function($name,$old,$value)use(&$owner,&$store,&$revision,&$observed){
			$reads=$store->reads; $observed[]=array($name,$old,$value);
			$this->error_code($owner->save_source_mode_if_current($revision,8,'wpvivid','external_optional'),'source_policy_unavailable');
			$this->assertSame($reads,$store->reads);
		});
		list($owner,$connection,$store)=$this->owner(); $revision=$this->revision($store);
		$this->assertSame('saved',$owner->save_source_mode_if_current($revision,7,'wpvivid','external_optional'));
		$this->assertCount(1,$observed); $this->assertSame(array(),$observed[0][1]);
		$this->assertSame(array(7=>array('wpvivid'=>'external_optional')),$observed[0][2]); $this->assertSame(1,$store->reads); $this->assertSame(1,$store->attempts); $this->assertSame(1,$connection->closes);
	}
	public function test_unknown_hook_is_rejected_without_invocation_or_read() {
		list($owner,$connection,$store)=$this->owner(); $revision=$this->revision($store);
		policy_isolated_hook('update_option',function(){self::fail('Unknown callback must not run.');});
		$this->error_code($owner->save_source_mode_if_current($revision,7,'wpvivid','external_optional'),'source_policy_unavailable');
		$this->assertSame(0,$store->reads); $this->assertSame(0,$store->attempts);
	}
	public function test_callback_registration_change_before_write_rejects_without_mutation() {
		$name=Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
		policy_isolated_hook('pre_update_option_'.$name,function($value){policy_isolated_hook('updated_option',function(){self::fail('New hook must not run.');});return $value;});
		list($owner,$connection,$store)=$this->owner();
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_unavailable');
		$this->assertSame(1,$store->reads); $this->assertSame(0,$store->attempts);
	}
	public function test_value_transformer_cannot_discard_non_target_policy() {
		$name=Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
		policy_isolated_hook('pre_update_option_'.$name,function(){return array();});
		list($owner,$connection,$store)=$this->owner(new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(9=>array('wpvivid'=>'external_optional')))); $before=$store->row;
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_unavailable');
		$this->assertSame($before,$store->row); $this->assertSame(0,$store->attempts);
	}
}
