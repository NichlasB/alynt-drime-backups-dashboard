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

$tables = array(
	$wpdb->prefix . 'alynt_drime_dashboard_snapshots',
	$wpdb->prefix . 'alynt_drime_dashboard_sites',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

delete_option( 'alynt_drime_backups_dashboard_schema_version' );
