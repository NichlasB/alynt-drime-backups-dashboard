<?php
/**
 * Fixed owned CI runtime/source observation, never bootstrap or database access.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// Refuse outside the exact ephemeral profile before reading any core source.
if ( PHP_SAPI !== 'cli' || 2 !== count( $argv ) || '--owned-profile-preflight' !== $argv[1]
	|| 'Linux' !== PHP_OS_FAMILY || '8.5.12' !== PHP_VERSION || 'x86_64' !== php_uname( 'm' ) || 8 !== PHP_INT_SIZE || PHP_ZTS
	|| 'true' !== getenv( 'GITHUB_ACTIONS' ) || 'workflow_dispatch' !== getenv( 'GITHUB_EVENT_NAME' )
	|| 'NichlasB/alynt-drime-backups-dashboard' !== getenv( 'GITHUB_REPOSITORY' )
	|| 'refs/heads/master' !== getenv( 'GITHUB_REF' ) || ! preg_match( '/\A[a-f0-9]{40}\z/', (string) getenv( 'GITHUB_SHA' ) )
	|| getenv( 'GITHUB_SHA' ) !== getenv( 'EXPECTED_SHA' ) || ! extension_loaded( 'mysqli' ) || ! extension_loaded( 'mysqlnd' ) ) {
	fwrite( STDERR, "Requires exact owned Linux CI environment-only preflight\n" );
	exit( 1 );
}

// phpcs:disable WordPress.DB.RestrictedFunctions -- Native client statistics/version observations only; no handle allocation, authentication or SQL.
// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Bounded CLI diagnostics become refusal, never suppressed-and-continued or raw output.
set_error_handler(
	static function () {
		throw new RuntimeException( 'Preflight diagnostic' );
	}
);
try {
	if ( 'mysqlnd 8.5.12' !== mysqli_get_client_info() || '' !== ini_get( 'auto_prepend_file' ) || '' !== ini_get( 'auto_append_file' )
		|| 1 !== count( get_included_files() ) || ! in_array( 'unix', stream_get_transports(), true ) ) {
		throw new RuntimeException( 'Runtime profile unavailable' );
	}
	$core = realpath( __DIR__ . '/../../.adb-linux-preflight-core/wp-includes' );
	$pins = json_decode( file_get_contents( __DIR__ . '/source-policy-linux-core-pins.json' ), true );
	if ( false === $core || ! is_array( $pins ) || 11 !== count( $pins ) ) {
		throw new RuntimeException( 'Pinned core unavailable' );
	}
	foreach ( $pins as $file => $hash ) {
		$core_path = $core . '/' . $file;
		if ( ! preg_match( '/\A[a-z-]+\.php\z/', $file ) || ! preg_match( '/\A[a-f0-9]{64}\z/', $hash )
			|| ! is_file( $core_path ) || is_link( $core_path ) || filesize( $core_path ) > 524288 || ! hash_equals( $hash, hash_file( 'sha256', $core_path ) ) ) {
			throw new RuntimeException( 'Pinned core mismatch' );
		}
	}
	$version = file_get_contents( $core . '/version.php', false, null, 0, 16384 );
	if ( ! preg_match( '/\$wp_version\s*=\s*\x277\.1\.4\x27\s*;/', $version ) ) {
		throw new RuntimeException( 'Core version mismatch' );
	}
	$stats = mysqli_get_client_stats();
	foreach ( array( 'connect_success', 'connect_failure', 'com_query' ) as $key ) {
		if ( '0' !== ( $stats[ $key ] ?? null ) ) {
			throw new RuntimeException( 'Unexpected native activity' );
		}
	}
	$driver = new mysqli_driver();
	if ( 3 !== $driver->report_mode ) {
		throw new RuntimeException( 'Fresh reporting context mismatch' );
	}
	$modules = array();
	foreach ( array(
		'mysqlnd' => 'mysqlnd 8.5.12',
		'mysqli'  => '8.5.12',
	) as $module => $module_version ) {
		if ( ( new ReflectionExtension( $module ) )->getVersion() !== $module_version ) {
			throw new RuntimeException( 'Module version mismatch' );
		}
		$modules[ $module ] = hash_file( 'sha256', ini_get( 'extension_dir' ) . '/' . $module . '.so' );
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed profile values, public binary digest and zero-activity counters only.
	echo json_encode(
		array(
			'php'                 => PHP_VERSION,
			'client'              => mysqli_get_client_info(),
			'php_sha256'          => hash_file( 'sha256', PHP_BINARY ),
			'module_sha256'       => $modules,
			'core'                => '7.1.4',
			'core_pins'           => count( $pins ),
			'initial_report_mode' => $driver->report_mode,
			'native_connections'  => 0,
			'sql_queries'         => 0,
			'wordpress_execution' => 0,
			'qualification'       => 'not-established',
		)
	), "\n";
} catch ( Throwable $error ) {
	fwrite( STDERR, "Owned Linux profile/source preflight refused\n" );
	exit( 1 );
} finally {
	restore_error_handler();
}
