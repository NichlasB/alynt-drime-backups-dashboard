<?php
/**
 * Helper stubs for admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides minimal helper methods required by composed admin rendering traits.
 */
trait Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Helper_Stubs {
	/**
	 * Minimal snapshot decoder needed by the included helper trait.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return array<string,mixed>
	 */
	private function decoded_snapshot_payload( array $snapshot ) {
		return isset( $snapshot['decoded_payload'] ) && is_array( $snapshot['decoded_payload'] ) ? $snapshot['decoded_payload'] : array();
	}

	/**
	 * Minimal backup source detail renderer needed by the included helper trait.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return void
	 */
	private function render_backup_sources_detail( array $payload ) {
		unset( $payload );
	}
}
