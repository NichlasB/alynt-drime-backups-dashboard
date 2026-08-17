<?php
/**
 * Plugin Name:       Alynt Drime Backups Dashboard
 * Plugin URI:        https://alynt.com/
 * Description:       Read-only central monitoring dashboard for Alynt Drime backup uploader sites.
 * Version:           0.1.10
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Alynt
 * Author URI:        https://alynt.com/
 * GitHub Plugin URI: NichlasB/alynt-drime-backups-dashboard
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       alynt-drime-backups-dashboard
 * Domain Path:       /languages
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALYNT_DRIME_BACKUPS_DASHBOARD_VERSION', '0.1.10' );
define( 'ALYNT_DRIME_BACKUPS_DASHBOARD_MINIMUM_WP', '6.0' );
define( 'ALYNT_DRIME_BACKUPS_DASHBOARD_MINIMUM_PHP', '7.4' );
define( 'ALYNT_DRIME_BACKUPS_DASHBOARD_FILE', __FILE__ );
define( 'ALYNT_DRIME_BACKUPS_DASHBOARD_PATH', plugin_dir_path( __FILE__ ) );
define( 'ALYNT_DRIME_BACKUPS_DASHBOARD_URL', plugin_dir_url( __FILE__ ) );
define( 'ALYNT_DRIME_BACKUPS_DASHBOARD_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Returns whether the current environment can load the plugin safely.
 *
 * @return bool
 */
function alynt_drime_backups_dashboard_meets_requirements() {
	$wp_version = isset( $GLOBALS['wp_version'] ) ? (string) $GLOBALS['wp_version'] : ALYNT_DRIME_BACKUPS_DASHBOARD_MINIMUM_WP;

	return version_compare( PHP_VERSION, ALYNT_DRIME_BACKUPS_DASHBOARD_MINIMUM_PHP, '>=' )
		&& version_compare( $wp_version, ALYNT_DRIME_BACKUPS_DASHBOARD_MINIMUM_WP, '>=' );
}

/**
 * Renders a requirements notice when the plugin cannot load.
 *
 * @return void
 */
function alynt_drime_backups_dashboard_requirements_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: 1: minimum WordPress version, 2: minimum PHP version. */
				__( 'Alynt Drime Backups Dashboard requires WordPress %1$s or higher and PHP %2$s or higher.', 'alynt-drime-backups-dashboard' ),
				ALYNT_DRIME_BACKUPS_DASHBOARD_MINIMUM_WP,
				ALYNT_DRIME_BACKUPS_DASHBOARD_MINIMUM_PHP
			)
		)
	);
}

if ( ! alynt_drime_backups_dashboard_meets_requirements() ) {
	add_action( 'admin_notices', 'alynt_drime_backups_dashboard_requirements_notice' );
	return;
}

$alynt_drime_backups_dashboard_includes = array(
	'includes/class-storage.php',
	'includes/class-origin-validator.php',
	'includes/class-credential-vault.php',
	'includes/traits/trait-site-repository-reads.php',
	'includes/traits/trait-site-repository-writes.php',
	'includes/class-site-repository.php',
	'includes/class-snapshot-repository.php',
	'includes/class-status-classifier.php',
	'includes/class-event-log-redactor.php',
	'includes/traits/trait-event-log-storage.php',
	'includes/traits/trait-event-log-settings.php',
	'includes/class-event-log.php',
	'includes/traits/trait-diagnostics-scheduler.php',
	'includes/traits/trait-diagnostics-support.php',
	'includes/traits/trait-diagnostics-backup-source-metrics.php',
	'includes/traits/trait-diagnostics-site-metrics.php',
	'includes/class-diagnostics.php',
	'includes/class-status-payload-validator.php',
	'includes/class-pairing-tokens.php',
	'includes/class-enrollment-manager.php',
	'includes/traits/trait-enrollment-rest-responses.php',
	'includes/traits/trait-enrollment-rest-route-args.php',
	'includes/traits/trait-enrollment-rest-validation.php',
	'includes/traits/trait-enrollment-rest-rate-limits.php',
	'includes/class-enrollment-rest-controller.php',
	'includes/class-safe-transport.php',
	'includes/traits/trait-poller-scheduling.php',
	'includes/traits/trait-poller-locks.php',
	'includes/traits/trait-poller-status-check.php',
	'includes/class-poller.php',
	'includes/traits/trait-admin-page-actions.php',
	'includes/traits/trait-admin-page-diagnostics-overview.php',
	'includes/traits/trait-admin-page-diagnostics-settings.php',
	'includes/traits/trait-admin-page-diagnostics-event-log.php',
	'includes/traits/trait-admin-page-diagnostics-tables.php',
	'includes/traits/trait-admin-page-diagnostics-support-output.php',
	'includes/traits/trait-admin-page-diagnostics.php',
	'includes/traits/trait-admin-page-sites-list.php',
	'includes/traits/trait-admin-page-add-site.php',
	'includes/traits/trait-admin-page-attention.php',
	'includes/traits/trait-admin-page-site-detail.php',
	'includes/traits/trait-admin-page-sites.php',
	'includes/traits/trait-admin-page-basic-detail-helpers.php',
	'includes/traits/trait-admin-page-sites-table.php',
	'includes/traits/trait-admin-page-backup-source-evidence.php',
	'includes/traits/trait-admin-page-display-helpers.php',
	'includes/traits/trait-admin-page-status-formatters.php',
	'includes/traits/trait-admin-page-site-formatters.php',
	'includes/traits/trait-admin-page-time-formatters.php',
	'includes/traits/trait-admin-page-diagnostic-formatters.php',
	'includes/traits/trait-admin-page-formatters.php',
	'includes/class-admin-page.php',
	'includes/class-activator.php',
	'includes/class-deactivator.php',
	'includes/class-plugin.php',
);

foreach ( $alynt_drime_backups_dashboard_includes as $alynt_drime_backups_dashboard_include ) {
	require_once ALYNT_DRIME_BACKUPS_DASHBOARD_PATH . $alynt_drime_backups_dashboard_include;
}

register_activation_hook( __FILE__, array( 'Alynt_Drime_Backups_Dashboard_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Alynt_Drime_Backups_Dashboard_Deactivator', 'deactivate' ) );

/**
 * Loads the plugin text domain.
 *
 * @return void
 */
function alynt_drime_backups_dashboard_load_textdomain() {
	load_plugin_textdomain(
		'alynt-drime-backups-dashboard',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

add_action( 'init', 'alynt_drime_backups_dashboard_load_textdomain', 0 );

/**
 * Returns the plugin singleton.
 *
 * @return Alynt_Drime_Backups_Dashboard_Plugin
 */
function alynt_drime_backups_dashboard() {
	static $alynt_drime_backups_dashboard_plugin = null;

	if ( null === $alynt_drime_backups_dashboard_plugin ) {
		$alynt_drime_backups_dashboard_plugin = new Alynt_Drime_Backups_Dashboard_Plugin();
	}

	return $alynt_drime_backups_dashboard_plugin;
}

add_action( 'plugins_loaded', 'alynt_drime_backups_dashboard' );
