<?php
/** Extension-free model, not genuine WordPress or native qualification. */
if(defined('ABSPATH'))return;
if(PHP_SAPI!=='cli'||count($argv)!==2||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
define('ABSPATH',dirname(__DIR__,2).'/'); define('ADB_POLICY_ISOLATED',true);
require ABSPATH.'vendor/autoload.php'; require __DIR__.'/wordpress-shims.php';
foreach(array('isolated-wordpress','bootstrap-driver','bootstrap-metadata','isolated-wpdb')as$file)require __DIR__.'/source-policy-'.$file.'.php';
foreach(array('class-source-policy.php','class-source-policy-save-contract.php','class-source-policy-native-profile.php','class-source-policy-query-context.php','class-source-policy-stored-map.php','class-source-policy-no-reconnect-connection.php','class-source-policy-native-adapter.php')as$file)require ABSPATH.'includes/'.$file;
require __DIR__.'/source-policy-isolated-fixtures.php';
use PHPUnit\Framework\Assert as A;
function minimum_adapter($frame,$evidence) {
	$GLOBALS['boot_counts']['options']=0; // Existing driver double indexes the three options per fresh handle.
	$owner=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection'))->newInstanceWithoutConstructor(); boot_invoke($owner,'__construct'); $GLOBALS['boot_connection']=$owner;
	$context=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context'))->newInstanceWithoutConstructor();
	foreach(array('owned'=>$owner,'ambient'=>null,'core_file'=>__DIR__.'/source-policy-isolated-wpdb.php')as$key=>$value)boot_set($context,$key,$value);
	$scope=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Minimum_Lab_Scope'))->newInstanceWithoutConstructor();boot_invoke($scope,'__construct',$owner,$context,$frame,$evidence);
	foreach(array('query_context'=>$context,'bootstrap_frame'=>$frame,'minimum_lab_scope'=>$scope,'bootstrap_binding'=>array('core_file'=>__DIR__.'/source-policy-isolated-wpdb.php','driver_file'=>__DIR__.'/source-policy-bootstrap-driver.php','cache_file'=>__DIR__.'/source-policy-isolated-wordpress.php','cache_api_file'=>__DIR__.'/source-policy-isolated-wordpress.php','parser_api_file'=>__DIR__.'/wordpress-shims-sanitization.php','observers'=>array(),'observer_files'=>array()))as$key=>$value)boot_set($owner,$key,$value);
	A::assertTrue(boot_invoke($owner,'bootstrap'));
	$adapter=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter'))->newInstanceWithoutConstructor();boot_set($adapter,'connection',$owner);return$adapter;
}
try {
	define('DB_CHARSET','utf8mb4'); define('DB_COLLATE','empty-collation'===$argv[1]?'':'utf8mb4_unicode_520_ci'); define('MULTISITE',false); define('WP_CONTENT_DIR',__DIR__);
	$GLOBALS['policy_bootstrap_model']=true; $GLOBALS['policy_ext_cache']=false; $GLOBALS['wp_object_cache']=new WP_Object_Cache(); $GLOBALS['wp_filter']=array(); $GLOBALS['policy_trace']=array(); $GLOBALS['policy_cache_fail']=false; $GLOBALS['policy_cache']=array();
	$GLOBALS['boot_counts']=array_fill_keys(array('object','allocate','options','connect','commands','results','freed','close','unexpected_connect'),0);
	$GLOBALS['boot_events']=array(); $GLOBALS['boot_after']=array(); $GLOBALS['boot_rewrite']=array(); $GLOBALS['boot_fault']=''; $GLOBALS['boot_store']=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(),'no');
	$GLOBALS['boot_server']='5.5.5-10.11.18-MariaDB-ubu2204'; $GLOBALS['boot_report_mode']=0;
	$GLOBALS['boot_expected_endpoint']=array('127.0.0.1','adbm','owned-private-password','adb_minimum',3306,null,0);
	foreach(array('identity','session')as$step)$GLOBALS['boot_rewrite'][$step]=static function($rows){$rows[0]['db']='adb_minimum';return$rows;};
	$owner=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection'))->newInstanceWithoutConstructor(); boot_invoke($owner,'__construct'); $GLOBALS['boot_connection']=$owner;
	$context=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context'))->newInstanceWithoutConstructor();
	foreach(array('owned'=>$owner,'ambient'=>null,'core_file'=>__DIR__.'/source-policy-isolated-wpdb.php')as$key=>$value)boot_set($context,$key,$value);
	$frame=array('core'=>'6.0','database'=>'adb_minimum','table'=>'adbm_aaaaaaaaaaaa_options','host'=>'127.0.0.1','port'=>3306,'user'=>'adbm','password'=>'owned-private-password','charset'=>DB_CHARSET,'collate'=>DB_COLLATE,'transport'=>'tcp_loopback','socket'=>null,'db_host'=>'127.0.0.1:3306');
	$evidence=array('model'=>'trusted-lab-functional-v1','platform'=>'Linux','arch'=>'x86_64','php'=>'7.4.33','client'=>'mysqlnd 7.4.33','core_commit'=>'cc101b64012b16d087780657a2b828ccd7794a63','runtime_image'=>'sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4','server_image'=>'sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5','initial_mode'=>0,'head'=>str_repeat('b',40),'image'=>str_repeat('c',64),'run'=>str_repeat('a',32));
	$case=$argv[1];
	if(0===strpos($case,'evidence-')){$key=substr($case,9);$evidence[$key]=is_int($evidence[$key])?3:$evidence[$key].'x';}
	if('image'===$case)$evidence['runtime_image']='sha256:'.str_repeat('0',64);
	if('core'===$case)$frame['core']='7.1.4';
	if('run'===$case)$evidence['run']=str_repeat('d',32);
	if('endpoint'===$case)$frame['port']=10004;
	$scope=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Minimum_Lab_Scope'))->newInstanceWithoutConstructor(); boot_invoke($scope,'__construct',$owner,$context,$frame,$evidence);
	boot_set($owner,'query_context',$context); boot_set($owner,'bootstrap_frame',$frame);
	boot_set($owner,'bootstrap_binding',array('core_file'=>__DIR__.'/source-policy-isolated-wpdb.php','driver_file'=>__DIR__.'/source-policy-bootstrap-driver.php','cache_file'=>__DIR__.'/source-policy-isolated-wordpress.php','cache_api_file'=>__DIR__.'/source-policy-isolated-wordpress.php','parser_api_file'=>__DIR__.'/wordpress-shims-sanitization.php','observers'=>array(),'observer_files'=>array()));
	if('default'!==$case)boot_set($owner,'minimum_lab_scope',$scope);
	if('mode'===$case)$GLOBALS['boot_report_mode']=3;
	if('server'===$case)$GLOBALS['boot_server']='10.11.18-MariaDB';
	$server_faults=array('server-unprefixed'=>'10.11.18-MariaDB-ubu2204','server-double-prefix'=>'5.5.5-5.5.5-10.11.18-MariaDB-ubu2204','server-wrong-build'=>'5.5.5-10.11.18-MariaDB-ubu2404','server-patch'=>'5.5.5-10.11.19-MariaDB-ubu2204');
	if(isset($server_faults[$case]))$GLOBALS['boot_server']=$server_faults[$case];
	if('empty-collation'===$case)$GLOBALS['boot_rewrite']['session']=static function($rows){$rows[0]['db']='adb_minimum';$rows[0]['collation']='utf8mb4_unicode_ci';return $rows;};
	if('context'===$case)boot_set($owner,'query_context',clone$context);
	if('late-context'===$case)$GLOBALS['boot_after']['identity']=static function()use($owner,$context){boot_set($owner,'query_context',clone$context);};
	if('late-frame'===$case)$GLOBALS['boot_after']['identity']=static function()use($owner,$frame){$frame['table']='adbm_bbbbbbbbbbbb_options';boot_set($owner,'bootstrap_frame',$frame);};
	$result=boot_invoke($owner,'bootstrap');
	if(in_array($case,array('positive','closed','active-context','active-facts'),true)) {
		A::assertTrue($result); A::assertTrue($owner->ready); A::assertSame(1,$GLOBALS['boot_counts']['allocate']); A::assertSame(1,$GLOBALS['boot_counts']['connect']);
		A::assertNull(boot_get($owner,'bootstrap_frame')); A::assertIsArray($owner->minimum_lab_profile());
		$facts=boot_get($owner,'qualification');
		A::assertInstanceOf(WP_Error::class,Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::admit($facts,$frame['table'],$context));
		$profile=$owner->admit_profile(); A::assertIsArray($profile); A::assertSame('no',$profile['autoload']); A::assertSame(array('yes','no'),$profile['states']);
		A::assertInstanceOf(WP_Error::class,Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::admit($facts,$frame['table'],clone$context,$owner));
		if('active-context'===$case)boot_set($owner,'query_context',clone$context);
		if('active-facts'===$case){$facts['server']='10.6.23-MariaDB';boot_set($owner,'qualification',$facts);}
		if(in_array($case,array('active-context','active-facts'),true))A::assertInstanceOf(WP_Error::class,$owner->admit_profile());
		if('positive'===$case) {
			// Actual adapter path on modeled storage; no injected ready flag or facts.
			$adapter=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter'))->newInstanceWithoutConstructor();boot_set($adapter,'connection',$owner);
			$view=$adapter->view_source_mode(7,'wpvivid'); A::assertIsArray($view);
			$adapter=minimum_adapter($frame,$evidence);
			A::assertSame('saved',$adapter->save_source_mode_if_current($view['revision'],7,'wpvivid','external_optional'));
			$adapter=minimum_adapter($frame,$evidence);
			$conflict=$adapter->save_source_mode_if_current($view['revision'],7,'wpvivid','required');
			A::assertInstanceOf(WP_Error::class,$conflict); A::assertSame('source_policy_conflict',$conflict->get_error_code());
			A::assertSame(1,$GLOBALS['boot_store']->mutations);
		}
		A::assertTrue($owner->close()); A::assertNull(boot_get($owner,'minimum_lab_scope')); A::assertInstanceOf(WP_Error::class,$owner->minimum_lab_profile()); A::assertInstanceOf(WP_Error::class,$owner->admit_profile());
		A::assertFalse($scope->expectation(new stdClass(),$context));
	} else {
		A::assertInstanceOf(WP_Error::class,$result); A::assertFalse($owner->ready);
		if(0===strpos($case,'evidence-'))A::assertSame(0,$GLOBALS['boot_counts']['allocate']);
		if(in_array($case,array('default','server'),true)||isset($server_faults[$case]))A::assertSame(0,$GLOBALS['boot_counts']['commands'],'Initial server mismatch must refuse before metadata queries.');
		if('empty-collation'===$case){A::assertSame('utf8mb4_unicode_ci',$owner->collate);A::assertSame(7,$GLOBALS['boot_counts']['commands']);A::assertSame(8,boot_get($owner,'bootstrap_position'));A::assertSame(12,boot_get($owner,'bootstrap_credits'));A::assertTrue($owner->close());}
		if(in_array($case,array('late-context','late-frame'),true))A::assertSame(1,$GLOBALS['boot_counts']['commands'],'Changed private binding stops before the next command.');
		if(in_array($case,array('image','core','run','endpoint','mode','context'),true))A::assertSame(0,$GLOBALS['boot_counts']['allocate']);
		if(in_array($case,array('default','server','late-context','late-frame'),true))A::assertSame(1,$GLOBALS['boot_counts']['close']);
	}
	A::assertSame(0,$owner->reconnects); A::assertInstanceOf(WP_Error::class,Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::create());
	echo 'checks:',A::getCount(),"\n";
}catch(Throwable$error){fwrite(STDERR,get_class($error).': '.$error->getMessage().' '.$error->getTraceAsString()."\n");exit(1);}
