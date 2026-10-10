<?php
/**
 * Admin schedule-management history fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule-management history fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Management_History_Fixtures {
	/**
	 * Builds a successful Schedule Apply row with rollback-preview metadata.
	 *
	 * @param string $public_id Public action ID.
	 * @return array<string,mixed>
	 */
	private function successful_schedule_apply_history_row( $public_id = '33333333-3333-4333-8333-333333333333' ) {
		return array(
			'public_id'             => $public_id,
			'action_type'           => 'schedule_apply',
			'state'                 => 'succeeded',
			'redacted_context_json' => wp_json_encode(
				array(
					'schedule_apply' => array(
						'schedule_id'       => 'alynt_scan_upload',
						'previous_cadence'  => 'every_15_minutes',
						'applied_cadence'   => 'every_30_minutes',
						'rollback_metadata' => array(
							'captured'                      => true,
							'schedule_id'                   => 'alynt_scan_upload',
							'rollback_metadata_fingerprint' => str_repeat( 'b', 64 ),
						),
					),
				)
			),
		);
	}
}
