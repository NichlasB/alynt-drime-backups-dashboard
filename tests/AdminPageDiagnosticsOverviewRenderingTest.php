<?php
/**
 * Admin diagnostics overview rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-diagnostics-rendering-test-harness.php';

/**
 * Tests diagnostics overview rendering.
 */
class AdminPageDiagnosticsOverviewRenderingTest extends TestCase {
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
					'attention_history'   => array(
						'records_with_history'         => 14,
						'recently_recovered_records'  => 2,
						'repeated_attention_records'  => 1,
						'recent_attention_transitions' => 3,
					),
					'restore_readiness'   => array(
						'reporting_sites'       => 14,
						'evidence_sites'        => 8,
						'incomplete_sites'      => 6,
						'stale_sites'           => 0,
						'incompatible_sites'    => 0,
						'unknown_sites'         => 0,
						'reported_candidates'   => 28,
						'complete_candidates'   => 8,
						'incomplete_candidates' => 20,
						'server_candidates'     => 14,
						'server_complete'       => 8,
						'server_incomplete'     => 6,
						'wpvivid_candidates'    => 14,
						'wpvivid_complete'      => 0,
						'wpvivid_incomplete'    => 14,
					),
					'local_removal'       => array(
						'archived_records'         => 2,
						'ready_records'            => 1,
						'blocked_records'          => 1,
						'retained_snapshot_rows'   => 6,
						'retained_action_rows'     => 4,
						'non_terminal_action_rows' => 1,
					),
				),
				'summaries' => array(
					'attention_history' => 'repeated_attention_seen',
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
		$this->assertStringContainsString( 'Attention / Recovery History', $html );
		$this->assertStringContainsString( 'Repeated attention seen', $html );
		$this->assertStringContainsString( 'Recently recovered records', $html );
		$this->assertStringContainsString( 'Repeated attention records', $html );
		$this->assertStringContainsString( 'Recent attention transitions', $html );
		$this->assertStringContainsString( 'These aggregate counts come from retained redacted snapshot status history only.', $html );
		$this->assertStringContainsString( 'Restore Readiness Evidence', $html );
		$this->assertStringContainsString( 'Mixed restore evidence across reporting sites', $html );
		$this->assertStringContainsString( 'Sites reporting evidence', $html );
		$this->assertStringContainsString( 'Reported source candidates', $html );
		$this->assertStringContainsString( 'Server runner candidates', $html );
		$this->assertStringContainsString( 'WPvivid candidates', $html );
		$this->assertStringContainsString( '14 reported · 8 complete · 6 incomplete', $html );
		$this->assertStringContainsString( '14 reported · 0 complete · 14 incomplete', $html );
		$this->assertStringContainsString( 'they are not a restore guarantee', $html );
		$this->assertStringContainsString( 'Local Removal Readiness', $html );
		$this->assertStringContainsString( 'Archived records evaluated', $html );
		$this->assertStringContainsString( 'Future-removal ready records', $html );
		$this->assertStringContainsString( 'Blocked retained records', $html );
		$this->assertStringContainsString( 'Retained snapshot rows', $html );
		$this->assertStringContainsString( 'Retained action history rows', $html );
		$this->assertStringContainsString( 'Non-terminal action rows', $html );
		$this->assertStringContainsString( 'Permanent local removal is still unavailable', $html );
		$this->assertStringContainsString( '<td>28</td>', $html );
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

	/**
	 * Diagnostics overview shows support-safe runtime identity evidence.
	 *
	 * @return void
	 */
	public function test_overview_renders_dashboard_runtime_identity() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Harness();
		$html    = $harness->overview_html(
			array(
				'scheduler' => array(
					'current_utc' => '2026-09-30 19:30:00',
				),
				'counts'    => array(),
				'recent'    => array(),
				'logging'   => array(),
				'support'   => array(),
			)
		);

		$this->assertStringContainsString( 'Dashboard Runtime', $html );
		$this->assertStringContainsString( 'Installed version', $html );
		$this->assertStringContainsString( ALYNT_DRIME_BACKUPS_DASHBOARD_VERSION, $html );
		$this->assertStringContainsString( 'Protocol v1 / Status schema v1', $html );
		$this->assertStringContainsString( 'support-safe identity check', $html );
	}
}
