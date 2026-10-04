<?php
/**
 * Remote action repository context sanitizer helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.58
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds bounded, redacted remote action context payload details.
 *
 * @since 0.1.58
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Context_Sanitizers {

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
			'rollback_metadata_fingerprint'       => isset( $metadata['rollback_metadata_fingerprint'] ) ? $this->sha256_or_empty( (string) $metadata['rollback_metadata_fingerprint'] ) : '',
			'captured_at'                         => isset( $metadata['captured_at'] ) ? sanitize_text_field( (string) $metadata['captured_at'] ) : '',
			'expires_at'                          => isset( $metadata['expires_at'] ) ? sanitize_text_field( (string) $metadata['expires_at'] ) : '',
		);
	}

	/**
	 * Sanitizes rollback-preview context for local dashboard storage.
	 *
	 * @param array<string,mixed> $preview Rollback preview.
	 * @return array<string,mixed>
	 */
	private function safe_schedule_rollback_preview_context( array $preview ) {
		return array(
			'schedule_id'                           => isset( $preview['schedule_id'] ) ? sanitize_key( (string) $preview['schedule_id'] ) : '',
			'label'                                 => $this->bounded_text( isset( $preview['label'] ) ? (string) $preview['label'] : '', 80 ),
			'owner'                                 => isset( $preview['owner'] ) ? sanitize_key( (string) $preview['owner'] ) : '',
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
			'capability_version'                    => isset( $preview['capability_version'] ) ? absint( $preview['capability_version'] ) : 0,
			'preview_created_at'                    => isset( $preview['preview_created_at'] ) ? sanitize_text_field( (string) $preview['preview_created_at'] ) : '',
			'preview_expires_at'                    => isset( $preview['preview_expires_at'] ) ? sanitize_text_field( (string) $preview['preview_expires_at'] ) : '',
		);
	}

	/**
	 * Sanitizes cleanup-preview context for local dashboard storage.
	 *
	 * @param array<string,mixed> $preview Cleanup preview.
	 * @return array<string,mixed>
	 */
	private function safe_cleanup_preview_context( array $preview ) {
		$clean = array(
			'preview_action_id'    => isset( $preview['preview_action_id'] ) ? $this->sanitize_uuid( (string) $preview['preview_action_id'] ) : '',
			'preview_fingerprint'  => isset( $preview['preview_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['preview_fingerprint'] ) : '',
			'capability_version'   => isset( $preview['capability_version'] ) ? absint( $preview['capability_version'] ) : 0,
			'scope'                => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::CLEANUP_SCOPE_SAFE_LOCAL === ( isset( $preview['scope'] ) ? sanitize_key( (string) $preview['scope'] ) : '' ) ? Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::CLEANUP_SCOPE_SAFE_LOCAL : '',
			'preview_created_at'   => isset( $preview['preview_created_at'] ) ? sanitize_text_field( (string) $preview['preview_created_at'] ) : '',
			'expires_at'           => isset( $preview['expires_at'] ) ? sanitize_text_field( (string) $preview['expires_at'] ) : '',
			'total_eligible_count' => isset( $preview['total_eligible_count'] ) ? max( 0, (int) $preview['total_eligible_count'] ) : 0,
			'total_approx_bytes'   => isset( $preview['total_approx_bytes'] ) ? max( 0, (int) $preview['total_approx_bytes'] ) : 0,
			'apply_supported'      => false,
			'categories'           => array(),
		);

		$categories = isset( $preview['categories'] ) && is_array( $preview['categories'] ) ? $preview['categories'] : array();

		foreach ( array_slice( $categories, 0, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::MAX_CLEANUP_CATEGORIES ) as $category ) {
			if ( ! is_array( $category ) ) {
				continue;
			}

			$category_id = isset( $category['category'] ) ? sanitize_key( (string) $category['category'] ) : '';
			if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::CLEANUP_CATEGORY_UPLOADER_TEMP !== $category_id ) {
				continue;
			}

			$clean['categories'][] = array(
				'category'       => $category_id,
				'eligible_count' => isset( $category['eligible_count'] ) ? max( 0, (int) $category['eligible_count'] ) : 0,
				'approx_bytes'   => isset( $category['approx_bytes'] ) ? max( 0, (int) $category['approx_bytes'] ) : 0,
				'age_band'       => isset( $category['age_band'] ) ? sanitize_key( (string) $category['age_band'] ) : '',
				'reason_code'    => isset( $category['reason_code'] ) ? sanitize_key( (string) $category['reason_code'] ) : '',
			);
		}

		return $clean;
	}
}
