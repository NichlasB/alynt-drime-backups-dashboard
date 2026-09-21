<?php
/**
 * Remote action repository rollback-preview lookup helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.43
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides schedule rollback-preview lookup validation helpers.
 *
 * @since 0.1.43
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Rollback_Preview_Lookups {
	/**
	 * Gets one successful schedule apply that may be used for rollback preview.
	 *
	 * @since 0.1.43
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $apply_public_id Apply action public ID.
	 * @param array<string,mixed> $capabilities Latest sanitized remote-action capabilities.
	 * @param string|null         $now Current UTC MySQL timestamp.
	 * @return array<string,mixed>|WP_Error
	 */
	public function successful_schedule_apply_for_rollback_preview( $site_id, $apply_public_id, array $capabilities, $now = null ) {
		$site_id         = absint( $site_id );
		$apply_public_id = $this->sanitize_uuid( $apply_public_id );

		if ( 0 === $site_id || '' === $apply_public_id ) {
			return new WP_Error( 'schedule_rollback_preview_apply_missing', __( 'Choose a successful Schedule Apply action before previewing rollback.', 'alynt-drime-backups-dashboard' ) );
		}

		$row = $this->find_by_public_id_for_site( $apply_public_id, $site_id );

		if ( ! is_array( $row ) || Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY !== $this->capabilities->sanitize_action_type( isset( $row['action_type'] ) ? (string) $row['action_type'] : '' ) ) {
			return new WP_Error( 'schedule_rollback_preview_apply_missing', __( 'The selected Schedule Apply action could not be found. Run a fresh Schedule Apply before previewing rollback.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( 'succeeded' !== $this->capabilities->sanitize_state( isset( $row['state'] ) ? (string) $row['state'] : '' ) ) {
			return new WP_Error( 'schedule_rollback_preview_apply_not_ready', __( 'The selected Schedule Apply action has not succeeded yet. Run Check Now and confirm the apply result before previewing rollback.', 'alynt-drime-backups-dashboard' ) );
		}

		$context  = $this->context_from_row( $row );
		$apply    = isset( $context['schedule_apply'] ) && is_array( $context['schedule_apply'] ) ? $context['schedule_apply'] : array();
		$metadata = isset( $apply['rollback_metadata'] ) && is_array( $apply['rollback_metadata'] ) ? $apply['rollback_metadata'] : array();

		if ( empty( $metadata['captured'] ) ) {
			return new WP_Error( 'schedule_rollback_preview_metadata_missing', __( 'The selected Schedule Apply action does not include rollback metadata. Run a new apply after rollback metadata capture is available.', 'alynt-drime-backups-dashboard' ) );
		}

		$schedule_id                   = isset( $metadata['schedule_id'] ) ? sanitize_key( (string) $metadata['schedule_id'] ) : '';
		$source_action_id              = isset( $metadata['source_action_id'] ) ? $this->sanitize_uuid( (string) $metadata['source_action_id'] ) : '';
		$rollback_metadata_fingerprint = isset( $metadata['rollback_metadata_fingerprint'] ) ? $this->sha256_or_empty( (string) $metadata['rollback_metadata_fingerprint'] ) : '';
		$capability_version            = isset( $apply['capability_version'] ) ? absint( $apply['capability_version'] ) : 0;

		if (
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::SCHEDULE_SCAN_UPLOAD !== $schedule_id
			|| ( '' !== $source_action_id && $apply_public_id !== $source_action_id )
			|| '' === $rollback_metadata_fingerprint
			|| $capability_version < 1
		) {
			return new WP_Error( 'schedule_rollback_preview_metadata_invalid', __( 'The selected Schedule Apply action is missing required safe rollback-preview evidence.', 'alynt-drime-backups-dashboard' ) );
		}

		$now_timestamp = strtotime( null === $now ? gmdate( 'Y-m-d H:i:s' ) : (string) $now );
		if ( false === $now_timestamp ) {
			$now_timestamp = time();
		}

		$metadata_expires_at = isset( $metadata['expires_at'] ) ? strtotime( (string) $metadata['expires_at'] ) : false;
		if ( false !== $metadata_expires_at && $metadata_expires_at <= $now_timestamp ) {
			return new WP_Error( 'schedule_rollback_preview_metadata_expired', __( 'The selected Schedule Apply rollback metadata has expired. Run a new schedule preview/apply sequence before previewing rollback.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( ! $this->capabilities->supports_schedule_rollback_preview_action( $capabilities, $schedule_id ) ) {
			return new WP_Error( 'schedule_rollback_preview_unavailable', __( 'The latest client report does not allow Schedule Rollback Preview for this schedule. Enable rollback-preview support on the client and run Check Now first.', 'alynt-drime-backups-dashboard' ) );
		}

		return array(
			'source_apply_action_id'        => $apply_public_id,
			'rollback_metadata_fingerprint' => $rollback_metadata_fingerprint,
			'schedule_id'                   => $schedule_id,
			'previous_cadence'              => isset( $metadata['previous_cadence'] ) ? sanitize_key( (string) $metadata['previous_cadence'] ) : '',
			'applied_cadence'               => isset( $metadata['applied_cadence'] ) ? sanitize_key( (string) $metadata['applied_cadence'] ) : '',
			'capability_version'            => $capability_version,
			'metadata_expires_at'           => isset( $metadata['expires_at'] ) ? sanitize_text_field( (string) $metadata['expires_at'] ) : '',
		);
	}
}
