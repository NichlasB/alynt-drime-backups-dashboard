<?php
/**
 * ONE explicitly approved initialized-v2 owned CI child, never an installed entry.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride, WordPress.PHP.DevelopmentFunctions -- Private owned child state and fixed error boundary, never application wiring.
if ( 'cli' !== PHP_SAPI || array( __FILE__, '--owned-http-initializer' ) !== $argv || 'true' !== getenv( 'ADB_HTTP_OWNED_APPROVED' ) || 'true' !== getenv( 'ADB_HTTP_INITIALIZED_APPROVED' ) ) {
	exit( 1 );
}
require __DIR__ . '/source-policy-http-owned-worker.php'; // Exact runtime/before-ABSPATH gate.
require_once __DIR__ . '/source-policy-http-initialization.php';
require_once __DIR__ . '/source-policy-http-initialized-clock.php';
require_once __DIR__ . '/source-policy-http-initialized-native-data.php';
require_once __DIR__ . '/source-policy-http-owned-diagnostic.php';

/** Non-catchable native refusal, including callbacks after the final sidecar.
 *
 * @param string $label Source-owned reason only.
 */
function adbh_initializer_refuse( $label ) {
	$GLOBALS['adbh_failure'] = hash( 'sha256', $label );
	fwrite( STDERR, json_encode( adbh_setup_diagnostic( new RuntimeException(), dirname( __DIR__, 3 ) ) ) . "\n" );
	exit( 1 );
}

/** Real pre-execution filter; never delegates to the installer's broad setup rule.
 *
 * @param string $sql Original core query.
 * @return string
 */
function adbh_initializer_query( $sql ) {
	try {
		$hook = $GLOBALS['wp_filter']['query'] ?? null;
		adbh_check( is_object( $hook ) && 'WP_Hook' === get_class( $hook ) && adbh_initialized_query_registry( $hook->callbacks, dirname( __DIR__, 3 ) . '/core/wp-includes/class-wpdb.php' ), 'initializer-query-registry' );
		return $GLOBALS['adbh_initializer_guard']->filter( $sql, ( $GLOBALS['adbh_initialized_clock'] )() );
	} catch ( Throwable $error ) {
		adbh_initializer_refuse( 'initializer-query-refused' );
	}
}

/** No transport, mail, local-network or hard-file exception in this role. */
function adbh_initializer_deny() {
	adbh_initializer_refuse( 'initializer-effect-refused' );
}

/** Fixed callback presence plus no source-policy implementation loaded. */
function adbh_initializer_ready() {
	adbh_check( 1 === has_filter( 'query', 'adbh_initializer_query' ), 'initializer-query-hook' );
	foreach ( array( 'pre_http_request', 'flush_rewrite_rules_hard', 'block_local_requests', 'pre_wp_mail' ) as $hook ) {
		adbh_check( 10 === has_filter( $hook, 'adbh_initializer_deny' ), 'initializer-effect-hook' );
	}
	foreach ( get_included_files() as $file ) {
		adbh_check( false === strpos( $file, '/proof/includes/' ), 'initializer-no-owner-source' );
	}
	adbh_check( false === wp_cache_get( 'alynt_drime_backups_dashboard_source_policies', 'options' ) && false === wp_cache_get( 'owned_non_target', 'options' ), 'initializer-protected-cache' );
	$all = wp_cache_get( 'alloptions', 'options' );
	adbh_check( ! is_array( $all ) || ( ! array_key_exists( 'alynt_drime_backups_dashboard_source_policies', $all ) && ! array_key_exists( 'owned_non_target', $all ) ), 'initializer-protected-autoload' );
	adbh_check( array() === $_COOKIE && ( ! isset( $GLOBALS['current_user'] ) || ( is_object( $GLOBALS['current_user'] ) && 'WP_User' === get_class( $GLOBALS['current_user'] ) && 0 === $GLOBALS['current_user']->ID ) ), 'initializer-no-principal' );
	adbh_check( ! wp_installing() && ! ( defined( 'SHORTINIT' ) && SHORTINIT ) && '' === get_option( 'permalink_structure' ), 'initializer-full-core' );
	adbh_source_check();
}

try {
	$root = dirname( __DIR__, 3 );
	$p    = require $root . '/private.php';
	adbh_check( is_array( $p ) && 'owned-http-l714-initialized-v2' === ( $p['model'] ?? null ) && preg_match( '/\A[a-f0-9]{32}\z/', $p['run'] ) && realpath( $root ) === $root && $root === $p['root'] . '/adb-http-' . $p['run'] && 'adbh_' . substr( $p['run'], 0, 12 ) . '_' === $p['prefix'], 'initializer-private' );
	$GLOBALS['adbh_private']           = $p;
	$GLOBALS['adbh_initialized_clock'] = adbh_initialized_clock( $p );
	$raw                               = stream_get_contents( STDIN, 8193 );
	adbh_check( is_string( $raw ) && strlen( $raw ) > 0 && strlen( $raw ) <= 8192 && feof( STDIN ), 'initializer-private-wire' );
	$ticket = json_decode( $raw, true );
	unset( $raw );
	adbh_check( ! defined( 'WP_INSTALLING' ) && ! defined( 'SHORTINIT' ) && ! defined( 'ABSPATH' ), 'initializer-fresh-context' );
	$GLOBALS['adbh_initializer_guard'] = new ADBH_Initialization_Query_Guard(
		$ticket,
		array(
			'sapi'                     => 'cli',
			'before_bootstrap'         => true,
			'installing_override'      => false,
			'short_init_override'      => false,
			'protected_cache_absent'   => true,
			'protected_non_autoloaded' => true,
			'owner_absent'             => true,
			'ddl_sealed'               => true,
		),
		( $GLOBALS['adbh_initialized_clock'] )()
	);
	$id                                = array_intersect_key( $p, array_flip( array( 'run', 'head', 'image', 'root' ) ) );
	adbh_check( $ticket['run'] === $p['run'] && $ticket['head'] === $p['head'] && $ticket['image'] === $p['image'] && hash( 'sha256', json_encode( $id, JSON_UNESCAPED_SLASHES ) ) === $ticket['owner'] && hash_file( 'sha256', $root . '/manifest.json' ) === $ticket['manifest'] && (int) $p['setup_started'] === $ticket['setup_started'] && (int) $p['setup_deadline'] === $ticket['setup_deadline'], 'initializer-ticket-binding' );
	// The parent independently checked non-autoloaded protected rows and revoked
	// DDL before this fresh child; it compares all protected anchors after exit.
	$GLOBALS['adbh_stage'] = 'early-hooks';
	set_error_handler(
		static function ( $severity, $message, $file, $line ) use ( $root ) {
			$GLOBALS['adbh_diagnostic_location'] = adbh_diagnostic_location( $file, $line, $root );
			adbh_initializer_refuse( 'initializer-php-diagnostic' );
		}
	);
	require_once $root . '/core/wp-includes/plugin.php';
	add_filter( 'query', 'adbh_initializer_query', 1, 1 );
	foreach ( array(
		'pre_http_request'         => 3,
		'flush_rewrite_rules_hard' => 1,
		'block_local_requests'     => 1,
		'pre_wp_mail'              => 2,
	) as $hook => $args ) {
		add_filter( $hook, 'adbh_initializer_deny', 10, $args );
	}
	$_SERVER['HTTP_HOST']      = '127.0.0.1:18777';
	$_SERVER['SERVER_NAME']    = '127.0.0.1';
	$_SERVER['SERVER_PORT']    = '18777';
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI']    = '/';
	$_SERVER['PHP_SELF']       = '/index.php';
	$GLOBALS['adbh_stage']     = 'core-bootstrap';
	require $root . '/core/wp-load.php';
	$GLOBALS['adbh_stage'] = 'core-ready';
	adbh_initializer_ready();
	adbh_check( 0 === did_action( 'shutdown' ), 'initializer-before-stock-shutdown' );
	// Stock core registered shutdown_action_hook first. Do not invoke it manually,
	// remove observers, mint a principal, or publish an early completion packet.
	register_shutdown_function(
		static function () use ( $ticket ) {
			try {
				$GLOBALS['adbh_stage'] = 'finalize';
				adbh_check( 1 === did_action( 'shutdown' ) && ( ! error_get_last() || ! in_array( error_get_last()['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ), true ) ), 'initializer-stock-shutdown' );
				adbh_initializer_ready();
				( $GLOBALS['adbh_initialized_clock'] )();
				$completion = array_merge(
					$ticket,
					array(
						'sql'               => $GLOBALS['adbh_initializer_guard']->finish(),
						'full_bootstrap'    => true,
						'installing'        => false,
						'short_init'        => false,
						'protected_reads'   => 0,
						'protected_writes'  => 0,
						'owner_allocations' => 0,
						'session_writes'    => 0,
						'mail'              => 0,
						'outbound'          => 0,
						'file_writes'       => 0,
						'source_unchanged'  => true,
					)
				);
				echo json_encode(
					array(
						'event'          => 'owned-http-initializer-complete',
						'stock_shutdown' => true,
						'completion'     => $completion,
					)
				) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Private bounded count/binding stdout only.
				// The filter remains armed: any late query is non-catchably refused and
				// changes exit status. Parent requires exit0 plus independent release.
			} catch ( Throwable $error ) {
				adbh_initializer_refuse( 'initializer-final-refused' );
			}
		}
	);
} catch ( Throwable $error ) {
	fwrite( STDERR, json_encode( adbh_setup_diagnostic( $error, dirname( __DIR__, 3 ) ) ) . "\n" );
	exit( 1 );
}
