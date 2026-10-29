<?php
/** Fixed in-memory guard controls in the existing PHP -n; no file mutation. */
if(!isset($argv)||4!==count($argv)||'--synthetic-minimum-control'!==$argv[1])return;
$root=$argv[2];$mutation=$argv[3];
$edits=array(
	'case-fold'=>array('artifacts','isset( $names[ $folded ] ) && $names[ $folded ] !== $prefix','false'),
	'digest'=>array('artifacts',"hash_equals( \$expected, hash( 'sha256', \$bytes ) )",'true'),
	'machine'=>array('pe',"0x8664 !== \$coff['machine']",'false'),
	'frame'=>array('runner','$this->prefix . $events[ $this->step ] . "\\n" !== $line','false'),
	'mode'=>array('runner','public static function runtime_data( array $facts ) {','public static function runtime_data( array $facts ) { $facts["mode"] = 0;'),
);
if('none'!==$mutation&&!isset($edits[$mutation]))exit(2);
foreach(array('artifacts','pe','tree','runner') as $name){
	if('none'===$mutation){require_once $root.'/scripts/manual/source-policy-minimum-'.$name.'.php';continue;}
	$source=file_get_contents($root.'/scripts/manual/source-policy-minimum-'.$name.'.php');
	if('none'!==$mutation&&$edits[$mutation][0]===$name){$source=str_replace($edits[$mutation][1],$edits[$mutation][2],$source,$count);if(1!==$count)exit(2);}
	// All declarations are loaded once in dependency order, with no on-disk edit.
	$source=str_replace("require_once __DIR__ . '/source-policy-minimum-artifacts.php';",'',substr($source,5));
	$source=str_replace('__DIR__',var_export($root.'/scripts/manual',true),$source);eval($source);
}
require_once $root.'/tests/support/source-policy-minimum-fixtures.php';
$good=array(minimum_test_entry('Dir/a'),minimum_test_entry('Dir/b'));$bad=$good;$bad[1]['path']='dir/b';
$pass=is_array(adbmin_inventory('php',$good,100,50))&&false===adbmin_inventory('php',$bad,100,50);
$pass=$pass&&adbmin_digest('synthetic',hash('sha256','synthetic'))&&!adbmin_digest('synthetic',str_repeat('0',64));
$pe=minimum_test_pe();$pass=$pass&&is_array(adbmin_pe($pe,'php.exe'))&&false===adbmin_pe(substr_replace($pe,pack('v',0x14c),132,2),'php.exe');
$facts=array('os'=>'Windows','php'=>'7.4.33','mysqlnd'=>'7.4.33','int_size'=>8,'zts'=>false,'mode'=>0,'core'=>'6.0','mysqli_internal'=>true,'autoload'=>'no');
$pass=$pass&&Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript::runtime_data($facts);$facts['mode']=3;$pass=$pass&&!Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript::runtime_data($facts);
$run=str_repeat('a',32);$image=str_repeat('b',64);$lines=minimum_test_lines($run,$image);$owner=new Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript($run,$image);
foreach($lines as $index=>$line)$pass=$owner->feed($line,$index)&&$pass;
$pass=$pass&&$owner->snapshot()['synthetic_complete']&&!$owner->snapshot()['execution'];
$other=new Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript($run,$image);$pass=$pass&&!$other->feed(str_replace($run,str_repeat('c',32),$lines[0]),0);
$pass=$pass&&!extension_loaded('mysqli')&&!extension_loaded('pdo_mysql')&&!function_exists('json_encode');
echo $pass?'contract-pass':'contract-killed';exit($pass?0:1);
