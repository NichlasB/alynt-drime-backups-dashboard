<?php
/**
 * Admin restore-readiness evidence rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-restore-readiness-evidence.php';

/**
 * Tests restore-readiness evidence rendering helpers.
 */
class AdminPageRestoreReadinessEvidenceTest extends TestCase {
	/**
	 * Site Detail renders read-only restore-readiness evidence and no restore control.
	 *
	 * @return void
	 */
	public function test_restore_readiness_panel_is_evidence_only() {
		$harness = new Alynt_Drime_Backups_Dashboard_Restore_Readiness_Test_Harness();
		$html    = $harness->panel_html(
			array(
				'payload_json' => wp_json_encode(
					array(
						'restore_readiness' => array(
							'generated_at'  => '2026-10-02T12:00:00Z',
							'overall_state' => 'evidence_available',
							'candidates'    => array(
								array(
									'source'                    => 'server',
									'candidate_ref'             => 'opaque-client-ref',
									'latest_backup_finished_at' => '2026-10-02T01:30:00Z',
									'component_state'           => 'complete',
									'checksum_state'            => 'verified',
									'manifest_state'            => 'compatible',
									'sidecar_state'             => 'present',
									'warnings'                  => array(),
								),
								array(
									'source'          => 'wpvivid',
									'component_state' => 'unknown',
									'checksum_state'  => 'not_reported',
									'manifest_state'  => 'not_reported',
									'sidecar_state'   => 'not_reported',
									'warnings'        => array( 'restore_evidence_incomplete' ),
								),
							),
						),
					)
				),
			)
		);

		$this->assertStringContainsString( 'Restore Readiness Evidence', $html );
		$this->assertStringContainsString( 'The dashboard cannot restore this site', $html );
		$this->assertStringContainsString( 'Server runner', $html );
		$this->assertStringContainsString( 'WPvivid', $html );
		$this->assertStringContainsString( 'Evidence available', $html );
		$this->assertStringContainsString( 'Not verified', $html );
		$this->assertStringContainsString( 'Opaque client reference reported', $html );
		$this->assertStringContainsString( 'restore_evidence_incomplete', $html );
		$this->assertStringNotContainsString( 'opaque-client-ref', $html );
		$this->assertStringNotContainsString( '<button', $html );
		$this->assertStringNotContainsString( 'Restore Now', $html );
	}

	/**
	 * Missing restore-readiness evidence renders no panel.
	 *
	 * @return void
	 */
	public function test_missing_restore_readiness_renders_no_panel() {
		$harness = new Alynt_Drime_Backups_Dashboard_Restore_Readiness_Test_Harness();
		$html    = $harness->panel_html(
			array(
				'payload_json' => wp_json_encode(
					array(
						'schema_version' => 1,
					)
				),
			)
		);

		$this->assertSame( '', $html );
	}
}

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
