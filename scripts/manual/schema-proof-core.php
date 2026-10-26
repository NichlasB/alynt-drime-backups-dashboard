<?php
/**
 * Installed core library composition for the explicitly approved manual proof.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( PHP_SAPI !== 'cli' || ! defined( 'ADB_SCHEMA_PROOF' ) ) {
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
if ( file_exists( WP_CONTENT_DIR . '/install.php' ) ) {
	throw new RuntimeException( 'Custom installation code is outside this proof' );
}
foreach ( array( 'version.php', 'compat-utf8.php', 'compat.php', 'load.php', 'plugin.php', 'formatting.php', 'functions.php', 'class-wp-error.php', 'cache.php', 'class-wpdb.php', 'pomo/translations.php', 'l10n.php', 'theme.php', 'class-wp-walker.php' ) as $file ) {
	require_once ABSPATH . WPINC . '/' . $file;
}
// In-memory core cache only; no drop-ins, wp-config, wp-load or application hooks.
wp_cache_init();
$l10n['alynt-drime-backups-dashboard'] = new NOOP_Translations(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Standalone CLI core-library composition, never an application request.
$wpdb                                  = new wpdb( 'root', getenv( 'MYSQL_PWD' ), 'local', '127.0.0.1:10004' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Fresh private connection for approved owned fixtures, never existing global state.
if ( ! $wpdb->ready ) {
	throw new RuntimeException( 'Confirmed local database is unavailable' );
}
$wpdb->hide_errors();
$wpdb->suppress_errors( true );
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-storage.php';
