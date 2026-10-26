<?php
/**
 * Genuine installed core libraries in a private owned lifecycle process.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( PHP_SAPI !== 'cli' || ! defined( 'ADB_LIFECYCLE_PROOF' ) ) {
	exit( 1 );
}
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_ADMIN', true );
define( 'WP_DEBUG', false );
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'WP_PLUGIN_DIR', $argv[4] );
define( 'WP_PLUGIN_URL', 'http://plugin-tester.local/owned-lifecycle-plugins' );
define( 'WP_CONTENT_URL', 'http://plugin-tester.local/wp-content' );
define( 'WPMU_PLUGIN_DIR', WP_PLUGIN_DIR . '/absent-mu-plugins' );
define( 'WPMU_PLUGIN_URL', WP_PLUGIN_URL . '/absent-mu-plugins' );
define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'MULTISITE', false );
define( 'SAVEQUERIES', false );
if ( file_exists( WP_CONTENT_DIR . '/install.php' ) ) {
	throw new RuntimeException( 'Custom install code is outside this proof' );
}
foreach ( array( 'version.php', 'compat-utf8.php', 'compat.php', 'load.php', 'plugin.php', 'formatting.php', 'functions.php', 'class-wp-error.php', 'cache.php', 'class-wpdb.php', 'pomo/translations.php', 'l10n.php', 'theme.php', 'class-wp-walker.php', 'link-template.php', 'cron.php', 'class-wp-plugin-dependencies.php' ) as $file ) {
	require_once ABSPATH . WPINC . '/' . $file;
}
wp_cache_init();
require_once ABSPATH . WPINC . '/default-constants.php';
wp_initial_constants();
$wp_plugin_paths = array(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Same empty initial core registry as wp-settings, private non-symlinked runtime copy.
// Genuine empty loaded catalogs avoid unrelated locale/authentication discovery.
foreach ( array( 'default', 'alynt-drime-backups-dashboard' ) as $proof_domain ) {
	$l10n[ $proof_domain ] = new Translations(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Private genuine empty catalogs, not application locale/authentication proof.
}
$wpdb = new wpdb( 'root', getenv( 'MYSQL_PWD' ), 'local', '127.0.0.1:10004' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Private approved fixture connection, never application global state.
if ( ! $wpdb->ready ) {
	throw new RuntimeException( 'Confirmed local database unavailable' );
}
$wpdb->hide_errors();
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
foreach ( array( 'error-protection.php', 'class-wp-recovery-mode-cookie-service.php', 'class-wp-recovery-mode-key-service.php', 'class-wp-recovery-mode-link-service.php', 'class-wp-recovery-mode-email-service.php', 'class-wp-recovery-mode.php', 'kses.php', 'shortcodes.php' ) as $file ) {
	require_once ABSPATH . WPINC . '/' . $file;
}
add_filter(
	'pre_http_request',
	function () {
		throw new RuntimeException( 'Networking is outside this proof' );
	}
);
