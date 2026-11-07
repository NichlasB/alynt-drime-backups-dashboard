<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/includes/class-source-policy-production-expectation.php';

/** Actual pure tuple/expectation DATA only; no core/auth/storage/native execution. */
final class SourcePolicyW714ProfileTest extends TestCase {
	private static function frame() {
		return array('core'=>'7.1.4','database'=>'isolated','table'=>'wp_options','host'=>'127.0.0.1','port'=>10004,'charset'=>'utf8mb4','collate'=>'','transport'=>'tcp_loopback','socket'=>null,'db_host'=>'127.0.0.1:10004');
	}
	private static function runtime() {
		return array('platform'=>'Windows','arch'=>'AMD64','php'=>'8.5.12','client'=>'mysqlnd 8.5.12','mode'=>0,'word_size'=>8,'nts'=>true);
	}
	private static function expected() {
		return array('core'=>'7.1.4','server'=>'10.6.23-MariaDB','collation'=>'utf8mb4_unicode_520_ci','database'=>'isolated','table'=>'wp_options');
	}
	public function test_w714_positive_is_distinct_from_linux_with_same_core() {
		$class=Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Expectation::class;
		$frame=self::frame();$runtime=self::runtime();$expected=self::expected();
		$this->assertTrue($class::matches($frame,$runtime,$expected));
		foreach(array('platform'=>'Linux','arch'=>'x86_64','mode'=>3,'nts'=>false,'word_size'=>4,'php'=>'8.5.11','client'=>'mysqlnd 8.5.11') as $key=>$value){$bad=$runtime;$bad[$key]=$value;$this->assertFalse($class::matches($frame,$bad,$expected),$key);}
		foreach(array('charset'=>'utf8','host'=>'::1','transport'=>'unix_socket','socket'=>'/tmp/a.sock','port'=>'10004','db_host'=>'localhost:/tmp/a.sock','core'=>'7.1.5') as $key=>$value){$bad=$frame;$bad[$key]=$value;$this->assertFalse($class::matches($bad,$runtime,$expected),$key);}
		foreach(array_keys($runtime) as $key){$bad=$runtime;unset($bad[$key]);$this->assertFalse($class::matches($frame,$bad,$expected));}
		$bad=$runtime;$bad['extra']=true;$this->assertFalse($class::matches($frame,$bad,$expected));
		$bad=$expected;$bad['server']='10.11.18-MariaDB-ubu2204';$this->assertFalse($class::matches($frame,$runtime,$bad));
	}
	public function test_w714_transport_and_server_remain_platform_bound_and_typed() {
		$class=Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::class;$frame=self::frame();
		$this->assertTrue($class::valid($frame,'Windows'));
		foreach(array('Linux','Darwin','windows',false,null,array()) as $platform)$this->assertFalse($class::valid($frame,$platform));
		$this->assertSame('10.6.23-MariaDB',$class::server('7.1.4','Windows'));
		$this->assertSame('10.11.18-MariaDB-ubu2204',$class::server('7.1.4','Linux'));
		$this->assertSame('10.11.18-MariaDB-ubu2204',$class::server('7.1.4'));
		foreach(array('Darwin','windows',true,0,array(),new stdClass()) as $platform)$this->assertFalse($class::server('7.1.4',$platform));
		foreach(array('6.0','7.1.3') as $core)$this->assertSame('10.6.23-MariaDB',$class::server($core));
		$frame['host']='::1';$frame['db_host']='[::1]:10004';$this->assertFalse($class::valid($frame,'Windows'));
	}
}
