<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyPlaceholderProvenanceTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	public function test_query_context_factory_and_empty_context_are_closed_without_io() {
		$this->error_code(Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context::create(),'source_policy_unavailable');
		$context=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context::class))->newInstanceWithoutConstructor();
		$this->assertFalse($context->begin()); $this->assertFalse($context->valid()); $this->assertFalse($context->finish()); $this->assertFalse(has_filter('query'));
	}
	/** @dataProvider overrides */
	public function test_overridden_virtual_dependencies_never_run($class) {
		require __DIR__.'/support/source-policy-placeholder-poison-fixtures.php'; $GLOBALS['policy_poison_calls']=0;
		$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(); $connection=new $class($store);
		list($owner)=$this->owner($store,'7.1.3',$connection);
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(0,$GLOBALS['policy_poison_calls']); $this->assertSame(0,$connection->placeholder_calls); $this->assertSame(0,$store->reads);
	}
	public static function overrides(){return array_map(function($name){return array('PolicyPoison'.$name);},array('Placeholder','Add','Remove','Prepare','Escape'));}
	/** @dataProvider bad_bindings */
	public function test_source_and_class_binding_failure_precedes_warm_up($field,$value) {
		$connection=new Alynt_Drime_Backups_Dashboard_Test_Policy_Connection(new Alynt_Drime_Backups_Dashboard_Test_Policy_Store()); $context=$this->context_for($connection);
		$this->inject($context,$field,$value); $this->assertFalse($context->begin()); $this->assertSame(0,$connection->placeholder_calls); $this->assertFalse(has_filter('query'));
	}
	public static function bad_bindings(){return array(array('core_file',__FILE__),array('owned_file',__FILE__),array('owned_class','wpdb'),array('core_file',null),array('core_file',__DIR__.'/owned-core-source-not-present.php'));}
	public function test_magic_receiver_metadata_cannot_become_a_trusted_object() {
		require __DIR__.'/support/source-policy-placeholder-poison-fixtures.php'; $GLOBALS['policy_poison_calls']=0;
		$context=$this->context_for(new PolicyReceiverTripwire()); $this->assertFalse($context->begin());
		$this->assertSame(0,$GLOBALS['policy_poison_calls']); $this->assertFalse(has_filter('query'));
	}
	/** @dataProvider bad_registrations */
	public function test_preexisting_wrong_receiver_or_registration_shape_rejects_without_invocation($kind) {
		$foreign=(new ReflectionClass(wpdb::class))->newInstanceWithoutConstructor();
		$invoked=false; $callback=function($sql)use(&$invoked){$invoked=true;return $sql;};
		if('receiver'===$kind) add_filter('query',array($foreign,'remove_placeholder_escape'),0);
		elseif('static'===$kind) add_filter('query','wpdb::remove_placeholder_escape',0);
		else { $GLOBALS['wpdb']=$foreign; add_filter('query','closure'===$kind?$callback:array($foreign,'remove_placeholder_escape'),'priority'===$kind?10:0,'args'===$kind?2:1); }
		list($owner,$connection,$store)=$this->owner(); $this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertFalse($invoked); $this->assertSame(0,$foreign->placeholder_calls); $this->assertSame(0,$connection->placeholder_calls); $this->assertSame(0,$store->reads);
	}
	public static function bad_registrations(){return array_map(function($name){return array($name);},array('receiver','priority','args','static','closure'));}
	public function test_warm_up_failure_cleans_exact_partial_registration() {
		$GLOBALS['policy_after_add']=function(){throw new RuntimeException('owned-private-warm');};
		list($owner,$connection,$store)=$this->owner(); $this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(0,$store->reads); $this->assertFalse(has_filter('query')); $this->assertSame(1,$connection->closes);
	}
	public function test_non_query_change_during_warm_up_is_not_sealed_as_approved() {
		$invoked=false; $GLOBALS['policy_after_add']=function()use(&$invoked){policy_isolated_hook('updated_option',function()use(&$invoked){$invoked=true;});};
		list($owner,$connection,$store)=$this->owner(); $this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertFalse($invoked); $this->assertSame(0,$store->reads); $this->assertFalse(has_filter('query')); $this->assertTrue(has_filter('updated_option'));
	}
	/** @dataProvider malformed_metadata */
	public function test_malformed_or_over_budget_registry_rejects_without_warm_up($kind) {
		$GLOBALS['wp_filter']['query']=new WP_Hook();
		if('object'===$kind) $GLOBALS['wp_filter']['query']=new stdClass();
		elseif('priorities'===$kind) $GLOBALS['wp_filter']['query']->callbacks=array_fill(0,33,array());
		elseif('entries'===$kind) $GLOBALS['wp_filter']['query']->callbacks[0]=array_fill(0,65,array('function'=>'unknown','accepted_args'=>1));
		else $GLOBALS['wp_filter']['query']->callbacks[0][]=array('function'=>'unknown','accepted_args'=>new stdClass());
		list($owner,$connection,$store)=$this->owner(); $this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(0,$connection->placeholder_calls); $this->assertSame(0,$connection->physical); $this->assertSame(0,$store->reads);
	}
	public static function malformed_metadata(){return array(array('object'),array('priorities'),array('entries'),array('args-object'));}
}
