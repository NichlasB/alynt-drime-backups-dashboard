<?php
/** Ordinary positive construction; no private state/ready/handle injection or native APIs. */
if(defined('ABSPATH'))return;
if(PHP_SAPI!=='cli'||count($argv)!==2||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
define('ABSPATH',dirname(__DIR__,2).'/');define('ADB_POLICY_ISOLATED',true);define('WPINC','wp-includes');define('MULTISITE',false);define('WP_CONTENT_DIR',__DIR__);if(!defined('ARRAY_A'))define('ARRAY_A','ARRAY_A');
define('DB_NAME','isolated');define('DB_USER','owned');define('DB_PASSWORD','owned-private-password');define('DB_HOST','127.0.0.1:10004');define('DB_CHARSET','utf8');define('DB_COLLATE','');
require ABSPATH.'vendor/autoload.php';require __DIR__.'/wordpress-shims.php';
foreach(array('isolated-wordpress','bootstrap-driver','bootstrap-metadata','isolated-wpdb','request-catalog')as$file)require __DIR__.'/source-policy-'.$file.'.php';
foreach(array('source-policy','source-policy-save-contract','source-policy-native-profile','source-policy-query-context','source-policy-no-reconnect-connection','source-policy-stored-map','source-policy-native-adapter','source-policy-guarded-entry')as$file)require ABSPATH.'includes/class-'.$file.'.php';
require ABSPATH.'includes/class-source-policy-trusted-source.php';
foreach(array('reads','local-state-writes','writes','runtime-writes')as$file)require ABSPATH.'includes/traits/trait-site-repository-'.$file.'.php';
require ABSPATH.'includes/class-site-repository.php';require ABSPATH.'includes/class-storage.php';require __DIR__.'/source-policy-isolated-fixtures.php';require __DIR__.'/source-policy-trusted-fixtures.php';require __DIR__.'/source-policy-request-cases.php';
use PHPUnit\Framework\Assert as A;
try {
	$GLOBALS['request_case']=$argv[1];$GLOBALS['request_ambient_model']=true;$GLOBALS['request_record_reads']=0;$GLOBALS['request_record']=array('id'=>7);
	$GLOBALS['policy_bootstrap_model']=true;$GLOBALS['policy_capable']=true;$GLOBALS['policy_trace']=array();$GLOBALS['wp_filter']=array();$GLOBALS['policy_cache_fail']=false;$GLOBALS['policy_cache']=array();$GLOBALS['policy_ext_cache']=false;$GLOBALS['wp_object_cache']=new WP_Object_Cache();
	$GLOBALS['boot_fault']='';$GLOBALS['boot_report_mode']=0;$GLOBALS['boot_client']='mysqlnd 7.4.33';$GLOBALS['wp_version']='6.0';$GLOBALS['boot_parser_core']='6.0';$GLOBALS['boot_events']=array();$GLOBALS['boot_after']=array();$GLOBALS['boot_rewrite']=array();
	$GLOBALS['boot_counts']=array_fill_keys(array('object','allocate','options','connect','commands','results','freed','close','unexpected_connect'),0);$GLOBALS['boot_server']='5.5.5-10.6.23-MariaDB';
	$GLOBALS['boot_rewrite']['session']=static function($rows){$rows[0]['collation']='utf8mb4_unicode_ci';return$rows;};
	$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(9=>array('wpvivid'=>'external_optional')),'no');$GLOBALS['boot_store']=$store;
	$ambient=new wpdb();$ambient->prefix='wp_';$GLOBALS['wpdb']=$ambient;$handle=$ambient->dbh;$request=trusted_request($store);
	request_case($argv[1],$request,$store);
	A::assertSame($handle,$ambient->dbh);A::assertSame(0,$ambient->closes);A::assertSame(0,$GLOBALS['boot_counts']['unexpected_connect']);
	if($GLOBALS['request_record_reads']>0){A::assertSame(0,has_filter('query',array($ambient,'remove_placeholder_escape')));A::assertCount(1,$GLOBALS['wp_filter']['query']->callbacks[0],'Only the stock record-query ambient callback remains.');}
	else A::assertFalse(isset($GLOBALS['wp_filter']['query']));
	A::assertFalse(class_exists('mysqli',false)&&extension_loaded('mysqli'));A::assertFalse(extension_loaded('pdo_mysql'));
	echo 'checks:',A::getCount(),"\n";
}catch(Throwable $error){fwrite(STDERR,'Request '.$argv[1].' '.get_class($error).' '.$error->getMessage()."\n".$error->getTraceAsString()."\n");exit(1);}
