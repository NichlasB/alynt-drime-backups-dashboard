<?php
/** In-memory W/L case-boundary verifier model; no WordPress, SQL or native APIs. */
use PHPUnit\Framework\Assert as A;
if (isset($argv[0]) && realpath($argv[0]) === __FILE__) {
if(PHP_SAPI!=='cli'||count($argv)!==3||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
require dirname(__DIR__,2).'/vendor/autoload.php';
define('ADB_WL_REQUEST_PROOF',true);define('ARRAY_A','ARRAY_A');
function adbg_check($condition,$label){A::assertTrue($condition,$label);}
function adbg_prefix(){return 'adbm_123456789abc_';}
function model_rows(){return array(array('option_name'=>adbg_prefix().'user_roles','option_value'=>serialize(array('capable'=>array('name'=>'Owned capable','capabilities'=>array('manage_options'=>true,'read'=>true)),'incapable'=>array('name'=>'Owned incapable','capabilities'=>array('read'=>true)))),'autoload'=>'yes'),array('option_name'=>'owned_non_target','option_value'=>'preserve-exact-bytes','autoload'=>'no'));}
function adbg_seed(){$GLOBALS['trace'][]='seed';}
function adbg_non_target(){
 $GLOBALS['trace'][]='non-target';$rows=model_rows();
 if($GLOBALS['scenario']==='roles')$rows[0]['option_value']='bad';
 if($GLOBALS['scenario']==='nontarget'&&$GLOBALS['verified'])$rows[1]['option_value']='changed';
 return $rows;
}
function adbg_reset_case($scenario){A::assertSame($GLOBALS['operation'],$scenario);$GLOBALS['trace'][]='reset';}
function adbg_preflight($expected='no'){A::assertSame($GLOBALS['operation']==='save'?'off':'no',$expected,'Exact post-write/preflight expectation');$GLOBALS['trace'][]='preflight';if($GLOBALS['scenario']==='preflight')adbg_check(false,'modeled bounded verifier failure');}
$GLOBALS['scenario']=$argv[1];$GLOBALS['trace']=array();$GLOBALS['verified']=false;$GLOBALS['operation']=array('view'=>'view','noop'=>'noop','stale'=>'stale','denial'=>'cookie-missing','view-autoload-off'=>'view')[$argv[1]]??'save';
$GLOBALS['wpdb']=new class {
 public $last_error='';
 function get_var($sql){A::assertMatchesRegularExpression('/\ASELECT COUNT\(\*\) FROM adbm_123456789abc_(users|usermeta|options|alynt_drime_dashboard_sites)\z/',$sql);$GLOBALS['trace'][]='empty';if($GLOBALS['scenario']==='empty-read'){$this->last_error='modeled';return null;}return $GLOBALS['scenario']==='nonempty'?'1':'0';}
 function prepare($sql,$key){A::assertSame('alynt_drime_backups_dashboard_source_policies',$key);return str_replace('%s',"'".$key."'",$sql);}
 function get_row($sql,$format){
  A::assertSame(ARRAY_A,$format);A::assertSame("SELECT option_value,autoload FROM adbm_123456789abc_options WHERE BINARY option_name=BINARY 'alynt_drime_backups_dashboard_source_policies'",$sql);$GLOBALS['trace'][]='policy';
  if($GLOBALS['scenario']==='verify-read'){$this->last_error='modeled';return null;}
  $map=array(9=>array('wpvivid'=>'external_optional'));if($GLOBALS['operation']==='save')$map[7]=array('wpvivid'=>'external_optional');
  $autoload=$GLOBALS['operation']==='save'?'off':'no';if($GLOBALS['scenario']==='autoload')$autoload='yes';if($GLOBALS['scenario']==='save-autoload-no')$autoload='no';if($GLOBALS['scenario']==='view-autoload-off')$autoload='off';
  return array('option_value'=>$GLOBALS['scenario']==='policy'?'wrong':serialize($map),'autoload'=>$autoload);
 }
};
try{
 require $argv[2];$scenario=$GLOBALS['scenario'];$GLOBALS['argv'][2]=$scenario==='next'?'1':'0';$failed=false;
 try{$before=adbwl_prepare_case($GLOBALS['operation']);$GLOBALS['verified']=true;adbwl_verify_case($GLOBALS['operation'],$before);}catch(PHPUnit\Framework\ExpectationFailedException $error){$failed=true;}
 A::assertSame(!in_array($scenario,array('positive','next','view','noop','stale','denial'),true),$failed,'Discriminating actual invariant/failed-read guard');
 if(!$failed){$expected=$scenario!=='next'?array('empty','empty','empty','empty','seed'):array();$expected=array_merge($expected,array('non-target','reset','preflight','non-target','policy'));A::assertSame($expected,$GLOBALS['trace'],'Exact case seed/reset/preflight/readback order');}
 A::assertFalse(class_exists('wpdb',false));A::assertFalse(function_exists('mysqli_init'));
 echo 'checks:',A::getCount(),"\n";
}catch(Throwable $error){fwrite(STDERR,get_class($error).' '.$error->getMessage()."\n");exit(1);}
}
