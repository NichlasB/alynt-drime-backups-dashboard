<?php
/** Source-backed SQL-shape/effect model only; no WordPress, driver or SQL executor. */
use PHPUnit\Framework\Assert as A;
if (isset($argv[0]) && realpath($argv[0]) === __FILE__) {
if (PHP_SAPI !== 'cli' || count($argv) !== 2 || extension_loaded('mysqli') || extension_loaded('pdo_mysql')) exit(1);
require dirname(__DIR__, 2) . '/vendor/autoload.php';
function adbg_check($condition, $label) { A::assertTrue($condition, $label); }
try {
	require $argv[1];
	$p = 'adbm_123456abcdef_'; $schema = "SHOW FULL COLUMNS FROM `{$p}usermeta`";
	$GLOBALS['wpdb'] = (object) array('prefix'=>$p);
	$GLOBALS['adbg_auth_phase'] = 'setup'; $GLOBALS['adbg_ledger'] = array('setup'=>0,'auth'=>0);
	$delivered = array();
	$deliver = static function($sql) use (&$delivered) {
		$unchanged = adbg_observe_query($sql); A::assertSame($sql,$unchanged);
		$delivered[] = $unchanged; // In-memory delivery witness, NOT database dispatch.
	};
	// Derived from pinned WP6.0 metadata/CRUD path, not a genuine core execution.
	$trace = array(
		"SELECT user_id, meta_key, meta_value FROM {$p}usermeta WHERE user_id IN (1) ORDER BY umeta_id ASC",
		"SELECT umeta_id FROM {$p}usermeta WHERE meta_key = 'session_tokens' AND user_id = 1",
		$schema,
		"INSERT INTO `{$p}usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('1', 'session_tokens', 'a:0:{}')",
	);
	A::assertSame('session-schema-read',adbg_sql_class($schema,$p));
	foreach ($trace as $sql) $deliver($sql);
	A::assertSame($trace,$delivered); A::assertSame(array('setup'=>4,'auth'=>0),$GLOBALS['adbg_ledger']);
	foreach (array('options','users','alynt_drime_dashboard_sites','usermeta_extra') as $table) {
		A::assertNull(adbg_sql_class("SHOW FULL COLUMNS FROM `{$p}{$table}`",$p));
	}
	$bad = array(str_replace($p,'foreign_',$schema),str_replace('`','',$schema),$schema.';',$schema.' -- suffix',$schema.' WHERE 1=1',str_replace('`'.$p,'`adb_minimum`.`'.$p,$schema),str_replace('FULL ','',$schema),$schema."\n",array($schema),new stdClass());
	foreach ($bad as $sql) {
		A::assertNull(adbg_sql_class($sql,$p)); $before = count($delivered); $failed = false;
		try { $deliver($sql); } catch (PHPUnit\Framework\ExpectationFailedException $error) { $failed = true; }
		A::assertTrue($failed); A::assertSame($before,count($delivered),'Unknown form never delivered.');
	}
	foreach (array('auth','outside','overflow') as $negative) {
		$GLOBALS['adbg_auth_phase'] = $negative === 'overflow' ? 'setup' : $negative;
		$GLOBALS['adbg_ledger'] = array('setup'=>$negative === 'overflow'?256:0,'auth'=>0);
		$before = count($delivered); $failed = false;
		try { $deliver($schema); } catch (PHPUnit\Framework\ExpectationFailedException $error) { $failed = true; }
		A::assertTrue($failed,$negative.' refused'); A::assertSame($before,count($delivered));
	}
	$GLOBALS['adbg_auth_phase'] = 'auth'; $GLOBALS['adbg_ledger'] = array('setup'=>4,'auth'=>0);
	$deliver("SELECT * FROM {$p}users WHERE ID = '1' LIMIT 1");
	A::assertSame(array('setup'=>4,'auth'=>1),$GLOBALS['adbg_ledger']);
	A::assertFalse(function_exists('mysqli_init')); A::assertFalse(class_exists('wpdb',false));
	echo 'checks:',A::getCount(),"\n";
} catch (Throwable $error) { fwrite(STDERR,get_class($error).' '.$error->getMessage()."\n"); exit(1); }
}
