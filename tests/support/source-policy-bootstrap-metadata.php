<?php
/** Finite metadata DATA, not a real SQL parser or native proof. */
if(defined('ADB_POLICY_ISOLATED')) {
function boot_rows($step){
	if('identity'===$step)return array(array('db'=>'isolated','ac'=>'1','tx'=>'0'));
	if('session'===$step)return array(array('db'=>'isolated','ac'=>'1','tx'=>'0','statement_limit'=>'3.000000','row_limit'=>'1','metadata_limit'=>'1','client_charset'=>'utf8mb4','connection_charset'=>'utf8mb4','result_charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_520_ci','mode'=>$GLOBALS['boot_handle']->mode));
	if('table'===$step)return array(array('engine'=>'InnoDB','collation'=>'utf8mb4_unicode_520_ci'));
	if('columns'===$step)return array(
		array('name'=>'option_id','type'=>'bigint(20) unsigned','nullable'=>'NO','key_kind'=>'PRI','extra'=>'auto_increment'),
		array('name'=>'option_name','type'=>'varchar(191)','nullable'=>'NO','key_kind'=>'UNI','extra'=>''),
		array('name'=>'option_value','type'=>'longtext','nullable'=>'NO','key_kind'=>'','extra'=>''),
		array('name'=>'autoload','type'=>'varchar(20)','nullable'=>'NO','key_kind'=>'MUL','extra'=>'')
	);
	return array(
		array('name'=>'PRIMARY','non_unique'=>'0','sequence'=>'1','column_name'=>'option_id','prefix_length'=>null),
		array('name'=>'autoload','non_unique'=>'1','sequence'=>'1','column_name'=>'autoload','prefix_length'=>null),
		array('name'=>'option_name','non_unique'=>'0','sequence'=>'1','column_name'=>'option_name','prefix_length'=>null)
	);
}
function boot_set($object,$name,$value){$slot=new ReflectionProperty($object,$name); if(PHP_VERSION_ID<80100)$slot->setAccessible(true); $slot->setValue($object,$value);}
function boot_get($object,$name){$slot=new ReflectionProperty($object,$name); if(PHP_VERSION_ID<80100)$slot->setAccessible(true); return $slot->getValue($object);}
function boot_invoke($object,$name,...$args){$method=new ReflectionMethod($object,$name); if(PHP_VERSION_ID<80100)$method->setAccessible(true); return $method->invoke($object,...$args);}
}
