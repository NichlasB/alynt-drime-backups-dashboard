<?php
/**
 * Test support for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared fixture builders for remote action dispatcher tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures {
	/**
	 * Creates dispatcher.
	 *
	 * @param callable|null                                 $http HTTP fake.
	 * @param callable|null                                 $resolver DNS resolver fake.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Repository|null $actions Actions repository.
	 * @return Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher
	 */
	private function dispatcher( $http = null, $resolver = null, $actions = null ) {
		return new Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher(
			new Alynt_Drime_Backups_Dashboard_Site_Repository(),
			new Alynt_Drime_Backups_Dashboard_Snapshot_Repository(),
			$actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository ? $actions : new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository(),
			new Alynt_Drime_Backups_Dashboard_Origin_Validator(),
			new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Vault(),
			new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Signer(),
			new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities(),
			$http,
			null === $resolver ? function () {
				return array( '93.184.216.34' );
			} : $resolver
		);
	}

	/**
	 * Site fixture.
	 *
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function site_row( array $overrides = array() ) {
		return array_merge(
			array(
				'id'                            => 9,
				'public_id'                     => '00000000-0000-4000-8000-000000000000',
				'site_uuid'                     => '11111111-1111-4111-8111-111111111111',
				'expected_origin'               => 'https://client.example.com',
				'enrollment_status'             => 'active',
				'polling_key_id'                => 'pk_test',
				'polling_secret_ciphertext'     => 'poll-cipher',
				'action_key_id'                 => 'ak_test',
				'action_private_key_ciphertext' => 'action-cipher',
			),
			$overrides
		);
	}

	/**
	 * Snapshot fixture.
	 *
	 * @param array<string,mixed> $remote_action_overrides Remote-action overrides.
	 * @return array<string,mixed>
	 */
	private function snapshot_row( array $remote_action_overrides = array() ) {
		$remote_actions = array_merge(
			array(
				'protocol_version'            => 2,
				'enabled'                     => true,
				'key_id'                      => 'ak_test',
				'allowed_actions'             => array( 'scan_upload_now', 'schedule_preview' ),
				'sodium_available'            => true,
				'min_interval_seconds'        => 3600,
				'one_running_action_per_site' => true,
				'preview_only'                => true,
				'apply_supported'             => false,
				'rollback_supported'          => false,
				'rollback_preview_supported'  => false,
			),
			$remote_action_overrides
		);

		return array(
			'payload_json' => wp_json_encode(
				array(
					'remote_actions' => array(
						'protocol_version'            => $remote_actions['protocol_version'],
						'enabled'                     => $remote_actions['enabled'],
						'key_id'                      => $remote_actions['key_id'],
						'allowed_actions'             => $remote_actions['allowed_actions'],
						'sodium_available'            => $remote_actions['sodium_available'],
						'min_interval_seconds'        => $remote_actions['min_interval_seconds'],
						'one_running_action_per_site' => $remote_actions['one_running_action_per_site'],
						'schedule_management'         => array(
							'protocol_version'           => 2,
							'capability_version'         => 1,
							'enabled'                    => true,
							'preview_only'               => $remote_actions['preview_only'],
							'apply_supported'            => $remote_actions['apply_supported'],
							'rollback_preview_supported' => $remote_actions['rollback_preview_supported'],
							'rollback_supported'         => $remote_actions['rollback_supported'],
							'schedules'                  => array(
								array(
									'schedule_id'                    => 'alynt_scan_upload',
									'label'                          => 'Alynt scan/upload',
									'owner'                          => 'alynt_uploader',
									'manageable'                     => true,
									'current_cadence'                => 'every_15_minutes',
									'current_interval_seconds'       => 900,
									'current_next_run_at'            => '2026-06-25T16:45:00+00:00',
									'supported_cadences'             => array( 'every_15_minutes', 'every_30_minutes', 'hourly' ),
									'minimum_interval_seconds'       => 900,
									'can_disable'                    => false,
									'requires_high_friction_disable' => true,
									'rollback_preview_supported'     => $remote_actions['rollback_preview_supported'],
									'rollback_supported'             => false,
								),
							),
						),
					),
				)
			),
		);
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
 * Test action repository with a fixed fresh preview.
 */
class Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Actions extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	/**
	 * Inserted requests.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $requests = array();

	/**
	 * Latest state update.
	 *
	 * @var array<string,mixed>
	 */
	public $latest_state = array();

	/**
	 * Returns a fixed fresh preview for apply.
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $preview_public_id Preview public ID.
	 * @param array<string,mixed> $capabilities Capabilities.
	 * @param string|null         $now Now.
	 * @return array<string,mixed>|WP_Error
	 */
	public function fresh_schedule_preview_for_apply( $site_id, $preview_public_id, array $capabilities, $now = null ) {
		unset( $site_id, $capabilities, $now );

		if ( '22222222-2222-4222-8222-222222222222' !== $preview_public_id ) {
			return new WP_Error( 'schedule_apply_preview_missing', 'Missing preview.' );
		}

		return array(
			'preview_action_id'   => $preview_public_id,
			'preview_fingerprint' => str_repeat( 'a', 64 ),
			'schedule_id'         => 'alynt_scan_upload',
			'current_cadence'     => 'every_15_minutes',
			'proposed_cadence'    => 'every_30_minutes',
			'capability_version'  => 1,
		);
	}

	/**
	 * Returns a fixed successful apply for rollback preview.
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $apply_public_id Apply public ID.
	 * @param array<string,mixed> $capabilities Capabilities.
	 * @param string|null         $now Now.
	 * @return array<string,mixed>|WP_Error
	 */
	public function successful_schedule_apply_for_rollback_preview( $site_id, $apply_public_id, array $capabilities, $now = null ) {
		unset( $site_id, $capabilities, $now );

		if ( '33333333-3333-4333-8333-333333333333' !== $apply_public_id ) {
			return new WP_Error( 'schedule_rollback_preview_apply_missing', 'Missing apply.' );
		}

		return array(
			'source_apply_action_id'         => $apply_public_id,
			'rollback_metadata_fingerprint' => str_repeat( 'b', 64 ),
			'schedule_id'                   => 'alynt_scan_upload',
			'previous_cadence'              => 'every_15_minutes',
			'applied_cadence'               => 'every_30_minutes',
			'capability_version'            => 1,
			'metadata_expires_at'           => '2099-01-01T00:15:00+00:00',
		);
	}

	/**
	 * Stores request context.
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $action_type Action type.
	 * @param int                 $requested_by User ID.
	 * @param string              $idempotency_key Idempotency key.
	 * @param string              $action_key_id Action key ID.
	 * @param string              $expires_at Expiry.
	 * @param string              $request_fingerprint Fingerprint.
	 * @param array<string,mixed> $context Context.
	 * @param string              $public_id Public ID.
	 * @return int
	 */
	public function create_request( $site_id, $action_type, $requested_by, $idempotency_key, $action_key_id, $expires_at, $request_fingerprint = '', array $context = array(), $public_id = '' ) {
		$this->requests[] = compact( 'site_id', 'action_type', 'requested_by', 'idempotency_key', 'action_key_id', 'expires_at', 'request_fingerprint', 'context', 'public_id' );

		return 55;
	}

	/**
	 * Marks dispatch.
	 *
	 * @param int $action_id Action ID.
	 * @return bool
	 */
	public function mark_dispatched( $action_id ) {
		unset( $action_id );
		return true;
	}

	/**
	 * Marks state.
	 *
	 * @param int    $action_id Action ID.
	 * @param string $state State.
	 * @param string $result_code Result code.
	 * @param string $result_summary Summary.
	 * @param int    $retry_after_seconds Retry after.
	 * @return bool
	 */
	public function mark_state( $action_id, $state, $result_code = '', $result_summary = '', $retry_after_seconds = 0 ) {
		$this->latest_state = compact( 'action_id', 'state', 'result_code', 'result_summary', 'retry_after_seconds' );

		return true;
	}
}
