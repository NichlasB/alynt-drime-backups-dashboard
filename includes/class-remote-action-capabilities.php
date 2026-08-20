<?php
/**
 * Remote action capability sanitizer.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates optional V2 remote-action capability summaries.
 *
 * @since 0.1.14
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities {
	const PROTOCOL_VERSION       = 2;
	const ACTION_SCAN_UPLOAD_NOW = 'scan_upload_now';

	const MAX_ALLOWED_ACTIONS       = 5;
	const MAX_RESULT_SUMMARY_LENGTH = 160;

	/**
	 * Allowed action states.
	 *
	 * @var array<int,string>
	 */
	private $allowed_states = array(
		'queued_for_dispatch',
		'dispatch_failed',
		'accepted',
		'rejected',
		'unsupported',
		'rate_limited',
		'busy',
		'running',
		'succeeded',
		'failed',
		'timed_out',
		'stale',
	);

	/**
	 * Fields that must never enter remote-action summaries.
	 *
	 * @var array<int,string>
	 */
	private $forbidden_fields = array(
		'api_token',
		'authorization',
		'backup_id',
		'backup_name',
		'backup_path',
		'backup_set_id',
		'body',
		'checksum_path',
		'command',
		'cookie',
		'drime_id',
		'file',
		'filename',
		'local_path',
		'manifest_path',
		'nonce',
		'package_id',
		'package_name',
		'password',
		'path',
		'private_key',
		'raw_response',
		'remote_catalog_path',
		'remote_index_path',
		'secret',
		'signature',
		'signed_url',
		'sql',
		'token',
	);

	/**
	 * Sanitizes a remote-action capability summary.
	 *
	 * @since 0.1.14
	 *
	 * @param mixed $payload Raw remote_actions value.
	 * @return array<string,mixed>|WP_Error
	 */
	public function sanitize( $payload ) {
		if ( ! is_array( $payload ) ) {
			return array();
		}

		if ( $this->contains_forbidden_field( $payload ) ) {
			return new WP_Error( 'payload_invalid', __( 'The remote action summary contains a forbidden field.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( self::PROTOCOL_VERSION !== absint( isset( $payload['protocol_version'] ) ? $payload['protocol_version'] : 0 ) ) {
			return array();
		}

		$allowed_actions = $this->allowed_actions( isset( $payload['allowed_actions'] ) ? $payload['allowed_actions'] : array() );

		$clean = array(
			'protocol_version'            => self::PROTOCOL_VERSION,
			'enabled'                     => ! empty( $payload['enabled'] ),
			'key_id'                      => isset( $payload['key_id'] ) ? $this->bounded_identifier( (string) $payload['key_id'], 128 ) : '',
			'allowed_actions'             => $allowed_actions,
			'sodium_available'            => ! empty( $payload['sodium_available'] ),
			'min_interval_seconds'        => $this->non_negative_int( $payload, 'min_interval_seconds' ),
			'one_running_action_per_site' => ! empty( $payload['one_running_action_per_site'] ),
		);

		$last_action = $this->last_action( isset( $payload['last_action'] ) ? $payload['last_action'] : array() );

		if ( ! empty( $last_action ) ) {
			$clean['last_action'] = $last_action;
		}

		return $clean;
	}

	/**
	 * Gets whether sanitized capabilities allow the initial V2.1 action.
	 *
	 * @since 0.1.14
	 *
	 * @param array<string,mixed> $capabilities Sanitized capabilities.
	 * @return bool
	 */
	public function supports_scan_upload_now( array $capabilities ) {
		return ! empty( $capabilities['enabled'] )
			&& ! empty( $capabilities['sodium_available'] )
			&& ! empty( $capabilities['allowed_actions'] )
			&& in_array( self::ACTION_SCAN_UPLOAD_NOW, (array) $capabilities['allowed_actions'], true );
	}

	/**
	 * Sanitizes an action type.
	 *
	 * @since 0.1.14
	 *
	 * @param string $action_type Action type.
	 * @return string
	 */
	public function sanitize_action_type( $action_type ) {
		$action_type = sanitize_key( (string) $action_type );

		return self::ACTION_SCAN_UPLOAD_NOW === $action_type ? $action_type : '';
	}

	/**
	 * Sanitizes an action state.
	 *
	 * @since 0.1.14
	 *
	 * @param string $state Action state.
	 * @return string
	 */
	public function sanitize_state( $state ) {
		$state = sanitize_key( (string) $state );

		return in_array( $state, $this->allowed_states, true ) ? $state : 'queued_for_dispatch';
	}

	/**
	 * Recursively detects forbidden keys.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return bool
	 */
	private function contains_forbidden_field( array $payload ) {
		foreach ( $payload as $key => $value ) {
			if ( in_array( sanitize_key( (string) $key ), $this->forbidden_fields, true ) ) {
				return true;
			}

			if ( is_array( $value ) && $this->contains_forbidden_field( $value ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sanitizes allowed action identifiers.
	 *
	 * @param mixed $actions Actions.
	 * @return array<int,string>
	 */
	private function allowed_actions( $actions ) {
		if ( ! is_array( $actions ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $actions, 0, self::MAX_ALLOWED_ACTIONS ) as $action ) {
			$action = $this->sanitize_action_type( (string) $action );

			if ( '' !== $action && ! in_array( $action, $clean, true ) ) {
				$clean[] = $action;
			}
		}

		return $clean;
	}

	/**
	 * Sanitizes a last-action summary.
	 *
	 * @param mixed $action Action summary.
	 * @return array<string,mixed>
	 */
	private function last_action( $action ) {
		if ( ! is_array( $action ) ) {
			return array();
		}

		$action_type = $this->sanitize_action_type( isset( $action['action_type'] ) ? (string) $action['action_type'] : '' );
		$action_id   = isset( $action['action_id'] ) ? $this->sanitize_uuid( (string) $action['action_id'] ) : '';

		if ( '' === $action_type || '' === $action_id ) {
			return array();
		}

		return array(
			'action_id'      => $action_id,
			'action_type'    => $action_type,
			'state'          => $this->sanitize_state( isset( $action['state'] ) ? (string) $action['state'] : '' ),
			'requested_at'   => isset( $action['requested_at'] ) ? sanitize_text_field( (string) $action['requested_at'] ) : '',
			'completed_at'   => isset( $action['completed_at'] ) ? sanitize_text_field( (string) $action['completed_at'] ) : '',
			'result_code'    => isset( $action['result_code'] ) ? sanitize_key( (string) $action['result_code'] ) : '',
			'result_summary' => $this->bounded_text( isset( $action['result_summary'] ) ? (string) $action['result_summary'] : '', self::MAX_RESULT_SUMMARY_LENGTH ),
			'counts'         => $this->counts( isset( $action['counts'] ) ? $action['counts'] : array() ),
		);
	}

	/**
	 * Sanitizes bounded count summaries.
	 *
	 * @param mixed $counts Counts.
	 * @return array<string,int>
	 */
	private function counts( $counts ) {
		if ( ! is_array( $counts ) ) {
			return array();
		}

		$clean = array();

		foreach ( array( 'found', 'queued', 'already_known', 'upload_attempted', 'failed' ) as $key ) {
			$clean[ $key ] = $this->non_negative_int( $counts, $key );
		}

		return $clean;
	}

	/**
	 * Gets a non-negative integer field.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $field Field.
	 * @return int
	 */
	private function non_negative_int( array $payload, $field ) {
		return isset( $payload[ $field ] ) ? max( 0, (int) $payload[ $field ] ) : 0;
	}

	/**
	 * Sanitizes a bounded identifier.
	 *
	 * @param string $value Raw value.
	 * @param int    $max_length Maximum length.
	 * @return string
	 */
	private function bounded_identifier( $value, $max_length ) {
		return substr( preg_replace( '/[^A-Za-z0-9_\-\.]/', '', (string) $value ), 0, max( 1, (int) $max_length ) );
	}

	/**
	 * Sanitizes a UUID.
	 *
	 * @param string $uuid UUID.
	 * @return string
	 */
	private function sanitize_uuid( $uuid ) {
		$uuid = strtolower( trim( (string) $uuid ) );

		return preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $uuid ) ? $uuid : '';
	}

	/**
	 * Sanitizes and bounds text.
	 *
	 * @param string $value Raw value.
	 * @param int    $max_length Maximum length.
	 * @return string
	 */
	private function bounded_text( $value, $max_length ) {
		$value      = sanitize_text_field( (string) $value );
		$max_length = max( 1, (int) $max_length );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max_length );
		}

		return substr( $value, 0, $max_length );
	}
}
