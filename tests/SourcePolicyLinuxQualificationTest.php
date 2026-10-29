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
		foreach(array('adbb_capture','adbb_check','adbb_ack') as $name){$this->assertSame(1,preg_match('/^function '.$name.'\([^\n]+\n.*?^\}/ms',$worker,$match));$definitions.=$match[0]."\n";}
		list($status,$out,$err)=$this->diagnostic_child($definitions.$body);
		$summary=self::diagnostic_summary($status,$out,$err);$this->assertSame(0,$status,$summary);$this->assertSame(0,strlen($err),$summary);$this->assertLessThanOrEqual(8192,strlen($out),$summary);
		$row=unserialize($out,array('allowed_classes'=>false));$this->assertIsArray($row);return $row;
	}
	/** No JSON dependency; only fixed kind/line/capability facts escape a thrown isolated fixture. */
	private function diagnostic_child($body) {
		$code='set_error_handler(static function(){throw new RuntimeException("Isolated fixture diagnostic");});try{'.$body.'}catch(Throwable $error){$kind=get_class($error);if(!in_array($kind,array("RuntimeException","Error","TypeError","ValueError","ParseError"),true))$kind="other";fwrite(STDERR,"ADBDIAG1|".$kind."|".$error->getLine()."|json:".(function_exists("json_encode")?"1":"0")."\n");exit(1);}';
		$process=proc_open(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode','-r',$code),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);return array(proc_close($process),$out,$err);
	}
	private static function diagnostic_summary($status,$out,$err) {
		$known=preg_match('/\AADBDIAG1\|(RuntimeException|Error|TypeError|ValueError|ParseError|other)\|([1-9][0-9]{0,4})\|json:([01])\n\z/',$err,$match);
		return 'exit:'.$status.' kind:'.($known?$match[1]:'unknown').' line:'.($known?$match[2]:'unknown').' json:'.($known?$match[3]:'unknown').' stdout_bytes:'.strlen($out).' stdout_sha256:'.hash('sha256',substr($out,0,8192)).' stderr_bytes:'.strlen($err).' stderr_sha256:'.hash('sha256',substr($err,0,1024));
	}
	public function test_json_free_fixture_round_trip_with_json_disabled() {
		$row=$this->diagnostic_run('echo serialize(array(function_exists("json_encode"),array("positive"=>true,"bytes"=>"owned\0bytes","missing"=>null)));');
		$this->assertSame(array(false,array('positive'=>true,'bytes'=>"owned\0bytes",'missing'=>null)),$row);
	}
	public function test_missing_json_and_thrown_fixture_have_safe_actionable_failures() {
		list($status,$out,$err)=$this->diagnostic_child('echo json_encode(array("positive"));');
		$this->assertSame(1,$status);$this->assertSame('',$out);$this->assertSame(1,preg_match('/\AADBDIAG1\|(Error|RuntimeException)\|1\|json:0\n\z/',$err),self::diagnostic_summary($status,$out,$err));
		list($status,$out,$err)=$this->diagnostic_child('fwrite(STDOUT,"private-stdout");throw new RuntimeException("private-message-path");');$summary=self::diagnostic_summary($status,$out,$err);
		$this->assertSame(1,$status);$this->assertStringContainsString('kind:RuntimeException line:1 json:0',$summary);$this->assertStringContainsString('stdout_bytes:14',$summary);
		foreach(array('private-stdout','private-message-path') as $secret)$this->assertStringNotContainsString($secret,$summary);
		$this->assertStringNotContainsString('private-stderr',self::diagnostic_summary(255,'private-stdout','private-stderr'));
	}
	public function test_first_assertion_survives_successful_and_failed_finally_checks() {
		$row=$this->diagnostic_run('$checks=0;$groups=0;$stage="escape";$phase="native";adbb_check(true,"positive");try{try{adbb_check(false,"original");}finally{adbb_check(true,"release-positive");adbb_check(false,"release-negative");}}catch(Throwable $error){adbb_capture($error);}echo serialize(array($checks,$GLOBALS["adbb_first_failure"]));');
		$this->assertSame(4,$row[0]);$this->assertSame(2,$row[1]['checks']);$this->assertSame(hash('sha256','original'),$row[1]['assertion']);$this->assertSame('RuntimeException',$row[1]['exception']);
	}
	public function test_non_assertion_throwable_is_captured_before_cleanup_without_secret_text() {
		$row=$this->diagnostic_run('$checks=0;$groups=0;$stage="escape";$phase="native";$GLOBALS["adbb_escape"]=array("step"=>"prepare","prepared"=>null,"ack_exact"=>null,"row_exact"=>null,"bytes_equal"=>null);try{try{throw new TypeError("private-secret-sql");}catch(Throwable $error){adbb_capture($error);throw $error;}finally{$GLOBALS["adbb_escape"]["step"]="release";adbb_check(false,"cleanup");}}catch(Throwable $error){adbb_capture($error);}echo serialize($GLOBALS["adbb_first_failure"]);');
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
		$row=$this->diagnostic_run('$checks=0;$groups=0;$stage="escape";$phase="native";adbb_check(true,"positive");$none=$GLOBALS["adbb_first_failure"]??null;class PrivateSecretException extends RuntimeException{}adbb_capture(new PrivateSecretException("private-secret"));echo serialize(array($none,$GLOBALS["adbb_first_failure"]));');
		$this->assertNull($row[0]);$this->assertSame('other',$row[1]['exception']);$this->assertSame(1,$row[1]['checks']);$this->assertStringNotContainsString('private-secret',strtolower(json_encode($row)));
	}
	/** @dataProvider escape_cases */
	public function test_actual_escape_block_preserves_short_circuit_acceptance_and_release($case,$step,$executions,$commands='one') {
		$worker=file_get_contents(dirname(__DIR__).'/scripts/ci/source-policy-linux-qualification.php');$start=strpos($worker,"\ttry {\n\t\t\$needle");$end=strpos($worker,"\tadb0_cases();",$start);
		$this->assertNotFalse($start);$this->assertNotFalse($end);$block=substr($worker,$start,$end-$start);
		$setup='$checks=0;$groups=0;$stage="escape";$phase="native";$case='.var_export($case,true).';$calls=array(0,0,0);$GLOBALS["adbb_escape"]=array("step"=>"prepare","prepared"=>null,"ack_exact"=>null,"row_exact"=>null,"bytes_equal"=>null,"ack"=>null,"query"=>null);';
		$double='class OwnedDiagnosticDouble{public $last_result=array(),$ready=true,$num_queries=0;private $value;public function prepare_checked($sql,$value){global $calls,$case;++$calls[0];$this->value=$value;if("prepare"===$case)throw new TypeError("private-prepare");return "opaque";}public function execute($sql){global $calls,$case;++$calls[1];$this->num_queries=array("commands-zero"=>0,"commands-two"=>2,"commands-many"=>3,"commands-unknown"=>"private-counter")[$case]??1;if("execute"===$case)throw new TypeError("private-execute");$this->last_result="rows"===$case?array():array((object)array("escaped"=>"bytes"===$case?"wrong":$this->value));return array("affected"=>"ack"===$case?false:1,"errno"=>0);}public function close(){global $calls,$case;++$calls[2];return "release"!==$case;}}function adb0_released($owner){adbb_check(true,"cleanup-positive");}$escape_owner=new OwnedDiagnosticDouble();$failed=false;';
		$row=$this->diagnostic_run($setup.$double.'try{'.$block.'}catch(Throwable $error){$failed=true;adbb_capture($error);}echo serialize(array($failed,$GLOBALS["adbb_first_failure"]??null,$calls,$GLOBALS["adbb_escape_release"],$GLOBALS["adbb_escape"]));');
		$positive='positive'===$case||0===strpos($case,'commands-');$this->assertSame(array(1,$executions,1),$row[2]);$this->assertSame(!$positive,$row[0]);$this->assertSame('release'!==$case,$row[3]);
		if($positive){$this->assertNull($row[1]);$this->assertSame($commands,$row[4]['query']['commands']);return;}
		$this->assertSame($step,$row[1]['escape']['step']);$this->assertSame(0,$row[1]['groups']);
		if('ack'===$case){$this->assertFalse($row[1]['escape']['ack_exact']);$this->assertNull($row[1]['escape']['row_exact']);$this->assertNull($row[1]['escape']['bytes_equal']);$this->assertSame(array('shape'=>true,'affected'=>'false','errno'=>'0'),$row[1]['escape']['ack']);$this->assertSame('one',$row[1]['escape']['query']['commands']);$this->assertTrue($row[1]['escape']['query']['ascii']);}
		if('rows'===$case){$this->assertFalse($row[1]['escape']['row_exact']);$this->assertNull($row[1]['escape']['bytes_equal']);}
		if('bytes'===$case)$this->assertFalse($row[1]['escape']['bytes_equal']);
		$this->assertStringNotContainsString('private-',json_encode($row));
	}
	public static function escape_cases(){return array(array('positive',null,1),array('prepare','prepare',0),array('execute','execute',1),array('ack','execute',1),array('rows','rows',1),array('bytes','bytes',1),array('release','release',1),array('commands-zero',null,1,'zero'),array('commands-two',null,1,'two'),array('commands-many',null,1,'many'),array('commands-unknown',null,1,'unknown'));}
	public function test_ack_classification_retains_types_and_never_emits_untrusted_payloads() {
		$row=$this->diagnostic_run('$inputs=array(array("affected"=>1,"errno"=>0),array("affected"=>false,"errno"=>null),array("affected"=>0,"errno"=>1064),array("affected"=>1.0,"errno"=>"0"),array("affected"=>null,"errno"=>1142),array("affected"=>"private-value","errno"=>9999),array("errno"=>0,"affected"=>1),"private-sql",new stdClass());echo serialize(array_map("adbb_ack",$inputs));');
		$this->assertSame(array('shape'=>true,'affected'=>'one','errno'=>'0'),$row[0]);$this->assertSame(array('shape'=>true,'affected'=>'false','errno'=>'null'),$row[1]);$this->assertSame('zero',$row[2]['affected']);$this->assertSame('1064',$row[2]['errno']);
		$this->assertSame(array('shape'=>true,'affected'=>'other','errno'=>'other'),$row[3]);$this->assertSame('null',$row[4]['affected']);$this->assertSame('1142',$row[4]['errno']);$this->assertSame('other',$row[5]['errno']);
		foreach(array(6,7,8) as $index)$this->assertSame(array('shape'=>false,'affected'=>'other','errno'=>'other'),$row[$index]);$this->assertStringNotContainsString('private-',serialize($row));
	}
}
