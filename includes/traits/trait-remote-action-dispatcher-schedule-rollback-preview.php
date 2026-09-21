<?php
/**
 * Remote action dispatcher schedule rollback-preview helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.43
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles non-mutating schedule rollback-preview remote action orchestration.
 *
 * @since 0.1.43
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Schedule_Rollback_Preview {
	/**
	 * Requests a non-mutating schedule rollback preview from one successful apply action.
	 *
	 * @since 0.1.43
	 *
	 * @param int    $site_id Site ID.
	 * @param string $source_apply_action_id Source Schedule Apply action public ID.
	 * @param int    $requested_by User ID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function request_schedule_rollback_preview( $site_id, $source_apply_action_id, $requested_by = 0 ) {
		$site_id                = absint( $site_id );
		$source_apply_action_id = strtolower( trim( (string) $source_apply_action_id ) );

		if ( 0 === $site_id ) {
			return new WP_Error( 'remote_action_site_required', __( 'Choose an enrolled site before requesting a remote action.', 'alynt-drime-backups-dashboard' ) );
		}

		$site = $this->sites->get( $site_id );
		if ( ! is_array( $site ) ) {
			return new WP_Error( 'remote_action_site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );
		}

		$capabilities = $this->latest_capabilities( $site_id, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_ROLLBACK_PREVIEW, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::SCHEDULE_SCAN_UPLOAD );
		if ( is_wp_error( $capabilities ) ) {
			return $capabilities;
		}

		$schedule_rollback_preview = $this->actions->successful_schedule_apply_for_rollback_preview( $site_id, $source_apply_action_id, $capabilities );
		if ( is_wp_error( $schedule_rollback_preview ) ) {
			return $schedule_rollback_preview;
		}

		$prepared = $this->prepare_signed_intent( $site, $capabilities, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_ROLLBACK_PREVIEW, array(), array(), $schedule_rollback_preview );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$action_id = $this->actions->create_request(
			$site_id,
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_ROLLBACK_PREVIEW,
			$requested_by,
			$prepared['body']['idempotency_key'],
			$prepared['key_id'],
			gmdate( 'Y-m-d H:i:s', strtotime( $prepared['body']['expires_at'] ) ),
			$prepared['request_fingerprint'],
			array(
				'capability_reported'           => true,
				'requested_action_type'         => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_ROLLBACK_PREVIEW,
				'schedule_id'                   => isset( $schedule_rollback_preview['schedule_id'] ) ? (string) $schedule_rollback_preview['schedule_id'] : '',
				'source_apply_action_id'        => isset( $schedule_rollback_preview['source_apply_action_id'] ) ? (string) $schedule_rollback_preview['source_apply_action_id'] : '',
				'rollback_metadata_fingerprint' => isset( $schedule_rollback_preview['rollback_metadata_fingerprint'] ) ? (string) $schedule_rollback_preview['rollback_metadata_fingerprint'] : '',
				'preview_only'                  => true,
				'non_mutating'                  => true,
			),
			$prepared['body']['action_id']
		);

		if ( is_wp_error( $action_id ) ) {
			return $action_id;
		}

		if ( method_exists( $this->actions, 'mark_dispatched' ) ) {
			$this->actions->mark_dispatched( $action_id );
		}

		$response = $this->post_intent( $prepared );

		if ( is_wp_error( $response ) ) {
			$this->actions->mark_state( $action_id, 'dispatch_failed', $response->get_error_code(), __( 'The signed request could not be delivered to the client site.', 'alynt-drime-backups-dashboard' ) );
			return $response;
		}

		$recorded = $this->actions->mark_state(
			$action_id,
			$response['state'],
			$response['code'],
			$response['summary'],
			$response['retry_after']
		);

		if ( ! $recorded ) {
			return new WP_Error( 'remote_action_state_store_failed', __( 'The dashboard could not store the remote action response.', 'alynt-drime-backups-dashboard' ) );
		}

		return array(
			'action'                 => 'schedule_rollback_preview',
			'site_id'                => $site_id,
			'action_id'              => $action_id,
			'source_apply_action_id' => isset( $schedule_rollback_preview['source_apply_action_id'] ) ? (string) $schedule_rollback_preview['source_apply_action_id'] : '',
			'remote_state'           => $response['state'],
			'result_code'            => $response['code'],
			'result_summary'         => $response['summary'],
			'retry_after'            => $response['retry_after'],
		);
	}
}
