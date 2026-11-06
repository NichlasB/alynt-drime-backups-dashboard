<?php
/**
 * Default-off genuine-session proof parent, not an application entry.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// This standalone parent is not loaded by the application bootstrap.
require_once __DIR__ . '/source-policy-minimum-request-contract.php';
if ( defined( 'ABSPATH' ) || ! adbg_entry( getenv() ) || ! adbg_identity( getenv() ) || PHP_SAPI !== 'cli' || '7.4.33' !== PHP_VERSION || 'Linux' !== PHP_OS_FAMILY || 'x86_64' !== php_uname( 'm' ) || PHP_ZTS || 8 !== PHP_INT_SIZE || 1 !== getmypid()
	|| 3 !== count( $argv ) || '--owned-minimum-qualification' !== $argv[1] || ! in_array( $argv[2], array( 'audit', 'proof' ), true ) || ! extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' ) ) {
	fwrite( STDERR, "Requires exact owned genuine request job\n" );
	exit( 1 );
}
define( 'ADB_M_REQUEST_PROOF', true );
require __DIR__ . '/source-policy-minimum-request-bootstrap.php';
require __DIR__ . '/source-policy-minimum-request-fixtures.php';
require __DIR__ . '/source-policy-minimum-request-process.php';
$ambient = null;
$failed  = false;
$groups  = 0;
$stage   = 'audit';
$index   = 0;
$failure = null;
// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- No raw diagnostics or private fixture material in output.
set_error_handler(
	static function () {
		throw new RuntimeException( 'Request proof diagnostic' );
	}
);
try {
	list( $rows, $modules ) = adbg_load();
	$image                  = getenv( 'MINIMUM_IMAGE' );
	$run                    = substr( hash( 'sha256', getenv( 'GITHUB_RUN_ID' ) . ':' . getenv( 'GITHUB_RUN_ATTEMPT' ) ), 0, 32 );
	if ( 'audit' === $argv[2] ) {
		echo 'ADBMGA1|' . $run . '|' . $image . '|' . $modules . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed private transcript.
		fflush( STDOUT );
		adbg_check( fgets( STDIN, 160 ) === 'ACK:' . $run . ':' . $image . "\n", 'held audit acknowledgement' );
		return;
	}
	$started = hrtime( true );
	$stage   = 'ambient';
	$ambient = adbg_ambient();
	$stage   = 'setup';
	adbg_seed();
	$non_target = adbg_non_target();
	foreach ( adbg_cases() as $index => $scenario ) {
		adbg_reset_case( $scenario );
		adbg_check( adbg_non_target() === $non_target, 'non-target preserved before auth' );
		$stage                   = 'entry';
		$counts                  = adbg_child( $index, $started );
		$GLOBALS['adbg_checks'] += $counts[0];
		$stage                   = 'verify';
		adbg_preflight();
		adbg_check( adbg_non_target() === $non_target, 'non-target preserved after request' );
		$expected = array( 9 => array( 'wpvivid' => 'external_optional' ) );
		if ( 'save' === $scenario ) {
			$expected[7] = array( 'wpvivid' => 'external_optional' );
		}
		$policy = $ambient->get_row( $ambient->prepare( 'SELECT option_value,autoload FROM ' . adbg_prefix() . 'options WHERE BINARY option_name=BINARY %s', 'alynt_drime_backups_dashboard_source_policies' ), ARRAY_A );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Exact source-owned primitive map, never an arbitrary object.
		adbg_check( '' === $ambient->last_error && is_array( $policy ) && serialize( $expected ) === $policy['option_value'] && 'no' === $policy['autoload'], 'physical policy bytes/autoload match exact result' );
		$stage = 'setup';
	}
	adbg_check( hrtime( true ) - $started < 20000000000 && adbm_source_rows() === $rows && adbm_modules() === $modules, 'whole-body deadline and unchanged cohort' );
	$groups = 9;
} catch ( Throwable $error ) {
	$failed  = true;
	$failure = isset( $GLOBALS['adbg_failure_wire'] ) && adbg_failure_valid( $GLOBALS['adbg_failure_wire'] ) ? $GLOBALS['adbg_failure_wire'] : adbg_failure( 'parent', $index, $stage, $error );
} finally {
	$stage = 'cleanup';
	if ( $ambient instanceof wpdb ) {
		try {
			remove_filter( 'query', array( $ambient, 'remove_placeholder_escape' ), 0 );
			$failed = true !== $ambient->close() || $failed;
			$ambient->flush();
		} catch ( Throwable $error ) {
			$failed  = true;
			$failure = $failure ?? adbg_failure( 'parent', $index, $stage, $error );
		}
	}
	unset( $GLOBALS['wpdb'], $ambient );
	restore_error_handler();
}
if ( $failed ) {
	fwrite( STDERR, $failure ?? adbg_failure( 'parent', $index, 'cleanup', new RuntimeException() ) );
	exit( 1 );
}
	echo 'ADBMGR1|' . $run . '|' . $image . '|' . getenv( 'MINIMUM_SERVER_CID' ) . '|1|' . $modules . '|' . $GLOBALS['adbg_checks'] . '|' . $groups . '|0|closed|trusted-distribution-legacy-v1|false|not-established|false|false' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed private count/identity transcript, not HTML.
	fflush( STDOUT );
if ( fgets( STDIN, 160 ) !== 'ACK:' . $run . ':' . $image . "\n" ) {
	exit( 1 );
}
