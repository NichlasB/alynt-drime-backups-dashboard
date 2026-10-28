<?php
/** ADBC2 test-only API model with explicit synthetic mysqlnd statistics. */
if ( defined('ADB_POLICY_ISOLATED') && !extension_loaded('mysqli') ) {
define('MYSQLI_OPT_CONNECT_TIMEOUT',0); define('MYSQLI_OPT_READ_TIMEOUT',11); define('MYSQLI_OPT_LOCAL_INFILE',8);
class mysqli { public $closed=false,$connected=false,$errno=0,$options=array(),$charset='utf8mb4',$collation='utf8mb4_unicode_520_ci',$mode='NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION'; }
class mysqli_result { public $rows,$position=0,$freed=false; public function __construct($rows){$this->rows=$rows; ++$GLOBALS['boot_counts']['results'];} }
function boot_event($name){
	$GLOBALS['boot_events'][]=$name;
	if('close'!==$name){
		$connection=$GLOBALS['boot_connection']; PHPUnit\Framework\Assert::assertFalse($connection->ready,'No early active publication.');
		PHPUnit\Framework\Assert::assertInstanceOf(WP_Error::class,$connection->admit_profile()); PHPUnit\Framework\Assert::assertFalse($connection->query('SELECT never-dispatched'));
		$refused=false;try{$connection->execute('SELECT never-dispatched');}catch(RuntimeException $error){$refused=true;} PHPUnit\Framework\Assert::assertTrue($refused,'No prequalification public execution.');
	}
	if($GLOBALS['boot_fault']===$name.'-throw') throw new RuntimeException('owned-private-driver-material');
	if($GLOBALS['boot_fault']===$name.'-warning') trigger_error('owned-private-driver-material',E_USER_WARNING);
	if(isset($GLOBALS['boot_after'][$name])){$after=$GLOBALS['boot_after'][$name]; unset($GLOBALS['boot_after'][$name]); $after();}
	if($GLOBALS['boot_fault']===$name.'-error' && isset($GLOBALS['boot_handle'])) $GLOBALS['boot_handle']->errno=1234;
}
function mysqli_get_client_stats(){
 $c=$GLOBALS['boot_counts'];$h=$GLOBALS['boot_handle']??null;$connected=$h&&!empty($h->connected);$post=$GLOBALS['boot_connection']->physical??0;
 $values=array((string)(int)$connected,(string)($c['connect']-(int)$connected),(string)($c['commands']+$post),(string)($c['results']+$post),(string)($c['freed']+$GLOBALS['adbc2_post_freed']),'0','0','0','0',$connected?'1':'0',$connected?($h->closed?'18446744073709551615':'1'):'0');
 if('stats'===$GLOBALS['adbc2_fault'])$values[0]='2';
 return array_combine(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::counters(),$values);
}
function mysqli_init(){++$GLOBALS['boot_counts']['allocate']; boot_event('allocate'); if('allocate-false'===$GLOBALS['boot_fault'])return false; $GLOBALS['boot_handle']=isset($GLOBALS['boot_alias_handle'])?$GLOBALS['boot_alias_handle']:new mysqli(); return $GLOBALS['boot_handle'];}
function mysqli_options($handle,$option,$value){
	PHPUnit\Framework\Assert::assertSame($GLOBALS['boot_handle'],$handle);
	$expected=array(array(MYSQLI_OPT_CONNECT_TIMEOUT,2),array(MYSQLI_OPT_READ_TIMEOUT,3),array(MYSQLI_OPT_LOCAL_INFILE,0));
	PHPUnit\Framework\Assert::assertSame($expected[$GLOBALS['boot_counts']['options']],array($option,$value));
	++$GLOBALS['boot_counts']['options']; boot_event('option'); return 'option-false'!==$GLOBALS['boot_fault'];
}
function mysqli_real_connect($handle,$host,$user,$password,$database,$port,$socket,$flags){
	++$GLOBALS['boot_counts']['connect']; boot_event('connect');
	PHPUnit\Framework\Assert::assertSame(array('127.0.0.1','owned','owned-private-password','isolated',10004,null,0),array($host,$user,$password,$database,$port,$socket,$flags));
	if('connect-false'===$GLOBALS['boot_fault'])return false; $handle->connected=true; return true;
}
function mysqli_get_client_info(){return 'bad-client'===$GLOBALS['boot_fault']?'unknown':'mysqlnd synthetic';}
function mysqli_get_server_info($handle){unset($handle); return 'bad-server'===$GLOBALS['boot_fault']?'unknown':'10.6.23-MariaDB';}
function mysqli_errno($handle){if($handle->closed)throw new Error('closed');return $handle->errno;}
function mysqli_query($handle,$sql){
	PHPUnit\Framework\Assert::assertTrue($handle->connected && !$handle->closed);
	++$GLOBALS['boot_counts']['commands']; $handle->errno=0;
	if(0===strpos($sql,'SET SESSION')){$step='limits'; $rows=null;}
	elseif(0===strpos($sql,'isolated:')){list($key)=explode('|',$sql,2); list($template)=$GLOBALS['boot_connection']->prepared[$key]; $step=strpos($template,'information_schema.TABLES')!==false?'table':(strpos($template,'information_schema.COLUMNS')!==false?'columns':'indexes'); $rows=boot_rows($step);}
	elseif(strpos($sql,'DATABASE()')!==false){$step=strpos($sql,'statement_limit')===false?'identity':'session'; $rows=boot_rows($step);}
	else {$step='mode'; $rows=array(array('mode'=>$handle->mode));}
	boot_event($step);
	if(isset($GLOBALS['boot_rewrite'][$step]))$rows=$GLOBALS['boot_rewrite'][$step]($rows);
	if($GLOBALS['boot_fault']===$step.'-false'){$handle->errno=1234;return false;}
	if($GLOBALS['boot_fault']===$step.'-extra')$rows=array_merge($rows,$rows,$rows);
	if($GLOBALS['boot_fault']===$step.'-bad-row')$rows=array(array('bad'=>new stdClass()));
	if($GLOBALS['boot_fault']===$step.'-bad')$rows[0][array_key_first($rows[0])]='wrong';
	if($GLOBALS['boot_fault']===$step.'-oversize')$rows[0][array_key_first($rows[0])]=str_repeat('x',1025);
	return null===$rows?true:new mysqli_result($rows);
}
function mysqli_num_rows($result){return count($result->rows);}
function mysqli_fetch_assoc($result){boot_event('fetch'); return $result->rows[$result->position++];}
function mysqli_free_result($result){$result->freed=true; ++$GLOBALS['boot_counts']['freed']; boot_event('free');}
function mysqli_close($handle){++$GLOBALS['boot_counts']['close']; boot_event('close'); if('close-false'===$GLOBALS['boot_fault'])return false; $handle->closed=true; return true;}
}
