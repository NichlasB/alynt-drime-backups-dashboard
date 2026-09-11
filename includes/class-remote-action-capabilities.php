<?php
/**
 * Remote action capability sanitizer.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates optional V2 remote-action capability summaries.
 *
 * @since 0.1.15
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities {
	const PROTOCOL_VERSION       = 2;
	const ACTION_SCAN_UPLOAD_NOW = 'scan_upload_now';

	const MAX_ALLOWED_ACTIONS       = 5;
	const MAX_RESULT_SUMMARY_LENGTH = 160;
	const MAX_SCHEDULES             = 5;
	const MAX_SCHEDULE_CADENCES     = 10;
	const MAX_SCHEDULE_LABEL_LENGTH = 80;

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
	 * @since 0.1.15
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

		$schedule_management = $this->schedule_management( isset( $payload['schedule_management'] ) ? $payload['schedule_management'] : array() );

		if ( ! empty( $schedule_management ) ) {
			$clean['schedule_management'] = $schedule_management;
		}

		return $clean;
	}

	/**
	 * Gets whether sanitized capabilities allow the initial V2.1 action.
	 *
	 * @since 0.1.15
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
	 * Gets whether sanitized capabilities report preview-only schedule management.
	 *
	 * @since 0.1.16
	 *
	 * @param array<string,mixed> $capabilities Sanitized capabilities.
	 * @return bool
	 */
	public function supports_schedule_management_preview( array $capabilities ) {
		$schedule_management = isset( $capabilities['schedule_management'] ) && is_array( $capabilities['schedule_management'] ) ? $capabilities['schedule_management'] : array();

		return ! empty( $schedule_management['enabled'] )
			&& ! empty( $schedule_management['preview_only'] )
			&& empty( $schedule_management['apply_supported'] )
			&& empty( $schedule_management['rollback_supported'] )
			&& ! empty( $schedule_management['schedules'] )
			&& is_array( $schedule_management['schedules'] );
	}

	/**
	 * Sanitizes an action type.
	 *
	 * @since 0.1.15
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
	 * @since 0.1.15
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
			'updated_at'     => isset( $action['updated_at'] ) ? sanitize_text_field( (string) $action['updated_at'] ) : '',
			'result_code'    => isset( $action['result_code'] ) ? sanitize_key( (string) $action['result_code'] ) : '',
			'result_summary' => $this->bounded_text( isset( $action['result_summary'] ) ? (string) $action['result_summary'] : '', self::MAX_RESULT_SUMMARY_LENGTH ),
			'counts'         => $this->counts( isset( $action['counts'] ) ? $action['counts'] : array() ),
		);
	}

	/**
	 * Sanitizes preview-only schedule-management capability reporting.
	 *
	 * @param mixed $capability Schedule-management capability payload.
	 * @return array<string,mixed>
	 */
	private function schedule_management( $capability ) {
		if ( ! is_array( $capability ) ) {
			return array();
		}

		if ( self::PROTOCOL_VERSION !== absint( isset( $capability['protocol_version'] ) ? $capability['protocol_version'] : 0 ) ) {
			return array();
		}

		$schedules    = $this->schedules( isset( $capability['schedules'] ) ? $capability['schedules'] : array() );
		$preview_safe = ! empty( $capability['preview_only'] ) && empty( $capability['apply_supported'] ) && empty( $capability['rollback_supported'] );

		return array(
			'protocol_version'   => self::PROTOCOL_VERSION,
			'capability_version' => $this->non_negative_int( $capability, 'capability_version' ),
			'enabled'            => ! empty( $capability['enabled'] ) && $preview_safe && ! empty( $schedules ),
			'preview_only'       => ! empty( $capability['preview_only'] ),
			'apply_supported'    => false,
			'rollback_supported' => false,
			'schedules'          => $schedules,
		);
	}

	/**
	 * Sanitizes schedule summaries.
	 *
	 * @param mixed $schedules Schedule summaries.
	 * @return array<int,array<string,mixed>>
	 */
	private function schedules( $schedules ) {
		if ( ! is_array( $schedules ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $schedules, 0, self::MAX_SCHEDULES ) as $schedule ) {
			if ( ! is_array( $schedule ) ) {
				continue;
			}

			$schedule_id = isset( $schedule['schedule_id'] ) ? sanitize_key( (string) $schedule['schedule_id'] ) : '';

			if ( '' === $schedule_id ) {
				continue;
			}

			$clean[] = array(
				'schedule_id'                    => $schedule_id,
				'label'                          => $this->bounded_text( isset( $schedule['label'] ) ? (string) $schedule['label'] : '', self::MAX_SCHEDULE_LABEL_LENGTH ),
				'owner'                          => $this->sanitize_schedule_owner( isset( $schedule['owner'] ) ? (string) $schedule['owner'] : '' ),
				'manageable'                     => ! empty( $schedule['manageable'] ),
				'current_cadence'                => isset( $schedule['current_cadence'] ) ? sanitize_key( (string) $schedule['current_cadence'] ) : '',
				'current_interval_seconds'       => $this->non_negative_int( $schedule, 'current_interval_seconds' ),
				'current_next_run_at'            => isset( $schedule['current_next_run_at'] ) ? sanitize_text_field( (string) $schedule['current_next_run_at'] ) : '',
				'supported_cadences'             => $this->schedule_cadences( isset( $schedule['supported_cadences'] ) ? $schedule['supported_cadences'] : array() ),
				'minimum_interval_seconds'       => $this->non_negative_int( $schedule, 'minimum_interval_seconds' ),
				'can_disable'                    => ! empty( $schedule['can_disable'] ),
				'requires_high_friction_disable' => ! empty( $schedule['requires_high_friction_disable'] ),
				'rollback_supported'             => false,
			);
		}

		return $clean;
	}

	/**
	 * Sanitizes schedule owner labels.
	 *
	 * @param string $owner Owner.
	 * @return string
	 */
	private function sanitize_schedule_owner( $owner ) {
		$owner   = sanitize_key( $owner );
		$allowed = array(
			'alynt_uploader',
			'wpvivid',
			'wordpress',
			'unknown',
			'',
		);

		return in_array( $owner, $allowed, true ) ? $owner : 'unknown';
	}

	/**
	 * Sanitizes supported cadence labels.
	 *
	 * @param mixed $cadences Cadence list.
	 * @return array<int,string>
	 */
	private function schedule_cadences( $cadences ) {
		if ( ! is_array( $cadences ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $cadences, 0, self::MAX_SCHEDULE_CADENCES ) as $cadence ) {
			$cadence = sanitize_key( (string) $cadence );

			if ( '' !== $cadence && ! in_array( $cadence, $clean, true ) ) {
				$clean[] = $cadence;
			}
		}

		return $clean;
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
