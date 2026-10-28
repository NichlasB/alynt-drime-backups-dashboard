<?php
/** Extension-free failed-connect API fixture; never a native driver implementation. */
if (defined('ABSPATH')) { return; }
define('ABSPATH', __DIR__ . '/');
define('ADB_POLICY_ISOLATED', true);
if (extension_loaded('mysqli') || extension_loaded('pdo_mysql')) { exit(2); }
require_once dirname(__DIR__, 2).'/scripts/manual/source-policy-failed-connect-observation.php';
if (!class_exists('mysqli_sql_exception', false)) { class mysqli_sql_exception extends RuntimeException {} }
$case=$argv[1]??'expected';$checks=0;
function observation_check($truth){global $checks;++$checks;if(!$truth){fwrite(STDERR,'assertion:'.$checks."\n");exit(1);}}
$owner=new Alynt_Drime_Backups_Dashboard_Failed_Connect_Observation();
$io=new class($case,$owner) implements Alynt_Drime_Backups_Dashboard_Cleanup_Observation_IO {
 public $calls=array('allocate'=>0,'owns'=>0,'connect'=>0,'close'=>0,'errno'=>0);
 public $nested=null;private $case;private $owner;private $handle;
 public function __construct($case,$owner){$this->case=$case;$this->owner=$owner;$this->handle=new stdClass();}
 public function allocate(){++$this->calls['allocate'];if('allocate-throw'===$this->case)throw new RuntimeException('private-secret');return 'allocate-false'===$this->case?false:$this->handle;}
 public function owns($h){++$this->calls['owns'];if('owns-throw'===$this->case || ('cleanup-owns-throw'===$this->case && $this->calls['owns']>1))throw new RuntimeException('private-secret');if('owns-one'===$this->case)return 1;return $h===$this->handle && 'alias'!==$this->case && !('lost-identity'===$this->case && $this->calls['owns']>1);}
 public function connect($h){++$this->calls['connect'];observation_check($h===$this->handle);
  if('reentry'===$this->case)$this->nested=$this->owner->run($this);
  if('false'===$this->case)return false;if('true'===$this->case)return true;
  if('wrong-class'===$this->case)throw new RuntimeException('private-secret',1049);
  throw new mysqli_sql_exception('private-secret',in_array($this->case,array('wrong-code','double-failure'),true)?2002:1049);
 }
 public function close($h){++$this->calls['close'];observation_check($h===$this->handle);if(in_array($this->case,array('close-throw','double-failure'),true))throw new RuntimeException('private-secret');if('close-one'===$this->case)return 1;return 'close-false'!==$this->case;}
 public function errno($h){++$this->calls['errno'];return 1049;}
};
$row=$owner->run($io);$again=$owner->run($io);
observation_check($row===$again);observation_check(1===$io->calls['allocate']);observation_check(0===$io->calls['errno']);
$allocated=!in_array($case,array('allocate-false','allocate-throw','alias','owns-one','owns-throw'),true);
observation_check(($allocated?1:0)===$io->calls['connect']);
observation_check(($allocated && !in_array($case,array('lost-identity','cleanup-owns-throw'),true)?1:0)===$io->calls['close']);
$expected=in_array($case,array('expected','reentry'),true);
observation_check($expected===$row['observed']);
$primary=in_array($case,array('wrong-class','wrong-code','false','true','double-failure'),true)?'connect':('allocate-throw'===$case?'allocation-throw':('owns-throw'===$case?'ownership-throw':($allocated?'expected-connect':'ownership')));
observation_check($primary===$row['primary']);
$cleanup=!$allocated?'unconfirmed':(in_array($case,array('close-throw','double-failure','cleanup-owns-throw'),true)?'throw':(in_array($case,array('close-false','close-one'),true)?'release':('lost-identity'===$case?'ownership':'acknowledged')));
observation_check($cleanup===$row['cleanup']);
observation_check(('expected-connect'===$primary?1049:null)===$row['code']);
observation_check(false===strpos(serialize($row),'private-secret'));observation_check(count($row)===4);
if('reentry'===$case)observation_check(array('observed'=>false,'code'=>null,'primary'=>'unconfirmed','cleanup'=>'unconfirmed')===$io->nested);
echo 'checks:'.$checks."\n";
