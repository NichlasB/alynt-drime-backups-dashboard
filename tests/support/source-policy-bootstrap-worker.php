<?php
/** Isolated synthetic bootstrap worker, never real authentication/native proof. */
if ( defined( 'ABSPATH' ) ) return; // PHPUnit discovery loads support files; only direct workers execute.
if ( PHP_SAPI !== 'cli' || 2 !== count( $argv ) || extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' ) ) exit( 1 );
define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
define( 'ADB_POLICY_ISOLATED', true );
require ABSPATH . 'vendor/autoload.php';
require ABSPATH . 'tests/support/wordpress-shims.php';
require ABSPATH . 'tests/support/source-policy-isolated-wordpress.php';
require ABSPATH . 'tests/support/source-policy-bootstrap-driver.php';
require ABSPATH . 'tests/support/source-policy-bootstrap-metadata.php';
require ABSPATH . 'tests/support/source-policy-bootstrap-scenarios.php';
require ABSPATH . 'tests/support/source-policy-bootstrap-cases.php';
require ABSPATH . 'tests/support/source-policy-transport-cases.php';
require ABSPATH . 'tests/support/source-policy-isolated-wpdb.php';
foreach(array('class-source-policy.php','class-source-policy-save-contract.php','class-source-policy-native-profile.php','class-source-policy-query-context.php','class-source-policy-stored-map.php') as $file)require ABSPATH.'includes/'.$file;
require ABSPATH . 'includes/class-source-policy-no-reconnect-connection.php';
require ABSPATH . 'includes/class-source-policy-native-adapter.php';
require ABSPATH . 'tests/support/source-policy-isolated-fixtures.php';
try {
	PHPUnit\Framework\Assert::assertTrue( ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' ) )->hasMethod( 'bootstrap' ), 'Private bootstrap orchestration must exist, not only unavailable factories.' );
	$transport=in_array($argv[1],policy_transport_scenarios(),true);
	define('DB_CHARSET',$transport?'utf8':'utf8mb4'); define('DB_COLLATE',$transport?'':'utf8mb4_unicode_520_ci');
	PHPUnit\Framework\Assert::assertContains($argv[1],array_merge(policy_bootstrap_scenarios(),policy_transport_scenarios()));
	define('MULTISITE','multisite'===$argv[1]); define('WP_CONTENT_DIR','dropin'===$argv[1]?ABSPATH.'tests/fixtures/bootstrap-content':ABSPATH.'tests/support');
	if('setup-mode'===$argv[1])define('WP_SETUP_CONFIG',true);
	if('legacy'===$argv[1])define('WP_USE_EXT_MYSQL',true);
	$GLOBALS['policy_bootstrap_model']=true; $GLOBALS['policy_ext_cache']=false; $GLOBALS['wp_object_cache']=new WP_Object_Cache(); $GLOBALS['wp_filter']=array(); $GLOBALS['policy_trace']=array(); $GLOBALS['policy_cache_fail']=false; $GLOBALS['policy_cache']=array();
	$GLOBALS['boot_counts']=array_fill_keys(array('object','allocate','options','connect','commands','results','freed','close','unexpected_connect'),0);
	$GLOBALS['boot_events']=array(); $GLOBALS['boot_after']=array(); $GLOBALS['boot_rewrite']=array(); $GLOBALS['boot_fault']=''; $GLOBALS['boot_store']=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(),'no');
	$connection=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection'))->newInstanceWithoutConstructor(); boot_invoke($connection,'__construct'); $GLOBALS['boot_connection']=$connection;
	$context=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context'))->newInstanceWithoutConstructor();
	foreach(array('owned'=>$connection,'ambient'=>null,'core_file'=>__DIR__.'/source-policy-isolated-wpdb.php') as $name=>$value)boot_set($context,$name,$value);
	boot_set($connection,'query_context',$context);
	boot_set($connection,'bootstrap_frame',array('core'=>'6.0','database'=>'isolated','table'=>'wp_options','host'=>'127.0.0.1','port'=>10004,'user'=>'owned','password'=>'owned-private-password','charset'=>DB_CHARSET,'collate'=>DB_COLLATE,'transport'=>'tcp_loopback','socket'=>null,'db_host'=>'127.0.0.1:10004'));
	boot_set($connection,'bootstrap_binding',array('core_file'=>__DIR__.'/source-policy-isolated-wpdb.php','driver_file'=>__DIR__.'/source-policy-bootstrap-driver.php','cache_file'=>__DIR__.'/source-policy-isolated-wordpress.php','cache_api_file'=>__DIR__.'/source-policy-isolated-wordpress.php','observers'=>array(),'observer_files'=>array()));
	boot_configure($argv[1],$connection,$context);
	if($transport)transport_configure($argv[1],$connection);
	// These unqualified domains must stay unreachable even in complete positive/error transcripts.
	foreach(array('wp_error_added','is_wp_error_instance','gettext','gettext_alynt-drime-backups-dashboard') as $error_hook)policy_isolated_hook($error_hook,static function(){throw new RuntimeException('Owned code dispatched an error/translation observer');});
	$GLOBALS['boot_prior_calls']=0; $GLOBALS['boot_prior_messages']=array(); $prior=static function($severity,$message){++$GLOBALS['boot_prior_calls'];$GLOBALS['boot_prior_messages'][]=array($severity,$message);return true;}; set_error_handler($prior);
	$result=boot_invoke($connection,'bootstrap');
	if($transport)transport_verify($argv[1],$connection,$result);else boot_verify($argv[1],$connection,$result);
	trigger_error('synthetic-after-bootstrap',E_USER_WARNING); PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_prior_calls']); restore_error_handler();
	echo 'checks:', PHPUnit\Framework\Assert::getCount(), "\n";
} catch ( Throwable $error ) {
	fwrite( STDERR, 'Synthetic bootstrap '.$argv[1].' '.get_class($error).' '.$error->getMessage()."\n" );
	exit( 1 );
}
