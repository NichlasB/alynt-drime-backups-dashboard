<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-linux-minimum-fixtures.php';

/** Actual helper in PHP -n; only assertions, not crashes, count as killed controls. */
final class SourcePolicyLinuxMinimumIsolationTest extends TestCase {
	/** @dataProvider controls */
	public function test_each_semantic_guard_control_is_assertion_killed($mutation,$status) {
		$root=dirname(__DIR__);$path=$root.'/scripts/manual/source-policy-linux-minimum-tuple.php';$hash=hash_file('sha256',$path);
		$disabled='fsockopen,pfsockopen,stream_socket_client,stream_socket_server,exec,shell_exec,system,passthru,proc_open,popen,json_encode,json_decode';
		// Quote the INI value itself: an unquoted Windows semicolon starts an INI comment.
		$paths='open_basedir="'.$root.'/scripts/manual'.PATH_SEPARATOR.$root.'/tests/support"';
		$p=proc_open(array(PHP_BINARY,'-n','-d','allow_url_fopen=0','-d','disable_functions='.$disabled,'-d',$paths,$root.'/tests/support/source-policy-linux-minimum-controls.php','--synthetic-linux-minimum',$root,$mutation),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame($status,proc_close($p),$mutation.':'.substr($err.$out,0,2048));$this->assertSame('',$err);$this->assertSame(0===$status?'contract-pass':'assertion-killed',$out);
		$this->assertSame($hash,hash_file('sha256',$path),'Actual source bytes must never be mutated.');
	}
	public static function controls() {
		$cases=array(array('none',0));foreach(array('always-unavailable','shape-count','required-key','digest-gate','wire-gate','parent-gate','event','tuple-hash','pid-type','pid-low','pid-high') as $name)$cases[]=array($name,1);
		foreach(array_keys(linux_minimum_tuple_fixture()) as $key)$cases[]=array('tuple-'.$key,1);
		foreach(array('head','source_image','run') as $key){$cases[]=array('hex-'.$key,1);$cases[]=array('length-'.$key,1);}
		foreach(array('head','source_image','run','pid') as $key)$cases[]=array('parent-'.$key,1);return $cases;
	}
	public function test_helper_call_surface_is_pure_and_not_in_production_imports() {
		$root=dirname(__DIR__);$source=file_get_contents($root.'/scripts/manual/source-policy-linux-minimum-tuple.php');
		$allowed=array('array','is_array','count','array_keys','array_key_exists','is_string','strlen','preg_match','is_int','hash','implode','template','consistent','digest','canonical','correlates','shape','transcript');
		$tokens=array_values(array_filter(token_get_all($source),function($t){return !is_array($t)||!in_array($t[0],array(T_WHITESPACE,T_COMMENT,T_DOC_COMMENT),true);}));
		foreach($tokens as $i=>$token){
			if(!is_array($token)){ $this->assertNotSame('`',$token);continue; }
			$this->assertNotContains($token[0],array(T_EVAL,T_ECHO,T_PRINT,T_EXIT,T_INCLUDE,T_INCLUDE_ONCE,T_REQUIRE,T_REQUIRE_ONCE,T_NEW,T_OBJECT_OPERATOR,T_GLOBAL));
			if(T_STRING===$token[0]&&'('===($tokens[$i+1]??null)&&(!is_array($tokens[$i-1])||T_FUNCTION!==$tokens[$i-1][0]))$this->assertContains(strtolower($token[1]),$allowed);
			if(T_VARIABLE===$token[0])$this->assertNotSame('(',$tokens[$i+1]??null,'No dynamic invocation');
		}
		$this->assertStringNotContainsString('source-policy-linux-minimum-tuple',file_get_contents($root.'/alynt-drime-backups-dashboard.php'));
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/includes',FilesystemIterator::SKIP_DOTS)) as $file)if($file->isFile()&&'php'===$file->getExtension())$this->assertStringNotContainsString('source-policy-linux-minimum-tuple',file_get_contents($file->getPathname()));
	}
	public function test_failure_diagnostics_are_bounded_and_do_not_expose_exception_text() {
		require_once __DIR__.'/support/source-policy-linux-minimum-controls.php';
		foreach(array('fixture',str_repeat('SECRET',1000)) as $phase){$message=linux_minimum_failure_diagnostic($phase,new RuntimeException('SECRET path SQL credentials'));
			$this->assertLessThanOrEqual(128,strlen($message));$this->assertStringNotContainsString('SECRET',$message);$this->assertMatchesRegularExpression('/\AUnexpected synthetic control failure phase:(fixture|unknown) kind:RuntimeException json_encode:[01] json_decode:[01]\z/',$message);}
		$this->assertStringContainsString('kind:other',linux_minimum_failure_diagnostic('contract',new Exception('SECRET')));
	}
	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_fixed_encoder_source_guards_reject_unsafe_edits_and_kill_bypasses() {
		$path=dirname(__DIR__).'/scripts/manual/source-policy-linux-minimum-tuple.php';$hash=hash_file('sha256',$path);$source=file_get_contents($path);$killed=0;
		$keyGuard="! is_string( \$key ) || 1 !== preg_match( '/\\A[a-z_]{1,32}\\z/', \$key )";
		$valueGuard="is_string( \$value ) && 1 === preg_match( '/\\A[A-Za-z0-9_.: -]{1,128}\\z/', \$value )";
		$cases=array(array('key','Linux',false),array('key','Linux',true),array('value','bad"quote',false),array('value','bad"quote',true),array('value',"bad\\slash",false),array('value',"bad\nline",false),array('value',str_repeat('x',129),false),array('value',true,false),array('value',array(),false));
		foreach($cases as $index=>$case){list($kind,$value,$bypass)=$case;
			$replacement=var_export('key'===$kind?'bad-key':'platform',true).' => '.var_export($value,true);
			$variant=str_replace("'platform'      => 'Linux'",$replacement,$source,$count);$this->assertSame(1,$count);
			if($bypass){$variant=str_replace('key'===$kind?$keyGuard:$valueGuard,'key'===$kind?'false':'is_string( $value )',$variant,$count);$this->assertSame(1,$count);}
			$name='Alynt_Drime_Backups_Dashboard_Test_Linux_Tuple_Variant_'.$index;
			$variant=str_replace('Alynt_Drime_Backups_Dashboard_Linux_Minimum_Tuple',$name,$variant,$count);$this->assertSame(1,$count);eval(substr($variant,5));
			$method=new ReflectionMethod($name,'canonical');if(PHP_VERSION_ID<80100)$method->setAccessible(true);$bytes=$method->invoke(null);$failed=false;
			try{$this->assertFalse($bytes);}catch(\PHPUnit\Framework\AssertionFailedError $error){if(!$bypass)throw $error;$failed=true;$killed++;}
			$this->assertSame($bypass,$failed,'Only assertion failure on an actual bypass counts as a killed control.');
		}
		$this->assertSame(2,$killed);$this->assertSame($hash,hash_file('sha256',$path));
	}
}
