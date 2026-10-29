<?php
use PHPUnit\Framework\TestCase;

/** Untrusted synthetic inventory DATA cannot authorize real file operations. */
final class SourcePolicyMinimumArtifactTest extends TestCase {
	protected function setUp():void {
		require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-artifacts.php';
		require_once __DIR__.'/support/source-policy-minimum-fixtures.php';
	}
	public function test_fixed_catalog_and_owned_layout_are_data_only() {
		$spec=adbmin_catalog();$this->assertSame('14ae3250d4447c8ccfc4c45a70d90adfbcd61e728d85f0be56a7ddf8f9c8aace',$spec['php_sha256']);
		$this->assertSame('a3bd7a4074a4c95c8af3b969346726273d68e167',$spec['core_tree']);$this->assertFalse($spec['execution']);
		$layout=adbmin_layout('C:/Owned/Temp',str_repeat('a',32));$this->assertSame('C:/Owned/Temp/alynt-minimum-'.str_repeat('a',32),$layout['root']);$this->assertFalse($layout['filesystem_verified']);
		foreach(array('C:/','C:/Owned/../Temp','//server/share','C:/Temp/','C:/Temp:stream','C:/CON') as $root)$this->assertFalse(adbmin_layout($root,str_repeat('a',32)));
		$this->assertFalse(adbmin_layout('C:/Temp',str_repeat('A',32)));$this->assertFalse(adbmin_layout('C:/Temp',null));
		$this->assertTrue(adbmin_digest('synthetic',hash('sha256','synthetic')));$this->assertFalse(adbmin_php_zip('synthetic'));
		$this->assertFalse(adbmin_digest('synthetic',str_repeat('0',64)));$this->assertFalse(adbmin_digest(array(),str_repeat('0',64)));
	}
	public function test_inventory_accepts_a_valid_bounded_plan_without_extracting() {
		$entries=array(minimum_test_entry('php.exe'),minimum_test_entry('ext',0,'directory'),minimum_test_entry('ext/php_mysqli.dll',2));
		$plan=adbmin_inventory('php',$entries,100,60);$this->assertIsArray($plan);$this->assertSame(3,$plan['expanded']);$this->assertFalse($plan['extraction']);
		$wrapper='WordPress-'.adbmin_catalog()['core_commit'].'/';
		$this->assertIsArray(adbmin_inventory('core',array(minimum_test_entry(rtrim($wrapper,'/'),0,'directory'),minimum_test_entry($wrapper.'wp-includes/version.php')),100,60));
		$this->assertFalse(adbmin_inventory('other',$entries,100,60));$this->assertFalse(adbmin_inventory('php',$entries,'100',60));
		$this->assertFalse(adbmin_inventory('php',$entries,67108865,60));$this->assertFalse(adbmin_inventory('php',$entries,100,239));
	}
	/** @dataProvider unsafe_paths */
	public function test_unsafe_paths_are_rejected($path) {$this->assertFalse(adbmin_inventory('php',array(minimum_test_entry($path)),100,50));}
	public static function unsafe_paths() {return array_map(function($p){return array($p);},array('../escape','a/../b','/absolute','C:/escape','//unc/share','a\\b','a//b','a/./b','a:stream','a/NUL.txt','COM1','lpt9.dll','CON .txt','CONIN$','clock$.txt','a.','a ','a/*','a/?','a/"','a/|',"a\0b",'é.php','',str_repeat('a',241)));}
	public function test_collisions_links_sizes_and_unknown_metadata_fail_closed() {
		foreach(array(array('a','A'),array('a','a/b'),array('a/b','a'),array('Dir/a','dir/b')) as $paths){$entries=array_map('minimum_test_entry',$paths);$this->assertFalse(adbmin_inventory('php',$entries,100,50));}
		foreach(array('link','junction','reparse','unknown') as $kind)$this->assertFalse(adbmin_inventory('php',array(minimum_test_entry('a',1,$kind)),100,50));
		foreach(array(-1,1.0,'1',true,268435457) as $size)$this->assertFalse(adbmin_inventory('php',array(minimum_test_entry('a',$size)),100,50));
		$entry=minimum_test_entry('a');$entry['trusted']=true;$this->assertFalse(adbmin_inventory('php',array($entry),100,50));
		$this->assertFalse(adbmin_inventory('php',array(minimum_test_entry('a',1,'directory')),100,50));
		$this->assertFalse(adbmin_inventory('php',array_fill(0,8193,minimum_test_entry('a')),100,50));
		$this->assertFalse(adbmin_inventory('php',array(minimum_test_entry('a',2)),1,50));
		$this->assertFalse(adbmin_inventory('core',array(minimum_test_entry('WordPress-wrong/a')),100,50));
	}
}
