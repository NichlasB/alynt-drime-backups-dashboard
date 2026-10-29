<?php
use PHPUnit\Framework\TestCase;

/** Executes existing trusted PHP against actual pure source, not a native proof. */
final class SourcePolicyMinimumHostIsolationTest extends TestCase {
	/** @dataProvider controls */
	public function test_extension_free_positive_contract_and_assertion_killed_controls($mutation,$status) {
		$root=dirname(__DIR__);$file=$root.'/scripts/manual/source-policy-minimum-host-admission.php';$hash=hash_file('sha256',$file);
		$p=proc_open(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode',$root.'/tests/support/source-policy-minimum-host-controls.php','--synthetic-host-control',$root,$mutation),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);$this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame($status,proc_close($p),$mutation.':'.$err);$this->assertSame('',$err);$this->assertSame(0===$status?'contract-pass':'contract-killed',$out);$this->assertSame($hash,hash_file('sha256',$file));
	}
	public static function controls(){return array_map(function($v){return array($v,'none'===$v?0:1);},array('none','profile','decision','image','source','module-path','module-comparison','identity','clock','observer-clock','release','cleanup','sticky','absence'));}
	public function test_closed_call_surface_has_no_discovery_io_launcher_or_hidden_handle_factory() {
		$source=file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-minimum-host-admission.php');$tokens=array_values(array_filter(token_get_all($source),function($t){return !is_array($t)||!in_array($t[0],array(T_WHITESPACE,T_COMMENT,T_DOC_COMMENT),true);}));
		$functions=array('is_array','is_int','is_string','strlen','preg_match','substr','strpos','strtolower','explode','array_keys','count','range','in_array','isset','hash','implode','ksort','invalidargumentexception');$methods=array('hex','path','profile_digest','binding','module_digest','reject','time','worker','controller','cleanup','snapshot');
		foreach($tokens as $i=>$t){$this->assertNotSame('`',$t);if(!is_array($t))continue;$next=$tokens[$i+1]??null;$prev=$tokens[$i-1]??null;$this->assertNotContains($t[0],array(T_EVAL,T_ECHO,T_PRINT,T_EXIT,T_INCLUDE,T_INCLUDE_ONCE,T_REQUIRE,T_REQUIRE_ONCE,T_GLOBAL,T_YIELD,T_YIELD_FROM,T_INLINE_HTML,T_CLOSE_TAG,T_OPEN_TAG_WITH_ECHO));
			if(T_VARIABLE===$t[0]){$this->assertNotContains($t[1],array('$_GET','$_POST','$_REQUEST','$_SERVER','$_ENV','$_COOKIE','$_SESSION','$_FILES','$GLOBALS','$argv','$argc'));$this->assertNotSame('(',$next);if(is_array($next)){$this->assertNotSame(T_DOUBLE_COLON,$next[0]);if(T_OBJECT_OPERATOR===$next[0])$this->assertSame('$this',$t[1]);}}
			if(T_NEW===$t[0])$this->assertSame('InvalidArgumentException',$next[1]);if(T_STRING===$t[0]&&is_array($next)&&T_DOUBLE_COLON===$next[0])$this->assertSame('self',$t[1]);
			if(T_STRING===$t[0]&&'('===$next&&(!is_array($prev)||T_FUNCTION!==$prev[0])){$member=is_array($prev)&&in_array($prev[0],array(T_DOUBLE_COLON,T_OBJECT_OPERATOR),true);$this->assertContains(strtolower($t[1]),$member?$methods:$functions);}
		}
		$this->assertStringNotContainsString('source-policy-minimum-host-admission',file_get_contents(dirname(__DIR__).'/alynt-drime-backups-dashboard.php'));$this->assertStringNotContainsString('require',$source);$this->assertStringNotContainsString('Minimum_Launch_Data',$source);$this->assertStringNotContainsString('Minimum_Preflight_Transcript',$source);
	}
}
