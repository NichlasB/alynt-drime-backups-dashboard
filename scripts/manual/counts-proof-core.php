<?php
/**
 * Genuine core/private local connection; no installed WordPress bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.DB.DirectDatabaseQuery -- Approved exact owned local proof only.
if ( PHP_SAPI !== 'cli' || ! defined( 'ADB_COUNTS_PROOF' ) ) {
	exit( 1 );
}
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_ADMIN', true );
define( 'WP_DEBUG', false );
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
define( 'WP_PLUGIN_URL', 'http://plugin-tester.local/owned-count-plugins' );
define( 'WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins' );
define( 'WPMU_PLUGIN_URL', 'http://plugin-tester.local/owned-count-mu' );
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
$wpdb->suppress_errors( true ); // Deliberate fixture-only SQL failures are asserted, not exposed.
if ( false === $wpdb->query( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=2, lock_wait_timeout=2' ) ) {
	throw new RuntimeException( 'Bounded owned session unavailable' );
}
$wpdb->set_prefix( $argv[2] );
$tables          = array(
	'snapshots' => $argv[2] . 'alynt_drime_dashboard_snapshots',
	'actions'   => $argv[2] . 'alynt_drime_dashboard_actions',
);
$queries         = 0;
$physical_faults = 0;
$http            = 0;
$trace           = array();
$faults          = array(
	'group'  => array(),
	'scalar' => array(),
);
$cleaning        = false;
add_filter(
	'query',
	function ( $sql ) use ( $tables, &$queries, &$physical_faults, &$trace, &$faults, &$cleaning ) {
		adb_counts_assert_sql_scope( $sql, $GLOBALS['argv'][2] );
		++$queries;
		if ( $queries > ( $cleaning ? 2020 : 2000 ) ) {
			throw new RuntimeException( 'Owned count proof query budget exceeded' );
		}
		if ( preg_match( '/^SELECT (?:dashboard_site_id, COUNT\(\*\) AS row_count|COUNT\(\*\)) FROM ([A-Za-z0-9_]+) WHERE dashboard_site_id /', $sql, $match ) ) {
			$kind          = $match[1] === $tables['snapshots'] ? 'snapshots' : ( false !== strpos( $sql, 'state IN' ) ? 'non_terminal' : 'actions' );
			$mode          = false !== strpos( $sql, 'GROUP BY' ) ? 'group' : 'scalar';
			$key           = $kind . '_' . $mode;
			$trace[ $key ] = ( $trace[ $key ] ?? 0 ) + 1;
			if ( ! empty( $faults[ $mode ][ $kind ] ) ) {
				--$faults[ $mode ][ $kind ];
				++$physical_faults;
				return $sql . ' ADB_OWNED_COUNTS_SYNTAX_FAILURE';
			}
		}
		return $sql;
	}
);
add_filter(
	'pre_http_request',
	function () use ( &$http ) {
		++$http;
		throw new RuntimeException( 'HTTP outside approved count proof' );
	}
);
require_once dirname( __DIR__, 2 ) . '/alynt-drime-backups-dashboard.php';
