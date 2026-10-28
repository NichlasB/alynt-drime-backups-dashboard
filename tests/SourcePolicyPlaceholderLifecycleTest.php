<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyPlaceholderLifecycleTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	public function test_core_mechanics_positive_save_and_exact_callback_cleanup() {
		list($owner,$connection,$store)=$this->owner();
		$this->assertSame('saved',$owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'));
		$this->assertSame(1,$store->reads); $this->assertSame(1,$store->mutations); $this->assertSame(2,$connection->physical);
		$this->assertGreaterThan(0,$connection->placeholder_calls);
		$this->assertFalse(has_filter('query',array($connection,'remove_placeholder_escape')));
		$this->assertTrue($connection->close()); $this->assertSame(1,$connection->closes);
	}
	/** @dataProvider ambient_modes */
	public function test_percent_parameter_pipeline_preserves_ambient_callback($present) {
		$ambient=(new ReflectionClass(wpdb::class))->newInstanceWithoutConstructor(); $GLOBALS['wpdb']=$ambient;
		if($present) $ambient->placeholder_escape();
		list($owner,$connection,$store)=$this->owner(); $value="literal %s %d 100% ' quote \\ slash";
		$prepared=$connection->prepare_checked('SELECT %s',$value);
		$this->assertStringNotContainsString('100%',$prepared);
		$this->assertSame(1,$connection->execute($prepared)['affected']);
		$this->assertSame(array($value),$connection->queries[0][1]);
		$this->assertTrue($connection->close()); $this->assertSame(0,$ambient->closes);
		$this->assertSame($present?0:false,has_filter('query',array($ambient,'remove_placeholder_escape')));
		$this->assertFalse(has_filter('query',array($connection,'remove_placeholder_escape')));
	}
	public static function ambient_modes(){return array(array(false),array(true));}
	public function test_sequential_owners_leave_no_callback_accumulation() {
		$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store();
		for($i=0;$i<4;++$i){
			list($owner,$connection,,,$context)=$this->owner($store);
			$this->assertSame($i?'unchanged':'saved',$owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'));
			$this->assertFalse(has_filter('query')); $this->assertFalse($context->valid()); $this->assertFalse($context->begin());
			$this->assertTrue($connection->close()); $this->assertSame(1,$connection->closes);
		}
	}
	public function test_second_concurrent_owner_cannot_delete_the_first_callback() {
		list($first,$first_connection,$first_store)=$this->owner();
		list($second,$second_connection,$second_store)=$this->owner();
		$this->error_code($second->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(0,$second_store->reads); $this->assertSame(0,$second_connection->placeholder_calls);
		$this->assertSame(0,has_filter('query',array($first_connection,'remove_placeholder_escape')));
		$this->assertSame('saved',$first->save_source_mode_if_current($this->revision($first_store),7,'wpvivid','external_optional'));
		$this->assertFalse(has_filter('query')); $this->assertSame(1,$first_store->mutations);
	}
	public function test_alias_to_global_is_rejected_without_closing_global_handle() {
		$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(); $connection=new Alynt_Drime_Backups_Dashboard_Test_Policy_Connection($store); $GLOBALS['wpdb']=$connection;
		list($owner)=$this->owner($store,'7.1.3',$connection);
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(0,$connection->closes); $this->assertTrue($connection->ready); $this->assertTrue($connection->dbh);
		$this->assertSame(0,$connection->placeholder_calls); $this->assertSame(0,$store->reads); $this->assertFalse($connection->close());
	}
	public function test_never_opened_setup_still_cleans_its_registration() {
		$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(); $connection=new Alynt_Drime_Backups_Dashboard_Test_Policy_Connection($store); $connection->ready=false; $connection->dbh=null;
		list($owner)=$this->owner($store,'7.1.3',$connection);
		$this->assertSame(0,has_filter('query',array($connection,'remove_placeholder_escape')));
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(0,$connection->closes); $this->assertFalse(has_filter('query')); $this->assertTrue($connection->close());
	}
	public function test_global_receiver_replacement_is_not_adopted() {
		$ambient=(new ReflectionClass(wpdb::class))->newInstanceWithoutConstructor(); $GLOBALS['wpdb']=$ambient; $ambient->placeholder_escape();
		list($owner,$connection,$store)=$this->owner(); $GLOBALS['wpdb']=(new ReflectionClass(wpdb::class))->newInstanceWithoutConstructor();
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(0,$store->reads); $this->assertSame(0,has_filter('query',array($ambient,'remove_placeholder_escape')));
		$this->assertFalse(has_filter('query',array($connection,'remove_placeholder_escape'))); $this->assertSame(0,$ambient->closes);
	}
}
