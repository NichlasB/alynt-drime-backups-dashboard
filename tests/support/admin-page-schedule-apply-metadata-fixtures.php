<?php
/**
 * Admin schedule-apply metadata fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule-apply metadata fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Apply_Metadata_Fixtures {
	/**
	 * Builds rollback metadata for a successful Schedule Apply row.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_apply_history_rollback_metadata() {
		return array(
			'captured'                      => true,
			'schedule_id'                   => 'alynt_scan_upload',
			'rollback_metadata_fingerprint' => str_repeat( 'b', 64 ),
		);
	}

	/**
	 * Builds rollback metadata for Schedule Apply history detail rendering.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_apply_detail_rollback_metadata() {
		return array(
			'captured'  => true,
			'available' => false,
			'reason'    => 'schedule_rollback_runtime_not_implemented',
			'expires_at' => '2026-09-15T19:24:12+00:00',
		);
	}
}
