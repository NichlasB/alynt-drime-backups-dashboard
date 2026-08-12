<?php
/**
 * Status classifier tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-status-classifier.php';

/**
 * Tests dashboard status classification.
 */
class StatusClassifierTest extends TestCase {
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
		$result = $this->classifier->classify(
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
		$result = $this->classifier->classify(
			$this->active_site(),
			$this->snapshot(
				array(
					'schema_version'            => 2,
					'server_outbox_configured'  => true,
					'wpvivid_override_configured' => false,
				)
			),
			1700000300
		);

		$this->assertSame( 'incompatible', $result['category'] );
	}

	/**
	 * Old snapshots become not reporting.
	 *
	 * @return void
	 */
	public function test_stale_snapshot_is_not_reporting() {
		$result = $this->classifier->classify(
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

	/**
	 * Failed uploads require attention.
	 *
	 * @return void
	 */
	public function test_failed_uploads_need_attention() {
		$result = $this->classifier->classify(
			$this->active_site(),
			$this->snapshot(
				array(
					'schema_version'           => 1,
					'server_outbox_configured' => true,
					'failed_count'             => 1,
				)
			),
			1700000300
		);

		$this->assertSame( 'needs_attention', $result['category'] );
	}

	/**
	 * New snapshot schema payload_json is decoded for classification.
	 *
	 * @return void
	 */
	public function test_payload_json_snapshot_is_decoded() {
		$result = $this->classifier->classify(
			$this->active_site(),
			array(
				'schema_version' => 1,
				'payload_json'   => wp_json_encode(
					array(
						'schema_version'           => 1,
						'server_outbox_configured' => true,
						'failed_count'             => 1,
					)
				),
				'observed_at'    => '2023-11-14 22:15:00',
			),
			1700000300
		);

		$this->assertSame( 'needs_attention', $result['category'] );
	}

	/**
	 * Queue alone is not a failure.
	 *
	 * @return void
	 */
	public function test_queue_alone_is_working() {
		$result = $this->classifier->classify(
			$this->active_site(),
			$this->snapshot(
				array(
					'schema_version'           => 1,
					'server_outbox_configured' => true,
					'queue_count'              => 3,
					'active_upload'            => true,
					'failed_count'             => 0,
					'warning_count'            => 0,
				)
			),
			1700000300
		);

		$this->assertSame( 'working', $result['category'] );
	}

	/**
	 * Source queue warnings alone stay informational.
	 *
	 * @return void
	 */
	public function test_source_queue_warning_alone_is_working() {
		$result = $this->classifier->classify(
			$this->active_site(),
			$this->snapshot(
				array_merge(
					$this->healthy_payload(),
					array(
						'backup_sources' => array(
							'server' => array_merge(
								$this->source_payload(),
								array(
									'queued_count'      => 1,
									'freshness_status'  => 'fresh',
									'warning_count'     => 1,
									'warnings'          => array(
										array(
											'code'    => 'source_queue_not_empty',
											'message' => 'Queued package waiting to upload.',
										),
									),
								)
							),
						),
					)
				)
			),
			1700000300
		);

		$this->assertSame( 'working', $result['category'] );
	}

	/**
	 * Configured stale source evidence needs attention.
	 *
	 * @return void
	 */
	public function test_stale_backup_source_needs_attention() {
		$result = $this->classifier->classify(
			$this->active_site(),
			$this->snapshot(
				array_merge(
					$this->healthy_payload(),
					array(
						'backup_sources' => array(
							'server' => array_merge(
								$this->source_payload(),
								array(
									'freshness_status' => 'stale',
								)
							),
						),
					)
				)
			),
			1700000300
		);

		$this->assertSame( 'needs_attention', $result['category'] );
		$this->assertStringContainsString( 'stale or missing upload evidence', $result['message'] );
	}

	/**
	 * Lack of all known sources is not configured.
	 *
	 * @return void
	 */
	public function test_no_known_source_is_not_configured() {
		$result = $this->classifier->classify(
			$this->active_site(),
			$this->snapshot(
				array(
					'schema_version'              => 1,
					'server_outbox_configured'    => false,
					'wpvivid_override_configured' => false,
					'old_wpvivid_uploader_active' => false,
				)
			),
			1700000300
		);

		$this->assertSame( 'not_configured', $result['category'] );
	}

	/**
	 * Source summaries can positively prove no supported source is configured.
	 *
	 * @return void
	 */
	public function test_backup_sources_can_prove_not_configured() {
		$result = $this->classifier->classify(
			$this->active_site(),
			$this->snapshot(
				array_merge(
					$this->healthy_payload(),
					array(
						'server_outbox_configured'    => true,
						'wpvivid_override_configured' => true,
						'backup_sources'              => array(
							'server'  => array_merge(
								$this->source_payload(),
								array(
									'configured'          => false,
									'has_upload_evidence' => false,
									'freshness_status'    => 'not_configured',
								)
							),
							'wpvivid' => array_merge(
								$this->source_payload(),
								array(
									'configured'          => false,
									'has_upload_evidence' => false,
									'freshness_status'    => 'not_configured',
								)
							),
						),
					)
				)
			),
			1700000300
		);

		$this->assertSame( 'not_configured', $result['category'] );
	}

	/**
	 * Builds an active site row.
	 *
	 * @param string $last_seen Last seen date.
	 * @return array<string,mixed>
	 */
	private function active_site( $last_seen = '2023-11-14 22:15:00' ) {
		return array(
			'status'       => 'working',
			'paused_at'    => null,
			'last_seen_at' => $last_seen,
		);
	}

	/**
	 * Builds a snapshot row.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $captured_at Captured date.
	 * @return array<string,mixed>
	 */
	private function snapshot( array $payload, $captured_at = '2023-11-14 22:15:00' ) {
		return array(
			'schema_version'  => isset( $payload['schema_version'] ) ? $payload['schema_version'] : 1,
			'decoded_payload' => $payload,
			'captured_at'     => $captured_at,
		);
	}

	/**
	 * Builds a healthy payload.
	 *
	 * @return array<string,mixed>
	 */
	private function healthy_payload() {
		return array(
			'schema_version'              => 1,
			'server_outbox_configured'    => true,
			'wpvivid_override_configured' => false,
			'old_wpvivid_uploader_active' => false,
			'failed_count'                => 0,
			'warning_count'               => 0,
			'warnings'                    => array(),
			'cron_status'                 => 'ok',
		);
	}

	/**
	 * Builds a source summary payload.
	 *
	 * @return array<string,mixed>
	 */
	private function source_payload() {
		return array(
			'source_key'                => 'server',
			'source_label'              => 'Server',
			'configured'                => true,
			'has_upload_evidence'       => true,
			'queued_count'              => 0,
			'uploaded_count'            => 1,
			'failed_count'              => 0,
			'remote_registry_count'     => 1,
			'latest_uploaded_at'        => 1700000000,
			'latest_inventory_count'    => 1,
			'latest_inventory_evidence' => 'local_upload_registry',
			'freshness_status'          => 'fresh',
			'warning_count'             => 0,
			'warnings'                  => array(),
		);
	}
}
