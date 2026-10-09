<?php
/**
 * Remote action repository schedule test fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-repository-schedule-apply-capabilities.php';

/**
 * Shared remote action repository schedule fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Schedule_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Schedule_Apply_Capabilities;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Schedule_Rollback_Fixtures;

	/**
	 * Returns an expired schedule-preview row.
	 *
	 * @param string $preview_id Preview action public ID.
	 * @return array<string,mixed>
	 */
	private function expired_schedule_preview_row( $preview_id ) {
		return array(
			'id'                    => 321,
			'public_id'             => $preview_id,
			'dashboard_site_id'     => 44,
			'action_type'           => 'schedule_preview',
			'state'                 => 'succeeded',
			'completed_at'          => '2026-08-20 12:00:00',
			'redacted_context_json' => wp_json_encode(
				array(
					'schedule_preview' => array(
						'preview_action_id'    => $preview_id,
						'preview_fingerprint'  => str_repeat( 'a', 64 ),
						'schedule_id'          => 'alynt_scan_upload',
						'current_cadence'      => 'every_15_minutes',
						'proposed_cadence'     => 'every_30_minutes',
						'capability_version'   => 1,
						'preview_expires_at'   => '2026-08-20T12:05:00+00:00',
						'would_change'         => true,
						'apply_supported'      => true,
						'rollback_supported'   => false,
					),
				)
			),
		);
	}
}
