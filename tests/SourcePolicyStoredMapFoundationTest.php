<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyStoredMapFoundationTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	/** @dataProvider invalid_bytes */
	public function test_malformed_objects_references_depth_counts_and_size_are_rejected($bytes) {
		$previous=function(){throw new RuntimeException('Previous handler must be restored, never used by decoder.');};
		set_error_handler($previous);
		try {
			$result=Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map::decode(array('option_value'=>$bytes,'autoload'=>'off'),'fixture',array('off'));
			$this->assertInstanceOf(WP_Error::class,$result);
			$restored=set_error_handler(function(){}); $this->assertSame($previous,$restored); restore_error_handler();
		} finally {restore_error_handler();}
	}
	public static function invalid_bytes() {
		return array_map(function($bytes){return array($bytes);},array('not serialized','b:0;','s:0:"";','a:999999999999999999:{}','a:2:{i:7;a:1:{s:7:"wpvivid";s:17:"external_optional";}}','a:1:{i:7;O:8:"stdClass":0:{}}','a:1:{i:7;a:1:{s:7:"wpvivid";R:1;}}','a:1:{i:7;a:1:{s:7:"wpvivid";a:1:{i:1;a:1:{i:2;a:0:{}}}}}','a:0:{} trailing',str_repeat('x',16385)));
	}
	public function test_existing_magic_method_class_is_not_instantiated() {
		$class=Alynt_Drime_Backups_Dashboard_Test_Policy_Decode_Tripwire::class;
		$class::$magic_calls=0;
		$bytes='a:1:{i:7;O:'.strlen($class).':"'.$class.'":0:{}}';
		$this->assertInstanceOf(WP_Error::class,Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map::decode(array('option_value'=>$bytes,'autoload'=>'off'),'fixture',array('off')));
		$this->assertSame(0,$class::$magic_calls);
	}
	public function test_primitive_aliases_and_absent_vs_present_empty_remain_data_not_authority() {
		$map=array('007'=>array('WPVIVID'=>'external_optional'),9=>array('wpvivid'=>'external_optional'));
		$decoded=Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map::decode(array('option_value'=>serialize($map),'autoload'=>'off'),'fixture',array('off'));
		$this->assertSame($map,$decoded['map']); $this->assertSame(array(7=>array('wpvivid'=>'external_optional'),9=>array('wpvivid'=>'external_optional')),$decoded['model']->plan($decoded['model']->revision(),7,'wpvivid','external_optional')['policies']);
		$absent=Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map::decode(null,'fixture',array('off'));
		$empty=Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map::decode(array('option_value'=>'a:0:{}','autoload'=>'off'),'fixture',array('off'));
		$this->assertNotSame($absent['model']->revision(),$empty['model']->revision());
		$this->assertSame(array(),json_decode(json_encode($decoded['model']),true));
	}
	public function test_hook_fingerprint_never_invokes_unknown_object_metadata() {
		$GLOBALS['wp_filter']['query']=new Alynt_Drime_Backups_Dashboard_Test_Policy_Decode_Tripwire();
		$this->error_code(Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::fingerprint(),'source_policy_unavailable');
	}
}
