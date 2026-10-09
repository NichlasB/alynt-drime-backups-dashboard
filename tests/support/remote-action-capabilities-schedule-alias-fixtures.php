<?php
/**
 * Remote action schedule latest-action alias fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule latest-action alias fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedule_Alias_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedule_Apply_Alias_Fixtures;

	/**
	 * Builds a schedule-preview latest-action summary with code aliases.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_preview_alias_summary() {
		return array(
			'protocol_version' => 2,
			'enabled'          => true,
			'last_action'      => array(
				'action_id'        => '11111111-1111-4111-8111-111111111111',
				'action_type'      => 'schedule_preview',
				'state'            => 'succeeded',
				'code'             => 'schedule_preview_ready',
				'summary'          => 'Schedule preview is ready. No schedule was changed.',
				'schedule_preview' => array(
					'schedule_id'      => 'alynt_scan_upload',
					'current_cadence'  => 'every_15_minutes',
					'proposed_cadence' => 'every_30_minutes',
					'would_change'     => true,
				),
			),
		);
	}

}
