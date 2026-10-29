<?php
/**
 * Confirmed Plugin Tester bootstrap qualification; never an application entry.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.DB.RestrictedFunctions -- Explicit private native proof only; no application bootstrap or imported candidate handle.
if ( PHP_SAPI !== 'cli' || 4 !== count( $argv ) || '--confirmed-plugin-tester-owned-bootstrap-proof' !== $argv[1]
	|| ! preg_match( '/\Aadbb_[a-f0-9]{12}_\z/', $argv[2] ) || ! in_array( $argv[3], array( 'audit', 'proof', 'diagnose-raw', 'diagnose-owner' ), true ) || defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Requires confirmed Plugin Tester owned bootstrap proof scope\n" );
	exit( 1 );
}
if ( 'Windows' !== PHP_OS_FAMILY || '8.5.12' !== PHP_VERSION || ! extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' )
	|| ( 'audit' !== $argv[3] && ! getenv( 'MYSQL_PWD' ) ) ) {
	fwrite( STDERR, "Requires the audited bundled PHP8.5.12 mysqli-only profile\n" );
	exit( 1 );
}
require_once __DIR__ . '/source-policy-supported-profile.php';
require_once __DIR__ . '/source-policy-proof-reflection.php';
$profile = adbp_profile( '7.1.3' ); // Fixed existing Windows candidate; no minimum native runner enabled.
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_DEBUG', false );
define( 'MULTISITE', false );
define( 'SAVEQUERIES', false );
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'DB_CHARSET', 'utf8mb4' ); // Private owned-fixture profile, not wp-config compatibility.
define( 'DB_COLLATE', '' );
define( 'AUTH_SALT', 'owned-non-secret-native-bootstrap-proof' );
$pins   = $profile['pins'];
$checks = 0;
$group  = 'source-audit';

/**
 * Checks a fixed non-secret fact; no native diagnostic or credential is printed.
 *
 * @param bool   $condition Required fact.
 * @param string $label Fixed assertion label.
 * @return void
 * @throws RuntimeException On a failed assertion.
 */
function adbb_check( $condition, $label ) {
	global $checks;
	++$checks;
	$GLOBALS['adbb_last_check'] = $label;
	if ( ! $condition ) {
		throw new RuntimeException( $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed CLI label only.
	}
}

try {
	foreach ( $pins as $file => $hash ) {
		adbb_check( is_file( ABSPATH . WPINC . '/' . $file ) && hash_equals( $hash, hash_file( 'sha256', ABSPATH . WPINC . '/' . $file ) ), 'pinned genuine source' );
	}
	foreach ( $profile['includes'] as $file ) {
		require_once ABSPATH . WPINC . '/' . $file;
	}
	foreach ( array( 'class-source-policy.php', 'class-source-policy-native-profile.php', 'class-source-policy-query-context.php', 'class-source-policy-no-reconnect-connection.php' ) as $file ) {
		require_once dirname( __DIR__, 2 ) . '/includes/' . $file;
	}
	wp_using_ext_object_cache( false ); // Initialize this private process's core-cache posture, not site configuration.
	wp_cache_init();
	$wpdb    = null;
	$binding = array(
		'core_file'       => ABSPATH . WPINC . '/' . $profile['core_file'],
		'driver_file'     => null,
		'cache_file'      => ABSPATH . WPINC . '/' . $profile['cache_file'],
		'cache_api_file'  => ABSPATH . WPINC . '/' . $profile['cache_api_file'],
		'parser_api_file' => ABSPATH . WPINC . '/' . $profile['parser_api_file'],
		'observers'       => array(),
		'observer_files'  => array(),
	);
	$class   = new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' );
	$owner   = adbb_construct( $class );
	$frame   = array(
		'core'      => '7.1.3',
		'database'  => 'local',
		'table'     => $argv[2] . 'options',
		'host'      => '127.0.0.1',
		'port'      => 10004,
		'user'      => 'root',
		'password'  => 'audit' === $argv[3] ? 'inert-audit-only' : getenv( 'MYSQL_PWD' ),
		'charset'   => DB_CHARSET,
		'collate'   => DB_COLLATE,
		'transport' => 'tcp_loopback',
		'socket'    => null,
		'db_host'   => '127.0.0.1:10004',
	);
	adbb_check( Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::settings( $frame ), 'finite private fixture settings' );
	adbb_check( Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::source( $owner, $binding ), 'genuine executable closure and empty observer eligibility' );
	adbb_check( null === $owner->dbh && ! $owner->ready && 0 === $owner->num_queries, 'own constructor remains handle-free' );
	adbb_check( is_wp_error( $owner::create() ) && false === $owner->db_connect() && false === $owner->check_connection(), 'factories and reconnect remain closed' );
	$stats = mysqli_get_client_stats();
	adbb_check( '0' === $stats['connect_success'] && '0' === $stats['connect_failure'] && '0' === $stats['com_query'], 'audit opens no native session or SQL' );
	$expected = array_map(
		static function ( $file ) {
			return realpath( ABSPATH . WPINC . '/' . $file );
		},
		array_keys( $pins )
	);
	foreach ( get_included_files() as $included ) {
		adbb_check( 0 !== strpos( $included, realpath( ABSPATH ) . DIRECTORY_SEPARATOR ) || in_array( $included, $expected, true ), 'only pinned core libraries, never application bootstrap' );
	}
	if ( 'audit' === $argv[3] ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed CLI text and integer assertion count only.
		echo 'audit checks:', $checks, " native_connections:0 sql_queries:0 fixtures:0\n";
		return;
	}
	if ( in_array( $argv[3], array( 'diagnose-raw', 'diagnose-owner' ), true ) ) {
		require __DIR__ . '/source-policy-failed-connect-diagnosis.php';
		$group  = $argv[3];
		$report = adbb_failed_connect_diagnosis( $class, $frame, $binding, $group );
		adbb_check( true === $report['close_ack'] && true === $report['closed_handle_probe'], 'diagnostic owned release confirmed' );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed labels, booleans and bounded native counter DATA only; no credentials/errors/SQL.
		echo json_encode(
			array(
				'checks'        => $checks,
				'diagnosis'     => $report,
				'qualification' => 'not-run',
			)
		), "\n";
		return;
	}
	require __DIR__ . '/source-policy-bootstrap-native-cases.php';
	$report = adbb_native_cases( $class, $frame, $binding );
	echo json_encode(
		array(
			'checks'            => $checks,
			'cases'             => $report,
			'production_wiring' => 'disabled',
		)
	), "\n";
} catch ( Throwable $error ) {
	if ( in_array( $group, array( 'diagnose-raw', 'diagnose-owner' ), true ) ) {
		fwrite( STDERR, 'Owned failed-connect diagnosis stopped in ' . $group . ' after ' . $checks . ' checks reached; last recorded assertion (not exception cause): ' . ( $GLOBALS['adbb_last_check'] ?? 'none' ) . "\n" );
		exit( 1 );
	}
	fwrite( STDERR, 'Owned bootstrap proof failed in ' . $group . ' after ' . $checks . ' checks: ' . ( $GLOBALS['adbb_last_check'] ?? 'before assertion' ) . "\n" );
	exit( 1 );
}
