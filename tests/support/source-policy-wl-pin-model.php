<?php
/** Actual pin-guard fragment with synthetic metadata only; no core or native execution. */
namespace AdbWlPinModel;
use PHPUnit\Framework\Assert as A;
if (isset($argv[0]) && \realpath($argv[0]) === __FILE__) {
if(PHP_SAPI!=='cli'||count($argv)!==4||\extension_loaded('mysqli')||\extension_loaded('pdo_mysql'))exit(1);
require \dirname(__DIR__,2).'/vendor/autoload.php';
define('ADB_WL_REQUEST_PROOF',true);define('ADB_M_REQUEST_PROOF',true);
function adbg_check($condition,$label){$GLOBALS['labels'][]=$label;if(!$condition)throw new \RuntimeException('Modeled pin refusal');}
function filesize($path){$GLOBALS['sizes'][]=$path;return $GLOBALS['scenario']==='size'&&\basename($path)==='class-wp-metadata-lazyloader.php'?6834:($GLOBALS['scenario']==='missing'&&\basename($path)==='class-wp-metadata-lazyloader.php'?false:($GLOBALS['expected'][\basename($path)][0]??100));}
function hash_file($algorithm,$path){A::assertSame('sha256',$algorithm);$GLOBALS['hashes'][]=$path;return $GLOBALS['scenario']==='hash'&&\basename($path)==='class-wp-metadata-lazyloader.php'?\str_repeat('f',64):$GLOBALS['expected'][\basename($path)][1];}
$scenario=$argv[1];$GLOBALS['scenario']=$scenario;$GLOBALS['sizes']=array();$GLOBALS['hashes']=array();$GLOBALS['labels']=array();
try{
 require $argv[2];$profile=$scenario==='w'?'w':'l';\putenv('WL_REQUEST_PROFILE='.$profile);$w=$profile==='w';$row=\adbwl_catalog();
 $dependency='class-wp-metadata-lazyloader.php';$pin=array(6833,'c1df481157f06db91ab7f81fe68981db206beaa38721e6cab49a064110219467');
 A::assertSame($w?9:10,\count($row['auth']));A::assertSame($w?null:$pin,$row['auth'][$dependency]??null);
 A::assertSame($w?'fc9832bef919c2a08541248db23af90d14ffb082':'db96f5eaa77a8d3e8eec8923a518fa2326f0d46a',$row['commit']);
 A::assertCount(12,$row['pins']);A::assertSame(array(),\array_intersect_key($row['pins'],$row['auth']));
 if($scenario==='extra')$row['auth']['unapproved.php']=array(100,\str_repeat('a',64));
 if($scenario==='overlap'){unset($row['auth']['pluggable.php']);$row['auth']['version.php']=array(100,$row['pins']['version.php']);}
 $GLOBALS['expected']=array();foreach($row['pins']as$name=>$hash)$GLOBALS['expected'][$name]=array(100,$hash);
 foreach($row['auth']as$name=>$value)$GLOBALS['expected'][$name]=$value;
 define('ABSPATH','/virtual/core/');define('WPINC','wp-includes');$failed=false;
 try{require $argv[3];}catch(\RuntimeException $error){$failed=true;}
 A::assertSame(!\in_array($scenario,array('positive','w'),true),$failed,'Actual pin guard decision');
 if(\in_array($scenario,array('extra','overlap'),true)){A::assertCount(0,$GLOBALS['sizes']);A::assertCount(0,$GLOBALS['hashes']);}
 if(!$failed){A::assertCount($w?21:22,$GLOBALS['hashes']);A::assertCount($w?9:10,$GLOBALS['sizes']);}
 A::assertFalse(\class_exists('WP_Metadata_Lazyloader',false));A::assertFalse(\class_exists('wpdb',false));A::assertFalse(\function_exists('mysqli_init'));
 echo 'checks:',A::getCount(),"\n";
}catch(\Throwable $error){\fwrite(STDERR,\get_class($error)."\n");exit(1);}
}
