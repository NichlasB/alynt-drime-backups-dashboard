<?php
/**
 * Remote action capability helper trait.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Handles public remote-action capability parsing and support checks.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Public {


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
			&& empty( $schedule_management['rollback_supported'] )
			&& ! empty( $schedule_management['schedules'] )
			&& is_array( $schedule_management['schedules'] );
	}

	/**
	 * Gets whether sanitized capabilities allow the preview-only schedule action.
	 *
	 * @since 0.1.22
	 *
	 * @param array<string,mixed> $capabilities Sanitized capabilities.
	 * @param string              $schedule_id Schedule ID.
	 * @param string              $proposed_cadence Proposed cadence.
	 * @return bool
	 */
	public function supports_schedule_preview_action( array $capabilities, $schedule_id, $proposed_cadence ) {
		if (
			empty( $capabilities['enabled'] )
			|| empty( $capabilities['sodium_available'] )
			|| empty( $capabilities['allowed_actions'] )
			|| ! in_array( self::ACTION_SCHEDULE_PREVIEW, (array) $capabilities['allowed_actions'], true )
			|| ! $this->supports_schedule_management_preview( $capabilities )
		) {
			return false;
		}

		$schedule_id      = sanitize_key( (string) $schedule_id );
		$proposed_cadence = sanitize_key( (string) $proposed_cadence );
		$schedules        = isset( $capabilities['schedule_management']['schedules'] ) && is_array( $capabilities['schedule_management']['schedules'] ) ? $capabilities['schedule_management']['schedules'] : array();

		foreach ( $schedules as $schedule ) {
			if (
				is_array( $schedule )
				&& ( isset( $schedule['schedule_id'] ) ? sanitize_key( (string) $schedule['schedule_id'] ) : '' ) === $schedule_id
				&& ! empty( $schedule['manageable'] )
				&& ! empty( $schedule['supported_cadences'] )
				&& in_array( $proposed_cadence, (array) $schedule['supported_cadences'], true )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets whether sanitized capabilities allow a guarded schedule apply action.
	 *
	 * @since 0.1.24
	 *
	 * @param array<string,mixed> $capabilities Sanitized capabilities.
	 * @param string              $schedule_id Schedule ID.
	 * @param string              $proposed_cadence Proposed cadence.
	 * @return bool
	 */
	public function supports_schedule_apply_action( array $capabilities, $schedule_id, $proposed_cadence ) {
		if (
			empty( $capabilities['enabled'] )
			|| empty( $capabilities['sodium_available'] )
			|| empty( $capabilities['allowed_actions'] )
			|| ! in_array( self::ACTION_SCHEDULE_APPLY, (array) $capabilities['allowed_actions'], true )
			|| empty( $capabilities['schedule_management'] )
			|| ! is_array( $capabilities['schedule_management'] )
			|| empty( $capabilities['schedule_management']['enabled'] )
			|| empty( $capabilities['schedule_management']['apply_supported'] )
			|| ! empty( $capabilities['schedule_management']['rollback_supported'] )
		) {
			return false;
		}

		$schedule_id      = sanitize_key( (string) $schedule_id );
		$proposed_cadence = sanitize_key( (string) $proposed_cadence );
		$schedules        = isset( $capabilities['schedule_management']['schedules'] ) && is_array( $capabilities['schedule_management']['schedules'] ) ? $capabilities['schedule_management']['schedules'] : array();

		foreach ( $schedules as $schedule ) {
			if (
				is_array( $schedule )
				&& self::SCHEDULE_SCAN_UPLOAD === $schedule_id
				&& ( isset( $schedule['schedule_id'] ) ? sanitize_key( (string) $schedule['schedule_id'] ) : '' ) === $schedule_id
				&& ! empty( $schedule['manageable'] )
				&& ! empty( $schedule['supported_cadences'] )
				&& in_array( $proposed_cadence, (array) $schedule['supported_cadences'], true )
				&& empty( $schedule['can_disable'] )
			) {
				return true;
			}
		}

		return false;
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

		return in_array( $action_type, array( self::ACTION_SCAN_UPLOAD_NOW, self::ACTION_SCHEDULE_PREVIEW, self::ACTION_SCHEDULE_APPLY, self::ACTION_SCHEDULE_ROLLBACK_PREVIEW ), true ) ? $action_type : '';
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
}
