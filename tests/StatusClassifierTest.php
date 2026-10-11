<?php
/**
 * Status classifier tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-classifier-test-bootstrap.php';

/**
 * Tests baseline dashboard status classification.
 */
class StatusClassifierTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Test_Fixtures;

	/**
	 * Classifier under test.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Status_Classifier
	 */
	private $classifier;

	/**
	 * Sets up the test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->classifier = new Alynt_Drime_Backups_Dashboard_Status_Classifier();
	}

	/**
	 * Pending sites stay pending before snapshots exist.
	 *
	 * @return void
	 */
	public function test_pending_site_stays_pending() {
		$result = $this->classify_site_snapshot(
			array(
				'status'    => 'pending',
				'paused_at' => null,
			),
			null,
			1234567890
		);

		$this->assertSame( 'pending', $result['category'] );
	}

	/**
	 * Unsupported schema versions are incompatible.
	 *
	 * @return void
	 */
	public function test_unsupported_schema_is_incompatible() {
		$result = $this->classify_payload(
			array(
				'schema_version'              => 2,
				'server_outbox_configured'    => true,
				'wpvivid_override_configured' => false,
			)
		);

		$this->assertSame( 'incompatible', $result['category'] );
	}

	/**
	 * Old snapshots become not reporting.
	 *
	 * @return void
	 */
	public function test_stale_snapshot_is_not_reporting() {
		$result = $this->classify_site_snapshot(
			$this->active_site( '2023-11-14 22:13:20' ),
			$this->snapshot(
				array(
					'schema_version'           => 1,
					'server_outbox_configured' => true,
				),
				'2023-11-14 22:13:20'
			),
			strtotime( '2023-11-14 23:30:00' )
		);

		$this->assertSame( 'not_reporting', $result['category'] );
	}

}
