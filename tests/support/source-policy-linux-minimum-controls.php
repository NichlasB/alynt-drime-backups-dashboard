<?php
/** In-memory source mutations only; no fixture writes, candidate or native IO. */
function linux_minimum_failure_diagnostic($phase,$error){
	$phase=in_array($phase,array('source-read','source-parse','fixture','isolation','contract'),true)?$phase:'unknown';
	$kind=is_object($error)&&in_array(get_class($error),array('Error','ParseError','RuntimeException'),true)?get_class($error):'other';
	return 'Unexpected synthetic control failure phase:'.$phase.' kind:'.$kind.' json_encode:'.(int)function_exists('json_encode').' json_decode:'.(int)function_exists('json_decode');
}
if(!isset($argv)||4!==count($argv)||'--synthetic-linux-minimum'!==$argv[1])return;
$root=$argv[2];$mutation=$argv[3];
require_once __DIR__.'/source-policy-linux-minimum-fixtures.php';
$edits=array(
	'always-unavailable'=>array('public static function consistent( $row ) {','public static function consistent( $row ) { return false;'),
	'shape-count'=>array('count( $keys ) !== count( $row )','false'),
	'required-key'=>array('! array_key_exists( $key, $row )','false'),
	'digest-gate'=>array('! self::consistent( $row )','false'),
	'wire-gate'=>array('! self::transcript( $wire, $hash )','false'),
	'parent-gate'=>array('! self::transcript( $expected, $hash )','false'),
	'event'=>array("'tuple-data' === \$row['event']",'true'),
	'tuple-hash'=>array("\$hash === \$row['tuple_hash']",'true'),
	'pid-type'=>array("is_int( \$row['pid'] )",'true'),
	'pid-low'=>array("\$row['pid'] > 0",'true'),
	'pid-high'=>array("\$row['pid'] <= 2147483647",'true'),
);
foreach(array_keys(linux_minimum_tuple_fixture()) as $key)$edits['tuple-'.$key]=array('( $row[ $key ] ?? null ) !== $value',"'".$key."' !== \$key && ( \$row[ \$key ] ?? null ) !== \$value");
foreach(array('head','source_image','run') as $key){
	$edits['hex-'.$key]=array("1 !== preg_match( '/\\A[a-f0-9]+\\z/', \$row[ \$key ] )","( '".$key."' !== \$key && 1 !== preg_match( '/\\A[a-f0-9]+\\z/', \$row[ \$key ] ) )");
	$edits['length-'.$key]=array('strlen( $row[ $key ] ) !== $length',"( '".$key."' !== \$key && strlen( \$row[ \$key ] ) !== \$length )");
}
foreach(array('head','source_image','run','pid') as $key)$edits['parent-'.$key]=array('$wire[ $key ] !== $expected[ $key ]',"'".$key."' !== \$key && \$wire[ \$key ] !== \$expected[ \$key ]");
if('none'!==$mutation&&!isset($edits[$mutation]))exit(2);
function linux_minimum_check($condition){if(!$condition)throw new RuntimeException('linux-minimum-assertion');}
set_error_handler(static function(){throw new RuntimeException('linux-minimum-diagnostic');});
$phase='source-read';
try{
	$source=file_get_contents($root.'/scripts/manual/source-policy-linux-minimum-tuple.php');
	if('none'!==$mutation){$source=str_replace($edits[$mutation][0],$edits[$mutation][1],$source,$count);if(1!==$count)throw new RuntimeException('control-anchor');}
	// The actual helper is evaluated in this fresh extension-free process only.
	$phase='source-parse';eval(substr($source,5));$c='Alynt_Drime_Backups_Dashboard_Linux_Minimum_Tuple';
	$phase='fixture';$row=linux_minimum_tuple_fixture();$wire=linux_minimum_correlation_fixture();
	$phase='isolation';
	linux_minimum_check(!extension_loaded('mysqli')&&!extension_loaded('pdo_mysql'));
	linux_minimum_check('0'===ini_get('allow_url_fopen'));
	linux_minimum_check(str_replace('\\','/',$root.'/scripts/manual'.PATH_SEPARATOR.$root.'/tests/support')===str_replace('\\','/',ini_get('open_basedir')));
	foreach(array('fsockopen','pfsockopen','stream_socket_client','stream_socket_server','exec','shell_exec','system','passthru','proc_open','popen','json_encode','json_decode') as $blocked)linux_minimum_check(in_array($blocked,explode(',',ini_get('disable_functions')),true));
	$phase='contract';
	linux_minimum_check($c::template()===$row&&$c::consistent($row)&&$c::digest($row)===$wire['tuple_hash']);
	linux_minimum_check($c::correlates($row,$wire,$wire));
	foreach(array_keys($row) as $key){$bad=$row;$bad[$key]='wrong';linux_minimum_check(false===$c::consistent($bad));linux_minimum_check(false===$c::digest($bad));}
	$bad=$row;$bad['extra']=null;linux_minimum_check(false===$c::consistent($bad));unset($bad['extra'],$bad['socket']);$bad['other']=null;linux_minimum_check(false===$c::consistent($bad));
	$bad=$wire;$bad['extra']=null;linux_minimum_check(false===$c::correlates($row,$bad,$wire));linux_minimum_check(false===$c::correlates($row,$wire,$bad));
	foreach(array('head'=>40,'source_image'=>64,'run'=>32) as $key=>$size){
		foreach(array(str_repeat('A',$size),str_repeat('a',$size-1),str_repeat('a',$size+1)) as $value){$bad=$wire;$bad[$key]=$value;linux_minimum_check(false===$c::correlates($row,$bad,$bad));}
		$bad=$wire;$bad[$key]=str_repeat('d',$size);linux_minimum_check(false===$c::correlates($row,$bad,$wire));
	}
	foreach(array('event'=>'closed','tuple_hash'=>str_repeat('a',64)) as $key=>$value){$bad=$wire;$bad[$key]=$value;linux_minimum_check(false===$c::correlates($row,$bad,$bad));}
	foreach(array(0,-1,2147483648,'123',true,123.0) as $pid){$bad=$wire;$bad['pid']=$pid;linux_minimum_check(false===$c::correlates($row,$bad,$bad));}
	$bad=$wire;$bad['pid']=124;linux_minimum_check(false===$c::correlates($row,$bad,$wire));
	echo 'contract-pass';exit(0);
}catch(Throwable $error){
	if($error instanceof RuntimeException&&'linux-minimum-assertion'===$error->getMessage()){echo 'assertion-killed';exit(1);}
	fwrite(STDERR,linux_minimum_failure_diagnostic($phase,$error));exit(2);
}
