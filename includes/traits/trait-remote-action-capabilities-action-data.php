<?php
/**
 * Remote action capability action-history sanitizers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes remote action action-history summary payload sections.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Action_Data {


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

		$result_code    = isset( $action['result_code'] ) ? (string) $action['result_code'] : ( isset( $action['code'] ) ? (string) $action['code'] : '' );
		$result_summary = isset( $action['result_summary'] ) ? (string) $action['result_summary'] : ( isset( $action['summary'] ) ? (string) $action['summary'] : '' );

		return array(
			'action_id'                 => $action_id,
			'action_type'               => $action_type,
			'state'                     => $this->sanitize_state( isset( $action['state'] ) ? (string) $action['state'] : '' ),
			'requested_at'              => isset( $action['requested_at'] ) ? sanitize_text_field( (string) $action['requested_at'] ) : '',
			'completed_at'              => isset( $action['completed_at'] ) ? sanitize_text_field( (string) $action['completed_at'] ) : '',
			'updated_at'                => isset( $action['updated_at'] ) ? sanitize_text_field( (string) $action['updated_at'] ) : '',
			'result_code'               => sanitize_key( $result_code ),
			'result_summary'            => $this->bounded_text( $result_summary, self::MAX_RESULT_SUMMARY_LENGTH ),
			'counts'                    => $this->counts( isset( $action['counts'] ) ? $action['counts'] : array() ),
			'schedule_preview'          => $this->schedule_preview( isset( $action['schedule_preview'] ) ? $action['schedule_preview'] : array() ),
			'schedule_apply'            => $this->schedule_apply( isset( $action['schedule_apply'] ) ? $action['schedule_apply'] : array() ),
			'schedule_rollback_preview' => $this->schedule_rollback_preview( isset( $action['schedule_rollback_preview'] ) ? $action['schedule_rollback_preview'] : array() ),
			'cleanup_preview'           => $this->cleanup_preview( isset( $action['cleanup_preview'] ) ? $action['cleanup_preview'] : array() ),
		);
	}
}
