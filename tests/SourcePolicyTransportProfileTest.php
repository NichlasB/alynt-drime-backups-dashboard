<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/includes/class-source-policy-transport-profile.php';

/** Pure tuple DATA checks; explicit platform arguments never qualify a session. */
final class SourcePolicyTransportProfileTest extends TestCase {
	private static function socket($path='/tmp/owned.sock'){return array('transport'=>'unix_socket','core'=>'7.1.4','host'=>'localhost','port'=>null,'socket'=>$path,'db_host'=>'localhost:'.$path);}
	/** @dataProvider endpoints */
	public function test_exact_transport_consistency($frame,$platform,$valid) {
		$this->assertSame($valid,Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::valid($frame,$platform));
	}
	public static function endpoints() {
		$socket=self::socket();$tcp=array('transport'=>'tcp_loopback','core'=>'6.0','host'=>'127.0.0.1','port'=>10004,'socket'=>null,'db_host'=>'127.0.0.1:10004');
		$cases=array(array($socket,'Linux',true),array($socket,'Windows',false),array($socket,'Darwin',false),array($tcp,'Windows',true),array($tcp,'Linux',true));
		foreach(array('','/','relative.sock','/tmp/../owned.sock','/tmp/./owned.sock','/tmp//owned.sock','/tmp/owned.sock/','p:/tmp/a','unix:///tmp/a','C:/tmp/a',"/tmp/a\0",'/tmp/a b',"/tmp/a\n",'/tmp/é') as $path)$cases[]=array(self::socket($path),'Linux',false);
		$cases[]=array(self::socket('/'.str_repeat('a',95)),'Linux',true);$cases[]=array(self::socket('/'.str_repeat('a',96)),'Linux',false);
		foreach(array('transport'=>'unknown','host'=>'127.0.0.1','port'=>10004,'socket'=>null,'db_host'=>'localhost:/tmp/owned.sock:10004','core'=>'7.1.3') as $key=>$value){$bad=$socket;$bad[$key]=$value;$cases[]=array($bad,'Linux',false);}
		foreach(array('host'=>'localhost','port'=>null,'socket'=>'/tmp/owned.sock','db_host'=>'127.0.0.1:10004garbage','core'=>'7.1.4') as $key=>$value){$bad=$tcp;$bad[$key]=$value;$cases[]=array($bad,'Linux',false);}
		foreach(array(0,65536,'10004',true,new stdClass()) as $port){$bad=$tcp;$bad['port']=$port;$cases[]=array($bad,'Linux',false);}
		foreach(array_keys($socket) as $key){$bad=$socket;unset($bad[$key]);$cases[]=array($bad,'Linux',false);}
		$tcp['host']='::1';$tcp['db_host']='[::1]:10004';$cases[]=array($tcp,'Linux',true);$tcp['db_host']='::1:10004';$cases[]=array($tcp,'Linux',false);
		return $cases;
	}
	public function test_server_rows_are_exact_candidates_not_prefix_grants() {
		foreach(array('6.0'=>'10.6.23-MariaDB','7.1.3'=>'10.6.23-MariaDB','7.1.4'=>'10.11.18-MariaDB-ubu2204') as $core=>$server)$this->assertSame($server,Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::server($core));
		foreach(array('7.1','7.1.4-dev','7.2',null,714) as $core)$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::server($core));
	}
}
