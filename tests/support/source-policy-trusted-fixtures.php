<?php
/** Isolated API counterparts only. Private Reflection is fixture composition, not authority. */
if(defined('ADB_POLICY_ISOLATED')) {
function trusted_fixture() {
	$GLOBALS['policy_capable']=true;$GLOBALS['policy_trace']=array();$GLOBALS['wp_filter']=array();$GLOBALS['policy_cache_fail']=false;$GLOBALS['policy_cache']=array();
	$GLOBALS['policy_ext_cache']=false;$GLOBALS['wp_object_cache']=new WP_Object_Cache();$GLOBALS['boot_fault']='';$GLOBALS['boot_report_mode']=3;
	$GLOBALS['wp_version']='7.1.3';$GLOBALS['boot_parser_core']='7.1.3';$GLOBALS['boot_counts']=array_fill_keys(array('allocate','connect','commands'),0);
	$ambient=(new ReflectionClass('wpdb'))->newInstanceWithoutConstructor();$ambient->dbh=new stdClass();$GLOBALS['wpdb']=$ambient;
	$store=new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array());
	$receiver=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::class))->newInstanceWithoutConstructor();boot_invoke($receiver,'__construct');
	$receiver->store=$store;$receiver->dbh=true;$receiver->ready=true;
	$query=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context::class))->newInstanceWithoutConstructor();
	foreach(array('owned'=>$receiver,'ambient'=>$ambient,'core_file'=>__DIR__.'/source-policy-isolated-wpdb.php')as$key=>$value)boot_set($query,$key,$value);
	PHPUnit\Framework\Assert::assertTrue($query->begin());
	$facts=array('core'=>'7.1.3','server'=>'10.6.23-MariaDB','database'=>'isolated','table'=>'wp_options','schema'=>'options4-unique-name-InnoDB','autocommit'=>true,'single_site'=>true,'dropins'=>false,'cache'=>'core-process','bounded_session'=>true,'hooks'=>Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::fingerprint());
	foreach(array('native_state'=>'active','report_mode'=>3,'report_source'=>__DIR__.'/source-policy-bootstrap-driver.php','qualification'=>$facts,'query_context'=>$query)as$key=>$value)boot_set($receiver,$key,$value);
	$adapter=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter::class))->newInstanceWithoutConstructor();boot_set($adapter,'connection',$receiver);
	$context=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context::class))->newInstanceWithoutConstructor();boot_set($context,'receiver',$receiver);
	$files=array('core_file'=>'source-policy-isolated-wpdb.php','cache_file'=>'source-policy-isolated-wordpress.php','cache_api_file'=>'source-policy-isolated-wordpress.php','parser_api_file'=>'wordpress-shims-sanitization.php','hook_file'=>'source-policy-isolated-wordpress.php');
	$pins=array();foreach(array_unique(array_merge(array_values($files),array('source-policy-bootstrap-driver.php')))as$file)$pins[$file]=hash_file('sha256',__DIR__.'/'.$file);
	$policy=array('core'=>'7.1.3','php'=>PHP_VERSION,'root'=>__DIR__.'/','pins'=>$pins,'files'=>$files,'driver'=>__DIR__.'/source-policy-bootstrap-driver.php');
	boot_set($context,'policy',$policy); // Explicit API-double declaration map; never enabled by a runtime constant or test branch.
	$provider=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Lazy_Provider::class))->newInstanceWithoutConstructor();
	boot_set($provider,'owner',$adapter);boot_set($provider,'context',$context);
	$sites=new Alynt_Drime_Backups_Dashboard_Test_Policy_Sites();
	$entry=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry::class))->newInstanceWithoutConstructor();boot_set($entry,'owner',$provider);boot_set($entry,'sites',$sites);
	$GLOBALS['boot_parse_calls']=0;
	return array($entry,$provider,$context,$adapter,$receiver,$store,$ambient,$sites);
}
function trusted_request($store) {
	$owner=hash('sha256','isolated:wp_options');$observation=Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map::decode($store->row,$owner,array('yes','no','on','off','auto','auto-on','auto-off'));
	return array('_wpnonce'=>'valid','dashboard_site_id'=>'7','source_key'=>'wpvivid','source_mode'=>'external_optional','source_policy_revision'=>$observation['model']->revision());
}
function trusted_error($result,$code) { PHPUnit\Framework\Assert::assertInstanceOf(WP_Error::class,$result);PHPUnit\Framework\Assert::assertSame($code,$result->get_error_code());PHPUnit\Framework\Assert::assertStringNotContainsString('owned-private',serialize($result)); }
}
