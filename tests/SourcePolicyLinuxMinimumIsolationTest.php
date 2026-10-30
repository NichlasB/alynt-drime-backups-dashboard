<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-linux-minimum-fixtures.php';

/** Actual helper in PHP -n; only assertions, not crashes, count as killed controls. */
final class SourcePolicyLinuxMinimumIsolationTest extends TestCase {
	/** @dataProvider controls */
	public function test_each_semantic_guard_control_is_assertion_killed($mutation,$status) {
		$root=dirname(__DIR__);$path=$root.'/scripts/manual/source-policy-linux-minimum-tuple.php';$hash=hash_file('sha256',$path);
		$disabled='fsockopen,pfsockopen,stream_socket_client,stream_socket_server,exec,shell_exec,system,passthru,proc_open,popen';
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
		$allowed=array('array','is_array','count','array_keys','array_key_exists','is_string','strlen','preg_match','is_int','hash','json_encode','template','consistent','digest','correlates','shape','transcript');
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
}
