<?php
/** Shared actual ADBC2 producer under explicit API/core doubles, no native execution. */
if(defined('ABSPATH'))return;
if(PHP_SAPI!=='cli'||count($argv)!==3||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
define('ABSPATH',dirname(__DIR__,2).'/');define('ADB_POLICY_ISOLATED',true);
require ABSPATH.'vendor/autoload.php';require __DIR__.'/wordpress-shims.php';require __DIR__.'/source-policy-isolated-wordpress.php';
require ABSPATH.'scripts/manual/source-policy-connected-cleanup-evidence.php';
foreach(array('metadata','scenarios') as $part)require __DIR__.'/source-policy-bootstrap-'.$part.'.php';
require __DIR__.'/source-policy-connected-cleanup-driver.php';require __DIR__.'/source-policy-connected-cleanup-wpdb.php';
foreach(array('class-source-policy.php','class-source-policy-save-contract.php','class-source-policy-native-profile.php','class-source-policy-query-context.php','class-source-policy-stored-map.php','class-source-policy-no-reconnect-connection.php') as $file)require ABSPATH.'includes/'.$file;
require __DIR__.'/source-policy-isolated-fixtures.php';
if(defined('ADB_POLICY_ISOLATED')) {
function adbb_check($condition,$label){PHPUnit\Framework\Assert::assertTrue($condition,$label);}
function adbb_bind($object,$name,$value){boot_set($object,$name,$value);}
}
require ABSPATH.'scripts/manual/source-policy-connected-cleanup-operation.php';
try {
 $case=$argv[1];$fault=$argv[2];if(!in_array($case,array('missing-table','positive'),true))exit(1);
 define('DB_CHARSET','utf8mb4');define('DB_COLLATE','utf8mb4_unicode_520_ci');define('MULTISITE',false);define('WP_CONTENT_DIR',__DIR__);
 $GLOBALS['policy_bootstrap_model']=true;$GLOBALS['policy_ext_cache']=false;$GLOBALS['wp_object_cache']=new WP_Object_Cache();$GLOBALS['wp_filter']=array();$GLOBALS['policy_trace']=array();$GLOBALS['policy_cache_fail']=false;$GLOBALS['policy_cache']=array();
 $GLOBALS['boot_counts']=array_fill_keys(array('object','allocate','options','connect','commands','results','freed','close','unexpected_connect'),0);
 $GLOBALS['boot_events']=array();$GLOBALS['boot_after']=array();$GLOBALS['boot_rewrite']=array();$GLOBALS['boot_fault']=$fault==='unavailable'?'connect-false':($fault==='close'?'close-false':($case==='missing-table'?'table-bad':''));
 $GLOBALS['adbc2_fault']=$fault;$GLOBALS['adbc2_post_freed']=0;$GLOBALS['boot_store']=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(),'no');$GLOBALS['boot_store']->row=array('option_value'=>'unused','autoload'=>'no');
 $class=new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection');
 $frame=array('core'=>'7.1.3','database'=>'isolated','table'=>'wp_options','host'=>'127.0.0.1','port'=>10004,'user'=>'owned','password'=>'owned-private-password','charset'=>DB_CHARSET,'collate'=>DB_COLLATE);
 $binding=array('core_file'=>__DIR__.'/source-policy-connected-cleanup-wpdb.php','driver_file'=>__DIR__.'/source-policy-connected-cleanup-driver.php','cache_file'=>__DIR__.'/source-policy-isolated-wordpress.php','cache_api_file'=>__DIR__.'/source-policy-isolated-wordpress.php','observers'=>array(),'observer_files'=>array());
 $wire=adbc2_owner($class,$frame,$binding,array(__FILE__,'isolated','adbb_0123456789ab_',str_repeat('a',32),str_repeat('b',64),$case),str_repeat('b',64));
 PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_counts']['allocate']);PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_counts']['close']);PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_counts']['unexpected_connect']);
 PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_store']->mutations);PHPUnit\Framework\Assert::assertFalse(has_filter('query'));PHPUnit\Framework\Assert::assertFalse(has_filter('all'));
 echo $wire;fflush(STDOUT);
 if($fault==='hold'){$token=fgets(STDIN,256);adbb_check('ADBC2-ACK|'.str_repeat('a',32).'|'.str_repeat('b',64).'|'.$case.'|'.getmypid()."\n"===$token,'isolated exact release token');}
}catch(Throwable $error){fwrite(STDERR,'isolated ADBC2 assertion: '.$error->getMessage()."\n");exit(1);}
