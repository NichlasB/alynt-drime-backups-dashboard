<?php
/** Pure declaration DATA only: no core/API doubles, credentials, handles or storage. */
if(defined('ABSPATH'))return;
if(PHP_SAPI!=='cli'||count($argv)!==2||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
define('ABSPATH',dirname(__DIR__,2).'/');define('WPINC','wp-includes');
require ABSPATH.'vendor/autoload.php';require ABSPATH.'includes/class-source-policy-trusted-source.php';
use PHPUnit\Framework\Assert as A;
try{
	$case=$argv[1];$class=Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Source::class;
	$positive='admission-m'===$case||('admission-shipped'===$case&&'7.4.33'===PHP_VERSION);
	foreach(array('6.0','7.1.3','7.1.4','unknown',null,6,false,array('6.0'),new stdClass())as$core){
		$actual=$class::request_policy($core);
		if($positive&&'6.0'===$core){A::assertIsArray($actual);A::assertSame($class::policy($core),$actual);A::assertSame('7.4.33',$actual['php']);A::assertNull($actual['driver']);A::assertCount(10,$actual['pins']);}
		else A::assertFalse($actual,'Only the exact M declaration may be eligible.');
	}
	A::assertFalse(class_exists('wpdb',false));A::assertFalse(class_exists('mysqli',false));A::assertFalse(function_exists('mysqli_init'));A::assertFalse(function_exists('current_user_can'));
	echo 'checks:',A::getCount(),"\n";
}catch(Throwable $error){fwrite(STDERR,'Admission DATA '.get_class($error).' '.$error->getMessage()."\n");exit(1);}
