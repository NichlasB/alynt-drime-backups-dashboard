<?php
/**
 * Admin diagnostics rendering test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-settings.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-reporting.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-event-log.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-basic-detail-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-diagnostic-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-diagnostics-overview.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-diagnostics-event-log.php';
require_once __DIR__ . '/admin-page-diagnostics-overview-test-harness.php';

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
