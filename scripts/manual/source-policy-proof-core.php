<?php
/**
 * Exact Plugin Tester core libraries, private connections and process-local cache.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Explicit manual owned fixture composition only; never a WordPress bootstrap or production caller.
if ( PHP_SAPI !== 'cli' || ! defined( 'ADB_POLICY_PROOF' ) ) {
	exit( 1 );
}
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_ADMIN', true );
define( 'WP_DEBUG', false );
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'MULTISITE', false );
define( 'SAVEQUERIES', false );
foreach ( array( 'object-cache.php', 'db.php', 'advanced-cache.php' ) as $dropin ) {
	if ( file_exists( WP_CONTENT_DIR . '/' . $dropin ) ) {
		throw new RuntimeException( 'Unqualified drop-in profile' );
	}
}
foreach ( array( 'version.php', 'compat-utf8.php', 'compat.php', 'load.php', 'plugin.php', 'utf8.php', 'formatting.php', 'functions.php', 'class-wp-error.php', 'cache.php', 'class-wpdb.php', 'pomo/translations.php', 'l10n.php', 'option.php' ) as $file ) {
	require_once ABSPATH . WPINC . '/' . $file;
}
if ( '7.1.3' !== $wp_version || ! function_exists( 'wp_determine_option_autoload_value' ) ) {
	throw new RuntimeException( 'Installed core profile changed; audit first' );
}
wp_cache_init();
foreach ( array( 'default', 'alynt-drime-backups-dashboard' ) as $domain ) {
	$l10n[ $domain ] = new Translations();
}
$wpdb = new wpdb( 'root', getenv( 'MYSQL_PWD' ), 'local', '127.0.0.1:10004' );
if ( ! $wpdb->ready ) {
	throw new RuntimeException( 'Confirmed local database unavailable' );
}
$wpdb->hide_errors();
$wpdb->suppress_errors( true ); // Expected native failures are asserted, never echoed/logged.
if ( false === $wpdb->query( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1' ) ) {
	throw new RuntimeException( 'Owned statement budgets unavailable' );
}
$wpdb->set_prefix( $argv[2] );
$table            = $wpdb->options;
$queries          = 0;
$core_add_control = false;
add_filter(
	'query',
	function ( $sql ) use ( $table, &$queries, &$core_add_control ) {
		adbp_sql_scope( $sql, $table, $core_add_control );
		++$queries;
		return $sql;
	}
);
foreach ( array( 'class-source-policy.php', 'class-source-policy-save-contract.php' ) as $file ) {
	require_once dirname( __DIR__, 2 ) . '/includes/' . $file;
}
