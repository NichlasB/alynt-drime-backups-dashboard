<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyNativeAdapterFailureTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	/** @dataProvider acknowledgements */
	public function test_only_exact_one_is_saved_and_unknown_never_replays($ack) {
		list($owner,$connection,$store)=$this->owner(); $store->ack=$ack;
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_write_uncertain');
		$this->assertSame(1,$store->attempts); $this->assertSame(1,$store->mutations); $this->assertSame(0,$connection->reconnects);
		$this->assertNotContains('updated_option',$GLOBALS['policy_trace']); $this->assertSame(1,$connection->closes);
	}
	public static function acknowledgements() { return array(array(true),array('1'),array(2),array(-1),array(null)); }
	/** @dataProvider failures */
	public function test_definite_driver_failures_are_not_all_duplicates($errno,$code) {
		list($owner,$connection,$store)=$this->owner(); $store->write_fault=$errno;
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),$code);
		$this->assertSame(0,$store->mutations); $this->assertSame(1,$store->attempts); $this->assertSame(1,$store->reads);
		$this->assertNotContains('updated_option',$GLOBALS['policy_trace']); $this->assertSame(1,$connection->closes);
	}
	public static function failures() { return array(array(1064,'source_policy_storage_error'),array(1205,'source_policy_storage_error'),array(1213,'source_policy_storage_error'),array(2006,'source_policy_write_uncertain'),array(9999,'source_policy_write_uncertain'),array(1062,'source_policy_write_uncertain')); }
	public function test_exception_after_mutation_is_uncertain_with_no_compensation() {
		list($owner,$connection,$store)=$this->owner(); $store->throw_after_write=true;
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_write_uncertain');
		$this->assertSame(1,$store->mutations); $this->assertSame(1,$store->attempts); $this->assertSame(2,$connection->physical);
	}
	public function test_cache_failure_after_mutation_is_uncertain_and_does_not_replay_hooks() {
		list($owner,$connection,$store)=$this->owner(); $GLOBALS['policy_cache_fail']=true; $GLOBALS['policy_cache']=array('options'=>array('alloptions'=>array('owned'=>'old')));
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_write_uncertain');
		$this->assertSame(1,$store->mutations); $this->assertNotContains('updated_option',$GLOBALS['policy_trace']); $this->assertSame(1,$connection->closes);
	}
	public function test_post_commit_observer_exception_is_uncertain_and_closes_owned_connection() {
		policy_isolated_hook('updated_option',function(){throw new RuntimeException('owned-private-observer');});
		list($owner,$connection,$store)=$this->owner();
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_write_uncertain');
		$this->assertSame(1,$store->mutations); $this->assertSame(1,$connection->closes);
	}
	/** @dataProvider bad_read_counts */
	public function test_bad_select_acknowledgements_never_become_missing_row($ack) {
		list($owner,$connection,$store)=$this->owner(); $store->read_ack=$ack;
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_storage_error');
		$this->assertSame(0,$store->attempts); $this->assertSame(1,$connection->closes);
	}
	public static function bad_read_counts() { return array(array(false),array(true),array('1'),array(2),array(0),array(-1)); }
	/** @dataProvider close_failures */
	public function test_unconfirmed_close_after_success_cannot_be_saved($throw) {
		list($owner,$connection,$store)=$this->owner(); $store->close_throw=$throw; $store->close_ok=false;
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_write_uncertain');
		$this->assertSame(1,$store->mutations); $this->assertSame(1,$connection->closes);
	}
	public static function close_failures() { return array(array(false),array(true)); }
}
