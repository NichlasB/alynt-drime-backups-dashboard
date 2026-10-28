<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/ci/source-policy-linux-identity-contract.php';

/** Pure DATA consistency, not a native database/session or admission proof. */
final class SourcePolicyLinuxIdentityContractTest extends TestCase {
	private static function row(){return array('version'=>'10.11.18-MariaDB-ubu2204','comment'=>'mariadb.org binary distribution','machine'=>'x86_64','os'=>'debian-linux-gnu','no_tcp'=>'1','read_only'=>'1','ac'=>'1','tx'=>'0','socket'=>'/run/adb-owned/native.sock','connection_id'=>'5','application_schemas'=>'0','other_sessions'=>'0','transactions'=>'0','principal'=>'root@localhost');}
	public function test_unqualified_build_identity_is_observable_without_granting_support() {
		$this->assertTrue(Alynt_Drime_Backups_Dashboard_Linux_Identity_Contract::valid(self::row(),'10.11.18-MariaDB-ubu2204','Localhost via UNIX socket'));
	}
	/** @dataProvider invalid_rows */
	public function test_metadata_mismatch_refuses($field,$value) {
		$row=self::row();$row[$field]=$value;
		$this->assertFalse(Alynt_Drime_Backups_Dashboard_Linux_Identity_Contract::valid($row,'10.11.18-MariaDB-ubu2204','Localhost via UNIX socket'));
	}
	public static function invalid_rows(){return array(array('version','10.11.18-MariaDB'),array('no_tcp','0'),array('read_only','0'),array('ac','0'),array('tx','1'),array('socket','/tmp/default.sock'),array('connection_id','0'),array('connection_id','5suffix'),array('application_schemas','1'),array('other_sessions','1'),array('transactions','1'),array('principal','unknown@localhost'),array('machine','aarch64'),array('comment',new stdClass()),array('os',"Linux\0"),array('comment',str_repeat('x',513)),array('unexpected','0'));}
	public function test_complete_shape_and_socket_transport_are_required() {
		foreach(array_keys(self::row()) as $key){$row=self::row();unset($row[$key]);$this->assertFalse(Alynt_Drime_Backups_Dashboard_Linux_Identity_Contract::valid($row,'10.11.18-MariaDB-ubu2204','Localhost via UNIX socket'));}
		$this->assertFalse(Alynt_Drime_Backups_Dashboard_Linux_Identity_Contract::valid(self::row(),'10.11.18-MariaDB-ubu2204','127.0.0.1 via TCP/IP'));
		$this->assertFalse(Alynt_Drime_Backups_Dashboard_Linux_Identity_Contract::valid(self::row(),new stdClass(),'Localhost via UNIX socket'));
	}
	/** @dataProvider invocations */
	public function test_extension_free_reader_refuses_before_socket_access($arguments) {
		$directory=dirname(__DIR__).'/scripts/ci';$process=proc_open(array_merge(array(PHP_BINARY,'-n','-d','open_basedir='.$directory,$directory.'/source-policy-linux-identity.php'),$arguments),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(1,proc_close($process));$this->assertSame('',$out);$this->assertSame("Requires exact owned Linux empty-database identity audit\n",$err);
	}
	public static function invocations(){return array(array(array()),array(array('--wrong')),array(array('--owned-identity-audit')),array(array('--owned-identity-audit','extra')));}
	public function test_workflow_is_exact_head_and_manual_only() {
		$workflow=file_get_contents(dirname(__DIR__).'/.github/workflows/source-policy-linux-identity.yml');
		foreach(array('workflow_dispatch:','contents: read','timeout-minutes: 10','"$EXPECTED_SHA" == "$GITHUB_SHA"','if: always()','source-policy-linux-preflight.sh','source-policy-linux-identity.sh') as $required)$this->assertStringContainsString($required,$workflow);
		foreach(array('push:','pull_request:','schedule:','services:','secrets.','self-hosted','MYSQL_PWD') as $forbidden)$this->assertStringNotContainsString($forbidden,$workflow);
	}
}
