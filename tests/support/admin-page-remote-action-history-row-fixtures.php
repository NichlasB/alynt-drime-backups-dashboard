<?php
/**
 * Remote-action history row factory fixtures for admin rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides reusable V2 remote-action history row factories.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_History_Row_Fixtures {
	/**
	 * Builds a remote-action history row fixture.
	 *
	 * @param array<string,mixed> $overrides Row overrides.
	 * @param array<string,mixed> $context Redacted context payload.
	 * @return array<string,mixed>
	 */
	private function history_row( array $overrides, array $context ) {
		return array_merge(
			array(
				'action_type'           => 'scan_upload_now',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 18:00:00',
				'client_result_summary' => '',
				'redacted_context_json' => wp_json_encode( $context ),
			),
			$overrides
		);
	}
}
