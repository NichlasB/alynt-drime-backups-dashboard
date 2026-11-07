<?php
/**
 * Shared genuine-request case body; entries must admit before requiring it.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Same read-only mysqlnd counters as the admitted M/W/L entries; no application query path.
if ( ! defined( 'ADB_M_REQUEST_PROOF' ) ) {
	exit( 1 ); }
$ambient  = null;
$failed   = false;
$scenario = adbg_cases()[ (int) $argv[2] ];
$stage    = 'audit';
$failure  = null;
// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Raw diagnostics never cross the private pipe.
set_error_handler(
	static function () {
		throw new RuntimeException( 'Request child diagnostic' );
	}
);
try {
	list( $rows, $modules ) = adbg_load();
	if ( defined( 'ADB_WL_REQUEST_PROOF' ) && 'audit' === getenv( 'WL_REQUEST_MODE' ) ) {
		echo adbwl_audit_wire( $rows, $modules ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed private DATA only.
		return;
	}
	$stage   = 'ambient';
	$ambient = adbg_ambient();
	if ( defined( 'ADB_WL_REQUEST_PROOF' ) ) {
		$case_state = adbwl_prepare_case( $scenario );
	}
	$stage   = 'auth';
	$_SERVER = array( 'REQUEST_METHOD' => 'POST' );
	$_COOKIE = array();
	adbg_auth_hooks();
	$GLOBALS['adbg_ledger'] = array(
		'setup' => 0,
		'auth'  => 0,
	);
	add_filter( 'query', 'adbg_observe_query', PHP_INT_MIN, 1 );
	list( , $request ) = adbg_request( $scenario );
	adbg_auth_hooks();
	adbg_check( true === remove_filter( 'query', 'adbg_observe_query', PHP_INT_MIN ) && false === has_filter( 'query', 'adbg_observe_query' ), 'remove only owned auth observer before guarded entry' );
	adbg_check( false === wp_cache_get( 'alynt_drime_backups_dashboard_source_policies', 'options' ), 'auth did not cache the non-autoloaded policy value' );
	$all = wp_cache_get( 'alloptions', 'options' );
	adbg_check( ! is_array( $all ) || ! array_key_exists( 'alynt_drime_backups_dashboard_source_policies', $all ), 'autoload auth cache excludes policy' );
	$class          = Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry::class;
	$before         = mysqli_get_client_stats();
	$before_queries = $ambient->num_queries;
	$stage          = 'entry';
	if ( in_array( $scenario, array( 'view', 'save', 'noop', 'stale' ), true ) ) {
		$view = $class::request_view( 7, 'wpvivid' );
		if ( defined( 'ADB_WL_REQUEST_PROOF' ) ) {
			$entry_stats = mysqli_get_client_stats();
			adbwl_entry_capture( $view, (int) $argv[2], $ambient->num_queries - $before_queries, (int) $entry_stats['connect_success'] - (int) $before['connect_success'], (int) $entry_stats['com_query'] - (int) $before['com_query'], (int) $entry_stats['connect_failure'] - (int) $before['connect_failure'] );
		}
		adbg_check( is_array( $view ) && array( 'mode', 'revision' ) === array_keys( $view ) && 'required' === $view['mode'], 'genuine same-observation view available' );
		$request['source_policy_revision'] = 'stale' === $scenario ? str_repeat( 'f', 64 ) : $view['revision'];
		adbg_check( 'stale' !== $scenario || $view['revision'] !== $request['source_policy_revision'], 'actual stale revision' );
		if ( 'noop' === $scenario ) {
			$request['source_mode'] = 'required';
		}
	}
	$result    = 'view' === $scenario ? $view : $class::request_submit( $request );
	$projected = is_array( $result ) ? 'view' : ( $result instanceof WP_Error ? $result->get_error_code() : $result );
	adbg_check( adbg_expected( $scenario ) === $projected, 'exact guarded result including positive saved availability' );
	$stage    = 'verify';
	$after    = mysqli_get_client_stats();
	$records  = $ambient->num_queries - $before_queries;
	$connects = (int) $after['connect_success'] - (int) $before['connect_success'];
	$commands = (int) $after['com_query'] - (int) $before['com_query'];
	if ( ! in_array( $scenario, array( 'view', 'save', 'noop', 'stale' ), true ) ) {
		$record_expected = 0 === strpos( $scenario, 'record-' ) ? 1 : 0;
		adbg_check( 0 === $connects && $record_expected === $records && $record_expected === $commands, 'denials have no owned connection or policy commands' );
	} else {
		$expected_operations = 'view' === $scenario ? 1 : 2;
		adbg_check( $expected_operations === $connects && $expected_operations === $records && $commands > $records, 'positive path includes each independent native operation without reconnect' );
	}
	adbg_check( 0 === ( new mysqli_driver() )->report_mode && 0 === (int) $after['connect_failure'], 'no reconnect or reporting change' );
	if ( defined( 'ADB_WL_REQUEST_PROOF' ) ) {
		adbwl_verify_case( $scenario, $case_state );
	}
	adbg_auth_hooks();
	adbg_check( adbm_source_rows() === $rows && adbm_modules() === $modules, 'fixed loaded cohort through guarded entry' );
} catch ( Throwable $error ) {
	$failed = true;
	if ( defined( 'ADB_WL_REQUEST_PROOF' ) ) {
		adbwl_auth_capture( $stage, (int) $argv[2], $error );
	}
	$failure = adbg_failure( 'child', (int) $argv[2], $stage, $error );
} finally {
	$stage = 'cleanup';
	if ( $ambient instanceof wpdb ) {
		try {
			remove_filter( 'query', 'adbg_observe_query', PHP_INT_MIN );
			remove_filter( 'query', array( $ambient, 'remove_placeholder_escape' ), 0 );
			$failed = true !== $ambient->close() || $failed;
			$ambient->flush();
		} catch ( Throwable $error ) {
			$failed  = true;
			$failure = $failure ?? adbg_failure( 'child', (int) $argv[2], $stage, $error );
		}
	}
	unset( $GLOBALS['wpdb'], $GLOBALS['current_user'], $GLOBALS['wp_roles'], $ambient, $request, $result );
	$_COOKIE = array();
	restore_error_handler();
}
if ( $failed ) {
	$wire = $failure ?? adbg_failure( 'child', (int) $argv[2], 'cleanup', new RuntimeException() );
	fwrite( STDERR, defined( 'ADB_WL_REQUEST_PROOF' ) ? adbwl_failure_wire( $wire ) : $wire );
	exit( 1 );
}
	echo defined( 'ADB_WL_REQUEST_PROOF' ) ? adbwl_case_wire( (int) $argv[2], $records, $connects, $commands ) : 'ADBMGC1|' . $argv[2] . '|' . $GLOBALS['adbg_checks'] . '|' . $GLOBALS['adbg_ledger']['setup'] . '|' . $GLOBALS['adbg_ledger']['auth'] . '|' . $records . '|' . $connects . '|' . $commands . "|closed\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed private ASCII counts only.
	fflush( STDOUT );
$ack = defined( 'ADB_WL_REQUEST_PROOF' ) ? 'ACK:' . getenv( 'WL_REQUEST_RUN' ) . ':' . getenv( 'WL_REQUEST_IMAGE' ) . ':' . $argv[2] . "\n" : "ACK\n";
if ( $ack !== fgets( STDIN, defined( 'ADB_WL_REQUEST_PROOF' ) ? 160 : 8 ) ) { // phpcs:ignore WordPress.PHP.YodaConditions -- Exact private acknowledgement identity, not scalar normalization.
	exit( 1 );
}
