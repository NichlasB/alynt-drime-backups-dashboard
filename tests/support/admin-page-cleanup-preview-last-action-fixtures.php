<?php
/**
 * Cleanup-preview last-action fixtures for admin rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides reusable cleanup-preview latest action summaries.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Last_Action_Fixtures {
	/**
	 * Builds a latest cleanup-preview action summary fixture.
	 *
	 * @param bool $include_unsafe_category Whether to include an unsafe category for filtering assertions.
	 * @return array<string,mixed>
	 */
	private function cleanup_preview_last_action_summary( $include_unsafe_category = true ) {
		return array(
			'action_id'       => '44444444-4444-4444-8444-444444444444',
			'action_type'     => 'cleanup_preview',
			'state'           => 'succeeded',
			'code'            => 'cleanup_preview_ready',
			'summary'         => 'Cleanup preview is ready. Nothing was deleted.',
			'cleanup_preview' => $this->cleanup_preview_evidence( $include_unsafe_category ),
		);
	}
}
