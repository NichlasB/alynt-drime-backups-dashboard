<?php
/**
 * Admin restore-readiness rendering test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-restore-readiness-evidence.php';

/**
 * Harness exposing private restore-readiness rendering helpers for focused tests.
 */
class Alynt_Drime_Backups_Dashboard_Restore_Readiness_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Restore_Readiness_Evidence;

	/**
	 * Exposes restore-readiness panel markup.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return string
	 */
	public function panel_html( array $snapshot ) {
		ob_start();
		$this->render_restore_readiness_panel( $snapshot );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes Sites-row restore-readiness hint markup.
	 *
	 * @param array<string,mixed> $payload Latest decoded payload.
	 * @return string
	 */
	public function row_hint_html( array $payload ) {
		return $this->restore_readiness_row_hint( $payload );
	}

	/**
	 * Decodes a snapshot payload.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return array<string,mixed>
	 */
	private function decoded_snapshot_payload( array $snapshot ) {
		$decoded = isset( $snapshot['decoded_payload'] ) && is_array( $snapshot['decoded_payload'] ) ? $snapshot['decoded_payload'] : json_decode( isset( $snapshot['payload_json'] ) ? (string) $snapshot['payload_json'] : '', true );

		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Minimal detail-list renderer used by the detail helper.
	 *
	 * @param string $label Label.
	 * @param string $value Value.
	 * @param bool   $raw Whether value is pre-escaped markup.
	 * @return void
	 */
	private function render_detail_item( $label, $value, $raw = false ) {
		echo '<dt>' . esc_html( $label ) . '</dt><dd>';
		echo $raw ? $value : esc_html( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Test harness preserves helper-provided markup when requested.
		echo '</dd>';
	}
}
