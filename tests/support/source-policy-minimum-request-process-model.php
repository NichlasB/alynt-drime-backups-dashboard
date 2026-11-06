<?php
/** In-memory process/stdio API doubles; no process spawn, SQL or WordPress. */
use PHPUnit\Framework\Assert as A;
if (isset($argv[0]) && realpath($argv[0]) === __FILE__) {
if(PHP_SAPI!=='cli'||count($argv)!==3||extension_loaded('mysqli'))exit(1);
require dirname(__DIR__,2).'/vendor/autoload.php';
define('ADB_M_REQUEST_PROOF',true);$GLOBALS['model_case']=$argv[1];$GLOBALS['model_closed']=0;$GLOBALS['model_terminated']=0;$GLOBALS['model_running']=true;$GLOBALS['model_status']=0;
require dirname(__DIR__,2).'/scripts/ci/source-policy-minimum-request-contract.php';
function adbg_check($condition,$label){A::assertTrue($condition,$label);}
$GLOBALS['wpdb']=new class {function get_var($sql){A::assertSame('SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()',$sql);return$GLOBALS['model_case']==='sessions'?'1':'0';}};
function adbg_model_proc_open($args,$descriptors,&$pipes,$cwd){
	A::assertSame(PHP_BINARY,$args[0]);A::assertSame('-n',$args[1]);A::assertContains('--owned-minimum-request-child',$args);A::assertSame('0',end($args));A::assertStringEndsWith('source-policy-minimum-request-child.php',$args[count($args)-3]);
	$pipes=array(fopen('php://temp','w+'),fopen('php://temp','w+'),fopen('php://temp','w+'));
	$GLOBALS['model_stdin']=$pipes[0];
	$wire="ADBMGC1|0|42|4|2|1|2|17|closed\n";
	if($GLOBALS['model_case']==='wrong-index')$wire=str_replace('|0|42|','|1|42|',$wire);
	if($GLOBALS['model_case']==='noise')$wire.='noise';
	if($GLOBALS['model_case']==='oversize')$wire.=str_repeat('x',4096);
	fwrite($pipes[1],$wire);rewind($pipes[1]);if($GLOBALS['model_case']==='stderr'){fwrite($pipes[2],'private-refusal');rewind($pipes[2]);}
	if($GLOBALS['model_case']==='failure'){fwrite($pipes[2],"ADBMGRF1|child|0|auth|TypeError|".str_repeat('a',64)."\n");rewind($pipes[2]);}
	return fopen('php://temp','w+');
}
function adbg_model_proc_get_status($process){A::assertIsResource($process);$running=$GLOBALS['model_running']&&is_resource($GLOBALS['model_stdin'])&&$GLOBALS['model_case']!=='exit';return array('running'=>$running,'exitcode'=>$GLOBALS['model_case']==='exit'?1:0);}
function adbg_model_proc_terminate($process,$signal=15){A::assertIsResource($process);++$GLOBALS['model_terminated'];if($GLOBALS['model_case']!=='never-exit'&&($GLOBALS['model_case']!=='kill'||$signal===9))$GLOBALS['model_running']=false;return true;}
function adbg_model_proc_close($process){++$GLOBALS['model_closed'];fclose($process);return 0;}
try {
	require $argv[2];$failed=false;
	try{adbg_child(0,hrtime(true)-(in_array($argv[1],array('deadline','kill','never-exit'),true)?20000000001:0));}catch(PHPUnit\Framework\ExpectationFailedException $error){$failed=true;}
	A::assertSame($argv[1]!=='positive',$failed);A::assertSame($argv[1]==='never-exit'?0:1,$GLOBALS['model_closed']);if($argv[1]==='deadline')A::assertSame(1,$GLOBALS['model_terminated']);
	if($argv[1]==='kill')A::assertSame(2,$GLOBALS['model_terminated']);
	if($argv[1]==='failure')A::assertSame("ADBMGRF1|child|0|auth|TypeError|".str_repeat('a',64)."\n",$GLOBALS['adbg_failure_wire']);
	echo 'checks:',A::getCount(),"\n";
}catch(Throwable $error){fwrite(STDERR,get_class($error).' '.$error->getMessage()."\n");exit(1);}
}
