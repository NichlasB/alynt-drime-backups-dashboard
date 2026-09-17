<?php
/**
 * Admin diagnostics rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-settings.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-reporting.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-basic-detail-helpers.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-diagnostics-event-log.php';

/**
 * Tests diagnostics action-history rendering.
 */
class AdminPageDiagnosticsRenderingTest extends TestCase {
	/**
	 * Empty action history copy names the local polling controls.
	 *
	 * @return void
	 */
	public function test_empty_audit_history_copy_mentions_polling_pause_resume() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Rendering_Test_Harness();
		$html    = $harness->audit_history_html(
			array(
				'summary' => array(
					'total'          => 0,
					'last_action_at' => '',
				),
				'events'  => array(),
			)
		);

		$this->assertStringContainsString( 'pauses or resumes scheduled polling', $html );
	}

	/**
	 * Stored action slugs render as operator-friendly labels.
	 *
	 * @return void
	 */
	public function test_audit_history_renders_polling_actions_as_operator_labels() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Rendering_Test_Harness();
		$html    = $harness->audit_history_html(
			array(
				'summary' => array(
					'total'          => 2,
					'last_action_at' => '2026-09-17 15:00:00',
				),
				'events'  => array(
					array(
						'timestamp' => '2026-09-17 15:00:00',
						'actor_id'  => 12,
						'action'    => 'pause_polling',
						'outcome'   => 'succeeded',
						'context'   => array(
							'dashboard_site_id' => 7,
						),
					),
					array(
						'timestamp' => '2026-09-17 15:05:00',
						'actor_id'  => 12,
						'action'    => 'resume_polling',
						'outcome'   => 'succeeded',
						'context'   => array(
							'dashboard_site_id' => 7,
						),
					),
				),
			)
		);

		$this->assertStringContainsString( 'Pause Polling', $html );
		$this->assertStringContainsString( 'Resume Polling', $html );
		$this->assertStringNotContainsString( '>pause_polling<', $html );
		$this->assertStringNotContainsString( '>resume_polling<', $html );
	}
}

/**
 * Harness exposing private diagnostics rendering helpers.
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics_Rendering_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Event_Log;

	/**
	 * Exposes audit-history markup.
	 *
	 * @param array<string,mixed> $audit Audit diagnostics.
	 * @return string
	 */
	public function audit_history_html( array $audit ) {
		ob_start();
		$this->render_audit_history_diagnostics( $audit );
		return (string) ob_get_clean();
	}
}
