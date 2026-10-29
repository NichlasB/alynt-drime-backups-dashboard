<?php
use PHPUnit\Framework\TestCase;

/** Fixed trust-source policy only; tests never call curl_exec or acquire artifacts. */
final class SourcePolicyMinimumNativeCaTest extends TestCase {
	protected function setUp():void {require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-acquire.php';}
	public function test_reviewed_backend_is_exact_and_missing_capability_never_falls_back() {
		$this->assertTrue(adbmin_native_ca_profile('Windows','8.17.0','OpenSSL/3.5.4',true));
		foreach(array(array('Linux','8.17.0','OpenSSL/3.5.4',true),array('Windows','8.16.0','OpenSSL/3.5.4',true),array('Windows','8.17.0','Schannel',true),array('Windows','8.17.0','OpenSSL/3.5.4',false),array('Windows','8.17.0','OpenSSL/3.5.4',1),array('Windows','8.17.0','OpenSSL/3.5.4','true'),array(null,'8.17.0','OpenSSL/3.5.4',true)) as $bad)$this->assertFalse(adbmin_native_ca_profile(...$bad));
	}
	public function test_actual_request_keeps_all_tls_and_identity_boundaries() {
		$source=file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-minimum-acquire.php');$compact=preg_replace('/[ \t]+/',' ',$source);
		$this->assertLessThan(strpos($source,'curl_init('),strpos($source,'if ( ! adbmin_native_ca_profile('));
		foreach(array("CURLOPT_SSL_OPTIONS => constant( 'CURLSSLOPT_NATIVE_CA' )","CURLOPT_SSL_VERIFYPEER => true","CURLOPT_SSL_VERIFYHOST => 2","CURLOPT_FOLLOWLOCATION => false","CURLOPT_MAXREDIRS => 0","CURLOPT_NETRC => CURL_NETRC_IGNORED","'https' : CURLPROTO_HTTPS") as $guard)$this->assertStringContainsString($guard,$compact);
		foreach(array('CURLSSLOPT_NO_REVOKE','CURLSSLOPT_REVOKE_BEST_EFFORT','CURLSSLOPT_ALLOW_BEAST','CURLSSLOPT_AUTO_CLIENT_CERT','CURLOPT_CAINFO','ini_set(','putenv(') as $forbidden)$this->assertStringNotContainsString($forbidden,$source);
		$this->assertStringContainsString("! adbmin_php_zip( \$bytes )",$source);$this->assertStringContainsString('finally {',$source);$this->assertStringContainsString('$owned->cleanup();',$source);
	}
	/** @dataProvider semantic_controls */
	public function test_required_backend_capability_predicates_are_assertion_killed($mutation,$status) {
		$root=dirname(__DIR__);$file=$root.'/scripts/manual/source-policy-minimum-acquire.php';$digest=hash_file('sha256',$file);
		$find=array('os'=>"'Windows' === \$os && ",'version'=>"'8.17.0' === \$version && ",'ssl'=>"'OpenSSL/3.5.4' === \$ssl && ",'capability'=>' && true === $native_ca');
		$code='$s=file_get_contents($argv[1]);$find='.var_export($find,true).';if("none"!==$argv[2]){$s=str_replace($find[$argv[2]],"",$s,$n);if(1!==$n)exit(2);}$s=str_replace("__DIR__",var_export(dirname($argv[1]),true),substr($s,5));eval($s);';
		$code.='$actual=array(adbmin_native_ca_profile("Windows","8.17.0","OpenSSL/3.5.4",true),adbmin_native_ca_profile("Linux","8.17.0","OpenSSL/3.5.4",true),adbmin_native_ca_profile("Windows","8.16.0","OpenSSL/3.5.4",true),adbmin_native_ca_profile("Windows","8.17.0","Schannel",true),adbmin_native_ca_profile("Windows","8.17.0","OpenSSL/3.5.4",false));$ok=array(true,false,false,false,false)===$actual;echo $ok?"contract-pass":"contract-killed";exit($ok?0:1);';
		$process=proc_open(array(PHP_BINARY,'-n','-r',$code,$file,$mutation),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame($status,proc_close($process),$err);$this->assertSame('',$err);$this->assertSame(0===$status?'contract-pass':'contract-killed',$out);$this->assertSame($digest,hash_file('sha256',$file));
	}
	public static function semantic_controls(){return array(array('none',0),array('os',1),array('version',1),array('ssl',1),array('capability',1));}
	public function test_actual_option_setup_guard_accepts_success_and_refuses_failure_without_a_connection() {
		$source=file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-minimum-acquire.php');$this->assertSame(1,preg_match('/\$configured\s*=\s*curl_setopt_array\(/',$source,$match,PREG_OFFSET_CAPTURE));$begin=$match[0][1];$end=strpos($source,"\n\ttry {",$begin);$this->assertNotFalse($end);$block=substr($source,$begin,$end-$begin);
		$code='function curl_setopt_array($handle,$options){return $GLOBALS["option_result"];}';preg_match_all('/\b(?:CURLOPT_[A-Z_]+|CURL_NETRC_IGNORED)\b/',$block,$constants);foreach(array_unique($constants[0]) as $index=>$name)$code.='define('.var_export($name,true).','.($index+10).');';
		$code.='function constant_native_ca(){return 16;}$curl=new stdClass;$protocol_option=1;$protocol_value="https";$bytes="";$actual=array();';
		$block=str_replace("constant( 'CURLSSLOPT_NATIVE_CA' )",'constant_native_ca()',$block);
		$code.='foreach(array(true,false) as $result){$GLOBALS["option_result"]=$result;try{'.$block.'$actual[]=true;}catch(RuntimeException $error){if("fixed-request-options-refused"!==$error->getMessage())exit(2);$actual[]=false;}}echo serialize($actual);';
		$process=proc_open(array(PHP_BINARY,'-n','-r',$code),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame(0,proc_close($process),$err);$this->assertSame('',$err);$this->assertSame(array(true,false),unserialize($out,array('allowed_classes'=>false)));
	}
}
