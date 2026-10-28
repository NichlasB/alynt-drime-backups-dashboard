<?php
use PHPUnit\Framework\TestCase;

/** Discovery must never register isolated driver doubles in the parent process. */
final class SourcePolicyConnectedRawDiscoveryTest extends TestCase {
 /** @dataProvider parent_profiles */
 public function test_discovery_include_preserves_symbols_constants_globals_and_output($profile) {
  // PHP -n and optional userland canaries only: no native module or database access.
  $code= <<<'PHP'
if($argv[2]==='existing-symbols'){
 class mysqli {}
 function mysqli_get_client_stats(){++$GLOBALS['discovery_canary_calls'];return array('canary');}
 function adcr_image(){++$GLOBALS['discovery_canary_calls'];return 'canary';}
}
define('ABSPATH','/isolated-discovery/');
$GLOBALS['discovery_canary_calls']=0;
$functions=get_defined_functions();$classes=get_declared_classes();
// Trusted self-snapshot only, never unserialized: NAN is unequal to itself.
$constants=serialize(get_defined_constants());$included=get_included_files();
$keys=array('mode','run','source','init_calls','connect_calls','close_calls','stats_calls','image_calls','connected','closed','options','allocated','exit');
$globals=array_intersect_key($GLOBALS,array_flip($keys));
ob_start();require $argv[1];$output=ob_get_clean();
if($functions!==get_defined_functions()||$classes!==get_declared_classes()){fwrite(STDERR,"Discovery symbol leak\n");exit(1);}
if($constants!==serialize(get_defined_constants())){fwrite(STDERR,"Discovery constant side effect\n");exit(1);}
if($globals!==array_intersect_key($GLOBALS,array_flip($keys))){fwrite(STDERR,"Discovery global side effect\n");exit(1);}
if($output!==''||$GLOBALS['discovery_canary_calls']!==0){fwrite(STDERR,"Discovery output/canary side effect\n");exit(1);}
$included[]=realpath($argv[1]);
if($included!==get_included_files()){fwrite(STDERR,"Discovery loaded an executable dependency\n");exit(1);}
echo "DISCOVERY_CLEAN\n";
PHP;
  $p=proc_open(array(PHP_BINARY,'-n','-r',$code,__DIR__.'/support/source-policy-connected-raw-worker.php',$profile),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($p),$err);$this->assertSame('',$err);$this->assertSame("DISCOVERY_CLEAN\n",$out);
 }
 public static function parent_profiles(){return array(array('clean-parent'),array('existing-symbols'));}
}
