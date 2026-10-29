<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/ci/source-policy-linux-qualification-contract.php';

/** Isolated DATA/source gates only; never core loading, native IO or SQL. */
final class SourcePolicyLinuxQualificationTest extends TestCase {
	private static function terminal(){return array('event'=>'closed','image'=>str_repeat('a',64),'run'=>str_repeat('b',32),'pid'=>123,'checks'=>200,'groups'=>9,'mode'=>0,'closed'=>true);}
	public function test_complete_correlated_positive_and_independent_negative_fields() {
		$row=self::terminal();$class='Alynt_Drime_Backups_Dashboard_Linux_Qualification_Contract';
		$this->assertTrue($class::terminal($row,$row['image'],$row['run'],123));
		foreach(array('event'=>'pending','image'=>str_repeat('c',64),'run'=>str_repeat('c',32),'pid'=>124,'checks'=>0,'groups'=>8,'mode'=>3,'closed'=>false,'extra'=>true) as $key=>$value){$bad=$row;$bad[$key]=$value;$this->assertFalse($class::terminal($bad,$row['image'],$row['run'],123));}
		foreach(array_keys($row) as $key){$bad=$row;unset($bad[$key]);$this->assertFalse($class::terminal($bad,$row['image'],$row['run'],123));}
		foreach(array('pid','checks','groups','mode') as $key){$bad=$row;$bad[$key]=(string)$bad[$key];$this->assertFalse($class::terminal($bad,$row['image'],$row['run'],123));}
		$this->assertFalse($class::terminal($row,$row['image'],$row['run'],'123'));
		$this->assertFalse($class::terminal($row,'a',$row['run'],123));
		$this->assertFalse($class::terminal(array_reverse($row,true),$row['image'],$row['run'],123));
	}
	/** @dataProvider invocations */
	public function test_non_owned_entry_refuses_before_core_or_native_access($arguments) {
		$directory=dirname(__DIR__).'/scripts/ci';
		$process=proc_open(array_merge(array(PHP_BINARY,'-n','-d','open_basedir='.$directory,$directory.'/source-policy-linux-qualification.php'),$arguments),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(1,proc_close($process));$this->assertSame('',$out);$this->assertSame("Requires exact owned Linux qualification job\n",$err);
	}
	public static function invocations(){return array(array(array()),array(array('--wrong','proof')),array(array('--owned-linux-qualification','audit')),array(array('--owned-linux-qualification','proof')),array(array('--owned-linux-qualification','proof','extra')));}
	/** Static caller-binding regression grounded in the hash-verified pinned declaration, not core execution. */
	public function test_parser_binding_uses_pinned_genuine_load_declaration() {
		$worker=file_get_contents(dirname(__DIR__).'/scripts/ci/source-policy-linux-qualification.php');
		preg_match_all('/\x27(parser_api_file|cache_api_file)\x27\s*=>\s*ABSPATH\s*\.\s*WPINC\s*\.\s*\x27(\/[a-z-]+\.php)\x27/',$worker,$matches,PREG_SET_ORDER);
		$bindings=array();foreach($matches as $match)$bindings[$match[1]]=$match[2];
		$this->assertSame(array('cache_api_file'=>'/load.php','parser_api_file'=>'/load.php'),$bindings,'Both actual declarations are in pinned load.php, not functions.php.');
		$pins=json_decode(file_get_contents(dirname(__DIR__).'/scripts/ci/source-policy-linux-core-pins.json'),true);
		$this->assertCount(11,$pins);
		$this->assertSame('3efd0934bd8a454a8d7076fbbd21504ea097a753125a48e64b8a1dad380d357a',$pins['load.php']);
	}
	public function test_only_manual_exact_head_immutable_actions_and_independent_cleanup() {
		$workflow=file_get_contents(dirname(__DIR__).'/.github/workflows/source-policy-linux-qualification.yml');
		foreach(array('workflow_dispatch:','contents: read','timeout-minutes: 10','"$EXPECTED_SHA" == "$GITHUB_SHA"','if: always()','source-policy-linux-preflight.sh','source-policy-linux-qualification.sh') as $required)$this->assertStringContainsString($required,$workflow);
		foreach(array('push:','pull_request:','schedule:','services:','secrets.','self-hosted') as $forbidden)$this->assertStringNotContainsString($forbidden,$workflow);
		preg_match_all('/uses: [^\s]+@([a-f0-9]{40})\b/',$workflow,$actions);$this->assertCount(4,$actions[0]);
		$worker=file_get_contents(dirname(__DIR__).'/scripts/ci/source-policy-linux-qualification.php');
		$this->assertStringNotContainsString('mysqli_report(',$worker);$this->assertStringNotContainsString('wp-load.php',$worker);
		$this->assertStringContainsString("\$ambient->db_connect( false )",$worker);
		$this->assertStringContainsString("source-policy-ambient-proof-cases.php",$worker);
	}
}
