<?php
use PHPUnit\Framework\TestCase;

/** Synthetic PE structure/import evidence only; no image loading or DLL trust. */
final class SourcePolicyMinimumPeTest extends TestCase {
	protected function setUp():void {require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-pe.php';require_once __DIR__.'/support/source-policy-minimum-fixtures.php';}
	public function test_genuine_parser_reads_synthetic_x64_import_structure() {
		$result=adbmin_pe(minimum_test_pe(),'php.exe');$this->assertSame(array('role'=>'php.exe','machine'=>'x64','format'=>'PE32+','imports'=>array('kernel32.dll'),'load_authorized'=>false),$result);
		$this->assertIsArray(adbmin_pe(minimum_test_pe(true),'php7.dll'));$this->assertIsArray(adbmin_pe(minimum_test_pe(true),'ext/php_mysqli.dll'));
		$this->assertFalse(adbmin_pe(minimum_test_pe(true),'php.exe'));$this->assertFalse(adbmin_pe(minimum_test_pe(),'php7.dll'));$this->assertFalse(adbmin_pe(minimum_test_pe(),'other.exe'));
	}
	/** @dataProvider malformed_headers */
	public function test_required_structure_and_import_guards_reject_mutations($offset,$replacement) {$bytes=minimum_test_pe();$this->assertFalse(adbmin_pe(substr_replace($bytes,$replacement,$offset,strlen($replacement)),'php.exe'));}
	public static function malformed_headers() {return array(array(0,'NO'),array(60,pack('V',0xfffffff0)),array(128,'PX'),array(132,pack('v',0x14c)),array(134,pack('v',0)),array(134,pack('v',17)),array(150,pack('v',0)),array(152,pack('v',0x10b)),array(260,pack('V',1)),array(272,pack('VV',0x3000,40)),array(272,pack('VV',0x1000,19)),array(368,pack('VV',0x1000,40)),array(412,pack('V',1000)),array(524,pack('V',0x3000)),array(532,str_repeat('x',20)),array(576,"../evil.dll\0"),array(576,str_repeat('x',82)));}
	public function test_truncation_and_non_string_inputs_are_refused() {foreach(array('',str_repeat('x',63),substr(minimum_test_pe(),0,1023),null,array()) as $bytes)$this->assertFalse(adbmin_pe($bytes,'php.exe'));}
}
