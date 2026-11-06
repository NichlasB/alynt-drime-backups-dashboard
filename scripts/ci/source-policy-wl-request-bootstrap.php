<?php
/**
 * Fixed library-only W/L setup; loaded only after the explicit child gate.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions, WordPress.WP.GlobalVariablesOverride.Prohibited -- Fixed separately admitted CLI library/owned ambient, never installed WordPress bootstrap or globals.
if ( ! defined( 'ADB_WL_REQUEST_PROOF' ) ) {
	exit( 1 ); }
require_once __DIR__ . '/source-policy-minimum-request-bootstrap.php';
require_once __DIR__ . '/source-policy-wl-request-source.php';

/** Exact W/L source catalogue, never selected by observed server output.
 *
 * @return array|false
 */
function adbwl_catalog() {
	if ( ! in_array( getenv( 'WL_REQUEST_PROFILE' ), array( 'w', 'l' ), true ) ) {
		return false;
	}
	$base = require __DIR__ . '/../manual/source-policy-supported-core-pins.php';
	$row  = $base['7.1.3'];
	if ( 'l' === getenv( 'WL_REQUEST_PROFILE' ) ) {
		$row['pins']['version.php'] = '7163a7bfaf1fbac5024c0382931f17f9993076e15ff245e93254f009e0e34cc5';
		$row['commit']              = 'db96f5eaa77a8d3e8eec8923a518fa2326f0d46a';
	}
	$row['auth'] = require __DIR__ . '/source-policy-wl-request-pins.php';
	// Independently audited L-only transitive meta.php declaration, not a wider runtime allowance.
	if ( 'l' === getenv( 'WL_REQUEST_PROFILE' ) ) {
		$row['auth']['class-wp-metadata-lazyloader.php'] = array( 6833, 'c1df481157f06db91ab7f81fe68981db206beaa38721e6cab49a064110219467' );
	}
	return $row;
}

/** Exact definitions and inventory, no WordPress bootstrap or DB access.
 *
 * @return array
 */
function adbwl_load() {
	global $wp_version;
	$w    = 'w' === getenv( 'WL_REQUEST_PROFILE' );
	$core = realpath( $w ? 'C:/Users/Captain/Local Sites/plugin-tester/app/public' : dirname( __DIR__, 2 ) . '/.adb-linux-preflight-core' );
	adbg_check( false !== $core && false === php_ini_loaded_file() && false === php_ini_scanned_files() && '' === ini_get( 'auto_prepend_file' ) && '' === ini_get( 'auto_append_file' ), 'fixed core and empty ini' );
	define( 'ABSPATH', str_replace( '\\', '/', $core ) . '/' );
	define( 'WPINC', 'wp-includes' );
	foreach ( array( 'WP_DEBUG', 'MULTISITE', 'SAVEQUERIES', 'DOING_AJAX' ) as $name ) {
		define( $name, false );
	}
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	define( 'DB_CHARSET', $w ? 'utf8mb4' : 'utf8' );
	define( 'DB_COLLATE', '' );
	define( 'HOUR_IN_SECONDS', 3600 );
	define( 'DAY_IN_SECONDS', 86400 );
	define( 'YEAR_IN_SECONDS', 31536000 );
	define( 'LOGGED_IN_COOKIE', 'adb_owned_logged_in' );
	$keys = array();
	foreach ( array( 'AUTH', 'SECURE_AUTH', 'LOGGED_IN', 'NONCE' ) as $scheme ) {
		foreach ( array( 'KEY', 'SALT' ) as $part ) {
			$key = bin2hex( random_bytes( 32 ) );
			define( $scheme . '_' . $part, $key );
			$keys[] = $key;
		}
	}
	adbg_check( 8 === count( array_unique( $keys ) ), 'distinct owned secrets without fallback options' );
	unset( $keys, $key );
	$row = adbwl_catalog();
	adbg_check( 12 === count( $row['pins'] ) && ( $w ? 9 : 10 ) === count( $row['auth'] ) && ! array_intersect_key( $row['pins'], $row['auth'] ), 'fixed profile pinned proof files, runtime allowance unchanged' );
	foreach ( $row['pins'] as $name => $hash ) {
		adbg_check( hash_equals( $hash, hash_file( 'sha256', ABSPATH . WPINC . '/' . $name ) ), 'pinned base source' );
	}
	foreach ( $row['auth'] as $name => $pin ) {
		adbg_check( $pin[0] === filesize( ABSPATH . WPINC . '/' . $name ) && hash_equals( $pin[1], hash_file( 'sha256', ABSPATH . WPINC . '/' . $name ) ), 'independently pinned nonlegacy auth source' ); // phpcs:ignore WordPress.PHP.YodaConditions -- Exact pinned byte count, no normalization.
	}
	foreach ( array_merge( $row['includes'], array( 'class-wp-role.php', 'class-wp-roles.php', 'class-wp-user.php', 'capabilities.php', 'meta.php', 'class-wp-session-tokens.php', 'class-wp-user-meta-session-tokens.php', 'user.php', 'pluggable.php' ) ) as $name ) {
		require_once ABSPATH . WPINC . '/' . $name;
	}
	adbg_check( ( $w ? '7.1.3' : '7.1.4' ) === $wp_version, 'actual version include, never an injected version' );
	wp_using_ext_object_cache( false );
	wp_cache_init();
	foreach ( array( 'source-policy', 'source-policy-native-profile', 'source-policy-query-context', 'source-policy-no-reconnect-connection', 'source-policy-save-contract', 'source-policy-stored-map', 'source-policy-native-adapter', 'source-policy-guarded-entry', 'source-policy-lazy-provider', 'storage' ) as $name ) {
		require_once dirname( __DIR__, 2 ) . '/includes/class-' . $name . '.php';
	}
	foreach ( array( 'reads', 'local-state-writes', 'writes', 'runtime-writes' ) as $name ) {
		require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-' . $name . '.php';
	}
	require_once dirname( __DIR__, 2 ) . '/includes/class-site-repository.php';
	foreach ( array( 'wp_validate_auth_cookie', 'wp_generate_auth_cookie', 'wp_set_current_user', 'wp_verify_nonce', 'wp_create_nonce', 'wp_hash' ) as $name ) {
		adbg_check( ( new ReflectionFunction( $name ) )->getFileName() === realpath( ABSPATH . WPINC . '/pluggable.php' ), 'no pluggable override' );
	}
	adbg_check( ( new ReflectionFunction( 'current_user_can' ) )->getFileName() === realpath( ABSPATH . WPINC . '/capabilities.php' ), 'genuine capabilities' );
	$rows = adbm_source_rows();
	adbg_check( 'audit' === getenv( 'WL_REQUEST_MODE' ) || hash_equals( getenv( 'WL_REQUEST_IMAGE' ), hash( 'sha256', json_encode( $rows ) ) ), 'parent frozen image' );
	$modules = adbm_modules();
	adbg_check( 'audit' === getenv( 'WL_REQUEST_MODE' ) || hash_equals( getenv( 'WL_REQUEST_MODULES' ), $modules ), 'audit and proof module inventory agree' );
	adbg_check( '0' === mysqli_get_client_stats()['connect_success'] && '0' === mysqli_get_client_stats()['com_query'], 'definition audit zero native effects' );
	return array( $rows, $modules );
}

/** Normal genuine constructor on only the fixed owned storage profile.
 *
 * @return wpdb
 */
function adbwl_ambient() {
	$w        = 'w' === getenv( 'WL_REQUEST_PROFILE' );
	$password = getenv( $w ? 'MYSQL_PWD' : 'QUALIFICATION_PASSWORD' );
	adbg_check( is_string( $password ) && '' !== $password && strlen( $password ) <= 256 && false === strpos( $password, "\0" ), 'owned private credential' );
	$socket = $w ? null : getenv( 'RUNNER_TEMP' ) . '/adb-linux-qualification-' . getenv( 'GITHUB_RUN_ID' ) . '-' . getenv( 'GITHUB_RUN_ATTEMPT' ) . '/socket/native.sock';
	define( 'DB_NAME', $w ? 'local' : 'adb_owned' );
	define( 'DB_USER', $w ? 'root' : 'adbp' );
	define( 'DB_PASSWORD', $password );
	define( 'DB_HOST', $w ? '127.0.0.1:10004' : 'localhost:' . $socket );
	$GLOBALS['blog_id'] = 1;
	$GLOBALS['wpdb']    = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
	$ambient            = $GLOBALS['wpdb'];
	adbg_check( true === $ambient->ready && $ambient->dbh instanceof mysqli && 0 === ( new mysqli_driver() )->report_mode, 'genuine ambient OFF' );
	adbg_check( ! is_wp_error( $ambient->set_prefix( getenv( 'WL_REQUEST_PREFIX' ) ) ), 'owned run prefix' );
	$ambient->set_blog_id( 1 );
	adbg_check( false !== $ambient->query( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1' ), 'bounded ambient commands' );
	return $ambient;
}
