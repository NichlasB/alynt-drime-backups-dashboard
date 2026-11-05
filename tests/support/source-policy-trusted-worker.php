<?php
/** Extension-free API tests. No WordPress bootstrap, configuration file or native IO. */
if(defined('ABSPATH'))return;
if(PHP_SAPI!=='cli'||count($argv)!==2||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
define('ABSPATH',dirname(__DIR__,2).'/');define('ADB_POLICY_ISOLATED',true);define('WPINC','wp-includes');define('MULTISITE',false);define('WP_CONTENT_DIR',__DIR__);
define('DB_NAME','isolated');define('DB_USER','owned');define('DB_PASSWORD','owned-private-context-password');define('DB_HOST','127.0.0.1:10004');define('DB_CHARSET','utf8mb4');define('DB_COLLATE','utf8mb4_unicode_520_ci');
require ABSPATH.'vendor/autoload.php';require __DIR__.'/wordpress-shims.php';
foreach(array('isolated-wordpress','bootstrap-driver','bootstrap-metadata','isolated-wpdb')as$file)require __DIR__.'/source-policy-'.$file.'.php';
foreach(array('source-policy','source-policy-save-contract','source-policy-native-profile','source-policy-query-context','source-policy-no-reconnect-connection','source-policy-stored-map','source-policy-native-adapter','source-policy-guarded-entry','source-policy-lazy-provider')as$file)require ABSPATH.'includes/class-'.$file.'.php';
require __DIR__.'/source-policy-isolated-fixtures.php';require __DIR__.'/source-policy-trusted-fixtures.php';require __DIR__.'/source-policy-trusted-cases.php';
use PHPUnit\Framework\Assert as A;
try {
	$case=$argv[1];list($entry,$provider,$context,$adapter,$receiver,$store,$ambient,$sites)=trusted_fixture();$request=trusted_request($store);$originalAmbient=$ambient->dbh;
	$prior=static function(){return true;};set_error_handler($prior);
	A::assertSame('fresh',boot_get($context,'state'));A::assertNull(boot_get($context,'frame'));A::assertSame(0,$GLOBALS['boot_parse_calls']);
	trusted_case($case,$entry,$provider,$context,$adapter,$receiver,$store,$sites,$request);
	$active=set_error_handler($prior);restore_error_handler();A::assertSame($prior,$active,'Restore the exact preexisting diagnostic handler.');restore_error_handler();
	A::assertSame(array('allocate'=>0,'connect'=>0,'commands'=>0),$GLOBALS['boot_counts'],'Every test is extension-free, not a native proof.');
	A::assertSame($originalAmbient,$ambient->dbh);A::assertSame(0,$ambient->closes);A::assertSame(0,$receiver->reconnects);
	echo 'checks:',A::getCount(),"\n";
}catch(Throwable $error){fwrite(STDERR,'Trusted fixture '.$argv[1].' '.get_class($error).' '.$error->getMessage()."\n");exit(1);}
