<?php
/**
 * Uninstall handler.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Rollback copies must live outside the plugins directory. This guard is a
 * second line of defence: if WordPress discovers and uninstalls a copied
 * plugin directory, it must not alter the canonical dashboard's shared data.
 */
if ( 'alynt-drime-backups-dashboard' !== basename( __DIR__ ) ) {
	return;
}

global $wpdb;

wp_clear_scheduled_hook( 'alynt_drime_backups_dashboard_poll_sites' );
wp_clear_scheduled_hook( 'alynt_drime_backups_dashboard_cleanup_snapshots' );
delete_transient( 'alynt_drime_backups_dashboard_poll_sites_lock' );

$site_lock_prefix       = 'alynt_drime_backups_dashboard_poll_site_lock_';
$site_lock_like         = $wpdb->esc_like( '_transient_' . $site_lock_prefix ) . '%';
$site_lock_timeout_like = $wpdb->esc_like( '_transient_timeout_' . $site_lock_prefix ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$site_lock_like,
		$site_lock_timeout_like
	)
);

$enrollment_rate_limit_prefix       = 'alynt_drime_backups_dashboard_enroll_fail_';
$enrollment_rate_limit_like         = $wpdb->esc_like( '_transient_' . $enrollment_rate_limit_prefix ) . '%';
$enrollment_rate_limit_timeout_like = $wpdb->esc_like( '_transient_timeout_' . $enrollment_rate_limit_prefix ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$enrollment_rate_limit_like,
		$enrollment_rate_limit_timeout_like
	)
);

/*
 * Preserve dashboard records by default. Pairings include dashboard-held
 * polling credentials, so an ordinary WordPress plugin deletion must not
 * silently destroy recoverable monitoring state. A deliberate permanent
 * purge requires an operator-controlled wp-config.php constant.
 */
if ( ! defined( 'ALYNT_DRIME_BACKUPS_DASHBOARD_PURGE_DATA_ON_UNINSTALL' ) || true !== ALYNT_DRIME_BACKUPS_DASHBOARD_PURGE_DATA_ON_UNINSTALL ) {
	return;
}

$tables = array(
	$wpdb->prefix . 'alynt_drime_dashboard_actions',
	$wpdb->prefix . 'alynt_drime_dashboard_snapshots',
	$wpdb->prefix . 'alynt_drime_dashboard_sites',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

delete_option( 'alynt_drime_backups_dashboard_schema_version' );
delete_option( 'alynt_drime_backups_dashboard_diagnostics_settings' );
delete_option( 'alynt_drime_backups_dashboard_diagnostics_events' );
delete_option( 'alynt_drime_backups_dashboard_audit_events' );
