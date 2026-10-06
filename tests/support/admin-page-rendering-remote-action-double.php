<?php
/**
 * Admin rendering remote-action repository test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Admin rendering remote action repository test double.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Actions extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	/**
	 * Returns fixed rollback-preview readiness data.
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $apply_public_id Apply public ID.
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
			'previous_cadence'              => 'every_15_minutes',
			'applied_cadence'               => 'every_30_minutes',
			'capability_version'            => 1,
			'metadata_expires_at'           => '2099-01-01T00:15:00+00:00',
		);
	}
}
