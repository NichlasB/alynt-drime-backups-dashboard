<?php
/**
 * Remote action capability schedule-result sanitizers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.59
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes client-reported schedule preview/apply result payload sections.
 *
 * @since 0.1.59
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedule_Results {
	/**
	 * Sanitizes a client-reported schedule preview result.
	 *
	 * @param mixed $preview Preview result.
	 * @return array<string,mixed>
	 */
	private function schedule_preview( $preview ) {
		if ( ! is_array( $preview ) ) {
			return array();
		}

		return array(
			'schedule_id'                   => isset( $preview['schedule_id'] ) ? sanitize_key( (string) $preview['schedule_id'] ) : '',
			'label'                         => $this->bounded_text( isset( $preview['label'] ) ? (string) $preview['label'] : '', self::MAX_SCHEDULE_LABEL_LENGTH ),
			'owner'                         => $this->sanitize_schedule_owner( isset( $preview['owner'] ) ? (string) $preview['owner'] : '' ),
			'current_cadence'               => isset( $preview['current_cadence'] ) ? sanitize_key( (string) $preview['current_cadence'] ) : '',
			'proposed_cadence'              => isset( $preview['proposed_cadence'] ) ? sanitize_key( (string) $preview['proposed_cadence'] ) : '',
			'current_next_run_at'           => isset( $preview['current_next_run_at'] ) ? sanitize_text_field( (string) $preview['current_next_run_at'] ) : '',
			'proposed_next_run_estimate_at' => isset( $preview['proposed_next_run_estimate_at'] ) ? sanitize_text_field( (string) $preview['proposed_next_run_estimate_at'] ) : '',
			'would_change'                  => ! empty( $preview['would_change'] ),
			'apply_supported'               => ! empty( $preview['apply_supported'] ),
			'rollback_supported'            => false,
			'preview_action_id'             => isset( $preview['preview_action_id'] ) ? $this->sanitize_uuid( (string) $preview['preview_action_id'] ) : '',
			'preview_fingerprint'           => isset( $preview['preview_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['preview_fingerprint'] ) : '',
			'current_schedule_fingerprint'  => isset( $preview['current_schedule_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['current_schedule_fingerprint'] ) : '',
			'capability_version'            => $this->non_negative_int( $preview, 'capability_version' ),
			'preview_created_at'            => isset( $preview['preview_created_at'] ) ? sanitize_text_field( (string) $preview['preview_created_at'] ) : '',
			'preview_expires_at'            => isset( $preview['preview_expires_at'] ) ? sanitize_text_field( (string) $preview['preview_expires_at'] ) : '',
			'warnings'                      => $this->schedule_preview_warnings( isset( $preview['warnings'] ) ? $preview['warnings'] : array() ),
		);
	}

	/**
	 * Sanitizes a client-reported schedule apply result.
	 *
	 * @param mixed $apply Apply result.
	 * @return array<string,mixed>
	 */
	private function schedule_apply( $apply ) {
		if ( ! is_array( $apply ) ) {
			return array();
		}

		$new_next_run_at = isset( $apply['new_next_run_at'] ) ? (string) $apply['new_next_run_at'] : ( isset( $apply['applied_next_run_at'] ) ? (string) $apply['applied_next_run_at'] : '' );

		$clean = array(
			'schedule_id'          => isset( $apply['schedule_id'] ) ? sanitize_key( (string) $apply['schedule_id'] ) : '',
			'label'                => $this->bounded_text( isset( $apply['label'] ) ? (string) $apply['label'] : '', self::MAX_SCHEDULE_LABEL_LENGTH ),
			'owner'                => $this->sanitize_schedule_owner( isset( $apply['owner'] ) ? (string) $apply['owner'] : '' ),
			'capability_version'   => $this->non_negative_int( $apply, 'capability_version' ),
			'preview_action_id'    => isset( $apply['preview_action_id'] ) ? $this->sanitize_uuid( (string) $apply['preview_action_id'] ) : '',
			'preview_fingerprint'  => isset( $apply['preview_fingerprint'] ) ? $this->sha256_or_empty( (string) $apply['preview_fingerprint'] ) : '',
			'proposed_cadence'     => isset( $apply['proposed_cadence'] ) ? sanitize_key( (string) $apply['proposed_cadence'] ) : '',
			'previous_cadence'     => isset( $apply['previous_cadence'] ) ? sanitize_key( (string) $apply['previous_cadence'] ) : '',
			'applied_cadence'      => isset( $apply['applied_cadence'] ) ? sanitize_key( (string) $apply['applied_cadence'] ) : '',
			'previous_next_run_at' => isset( $apply['previous_next_run_at'] ) ? sanitize_text_field( (string) $apply['previous_next_run_at'] ) : '',
			'new_next_run_at'      => sanitize_text_field( $new_next_run_at ),
			'changed'              => ! empty( $apply['changed'] ),
			'rollback_available'   => false,
			'rollback_expires_at'  => isset( $apply['rollback_expires_at'] ) ? sanitize_text_field( (string) $apply['rollback_expires_at'] ) : '',
			'warnings'             => $this->schedule_preview_warnings( isset( $apply['warnings'] ) ? $apply['warnings'] : array() ),
		);

		if ( isset( $apply['rollback_metadata'] ) && is_array( $apply['rollback_metadata'] ) ) {
			$clean['rollback_metadata'] = $this->schedule_rollback_metadata( $apply['rollback_metadata'] );
		}

		return $clean;
	}

	/**
	 * Sanitizes evidence-only rollback metadata reported by a client apply result.
	 *
	 * @param mixed $metadata Rollback metadata.
	 * @return array<string,mixed>
	 */
	private function schedule_rollback_metadata( $metadata ) {
		if ( ! is_array( $metadata ) ) {
			return array();
		}

		return array(
			'captured'                            => ! empty( $metadata['captured'] ),
			'available'                           => false,
			'reason'                              => isset( $metadata['reason'] ) ? sanitize_key( (string) $metadata['reason'] ) : '',
			'source_action_id'                    => isset( $metadata['source_action_id'] ) ? $this->sanitize_uuid( (string) $metadata['source_action_id'] ) : '',
			'source_preview_action_id'            => isset( $metadata['source_preview_action_id'] ) ? $this->sanitize_uuid( (string) $metadata['source_preview_action_id'] ) : '',
			'schedule_id'                         => isset( $metadata['schedule_id'] ) ? sanitize_key( (string) $metadata['schedule_id'] ) : '',
			'owner'                               => $this->sanitize_schedule_owner( isset( $metadata['owner'] ) ? (string) $metadata['owner'] : '' ),
			'previous_cadence'                    => isset( $metadata['previous_cadence'] ) ? sanitize_key( (string) $metadata['previous_cadence'] ) : '',
			'applied_cadence'                     => isset( $metadata['applied_cadence'] ) ? sanitize_key( (string) $metadata['applied_cadence'] ) : '',
			'previous_next_run_at'                => isset( $metadata['previous_next_run_at'] ) ? sanitize_text_field( (string) $metadata['previous_next_run_at'] ) : '',
			'applied_next_run_at'                 => isset( $metadata['applied_next_run_at'] ) ? sanitize_text_field( (string) $metadata['applied_next_run_at'] ) : '',
			'current_schedule_fingerprint_before' => isset( $metadata['current_schedule_fingerprint_before'] ) ? $this->sha256_or_empty( (string) $metadata['current_schedule_fingerprint_before'] ) : '',
			'current_schedule_fingerprint_after'  => isset( $metadata['current_schedule_fingerprint_after'] ) ? $this->sha256_or_empty( (string) $metadata['current_schedule_fingerprint_after'] ) : '',
			'rollback_metadata_fingerprint'       => isset( $metadata['rollback_metadata_fingerprint'] ) ? $this->sha256_or_empty( (string) $metadata['rollback_metadata_fingerprint'] ) : '',
			'captured_at'                         => isset( $metadata['captured_at'] ) ? sanitize_text_field( (string) $metadata['captured_at'] ) : '',
			'expires_at'                          => isset( $metadata['expires_at'] ) ? sanitize_text_field( (string) $metadata['expires_at'] ) : '',
		);
	}

	/**
	 * Sanitizes a client-reported schedule rollback preview result.
	 *
	 * @since 0.1.27
	 *
	 * @param mixed $preview Rollback preview result.
	 * @return array<string,mixed>
	 */
	private function schedule_rollback_preview( $preview ) {
		if ( ! is_array( $preview ) ) {
			return array();
		}

		return array(
			'schedule_id'                           => isset( $preview['schedule_id'] ) ? sanitize_key( (string) $preview['schedule_id'] ) : '',
			'label'                                 => $this->bounded_text( isset( $preview['label'] ) ? (string) $preview['label'] : '', self::MAX_SCHEDULE_LABEL_LENGTH ),
			'owner'                                 => $this->sanitize_schedule_owner( isset( $preview['owner'] ) ? (string) $preview['owner'] : '' ),
			'current_cadence'                       => isset( $preview['current_cadence'] ) ? sanitize_key( (string) $preview['current_cadence'] ) : '',
			'applied_cadence'                       => isset( $preview['applied_cadence'] ) ? sanitize_key( (string) $preview['applied_cadence'] ) : '',
			'rollback_cadence'                      => isset( $preview['rollback_cadence'] ) ? sanitize_key( (string) $preview['rollback_cadence'] ) : '',
			'current_next_run_at'                   => isset( $preview['current_next_run_at'] ) ? sanitize_text_field( (string) $preview['current_next_run_at'] ) : '',
			'rollback_next_run_estimate_at'         => isset( $preview['rollback_next_run_estimate_at'] ) ? sanitize_text_field( (string) $preview['rollback_next_run_estimate_at'] ) : '',
			'would_change'                          => ! empty( $preview['would_change'] ),
			'rollback_apply_supported'              => false,
			'rollback_supported'                    => false,
			'preview_action_id'                     => isset( $preview['preview_action_id'] ) ? $this->sanitize_uuid( (string) $preview['preview_action_id'] ) : '',
			'preview_fingerprint'                   => isset( $preview['preview_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['preview_fingerprint'] ) : '',
			'source_apply_action_id'                => isset( $preview['source_apply_action_id'] ) ? $this->sanitize_uuid( (string) $preview['source_apply_action_id'] ) : '',
			'rollback_metadata_fingerprint'         => isset( $preview['rollback_metadata_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['rollback_metadata_fingerprint'] ) : '',
			'current_schedule_fingerprint'          => isset( $preview['current_schedule_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['current_schedule_fingerprint'] ) : '',
			'expected_current_schedule_fingerprint' => isset( $preview['expected_current_schedule_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['expected_current_schedule_fingerprint'] ) : '',
			'previous_schedule_fingerprint'         => isset( $preview['previous_schedule_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['previous_schedule_fingerprint'] ) : '',
			'capability_version'                    => $this->non_negative_int( $preview, 'capability_version' ),
			'preview_created_at'                    => isset( $preview['preview_created_at'] ) ? sanitize_text_field( (string) $preview['preview_created_at'] ) : '',
			'preview_expires_at'                    => isset( $preview['preview_expires_at'] ) ? sanitize_text_field( (string) $preview['preview_expires_at'] ) : '',
			'warnings'                              => $this->schedule_preview_warnings( isset( $preview['warnings'] ) ? $preview['warnings'] : array() ),
		);
	}

	/**
	 * Sanitizes schedule preview warning codes.
	 *
	 * @param mixed $warnings Warning list.
	 * @return array<int,string>
	 */
	private function schedule_preview_warnings( $warnings ) {
		if ( ! is_array( $warnings ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $warnings, 0, 10 ) as $warning ) {
			$warning = sanitize_key( (string) $warning );
			if ( '' !== $warning && ! in_array( $warning, $clean, true ) ) {
				$clean[] = $warning;
			}
		}

		return $clean;
	}
}
