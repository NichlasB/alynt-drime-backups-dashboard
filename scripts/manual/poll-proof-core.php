<?php
/**
 * Genuine installed core libraries and private connection, no WordPress bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited -- Standalone explicitly approved CLI composition, not an application request.
if ( PHP_SAPI !== 'cli' || ! defined( 'ADB_POLL_PROOF' ) ) {
	exit( 1 );
}
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_ADMIN', true );
define( 'WP_DEBUG', false );
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'MULTISITE', false );
define( 'SAVEQUERIES', false );
foreach ( array( 'version.php', 'compat-utf8.php', 'compat.php', 'load.php', 'plugin.php', 'utf8.php', 'formatting.php', 'functions.php', 'class-wp-error.php', 'cache.php', 'class-wpdb.php', 'pomo/translations.php', 'l10n.php' ) as $file ) {
	require_once ABSPATH . WPINC . '/' . $file;
}
wp_cache_init();
// Genuine core in-memory cache supplies the fixed fixture charset, not application options.
wp_cache_add( 'alloptions', array( 'blog_charset' => 'UTF-8' ), 'options' );
foreach ( array( 'default', 'alynt-drime-backups-dashboard' ) as $domain ) {
	$l10n[ $domain ] = new Translations();
}
$wpdb = new wpdb( 'root', getenv( 'MYSQL_PWD' ), 'local', '127.0.0.1:10004' );
if ( ! $wpdb->ready ) {
	throw new RuntimeException( 'Confirmed local database unavailable' );
}
$wpdb->hide_errors();
// Intentional server faults are asserted through false/last_error, not raw SQL logs.
$wpdb->suppress_errors( true );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Private session budgets, not application data or a cached request read.
if ( false === $wpdb->query( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=2, lock_wait_timeout=2' ) ) {
	throw new RuntimeException( 'Could not establish bounded owned session' );
}
$wpdb->set_prefix( $argv[2] );
foreach ( array( 'class-storage.php', 'traits/trait-site-repository-reads.php', 'traits/trait-site-repository-local-state-writes.php', 'traits/trait-site-repository-writes.php', 'traits/trait-site-repository-runtime-writes.php', 'class-site-repository.php' ) as $file ) {
	require_once dirname( __DIR__, 2 ) . '/includes/' . $file;
}
$table       = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
$queries     = 0;
$reads       = 0;
$fault       = '';
$stage_path  = '';
$before_read = null;
add_filter(
	'query',
	function ( $sql ) use ( $table, &$queries, &$reads, &$fault, &$stage_path, &$before_read ) {
		adb_poll_assert_sql_scope( $sql, $table );
		if ( $stage_path ) {
			file_put_contents( $stage_path, json_encode( array( 'stage' => strtok( $sql, ' ' ) ) ), LOCK_EX );
		}
		++$queries;
		if ( 0 === strpos( $sql, 'SELECT 1 FROM ' ) ) {
			++$reads;
			if ( $before_read ) {
				$callback    = $before_read;
				$before_read = null;
				$callback();
			}
			if ( 'read' === $fault ) {
				return 'SELECT ADB_OWNED_INVALID_COLUMN FROM ' . $table;
			}
		}
		if ( 'write' === $fault && 0 === strpos( $sql, 'UPDATE ' ) ) {
			return $sql . ' ADB_OWNED_INVALID_SYNTAX';
		}
		return $sql;
	}
);
