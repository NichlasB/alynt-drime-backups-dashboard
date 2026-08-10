<?php
/**
 * Diagnostics tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-site-repository.php';
require_once dirname( __DIR__ ) . '/includes/class-snapshot-repository.php';
require_once dirname( __DIR__ ) . '/includes/class-status-classifier.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-enrollment-rest-responses.php';
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
	 * Constructor.
	 *
	 * @param array<int,array<string,mixed>> $snapshots Snapshots.
	 */
	public function __construct( array $snapshots ) {
		$this->snapshots = $snapshots;
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
		$this->assertSame( 1, $result['counts']['due_now'] );
		$this->assertSame( 1, $result['counts']['missing_credentials'] );
		$this->assertSame( 1, $result['counts']['paused'] );
		$this->assertSame( 1, $result['counts']['with_failures'] );
		$this->assertSame( 'unavailable', $result['scheduler']['poll_schedule_state'] );
		$this->assertSame( 30, $result['scheduler']['retention_days'] );
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
	private function snapshot() {
		return array(
			'schema_version'   => 1,
			'observed_at'      => gmdate( 'Y-m-d H:i:s' ),
			'decoded_payload'  => array(
				'schema_version'           => 1,
				'server_outbox_configured' => true,
				'failed_count'             => 0,
				'warning_count'            => 0,
				'warnings'                 => array(),
				'cron_status'              => 'ok',
			),
		);
	}
}
