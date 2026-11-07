<?php
/** In-memory verifier/query-effect doubles only; no SQL, WordPress or native sessions. */
use PHPUnit\Framework\Assert as A;
if (isset($argv[0]) && realpath($argv[0]) === __FILE__) {
if (PHP_SAPI !== 'cli' || count($argv) !== 3 || extension_loaded('mysqli')) exit(1);
require dirname(__DIR__, 2) . '/vendor/autoload.php';
define('ADB_M_REQUEST_PROOF', true);
define('ARRAY_A', 'ARRAY_A');
function adbg_check($condition, $label) { A::assertTrue($condition, $label); }
class WP_Hook { public $callbacks = array(); }
$scenario = $argv[1];
if(strpos($scenario,'wl-')===0){define('ADB_WL_REQUEST_PROOF',true);$GLOBALS['wp_version']='7.1.4';}
$GLOBALS['wpdb'] = new class {
	public $prefix = 'adbm_123456abcdef_';
	public $last_error = '';
	public $effects = array();
	public $reads = 0;
	function prepare($sql, $value) { return str_replace('%s', "'" . $value . "'", $sql); }
	function query($sql) {
		A::assertSame("DELETE FROM {$this->prefix}options WHERE BINARY option_name=BINARY 'alynt_drime_backups_dashboard_source_policies'", $sql);
		$this->effects[] = 'policy-delete'; return 1;
	}
	function insert($table, $row) {
		A::assertSame($this->prefix . 'options', $table);
		A::assertSame(array('option_name'=>'alynt_drime_backups_dashboard_source_policies','option_value'=>serialize(array(9=>array('wpvivid'=>'external_optional'))),'autoload'=>'no'), $row);
		$this->effects[] = 'policy-insert'; return 1;
	}
	function get_var($sql) {
		++$this->reads;
		global $scenario;
		$this->last_error = '';
		if (strpos($sql, 'OCTET_LENGTH(meta_value)') !== false) {
			if ($scenario === 'failed-large-read') { $this->last_error = 'modeled'; return null; }
			return $scenario === 'large-value' ? '1' : '0';
		}
		A::assertMatchesRegularExpression('/^SELECT COUNT\(\*\) FROM adbm_123456abcdef_(users|usermeta|options|alynt_drime_dashboard_sites)$/', $sql);
		if ($scenario === 'failed-count') { $this->last_error = 'modeled'; return null; }
		return $scenario === 'cardinality' ? '13' : '1';
	}
	function get_results($sql, $format) {
		global $scenario;
		A::assertSame(ARRAY_A, $format); $this->last_error = '';
		if (strpos($sql, 'GROUP BY') !== false) return $scenario === 'per-user' ? array(array('user_id'=>'1')) : array();
		if (strpos($sql, "meta_key='session_tokens'") !== false) {
			if ($scenario === 'failed-session-read') { $this->last_error = 'modeled'; return array(); }
			$session = array(str_repeat('a',64)=>array('expiration'=>100,'login'=>90));
			if ($scenario === 'sessions') $session = array_fill_keys(array(str_repeat('a',64),str_repeat('b',64),str_repeat('c',64)),array('expiration'=>100,'login'=>90));
			if ($scenario === 'session-shape') $session[str_repeat('a',64)]['ip'] = 'not-allowed';
			return array(array('user_id'=>'1','meta_value'=>serialize($session)));
		}
		A::assertSame("SELECT option_name,option_value,autoload FROM {$this->prefix}options WHERE BINARY option_name<>BINARY 'alynt_drime_backups_dashboard_source_policies' ORDER BY option_name", $sql);
		return array(array('option_name'=>'owned_non_target','option_value'=>'preserved','autoload'=>'no'),array('option_name'=>$this->prefix.'user_roles','option_value'=>'roles','autoload'=>'yes'));
	}
	function get_row($sql, $format) {
		global $scenario;
		A::assertSame(ARRAY_A, $format); $this->last_error = '';
		A::assertSame("SELECT OCTET_LENGTH(option_value) AS bytes,autoload FROM {$this->prefix}options WHERE BINARY option_name=BINARY 'alynt_drime_backups_dashboard_source_policies'", $sql);
		if ($scenario === 'failed-policy-read') { $this->last_error = 'modeled'; return null; }
		return array('bytes'=>$scenario==='policy-large'?'16385':($scenario==='policy-malformed'?'invalid':'64'),'autoload'=>$scenario==='autoload'?'yes':($scenario==='wl-off'?'off':'no'));
	}
};
try {
	require $argv[2];
	$failed = false;
	try { adbg_preflight(in_array($scenario,array('wl-off','wl-off-wrong','legacy-off'),true)?'off':($scenario==='wl-on'?'on':'no')); } catch (PHPUnit\Framework\ExpectationFailedException $error) { $failed = true; }
	A::assertSame(!in_array($scenario,array('positive','wl-off','wl-no'),true), $failed);
	if(in_array($scenario,array('legacy-off','wl-on'),true))A::assertSame(0,$GLOBALS['wpdb']->reads,'Invalid phase refused before fixture reads');
	if (!$failed) {
		$before = adbg_non_target(); adbg_seed_policy(); A::assertSame($before,adbg_non_target());
		A::assertSame(array('policy-delete','policy-insert'),$GLOBALS['wpdb']->effects);
	}
	require dirname(__DIR__, 2) . '/scripts/ci/source-policy-minimum-request-observer.php';
	$GLOBALS['adbg_auth_phase'] = 'auth'; $GLOBALS['adbg_ledger'] = array('auth'=>0,'setup'=>0);
	$sql = "SELECT * FROM {$GLOBALS['wpdb']->prefix}users WHERE ID = '1' LIMIT 1";
	A::assertSame($sql,adbg_observe_query($sql)); A::assertSame(1,$GLOBALS['adbg_ledger']['auth']);
	$hook = new WP_Hook(); $hook->callbacks = array(0=>array('owned'=>array('function'=>array($GLOBALS['wpdb'],'remove_placeholder_escape'),'accepted_args'=>1)),PHP_INT_MIN=>array('observer'=>array('function'=>'adbg_observe_query','accepted_args'=>1)));
	$GLOBALS['wp_filter'] = array('query'=>$hook); adbg_auth_hooks();
	foreach (array('unexpected','write','limit','hooks','query-hook') as $negative) {
		$failed = false; $GLOBALS['wp_filter'] = array(); $GLOBALS['adbg_auth_phase'] = 'auth'; $GLOBALS['adbg_ledger']['auth'] = 0;
		try {
			if ($negative === 'unexpected') adbg_observe_query('SELECT secret FROM elsewhere');
			if ($negative === 'write') adbg_observe_query("DELETE FROM {$GLOBALS['wpdb']->prefix}usermeta WHERE umeta_id IN( 4 )");
			if ($negative === 'limit') { $GLOBALS['adbg_ledger']['auth'] = 256; adbg_observe_query($sql); }
			if ($negative === 'hooks') { $hook = new WP_Hook(); $hook->callbacks = array('caller'); $GLOBALS['wp_filter']['nonce_life'] = $hook; adbg_auth_hooks(); }
			if ($negative === 'query-hook') { $hook = new WP_Hook(); $hook->callbacks = array(0=>array('foreign'=>array('function'=>'untrusted-query-callback','accepted_args'=>1))); $GLOBALS['wp_filter']['query'] = $hook; adbg_auth_hooks(); }
		} catch (PHPUnit\Framework\ExpectationFailedException $error) { $failed = true; }
		A::assertTrue($failed,'observer guard '.$negative);
	}
	echo 'checks:', A::getCount(), "\n";
} catch (Throwable $error) { fwrite(STDERR,get_class($error).' '.$error->getMessage()."\n"); exit(1); }
}
