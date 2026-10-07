<?php
/**
 * Admin diagnostics overview test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-diagnostics-overview-service-stub.php';

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
