<?php
/** In-memory APIs only; no genuine core, SQL, network or native driver. */
if(defined('ABSPATH'))return;
if(PHP_SAPI!=='cli'||count($argv)!==2||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
define('ABSPATH',dirname(__DIR__,2).'/');define('ADB_POLICY_ISOLATED',true);
require ABSPATH.'vendor/autoload.php';require __DIR__.'/wordpress-shims.php';
foreach(array('isolated-wordpress','bootstrap-driver','bootstrap-metadata','isolated-wpdb')as$file)require __DIR__.'/source-policy-'.$file.'.php';
foreach(array('class-source-policy.php','class-source-policy-save-contract.php','class-source-policy-native-profile.php','class-source-policy-query-context.php','class-source-policy-stored-map.php','class-source-policy-no-reconnect-connection.php')as$file)require ABSPATH.'includes/'.$file;
require __DIR__.'/source-policy-isolated-fixtures.php';
require ABSPATH.'scripts/ci/source-policy-minimum-bootstrap-diagnostics.php';
use PHPUnit\Framework\Assert as A;
try {
 define('DB_CHARSET','utf8mb4');define('DB_COLLATE','utf8mb4_unicode_520_ci');define('MULTISITE',false);define('WP_CONTENT_DIR',__DIR__);
 $GLOBALS['policy_bootstrap_model']=true;$GLOBALS['policy_ext_cache']=false;$GLOBALS['wp_object_cache']=new WP_Object_Cache();$GLOBALS['wp_filter']=array();
 $GLOBALS['policy_trace']=array();$GLOBALS['policy_cache_fail']=false;$GLOBALS['policy_cache']=array();
 $GLOBALS['boot_counts']=array_fill_keys(array('object','allocate','options','connect','commands','results','freed','close','unexpected_connect'),0);
 $GLOBALS['boot_events']=array();$GLOBALS['boot_after']=array();$GLOBALS['boot_rewrite']=array();$GLOBALS['boot_fault']='';
 $GLOBALS['boot_store']=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(),'no');$GLOBALS['boot_server']='10.11.18-MariaDB-ubu2204';$GLOBALS['boot_report_mode']=0;
 $GLOBALS['boot_expected_endpoint']=array('127.0.0.1','adbm','owned-private-password','adb_minimum',3306,null,0);
 foreach(array('identity','session')as$step)$GLOBALS['boot_rewrite'][$step]=static function($rows){$rows[0]['db']='adb_minimum';return$rows;};
 $owner=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection'))->newInstanceWithoutConstructor();boot_invoke($owner,'__construct');$GLOBALS['boot_connection']=$owner;
 $context=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context'))->newInstanceWithoutConstructor();
 foreach(array('owned'=>$owner,'ambient'=>null,'core_file'=>__DIR__.'/source-policy-isolated-wpdb.php')as$key=>$value)boot_set($context,$key,$value);
 $frame=array('core'=>'6.0','database'=>'adb_minimum','table'=>'adbm_aaaaaaaaaaaa_options','host'=>'127.0.0.1','port'=>3306,'user'=>'adbm','password'=>'owned-private-password','charset'=>DB_CHARSET,'collate'=>DB_COLLATE,'transport'=>'tcp_loopback','socket'=>null,'db_host'=>'127.0.0.1:3306');
 $evidence=array('model'=>'trusted-lab-functional-v1','platform'=>'Linux','arch'=>'x86_64','php'=>'7.4.33','client'=>'mysqlnd 7.4.33','core_commit'=>'cc101b64012b16d087780657a2b828ccd7794a63','runtime_image'=>'sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4','server_image'=>'sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5','initial_mode'=>0,'head'=>str_repeat('b',40),'image'=>str_repeat('c',64),'run'=>str_repeat('a',32));
 $scope=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Minimum_Lab_Scope'))->newInstanceWithoutConstructor();boot_invoke($scope,'__construct',$owner,$context,$frame,$evidence);
 $binding=array('core_file'=>__DIR__.'/source-policy-isolated-wpdb.php','driver_file'=>__DIR__.'/source-policy-bootstrap-driver.php','cache_file'=>__DIR__.'/source-policy-isolated-wordpress.php','cache_api_file'=>__DIR__.'/source-policy-isolated-wordpress.php','parser_api_file'=>__DIR__.'/wordpress-shims-sanitization.php','observers'=>array(),'observer_files'=>array());
 if('bad-source'===$argv[1])$binding['core_file']=__DIR__.'/nonexistent-never-read.php';
 foreach(array('query_context'=>$context,'bootstrap_frame'=>$frame,'minimum_lab_scope'=>$scope,'bootstrap_binding'=>$binding)as$key=>$value)boot_set($owner,$key,$value);
 if('bad-context'===$argv[1])boot_set($context,'core_file',__DIR__.'/nonexistent-never-read.php');
 if('bad-server'===$argv[1])$GLOBALS['boot_fault']='bad-server';
 $counts=$GLOBALS['boot_counts'];$state=boot_get($owner,'native_state');$GLOBALS['adbb_first_failure']=array('sentinel'=>'unchanged');$prior_calls=0;
 $prior=static function()use(&$prior_calls){++$prior_calls;return true;};set_error_handler($prior);
 $before=adbm_bootstrap_before($owner,$context,$frame,$binding);
 A::assertSame('bad-source'===$argv[1]?'1011':('bad-context'===$argv[1]?'1101':'1111'),$before);A::assertSame($counts,$GLOBALS['boot_counts']);A::assertSame($state,boot_get($owner,'native_state'));
 trigger_error('owned-test-warning',E_USER_WARNING);A::assertSame(1,$prior_calls);
 if('unknown'===$argv[1]) {
  $unknown=new class{public static $calls=0;public function __get($key){++self::$calls;throw new RuntimeException('No unknown magic');}};
  A::assertSame('uuuu',adbm_bootstrap_before($unknown,$unknown,$frame,$binding));
  $row=adbm_bootstrap_after($unknown,$unknown,'uuuu',false);
  A::assertSame(0,$unknown::$calls);
 } else {
  $result=boot_invoke($owner,'bootstrap');$counts=$GLOBALS['boot_counts'];
  $row=adbm_bootstrap_after($owner,$context,$before,$result);
  A::assertSame($counts,$GLOBALS['boot_counts']);
  if('bad-server'===$argv[1]){A::assertInstanceOf(WP_Error::class,$result);A::assertSame(1,$counts['connect']);A::assertSame(0,$counts['commands']);A::assertSame(1,$counts['close']);}
  elseif(in_array($argv[1],array('bad-source','bad-context'),true)){A::assertInstanceOf(WP_Error::class,$result);A::assertSame(0,$counts['allocate']);A::assertSame(0,$counts['connect']);}
  else {A::assertTrue($result);A::assertSame(1,$counts['connect']);A::assertTrue($owner->ready);}
 }
 trigger_error('owned-test-warning',E_USER_WARNING);A::assertSame(2,$prior_calls);restore_error_handler();
 A::assertSame(array('sentinel'=>'unchanged'),$GLOBALS['adbb_first_failure']);
 $owner->close();echo adbm_bootstrap_wire($row);
}catch(Throwable$error){fwrite(STDERR,'Isolated bootstrap diagnostic refused: '.get_class($error)."\n");exit(1);}
