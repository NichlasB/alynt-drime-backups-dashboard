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
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-diagnostic-formatters.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-diagnostics-overview.php';
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

	/**
	 * Site-polling diagnostics explain non-polling record states.
	 *
	 * @return void
	 */
	public function test_overview_renders_record_state_polling_summary() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Harness();
		$html    = $harness->overview_html(
			array(
				'scheduler' => array(
					'poll_hook'             => 'alynt_drime_backups_dashboard_poll_sites',
					'poll_schedule_state'   => 'scheduled',
					'poll_next_at'          => '2026-09-18 12:15:00',
					'poll_interval_seconds' => 900,
					'poll_batch_size'       => 5,
					'stale_after_seconds'   => 3600,
					'global_lock_active'    => false,
					'current_utc'           => '2026-09-18 12:00:00',
					'cleanup_hook'          => 'alynt_drime_backups_dashboard_cleanup_snapshots',
					'cleanup_state'         => 'scheduled',
					'cleanup_next_at'       => '2026-09-19 00:00:00',
					'retention_days'        => 30,
					'cleanup_batch_size'    => 100,
				),
				'counts'    => array(
					'total_sites'         => 17,
					'polling_ready'       => 14,
					'not_polling'         => 3,
					'due_now'             => 6,
					'missing_credentials' => 0,
					'paused'              => 0,
					'with_failures'       => 0,
					'record_states'       => array(
						'active'              => 14,
						'awaiting_first_poll' => 0,
						'pending'             => 2,
						'revoked'             => 1,
						'archived'            => 0,
						'other'               => 0,
						'unknown'             => 0,
					),
				),
				'recent'    => array(),
				'logging'   => array(),
				'support'   => array(),
			)
		);

		$this->assertStringContainsString( 'Total dashboard records', $html );
		$this->assertStringContainsString( 'Records not currently polling', $html );
		$this->assertStringContainsString( 'Pending pairing records', $html );
		$this->assertStringContainsString( 'Locally revoked records', $html );
		$this->assertStringContainsString( 'Archived local records', $html );
		$this->assertStringContainsString( '<td>3</td>', $html );
	}

	/**
	 * Diagnostics overview shows generated-at evidence and a cache-busted refresh link.
	 *
	 * @return void
	 */
	public function test_overview_renders_diagnostics_freshness_notice() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Harness();
		$html    = $harness->overview_html(
			array(
				'scheduler' => array(
					'current_utc'           => '2026-09-19 10:53:22',
					'poll_hook'             => 'alynt_drime_backups_dashboard_poll_sites',
					'poll_schedule_state'   => 'scheduled',
					'poll_next_at'          => '2026-09-19 11:06:29',
					'poll_interval_seconds' => 900,
					'poll_batch_size'       => 20,
					'stale_after_seconds'   => 3600,
				),
				'counts'    => array(),
				'recent'    => array(),
				'logging'   => array(),
				'support'   => array(),
			)
		);

		$this->assertStringContainsString( 'Generated at 2026-09-19 10:53:22 UTC.', $html );
		$this->assertStringContainsString( 'Refresh diagnostics', $html );
		$this->assertStringContainsString( 'tab=diagnostics', $html );
		$this->assertStringContainsString( '_adbd_check=20260919105322', $html );
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

/**
 * Harness exposing the diagnostics overview shell.
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostic_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Overview;

	const MENU_SLUG = 'alynt-drime-backups-dashboard';

	/**
	 * Diagnostics service stub.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Service_Stub
	 */
	private $diagnostics;

	/**
	 * Exposes diagnostics overview markup.
	 *
	 * @param array<string,mixed> $diagnostics Diagnostics payload.
	 * @return string
	 */
	public function overview_html( array $diagnostics ) {
		$this->diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Service_Stub( $diagnostics );

		ob_start();
		$this->render_diagnostics_shell();
		return (string) ob_get_clean();
	}

	/**
	 * Stubs settings diagnostics for this focused overview test.
	 *
	 * @param array<string,mixed> $logging Logging diagnostics.
	 * @return void
	 */
	private function render_diagnostics_settings( array $logging ) {
		unset( $logging );
	}

	/**
	 * Stubs status-count diagnostics for this focused overview test.
	 *
	 * @param array<string,int> $statuses Status counts.
	 * @return void
	 */
	private function render_status_count_table( array $statuses ) {
		unset( $statuses );
	}

	/**
	 * Stubs recent polling diagnostics for this focused overview test.
	 *
	 * @param array<int,array<string,mixed>> $recent Recent outcomes.
	 * @return void
	 */
	private function render_recent_poll_outcomes( array $recent ) {
		unset( $recent );
	}

	/**
	 * Stubs event-log diagnostics for this focused overview test.
	 *
	 * @param array<string,mixed> $logging Logging diagnostics.
	 * @return void
	 */
	private function render_event_log_diagnostics( array $logging ) {
		unset( $logging );
	}

	/**
	 * Stubs support-copy diagnostics for this focused overview test.
	 *
	 * @param array<string,mixed> $support Support diagnostics.
	 * @return void
	 */
	private function render_support_copy_output( array $support ) {
		unset( $support );
	}
}

/**
 * Fake diagnostics service for overview rendering tests.
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Service_Stub {
	/**
	 * Diagnostics payload.
	 *
	 * @var array<string,mixed>
	 */
	private $diagnostics;

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed> $diagnostics Diagnostics payload.
	 */
	public function __construct( array $diagnostics ) {
		$this->diagnostics = $diagnostics;
	}

	/**
	 * Returns the diagnostics payload.
	 *
	 * @return array<string,mixed>
	 */
	public function collect() {
		return $this->diagnostics;
	}
}
