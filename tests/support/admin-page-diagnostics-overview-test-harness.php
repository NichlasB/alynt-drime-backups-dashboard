<?php
/**
 * Admin diagnostics overview test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-diagnostics-overview-service-stub.php';
require_once __DIR__ . '/admin-page-diagnostics-overview-stub-methods.php';

/**
 * Harness exposing the diagnostics overview shell.
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostic_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Overview;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Stub_Methods;

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
}
