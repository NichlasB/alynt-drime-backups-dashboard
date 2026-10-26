<?php
/**
 * Admin diagnostics rendering test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/diagnostics/class-event-log-redactor.php';
require_once dirname( __DIR__, 2 ) . '/includes/diagnostics/traits/trait-event-log-storage.php';
require_once dirname( __DIR__, 2 ) . '/includes/diagnostics/traits/trait-event-log-settings.php';
require_once dirname( __DIR__, 2 ) . '/includes/diagnostics/traits/trait-event-log-reporting.php';
require_once dirname( __DIR__, 2 ) . '/includes/diagnostics/class-event-log.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-basic-detail-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-diagnostic-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-diagnostics-overview.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-diagnostics-audit-history.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-diagnostics-event-log.php';
require_once __DIR__ . '/admin-page-diagnostics-overview-test-harness.php';

/**
 * Harness exposing private diagnostics rendering helpers.
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics_Rendering_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Event_Log;

	const MENU_SLUG = 'alynt-drime-backups-dashboard';

	/**
	 * Event-log read double, without option/database access.
	 *
	 * @var object
	 */
	public $event_log;

	/**
	 * Exposes the actual export renderer with a bounded read double.
	 *
	 * @param array $events Events returned by the read double.
	 * @return string
	 */
	public function event_export_html( array $events ) {
		$this->event_log = new class( $events ) {
			private $events;
			public function __construct( array $events ) {
				$this->events = $events;
			}
			public function recent_events( $limit ) {
				return array_slice( $this->events, 0, $limit );
			}
		};
		ob_start();
		$this->render_event_log_diagnostics( array( 'events' => $events ) );
		return (string) ob_get_clean();
	}

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
