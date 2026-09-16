<?php
/**
 * Remote action repository helper trait.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Builds bounded, redacted remote action context payloads.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Context {


	/**
	 * Redacts context before local storage.
	 *
	 * @param array<string,mixed> $context Context.
	 * @return array<string,mixed>
	 */
	private function redacted_context( array $context ) {
		$clean = array();

		foreach ( $context as $key => $value ) {
			$key = sanitize_key( (string) $key );

			if ( '' === $key ) {
				continue;
			}

			if (
				preg_match(
					'/(secret|token|credential|password|authorization|cookie|nonce|signature|private|path|file|package|drime|url|sql)/',
					$key
				)
			) {
				$clean[ $key ] = '[redacted]';
				continue;
			}

			if ( is_bool( $value ) ) {
				$clean[ $key ] = $value;
			} elseif ( is_int( $value ) || is_float( $value ) ) {
				$clean[ $key ] = max( 0, (int) $value );
			} elseif ( is_scalar( $value ) ) {
				$clean[ $key ] = $this->bounded_text( (string) $value, 120 );
			}
		}

		return $clean;
	}

	/**
	 * Merges support-safe client action details into existing redacted context.
	 *
	 * @param int                 $action_id Action row ID.
	 * @param array<string,mixed> $client_action Client action.
	 * @return string
	 */
	private function merge_client_action_context_json( $action_id, array $client_action ) {
		$row = $this->row_by_id( $action_id );

		if ( ! is_array( $row ) ) {
			return '';
		}

		$context = $this->context_from_row( $row );

		if ( ! empty( $client_action['schedule_preview'] ) && is_array( $client_action['schedule_preview'] ) ) {
			$context['schedule_preview'] = $this->safe_schedule_preview_context( $client_action['schedule_preview'] );
		}

		if ( ! empty( $client_action['schedule_apply'] ) && is_array( $client_action['schedule_apply'] ) ) {
			$context['schedule_apply'] = $this->safe_schedule_apply_context( $client_action['schedule_apply'] );
		}

		$encoded = wp_json_encode( $context, JSON_UNESCAPED_SLASHES );

		return false === $encoded ? '' : (string) $encoded;
	}

	/**
	 * Sanitizes preview context for local dashboard storage.
	 *
	 * @param array<string,mixed> $preview Preview.
	 * @return array<string,mixed>
	 */
	private function safe_schedule_preview_context( array $preview ) {
		return array(
			'schedule_id'                   => isset( $preview['schedule_id'] ) ? sanitize_key( (string) $preview['schedule_id'] ) : '',
			'label'                         => $this->bounded_text( isset( $preview['label'] ) ? (string) $preview['label'] : '', 80 ),
			'owner'                         => isset( $preview['owner'] ) ? sanitize_key( (string) $preview['owner'] ) : '',
			'current_cadence'               => isset( $preview['current_cadence'] ) ? sanitize_key( (string) $preview['current_cadence'] ) : '',
			'proposed_cadence'              => isset( $preview['proposed_cadence'] ) ? sanitize_key( (string) $preview['proposed_cadence'] ) : '',
			'current_next_run_at'           => isset( $preview['current_next_run_at'] ) ? sanitize_text_field( (string) $preview['current_next_run_at'] ) : '',
			'proposed_next_run_estimate_at' => isset( $preview['proposed_next_run_estimate_at'] ) ? sanitize_text_field( (string) $preview['proposed_next_run_estimate_at'] ) : '',
			'would_change'                  => ! empty( $preview['would_change'] ),
			'apply_supported'               => ! empty( $preview['apply_supported'] ),
			'preview_action_id'             => isset( $preview['preview_action_id'] ) ? $this->sanitize_uuid( (string) $preview['preview_action_id'] ) : '',
			'preview_fingerprint'           => isset( $preview['preview_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['preview_fingerprint'] ) : '',
			'current_schedule_fingerprint'  => isset( $preview['current_schedule_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['current_schedule_fingerprint'] ) : '',
			'capability_version'            => isset( $preview['capability_version'] ) ? absint( $preview['capability_version'] ) : 0,
			'preview_created_at'            => isset( $preview['preview_created_at'] ) ? sanitize_text_field( (string) $preview['preview_created_at'] ) : '',
			'preview_expires_at'            => isset( $preview['preview_expires_at'] ) ? sanitize_text_field( (string) $preview['preview_expires_at'] ) : '',
		);
	}

	/**
	 * Sanitizes apply context for local dashboard storage.
	 *
	 * @param array<string,mixed> $apply Apply result.
	 * @return array<string,mixed>
	 */
	private function safe_schedule_apply_context( array $apply ) {
		$new_next_run_at = isset( $apply['new_next_run_at'] ) ? (string) $apply['new_next_run_at'] : ( isset( $apply['applied_next_run_at'] ) ? (string) $apply['applied_next_run_at'] : '' );

		$clean = array(
			'schedule_id'          => isset( $apply['schedule_id'] ) ? sanitize_key( (string) $apply['schedule_id'] ) : '',
			'label'                => $this->bounded_text( isset( $apply['label'] ) ? (string) $apply['label'] : '', 80 ),
			'owner'                => isset( $apply['owner'] ) ? sanitize_key( (string) $apply['owner'] ) : '',
			'capability_version'   => isset( $apply['capability_version'] ) ? absint( $apply['capability_version'] ) : 0,
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
		);

		if ( isset( $apply['rollback_metadata'] ) && is_array( $apply['rollback_metadata'] ) ) {
			$clean['rollback_metadata'] = $this->safe_schedule_rollback_metadata_context( $apply['rollback_metadata'] );
		}

		return $clean;
	}

	/**
	 * Sanitizes evidence-only rollback metadata for local dashboard storage.
	 *
	 * @param array<string,mixed> $metadata Rollback metadata.
	 * @return array<string,mixed>
	 */
	private function safe_schedule_rollback_metadata_context( array $metadata ) {
		return array(
			'captured'                            => ! empty( $metadata['captured'] ),
			'available'                           => false,
			'reason'                              => isset( $metadata['reason'] ) ? sanitize_key( (string) $metadata['reason'] ) : '',
			'source_action_id'                    => isset( $metadata['source_action_id'] ) ? $this->sanitize_uuid( (string) $metadata['source_action_id'] ) : '',
			'source_preview_action_id'            => isset( $metadata['source_preview_action_id'] ) ? $this->sanitize_uuid( (string) $metadata['source_preview_action_id'] ) : '',
			'schedule_id'                         => isset( $metadata['schedule_id'] ) ? sanitize_key( (string) $metadata['schedule_id'] ) : '',
			'owner'                               => isset( $metadata['owner'] ) ? sanitize_key( (string) $metadata['owner'] ) : '',
			'previous_cadence'                    => isset( $metadata['previous_cadence'] ) ? sanitize_key( (string) $metadata['previous_cadence'] ) : '',
			'applied_cadence'                     => isset( $metadata['applied_cadence'] ) ? sanitize_key( (string) $metadata['applied_cadence'] ) : '',
			'previous_next_run_at'                => isset( $metadata['previous_next_run_at'] ) ? sanitize_text_field( (string) $metadata['previous_next_run_at'] ) : '',
			'applied_next_run_at'                 => isset( $metadata['applied_next_run_at'] ) ? sanitize_text_field( (string) $metadata['applied_next_run_at'] ) : '',
			'current_schedule_fingerprint_before' => isset( $metadata['current_schedule_fingerprint_before'] ) ? $this->sha256_or_empty( (string) $metadata['current_schedule_fingerprint_before'] ) : '',
			'current_schedule_fingerprint_after'  => isset( $metadata['current_schedule_fingerprint_after'] ) ? $this->sha256_or_empty( (string) $metadata['current_schedule_fingerprint_after'] ) : '',
			'captured_at'                         => isset( $metadata['captured_at'] ) ? sanitize_text_field( (string) $metadata['captured_at'] ) : '',
			'expires_at'                          => isset( $metadata['expires_at'] ) ? sanitize_text_field( (string) $metadata['expires_at'] ) : '',
		);
	}

	/**
	 * Sanitizes client-reported action counts.
	 *
	 * @param mixed $counts Counts.
	 * @return array<string,int>
	 */
	private function client_action_counts( $counts ) {
		if ( ! is_array( $counts ) ) {
			return array();
		}

		$clean = array();

		foreach ( array( 'found', 'queued', 'already_known', 'upload_attempted', 'failed' ) as $key ) {
			$clean[ $key ] = isset( $counts[ $key ] ) ? max( 0, (int) $counts[ $key ] ) : 0;
		}

		return $clean;
	}

	/**
	 * Gets the best client-side timestamp available for an action report.
	 *
	 * @param array<string,mixed> $client_action Client action report.
	 * @return string
	 */
	private function client_action_updated_at( array $client_action ) {
		foreach ( array( 'updated_at', 'completed_at', 'requested_at' ) as $key ) {
			if ( empty( $client_action[ $key ] ) ) {
				continue;
			}

			$date = $this->date_or_default( (string) $client_action[ $key ], '' );

			if ( '' !== $date ) {
				return $date;
			}
		}

		return '';
	}
}
