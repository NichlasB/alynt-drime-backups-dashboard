<?php
/**
 * Remote action repository client-report tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-repository-test-bootstrap.php';
require_once __DIR__ . '/support/remote-action-repository-client-report-rollback-summary-fixtures.php';
require_once __DIR__ . '/support/remote-action-repository-client-report-rollback-fixtures.php';
require_once __DIR__ . '/support/remote-action-repository-client-report-fixtures.php';

/**
 * Tests support-safe client report context preservation.
 */
class RemoteActionRepositoryClientReportTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Client_Report_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_WPDB_Setup;

	/**
	 * Client reports store support-safe reconciliation fields.
	 *
	 * @return void
	 */
	public function test_mark_client_report_stores_sanitized_reconciliation_fields() {
		$repository = $this->remote_action_repository();

		$this->assertTrue(
			$repository->mark_client_report(
				321,
				$this->client_reconciliation_report(),
				'2026-08-20 12:05:00'
			)
		);

		$this->assertSame( array( 'id' => 321 ), $this->wpdb->updated_where );
		$this->assertSame( 'succeeded', $this->wpdb->updated_data['state'] );
		$this->assertSame( 'succeeded', $this->wpdb->updated_data['client_state'] );
		$this->assertSame( 'action_succeeded', $this->wpdb->updated_data['client_result_code'] );
		$this->assertSame( '<b>Scan completed safely.</b>', $this->wpdb->updated_data['client_result_summary'] );
		$this->assertSame( '2026-08-20 12:04:00', $this->wpdb->updated_data['client_updated_at'] );
		$this->assertSame( '2026-08-20 12:05:00', $this->wpdb->updated_data['reconciled_at'] );
		$this->assertArrayHasKey( 'completed_at', $this->wpdb->updated_data );

		$counts = json_decode( $this->wpdb->updated_data['client_counts_json'], true );

		$this->assertSame( 4, $counts['found'] );
		$this->assertSame( 0, $counts['failed'] );
		$this->assertArrayNotHasKey( 'local_path', $counts );
	}

}
