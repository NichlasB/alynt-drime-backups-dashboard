<?php
/**
 * Admin diagnostics support-output rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-diagnostics-support-output.php';

if ( ! function_exists( 'esc_textarea' ) ) {
	/**
	 * Escapes textarea values for focused rendering tests.
	 *
	 * @param string $text Text value.
	 * @return string
	 */
	function esc_textarea( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

/**
 * Tests Diagnostics support-copy rendering.
 */
class AdminPageDiagnosticsSupportOutputTest extends TestCase {
	/**
	 * Support copy offers copy and download controls for the redacted JSON.
	 *
	 * @return void
	 */
	public function test_support_copy_renders_download_control_for_redacted_summary() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Output_Test_Harness();
		$html    = $harness->support_copy_html(
			array(
				'generated_at' => '2026-10-05 12:00:00',
				'counts'       => array(
					'polling_ready' => 14,
				),
			)
		);

		$this->assertStringContainsString( 'Support Copy', $html );
		$this->assertStringContainsString( 'remote-action boundary evidence', $html );
		$this->assertStringContainsString( 'does not grant new client actions', $html );
		$this->assertStringContainsString( 'store Drime API credentials', $html );
		$this->assertStringContainsString( 'id="adbd-support-copy"', $html );
		$this->assertStringContainsString( 'Copy Support Summary', $html );
		$this->assertStringContainsString( 'Download Support Summary', $html );
		$this->assertStringContainsString( 'class="button adbd-download-button"', $html );
		$this->assertStringContainsString( 'data-download-target="adbd-support-copy"', $html );
		$this->assertStringContainsString( 'data-download-filename="alynt-drime-dashboard-support-summary.json"', $html );
		$this->assertStringContainsString( 'Redacted support summary downloaded.', $html );
		$this->assertStringContainsString( 'Use Copy Support Summary instead.', $html );
		$this->assertStringContainsString( 'aria-live="polite"', $html );
	}
}

/**
 * Harness exposing support-copy output.
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Output_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Support_Output;

	/**
	 * Exposes support-copy markup.
	 *
	 * @param array<string,mixed> $support Support diagnostics.
	 * @return string
	 */
	public function support_copy_html( array $support ) {
		ob_start();
		$this->render_support_copy_output( $support );
		return (string) ob_get_clean();
	}
}
