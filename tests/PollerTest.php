<?php
/**
 * Poller tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-origin-validator.php';
require_once dirname( __DIR__ ) . '/includes/class-pairing-tokens.php';
require_once dirname( __DIR__ ) . '/includes/class-credential-vault.php';
require_once dirname( __DIR__ ) . '/includes/class-site-repository.php';
require_once dirname( __DIR__ ) . '/includes/class-snapshot-repository.php';
require_once dirname( __DIR__ ) . '/includes/class-status-classifier.php';
require_once dirname( __DIR__ ) . '/includes/class-status-payload-validator.php';
require_once dirname( __DIR__ ) . '/includes/class-safe-transport.php';
require_once dirname( __DIR__ ) . '/includes/class-poller.php';

/**
 * Fake site repository for poller tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Site.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site;

	/**
	 * Sites keyed by ID.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $sites = array();

	/**
	 * Due sites.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $due_sites = array();

	/**
	 * Success data.
	 *
	 * @var array<string,mixed>
	 */
	public $success = array();

	/**
	 * Success rows.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $successes = array();

	/**
	 * Failure data.
	 *
	 * @var array<string,mixed>
	 */
	public $failure = array();

	/**
	 * Failure rows.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $failures = array();

	/**
	 * Last due-for-poll query.
	 *
	 * @var array<string,mixed>
	 */
	public $due_query = array();

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed>|array<int,array<string,mixed>>|null $site Site.
	 */
	public function __construct( $site ) {
		if ( is_array( $site ) && isset( $site[0] ) && is_array( $site[0] ) ) {
			foreach ( $site as $row ) {
				$this->sites[ (int) $row['id'] ] = $row;
			}

			$this->due_sites = array_values( $this->sites );
			$this->site      = reset( $this->sites );
		} else {
			$this->site = $site;

			if ( is_array( $site ) && isset( $site['id'] ) ) {
				$this->sites[ (int) $site['id'] ] = $site;
			}
		}
	}

	/**
	 * Gets the fake site.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function get( $site_id ) {
		return isset( $this->sites[ (int) $site_id ] ) ? $this->sites[ (int) $site_id ] : null;
	}

	/**
	 * Gets due sites.
	 *
	 * @param int    $limit Limit.
	 * @param string $now Now.
	 * @return array<int,array<string,mixed>>
	 */
	public function due_for_poll( $limit = 5, $now = '' ) {
		$this->due_query = array(
			'limit' => $limit,
			'now'   => $now,
		);

		return array_slice( $this->due_sites, 0, (int) $limit );
	}

	/**
	 * Marks success.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $status Status.
	 * @param string $plugin_version Plugin version.
	 * @param string $next_poll_at Next poll.
	 * @return bool
	 */
	public function mark_poll_success( $site_id, $status, $plugin_version = '', $next_poll_at = '' ) {
		$this->success = array(
			'site_id'        => $site_id,
			'status'         => $status,
			'plugin_version' => $plugin_version,
			'next_poll_at'   => $next_poll_at,
		);
		$this->successes[] = $this->success;

		return true;
	}

	/**
	 * Marks failure.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $error_code Error code.
	 * @param string $summary Summary.
	 * @param string $next_poll_at Next poll.
	 * @param int    $consecutive_failures Consecutive failures.
	 * @return bool
	 */
	public function mark_poll_failure( $site_id, $error_code, $summary = '', $next_poll_at = '', $consecutive_failures = 1 ) {
		$this->failure = array(
			'site_id'              => $site_id,
			'error_code'           => $error_code,
			'summary'              => $summary,
			'next_poll_at'         => $next_poll_at,
			'consecutive_failures' => $consecutive_failures,
		);
		$this->failures[] = $this->failure;

		return true;
	}
}

/**
 * Fake snapshot repository for poller tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository extends Alynt_Drime_Backups_Dashboard_Snapshot_Repository {
	/**
	 * Recorded snapshot.
	 *
	 * @var array<string,mixed>
	 */
	public $recorded = array();

	/**
	 * Records a fake snapshot.
	 *
	 * @param int    $site_id Site ID.
	 * @param array  $payload Payload.
	 * @param string $status_category Status.
	 * @return int
	 */
	public function record( $site_id, array $payload, $status_category ) {
		$this->recorded = array(
			'site_id' => $site_id,
			'payload' => $payload,
			'status'  => $status_category,
		);

		return 555;
	}
}

/**
 * Tests manual status checks.
 */
class PollerTest extends TestCase {
	/**
	 * Successful manual poll records snapshot and activates the site.
	 *
	 * @return void
	 */
	public function test_check_status_now_records_snapshot_and_marks_success() {
		$vault      = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$site       = $this->site( $vault );
		$sites      = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $site );
		$snapshots  = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$captured   = array();
		$http_client = function ( $url, $args ) use ( &$captured ) {
			$captured = array(
				'url'  => $url,
				'args' => $args,
			);

			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => wp_json_encode( $this->payload() ),
			);
		};
		$poller     = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertIsArray( $result );
		$this->assertSame( 'working', $result['category'] );
		$this->assertSame( 555, $result['snapshot_id'] );
		$this->assertSame( 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v1/status', $captured['url'] );
		$this->assertSame( 'GET', $captured['args']['method'] );
		$this->assertStringStartsWith( 'Bearer adb-poll-v1.pk_example_', $captured['args']['headers']['Authorization'] );
		$this->assertSame( 'working', $snapshots->recorded['status'] );
		$this->assertSame( 'working', $sites->success['status'] );
		$this->assertSame( '0.5.3', $sites->success['plugin_version'] );
		$this->assertNotEmpty( $sites->success['next_poll_at'] );
		$this->assertSame( array(), $sites->failure );
	}

	/**
	 * Scheduled polling processes only the bounded due-site batch.
	 *
	 * @return void
	 */
	public function test_scheduled_poll_processes_bounded_due_site_batch() {
		$vault = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$sites = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository(
			array(
				$this->site( $vault, array( 'id' => 77 ) ),
				$this->site( $vault, array( 'id' => 78 ) ),
				$this->site( $vault, array( 'id' => 79 ) ),
			)
		);
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$calls     = 0;

		$http_client = function () use ( &$calls ) {
			++$calls;

			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => wp_json_encode( $this->payload() ),
			);
		};
		$poller      = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->poll_sites( 2 );

		$this->assertSame( 2, $sites->due_query['limit'] );
		$this->assertSame( 2, $calls );
		$this->assertSame( 2, $result['processed'] );
		$this->assertSame( 2, $result['success'] );
		$this->assertSame( 0, $result['failure'] );
		$this->assertCount( 2, $sites->successes );
	}

	/**
	 * Invalid payload marks safe failure without recording a snapshot.
	 *
	 * @return void
	 */
	public function test_invalid_payload_marks_failure_without_snapshot() {
		$vault      = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$sites      = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $this->site( $vault ) );
		$snapshots  = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$http_client = function () {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => wp_json_encode(
					array_merge(
						$this->payload(),
						array(
							'site_uuid' => '22222222-2222-4222-8222-222222222222',
						)
					)
				),
			);
		};
		$poller     = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_uuid_mismatch', $result->get_error_code() );
		$this->assertSame( array(), $snapshots->recorded );
		$this->assertSame( 'site_uuid_mismatch', $sites->failure['error_code'] );
		$this->assertSame( 1, $sites->failure['consecutive_failures'] );
		$this->assertNotEmpty( $sites->failure['next_poll_at'] );
		$this->assertSame( array(), $sites->success );
	}

	/**
	 * Missing credentials fail before transport.
	 *
	 * @return void
	 */
	public function test_missing_polling_credentials_fail_before_transport() {
		$sites      = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository(
			array(
				'id'              => 77,
				'public_id'       => '00000000-0000-4000-8000-000000000000',
				'expected_origin' => 'https://client.example.com',
				'site_uuid'       => '11111111-1111-4111-8111-111111111111',
			)
		);
		$snapshots  = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$http_client = function () {
			$this->fail( 'HTTP client should not be called without credentials.' );
		};
		$poller     = $this->poller( $sites, $snapshots, new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) ), $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_missing', $result->get_error_code() );
		$this->assertSame( array(), $snapshots->recorded );
		$this->assertSame( 'auth_missing', $sites->failure['error_code'] );
	}

	/**
	 * Failure backoff increments the stored failure counter.
	 *
	 * @return void
	 */
	public function test_failure_backoff_increments_consecutive_failures() {
		$vault = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$sites = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository(
			$this->site(
				$vault,
				array(
					'consecutive_failures' => 2,
				)
			)
		);
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();

		$http_client = function () {
			return new WP_Error( 'transport_failed', 'Client status endpoint unavailable.' );
		};
		$poller      = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'transport_failed', $sites->failure['error_code'] );
		$this->assertSame( 3, $sites->failure['consecutive_failures'] );
		$this->assertNotEmpty( $sites->failure['next_poll_at'] );
		$this->assertSame( array(), $snapshots->recorded );
	}

	/**
	 * Creates a poller.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository     $sites Sites.
	 * @param Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository $snapshots Snapshots.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault                $vault Vault.
	 * @param callable                                                      $http_client HTTP client.
	 * @return Alynt_Drime_Backups_Dashboard_Poller
	 */
	private function poller( $sites, $snapshots, $vault, $http_client ) {
		return new Alynt_Drime_Backups_Dashboard_Poller(
			$sites,
			$snapshots,
			new Alynt_Drime_Backups_Dashboard_Status_Classifier(),
			$vault,
			new Alynt_Drime_Backups_Dashboard_Safe_Transport(),
			new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator(),
			$http_client
		);
	}

	/**
	 * Creates a dashboard site row.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault $vault Vault.
	 * @return array<string,mixed>
	 */
	private function site( $vault, $overrides = array() ) {
		$public_id = isset( $overrides['public_id'] ) ? (string) $overrides['public_id'] : '00000000-0000-4000-8000-000000000000';
		$site      = array(
			'id'                         => 77,
			'public_id'                  => $public_id,
			'expected_origin'            => 'https://client.example.com',
			'site_uuid'                  => '11111111-1111-4111-8111-111111111111',
			'polling_key_id'             => 'pk_example_0000000000000000',
			'polling_secret_ciphertext'  => $vault->encrypt( str_repeat( 'S', 43 ), 'site:' . $public_id ),
			'enrollment_status'          => 'awaiting_first_poll',
			'overall_status'             => 'pending',
			'consecutive_failures'       => 0,
		);

		return array_merge( $site, $overrides );
	}

	/**
	 * Creates a valid status payload.
	 *
	 * @return array<string,mixed>
	 */
	private function payload() {
		return array(
			'schema_version'              => 1,
			'site_uuid'                   => '11111111-1111-4111-8111-111111111111',
			'plugin_version'              => '0.5.3',
			'queue_count'                 => 0,
			'uploaded_count'              => 1,
			'failed_count'                => 0,
			'active_upload'               => false,
			'auto_scan_enabled'           => true,
			'server_cron_expected'        => false,
			'server_outbox_configured'    => true,
			'server_outbox_readable'      => true,
			'wpvivid_override_configured' => false,
			'old_wpvivid_uploader_active' => false,
			'wp_cron_disabled'            => false,
			'cron_status'                 => 'ok',
			'cron_reason'                 => 'Scheduled scans are available.',
			'warning_count'               => 0,
			'warnings'                    => array(),
			'last_runner'                 => 'wp_cron',
			'last_runner_at'              => 1786305600,
			'last_scheduled_scan_at'      => 1786305600,
			'last_wp_cli_scan_at'         => 0,
		);
	}
}
