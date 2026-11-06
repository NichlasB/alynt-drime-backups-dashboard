<?php
/**
 * Frozen library setup for the private request proof, not WordPress bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions, WordPress.WP.GlobalVariablesOverride.Prohibited -- Exact-job standalone library/proof ambient only, never installed WordPress state.
if ( ! defined( 'ADB_M_REQUEST_PROOF' ) ) {
	exit( 1 );
}

/**
 * Fixed aggregate assertion; never echo an argument or exception message.
 *
 * @param bool   $condition Required fact.
 * @param string $label Source-owned label, retained only as a digest on failure.
 * @return void
 * @throws RuntimeException On refusal.
 */
function adbg_check( $condition, $label ) {
	$GLOBALS['adbg_checks'] = ( $GLOBALS['adbg_checks'] ?? 0 ) + 1;
	if ( ! $condition ) {
		$GLOBALS['adbg_assertion'] = $GLOBALS['adbg_assertion'] ?? hash( 'sha256', $label );
		throw new RuntimeException( 'Request proof refused' );
	}
}

/**
 * Existing immutable source/module readers use this assertion contract.
 *
 * @param bool   $condition Required fact.
 * @param string $label Fixed source-owned label.
 * @return void
 */
function adbb_check( $condition, $label ) {
	adbg_check( $condition, $label );
}

/**
 * Load definitions only after exact job/runtime admission. No auth or SQL.
 *
 * @return array Parent-frozen rows and module digest.
 */
function adbg_load() {
	global $wp_version;
	if ( defined( 'ADB_WL_REQUEST_PROOF' ) ) {
		return adbwl_load();
	}
	adbg_check( false === php_ini_loaded_file() && false === php_ini_scanned_files() && '' === (string) ini_get( 'auto_prepend_file' ) && '' === (string) ini_get( 'auto_append_file' ), 'no injected process configuration' );
	$core = realpath( dirname( __DIR__, 2 ) . '/core' );
	adbg_check( false !== $core && dirname( $core ) === realpath( dirname( __DIR__, 2 ) ), 'fixed core child' );
	define( 'ABSPATH', $core . '/' );
	define( 'WPINC', 'wp-includes' );
	define( 'WP_DEBUG', false );
	define( 'MULTISITE', false );
	define( 'SAVEQUERIES', false );
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	define( 'DB_CHARSET', 'utf8' );
	define( 'DB_COLLATE', '' );
	define( 'HOUR_IN_SECONDS', 3600 );
	define( 'DAY_IN_SECONDS', 86400 );
	define( 'YEAR_IN_SECONDS', 31536000 );
	define( 'LOGGED_IN_COOKIE', 'adb_owned_logged_in' );
	define( 'DOING_AJAX', false );
	$keys = array();
	foreach ( array( 'AUTH', 'SECURE_AUTH', 'LOGGED_IN', 'NONCE' ) as $scheme ) {
		foreach ( array( 'KEY', 'SALT' ) as $part ) {
			$key    = bin2hex( random_bytes( 32 ) );
			$keys[] = $key;
			define( $scheme . '_' . $part, $key );
		}
	}
	adbg_check( 8 === count( array_unique( $keys ) ), 'distinct owned keys' );
	unset( $keys, $key );
	$base  = require __DIR__ . '/../manual/source-policy-supported-core-pins.php';
	$extra = require __DIR__ . '/source-policy-minimum-request-pins.php';
	adbg_check( 11 === count( $base['6.0']['pins'] ) && 9 === count( $extra ) && ! array_intersect_key( $base['6.0']['pins'], $extra ), 'twenty distinct pinned proof files' );
	foreach ( $base['6.0']['pins'] as $name => $digest ) {
		adbg_check( hash_equals( $digest, hash_file( 'sha256', ABSPATH . WPINC . '/' . $name ) ), 'fixed base source' );
	}
	foreach ( $extra as $name => $row ) {
		$path = ABSPATH . WPINC . '/' . $name;
		// phpcs:ignore WordPress.PHP.YodaConditions -- Exact pinned byte count, not normalization.
		adbg_check( $row[0] === filesize( $path ) && hash_equals( $row[1], hash_file( 'sha256', $path ) ), 'fixed auth source' );
	}
	foreach ( $base['6.0']['includes'] as $name ) {
		require_once ABSPATH . WPINC . '/' . $name;
	}
	foreach ( array( 'class-wp-role.php', 'class-wp-roles.php', 'class-wp-user.php', 'capabilities.php', 'meta.php', 'class-wp-session-tokens.php', 'class-wp-user-meta-session-tokens.php', 'user.php', 'pluggable.php' ) as $name ) {
		require_once ABSPATH . WPINC . '/' . $name;
	}
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
	adbg_check( ( new ReflectionFunction( 'current_user_can' ) )->getFileName() === realpath( ABSPATH . WPINC . '/capabilities.php' ), 'genuine capability API' );
	require_once __DIR__ . '/source-policy-minimum-worker-source.php';
	$rows    = adbm_source_rows();
	$modules = adbm_modules();
	adbg_check( hash_equals( getenv( 'MINIMUM_IMAGE' ), hash_file( 'sha256', dirname( __DIR__, 2 ) . '/cohort.sha256' ) ), 'parent frozen cohort' );
	adbg_check( '0' === mysqli_get_client_stats()['connect_success'] && '0' === mysqli_get_client_stats()['com_query'], 'definition audit has zero native effects' );
	return array( $rows, $modules );
}

/**
 * Normal genuine ambient constructor, never a borrowed or reflected handle.
 *
 * @return wpdb Owned proof ambient.
 */
function adbg_ambient() {
	if ( defined( 'ADB_WL_REQUEST_PROOF' ) ) {
		return adbwl_ambient();
	}
	adbg_check( 1 === preg_match( '/\A[a-f0-9]{64}\z/', (string) getenv( 'QUALIFICATION_PASSWORD' ) ), 'owned private credential' );
	define( 'DB_NAME', 'adb_minimum' );
	define( 'DB_USER', 'adbm' );
	define( 'DB_PASSWORD', getenv( 'QUALIFICATION_PASSWORD' ) );
	define( 'DB_HOST', '127.0.0.1:3306' );
	adbg_check( '6.0' === ( $GLOBALS['wp_version'] ?? null ), 'actual pinned core version' );
	$GLOBALS['blog_id'] = 1;
	$GLOBALS['wpdb']    = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
	$ambient            = $GLOBALS['wpdb'];
	adbg_check( true === $ambient->ready && $ambient->dbh instanceof mysqli && 0 === ( new mysqli_driver() )->report_mode, 'genuine ambient OFF' );
	$prefix = 'adbm_' . substr( hash( 'sha256', getenv( 'GITHUB_RUN_ID' ) . ':' . getenv( 'GITHUB_RUN_ATTEMPT' ) ), 0, 12 ) . '_';
	adbg_check( ! is_wp_error( $ambient->set_prefix( $prefix ) ), 'owned core table prefix' );
	$ambient->set_blog_id( 1 );
	adbg_check( false !== $ambient->query( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1' ), 'bounded ambient statements' );
	return $ambient;
}
