<?php
/**
 * Diagnostics audit-history support tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Test get_option shim.
	 *
	 * @param string $option Option.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	function get_option( $option, $default = false ) {
		global $alynt_drime_backups_dashboard_test_options;

		if ( ! is_array( $alynt_drime_backups_dashboard_test_options ) ) {
			$alynt_drime_backups_dashboard_test_options = array();
		}

		return array_key_exists( $option, $alynt_drime_backups_dashboard_test_options ) ? $alynt_drime_backups_dashboard_test_options[ $option ] : $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * Test update_option shim.
	 *
	 * @param string $option Option.
	 * @param mixed  $value Value.
	 * @param mixed  $autoload Autoload.
	 * @return bool
	 */
	function update_option( $option, $value, $autoload = null ) {
		global $alynt_drime_backups_dashboard_test_options;
		global $alynt_drime_backups_dashboard_test_autoload;

		$alynt_drime_backups_dashboard_test_options[ $option ]  = $value;
		$alynt_drime_backups_dashboard_test_autoload[ $option ] = $autoload;

		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * Test delete_option shim.
	 *
	 * @param string $option Option.
	 * @return bool
	 */
	function delete_option( $option ) {
		global $alynt_drime_backups_dashboard_test_options;

		unset( $alynt_drime_backups_dashboard_test_options[ $option ] );

		return true;
	}
}

require_once dirname( __DIR__ ) . '/includes/class-site-repository.php';
require_once dirname( __DIR__ ) . '/includes/class-snapshot-repository.php';
require_once dirname( __DIR__ ) . '/includes/class-status-classifier.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-enrollment-rest-responses.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-enrollment-rest-route-args.php';
require_once dirname( __DIR__ ) . '/includes/class-enrollment-rest-controller.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-poller-scheduling.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-poller-locks.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-poller-status-check.php';
require_once dirname( __DIR__ ) . '/includes/class-poller.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-diagnostics-scheduler.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-diagnostics-support.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-diagnostics-site-metrics.php';
require_once dirname( __DIR__ ) . '/includes/class-diagnostics.php';

/**
 * Fake site repository for audit support tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Audit_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Lists sites.
	 *
	 * @param array $args Query args.
	 * @return array<int,array<string,mixed>>
	 */
	public function all( $args = array() ) {
		unset( $args );

		return array();
	}
}

/**
 * Fake snapshot repository for audit support tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Audit_Snapshot_Repository extends Alynt_Drime_Backups_Dashboard_Snapshot_Repository {
	/**
	 * Gets latest snapshots keyed by site ID.
	 *
	 * @param array<int> $site_ids Site IDs.
	 * @return array<int,array<string,mixed>>
	 */
	public function latest_by_site_ids( array $site_ids ) {
		unset( $site_ids );

		return array();
	}
}

/**
 * Tests support-safe audit-history diagnostics.
 */
class DiagnosticsAuditSupportTest extends TestCase {
	/**
	 * Resets option shims.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		global $alynt_drime_backups_dashboard_test_options;
		global $alynt_drime_backups_dashboard_test_autoload;

		$alynt_drime_backups_dashboard_test_options  = array();
		$alynt_drime_backups_dashboard_test_autoload = array();
	}

	/**
	 * Audit summary includes aggregates without leaking event context.
	 *
	 * @return void
	 */
	public function test_support_summary_includes_audit_aggregate_only() {
		$event_log = new Alynt_Drime_Backups_Dashboard_Event_Log();
		$event_log->audit_action(
			'check_status_now',
			'succeeded',
			array(
				'dashboard_site_id' => 9,
				'pairing_token'     => 'adb1.secret-token',
			)
		);

		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Audit_Site_Repository(),
			new Alynt_Drime_Backups_Dashboard_Test_Audit_Snapshot_Repository(),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier(),
			null,
			$event_log
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertNotFalse( $encoded );
		$this->assertStringContainsString( 'audit_history', $encoded );
		$this->assertSame( 1, $result['support']['logging']['audit_history']['event_count'] );
		$this->assertSame( 90, $result['support']['logging']['audit_history']['retention_days'] );
		$this->assertSame( 500, $result['support']['logging']['audit_history']['max_events'] );
		$this->assertStringNotContainsString( 'adb1.secret-token', $encoded );
		$this->assertStringNotContainsString( 'dashboard_site_id', $encoded );
	}
}
