<?php
/** Isolated execution of the actual raw operation; no drivers, WordPress or IO backend. */
if(defined('ABSPATH'))return;
if(PHP_SAPI!=='cli'||count($argv)!==4||$argv[1]!=='--isolated-raw-connected'||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(2);
define('ADCR_PRIVATE',true);
define('MYSQLI_OPT_CONNECT_TIMEOUT',1);define('MYSQLI_OPT_READ_TIMEOUT',2);define('MYSQLI_OPT_LOCAL_INFILE',3);
require dirname(__DIR__,2).'/scripts/manual/source-policy-connected-raw-operation.php';
$mode=$argv[3];$run=$argv[2];$source=hash('sha256','isolated-raw-connected');
$init_calls=0;$connect_calls=0;$close_calls=0;$stats_calls=0;$image_calls=0;$connected=false;$closed=false;$options=array();$allocated=null;
if('ambient'===$mode)$GLOBALS['wpdb']=new stdClass();
class mysqli {}
function adcr_image(){global $image_calls,$mode,$source;++$image_calls;return 'pre-source-drift'===$mode||('source-drift'===$mode&&$image_calls>1)?str_repeat('0',64):$source;}
function mysqli_get_client_stats(){
 global $stats_calls,$init_calls,$connect_calls,$close_calls,$connected,$closed,$mode;++$stats_calls;
 $map=array_fill_keys(Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::counters(),'0');
 if('dirty'===$mode||('dirty-init'===$mode&&$init_calls))$map['com_query']='1';
 if('bad-type'===$mode)$map['active_connections']=0;
 if($connected){$map['connect_success']='1';$map['pconnect_success']='1';$map['active_connections']=$closed?'18446744073709551615':'1';$map['active_persistent_connections']=$map['active_connections'];}
 if($close_calls)$map['explicit_close']='1';
 if('always-closed'===$mode)$map['connect_success']='0';
 if('extra-sql'===$mode&&$connected)$map['com_query']='1';
 return $map;
}
function mysqli_init(){global $init_calls,$allocated,$mode;++$init_calls;return $allocated='allocation-false'===$mode?false:new mysqli();}
function mysqli_options($handle,$key,$value){global $options,$allocated,$mode;if($handle!==$allocated)throw new RuntimeException('private-secret');$options[$key]=$value;return 'option-false'!==$mode;}
function mysqli_real_connect($handle,$host,$user,$password,$database,$port,$socket,$flags){
 global $connect_calls,$connected,$mode,$options,$allocated;++$connect_calls;
 if($handle!==$allocated||$host!=='127.0.0.1'||$user!=='root'||$database!=='local'||$port!==10004||null!==$socket||0!==$flags||$options!==array(1=>2,2=>3,3=>0))throw new RuntimeException('private-secret');
 if('unavailable'===$mode)return false;
 if('connect-throw'===$mode||'paired'===$mode)throw new RuntimeException('private-secret');
 $connected=true;return true;
}
function mysqli_get_server_info($handle){return '10.6.23-MariaDB';}
function mysqli_close($handle){global $close_calls,$closed,$mode,$allocated;++$close_calls;if($handle!==$allocated)throw new RuntimeException('foreign-object');$closed=true;if('close-throw'===$mode||'paired'===$mode)throw new RuntimeException('private-secret');return 'close-false'!==$mode;}
$exit=0;
try{adcr_raw($run,$source);}catch(Throwable $error){$exit=1;fwrite(STDERR,'isolated primary:'.($GLOBALS['adcr_primary_kind']??'unconfirmed').' cleanup:'.($GLOBALS['adcr_cleanup_kind']??'not_attempted')."\n");}
echo 'CHECKS|'.$init_calls.'|'.$connect_calls.'|'.$close_calls.'|'.$stats_calls."\n";
exit($exit);
