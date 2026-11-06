<?php
/** Extension-free API model only: no genuine cookie, WordPress, DB or private state injection. */
use PHPUnit\Framework\Assert as A;
if (isset($argv[0]) && realpath($argv[0]) === __FILE__) {
if(PHP_SAPI!=='cli'||count($argv)!==3||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
require dirname(__DIR__,2).'/vendor/autoload.php';
define('ADB_M_REQUEST_PROOF',true);define('HOUR_IN_SECONDS',3600);define('DAY_IN_SECONDS',86400);define('LOGGED_IN_COOKIE','owned-model');
$GLOBALS['model_sessions']=array();$GLOBALS['model_counter']=0;$GLOBALS['model_validated']=null;$GLOBALS['model_established']=array();
function adbg_check($condition,$label){A::assertTrue($condition,$label);}
class WP_Session_Tokens {
	private $user;
	private function __construct($user){$this->user=$user;}
	public static function get_instance($user){return new self($user);}
	public function create($expiration){$token=str_pad((string)++$GLOBALS['model_counter'],43,'t');$GLOBALS['model_sessions'][$this->user][$token]=$expiration;return$token;}
	public function verify($token){return isset($GLOBALS['model_sessions'][$this->user][$token])&&$GLOBALS['model_sessions'][$this->user][$token]>=time();}
	public function destroy($token){unset($GLOBALS['model_sessions'][$this->user][$token]);}
}
function wp_generate_auth_cookie($user,$expiration,$scheme,$token){A::assertSame('logged_in',$scheme);$name=array(1=>'owned_one',2=>'owned_two',3=>'owned_low')[$user];return$name.'|'.$expiration.'|'.$token.'|'.hash('sha256',$user.'|'.$expiration.'|'.$token);}
function wp_validate_auth_cookie($cookie,$scheme){
	A::assertSame('logged_in',$scheme);$GLOBALS['model_validated']=null;$parts=explode('|',$cookie);if(count($parts)!==4)return false;
	list($name,$expiration,$token,$hash)=$parts;$id=array('owned_one'=>1,'owned_two'=>2,'owned_low'=>3)[$name]??0;
	if(!$id||$expiration+HOUR_IN_SECONDS<time()||!hash_equals(hash('sha256',$id.'|'.$expiration.'|'.$token),$hash)||!WP_Session_Tokens::get_instance($id)->verify($token))return false;
	$GLOBALS['model_validated']=$id;return$id;
}
function wp_set_current_user($id){A::assertSame($GLOBALS['model_validated'],$id,'No unvalidated ID shortcut.');$GLOBALS['model_established'][]=$id;$GLOBALS['current_user']=(object)array('ID'=>$id);return$GLOBALS['current_user'];}
function wp_get_current_user(){return$GLOBALS['current_user'];}
function wp_cache_flush(){return true;}
function wp_get_session_token(){return explode('|',$_COOKIE[LOGGED_IN_COOKIE])[2];}
function wp_nonce_tick(){return ceil(time()/(DAY_IN_SECONDS/2));}
function wp_hash($data,$scheme){return hash('sha256',$scheme.'|'.$data);}
function wp_create_nonce($action){return substr(wp_hash(wp_nonce_tick().'|'.$action.'|'.wp_get_current_user()->ID.'|'.wp_get_session_token(),'nonce'),-12,10);}
function model_verify_nonce($nonce,$action){if(!is_string($nonce)||$nonce==='')return false;foreach(array(0,1)as$age){$hash=substr(wp_hash((wp_nonce_tick()-$age).'|'.$action.'|'.wp_get_current_user()->ID.'|'.wp_get_session_token(),'nonce'),-12,10);if(hash_equals($hash,$nonce))return true;}return false;}
try {
	require $argv[2];$scenario=$argv[1];list($id,$request)=adbg_request($scenario);
	if(strpos($scenario,'cookie-')===0){A::assertFalse($id);A::assertFalse(isset($GLOBALS['current_user']));A::assertSame(array(),$GLOBALS['model_established']);}
	else {A::assertSame($scenario==='incapable'?3:1,$id);A::assertSame($id,wp_get_current_user()->ID);$valid=model_verify_nonce($request['_wpnonce'],'alynt_drime_backups_dashboard_update_source_policy');A::assertSame(strpos($scenario,'nonce-')!==0,$valid);}
	foreach($GLOBALS['model_sessions']as$sessions)A::assertLessThanOrEqual(2,count($sessions));
	A::assertFalse(function_exists('mysqli_init'));echo 'checks:',A::getCount(),"\n";
}catch(Throwable $error){fwrite(STDERR,get_class($error).' '.$error->getMessage()."\n");exit(1);}
}
