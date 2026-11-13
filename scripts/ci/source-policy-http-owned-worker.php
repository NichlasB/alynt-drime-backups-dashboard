<?php
/**
 * Explicitly admitted owned CI bootstrap. Never an installed plugin entry.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL, WordPress.DB.RestrictedFunctions, WordPress.WP.GlobalVariablesOverride -- Explicit isolated CI fixtures/uncached evidence only, no installed state.
if ( defined( 'ABSPATH' ) || ! in_array( PHP_SAPI, array( 'cli', 'cli-server' ), true ) || 'Linux' !== PHP_OS_FAMILY || '8.5.12' !== PHP_VERSION || PHP_ZTS || 8 !== PHP_INT_SIZE || ! extension_loaded( 'mysqli' ) || ! extension_loaded( 'mysqlnd' ) || extension_loaded( 'pdo_mysql' ) || false !== php_ini_loaded_file() || false !== php_ini_scanned_files() ) {
	exit( 1 ); }

/** Safe assertion, no messages or private values in output.
 *
 * @param bool   $ok Required condition.
 * @param string $label Code-owned stage label.
 * @throws RuntimeException On failed evidence.
 */
function adbh_check( $ok, $label ) {
	$GLOBALS['adbh_checks'] = ( $GLOBALS['adbh_checks'] ?? 0 ) + 1;
	if ( ! $ok ) {
		$GLOBALS['adbh_failure'] = hash( 'sha256', $label );
		throw new RuntimeException( 'Owned HTTP worker refused' ); }
}

/** Bind every actual loaded file to the parent's frozen manifest, including late includes. */
function adbh_source_check() {
	$p        = $GLOBALS['adbh_private'];
	$root     = dirname( __DIR__, 3 );
	$manifest = json_decode( file_get_contents( $root . '/manifest.json' ), true );
	adbh_check( is_array( $manifest ) && count( $manifest ) <= 8192, 'manifest-shape' );
	$map = array(); foreach ( $manifest as $row ) {
		$path = $root . '/' . $row['path'];
		adbh_check( is_file( $path ) && ! is_link( $path ) && filesize( $path ) === $row['size'] && hash_equals( $row['digest'], hash_file( 'sha256', $path ) ), 'frozen-file' );
		$map[ $path ] = $row;
	}
	foreach ( get_included_files() as $path ) {
		$real = realpath( $path );
		adbh_check( isset( $map[ $real ] ) && is_file( $real ) && ! is_link( $real ) && filesize( $real ) === $map[ $real ]['size'] && hash_equals( $map[ $real ]['digest'], hash_file( 'sha256', $real ) ), 'loaded-source' );
	}
	adbh_check( '7.1.4' === $GLOBALS['wp_version'] && '67108864' === (string) wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) ) && '64M' === WP_MEMORY_LIMIT && '64M' === WP_MAX_MEMORY_LIMIT && ! wp_using_ext_object_cache() && ! is_multisite(), 'runtime-profile' );
	adbh_check( DB_NAME === $p['database'] && DB_USER === $p['user'] && DB_HOST === 'localhost:' . $p['socket'] && 'utf8' === DB_CHARSET && '' === DB_COLLATE && $GLOBALS['wpdb']->prefix === $p['prefix'] && 'wpdb' === get_class( $GLOBALS['wpdb'] ) && 0 === ( new mysqli_driver() )->report_mode, 'storage-profile' );
}

/** Install callbacks through genuine plugin API before complete stock bootstrap.
 *
 * @param string $purpose Fixed source-owned setup/request enum.
 */
function adbh_bootstrap( $purpose ) {
	global $table_prefix;
	$root                  = dirname( __DIR__, 3 );
	$GLOBALS['adbh_stage'] = 'early-hooks';
	require_once __DIR__ . '/source-policy-http-owned-diagnostic.php';
	adbh_check( false === strpos( $root, ' ' ) && realpath( $root ) === $root && 'true' === getenv( 'ADB_HTTP_OWNED_APPROVED' ), 'owned-worker-root' );
	$p = require $root . '/private.php';
	adbh_check( is_array( $p ) && preg_match( '/\A[a-f0-9]{32}\z/', $p['run'] ) && $root === $p['root'] . '/adb-http-' . $p['run'] && preg_match( '/\Aadbh_[a-f0-9]{12}_\z/', $p['prefix'] ), 'owned-private-context' );
	$GLOBALS['adbh_private'] = $p;
	$GLOBALS['adbh_ledger']  = array(
		'setup'     => 0,
		'bootstrap' => 0,
		'auth'      => 0,
	);
	define( 'ADB_HTTP_WORKER', $purpose );
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Safe refusal, never raw diagnostics.
	set_error_handler(
		static function ( $severity, $message, $file, $line ) use ( $root ) {
			$GLOBALS['adbh_diagnostic_location'] = adbh_diagnostic_location( $file, $line, $root );
			adbh_check( false, 'php-diagnostic' );
		}
	);
	require_once $root . '/core/wp-includes/plugin.php';
	require_once __DIR__ . '/source-policy-http-installer.php';
	require_once __DIR__ . '/source-policy-http-owned-observer.php';
	$GLOBALS['adbh_containment'] = new Adbh_Installer_Containment( array_intersect_key( $p, array_flip( array( 'run', 'head', 'image', 'root' ) ) ), $purpose );
	$GLOBALS['adbh_containment']->register();
	add_filter( 'query', 'adbh_owned_observe', PHP_INT_MIN, 1 );
	if ( 'setup' === $purpose ) {
		define( 'WP_INSTALLING', true ); }
	$GLOBALS['adbh_stage'] = 'core-bootstrap';
	require $root . '/core/wp-load.php';
	$GLOBALS['adbh_stage'] = 'core-ready';
	adbh_source_check();
}

/** Count-only bounded ledgers; no raw SQL/secret evidence.
 *
 * @param string $phase Fixed phase.
 * @return array
 */
function adbh_ledger_packet( $phase ) {
	return array(
		'phase'             => $phase,
		'sql'               => $GLOBALS['adbh_ledger'][ $phase ],
		'policy_reads'      => 0,
		'policy_writes'     => 0,
		'owner_allocations' => 0,
		'unknown_changes'   => 0,
		'mail'              => 'setup' === $phase ? $GLOBALS['adbh_containment']->summary()['mail'] : 0,
		'outbound'          => 0,
	);
}

/** Exact private sidecar after stock shutdown, not a runtime authority.
 *
 * @param array|null $post Independent first-post fields only during setup.
 * @return array
 */
function adbh_finish_packet( $post = null ) {
	adbh_source_check();
	adbh_check( ! isset( $GLOBALS['adbh_failure'] ) && '' === get_option( 'permalink_structure' ) && ! wp_installing(), 'final-core-state' );
	return $GLOBALS['adbh_containment']->seal(
		$post,
		array(
			'permalinks'     => '',
			'files_equal'    => true,
			'installing'     => false,
			'source_matches' => true,
		)
	);
}
