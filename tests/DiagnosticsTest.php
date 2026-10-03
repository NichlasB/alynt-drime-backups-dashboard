<?php
/**
 * Diagnostics tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/traits/trait-site-repository-reads.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-site-repository-writes.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-site-repository-runtime-writes.php';
require_once dirname( __DIR__ ) . '/includes/class-site-repository.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-snapshot-repository-reads.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-snapshot-repository-retention.php';
require_once dirname( __DIR__ ) . '/includes/class-snapshot-repository.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-status-classifier-backup-sources.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-status-classifier-helpers.php';
require_once dirname( __DIR__ ) . '/includes/class-status-classifier.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-settings.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-reporting.php';
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
require_once dirname( __DIR__ ) . '/includes/traits/trait-diagnostics-attention-history-metrics.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-diagnostics-site-metrics.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-diagnostics-site-metric-helpers.php';
require_once dirname( __DIR__ ) . '/includes/class-diagnostics.php';

/**
 * Fake site repository for diagnostics tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Sites.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $sites;

	/**
	 * Constructor.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 */
	public function __construct( array $sites ) {
		$this->sites = $sites;
	}

	/**
	 * Lists sites.
	 *
	 * @param array $args Query args.
	 * @return array<int,array<string,mixed>>
	 */
	public function all( $args = array() ) {
		unset( $args );

		return $this->sites;
	}
}

/**
 * Fake snapshot repository for diagnostics tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository extends Alynt_Drime_Backups_Dashboard_Snapshot_Repository {
	/**
	 * Snapshots keyed by site ID.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $snapshots;

	/**
	 * Recent snapshot histories keyed by site ID.
	 *
	 * @var array<int,array<int,array<string,mixed>>>
	 */
	private $histories;

	/**
	 * Constructor.
	 *
	 * @param array<int,array<string,mixed>>            $snapshots Snapshots.
	 * @param array<int,array<int,array<string,mixed>>> $histories Recent snapshot histories.
	 */
	public function __construct( array $snapshots, array $histories = array() ) {
		$this->snapshots = $snapshots;
		$this->histories = $histories;
	}

	/**
	 * Gets latest snapshots keyed by site ID.
	 *
	 * @param array<int> $site_ids Site IDs.
	 * @return array<int,array<string,mixed>>
	 */
	public function latest_by_site_ids( array $site_ids ) {
		$matched = array();

		foreach ( $site_ids as $site_id ) {
			if ( isset( $this->snapshots[ (int) $site_id ] ) ) {
				$matched[ (int) $site_id ] = $this->snapshots[ (int) $site_id ];
			}
		}

		return $matched;
	}

	/**
	 * Gets recent snapshots for one site.
	 *
	 * @param int $site_id Site ID.
	 * @param int $limit Maximum snapshots.
	 * @return array<int,array<string,mixed>>
	 */
	public function recent_for_site( $site_id, $limit = 10 ) {
		$site_id = (int) $site_id;

		if ( empty( $this->histories[ $site_id ] ) ) {
			return array();
		}

		return array_slice( $this->histories[ $site_id ], 0, max( 1, min( 50, (int) $limit ) ) );
	}
}

/**
 * Tests redacted dashboard diagnostics.
 */
class DiagnosticsTest extends TestCase {
	/**
	 * Diagnostics count polling-ready, due, paused, and failed sites.
	 *
	 * @return void
	 */
	public function test_collect_counts_polling_states() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site(
						1,
						array(
							'overall_status'          => 'working',
							'next_poll_at'            => '2020-01-01 00:00:00',
							'last_poll_attempt_at'    => '2026-08-10 08:00:00',
							'last_seen_at'            => '2026-08-10 08:00:00',
							'consecutive_failures'    => 1,
							'last_error_code'         => 'transport_failed',
							'last_error_summary'      => 'Client status endpoint unavailable.',
							'polling_key_id'          => 'pk_example_0000000000000000',
							'polling_secret_ciphertext' => 'adbv1.ciphertext',
						)
					),
					$this->site(
						2,
						array(
							'enrollment_status' => 'awaiting_first_poll',
							'overall_status'    => 'pending',
							'polling_key_id'    => '',
						)
					),
					$this->site(
						3,
						array(
							'overall_status' => 'working',
							'paused_at'      => '2026-08-10 08:05:00',
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(),
					3 => $this->snapshot(),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result = $diagnostics->collect();

		$this->assertSame( 3, $result['counts']['total_sites'] );
		$this->assertSame( 1, $result['counts']['polling_ready'] );
		$this->assertSame( 2, $result['counts']['not_polling'] );
		$this->assertSame( 1, $result['counts']['due_now'] );
		$this->assertSame( 1, $result['counts']['missing_credentials'] );
		$this->assertSame( 1, $result['counts']['paused'] );
		$this->assertSame( 1, $result['counts']['with_failures'] );
		$this->assertSame( 2, $result['counts']['record_states']['active'] );
		$this->assertSame( 1, $result['counts']['record_states']['awaiting_first_poll'] );
		$this->assertSame( 0, $result['counts']['record_states']['pending'] );
		$this->assertSame( 0, $result['counts']['record_states']['revoked'] );
		$this->assertSame( 0, $result['counts']['backup_sources']['reporting_sites'] );
		$this->assertSame( 'unavailable', $result['scheduler']['poll_schedule_state'] );
		$this->assertSame( 30, $result['scheduler']['retention_days'] );
	}

	/**
	 * Diagnostics expose support-safe aggregate record states.
	 *
	 * @return void
	 */
	public function test_record_state_diagnostics_explain_non_polling_records() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site(
						1,
						array(
							'enrollment_status' => 'pending',
							'overall_status'    => 'pending',
							'polling_key_id'    => '',
						)
					),
					$this->site(
						2,
						array(
							'enrollment_status' => 'revoked',
							'overall_status'    => 'pending',
							'polling_key_id'    => '',
						)
					),
					$this->site(
						3,
						array(
							'enrollment_status' => '',
							'overall_status'    => 'pending',
							'polling_key_id'    => '',
						)
					),
					$this->site(
						4,
						array(
							'enrollment_status' => 'revoked',
							'overall_status'    => 'pending',
							'polling_key_id'    => '',
							'archived_at'       => '2026-09-19 18:30:00',
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository( array() ),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 4, $result['counts']['total_sites'] );
		$this->assertSame( 0, $result['counts']['polling_ready'] );
		$this->assertSame( 4, $result['counts']['not_polling'] );
		$this->assertSame( 1, $result['counts']['record_states']['pending'] );
		$this->assertSame( 1, $result['counts']['record_states']['revoked'] );
		$this->assertSame( 1, $result['counts']['record_states']['archived'] );
		$this->assertSame( 1, $result['counts']['record_states']['unknown'] );
		$this->assertStringContainsString( 'record_states', $encoded );
		$this->assertStringContainsString( 'not_polling', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
	}

	/**
	 * Backup source diagnostics are aggregate-only.
	 *
	 * @return void
	 */
	public function test_backup_source_diagnostics_are_aggregate_only() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site( 1 ),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(
						array(
							'backup_sources' => array(
								'server'  => array(
									'freshness_status' => 'stale',
								),
								'wpvivid' => array(
									'freshness_status' => 'no_upload_evidence',
								),
							),
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 1, $result['counts']['backup_sources']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['backup_sources']['stale_sources'] );
		$this->assertSame( 1, $result['counts']['backup_sources']['no_upload_evidence_sources'] );
		$this->assertStringContainsString( 'backup_sources', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
	}

	/**
	 * Schedule-management diagnostics are aggregate-only.
	 *
	 * @return void
	 */
	public function test_schedule_management_diagnostics_are_aggregate_only() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site( 1 ),
					$this->site( 2 ),
					$this->site( 3 ),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(
						array(
							'remote_actions' => array(
								'protocol_version'    => 2,
								'enabled'             => true,
								'schedule_management' => array(
									'protocol_version'   => 2,
									'capability_version' => 1,
									'enabled'            => true,
									'preview_only'       => true,
									'apply_supported'    => false,
									'rollback_supported' => false,
									'schedules'          => array(
										array(
											'schedule_id'       => 'alynt_scan_upload',
											'current_cadence'   => 'every_15_minutes',
										),
									),
								),
							),
						)
					),
					2 => $this->snapshot(),
					3 => $this->snapshot(
						array(
							'remote_actions' => array(
								'protocol_version'    => 2,
								'enabled'             => true,
								'schedule_management' => array(
									'protocol_version'            => 2,
									'capability_version'          => 1,
									'enabled'                     => true,
									'preview_only'                => false,
									'apply_supported'             => true,
									'rollback_preview_supported'  => true,
									'rollback_supported'          => false,
									'schedules'                   => array(
										array(
											'schedule_id'                => 'alynt_scan_upload',
											'current_cadence'            => 'every_30_minutes',
											'rollback_preview_supported' => true,
										),
									),
								),
							),
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 2, $result['counts']['schedule_management']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['preview_only_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['apply_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['unavailable_sites'] );
		$this->assertSame( 2, $result['counts']['schedule_management']['reported_schedules'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['rollback_preview_supported_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['rollback_preview_hidden_sites'] );
		$this->assertSame( 0, $result['counts']['schedule_management']['rollback_apply_advertised_sites'] );
		$this->assertStringContainsString( 'schedule_management', $encoded );
		$this->assertStringContainsString( 'rollback_preview_supported_sites', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'alynt_scan_upload', $encoded );
	}

	/**
	 * Cleanup-preview diagnostics are aggregate-only.
	 *
	 * @return void
	 */
	public function test_cleanup_preview_diagnostics_are_aggregate_only() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site( 1 ),
					$this->site( 2 ),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(
						array(
							'remote_actions' => array(
								'protocol_version'   => 2,
								'enabled'            => true,
								'cleanup_management' => array(
									'protocol_version'        => 2,
									'capability_version'      => 1,
									'enabled'                 => true,
									'preview_supported'       => true,
									'apply_supported'         => false,
									'scope'                   => 'safe_local_uploader_owned',
									'supported_categories'    => array( 'uploader_temp_artifacts' ),
									'max_preview_age_seconds' => 900,
								),
							),
						)
					),
					2 => $this->snapshot(
						array(
							'remote_actions' => array(
								'protocol_version'   => 2,
								'enabled'            => true,
								'cleanup_management' => array(
									'protocol_version'       => 2,
									'enabled'                => true,
									'preview_supported'      => true,
									'apply_supported'        => true,
									'cleanup_apply_available' => true,
									'scope'                  => 'safe_local_uploader_owned',
									'supported_categories'   => array( 'uploader_temp_artifacts' ),
								),
							),
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 2, $result['counts']['cleanup_preview']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['cleanup_preview']['preview_supported_sites'] );
		$this->assertSame( 1, $result['counts']['cleanup_preview']['unavailable_sites'] );
		$this->assertSame( 1, $result['counts']['cleanup_preview']['apply_or_mutation_advertised_sites'] );
		$this->assertSame( 2, $result['counts']['cleanup_preview']['supported_categories'] );
		$this->assertStringContainsString( 'cleanup_preview', $encoded );
		$this->assertStringContainsString( 'preview_supported_sites', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'uploader_temp_artifacts', $encoded );
	}

	/**
	 * Restore-readiness diagnostics are aggregate-only and evidence-only.
	 *
	 * @return void
	 */
	public function test_restore_readiness_diagnostics_are_aggregate_only() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site( 1 ),
					$this->site( 2 ),
					$this->site( 3 ),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(
						array(
							'restore_readiness' => array(
								'overall_state' => 'evidence_available',
								'candidates'    => array(
									array(
										'source'          => 'server',
										'candidate_ref'   => 'opaque-do-not-export',
										'component_state' => 'complete',
										'checksum_state'  => 'verified',
										'manifest_state'  => 'compatible',
										'sidecar_state'   => 'present',
									),
									array(
										'source'          => 'wpvivid',
										'candidate_ref'   => 'opaque-do-not-export-2',
										'component_state' => 'unknown',
										'checksum_state'  => 'not_reported',
										'manifest_state'  => 'not_reported',
										'sidecar_state'   => 'not_reported',
									),
								),
							),
						)
					),
					2 => $this->snapshot(),
					3 => $this->snapshot(
						array(
							'restore_readiness' => array(
								'overall_state' => 'incomplete',
								'candidates'    => array(
									array(
										'source'          => 'server',
										'candidate_ref'   => 'another-opaque-ref',
										'component_state' => 'partial',
										'checksum_state'  => 'unknown',
										'manifest_state'  => 'unknown',
										'sidecar_state'   => 'missing',
									),
								),
							),
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 2, $result['counts']['restore_readiness']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['evidence_sites'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['incomplete_sites'] );
		$this->assertSame( 3, $result['counts']['restore_readiness']['reported_candidates'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['complete_candidates'] );
		$this->assertSame( 2, $result['counts']['restore_readiness']['incomplete_candidates'] );
		$this->assertSame( 2, $result['counts']['restore_readiness']['server_candidates'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['server_complete'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['server_incomplete'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['wpvivid_candidates'] );
		$this->assertSame( 0, $result['counts']['restore_readiness']['wpvivid_complete'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['wpvivid_incomplete'] );
		$this->assertStringContainsString( 'restore_readiness', $encoded );
		$this->assertStringContainsString( 'reported_candidates', $encoded );
		$this->assertStringContainsString( 'server_candidates', $encoded );
		$this->assertStringContainsString( 'wpvivid_candidates', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'opaque-do-not-export', $encoded );
		$this->assertStringNotContainsString( 'another-opaque-ref', $encoded );
	}

	/**
	 * Attention-history diagnostics are aggregate-only and support safe.
	 *
	 * @return void
	 */
	public function test_attention_history_diagnostics_are_aggregate_only() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site( 1 ),
					$this->site( 2 ),
					$this->site(
						3,
						array(
							'archived_at' => '2026-09-29 10:00:00',
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(),
				array(
					1 => array(
						$this->snapshot_row( 'working', '2026-09-29 11:00:00' ),
						$this->snapshot_row( 'needs_attention', '2026-09-29 10:00:00' ),
						$this->snapshot_row( 'working', '2026-09-29 09:00:00' ),
					),
					2 => array(
						$this->snapshot_row( 'working', '2026-09-29 11:00:00' ),
						$this->snapshot_row( 'needs_attention', '2026-09-29 10:00:00' ),
						$this->snapshot_row( 'working', '2026-09-29 09:00:00' ),
						$this->snapshot_row( 'not_reporting', '2026-09-29 08:00:00' ),
						$this->snapshot_row( 'working', '2026-09-29 07:00:00' ),
					),
					3 => array(
						$this->snapshot_row( 'working', '2026-09-29 11:00:00' ),
						$this->snapshot_row( 'needs_attention', '2026-09-29 10:00:00' ),
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 2, $result['counts']['attention_history']['records_with_history'] );
		$this->assertSame( 2, $result['counts']['attention_history']['recently_recovered_records'] );
		$this->assertSame( 1, $result['counts']['attention_history']['repeated_attention_records'] );
		$this->assertSame( 3, $result['counts']['attention_history']['recent_attention_transitions'] );
		$this->assertSame( 'repeated_attention_seen', $result['summaries']['attention_history'] );
		$this->assertStringContainsString( 'attention_history', $encoded );
		$this->assertStringContainsString( 'recently_recovered_records', $encoded );
		$this->assertStringContainsString( 'repeated_attention_seen', $encoded );
		$this->assertStringContainsString( 'Retained snapshot history shows repeated transitions into attention states.', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'needs_attention -&gt; working', $encoded );
	}

	/**
	 * Recent diagnostics omit stored credential fields.
	 *
	 * @return void
	 */
	public function test_recent_poll_outcomes_are_redacted() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site(
						1,
						array(
							'last_poll_attempt_at'      => '2026-08-10 08:00:00',
							'last_seen_at'              => '2026-08-10 08:00:00',
							'polling_key_id'            => 'pk_example_0000000000000000',
							'polling_secret_ciphertext' => 'adbv1.secret-ciphertext',
							'pairing_secret_hash'       => str_repeat( 'a', 64 ),
							'latest_payload_json'       => '{"unsafe":"raw"}',
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository( array() ),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['recent'] );

		$this->assertNotFalse( $encoded );
		$this->assertStringNotContainsString( 'polling_secret_ciphertext', $encoded );
		$this->assertStringNotContainsString( 'pairing_secret_hash', $encoded );
		$this->assertStringNotContainsString( 'latest_payload_json', $encoded );
		$this->assertStringNotContainsString( 'secret-ciphertext', $encoded );
		$this->assertStringContainsString( 'last_poll_attempt_at', $encoded );
	}

	/**
	 * Support-copy summary omits client-identifying and secret-bearing fields.
	 *
	 * @return void
	 */
	public function test_support_summary_is_support_safe() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site(
						9,
						array(
							'site_label'                 => 'Very Private Client',
							'expected_origin'            => 'https://private-client.example.com',
							'last_poll_attempt_at'       => '2026-08-10 08:00:00',
							'last_seen_at'               => '2026-08-10 08:00:00',
							'next_poll_at'               => '2026-08-10 08:15:00',
							'polling_key_id'             => 'pk_example_private',
							'polling_secret_ciphertext'  => 'adbv1.private-ciphertext',
							'pairing_secret_hash'        => str_repeat( 'b', 64 ),
							'latest_payload_json'        => '{"raw":"payload"}',
							'last_error_code'            => 'transport_failed',
							'last_error_summary'         => 'Sanitized failure summary.',
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository( array() ),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertNotFalse( $encoded );
		$this->assertStringContainsString( 'recent_safe', $encoded );
		$this->assertStringContainsString( 'transport_failed', $encoded );
		$this->assertStringNotContainsString( 'Very Private Client', $encoded );
		$this->assertStringNotContainsString( 'private-client.example.com', $encoded );
		$this->assertStringNotContainsString( 'polling_key_id', $encoded );
		$this->assertStringNotContainsString( 'polling_secret_ciphertext', $encoded );
		$this->assertStringNotContainsString( 'pairing_secret_hash', $encoded );
		$this->assertStringNotContainsString( 'latest_payload_json', $encoded );
		$this->assertStringNotContainsString( 'private-ciphertext', $encoded );
		$this->assertStringNotContainsString( 'Sanitized failure summary.', $encoded );
	}

	/**
	 * Support action summaries include preview-only action counts as aggregates only.
	 *
	 * @return void
	 */
	public function test_support_action_summary_includes_preview_only_action_counts() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Test_Harness();
		$actions = $harness->support_action_summary(
			array(
				'total'                     => 7,
				'client_reconciled'         => 5,
				'stale'                     => 1,
				'awaiting_confirmation'     => 2,
				'schedule_apply'            => 3,
				'schedule_rollback_preview' => 2,
				'cleanup_preview'           => 1,
				'rollback_metadata'         => 1,
				'latest_updated_at'         => '2026-09-21 16:00:00',
			)
		);

		$this->assertSame( 7, $actions['total'] );
		$this->assertSame( 3, $actions['schedule_apply'] );
		$this->assertSame( 2, $actions['schedule_rollback_preview'] );
		$this->assertSame( 1, $actions['cleanup_preview'] );
		$this->assertSame( 1, $actions['rollback_metadata'] );
		$this->assertArrayNotHasKey( 'redacted_context_json', $actions );
		$this->assertArrayNotHasKey( 'source_apply_action_id', $actions );
	}

	/**
	 * Support action summaries keep preview-only action counts non-negative.
	 *
	 * @return void
	 */
	public function test_support_action_summary_bounds_preview_only_action_counts() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Test_Harness();
		$actions = $harness->support_action_summary(
			array(
				'schedule_rollback_preview' => -4,
				'cleanup_preview'           => -2,
			)
		);

		$this->assertSame( 0, $actions['schedule_rollback_preview'] );
		$this->assertSame( 0, $actions['cleanup_preview'] );
	}

	/**
	 * Creates a site row.
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function site( $site_id, array $overrides = array() ) {
		return array_merge(
			array(
				'id'                         => $site_id,
				'public_id'                  => '00000000-0000-4000-8000-00000000000' . $site_id,
				'site_label'                 => 'Client ' . $site_id,
				'expected_origin'            => 'https://client' . $site_id . '.example.com',
				'enrollment_status'          => 'active',
				'overall_status'             => 'working',
				'polling_key_id'             => 'pk_example_0000000000000000',
				'polling_secret_ciphertext'  => 'adbv1.ciphertext',
				'next_poll_at'               => '2099-01-01 00:00:00',
				'last_poll_attempt_at'       => '',
				'last_seen_at'               => '',
				'consecutive_failures'       => 0,
				'last_error_code'            => '',
				'last_error_summary'         => '',
				'paused_at'                  => '',
			),
			$overrides
		);
	}

	/**
	 * Creates a healthy snapshot.
	 *
	 * @return array<string,mixed>
	 */
	private function snapshot( array $overrides = array() ) {
		return array(
			'schema_version'   => 1,
			'observed_at'      => gmdate( 'Y-m-d H:i:s' ),
			'decoded_payload'  => array_merge(
				array(
					'schema_version'           => 1,
					'server_outbox_configured' => true,
					'failed_count'             => 0,
					'warning_count'            => 0,
					'warnings'                 => array(),
					'cron_status'              => 'ok',
				),
				$overrides
			),
		);
	}

	/**
	 * Creates a retained snapshot summary row.
	 *
	 * @param string $status Snapshot status.
	 * @param string $observed_at Observed time.
	 * @return array<string,mixed>
	 */
	private function snapshot_row( $status, $observed_at ) {
		return array(
			'overall_status' => $status,
			'observed_at'    => $observed_at,
		);
	}
}

/**
 * Harness exposing support-summary action aggregates.
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Support;

	/**
	 * Gets support-safe action aggregate output.
	 *
	 * @param array<string,mixed> $remote_actions Remote-action aggregate.
	 * @return array<string,mixed>
	 */
	public function support_action_summary( array $remote_actions ) {
		$support = $this->support_summary_from_diagnostics(
			array(
				'poll_schedule_state'  => 'scheduled',
				'cleanup_state'        => 'scheduled',
				'poll_interval_seconds' => 900,
				'poll_batch_size'      => 20,
				'retention_days'       => 30,
				'global_lock_active'   => false,
			),
			array(),
			array(),
			array(
				'settings' => array(),
				'summary'  => array(),
				'audit'    => array(
					'summary' => array(),
				),
			),
			1789843200,
			$remote_actions
		);

		return $support['actions'];
	}
}
