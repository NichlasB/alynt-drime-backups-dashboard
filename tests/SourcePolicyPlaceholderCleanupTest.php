<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyPlaceholderCleanupTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	/** @dataProvider native_close_faults */
	public function test_native_close_failure_still_removes_owned_callback_and_never_retries($throw) {
		list($owner,$connection,$store)=$this->owner(); $store->close_ok=false; $store->close_throw=$throw;
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_write_uncertain');
		$this->assertSame(1,$store->mutations); $this->assertFalse(has_filter('query')); $this->assertFalse($connection->close()); $this->assertSame(1,$connection->closes);
	}
	public static function native_close_faults(){return array(array(false),array(true));}
	/** @dataProvider removal_faults */
	public function test_removal_failure_still_closes_native_once_and_retains_uncertainty($throw,$submit) {
		list($owner,$connection,$store)=$this->owner(); $GLOBALS['policy_remove_throw']=$throw; $GLOBALS['policy_remove_retain']=!$throw;
		$request=$submit?$this->revision($store):'invalid';
		$this->error_code($owner->save_source_mode_if_current($request,7,'wpvivid','external_optional'),$submit?'source_policy_write_uncertain':'source_policy_unavailable');
		$this->assertSame($submit?1:0,$store->mutations); $this->assertNull($connection->dbh); $this->assertSame(1,$connection->closes);
		$this->assertSame(0,has_filter('query',array($connection,'remove_placeholder_escape'))); $this->assertFalse($connection->close()); $this->assertSame(1,$connection->closes);
	}
	public static function removal_faults(){return array(array(false,false),array(true,false),array(false,true),array(true,true));}
	public function test_moved_owned_entry_is_not_deleted_or_reported_clean() {
		list($owner,$connection,$store)=$this->owner(); remove_filter('query',array($connection,'remove_placeholder_escape'),0); add_filter('query',array($connection,'remove_placeholder_escape'),1);
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(0,$store->reads); $this->assertSame(1,has_filter('query',array($connection,'remove_placeholder_escape'))); $this->assertFalse($connection->close());
	}
	public function test_replaced_owned_slot_does_not_authorize_deleting_a_foreign_function() {
		list($owner,$connection,$store)=$this->owner(); $invoked=false;
		$key=policy_isolated_callback_key(array($connection,'remove_placeholder_escape'));
		$GLOBALS['wp_filter']['query']->callbacks[0][$key]['function']=function($sql)use(&$invoked){$invoked=true;return $sql;};
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable'); $this->assertFalse($invoked);
		$this->assertArrayHasKey($key,$GLOBALS['wp_filter']['query']->callbacks[0]); $this->assertSame(0,$store->reads); $this->assertFalse($connection->close());
	}
	public function test_preexisting_unleased_owned_registration_is_not_claimed_as_removed() {
		$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(); $connection=new Alynt_Drime_Backups_Dashboard_Test_Policy_Connection($store); $connection->placeholder_escape();
		list($owner)=$this->owner($store,'7.1.3',$connection); $this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(0,has_filter('query',array($connection,'remove_placeholder_escape'))); $this->assertFalse($connection->close()); $this->assertSame(0,$store->reads);
	}
	public function test_missing_handle_after_active_state_is_not_evidence_of_never_opened() {
		$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(); $connection=new Alynt_Drime_Backups_Dashboard_Test_Policy_Connection($store); $connection->ready=false; $connection->dbh=null;
		$this->inject($connection,'native_state','active'); list($owner)=$this->owner($store,'7.1.3',$connection);
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertFalse($connection->close()); $this->assertSame(0,$connection->closes); $this->assertFalse(has_filter('query')); $this->assertSame(0,$store->reads);
	}
}
