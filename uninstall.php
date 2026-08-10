<?php
/**
 * Uninstall handler.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

wp_clear_scheduled_hook( 'alynt_drime_backups_dashboard_poll_sites' );
wp_clear_scheduled_hook( 'alynt_drime_backups_dashboard_cleanup_snapshots' );
delete_transient( 'alynt_drime_backups_dashboard_poll_sites_lock' );

$tables = array(
	$wpdb->prefix . 'alynt_drime_dashboard_snapshots',
	$wpdb->prefix . 'alynt_drime_dashboard_sites',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

delete_option( 'alynt_drime_backups_dashboard_schema_version' );
