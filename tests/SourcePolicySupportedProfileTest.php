<?php
use PHPUnit\Framework\TestCase;

/** Pure fixed candidate DATA and actual harness wiring; never core/native execution. */
final class SourcePolicySupportedProfileTest extends TestCase {
	public function test_fixed_core_rows_and_no_unknown_version_fallback() {
		require_once dirname(__DIR__).'/scripts/manual/source-policy-supported-profile.php';
		$minimum=adbp_profile('6.0');$windows=adbp_profile('7.1.3');
		$this->assertSame('cc101b64012b16d087780657a2b828ccd7794a63',$minimum['commit']);
		$this->assertSame('fc9832bef919c2a08541248db23af90d14ffb082',$windows['commit']);
		$this->assertSame('wp-db.php',$minimum['core_file']);$this->assertSame('functions.php',$minimum['parser_api_file']);
		$this->assertSame('class-wpdb.php',$windows['core_file']);$this->assertSame('load.php',$windows['parser_api_file']);
		$this->assertCount(11,$minimum['pins']);$this->assertCount(12,$windows['pins']);
		foreach(array('6','6.0.0','7.1.4','7.1.3-dev',null,6,array(),new stdClass()) as $unknown)$this->assertFalse(adbp_profile($unknown));
		foreach(array($minimum,$windows) as $row){
			$this->assertSame('load.php',$row['cache_api_file']);$this->assertSame('class-wp-object-cache.php',$row['cache_file']);
			$this->assertArrayHasKey('version.php',$row['pins']);$this->assertSame('version.php',$row['includes'][0]);
			foreach($row['includes'] as $file)$this->assertArrayHasKey($file,$row['pins']);
			foreach($row['pins'] as $file=>$hash){$this->assertMatchesRegularExpression('/\A[a-z-]+\.php\z/',$file);$this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/',$hash);}
		}
		$this->assertNotSame($minimum['pins']['version.php'],$windows['pins']['version.php']);
		$this->assertArrayNotHasKey('class-wp-filter-sentinel.php',$minimum['pins']);$this->assertArrayHasKey('class-wp-filter-sentinel.php',$windows['pins']);
	}
	public function test_source_only_runtime_candidates_are_exact_and_never_authority() {
		require_once dirname(__DIR__).'/scripts/manual/source-policy-supported-profile.php';
		foreach(array('6.0'=>array('7.4.33',0,'php7.dll'),'7.1.3'=>array('8.5.12',3,'php8.dll')) as $core=>$expected){
			$row=adbp_profile($core);$this->assertSame($expected,array($row['php'],$row['initial_mode'],$row['runtime_module']));
			$this->assertTrue(adbp_initial_mode($core,$expected[0],$expected[1]));
			foreach(array(array($core,$expected[0],(string)$expected[1]),array($core,$expected[0],1),array($core,'8.5.1',$expected[1]),array('7.1.4',$expected[0],$expected[1]),array($core,$expected[0],0===$expected[1]?3:0)) as $bad)$this->assertFalse(adbp_initial_mode(...$bad));
		}
		$this->assertFalse(adbp_initial_mode('6.0',null,0));$this->assertFalse(adbp_initial_mode(array(),'7.4.33',0));
	}
	public function test_manifest_and_mode_helpers_need_no_json_or_native_extension() {
		$helper=dirname(__DIR__).'/scripts/manual/source-policy-supported-profile.php';
		$code='require $argv[1];$min=adbp_profile("6.0");$win=adbp_profile("7.1.3");echo serialize(array(count($min["pins"]),count($win["pins"]),adbp_initial_mode("6.0","7.4.33",0),adbp_initial_mode("7.1.3","8.5.12",3),function_exists("json_encode"),extension_loaded("mysqli"),extension_loaded("pdo_mysql")));';
		$process=proc_open(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode','-r',$code,$helper),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);$this->assertIsResource($process);fclose($pipes[0]);
		$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame(0,proc_close($process),$err);$this->assertSame('',$err);
		$this->assertSame(array(11,12,true,true,false,false,false),unserialize($out,array('allowed_classes'=>false)));
	}
	/** @dataProvider semantic_controls */
	public function test_required_mode_and_php_predicates_are_killed_by_the_same_contract($mutation,$expected_status) {
		$helper=dirname(__DIR__).'/scripts/manual/source-policy-supported-profile.php';
		$code='$source=file_get_contents($argv[1]);$source=str_replace("__DIR__",var_export(dirname($argv[1]),true),substr($source,5));';
		$code.='$find=array("php"=>\'$profile["php"] === $php && \',"mode"=>\' && $profile["initial_mode"] === $mode\');$find=str_replace(chr(34),chr(39),$find);if("none"!==$argv[2]){$source=str_replace($find[$argv[2]],"",$source,$count);if(1!==$count)exit(2);}eval($source);';
		$code.='$actual=array(adbp_initial_mode("6.0","7.4.33",0),adbp_initial_mode("6.0","8.5.12",0),adbp_initial_mode("6.0","7.4.33",3));$pass=array(true,false,false)===$actual;echo $pass?"contract-pass":"contract-killed";exit($pass?0:1);';
		$process=proc_open(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode','-r',$code,$helper,$mutation),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);$this->assertIsResource($process);fclose($pipes[0]);
		$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame($expected_status,proc_close($process),$err);$this->assertSame('',$err);$this->assertSame($expected_status?'contract-killed':'contract-pass',$out);
	}
	public static function semantic_controls(){return array(array('none',0),array('php',1),array('mode',1));}
	public function test_windows_source_composition_is_fixed_and_existing_native_gates_stay_closed() {
		$root=dirname(__DIR__);$source=file_get_contents($root.'/scripts/manual/source-policy-bootstrap-owned-proof.php');
		$this->assertStringContainsString("adbp_profile( '7.1.3' )",$source);
		$this->assertStringContainsString("\$profile['parser_api_file']",$source);$this->assertStringNotContainsString("'parser_api_file' => ABSPATH . WPINC . '/functions.php'",$source);
		$this->assertStringContainsString("\$profile['pins']",$source);$this->assertStringContainsString("\$profile['includes']",$source);
		$this->assertStringContainsString("'8.5.12' !== PHP_VERSION",$source);$this->assertStringContainsString("'Windows' !== PHP_OS_FAMILY",$source);
		$this->assertLessThan(strpos($source,'source-policy-supported-profile.php'),strpos($source,"'8.5.12' !== PHP_VERSION"));
		$this->assertLessThan(strpos($source,'hash_file('),strpos($source,"\$profile['pins']"));
		$this->assertLessThan(strpos($source,'require_once ABSPATH'),strpos($source,'hash_file('));
		foreach(array('adapter','ambient') as $kind){$worker=file_get_contents($root.'/scripts/manual/source-policy-'.$kind.'-owned-proof.php');$this->assertStringContainsString("\$profile['runtime_module']",$worker);$this->assertStringNotContainsString('mysqli_report(',$worker);}
		$linux=file_get_contents($root.'/scripts/ci/source-policy-linux-qualification.php');
		$this->assertStringContainsString("require_once __DIR__ . '/../manual/source-policy-proof-reflection.php'",$linux);
		$this->assertLessThan(strpos($linux,'source-policy-proof-reflection.php'),strpos($linux,"'no injected entry'"));
	}
	public function test_actual_preload_hash_guard_accepts_real_repo_bytes_and_refuses_changed_or_missing_bytes() {
		$root=dirname(__DIR__);$source=file_get_contents($root.'/scripts/manual/source-policy-bootstrap-owned-proof.php');
		$this->assertSame(1,preg_match('/\tforeach \( \$pins as \$file => \$hash \) \{\n.*?\n\t\}/s',$source,$match));
		$block=$match[0];$this->assertStringContainsString('hash_equals(',$block);
		$code='define("ABSPATH",$argv[1]."/");define("WPINC","tests");function adbb_check($ok,$label){if(!$ok)throw new RuntimeException("guard");}$file="SourcePolicySupportedProfileTest.php";$digest=hash_file("sha256",ABSPATH.WPINC."/".$file);$out=array();';
		foreach(array('positive','changed','missing') as $case){$code.='$pins=array('.var_export('missing'===$case?'missing-profile-fixture.php':'SourcePolicySupportedProfileTest.php',true).'=>'.('changed'===$case?'str_repeat("0",64)':'$digest').');try{'.$block.';$out[]=true;}catch(RuntimeException $error){$out[]=false;}';}
		$code.='echo serialize($out);';
		$process=proc_open(array(PHP_BINARY,'-n','-r',$code,$root),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);$this->assertIsResource($process);fclose($pipes[0]);
		$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame(0,proc_close($process),$err);$this->assertSame('',$err);
		$this->assertSame(array(true,false,false),unserialize($out,array('allowed_classes'=>false)),'Actual pre-load guard must not always refuse or accept altered source.');
	}
}
