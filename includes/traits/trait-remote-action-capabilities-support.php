<?php
/**
 * Remote action capability support checks.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.59
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects support for allowlisted remote action capabilities.
 *
 * @since 0.1.59
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Support {
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
	 * Gets whether sanitized capabilities allow a non-mutating rollback preview action.
	 *
	 * @since 0.1.43
	 *
	 * @param array<string,mixed> $capabilities Sanitized capabilities.
	 * @param string              $schedule_id Schedule ID.
	 * @return bool
	 */
	public function supports_schedule_rollback_preview_action( array $capabilities, $schedule_id ) {
		if (
			empty( $capabilities['enabled'] )
			|| empty( $capabilities['sodium_available'] )
			|| empty( $capabilities['allowed_actions'] )
			|| ! in_array( self::ACTION_SCHEDULE_ROLLBACK_PREVIEW, (array) $capabilities['allowed_actions'], true )
			|| empty( $capabilities['schedule_management'] )
			|| ! is_array( $capabilities['schedule_management'] )
			|| empty( $capabilities['schedule_management']['enabled'] )
			|| empty( $capabilities['schedule_management']['rollback_preview_supported'] )
			|| ! empty( $capabilities['schedule_management']['rollback_supported'] )
		) {
			return false;
		}

		$schedule_id = sanitize_key( (string) $schedule_id );
		$schedules   = isset( $capabilities['schedule_management']['schedules'] ) && is_array( $capabilities['schedule_management']['schedules'] ) ? $capabilities['schedule_management']['schedules'] : array();

		foreach ( $schedules as $schedule ) {
			if (
				is_array( $schedule )
				&& self::SCHEDULE_SCAN_UPLOAD === $schedule_id
				&& ( isset( $schedule['schedule_id'] ) ? sanitize_key( (string) $schedule['schedule_id'] ) : '' ) === $schedule_id
				&& ! empty( $schedule['manageable'] )
				&& ! empty( $schedule['rollback_preview_supported'] )
				&& empty( $schedule['rollback_supported'] )
			) {
				return true;
			}
		}

		return false;
	}
}
