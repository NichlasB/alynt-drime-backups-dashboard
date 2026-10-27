<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyNoReconnectFoundationTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	public function test_all_production_factories_are_closed_without_io() {
		foreach (array('No_Reconnect_Connection','Native_Adapter','Guarded_Entry') as $suffix) {
			$class='Alynt_Drime_Backups_Dashboard_Source_Policy_'.$suffix;
			$this->error_code($class::create(),'source_policy_unavailable');
			$this->assertFalse((new ReflectionMethod($class,'__construct'))->isPublic());
		}
		$this->assertSame(array(),$GLOBALS['policy_trace']);
		$this->assertTrue((new ReflectionMethod(Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::class,'check_connection'))->isFinal());
	}
	public function test_default_core_double_replays_but_fixed_connection_does_not() {
		$control=(new ReflectionClass(wpdb::class))->newInstanceWithoutConstructor();
		$control->store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(null); $control->store->lose_ack=true;
		$sql='INSERT INTO wp_options (option_name,option_value,autoload) VALUES (%s,%s,%s)';
		$control->query($control->prepare($sql,'owned','a:0:{}','off'));
		$this->assertSame(2,$control->physical); $this->assertSame(1,$control->reconnects);
		list($owner,$connection,$store)=$this->owner(new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(null));
		$store->lose_ack=true;
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_write_uncertain');
		$this->assertSame(2,$connection->physical); // one SELECT + one physical mutation, not a replay.
		$this->assertSame(1,$store->attempts); $this->assertSame(1,$store->mutations);
		$this->assertSame(0,$connection->reconnects); $this->assertSame(1,$connection->closes);
		$this->assertFalse($connection->check_connection()); $this->assertFalse($connection->db_connect());
		$this->assertFalse($connection->print_error('owned-private-sql'));
	}
	public function test_failed_read_is_never_absence_or_permission_to_insert() {
		list($owner,$connection,$store)=$this->owner(new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(null));
		$revision=$this->revision($store); $store->read_fault=2006;
		$this->error_code($owner->save_source_mode_if_current($revision,7,'wpvivid','external_optional'),'source_policy_storage_error');
		$this->assertSame(1,$store->reads); $this->assertSame(0,$store->attempts); $this->assertSame(0,$connection->reconnects);
		$this->assertSame(1,$connection->closes); $this->assertNull($store->row);
	}
}
