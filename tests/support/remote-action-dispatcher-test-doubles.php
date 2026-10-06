<?php
/**
 * Test doubles for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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
