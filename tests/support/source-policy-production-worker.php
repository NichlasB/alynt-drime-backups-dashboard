<?php
/** Extension-free two-phase join; all driver/auth/storage/runtime APIs are isolated doubles. */
if(defined('ABSPATH'))return;
if(PHP_SAPI!=='cli'||count($argv)!==2||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
define('ABSPATH',dirname(__DIR__,2).'/');define('ADB_POLICY_ISOLATED',true);define('WPINC','wp-includes');define('MULTISITE',false);define('WP_CONTENT_DIR',__DIR__);
define('DB_NAME','isolated');define('DB_USER','owned');define('DB_PASSWORD','owned-private-password');define('DB_HOST','127.0.0.1:10004');define('DB_CHARSET','utf8');define('DB_COLLATE','');
require ABSPATH.'vendor/autoload.php';require __DIR__.'/wordpress-shims.php';
foreach(array('isolated-wordpress','bootstrap-driver','bootstrap-metadata','isolated-wpdb')as$file)require __DIR__.'/source-policy-'.$file.'.php';
foreach(array('source-policy','source-policy-save-contract','source-policy-native-profile','source-policy-query-context','source-policy-no-reconnect-connection','source-policy-stored-map','source-policy-native-adapter','source-policy-guarded-entry','source-policy-lazy-provider')as$file)require ABSPATH.'includes/class-'.$file.'.php';
require __DIR__.'/source-policy-isolated-fixtures.php';require __DIR__.'/source-policy-trusted-fixtures.php';require __DIR__.'/source-policy-production-fixtures.php';require __DIR__.'/source-policy-production-cases.php';require __DIR__.'/source-policy-production-data.php';
use PHPUnit\Framework\Assert as A;
try {
	$case=$argv[1];list($entry,$provider,$context,$adapter,$receiver,$store,$ambient,$sites,$query)=production_fixture();$request=trusted_request($store);$handle=$ambient->dbh;
	A::assertFalse($receiver->ready);A::assertNull($receiver->profile());A::assertNull($receiver->dbh);A::assertFalse($context->valid($receiver));A::assertFalse($context->activate($receiver,$query));
	A::assertFalse($receiver->query('SELECT never-dispatched'));A::assertSame(0,$store->reads);
	$prior=static function(){return true;};set_error_handler($prior);
	if('data'===$case){production_data($context,$receiver);A::assertFalse($context->finish());}else production_case($case,$entry,$provider,$context,$adapter,$receiver,$store,$sites,$query,$request);
	$active=set_error_handler($prior);restore_error_handler();A::assertSame($prior,$active);restore_error_handler();
	A::assertSame($handle,$ambient->dbh);A::assertSame(0,$ambient->closes);A::assertSame(0,$receiver->reconnects);A::assertSame(0,$GLOBALS['boot_counts']['unexpected_connect']);
	A::assertSame(0,count(array_intersect(array('mysqli','pdo_mysql'),get_loaded_extensions())));
	echo 'checks:',A::getCount(),"\n";
}catch(Throwable $error){fwrite(STDERR,'Production join '.$argv[1].' '.get_class($error).' '.$error->getMessage()."\n".$error->getTraceAsString()."\n");exit(1);}
