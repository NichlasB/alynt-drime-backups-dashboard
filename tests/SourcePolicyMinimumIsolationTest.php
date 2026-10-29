<?php
use PHPUnit\Framework\TestCase;

/** The same positive/refusal contract must kill removal of each required guard. */
final class SourcePolicyMinimumIsolationTest extends TestCase {
	/** @dataProvider controls */
	public function test_extension_free_actual_helpers_and_in_memory_semantic_controls($mutation,$status) {
		$root=dirname(__DIR__);$files=array();foreach(array('artifacts','pe','tree','runner') as $name){$path=$root.'/scripts/manual/source-policy-minimum-'.$name.'.php';$files[$path]=hash_file('sha256',$path);}
		$process=proc_open(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode',$root.'/tests/support/source-policy-minimum-controls.php','--synthetic-minimum-control',$root,$mutation),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame($status,proc_close($process),$err);$this->assertSame('',$err);$this->assertSame(0===$status?'contract-pass':'contract-killed',$out);
		foreach($files as $path=>$digest)$this->assertSame($digest,hash_file('sha256',$path));
	}
	public static function controls(){return array(array('none',0),array('case-fold',1),array('digest',1),array('machine',1),array('frame',1),array('mode',1));}
	public function test_source_call_surface_has_no_io_loader_or_launcher() {
		$allowed=array('is_string','is_int','is_array','strlen','str_repeat','preg_match','count','explode','in_array','substr','strpos','strrpos','strtolower','array_keys','hash_equals','hash','sha1','pack','unpack','uksort','invalidargumentexception');
		foreach(array('artifacts','pe','tree','runner') as $name){
			$source=file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-minimum-'.$name.'.php');$tokens=array_values(array_filter(token_get_all($source),function($t){return !is_array($t)||!in_array($t[0],array(T_WHITESPACE,T_COMMENT,T_DOC_COMMENT),true);}));
			foreach($tokens as $index=>$token){if(!is_array($token))continue;
				$this->assertNotContains($token[0],array(T_EVAL,T_ECHO,T_PRINT,T_EXIT,T_INCLUDE,T_INCLUDE_ONCE),$name);
				if(T_STRING===$token[0]&&'('===($tokens[$index+1]??null)&&(!is_array($tokens[$index-1])||T_FUNCTION!==$tokens[$index-1][0]))$this->assertTrue(0===strpos($token[1],'adbmin_')||in_array(strtolower($token[1]),$allowed,true),$token[1]);
				if(T_VARIABLE===$token[0]&&'('===($tokens[$index+1]??null))$this->assertSame('$encode',$token[1]);
			}
			$this->assertStringNotContainsString('getenv(',$source);$this->assertStringNotContainsString('WP_CONTENT_DIR',$source);
		}
		$this->assertStringNotContainsString('source-policy-minimum-',file_get_contents(dirname(__DIR__).'/alynt-drime-backups-dashboard.php'));
	}
}
