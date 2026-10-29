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
	/** Executes only the actual pure diagnostic function declarations, never the guarded worker/native body. */
	private function diagnostic_run($body) {
		$worker=file_get_contents(dirname(__DIR__).'/scripts/ci/source-policy-linux-qualification.php');$definitions='';
		foreach(array('adbb_capture','adbb_check') as $name){$this->assertSame(1,preg_match('/^function '.$name.'\([^\n]+\n.*?^\}/ms',$worker,$match));$definitions.=$match[0]."\n";}
		$process=proc_open(array(PHP_BINARY,'-n','-r',$definitions.$body),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process));$this->assertSame('',$err);return json_decode($out,true);
	}
	public function test_first_assertion_survives_successful_and_failed_finally_checks() {
		$row=$this->diagnostic_run('$checks=0;$groups=0;$stage="escape";$phase="native";adbb_check(true,"positive");try{try{adbb_check(false,"original");}finally{adbb_check(true,"release-positive");adbb_check(false,"release-negative");}}catch(Throwable $error){adbb_capture($error);}echo json_encode(array($checks,$GLOBALS["adbb_first_failure"]));');
		$this->assertSame(4,$row[0]);$this->assertSame(2,$row[1]['checks']);$this->assertSame(hash('sha256','original'),$row[1]['assertion']);$this->assertSame('RuntimeException',$row[1]['exception']);
	}
	public function test_non_assertion_throwable_is_captured_before_cleanup_without_secret_text() {
		$row=$this->diagnostic_run('$checks=0;$groups=0;$stage="escape";$phase="native";$GLOBALS["adbb_escape"]=array("step"=>"prepare","prepared"=>null,"ack_exact"=>null,"row_exact"=>null,"bytes_equal"=>null);try{try{throw new TypeError("private-secret-sql");}catch(Throwable $error){adbb_capture($error);throw $error;}finally{$GLOBALS["adbb_escape"]["step"]="release";adbb_check(false,"cleanup");}}catch(Throwable $error){adbb_capture($error);}echo json_encode($GLOBALS["adbb_first_failure"]);');
		$this->assertSame(0,$row['checks']);$this->assertSame('TypeError',$row['exception']);$this->assertSame(hash('sha256','none'),$row['assertion']);$this->assertSame('prepare',$row['escape']['step']);$this->assertStringNotContainsString('private-secret-sql',json_encode($row));
	}
	public function test_escape_capture_precedes_finally_and_preserves_single_dispatch_and_original_predicates() {
		$worker=file_get_contents(dirname(__DIR__).'/scripts/ci/source-policy-linux-qualification.php');
		$this->assertMatchesRegularExpression('/catch \( Throwable \$error \) \{\s*adbb_capture\( \$error \);\s*throw \$error;\s*\} finally/s',$worker);
		foreach(array('prepared','ack_exact','row_exact','bytes_equal','adbb_escape_release') as $field)$this->assertStringContainsString($field,$worker);
		$this->assertSame(1,substr_count($worker,'$escape_owner->prepare_checked('));$this->assertSame(1,substr_count($worker,'$escape_owner->execute('));
		$this->assertStringContainsString("\$needle === \$escape_owner->last_result[0]->escaped",$worker);
		$this->assertStringContainsString("json_encode( \$GLOBALS['adbb_first_failure'] + array( 'escape_release' => \$GLOBALS['adbb_escape_release'] ) )",$worker);
		$this->assertStringNotContainsString('adbb_last_check',$worker);
	}
	public function test_success_does_not_create_failure_and_unknown_exception_names_are_not_emitted() {
		$row=$this->diagnostic_run('$checks=0;$groups=0;$stage="escape";$phase="native";adbb_check(true,"positive");$none=$GLOBALS["adbb_first_failure"]??null;class PrivateSecretException extends RuntimeException{}adbb_capture(new PrivateSecretException("private-secret"));echo json_encode(array($none,$GLOBALS["adbb_first_failure"]));');
		$this->assertNull($row[0]);$this->assertSame('other',$row[1]['exception']);$this->assertSame(1,$row[1]['checks']);$this->assertStringNotContainsString('private-secret',strtolower(json_encode($row)));
	}
	/** @dataProvider escape_cases */
	public function test_actual_escape_block_preserves_short_circuit_acceptance_and_release($case,$step,$executions) {
		$worker=file_get_contents(dirname(__DIR__).'/scripts/ci/source-policy-linux-qualification.php');$start=strpos($worker,"\ttry {\n\t\t\$needle");$end=strpos($worker,"\tadb0_cases();",$start);
		$this->assertNotFalse($start);$this->assertNotFalse($end);$block=substr($worker,$start,$end-$start);
		$setup='$checks=0;$groups=0;$stage="escape";$phase="native";$case='.var_export($case,true).';$calls=array(0,0,0);$GLOBALS["adbb_escape"]=array("step"=>"prepare","prepared"=>null,"ack_exact"=>null,"row_exact"=>null,"bytes_equal"=>null);';
		$double='class OwnedDiagnosticDouble{public $last_result=array();private $value;public function prepare_checked($sql,$value){global $calls,$case;++$calls[0];$this->value=$value;if("prepare"===$case)throw new TypeError("private-prepare");return "opaque";}public function execute($sql){global $calls,$case;++$calls[1];if("execute"===$case)throw new TypeError("private-execute");$this->last_result="rows"===$case?array():array((object)array("escaped"=>"bytes"===$case?"wrong":$this->value));return array("affected"=>"ack"===$case?false:1,"errno"=>0);}public function close(){global $calls,$case;++$calls[2];return "release"!==$case;}}function adb0_released($owner){adbb_check(true,"cleanup-positive");}$escape_owner=new OwnedDiagnosticDouble();$failed=false;';
		$row=$this->diagnostic_run($setup.$double.'try{'.$block.'}catch(Throwable $error){$failed=true;adbb_capture($error);}echo json_encode(array($failed,$GLOBALS["adbb_first_failure"]??null,$calls,$GLOBALS["adbb_escape_release"]));');
		$this->assertSame(array(1,$executions,1),$row[2]);$this->assertSame('positive'!==$case,$row[0]);$this->assertSame('release'!==$case,$row[3]);
		if('positive'===$case){$this->assertNull($row[1]);return;}
		$this->assertSame($step,$row[1]['escape']['step']);$this->assertSame(0,$row[1]['groups']);
		if('ack'===$case){$this->assertFalse($row[1]['escape']['ack_exact']);$this->assertNull($row[1]['escape']['row_exact']);$this->assertNull($row[1]['escape']['bytes_equal']);}
		if('rows'===$case){$this->assertFalse($row[1]['escape']['row_exact']);$this->assertNull($row[1]['escape']['bytes_equal']);}
		if('bytes'===$case)$this->assertFalse($row[1]['escape']['bytes_equal']);
		$this->assertStringNotContainsString('private-',json_encode($row));
	}
	public static function escape_cases(){return array(array('positive',null,1),array('prepare','prepare',0),array('execute','execute',1),array('ack','execute',1),array('rows','rows',1),array('bytes','bytes',1),array('release','release',1));}
}
