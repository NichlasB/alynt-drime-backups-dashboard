<?php
/**
 * Remote action schedule apply latest-action alias fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-capabilities-schedule-apply-rollback-metadata-fixtures.php';

/**
 * Shared schedule apply latest-action alias fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedule_Apply_Alias_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedule_Apply_Rollback_Metadata_Fixtures;

	/**
	 * Builds a schedule-apply latest-action summary with next-run aliases.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_apply_alias_summary() {
		return array(
			'protocol_version' => 2,
			'enabled'          => true,
			'last_action'      => array(
				'action_id'      => '11111111-1111-4111-8111-111111111111',
				'action_type'    => 'schedule_apply',
				'state'          => 'succeeded',
				'code'           => 'schedule_apply_succeeded',
				'summary'        => 'Schedule apply completed for Alynt scan/upload.',
				'schedule_apply' => array(
					'schedule_id'          => 'alynt_scan_upload',
					'capability_version'   => 1,
					'preview_action_id'    => '22222222-2222-4222-8222-222222222222',
					'preview_fingerprint'  => str_repeat( 'a', 64 ),
					'previous_cadence'     => 'every_15_minutes',
					'applied_cadence'      => 'every_30_minutes',
					'previous_next_run_at' => '2026-09-15T18:30:03+00:00',
					'applied_next_run_at'  => '2026-09-15T18:53:55+00:00',
					'changed'              => true,
					'rollback_available'   => true,
					'rollback_metadata'    => $this->schedule_apply_rollback_metadata(),
				),
			),
		);
	}
}
