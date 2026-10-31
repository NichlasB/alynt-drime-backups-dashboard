<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/includes/class-source-policy-transport-profile.php';
require_once __DIR__.'/support/source-policy-parser-tuple-data.php';

/** Isolated tuple DATA; no genuine core loading, authentication, storage or native IO. */
final class SourcePolicyParsedTransportTest extends TestCase {
	private static function frame($core='6.0',$port=3306,$host='127.0.0.1') {
		return array('core'=>$core,'transport'=>'tcp_loopback','host'=>$host,'port'=>$port,'socket'=>null,'db_host'=>('::1'===$host?'[::1]':$host).':'.$port);
	}
	/** @dataProvider source_tuples */
	public function test_source_derived_tcp_output_is_accepted_without_casting_parser_data($frame,$tuple) {
		$this->assertTrue(Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed($frame,$tuple));
	}
	public static function source_tuples() {
		$cases=array();
		foreach(policy_parser_tuple_data() as $core=>$data)foreach($data['tuples'] as $input=>$tuple) {
			if(null===$tuple[1])continue; // The socket profile has a separate Linux-only contract.
			$frame=self::frame($core,(int)$tuple[1],$tuple[0]);
			$cases[$core.' '.$input]=array($frame,$tuple);
		}
		return $cases;
	}
	public function test_data_provenance_matches_unchanged_official_core_pins() {
		$pins=require dirname(__DIR__).'/scripts/manual/source-policy-supported-core-pins.php';
		foreach(policy_parser_tuple_data() as $core=>$data){
			$this->assertSame($pins[$core]['commit'],$data['commit']);
			$this->assertSame($pins[$core]['core_file'],$data['file']);
			$this->assertSame($pins[$core]['pins'][$data['file']],$data['sha256']);
			$port=$data['tuples']['127.0.0.1:3306'][1];
			$this->assertSame('6.0'===$core?'3306':3306,$port);
		}
	}
	/** @dataProvider invalid_tuples */
	public function test_malformed_or_different_parser_tuple_never_matches($core,$tuple) {
		$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed(self::frame($core),$tuple));
	}
	public static function invalid_tuples() {
		$cases=array();
		foreach(array('6.0','7.1.3') as $core){
			$good=array('127.0.0.1','6.0'===$core?'3306':3306,null,false);
			foreach(array(null,false,true,0,'3306',new stdClass(),array(),array_slice($good,0,3),array_merge($good,array('extra')),array_reverse($good,true)) as $tuple)$cases[]=array($core,$tuple);
			foreach(array(null,false,true,3306.0,'03306','+3306','3306 ','3306tail',"3306\0",'3.306e3',0,'0',65536,'65536',str_repeat('3',1025),new stdClass(),'6.0'===$core?3306:'3306') as $port){$tuple=$good;$tuple[1]=$port;$cases[]=array($core,$tuple);}
			foreach(array(0=>array('localhost','::1',false,new stdClass()),2=>array('',false,'/tmp/a',new stdClass()),3=>array(0,1,'',null,new stdClass())) as $slot=>$bad)foreach($bad as $value){$tuple=$good;$tuple[$slot]=$value;$cases[]=array($core,$tuple);}
		}
		return $cases;
	}
	public function test_full_frame_validation_precedes_comparison_and_never_coerces_objects() {
		$tuple=array('127.0.0.1','3306',null,false);$base=self::frame();
		foreach(array('core'=>'unknown','host'=>'localhost','port'=>'3306','socket'=>'/tmp/a','db_host'=>'127.0.0.1:3306suffix','transport'=>'unknown') as $key=>$value){$frame=$base;$frame[$key]=$value;$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed($frame,$tuple));}
		foreach(array_keys($base) as $key){$frame=$base;unset($frame[$key]);$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed($frame,$tuple));}
		foreach(array(0,-1,65536,PHP_INT_MAX,3306.0,true,null) as $port)$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed(self::frame('6.0',$port),$tuple));
		$tripwire=new class {public function __toString(){throw new RuntimeException('No tuple coercion');}};
		$tuple[1]=$tripwire;$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed($base,$tuple));
		$bad=$base;$bad['port']=$tripwire;$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed($bad,array('127.0.0.1','3306',null,false)));
	}
	public function test_socket_profile_keeps_null_port_and_exact_platform_path_and_flag() {
		$frame=array('core'=>'7.1.4','transport'=>'unix_socket','host'=>'localhost','port'=>null,'socket'=>'/tmp/owned.sock','db_host'=>'localhost:/tmp/owned.sock');
		$tuple=array('localhost',null,'/tmp/owned.sock',false);
		$this->assertSame('Linux'===PHP_OS_FAMILY,Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed($frame,$tuple));
		foreach(array(1=>'',2=>'/tmp/other.sock',3=>0) as $slot=>$value){$bad=$tuple;$bad[$slot]=$value;$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed($frame,$bad));}
	}
}
