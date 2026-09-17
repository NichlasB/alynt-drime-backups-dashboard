<?php
/**
 * Remote action dispatcher helper trait.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Handles public remote action request orchestration methods.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Actions {


	/**
	 * Requests scan/upload-now on one opted-in client site.
	 *
	 * @param int $site_id Site ID.
	 * @param int $requested_by User ID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function request_scan_upload_now( $site_id, $requested_by = 0 ) {
		$site_id = absint( $site_id );

		if ( 0 === $site_id ) {
			return new WP_Error( 'remote_action_site_required', __( 'Choose an enrolled site before requesting a remote action.', 'alynt-drime-backups-dashboard' ) );
		}

		$site = $this->sites->get( $site_id );
		if ( ! is_array( $site ) ) {
			return new WP_Error( 'remote_action_site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );
		}

		$capabilities = $this->latest_capabilities( $site_id );
		if ( is_wp_error( $capabilities ) ) {
			return $capabilities;
		}

		$prepared = $this->prepare_signed_intent( $site, $capabilities, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$action_id = $this->actions->create_request(
			$site_id,
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW,
			$requested_by,
			$prepared['body']['idempotency_key'],
			$prepared['key_id'],
			gmdate( 'Y-m-d H:i:s', strtotime( $prepared['body']['expires_at'] ) ),
			$prepared['request_fingerprint'],
			array(
				'capability_reported'   => true,
				'min_interval_seconds'  => isset( $capabilities['min_interval_seconds'] ) ? absint( $capabilities['min_interval_seconds'] ) : 0,
				'requested_action_type' => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW,
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
			'action'         => 'request_backup_now',
			'site_id'        => $site_id,
			'action_id'      => $action_id,
			'remote_state'   => $response['state'],
			'result_code'    => $response['code'],
			'result_summary' => $response['summary'],
			'retry_after'    => $response['retry_after'],
		);
	}
}
