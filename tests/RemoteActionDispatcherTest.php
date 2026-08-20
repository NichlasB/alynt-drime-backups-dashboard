<?php
/**
 * Remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

if ( ! function_exists( 'current_time' ) ) {
	/**
	 * Test current_time shim.
	 *
	 * @param string $type Type.
	 * @param bool   $gmt GMT.
	 * @return string
	 */
	function current_time( $type, $gmt = false ) {
		unset( $type, $gmt );
		return '2099-01-01 00:00:00';
	}
}

/**
 * Fake wpdb for dispatcher tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB {
	/**
	 * Prefix.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * Insert ID.
	 *
	 * @var int
	 */
	public $insert_id = 44;

	/**
	 * Site row.
	 *
	 * @var array<string,mixed>
	 */
	public $site = array();

	/**
	 * Snapshot row.
	 *
	 * @var array<string,mixed>
	 */
	public $snapshot = array();

	/**
	 * Inserted action.
	 *
	 * @var array<string,mixed>
	 */
	public $inserted_data = array();

	/**
	 * Update calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $updates = array();

	/**
	 * Prepares SQL.
	 *
	 * @param string $query Query.
	 * @param mixed  ...$args Args.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		unset( $args );
		return $query;
	}

	/**
	 * Gets a row.
	 *
	 * @param string $query Query.
	 * @param string $output Output.
	 * @return array<string,mixed>|null
	 */
	public function get_row( $query, $output = ARRAY_A ) {
		unset( $output );

		if ( false !== strpos( $query, 'alynt_drime_dashboard_snapshots' ) ) {
			return empty( $this->snapshot ) ? null : $this->snapshot;
		}

		return empty( $this->site ) ? null : $this->site;
	}

	/**
	 * Inserts a row.
	 *
	 * @param string              $table Table.
	 * @param array<string,mixed> $data Data.
	 * @return int
	 */
	public function insert( $table, $data ) {
		unset( $table );
		$this->inserted_data = $data;

		return 1;
	}

	/**
	 * Updates a row.
	 *
	 * @param string              $table Table.
	 * @param array<string,mixed> $data Data.
	 * @param array<string,mixed> $where Where.
	 * @return int
	 */
	public function update( $table, $data, $where ) {
		unset( $table );
		$this->updates[] = array(
			'data'  => $data,
			'where' => $where,
		);

		return 1;
	}
}

/**
 * Test vault.
 */
class Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Vault extends Alynt_Drime_Backups_Dashboard_Credential_Vault {
	/**
	 * Decrypts fixed private key.
	 *
	 * @param string $stored Stored.
	 * @param string $context Context.
	 * @return string
	 */
	public function decrypt( $stored, $context = 'polling' ) {
		unset( $stored, $context );
		return 'private-key';
	}
}

/**
 * Test signer.
 */
class Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Signer extends Alynt_Drime_Backups_Dashboard_Remote_Action_Signer {
	/**
	 * Whether supported.
	 *
	 * @return bool
	 */
	public function is_supported() {
		return true;
	}

	/**
	 * Encodes canonical JSON.
	 *
	 * @param array<string,mixed> $body Body.
	 * @return string
	 */
	public function canonical_json( array $body ) {
		ksort( $body );
		return wp_json_encode( $body, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Signs input.
	 *
	 * @param string $private_key Private key.
	 * @param string $signing_input Signing input.
	 * @return string
	 */
	public function sign( $private_key, $signing_input ) {
		unset( $private_key );
		return 'sig_' . hash( 'sha256', $signing_input );
	}
}

/**
 * Tests signed remote action dispatch.
 */
class RemoteActionDispatcherTest extends TestCase {
	/**
	 * Previous wpdb.
	 *
	 * @var mixed
	 */
	private $previous_wpdb;

	/**
	 * Fake wpdb.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB
	 */
	private $wpdb;

	protected function setUp(): void {
		parent::setUp();

		global $wpdb;

		$this->previous_wpdb = $wpdb;
		$this->wpdb          = new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB();
		$this->wpdb->site    = $this->site_row();
		$this->wpdb->snapshot = $this->snapshot_row();
		$wpdb                = $this->wpdb;
	}

	protected function tearDown(): void {
		global $wpdb;

		$wpdb = $this->previous_wpdb;

		parent::tearDown();
	}

	public function test_dispatch_posts_signed_fixed_intent_and_records_acceptance() {
		$captured = array();
		$http     = function ( $url, $args ) use ( &$captured ) {
			$captured = array(
				'url'  => $url,
				'args' => $args,
			);
			$body     = json_decode( $args['body'], true );

			return array(
				'response' => array(
					'code' => 202,
				),
				'body'     => wp_json_encode(
					array(
						'protocol_version' => 2,
						'action_id'        => $body['action_id'],
						'state'            => 'accepted',
						'code'             => 'action_accepted',
						'summary'          => 'Accepted safely.',
						'retry_after'      => 0,
					)
				),
			);
		};

		$result = $this->dispatcher( $http )->request_scan_upload_now( 9, 7 );

		$this->assertIsArray( $result );
		$this->assertSame( 'request_backup_now', $result['action'] );
		$this->assertSame( 'accepted', $result['remote_state'] );
		$this->assertSame( 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v2/action-intents', $captured['url'] );
		$this->assertSame( 'POST', $captured['args']['method'] );
		$this->assertSame( 'application/json', $captured['args']['headers']['Content-Type'] );
		$this->assertSame( 'ak_test', $captured['args']['headers']['X-Adbd-Action-Key-Id'] );
		$this->assertStringStartsWith( 'sig_', $captured['args']['headers']['X-Adbd-Action-Signature'] );
		$this->assertSame( 'scan_upload_now', $this->wpdb->inserted_data['action_type'] );
		$last_update = end( $this->wpdb->updates );
		$this->assertSame( 'accepted', $last_update['data']['state'] );
		$this->assertStringNotContainsString( 'private-key', wp_json_encode( $this->wpdb->inserted_data ) );
	}

	public function test_missing_capability_fails_without_http_dispatch() {
		$this->wpdb->snapshot = array(
			'payload_json' => wp_json_encode(
				array(
					'remote_actions' => array(
						'protocol_version' => 2,
						'enabled'          => false,
					),
				)
			),
		);
		$called = false;
		$http   = function () use ( &$called ) {
			$called = true;
			return array();
		};

		$result = $this->dispatcher( $http )->request_scan_upload_now( 9, 7 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'remote_action_capability_missing', $result->get_error_code() );
		$this->assertFalse( $called );
		$this->assertSame( array(), $this->wpdb->inserted_data );
	}

	public function test_mismatched_client_action_response_is_recorded_as_dispatch_failure() {
		$http = function ( $url, $args ) {
			unset( $url, $args );

			return array(
				'response' => array(
					'code' => 202,
				),
				'body'     => wp_json_encode(
					array(
						'protocol_version' => 2,
						'action_id'        => 'different-action-id',
						'state'            => 'accepted',
						'code'             => 'action_accepted',
						'summary'          => 'Accepted safely.',
						'retry_after'      => 0,
					)
				),
			);
		};

		$result = $this->dispatcher( $http )->request_scan_upload_now( 9, 7 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'remote_action_response_mismatch', $result->get_error_code() );
		$this->assertSame( 'scan_upload_now', $this->wpdb->inserted_data['action_type'] );
		$last_update = end( $this->wpdb->updates );
		$this->assertSame( 'dispatch_failed', $last_update['data']['state'] );
		$this->assertSame( 'remote_action_response_mismatch', $last_update['data']['result_code'] );
		$this->assertArrayHasKey( 'completed_at', $last_update['data'] );
	}

	public function test_client_rate_limit_response_is_recorded_as_rate_limited() {
		$http = function ( $url, $args ) {
			unset( $url );
			$body = json_decode( $args['body'], true );

			return array(
				'response' => array(
					'code' => 429,
				),
				'body'     => wp_json_encode(
					array(
						'protocol_version' => 2,
						'action_id'        => $body['action_id'],
						'state'            => 'rate_limited',
						'code'             => 'action_rate_limited',
						'summary'          => 'Rate limited safely.',
						'retry_after'      => 3600,
					)
				),
			);
		};

		$result = $this->dispatcher( $http )->request_scan_upload_now( 9, 7 );

		$this->assertIsArray( $result );
		$this->assertSame( 'rate_limited', $result['remote_state'] );
		$this->assertSame( 3600, $result['retry_after'] );
		$last_update = end( $this->wpdb->updates );
		$this->assertSame( 'rate_limited', $last_update['data']['state'] );
		$this->assertSame( 3600, $last_update['data']['retry_after_seconds'] );
	}

	/**
	 * Creates dispatcher.
	 *
	 * @param callable $http HTTP fake.
	 * @return Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher
	 */
	private function dispatcher( $http ) {
		return new Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher(
			new Alynt_Drime_Backups_Dashboard_Site_Repository(),
			new Alynt_Drime_Backups_Dashboard_Snapshot_Repository(),
			new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository(),
			new Alynt_Drime_Backups_Dashboard_Origin_Validator(),
			new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Vault(),
			new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Signer(),
			new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities(),
			$http,
			function () {
				return array( '93.184.216.34' );
			}
		);
	}

	/**
	 * Site fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function site_row() {
		return array(
			'id'                            => 9,
			'public_id'                     => '00000000-0000-4000-8000-000000000000',
			'site_uuid'                     => '11111111-1111-4111-8111-111111111111',
			'expected_origin'               => 'https://client.example.com',
			'enrollment_status'             => 'active',
			'polling_key_id'                => 'pk_test',
			'polling_secret_ciphertext'     => 'poll-cipher',
			'action_key_id'                 => 'ak_test',
			'action_private_key_ciphertext' => 'action-cipher',
		);
	}

	/**
	 * Snapshot fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function snapshot_row() {
		return array(
			'payload_json' => wp_json_encode(
				array(
					'remote_actions' => array(
						'protocol_version'            => 2,
						'enabled'                     => true,
						'key_id'                      => 'ak_test',
						'allowed_actions'             => array( 'scan_upload_now' ),
						'sodium_available'            => true,
						'min_interval_seconds'        => 3600,
						'one_running_action_per_site' => true,
					),
				)
			),
		);
	}
}
