<?php
/**
 * Remote action repository schedule-apply lookup helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.59
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles schedule-preview lookup validation for guarded schedule apply.
 *
 * @since 0.1.59
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Schedule_Apply_Lookups {

	/**
	 * Gets one fresh successful schedule preview that may be used for apply.
	 *
	 * @since 0.1.24
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $preview_public_id Preview action public ID.
	 * @param array<string,mixed> $capabilities Latest sanitized remote-action capabilities.
	 * @param string|null         $now Current UTC MySQL timestamp.
	 * @return array<string,mixed>|WP_Error
	 */
	public function fresh_schedule_preview_for_apply( $site_id, $preview_public_id, array $capabilities, $now = null ) {
		$site_id           = absint( $site_id );
		$preview_public_id = $this->sanitize_uuid( $preview_public_id );

		if ( 0 === $site_id || '' === $preview_public_id ) {
			return new WP_Error( 'schedule_apply_preview_missing', __( 'Choose a fresh schedule preview before applying a schedule change.', 'alynt-drime-backups-dashboard' ) );
		}

		$row = $this->find_by_public_id_for_site( $preview_public_id, $site_id );

		if ( ! is_array( $row ) || Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW !== $this->capabilities->sanitize_action_type( isset( $row['action_type'] ) ? (string) $row['action_type'] : '' ) ) {
			return new WP_Error( 'schedule_apply_preview_missing', __( 'The selected schedule preview could not be found. Run a new preview before applying a schedule change.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( 'succeeded' !== $this->capabilities->sanitize_state( isset( $row['state'] ) ? (string) $row['state'] : '' ) ) {
			return new WP_Error( 'schedule_apply_preview_not_ready', __( 'The selected schedule preview has not succeeded yet. Run Check Now and confirm the preview result before applying.', 'alynt-drime-backups-dashboard' ) );
		}

		$context = $this->context_from_row( $row );
		$preview = isset( $context['schedule_preview'] ) && is_array( $context['schedule_preview'] ) ? $context['schedule_preview'] : array();

		if ( empty( $preview['would_change'] ) ) {
			return new WP_Error( 'schedule_apply_preview_not_applicable', __( 'The selected preview does not describe a schedule change to apply.', 'alynt-drime-backups-dashboard' ) );
		}

		$schedule_id         = isset( $preview['schedule_id'] ) ? sanitize_key( (string) $preview['schedule_id'] ) : '';
		$proposed_cadence    = isset( $preview['proposed_cadence'] ) ? sanitize_key( (string) $preview['proposed_cadence'] ) : '';
		$preview_action_id   = isset( $preview['preview_action_id'] ) ? $this->sanitize_uuid( (string) $preview['preview_action_id'] ) : '';
		$preview_fingerprint = isset( $preview['preview_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['preview_fingerprint'] ) : '';
		$capability_version  = isset( $preview['capability_version'] ) ? absint( $preview['capability_version'] ) : 0;

		if (
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::SCHEDULE_SCAN_UPLOAD !== $schedule_id
			|| '' === $proposed_cadence
			|| ( '' !== $preview_action_id && $preview_public_id !== $preview_action_id )
			|| '' === $preview_fingerprint
			|| $capability_version < 1
		) {
			return new WP_Error( 'schedule_apply_preview_invalid', __( 'The selected preview is missing required safe apply evidence. Run a new preview before applying.', 'alynt-drime-backups-dashboard' ) );
		}

		$now_timestamp = strtotime( null === $now ? gmdate( 'Y-m-d H:i:s' ) : (string) $now );
		if ( false === $now_timestamp ) {
			$now_timestamp = time();
		}

		$preview_expires_at = isset( $preview['preview_expires_at'] ) ? strtotime( (string) $preview['preview_expires_at'] ) : false;
		$completed_at       = isset( $row['completed_at'] ) ? strtotime( (string) $row['completed_at'] ) : false;

		if ( false !== $preview_expires_at && $preview_expires_at <= $now_timestamp ) {
			return new WP_Error( 'schedule_apply_preview_expired', __( 'The selected schedule preview has expired. Run a new preview before applying.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( false !== $completed_at && $completed_at < ( $now_timestamp - self::SCHEDULE_PREVIEW_FRESH_SECONDS ) ) {
			return new WP_Error( 'schedule_apply_preview_expired', __( 'The selected schedule preview is no longer fresh. Run a new preview before applying.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( ! $this->capabilities->supports_schedule_apply_action( $capabilities, $schedule_id, $proposed_cadence ) ) {
			return new WP_Error( 'schedule_apply_unavailable', __( 'The latest client report does not allow applying this previewed schedule change. Run Check Now after enabling Schedule Apply on the client.', 'alynt-drime-backups-dashboard' ) );
		}

		return array(
			'preview_action_id'    => $preview_public_id,
			'preview_fingerprint'  => $preview_fingerprint,
			'schedule_id'          => $schedule_id,
			'current_cadence'      => isset( $preview['current_cadence'] ) ? sanitize_key( (string) $preview['current_cadence'] ) : '',
			'proposed_cadence'     => $proposed_cadence,
			'capability_version'   => $capability_version,
			'preview_expires_at'   => isset( $preview['preview_expires_at'] ) ? sanitize_text_field( (string) $preview['preview_expires_at'] ) : '',
			'preview_completed_at' => isset( $row['completed_at'] ) ? sanitize_text_field( (string) $row['completed_at'] ) : '',
		);
	}
}
