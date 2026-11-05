<?php
/** Extension-free execution of the actual proof owner-construction function. */
if(defined('ABSPATH'))return;
if(PHP_SAPI!=='cli'||!isset($argv)||count($argv)!==2||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
define('ABSPATH',dirname(__DIR__,2).'/');define('ADB_POLICY_ISOLATED',true);define('ADB_MINIMUM_DISTRIBUTION',true);
define('DB_CHARSET','configured-charset'===$argv[1]?'utf8mb4':'utf8');define('DB_COLLATE','explicit-collation'===$argv[1]?'utf8mb4_unicode_520_ci':'');define('MULTISITE',false);define('WP_CONTENT_DIR',__DIR__);
require ABSPATH.'vendor/autoload.php';require __DIR__.'/wordpress-shims.php';
foreach(array('isolated-wordpress','bootstrap-driver','bootstrap-metadata','isolated-wpdb')as$file)require __DIR__.'/source-policy-'.$file.'.php';
foreach(array('class-source-policy.php','class-source-policy-save-contract.php','class-source-policy-native-profile.php','class-source-policy-query-context.php','class-source-policy-stored-map.php','class-source-policy-no-reconnect-connection.php','class-source-policy-native-adapter.php')as$file)require ABSPATH.'includes/'.$file;
require __DIR__.'/source-policy-isolated-fixtures.php';
use PHPUnit\Framework\Assert as A;
if(defined('ADB_POLICY_ISOLATED')) {
function adbb_bind($object,$name,$value){boot_set($object,$name,$value);}
function adbb_construct($reflection){++$GLOBALS['owned_constructs'];$GLOBALS['boot_counts']['options']=0;$object=$reflection->newInstanceWithoutConstructor();boot_invoke($object,'__construct');$GLOBALS['boot_connection']=$object;return$object;}
function adbb_check($value,$label){A::assertTrue($value,$label);}
}
foreach(array('distribution-contract','native-contract','native-operation','bootstrap-diagnostics')as$file)require ABSPATH.'scripts/ci/source-policy-minimum-'.$file.'.php';
try{
	$GLOBALS['policy_bootstrap_model']=true;$GLOBALS['policy_ext_cache']=false;$GLOBALS['wp_object_cache']=new WP_Object_Cache();$GLOBALS['wp_filter']=array();$GLOBALS['policy_trace']=array();$GLOBALS['policy_cache_fail']=false;$GLOBALS['policy_cache']=array();
	$GLOBALS['boot_counts']=array_fill_keys(array('object','allocate','options','connect','commands','results','freed','close','unexpected_connect'),0);
	$GLOBALS['boot_events']=array();$GLOBALS['boot_after']=array();$GLOBALS['boot_rewrite']=array();$GLOBALS['boot_fault']='';$GLOBALS['boot_store']=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(9=>array('wpvivid'=>'external_optional')),'no');
	$GLOBALS['boot_server']='5.5.5-10.6.23-MariaDB';$GLOBALS['boot_report_mode']=0;$GLOBALS['owned_constructs']=0;
	$run=substr(hash('sha256','123:1'),0,32);
	$job=array('GITHUB_ACTIONS'=>'true','GITHUB_EVENT_NAME'=>'workflow_dispatch','GITHUB_REPOSITORY'=>'NichlasB/alynt-drime-backups-dashboard','GITHUB_REF'=>'refs/heads/master','GITHUB_WORKFLOW_REF'=>'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-minimum-distribution.yml@refs/heads/master','GITHUB_ACTOR'=>'NichlasB','RUNNER_OS'=>'Linux','RUNNER_ENVIRONMENT'=>'github-hosted','GITHUB_RUN_ATTEMPT'=>'1','GITHUB_RUN_ID'=>'123','GITHUB_SHA'=>str_repeat('a',40),'EXPECTED_SHA'=>str_repeat('a',40),'MINIMUM_MODEL'=>'trusted-distribution-legacy-v1','DISTRIBUTION_EXECUTION_APPROVED'=>'true','MINIMUM_IMAGE'=>str_repeat('c',64));
	foreach($job as$key=>$value)putenv($key.'='.$value);foreach(array('MINIMUM_SCOPE','DOCKER_HOST','DOCKER_CONTEXT')as$key)putenv($key);
	$evidence=array('model'=>$job['MINIMUM_MODEL'],'platform'=>'Linux','arch'=>'x86_64','php'=>'7.4.33','client'=>'mysqlnd 7.4.33','core_commit'=>'cc101b64012b16d087780657a2b828ccd7794a63','runtime_image'=>'sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4','server_image'=>'sha256:14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1','initial_mode'=>0,'head'=>$job['GITHUB_SHA'],'image'=>$job['MINIMUM_IMAGE'],'run'=>$run);
	$frame=array('core'=>'6.0','database'=>'adb_minimum','table'=>'adbm_'.substr($run,0,12).'_options','host'=>'127.0.0.1','port'=>3306,'user'=>'adbm','password'=>'owned-private-password','charset'=>DB_CHARSET,'collate'=>DB_COLLATE,'transport'=>'tcp_loopback','socket'=>null,'db_host'=>'127.0.0.1:3306');
	$binding=array('core_file'=>__DIR__.'/source-policy-isolated-wpdb.php','driver_file'=>__DIR__.'/source-policy-bootstrap-driver.php','cache_file'=>__DIR__.'/source-policy-isolated-wordpress.php','cache_api_file'=>__DIR__.'/source-policy-isolated-wordpress.php','parser_api_file'=>__DIR__.'/wordpress-shims-sanitization.php','observers'=>array(),'observer_files'=>array());
	$GLOBALS['boot_expected_endpoint']=array('127.0.0.1','adbm','owned-private-password','adb_minimum',3306,null,0);
	foreach(array('identity','session')as$step)$GLOBALS['boot_rewrite'][$step]=static function($rows){$rows[0]['db']='adb_minimum';return$rows;};
	$GLOBALS['boot_rewrite']['session']=static function($rows){$rows[0]['db']='adb_minimum';$rows[0]['collation']='utf8mb4_unicode_ci';return$rows;};
	$owners=array();$ambient=null;$class=new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection');
	$case=$argv[1];
	if('old-model'===$case)$evidence['model']='trusted-lab-functional-v1';
	if('foreign-source'===$case)$evidence['image']=str_repeat('f',64);
	if('wrong-job'===$case)putenv('DISTRIBUTION_EXECUTION_APPROVED=false');
	$serverFaults=array('unprefixed'=>'10.6.23-MariaDB','double-prefix'=>'5.5.5-5.5.5-10.6.23-MariaDB','newer-server'=>'5.5.5-10.11.18-MariaDB-ubu2204','wrong-build'=>'5.5.5-10.6.24-MariaDB');
	if(isset($serverFaults[$case]))$GLOBALS['boot_server']=$serverFaults[$case];
	if(0===strpos($case,'evidence-')){$key=substr($case,9);$evidence[$key]=is_int($evidence[$key])?3:$evidence[$key].'x';}
	if(0===strpos($case,'job-'))putenv(substr($case,4).'=other');
	if(0===strpos($case,'frame-')){$key=substr($case,6);$frame[$key]=is_int($frame[$key])?10004:'wrong';}
	if('helper'===$case)$GLOBALS['boot_helper_wrong']=true;
	if('helper-collation'===$case)$GLOBALS['boot_after']['charset']=static function(){$GLOBALS['boot_connection']->collate='utf8mb4_general_ci';};
	if('schema'===$case)$GLOBALS['boot_rewrite']['table']=static function($rows){$rows[0]['collation']='utf8mb4_unicode_ci';return$rows;};
	if(0===strpos($case,'session-')){$key=substr($case,8);$GLOBALS['boot_rewrite']['session']=static function($rows)use($key){$rows[0]['db']='adb_minimum';$rows[0]['collation']='utf8mb4_unicode_ci';$rows[0][$key]='wrong';return$rows;};}
	if('late-context'===$case)$GLOBALS['boot_after']['identity']=static function(){ $owner=$GLOBALS['boot_connection'];boot_set($owner,'query_context',clone boot_get($owner,'query_context'));};
	if('late-frame'===$case)$GLOBALS['boot_after']['identity']=static function(){ $owner=$GLOBALS['boot_connection'];$frame=boot_get($owner,'bootstrap_frame');$frame['table']='other_options';boot_set($owner,'bootstrap_frame',$frame);};
	$refused=false;try{$owner=adb0_owner();}catch(Throwable $error){$refused=true;}
	if(in_array($case,array('old-model','foreign-source','wrong-job'),true)||0===strpos($case,'evidence-')||0===strpos($case,'job-')){
		A::assertTrue($refused);A::assertSame(0,$GLOBALS['owned_constructs']);A::assertSame(0,$GLOBALS['boot_counts']['allocate']);A::assertSame(array(),$owners);
	}elseif(0===strpos($case,'frame-')||in_array($case,array('configured-charset','explicit-collation'),true)){
		A::assertTrue($refused);A::assertSame(0,$GLOBALS['boot_counts']['allocate']);A::assertFalse($owners[0]->ready);
	}elseif(isset($serverFaults[$case])){
		A::assertTrue($refused);A::assertSame(1,$GLOBALS['boot_counts']['allocate']);A::assertSame(1,$GLOBALS['boot_counts']['connect']);A::assertSame(0,$GLOBALS['boot_counts']['commands']);A::assertSame(1,$GLOBALS['boot_counts']['close']);A::assertFalse($owners[0]->ready);
	}elseif(in_array($case,array('helper','helper-collation','schema','late-context','late-frame'),true)||0===strpos($case,'session-')){
		A::assertTrue($refused);A::assertFalse($owners[0]->ready);A::assertSame(1,$GLOBALS['boot_counts']['close']);
		if(0===strpos($case,'late-'))A::assertSame(1,$GLOBALS['boot_counts']['commands']);else{A::assertSame(8,boot_get($owners[0],'bootstrap_position'));A::assertSame(12,boot_get($owners[0],'bootstrap_credits'));}
	}else{
		A::assertFalse($refused);A::assertTrue($owner->ready);A::assertIsObject(boot_get($owner,'minimum_lab_scope'));A::assertSame('5.5.5-10.6.23-MariaDB',$owner->profile()['server']);A::assertSame('utf8mb4_unicode_ci',$owner->collate);
		if(0===strpos($case,'active-')){
			if('active-facts'===$case){$facts=boot_get($owner,'qualification');$facts['server']='10.6.23-MariaDB';boot_set($owner,'qualification',$facts);}elseif('active-charset'===$case)$owner->charset='utf8';else $owner->collate='utf8mb4_unicode_520_ci';
			$counts=$GLOBALS['boot_counts'];A::assertInstanceOf(WP_Error::class,$owner->admit_profile());A::assertFalse($owner->query('SELECT never-dispatched'));A::assertSame($counts,$GLOBALS['boot_counts']);
		}else{
		A::assertSame('positive',$case);$scope=boot_get($owner,'minimum_lab_scope');$context=boot_get($owner,'query_context');A::assertFalse($scope->expectation($owner,clone $context));A::assertFalse($scope->expectation(clone $owner,$context));A::assertInstanceOf(WP_Error::class,Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::admit($owner->profile(),$frame['table'],$context));
		$adapter=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter'))->newInstanceWithoutConstructor();boot_set($adapter,'connection',$owner);
		$view=$adapter->view_source_mode(7,'wpvivid');A::assertSame('required',$view['mode']);A::assertSame(64,strlen($view['revision']));A::assertTrue($owner->close());A::assertFalse($owner->ready);
		$GLOBALS['boot_counts']['options']=0;$fresh=adb0_owner();$save=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter'))->newInstanceWithoutConstructor();boot_set($save,'connection',$fresh);
		A::assertSame('saved',$save->save_source_mode_if_current($view['revision'],7,'wpvivid','external_optional'));A::assertSame(1,$GLOBALS['boot_store']->mutations);A::assertSame(array(9=>array('wpvivid'=>'external_optional'),7=>array('wpvivid'=>'external_optional')),unserialize($GLOBALS['boot_store']->row['option_value']));A::assertFalse($fresh->ready);A::assertTrue($fresh->close());
		$conflictOwner=adb0_owner();$conflictAdapter=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter'))->newInstanceWithoutConstructor();boot_set($conflictAdapter,'connection',$conflictOwner);$conflict=$conflictAdapter->save_source_mode_if_current($view['revision'],7,'wpvivid','required');A::assertSame('source_policy_conflict',$conflict->get_error_code());A::assertSame(1,$GLOBALS['boot_store']->mutations);
		}
	}
	foreach($owners as$owned){
		if(0===strpos($case,'frame-')||in_array($case,array('configured-charset','explicit-collation'),true)){A::assertFalse($owned->close());A::assertNull($owned->dbh);A::assertFalse(has_filter('query',array($owned,'remove_placeholder_escape')));}
		else A::assertTrue($owned->close());
		A::assertNull(boot_get($owned,'minimum_lab_scope'));
	}A::assertSame(0,$GLOBALS['boot_counts']['unexpected_connect']);A::assertInstanceOf(WP_Error::class,Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::create());
	echo 'checks:'.A::getCount()."\n";
}catch(Throwable $error){fwrite(STDERR,get_class($error).':'.$error->getMessage()."\n".$error->getTraceAsString()."\n");exit(1);}
