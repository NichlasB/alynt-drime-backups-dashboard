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
Handles capability lookup and signed intent preparation.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Intent {


	/**
	 * Gets latest remote action capabilities.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $action_type Action type.
	 * @param string $schedule_id Schedule ID.
	 * @param string $proposed_cadence Proposed cadence.
	 * @return array<string,mixed>|WP_Error
	 */
	private function latest_capabilities( $site_id, $action_type = '', $schedule_id = '', $proposed_cadence = '' ) {
		$snapshot = $this->snapshots->latest_for_site( $site_id );
		$payload  = is_array( $snapshot ) && isset( $snapshot['decoded_payload'] ) && is_array( $snapshot['decoded_payload'] ) ? $snapshot['decoded_payload'] : array();
		$remote   = isset( $payload['remote_actions'] ) && is_array( $payload['remote_actions'] ) ? $payload['remote_actions'] : array();
		$clean    = $this->capabilities->sanitize( $remote );

		if ( is_wp_error( $clean ) ) {
			return $clean;
		}

		$action_type = '' === $action_type ? Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW : sanitize_key( (string) $action_type );

		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW === $action_type ) {
			if ( ! $this->capabilities->supports_schedule_preview_action( $clean, $schedule_id, $proposed_cadence ) ) {
				return new WP_Error( 'schedule_management_unavailable', __( 'The latest client report does not allow schedule preview for the selected schedule and cadence. Run Check Now after updating the client.', 'alynt-drime-backups-dashboard' ) );
			}

			return $clean;
		}

		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY === $action_type ) {
			if (
				empty( $clean['enabled'] )
				|| empty( $clean['sodium_available'] )
				|| empty( $clean['allowed_actions'] )
				|| ! in_array( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY, (array) $clean['allowed_actions'], true )
				|| empty( $clean['schedule_management']['enabled'] )
				|| empty( $clean['schedule_management']['apply_supported'] )
			) {
				return new WP_Error( 'schedule_apply_unavailable', __( 'The latest client report does not allow Schedule Apply. Enable Schedule Apply on the client and run Check Now first.', 'alynt-drime-backups-dashboard' ) );
			}

			return $clean;
		}

		if ( ! $this->capabilities->supports_scan_upload_now( $clean ) ) {
			return new WP_Error( 'remote_action_capability_missing', __( 'The latest client report does not allow Request Backup Now. Complete V2 opt-in and run Check Now first.', 'alynt-drime-backups-dashboard' ) );
		}

		return $clean;
	}

	/**
	 * Builds a signed request descriptor.
	 *
	 * @param array<string,mixed> $site Site.
	 * @param array<string,mixed> $capabilities Capabilities.
	 * @param string              $action_type Action type.
	 * @param array<string,mixed> $schedule_preview Schedule preview request.
	 * @param array<string,mixed> $schedule_apply Schedule apply request.
	 * @return array<string,mixed>|WP_Error
	 */
	private function prepare_signed_intent( array $site, array $capabilities, $action_type, array $schedule_preview = array(), array $schedule_apply = array() ) {
		if ( empty( $site['polling_key_id'] ) || empty( $site['polling_secret_ciphertext'] ) ) {
			return new WP_Error( 'remote_action_requires_pairing', __( 'Active read-only pairing is required before requesting a remote action.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( empty( $site['public_id'] ) || empty( $site['site_uuid'] ) || empty( $site['expected_origin'] ) || empty( $site['action_key_id'] ) || empty( $site['action_private_key_ciphertext'] ) ) {
			return new WP_Error( 'remote_action_key_missing', __( 'Generate and complete a V2 action opt-in token before requesting this action.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( empty( $capabilities['key_id'] ) || ! hash_equals( (string) $site['action_key_id'], (string) $capabilities['key_id'] ) ) {
			return new WP_Error( 'remote_action_key_mismatch', __( 'The latest client capability report does not match the dashboard action key. Regenerate the V2 opt-in token and run Check Now.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( ! $this->signer->is_supported() ) {
			return new WP_Error( 'action_signing_unavailable', __( 'Remote action signing is unavailable because PHP Sodium support is missing.', 'alynt-drime-backups-dashboard' ) );
		}

		$origin                = $this->origins->normalize_public_https_origin( (string) $site['expected_origin'] );
		$is_same_origin_action = $this->is_same_origin_self_action( $origin );
		if ( '' === $origin ) {
			return new WP_Error( 'remote_action_destination_invalid', __( 'The client action destination is not a supported public HTTPS origin.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( ! $is_same_origin_action && ! $this->origins->resolved_origin_is_public( $origin, $this->resolver ) ) {
			return new WP_Error( 'remote_action_destination_unsafe', __( 'The client action destination did not resolve to a public IP address.', 'alynt-drime-backups-dashboard' ) );
		}

		$private_key = $this->vault->decrypt( (string) $site['action_private_key_ciphertext'], 'action:' . (string) $site['public_id'] );
		if ( is_wp_error( $private_key ) ) {
			return $private_key;
		}

		$now  = time();
		$body = array(
			'protocol_version'         => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::PROTOCOL_VERSION,
			'action_id'                => $this->create_uuid(),
			'dashboard_site_public_id' => sanitize_text_field( (string) $site['public_id'] ),
			'site_uuid'                => sanitize_text_field( (string) $site['site_uuid'] ),
			'action_type'              => sanitize_key( (string) $action_type ),
			'requested_at'             => gmdate( 'c', $now ),
			'expires_at'               => gmdate( 'c', $now + self::INTENT_TTL_SECONDS ),
			'idempotency_key'          => $this->create_idempotency_key(),
		);
		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW === $body['action_type'] ) {
			$body['schedule_preview'] = array(
				'schedule_id'        => isset( $schedule_preview['schedule_id'] ) ? sanitize_key( (string) $schedule_preview['schedule_id'] ) : '',
				'proposed_cadence'   => isset( $schedule_preview['proposed_cadence'] ) ? sanitize_key( (string) $schedule_preview['proposed_cadence'] ) : '',
				'capability_version' => isset( $schedule_preview['capability_version'] ) ? absint( $schedule_preview['capability_version'] ) : 1,
			);
		}
		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY === $body['action_type'] ) {
			$body['schedule_apply'] = array(
				'schedule_id'         => isset( $schedule_apply['schedule_id'] ) ? sanitize_key( (string) $schedule_apply['schedule_id'] ) : '',
				'proposed_cadence'    => isset( $schedule_apply['proposed_cadence'] ) ? sanitize_key( (string) $schedule_apply['proposed_cadence'] ) : '',
				'capability_version'  => isset( $schedule_apply['capability_version'] ) ? absint( $schedule_apply['capability_version'] ) : 1,
				'preview_action_id'   => isset( $schedule_apply['preview_action_id'] ) ? sanitize_text_field( (string) $schedule_apply['preview_action_id'] ) : '',
				'preview_fingerprint' => isset( $schedule_apply['preview_fingerprint'] ) ? preg_replace( '/[^a-f0-9]/', '', (string) $schedule_apply['preview_fingerprint'] ) : '',
			);
		}
		$body_json = $this->signer->canonical_json( $body );

		if ( is_wp_error( $body_json ) ) {
			return $body_json;
		}

		$signed_at     = gmdate( 'c', $now );
		$signing_input = $this->signer->signing_input( 'POST', self::ACTION_ROUTE, $origin, $body_json, $signed_at );
		$signature     = $this->signer->sign( $private_key, $signing_input );

		if ( is_wp_error( $signature ) ) {
			return $signature;
		}

		return array(
			'url'                 => $origin . self::ACTION_ROUTE,
			'origin'              => $origin,
			'route'               => self::ACTION_ROUTE,
			'key_id'              => (string) $site['action_key_id'],
			'signed_at'           => $signed_at,
			'signature'           => $signature,
			'body'                => $body,
			'body_json'           => $body_json,
			'request_fingerprint' => hash( 'sha256', $body_json ),
			'reject_unsafe_urls'  => ! $is_same_origin_action,
		);
	}
}
