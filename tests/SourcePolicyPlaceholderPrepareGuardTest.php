<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyPlaceholderPrepareGuardTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	/** @dataProvider phases */
	public function test_callback_added_during_preparation_never_executes_or_reaches_driver($mutation) {
		list($owner,$connection,$store)=$this->owner(); $invoked=false;
		$change=function()use(&$invoked){policy_isolated_hook('query',function($sql)use(&$invoked){$invoked=true;return $sql;},1);};
		if($mutation) $store->after_read=function()use($store,$change){$store->after_prepare=$change;}; else $store->after_prepare=$change;
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_unavailable');
		$this->assertFalse($invoked); $this->assertSame($mutation?1:0,$store->reads); $this->assertSame($mutation?1:0,$connection->physical);
		$this->assertSame(0,$store->attempts); $this->assertFalse(has_filter('query',array($connection,'remove_placeholder_escape')));
		$this->assertTrue(has_filter('query')); // Foreign entry is not deleted as a cleanup repair.
	}
	public static function phases(){return array(array(false),array(true));}
	public function test_prepare_helper_itself_rejects_changed_metadata_before_returning_sql() {
		list($owner,$connection,$store)=$this->owner(); $store->after_prepare=function(){policy_isolated_hook('query',function($sql){return $sql;},1);}; $returned=false;
		try{$connection->prepare_checked('SELECT %d',7);$returned=true;}catch(RuntimeException $error){}
		$this->assertFalse($returned); $this->assertSame(0,$connection->physical); $this->assertFalse($connection->close());
		$this->assertFalse(has_filter('query',array($connection,'remove_placeholder_escape')));
	}
	public function test_missing_ambient_callback_added_during_prepare_is_not_adopted() {
		$ambient=(new ReflectionClass(wpdb::class))->newInstanceWithoutConstructor(); $GLOBALS['wpdb']=$ambient;
		list($owner,$connection,$store)=$this->owner(); $store->after_prepare=function()use($ambient){$ambient->placeholder_escape();};
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable'); $this->assertSame(0,$store->reads);
		$this->assertSame(0,has_filter('query',array($ambient,'remove_placeholder_escape'))); $this->assertSame(0,$ambient->closes);
	}
	public function test_refreshing_facts_after_a_receiver_swap_cannot_reseal_context() {
		list($owner,$connection,$store,$facts)=$this->owner(); $foreign=(new ReflectionClass(wpdb::class))->newInstanceWithoutConstructor();
		remove_filter('query',array($connection,'remove_placeholder_escape'),0); add_filter('query',array($foreign,'remove_placeholder_escape'),0);
		$facts['hooks']=Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::fingerprint(); $this->inject($connection,'qualification',$facts);
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(0,$foreign->placeholder_calls); $this->assertSame(0,$store->reads); $this->assertSame(0,has_filter('query',array($foreign,'remove_placeholder_escape')));
	}
	public function test_query_order_change_is_not_hidden_by_refreshing_fingerprint() {
		$ambient=(new ReflectionClass(wpdb::class))->newInstanceWithoutConstructor(); $GLOBALS['wpdb']=$ambient; $ambient->placeholder_escape();
		list($owner,$connection,$store,$facts)=$this->owner();
		$GLOBALS['wp_filter']['query']->callbacks[0]=array_reverse($GLOBALS['wp_filter']['query']->callbacks[0],true);
		$facts['hooks']=Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::fingerprint(); $this->inject($connection,'qualification',$facts);
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable'); $this->assertSame(0,$store->reads);
		$this->assertSame(0,has_filter('query',array($ambient,'remove_placeholder_escape'))); $this->assertFalse(has_filter('query',array($connection,'remove_placeholder_escape')));
	}
	public function test_non_query_change_during_prepare_cannot_reach_storage() {
		list($owner,$connection,$store)=$this->owner(); $invoked=false;
		$store->after_prepare=function()use(&$invoked){policy_isolated_hook('updated_option',function()use(&$invoked){$invoked=true;});};
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_unavailable');
		$this->assertFalse($invoked); $this->assertSame(0,$store->reads); $this->assertSame(0,$store->attempts);
	}
}
