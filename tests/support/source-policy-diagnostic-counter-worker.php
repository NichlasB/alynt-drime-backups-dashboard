<?php
/** Synthetic counter formatting only; no driver, site or native owner. */
if ( defined('ADB_DIAGNOSTIC_COUNTER_WORKER') ) {
if ( 3 !== count( $argv ) || extension_loaded( 'mysqli' ) ) { exit( 1 ); }
$value=$argv[2];
function adbb_check($condition,$label) { if(!$condition)throw new RuntimeException('counter rejected'); }
function mysqli_get_client_stats() {
	$value='array'===$GLOBALS['value']?array():('float'===$GLOBALS['value']?1.8446744073709552E+19:$GLOBALS['value']);
	return array_fill_keys(array('connect_success','connect_failure','explicit_close','implicit_close','reconnect','pconnect_success','com_query'),'0')+array('active_connections'=>$value);
}
$argv[3]='diagnose-raw'; $frame=array(); $binding=array(); $class=new stdClass();
require dirname(__DIR__,2).'/scripts/manual/source-policy-failed-connect-diagnosis.php';
try {
	if('exception-marker'===$value){echo adbb_diagnostic_failure(new Error('private-secret-message'),'raw-errno'); exit(0);}
	$stats=adbb_diagnostic_stats();
	echo 'accepted:',('float'===$value?gettype($stats['active_connections']):$stats['active_connections']);
} catch (RuntimeException $error) { echo 'counter-rejected'; }
}
