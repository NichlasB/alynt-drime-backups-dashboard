<?php
/** Extension-free two-phase join; all driver/auth/storage/runtime APIs are isolated doubles. */
if(defined('ABSPATH'))return;
if(PHP_SAPI!=='cli'||count($argv)!==2||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(1);
define('ABSPATH',dirname(__DIR__,2).'/');define('ADB_POLICY_ISOLATED',true);define('WPINC','wp-includes');define('MULTISITE',false);define('WP_CONTENT_DIR',__DIR__);
$parts=explode(':',$argv[1],2);$profile=count($parts)===2?$parts[0]:'m';$case=count($parts)===2?$parts[1]:$parts[0];
if(!in_array($profile,array('m','w','w714','l'),true))exit(1);
define('DB_NAME','isolated');define('DB_USER','owned');define('DB_PASSWORD','owned-private-password');define('DB_HOST',$profile==='l'?'localhost:/tmp/owned.sock':'127.0.0.1:10004');define('DB_CHARSET',in_array($profile,array('w','w714'),true)?'utf8mb4':'utf8');define('DB_COLLATE','');
require ABSPATH.'vendor/autoload.php';require __DIR__.'/wordpress-shims.php';
foreach(array('isolated-wordpress','bootstrap-driver','bootstrap-metadata','isolated-wpdb')as$file)require __DIR__.'/source-policy-'.$file.'.php';
foreach(array('source-policy','source-policy-save-contract','source-policy-native-profile','source-policy-query-context','source-policy-no-reconnect-connection','source-policy-stored-map','source-policy-native-adapter','source-policy-guarded-entry','source-policy-lazy-provider')as$file)require ABSPATH.'includes/class-'.$file.'.php';
require __DIR__.'/source-policy-isolated-fixtures.php';require __DIR__.'/source-policy-trusted-fixtures.php';require __DIR__.'/source-policy-production-fixtures.php';require __DIR__.'/source-policy-production-cases.php';require __DIR__.'/source-policy-production-data.php';
require __DIR__.'/source-policy-nonlegacy-data.php';
use PHPUnit\Framework\Assert as A;
try {
	list($entry,$provider,$context,$adapter,$receiver,$store,$ambient,$sites,$query)=production_fixture($profile);$request=trusted_request($store);$handle=$ambient->dbh;
	A::assertFalse($receiver->ready);A::assertNull($receiver->profile());A::assertNull($receiver->dbh);A::assertFalse($context->valid($receiver));A::assertFalse($context->activate($receiver,$query));
	A::assertFalse($receiver->query('SELECT never-dispatched'));A::assertSame(0,$store->reads);
	$prior=static function(){return true;};set_error_handler($prior);
	if('data'===$case){if('m'===$profile)production_data($context,$receiver);else production_nonlegacy_data($context,$receiver);A::assertFalse($context->finish());}
	elseif((('l'===$profile&&'Linux'!==PHP_OS_FAMILY)||('w714'===$profile&&'Windows'!==PHP_OS_FAMILY))&&'runtime-observation'!==$case){trusted_error($entry->submit($request),'source_policy_unavailable');A::assertSame(0,$GLOBALS['boot_counts']['allocate']);A::assertSame(0,$store->reads);}
	else production_case($case,$entry,$provider,$context,$adapter,$receiver,$store,$sites,$query,$request);
	A::assertTrue(null===boot_get($receiver,'bootstrap_frame'),'Terminal owner must drop the private credential descriptor even before initialization.');A::assertNull(boot_get($receiver,'bootstrap_binding'));A::assertSame('',boot_get($receiver,'dbpassword'));
	$active=set_error_handler($prior);restore_error_handler();A::assertSame($prior,$active);restore_error_handler();
	A::assertSame($handle,$ambient->dbh);A::assertSame(0,$ambient->closes);A::assertSame(0,$receiver->reconnects);A::assertSame(0,$GLOBALS['boot_counts']['unexpected_connect']);
	A::assertSame(0,count(array_intersect(array('mysqli','pdo_mysql'),get_loaded_extensions())));
	echo 'checks:',A::getCount(),"\n";
}catch(Throwable $error){fwrite(STDERR,'Production join '.$argv[1].' '.get_class($error).' '.$error->getMessage()."\n".$error->getTraceAsString()."\n");exit(1);}
