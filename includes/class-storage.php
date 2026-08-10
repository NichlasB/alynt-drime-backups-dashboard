<?php
/**
 * Dashboard storage schema.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns local dashboard tables.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Storage {
	const SCHEMA_VERSION        = '2';
	const OPTION_SCHEMA_VERSION = 'alynt_drime_backups_dashboard_schema_version';

	/**
	 * Returns the sites table name.
	 *
	 * @return string
	 */
	public static function sites_table() {
		global $wpdb;

		return $wpdb->prefix . 'alynt_drime_dashboard_sites';
	}

	/**
	 * Returns the snapshots table name.
	 *
	 * @return string
	 */
	public static function snapshots_table() {
		global $wpdb;

		return $wpdb->prefix . 'alynt_drime_dashboard_snapshots';
	}

	/**
	 * Installs or upgrades dashboard-owned tables.
	 *
	 * @return void
	 */
	public static function maybe_install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$sites_table     = self::sites_table();
		$snapshots_table = self::snapshots_table();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta(
			"CREATE TABLE {$sites_table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				public_id char(36) NOT NULL,
				site_uuid char(36) NULL,
				site_label varchar(191) NOT NULL DEFAULT '',
				expected_origin varchar(255) NOT NULL DEFAULT '',
				environment varchar(32) NOT NULL DEFAULT 'production',
				enrollment_status varchar(32) NOT NULL DEFAULT 'pending',
				pairing_secret_hash varchar(255) NULL,
				pairing_expires_at datetime NULL,
				polling_key_id varchar(64) NULL,
				polling_secret_ciphertext longtext NULL,
				plugin_version varchar(64) NULL,
				payload_schema_version smallint(5) unsigned NULL,
				overall_status varchar(32) NOT NULL DEFAULT 'pending',
				last_poll_attempt_at datetime NULL,
				last_seen_at datetime NULL,
				next_poll_at datetime NULL,
				consecutive_failures int(10) unsigned NOT NULL DEFAULT 0,
				last_error_code varchar(64) NULL,
				last_error_summary text NULL,
				paused_at datetime NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY public_id (public_id),
				UNIQUE KEY site_uuid (site_uuid),
				KEY expected_origin (expected_origin(191)),
				KEY enrollment_status (enrollment_status),
				KEY next_poll_at (next_poll_at),
				KEY poll_due (enrollment_status, paused_at, next_poll_at, id),
				KEY last_seen_at (last_seen_at)
			) {$charset_collate};"
		);

		dbDelta(
			"CREATE TABLE {$snapshots_table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				dashboard_site_id bigint(20) unsigned NOT NULL,
				schema_version smallint(5) unsigned NOT NULL DEFAULT 1,
				observed_at datetime NOT NULL,
				payload_fingerprint char(64) NOT NULL DEFAULT '',
				overall_status varchar(32) NOT NULL DEFAULT '',
				queue_count int(10) unsigned NOT NULL DEFAULT 0,
				uploaded_count int(10) unsigned NOT NULL DEFAULT 0,
				failed_count int(10) unsigned NOT NULL DEFAULT 0,
				active_upload tinyint(1) NOT NULL DEFAULT 0,
				warning_count int(10) unsigned NOT NULL DEFAULT 0,
				cron_status varchar(64) NOT NULL DEFAULT '',
				payload_json longtext NOT NULL,
				PRIMARY KEY  (id),
				KEY site_observed (dashboard_site_id, observed_at),
				KEY site_fingerprint (dashboard_site_id, payload_fingerprint),
				KEY site_latest (dashboard_site_id, id),
				KEY retention_cleanup (observed_at, id),
				KEY overall_status (overall_status)
			) {$charset_collate};"
		);

		update_option( self::OPTION_SCHEMA_VERSION, self::SCHEMA_VERSION, false );
	}

	/**
	 * Ensures installed schemas receive safe dbDelta upgrades after plugin updates.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		$stored_version = function_exists( 'get_option' ) ? (string) get_option( self::OPTION_SCHEMA_VERSION, '' ) : '';

		if ( self::SCHEMA_VERSION === $stored_version ) {
			return;
		}

		self::maybe_install();
	}
}
