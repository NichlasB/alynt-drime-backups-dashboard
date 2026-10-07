<?php
/**
 * Schedule lookup helpers for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides fixed schedule lookup responses for dispatcher tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Schedule_Lookups {
	/**
	 * Returns a fixed fresh preview for apply.
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $preview_public_id Preview public ID.
	 * @param array<string,mixed> $capabilities Capabilities.
	 * @param string|null         $now Now.
	 * @return array<string,mixed>|WP_Error
	 */
	public function fresh_schedule_preview_for_apply( $site_id, $preview_public_id, array $capabilities, $now = null ) {
		unset( $site_id, $capabilities, $now );

		if ( '22222222-2222-4222-8222-222222222222' !== $preview_public_id ) {
			return new WP_Error( 'schedule_apply_preview_missing', 'Missing preview.' );
		}

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
	 * Returns a fixed successful apply for rollback preview.
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
