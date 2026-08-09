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
	const SCHEMA_VERSION        = '1';
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
				site_uuid char(36) NOT NULL DEFAULT '',
				site_url varchar(255) NOT NULL DEFAULT '',
				display_name varchar(191) NOT NULL DEFAULT '',
				status varchar(40) NOT NULL DEFAULT 'pending',
				polling_secret_enc longtext NULL,
				polling_secret_hint varchar(32) NOT NULL DEFAULT '',
				last_polled_at datetime NULL,
				last_seen_at datetime NULL,
				last_error text NULL,
				paused_at datetime NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY site_uuid (site_uuid),
				KEY site_url (site_url(191)),
				KEY status (status),
				KEY last_seen_at (last_seen_at)
			) {$charset_collate};"
		);

		dbDelta(
			"CREATE TABLE {$snapshots_table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				site_id bigint(20) unsigned NOT NULL,
				schema_version smallint(5) unsigned NOT NULL DEFAULT 1,
				status_category varchar(40) NOT NULL DEFAULT '',
				payload_hash char(64) NOT NULL DEFAULT '',
				status_payload longtext NOT NULL,
				captured_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY site_id (site_id),
				KEY status_category (status_category),
				KEY captured_at (captured_at),
				KEY payload_hash (payload_hash)
			) {$charset_collate};"
		);

		update_option( self::OPTION_SCHEMA_VERSION, self::SCHEMA_VERSION, false );
	}
}
