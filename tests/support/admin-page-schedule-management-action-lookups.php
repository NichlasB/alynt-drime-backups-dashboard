<?php
/**
 * Admin schedule-management action lookup double methods.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake schedule lookup responses for schedule-management rendering tests.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Management_Action_Lookups {
	/**
	 * Fresh preview response.
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $preview_public_id Preview ID.
	 * @param array<string,mixed> $capabilities Capabilities.
	 * @param string|null         $now Now.
	 * @return array<string,mixed>
	 */
	public function fresh_schedule_preview_for_apply( $site_id, $preview_public_id, array $capabilities, $now = null ) {
		unset( $site_id, $capabilities, $now );

		return array(
			'preview_action_id'   => $preview_public_id,
			'preview_fingerprint' => str_repeat( 'a', 64 ),
			'schedule_id'         => 'alynt_scan_upload',
			'current_cadence'     => 'every_15_minutes',
			'proposed_cadence'    => 'every_30_minutes',
			'capability_version'  => 1,
		);
	}

	/**
	 * Successful apply response for rollback-preview readiness tests.
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $apply_public_id Apply ID.
	 * @param array<string,mixed> $capabilities Capabilities.
	 * @param string|null         $now Now.
	 * @return array<string,mixed>|WP_Error
	 */
	public function successful_schedule_apply_for_rollback_preview( $site_id, $apply_public_id, array $capabilities, $now = null ) {
		unset( $site_id, $capabilities, $now );

		if ( '33333333-3333-4333-8333-333333333333' !== $apply_public_id ) {
			return new WP_Error( 'schedule_rollback_preview_apply_missing', 'Missing apply.' );
		}

		return array(
			'source_apply_action_id'         => $apply_public_id,
			'rollback_metadata_fingerprint' => str_repeat( 'b', 64 ),
			'schedule_id'                   => 'alynt_scan_upload',
			'applied_cadence'               => 'every_30_minutes',
			'previous_cadence'              => 'every_15_minutes',
			'capability_version'            => 1,
		);
	}
}
