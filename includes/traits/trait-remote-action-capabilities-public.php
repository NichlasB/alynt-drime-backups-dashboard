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
Handles public remote-action capability parsing and sanitizers.
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

		$cleanup_management = $this->cleanup_management( isset( $payload['cleanup_management'] ) ? $payload['cleanup_management'] : array() );

		if ( ! empty( $cleanup_management ) ) {
			$clean['cleanup_management'] = $cleanup_management;
		}

		return $clean;
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

		return in_array( $action_type, array( self::ACTION_SCAN_UPLOAD_NOW, self::ACTION_SCHEDULE_PREVIEW, self::ACTION_SCHEDULE_APPLY, self::ACTION_SCHEDULE_ROLLBACK_PREVIEW, self::ACTION_CLEANUP_PREVIEW ), true ) ? $action_type : '';
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
