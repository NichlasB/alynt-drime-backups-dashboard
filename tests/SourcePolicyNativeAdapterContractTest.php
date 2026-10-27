<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyNativeAdapterContractTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	/** @dataProvider profiles */
	public function test_positive_same_observation_view_and_conditional_update($core,$autoload) {
		$map=array(9=>array('wpvivid'=>'external_optional'));
		$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store($map,$autoload);
		list($viewOwner,$viewConnection)=$this->owner($store,$core);
		$view=$viewOwner->view_source_mode(7,'wpvivid');
		$this->assertSame(array('mode','revision'),array_keys($view)); $this->assertSame('required',$view['mode']);
		$this->assertSame($this->revision($store,$core),$view['revision']); $this->assertSame(1,$store->reads);
		$this->assertSame(0,$store->attempts); $this->assertSame(1,$viewConnection->closes);
		list($owner,$connection)=$this->owner($store,$core);
		$this->assertSame('saved',$owner->save_source_mode_if_current($view['revision'],7,'wpvivid','external_optional'));
		$this->assertSame(2,$store->reads); $this->assertSame(1,$store->attempts); $this->assertSame(1,$store->mutations);
		$this->assertSame(array(9=>$map[9],7=>$map[9]),unserialize($store->row['option_value']));
		$this->assertSame('6.0'===$core?'no':'off',$store->row['autoload']);
		$this->assertSame(2,$connection->physical); $this->assertSame(1,$connection->closes);
		$this->assertSame(3,count(array_filter($GLOBALS['policy_trace'],function($event){return strpos($event,'cache-delete:')===0;})));
		$this->assertStringContainsString('BINARY option_value=BINARY %s',$connection->queries[1][0]);
		$this->assertStringContainsString('OCTET_LENGTH(autoload)=%d',$connection->queries[1][0]);
	}
	public static function profiles() { return array_map(function($pair){return $pair;},array(array('6.0','yes'),array('6.0','no'),array('7.1.3','yes'),array('7.1.3','no'),array('7.1.3','on'),array('7.1.3','off'),array('7.1.3','auto'),array('7.1.3','auto-on'),array('7.1.3','auto-off'))); }
	public function test_missing_required_noop_and_strict_creation_are_distinct() {
		$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(null); $revision=$this->revision($store);
		list($noop)=$this->owner($store); $this->assertSame('unchanged',$noop->save_source_mode_if_current($revision,7,'wpvivid','required'));
		$this->assertNull($store->row); $this->assertSame(0,$store->attempts);
		list($insert,$connection)=$this->owner($store); $this->assertSame('saved',$insert->save_source_mode_if_current($revision,7,'wpvivid','external_optional'));
		$this->assertSame(1,$store->mutations); $this->assertStringStartsWith('INSERT INTO wp_options ',$connection->queries[1][0]);
		$this->assertStringNotContainsString('DUPLICATE',$connection->queries[1][0]);
		$this->assertNotSame($revision,$this->revision($store));
	}
	public function test_noop_has_no_mutation_cache_or_success_hook_replay() {
		list($owner,$connection,$store)=$this->owner(new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(7=>array('wpvivid'=>'external_optional'))));
		$this->assertSame('unchanged',$owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'));
		$this->assertSame(1,$store->reads); $this->assertSame(0,$store->attempts); $this->assertSame(1,$connection->closes);
		$this->assertNotContains('updated_option',$GLOBALS['policy_trace']); $this->assertNotContains('cache-delete:alloptions',$GLOBALS['policy_trace']);
	}
	public function test_legacy_bool_bridge_uses_one_observation_and_same_conditional_writer() {
		list($owner,$connection,$store)=$this->owner();
		$this->assertTrue($owner->set_source_mode(7,'wpvivid','external_optional'));
		$this->assertSame(1,$store->reads); $this->assertSame(1,$store->attempts); $this->assertSame(2,$connection->physical);
		list($remove)=$this->owner($store); $this->assertTrue($remove->set_source_mode(7,'wpvivid','legacy-unknown-means-required'));
		$this->assertSame(array(),unserialize($store->row['option_value']));
		list($failure)=$this->owner($store); $store->write_fault=1205;
		$this->assertFalse($failure->set_source_mode(7,'wpvivid','external_optional'));
	}
	/** @dataProvider rejected_profiles */
	public function test_unknown_private_profile_data_never_reaches_storage($key,$value) {
		list($owner,$connection,$store,$facts)=$this->owner(); $revision=$this->revision($store);
		$facts[$key]=$value; $this->inject($connection,'qualification',$facts);
		$this->error_code($owner->save_source_mode_if_current($revision,7,'wpvivid','external_optional'),'source_policy_unavailable');
		$this->assertSame(0,$connection->physical); $this->assertSame(0,$store->mutations); $this->assertSame(1,$connection->closes);
	}
	public static function rejected_profiles() { return array(array('core','9.0'),array('server','MySQL'),array('database','invalid.name'),array('table','wp_other_options'),array('schema','no-unique-key'),array('autocommit',1),array('single_site',false),array('dropins',true),array('cache','external'),array('bounded_session',false),array('hooks',str_repeat('a',64))); }
}
