<?php
/** Private isolated composition only; never imports ready state or qualification facts. */
if(defined('ADB_POLICY_ISOLATED')) {
function production_fixture($profile='m') {
	$GLOBALS['policy_bootstrap_model']=true;$GLOBALS['policy_capable']=true;$GLOBALS['policy_trace']=array();$GLOBALS['wp_filter']=array();$GLOBALS['policy_cache_fail']=false;$GLOBALS['policy_cache']=array();
	$GLOBALS['policy_ext_cache']=false;$GLOBALS['wp_object_cache']=new WP_Object_Cache();$GLOBALS['boot_fault']='';$GLOBALS['boot_report_mode']=0;$GLOBALS['boot_client']='mysqlnd 7.4.33';
	$GLOBALS['wp_version']='6.0';$GLOBALS['boot_parser_core']='6.0';$GLOBALS['boot_events']=array();$GLOBALS['boot_after']=array();$GLOBALS['boot_rewrite']=array();
	$GLOBALS['boot_counts']=array_fill_keys(array('object','allocate','options','connect','commands','results','freed','close','unexpected_connect'),0);
	$GLOBALS['boot_server']='5.5.5-10.6.23-MariaDB';$GLOBALS['boot_store']=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(9=>array('wpvivid'=>'external_optional')),'no');
	$GLOBALS['boot_expected_endpoint']=array('127.0.0.1','owned','owned-private-password','isolated',10004,null,0);
	$GLOBALS['boot_rewrite']['session']=static function($rows){$rows[0]['collation']='utf8mb4_unicode_ci';return$rows;};
	$ambient=(new ReflectionClass('wpdb'))->newInstanceWithoutConstructor();$ambient->dbh=new stdClass();$GLOBALS['wpdb']=$ambient;
	$receiver=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::class))->newInstanceWithoutConstructor();boot_invoke($receiver,'__construct');$GLOBALS['boot_connection']=$receiver;
	$query=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context::class))->newInstanceWithoutConstructor();
	foreach(array('owned'=>$receiver,'ambient'=>$ambient,'core_file'=>__DIR__.'/source-policy-isolated-wpdb.php')as$key=>$value)boot_set($query,$key,$value);
	$context=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context::class))->newInstanceWithoutConstructor();
	$files=array('core_file'=>'source-policy-isolated-wpdb.php','cache_file'=>'source-policy-isolated-wordpress.php','cache_api_file'=>'source-policy-isolated-wordpress.php','parser_api_file'=>'wordpress-shims-sanitization.php','hook_file'=>'source-policy-isolated-wordpress.php');
	$pins=array();foreach(array_unique(array_merge(array_values($files),array('source-policy-bootstrap-driver.php')))as$file)$pins[$file]=hash_file('sha256',__DIR__.'/'.$file);
	$policy=array('core'=>'6.0','php'=>PHP_VERSION,'root'=>__DIR__.'/','pins'=>$pins,'files'=>$files,'driver'=>__DIR__.'/source-policy-bootstrap-driver.php');
	// Models immutable process observations, NOT real Linux/PHP 7.4 or native qualification.
	$runtime=array('platform'=>'Linux','arch'=>'x86_64','php'=>'7.4.33','client'=>'mysqlnd 7.4.33','mode'=>0);
	if('m'!==$profile){
		$core='w'===$profile?'7.1.3':'7.1.4';$GLOBALS['wp_version']=$core;$GLOBALS['boot_parser_core']=$core;$policy['core']=$core;$windows=in_array($profile,array('w','w714'),true);
		$runtime=array('platform'=>$windows?'Windows':'Linux','arch'=>$windows?'AMD64':'x86_64','php'=>'8.5.12','client'=>'mysqlnd 8.5.12','mode'=>0,'word_size'=>8,'nts'=>true);
		$GLOBALS['boot_client']='mysqlnd 8.5.12';$GLOBALS['boot_server']=$windows?'10.6.23-MariaDB':'10.11.18-MariaDB-ubu2204';unset($GLOBALS['boot_rewrite']['session']);
		if('l'===$profile)$GLOBALS['boot_expected_endpoint']=array('localhost','owned','owned-private-password','isolated',null,'/tmp/owned.sock',0);
	}
	foreach(array('receiver'=>$receiver,'policy'=>$policy,'production_query'=>$query,'production_runtime'=>$runtime)as$key=>$value)boot_set($context,$key,$value);
	$frame=array('core'=>'6.0','database'=>DB_NAME,'table'=>'wp_options','host'=>'127.0.0.1','port'=>10004,'user'=>DB_USER,'password'=>DB_PASSWORD,'charset'=>DB_CHARSET,'collate'=>DB_COLLATE,'transport'=>'tcp_loopback','socket'=>null,'db_host'=>DB_HOST);
	$frame['core']=$policy['core'];if('l'===$profile){$frame['host']='localhost';$frame['port']=null;$frame['transport']='unix_socket';$frame['socket']='/tmp/owned.sock';}
	$binding=array('driver_file'=>$policy['driver'],'observers'=>array(),'observer_files'=>array());foreach($files as$key=>$file)$binding[$key]=realpath(__DIR__.'/'.$file);
	foreach(array('bootstrap_frame'=>$frame,'bootstrap_binding'=>$binding,'query_context'=>$query)as$key=>$value)boot_set($receiver,$key,$value);
	$adapter=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter::class))->newInstanceWithoutConstructor();boot_set($adapter,'connection',$receiver);
	$provider=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Lazy_Provider::class))->newInstanceWithoutConstructor();foreach(array('owner'=>$adapter,'context'=>$context,'bootstrap'=>true)as$key=>$value)boot_set($provider,$key,$value);
	$sites=new Alynt_Drime_Backups_Dashboard_Test_Policy_Sites();$entry=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry::class))->newInstanceWithoutConstructor();boot_set($entry,'owner',$provider);boot_set($entry,'sites',$sites);
	$GLOBALS['boot_parse_calls']=0;return array($entry,$provider,$context,$adapter,$receiver,$GLOBALS['boot_store'],$ambient,$sites,$query);
}
}
