<?php
/**
 * Dashboard site repository local-state write helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.59
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writes dashboard-local archive and polling state.
 *
 * @since 0.1.59
 */
trait Alynt_Drime_Backups_Dashboard_Site_Repository_Local_State_Writes {
	/**
	 * Archives a non-polling dashboard-owned record locally.
	 *
	 * @since 0.1.37
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function archive_local( $site_id ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Dashboard-owned repository write; no cache layer.
		$updated = $wpdb->update(
			$table,
			array(
				'archived_at' => $now,
				'updated_at'  => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return $this->update_changed_existing_row( $updated );
	}

	/**
	 * Restores a locally archived dashboard-owned record to visible local history.
	 *
	 * @since 0.1.37
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function unarchive_local( $site_id ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Dashboard-owned repository write; no cache layer.
		$updated = $wpdb->update(
			$table,
			array(
				'archived_at' => null,
				'updated_at'  => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return $this->update_changed_existing_row( $updated );
	}

	/**
	 * Pauses scheduled polling for one dashboard-owned site record.
	 *
	 * @since 0.1.27
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function pause_polling( $site_id ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Dashboard-owned repository write; no cache layer.
		$updated = $wpdb->update(
			$table,
			array(
				'paused_at'    => $now,
				'next_poll_at' => null,
				'updated_at'   => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		return $this->update_changed_existing_row( $updated );
	}

	/**
	 * Resumes scheduled polling for one dashboard-owned site record.
	 *
	 * @since 0.1.27
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function resume_polling( $site_id ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Dashboard-owned repository write; no cache layer.
		$updated = $wpdb->update(
			$table,
			array(
				'paused_at'    => null,
				'next_poll_at' => $now,
				'updated_at'   => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		return $this->update_changed_existing_row( $updated );
	}
}
