<?php
/** Actual dormant owner with synthetic APIs only. No native/source authentication. */
if ( defined('ABSPATH') ) return;
if(PHP_SAPI!=='cli'||count($argv)!==2||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
define('ABSPATH',dirname(__DIR__,2).'/'); define('ADB_POLICY_ISOLATED',true);
require ABSPATH.'vendor/autoload.php'; require __DIR__.'/wordpress-shims.php'; require __DIR__.'/source-policy-isolated-wordpress.php';
foreach(array('driver','metadata','scenarios','cases') as $part)require __DIR__.'/source-policy-bootstrap-'.$part.'.php';
require __DIR__.'/source-policy-isolated-wpdb.php';
foreach(array('class-source-policy.php','class-source-policy-save-contract.php','class-source-policy-native-profile.php','class-source-policy-query-context.php','class-source-policy-stored-map.php','class-source-policy-no-reconnect-connection.php') as $file)require ABSPATH.'includes/'.$file;
require __DIR__.'/source-policy-isolated-fixtures.php'; require ABSPATH.'scripts/manual/source-policy-cleanup-evidence.php';
try {
	$case=$argv[1]; $cases=Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::cases(); PHPUnit\Framework\Assert::assertContains($case,$cases);
	define('DB_CHARSET','utf8mb4'); define('DB_COLLATE','utf8mb4_unicode_520_ci'); define('MULTISITE',false); define('WP_CONTENT_DIR',__DIR__);
	$GLOBALS['policy_bootstrap_model']=true; $GLOBALS['policy_ext_cache']=false; $GLOBALS['wp_object_cache']=new WP_Object_Cache(); $GLOBALS['wp_filter']=array(); $GLOBALS['policy_trace']=array(); $GLOBALS['policy_cache_fail']=false; $GLOBALS['policy_cache']=array();
	$GLOBALS['boot_counts']=array_fill_keys(array('object','allocate','options','connect','commands','results','freed','close','unexpected_connect'),0);
	$GLOBALS['boot_events']=array(); $GLOBALS['boot_after']=array(); $GLOBALS['boot_rewrite']=array(); $GLOBALS['boot_fault']=''; $GLOBALS['boot_store']=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(),'no');
	$connection=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection'))->newInstanceWithoutConstructor(); boot_invoke($connection,'__construct'); $GLOBALS['boot_connection']=$connection;
	$context=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context'))->newInstanceWithoutConstructor();
	foreach(array('owned'=>$connection,'ambient'=>null,'core_file'=>__DIR__.'/source-policy-isolated-wpdb.php') as $key=>$value)boot_set($context,$key,$value);
	boot_set($connection,'query_context',$context);
	boot_set($connection,'bootstrap_frame',array('core'=>'7.1.3','database'=>'isolated','table'=>'wp_options','host'=>'127.0.0.1','port'=>10004,'user'=>'owned','password'=>'owned-private-password','charset'=>DB_CHARSET,'collate'=>DB_COLLATE));
	boot_set($connection,'bootstrap_binding',array('core_file'=>__DIR__.'/source-policy-isolated-wpdb.php','driver_file'=>__DIR__.'/source-policy-bootstrap-driver.php','cache_file'=>__DIR__.'/source-policy-isolated-wordpress.php','cache_api_file'=>__DIR__.'/source-policy-isolated-wordpress.php','observers'=>array(),'observer_files'=>array()));
	$mapping=array('wrong-source'=>'bad-core-source','missing-database'=>'connect-false','missing-table'=>'table-bad','all'=>'positive','incompatible_sql_modes'=>'positive'); boot_configure($mapping[$case]??$case,$connection,$context);
	if(in_array($case,array('all','incompatible_sql_modes'),true)){
		$fixture_callback=static function(){++$GLOBALS['boot_observer_calls'];};add_filter($case,$fixture_callback,1);
		$binding=boot_get($connection,'bootstrap_binding');$binding['observers'][$case]=$GLOBALS['wp_filter'][$case]->callbacks;$binding['observer_files'][__FILE__]=hash_file('sha256',__FILE__);boot_set($connection,'bootstrap_binding',$binding);
	}
	$probe_callbacks=array();foreach(array('all','incompatible_sql_modes') as $hook)foreach(($GLOBALS['wp_filter'][$hook]->callbacks??array()) as $priority=>$entries)foreach($entries as $entry)$probe_callbacks[]=array($hook,$entry['function'],$priority);
	$result=boot_invoke($connection,'bootstrap'); $active=$connection->ready; $boot=$GLOBALS['boot_counts'];
	$positive='positive'===$case; PHPUnit\Framework\Assert::assertSame($positive,true===$result,'Always-unavailable owner must fail.');
	$round=false; $seal=false; $post_results=0; $post_freed=0; $observer=null;
	if($positive){
		$literal="quote ' slash \\ percent %s %d 100% nul\0 end"; $GLOBALS['boot_store']->row=array('option_value'=>$literal,'autoload'=>'no');
		$sql=$connection->prepare_checked('SELECT %s AS bytes',$literal); $ack=$connection->execute($sql);
		$post_results=count($connection->last_result); $round=1===$ack['affected']&&0===$ack['errno']&&$literal===$connection->last_result[0]->option_value;
		// This is a RAM result lifecycle, not genuine mysqli result-free evidence.
		$post_freed=$post_results; $connection->last_result=array();
		$observer=static function(){++$GLOBALS['boot_observer_calls'];}; add_filter('all',$observer,1);
		$physical=$connection->physical; try{$connection->execute('SELECT never');}catch(RuntimeException $error){$seal=$physical===$connection->physical&&0===$GLOBALS['boot_observer_calls'];}
	}
	if($positive)$GLOBALS['boot_after']['close']=static function()use($connection){PHPUnit\Framework\Assert::assertNull($connection->profile(),'Revoke before fallible cleanup.');PHPUnit\Framework\Assert::assertNull(boot_get($connection,'qualification'));PHPUnit\Framework\Assert::assertInstanceOf(WP_Error::class,$connection->admit_profile());};
	$close=$connection->close(); $counts=$GLOBALS['boot_counts']; $again=$connection->close();
	$cached=$close===$again&&$counts===$GLOBALS['boot_counts']; if($observer)remove_filter('all',$observer,1);
	foreach($probe_callbacks as $probe)remove_filter($probe[0],$probe[1],$probe[2]);
	$preflight=array_search($case,$cases,true)<4; $connected=!empty($GLOBALS['boot_handle']->connected); $allocated=$counts['allocate'];
	$vector=static function($c,$post,$active_count)use($connected){return implode(',',array((string)(int)$connected,(string)($c['connect']-(int)$connected),(string)($c['commands']+$post),(string)($c['results']+$post),(string)($c['freed']+$post),'0','0','0','0','0',$active_count));};
	// Counter strings are explicit model projections, not observations of a real driver.
	$counter_active='missing-database'===$case?Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::WRAP:($active?'1':'0');
	$values=array('ADBC1',str_repeat('a',32),$case,Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::PROFILE,hash('sha256','isolated-cleanup-fixture'),(string)(100+array_search($case,$cases,true)),implode(',',array_fill(0,11,'0')),$vector($boot,0,$counter_active),$vector($counts,$connection->physical,'missing-database'===$case?$counter_active:'0'),(string)$allocated,$positive?'active':'unavailable',$active?'1':'0',$close?'1':'0',(string)$counts['close'],$cached?'1':'0',$context->owns($connection)?'1':'0',isset($GLOBALS['wpdb'])?'1':'0',null===$connection->dbh?'1':'0',null===$connection->profile()?'1':'0',(null===boot_get($connection,'bootstrap_frame')&&null===boot_get($connection,'bootstrap_binding')&&''===$connection->dbpassword)?'1':'0',(!has_filter('query')&&!has_filter('all')&&!has_filter('incompatible_sql_modes'))?'1':'0',($counts['results']===$counts['freed']&&$post_results===$post_freed&&array()===$connection->last_result)?'1':'0',(!empty($GLOBALS['boot_handle']->closed))?'1':'0',$preflight?'preflight':boot_get($connection,'native_state'),(string)$GLOBALS['boot_observer_calls'],(string)boot_get($connection,'bootstrap_position'),(string)$connection->physical,$round?'1':'0',$seal?'1':'0','1');
	$row=array_combine(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::fields(),$values);
	PHPUnit\Framework\Assert::assertTrue(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches($row,str_repeat('a',32),$case,hash('sha256','isolated-cleanup-fixture'),100+array_search($case,$cases,true)),'Actual isolated owner facts must satisfy the contract: '.implode('|',$values));
	echo implode('|',$values),"\n";
} catch(Throwable $error){fwrite(STDERR,'Isolated cleanup owner '.$case.' '.get_class($error).' '.$error->getMessage()."\n");exit(1);}
