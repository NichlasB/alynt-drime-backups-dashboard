<?php
/** Only the owned source copy binds this catalog; no shipped runtime test switch. */
if(defined('ADB_POLICY_ISOLATED')) {
function request_fixture_policy($core) {
	if('6.0'!==$core)return false;
	$files=array('core_file'=>'source-policy-isolated-wpdb.php','cache_file'=>'source-policy-isolated-wordpress.php','cache_api_file'=>'source-policy-isolated-wordpress.php','parser_api_file'=>'wordpress-shims-sanitization.php','hook_file'=>'source-policy-isolated-wordpress.php');
	$pins=array();foreach(array_unique(array_merge(array_values($files),array('source-policy-bootstrap-driver.php')))as$file)$pins[$file]=hash_file('sha256',__DIR__.'/'.$file);
	if('source'===$GLOBALS['request_case'])$pins['source-policy-isolated-wpdb.php']=str_repeat('f',64);
	return array('core'=>'6.0','php'=>PHP_VERSION,'root'=>__DIR__.'/','pins'=>$pins,'files'=>$files,'driver'=>'native-policy'===$GLOBALS['request_case']?null:__DIR__.'/source-policy-bootstrap-driver.php');
}
}
