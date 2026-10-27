<?php
/**
 * Private genuine-core composition and fixed owned query observations.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.DB.DirectDatabaseQuery -- Private confirmed CLI proof, never application state.
if ( PHP_SAPI !== 'cli' || ! defined( 'ADB_RETENTION_PROOF' ) ) {
	exit( 1 );
}
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_ADMIN', true );
define( 'WP_DEBUG', false );
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
define( 'WP_PLUGIN_URL', 'http://plugin-tester.local/owned-retention-plugins' );
define( 'WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins' );
define( 'WPMU_PLUGIN_URL', 'http://plugin-tester.local/owned-retention-mu' );
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
$wpdb->suppress_errors( true ); // Expected physical faults are asserted, not printed as private SQL.
if ( false === $wpdb->query( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=2, lock_wait_timeout=2' ) ) {
	throw new RuntimeException( 'Bounded fixture session unavailable' );
}
$wpdb->set_prefix( $argv[2] );
$tables        = array( $wpdb->options, $argv[2] . 'alynt_drime_dashboard_snapshots', $argv[2] . 'alynt_drime_dashboard_actions' );
$queries       = 0;
$faults        = array();
$trace         = array();
$deletes       = array();
$before_delete = null;
add_filter(
	'query',
	function ( $sql ) use ( $tables, &$queries, &$faults, &$trace, &$deletes, &$before_delete ) {
		adb_retention_assert_sql_scope( $sql, $GLOBALS['argv'][2] );
		++$queries;
		foreach ( array(
			1 => 'snapshots',
			2 => 'actions',
		) as $index => $kind ) {
			if ( 0 === strpos( $sql, 'DELETE FROM ' . $tables[ $index ] ) ) {
				$trace[]   = $kind;
				$deletes[] = array( $kind, $sql );
				if ( $before_delete ) {
					$callback = $before_delete;
					$callback( $kind, $sql );
				}
				if ( ! empty( $faults[ $kind ] ) ) {
					return $sql . ' ADB_OWNED_INVALID_SYNTAX';
				}
			}
		}
		if ( preg_match( '/^(?:INSERT|UPDATE)\b/', $sql ) && false !== strpos( $sql, 'alynt_drime_backups_dashboard_diagnostics_events' ) ) {
			$trace[] = 'event_write';
			if ( ! empty( $faults['events'] ) ) {
				return $sql . ' ADB_OWNED_INVALID_SYNTAX';
			}
		}
		return $sql;
	}
);
add_filter(
	'pre_http_request',
	function () {
		throw new RuntimeException( 'HTTP is outside this proof' );
	}
);
require_once dirname( __DIR__, 2 ) . '/alynt-drime-backups-dashboard.php';
