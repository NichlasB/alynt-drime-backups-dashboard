<?php
/** Isolated actual-source include guard model: synthetic metadata only, no core/native IO. */
namespace AdbWlIncludeModel;
use PHPUnit\Framework\Assert as A;
if (isset($argv[0]) && \realpath($argv[0]) === __FILE__) {
if(PHP_SAPI!=='cli'||count($argv)!==3||\extension_loaded('mysqli')||\extension_loaded('pdo_mysql'))exit(1);
require \dirname(__DIR__,2).'/vendor/autoload.php';
define('ADB_WL_REQUEST_PROOF',true);define('ABSPATH','/repo/core/');define('WPINC','wp-includes');
function adbg_check($condition,$label){$GLOBALS['labels'][]=$label;if(!$condition)throw new \RuntimeException('Modeled guard refusal');}
function adbwl_catalog(){return $GLOBALS['dependency']?\adbwl_catalog():array('pins'=>array('known.php'=>'fixed'),'auth'=>array());}
function get_included_files(){return $GLOBALS['loaded'];}
function realpath($path){if($path===$GLOBALS['root_input'])return '/repo';return $path==='/missing.php'?false:$path;}
function filesize($path){
 $GLOBALS['stats'][]=$path;
 if($path===$GLOBALS['candidate']){
  if($GLOBALS['scenario']==='stat-throw')throw new \RuntimeException('Modeled metadata refusal');
  if($GLOBALS['scenario']==='stat-false')return false;
  if(\in_array($GLOBALS['scenario'],array('size','dependency-size','localization-size'),true))return 1048577;
  if($GLOBALS['scenario']==='boundary')return 1048576;
 }
 return 100;
}
function is_file($path){return true;}
function hash_file($algorithm,$path){A::assertSame('sha256',$algorithm);return \hash('sha256',$path);}
$scenario=$argv[1];$copy=$argv[2];$GLOBALS['scenario']=$scenario;$GLOBALS['root_input']=\dirname(\dirname($copy),2);
$GLOBALS['dependency']=0===\strpos($scenario,'dependency-')||0===\strpos($scenario,'localization-');
if($GLOBALS['dependency']){define('ADB_M_REQUEST_PROOF',true);require \dirname(__DIR__,2).'/scripts/ci/source-policy-wl-request-bootstrap.php';}
$paths=array('scope'=>'/repo/core/wp-includes/unknown.php','first'=>'/repo/core/wp-includes/unknown.php','nested'=>'/repo/core/wp-includes/nested/known.php','repo'=>'/repo/tests/private.php','boundary-path'=>'/repo/includes-other/private.php','foreign'=>'/elsewhere/private.php','missing'=>'/missing.php','size'=>'/repo/includes/target.php','stat-false'=>'/repo/includes/target.php','stat-throw'=>'/repo/includes/target.php','w'=>'/repo/core/wp-includes/unknown.php','proof'=>'/repo/core/wp-includes/unknown.php','ordinal'=>'/repo/core/wp-includes/unknown.php','overflow'=>'/repo/core/wp-includes/unknown.php');
$candidate=$paths[$scenario]??'/repo/core/wp-includes/known.php';$GLOBALS['candidate']=$candidate;
if($scenario==='foreign-equal'){$candidate='/evil/core/wp-includes/known.php';$GLOBALS['candidate']=$candidate;}
if($GLOBALS['dependency']){$candidate=($scenario==='dependency-foreign'?'/elsewhere/':'/repo/core/wp-includes/').($scenario==='dependency-nested'?'nested/':'').'class-wp-metadata-lazyloader.php';$GLOBALS['candidate']=$candidate;}
if(0===\strpos($scenario,'localization-')){$candidate=($scenario==='localization-foreign'?'/elsewhere/':'/repo/core/wp-includes/').($scenario==='localization-nested'?'nested/':'').($scenario==='localization-root'?'':'pomo/').'translations.php';$GLOBALS['candidate']=$candidate;}
$GLOBALS['loaded']=array();for($i=0;$i<32;$i++)$GLOBALS['loaded'][]='/repo/includes/fixed-'.$i.'.php';
$ordinal=$scenario==='ordinal'?511:($scenario==='overflow'?512:32);$GLOBALS['loaded'][$ordinal]=$candidate;
$GLOBALS['stats']=array();$GLOBALS['labels']=array();
\putenv('WL_REQUEST_PROFILE='.(\in_array($scenario,array('w','dependency-w','localization-w'),true)?'w':'l'));\putenv('WL_REQUEST_MODE='.($scenario==='proof'?'proof':'audit'));
try{
 require $copy;$failed=false;
 try{$rows=adbm_source_rows();}catch(\RuntimeException $error){$failed=true;}
 $positive=\in_array($scenario,array('positive','boundary','late','dependency-l','dependency-w','localization-l','localization-w'),true);A::assertSame(!$positive,$failed,'Actual guard decision, not diagnostic availability');
 $outside=\in_array($scenario,array('scope','first','nested','repo','boundary-path','foreign','foreign-equal','missing','w','proof','ordinal','overflow','dependency-nested','dependency-foreign','localization-nested','localization-root','localization-foreign'),true);
 A::assertCount($outside?32:33,$GLOBALS['stats'],'No stat before origin/catalog admission');
 if($outside)A::assertNotContains($candidate,$GLOBALS['stats']);
 if($positive||\in_array($scenario,array('w','proof','overflow','dependency-w','localization-w'),true))A::assertArrayNotHasKey('adbwl_include_failure',$GLOBALS);
 else{
  $origin=\in_array($scenario,array('scope','first','nested','ordinal'),true)?'core':(\in_array($scenario,array('repo','boundary-path','size','stat-false','stat-throw'),true)?'repo':($scenario==='missing'?'unresolved':'foreign'));
  if($GLOBALS['dependency'])$origin=\in_array($scenario,array('dependency-foreign','localization-foreign'),true)?'foreign':'core';
  $identity=$origin==='core'?\substr($candidate,\strlen('/repo/core/wp-includes/')):($origin==='repo'?\substr($candidate,\strlen('/repo/')):($origin==='unresolved'?'':$candidate));
  $reason=\in_array($scenario,array('size','dependency-size','localization-size'),true)?'size':(\in_array($scenario,array('stat-false','stat-throw'),true)?'stat':'scope');
  A::assertSame(array('ordinal'=>$ordinal,'origin'=>$origin,'reason'=>$reason,'path_digest'=>\hash('sha256',$identity)),$GLOBALS['adbwl_include_failure']??null,'Actual first failed include context');
 }
 if($scenario==='first'){
  $first=$GLOBALS['adbwl_include_failure'];$GLOBALS['loaded'][$ordinal]='/repo/includes/after-refusal.php';adbm_source_rows();
  A::assertSame($first,$GLOBALS['adbwl_include_failure']??null,'First refusal survives later inventory/cleanup work');
 }
 if($scenario==='late'){
  $GLOBALS['loaded'][]='/repo/includes/later.php';$failed=false;
  try{adbm_source_rows();}catch(\RuntimeException $error){$failed=true;}
  A::assertTrue($failed,'Late includes still refuse');A::assertSame('no late included code',\end($GLOBALS['labels']));
  A::assertArrayNotHasKey('adbwl_include_failure',$GLOBALS,'No stale context after successful includes');
 }
 A::assertFalse(\class_exists('wpdb',false));A::assertFalse(\function_exists('mysqli_init'));
 echo 'checks:',A::getCount(),"\n";
}catch(\Throwable $error){\fwrite(STDERR,\get_class($error)."\n");exit(1);}
}
