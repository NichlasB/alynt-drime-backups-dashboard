<?php
/**
 * Private genuine-core composition; no installed WordPress bootstrap or auth.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.DB.DirectDatabaseQuery -- Approved exact local fixture composition only.
if ( PHP_SAPI !== 'cli' || ! defined( 'ADB_LATEST_PROOF' ) ) {
	exit( 1 );
}
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_ADMIN', true );
define( 'WP_DEBUG', false );
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
define( 'WP_PLUGIN_URL', 'http://plugin-tester.local/owned-latest-plugins' );
define( 'WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins' );
define( 'WPMU_PLUGIN_URL', 'http://plugin-tester.local/owned-latest-mu' );
define( 'WP_CONTENT_URL', 'http://plugin-tester.local/wp-content' );
define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'MULTISITE', false );
define( 'SAVEQUERIES', false );
foreach ( array( 'version.php', 'compat-utf8.php', 'compat.php', 'load.php', 'plugin.php', 'utf8.php', 'formatting.php', 'functions.php', 'class-wp-error.php', 'cache.php', 'class-wpdb.php', 'pomo/translations.php', 'l10n.php', 'theme.php', 'class-wp-walker.php', 'link-template.php' ) as $file ) {
	require_once ABSPATH . WPINC . '/' . $file;
}
require_once ABSPATH . WPINC . '/default-constants.php';
wp_initial_constants();
wp_cache_init();
wp_cache_add( 'alloptions', array( 'blog_charset' => 'UTF-8' ), 'options' );
$wp_plugin_paths = array();
foreach ( array( 'default', 'alynt-drime-backups-dashboard' ) as $domain ) {
	$l10n[ $domain ] = new Translations();
}
$wpdb = new wpdb( 'root', getenv( 'MYSQL_PWD' ), 'local', '127.0.0.1:10004' );
if ( ! $wpdb->ready ) {
	throw new RuntimeException( 'Confirmed local database unavailable' );
}
$wpdb->hide_errors();
$wpdb->suppress_errors( true ); // No raw database details on private proof failures.
if ( false === $wpdb->query( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=2, lock_wait_timeout=2' ) ) {
	throw new RuntimeException( 'Bounded local session unavailable' );
}
$wpdb->set_prefix( $argv[2] );
$tables  = array( $argv[2] . 'alynt_drime_dashboard_sites', $argv[2] . 'alynt_drime_dashboard_snapshots' );
$queries = 0;
$http    = 0;
add_filter(
	'query',
	function ( $sql ) use ( &$queries ) {
		adb_latest_assert_sql_scope( $sql, $GLOBALS['argv'][2] );
		++$queries;
		if ( $queries > 200 ) {
			throw new RuntimeException( 'Owned proof query budget exceeded' );
		}
		return $sql;
	}
);
add_filter(
	'pre_http_request',
	function () use ( &$http ) {
		++$http;
		throw new RuntimeException( 'HTTP outside approved selection proof' );
	}
);
require_once dirname( __DIR__, 2 ) . '/alynt-drime-backups-dashboard.php';
