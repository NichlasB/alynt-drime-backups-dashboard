<?php
/**
 * Remote action dispatcher schedule-action helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles schedule preview/apply remote action request orchestration.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Schedule_Actions {
	/**
	 * Requests a non-mutating schedule preview on one opted-in client site.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $schedule_id Schedule ID.
	 * @param string $proposed_cadence Proposed cadence.
	 * @param int    $requested_by User ID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function request_schedule_preview( $site_id, $schedule_id, $proposed_cadence, $requested_by = 0 ) {
		$site_id          = absint( $site_id );
		$schedule_id      = sanitize_key( (string) $schedule_id );
		$proposed_cadence = sanitize_key( (string) $proposed_cadence );

		if ( 0 === $site_id ) {
			return new WP_Error( 'remote_action_site_required', __( 'Choose an enrolled site before requesting a remote action.', 'alynt-drime-backups-dashboard' ) );
		}

		$site = $this->sites->get( $site_id );
		if ( ! is_array( $site ) ) {
			return new WP_Error( 'remote_action_site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );
		}

		$capabilities = $this->latest_capabilities( $site_id, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW, $schedule_id, $proposed_cadence );
		if ( is_wp_error( $capabilities ) ) {
			return $capabilities;
		}

		$schedule_preview = array(
			'schedule_id'        => $schedule_id,
			'proposed_cadence'   => $proposed_cadence,
			'capability_version' => 1,
		);
		$prepared         = $this->prepare_signed_intent( $site, $capabilities, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW, $schedule_preview );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$action_id = $this->actions->create_request(
			$site_id,
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW,
			$requested_by,
			$prepared['body']['idempotency_key'],
			$prepared['key_id'],
			gmdate( 'Y-m-d H:i:s', strtotime( $prepared['body']['expires_at'] ) ),
			$prepared['request_fingerprint'],
			array(
				'capability_reported'   => true,
				'requested_action_type' => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW,
				'schedule_id'           => $schedule_id,
				'proposed_cadence'      => $proposed_cadence,
				'preview_only'          => true,
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
			'action'         => 'schedule_preview',
			'site_id'        => $site_id,
			'action_id'      => $action_id,
			'remote_state'   => $response['state'],
			'result_code'    => $response['code'],
			'result_summary' => $response['summary'],
			'retry_after'    => $response['retry_after'],
		);
	}

	/**
	 * Requests a guarded schedule apply from one fresh successful preview.
	 *
	 * @since 0.1.24
	 *
	 * @param int    $site_id Site ID.
	 * @param string $preview_action_id Preview action public ID.
	 * @param int    $requested_by User ID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function request_schedule_apply( $site_id, $preview_action_id, $requested_by = 0 ) {
		$site_id           = absint( $site_id );
		$preview_action_id = strtolower( trim( (string) $preview_action_id ) );

		if ( 0 === $site_id ) {
			return new WP_Error( 'remote_action_site_required', __( 'Choose an enrolled site before requesting a remote action.', 'alynt-drime-backups-dashboard' ) );
		}

		$site = $this->sites->get( $site_id );
		if ( ! is_array( $site ) ) {
			return new WP_Error( 'remote_action_site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );
		}

		$capabilities = $this->latest_capabilities( $site_id, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY );
		if ( is_wp_error( $capabilities ) ) {
			return $capabilities;
		}

		$schedule_apply = $this->actions->fresh_schedule_preview_for_apply( $site_id, $preview_action_id, $capabilities );
		if ( is_wp_error( $schedule_apply ) ) {
			return $schedule_apply;
		}

		$prepared = $this->prepare_signed_intent( $site, $capabilities, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY, array(), $schedule_apply );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$action_id = $this->actions->create_request(
			$site_id,
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY,
			$requested_by,
			$prepared['body']['idempotency_key'],
			$prepared['key_id'],
			gmdate( 'Y-m-d H:i:s', strtotime( $prepared['body']['expires_at'] ) ),
			$prepared['request_fingerprint'],
			array(
				'capability_reported'   => true,
				'requested_action_type' => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY,
				'schedule_id'           => isset( $schedule_apply['schedule_id'] ) ? (string) $schedule_apply['schedule_id'] : '',
				'proposed_cadence'      => isset( $schedule_apply['proposed_cadence'] ) ? (string) $schedule_apply['proposed_cadence'] : '',
				'preview_action_id'     => isset( $schedule_apply['preview_action_id'] ) ? (string) $schedule_apply['preview_action_id'] : '',
				'preview_fingerprint'   => isset( $schedule_apply['preview_fingerprint'] ) ? (string) $schedule_apply['preview_fingerprint'] : '',
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
			'action'            => 'schedule_apply',
			'site_id'           => $site_id,
			'action_id'         => $action_id,
			'preview_action_id' => isset( $schedule_apply['preview_action_id'] ) ? (string) $schedule_apply['preview_action_id'] : '',
			'remote_state'      => $response['state'],
			'result_code'       => $response['code'],
			'result_summary'    => $response['summary'],
			'retry_after'       => $response['retry_after'],
		);
	}
}
