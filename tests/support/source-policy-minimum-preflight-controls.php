<?php
/** Fixed in-memory semantic controls, never artifact loading or file mutation. */
if(!isset($argv)||4!==count($argv)||'--synthetic-preflight-control'!==$argv[1])return;
$root=$argv[2];$mutation=$argv[3];
$edits=array(
	'path'=>array('launch-data',"! adbmin_path( substr( \$facts['root'], 3 ) )",'false'),
	'dependency'=>array('launch-data',"self::dependencies() !== \$facts['dependencies']",'false'),
	'environment'=>array('launch-data',"!== \$facts['environment']","!== \$facts['environment'] && false"),
	'role'=>array('launch-data',"\$facts['files'][ \$role ] !== \$hash",'false'),
	'runtime'=>array('launch-data','$expected === $facts','true'),
	'modules'=>array('launch-data','$want !== $have','false'),
	'module-path'=>array('launch-data',"\$path !== \$row['path']",'false'),
	'frame'=>array('preflight-transcript','$line !== $expected[1]','false'),
	'channel'=>array('preflight-transcript','$channel !== $expected[0]','false'),
	'clock'=>array('preflight-transcript','$now >= 25000','false'),
	'cleanup'=>array('preflight-transcript','! $this->failed && 16 === $this->step','! $this->failed && 15 <= $this->step'),
	'stderr'=>array('preflight-transcript',"if ( '' !== \$bytes )",'if ( false )'),
);
if('none'!==$mutation&&!isset($edits[$mutation]))exit(2);
require_once $root.'/scripts/manual/source-policy-minimum-artifacts.php';
foreach(array('launch-data','preflight-transcript') as $name){
	if('none'===$mutation){require_once $root.'/scripts/manual/source-policy-minimum-'.$name.'.php';continue;}
	$source=file_get_contents($root.'/scripts/manual/source-policy-minimum-'.$name.'.php');
	if($edits[$mutation][0]===$name){$source=str_replace($edits[$mutation][1],$edits[$mutation][2],$source,$count);if(1!==$count)exit(2);}
	$source=str_replace(array("require_once __DIR__ . '/source-policy-minimum-artifacts.php';","require_once __DIR__ . '/source-policy-minimum-launch-data.php';"),'',substr($source,5));eval($source);
}
require_once $root.'/tests/support/source-policy-minimum-preflight-fixtures.php';
$launch='Alynt_Drime_Backups_Dashboard_Minimum_Launch_Data';$f=minimum_launch_fixture();$r=$launch::inspect($f);$pass=is_array($r)&&$r['synthetic_consistent']&&!$r['execution']&&!$r['platform_verified'];
$bad=$f;$bad['root']='C:/Temp/../alynt-minimum-'.$bad['run'];$bad['environment']['TEMP']=$bad['root'].'/tmp';$bad['environment']['TMP']=$bad['root'].'/tmp';$pass=$pass&&false===$launch::inspect($bad);
$bad=$f;$bad['dependencies']['unknown.dll']='platform-profile';$pass=$pass&&false===$launch::inspect($bad);
$bad=$f;$bad['environment']['PHPRC']='C:/User';$pass=$pass&&false===$launch::inspect($bad);
$bad=$f;$bad['files']['php.exe']=str_repeat('0',64);$pass=$pass&&false===$launch::inspect($bad);
$facts=$launch::runtime_template('mysqli',str_repeat('a',64),str_repeat('b',64));$pass=$pass&&$launch::runtime_data('mysqli',$facts,str_repeat('a',64),str_repeat('b',64));$facts['mode_before']=3;$pass=$pass&&!$launch::runtime_data('mysqli',$facts,str_repeat('a',64),str_repeat('b',64));
$rows=minimum_modules_fixture('base');$pass=$pass&&is_array($launch::module_data('base',$f,$rows,$rows));$bad=$rows;$bad[3]['sha256']=str_repeat('0',64);$pass=$pass&&false===$launch::module_data('base',$f,$rows,$bad);$bad=$rows;$bad[0]['path']='C:/User/php.exe';$pass=$pass&&false===$launch::module_data('base',$f,$bad,$bad);
$events=minimum_preflight_lines();$o=minimum_preflight_owner();foreach($events as $i=>$event){$pass=$o->{$event[0]}($event[1],$i)&&$pass;if(14===$i)$pass=$pass&&!$o->snapshot()['synthetic_complete'];}$pass=$pass&&$o->snapshot()['synthetic_complete']&&!$o->snapshot()['execution']&&!$o->snapshot()['native_qualified'];
$o=minimum_preflight_owner();$pass=$pass&&!$o->worker(str_replace(str_repeat('d',64),str_repeat('b',64),$events[0][1]),0);
$o=minimum_preflight_owner();$pass=$pass&&!$o->controller($events[0][1],0);$o=minimum_preflight_owner();$pass=$pass&&!$o->worker($events[0][1],25000);$o=minimum_preflight_owner();$pass=$pass&&!$o->stderr('not retained',0);
$pass=$pass&&!extension_loaded('mysqli')&&!extension_loaded('pdo_mysql')&&!function_exists('json_encode');echo $pass?'contract-pass':'contract-killed';exit($pass?0:1);
