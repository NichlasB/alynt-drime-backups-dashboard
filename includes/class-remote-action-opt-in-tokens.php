<?php
/**
 * Remote action opt-in token helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds dashboard-generated V2 remote-action opt-in token material.
 *
 * @since 0.1.15
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Tokens {
	const TOKEN_PREFIX     = 'adb2a.';
	const PROTOCOL_VERSION = 2;
	const PURPOSE          = 'remote_action_opt_in';

	/**
	 * Formats an action opt-in token payload.
	 *
	 * The action public key is not secret, but this token is still display-once
	 * because it binds one dashboard/site/action-key tuple for client opt-in.
	 *
	 * @since 0.1.15
	 *
	 * @param array<string,mixed> $payload Token payload.
	 * @return string|WP_Error
	 */
	public static function format_token( array $payload ) {
		$payload = array(
			'protocol_version'         => self::PROTOCOL_VERSION,
			'purpose'                  => self::PURPOSE,
			'dashboard_origin'         => isset( $payload['dashboard_origin'] ) ? (string) $payload['dashboard_origin'] : '',
			'expected_client_origin'   => isset( $payload['expected_client_origin'] ) ? (string) $payload['expected_client_origin'] : '',
			'dashboard_site_public_id' => isset( $payload['dashboard_site_public_id'] ) ? (string) $payload['dashboard_site_public_id'] : '',
			'site_uuid'                => isset( $payload['site_uuid'] ) ? (string) $payload['site_uuid'] : '',
			'action_key_id'            => isset( $payload['action_key_id'] ) ? (string) $payload['action_key_id'] : '',
			'action_public_key'        => isset( $payload['action_public_key'] ) ? (string) $payload['action_public_key'] : '',
			'allowed_actions'          => array( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW ),
			'expires_at'               => isset( $payload['expires_at'] ) ? (string) $payload['expires_at'] : '',
		);

		$encoded = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES );

		if ( false === $encoded ) {
			return new WP_Error( 'action_opt_in_token_encode_failed', __( 'The action opt-in token could not be encoded.', 'alynt-drime-backups-dashboard' ) );
		}

		return self::TOKEN_PREFIX . self::base64url_encode( $encoded );
	}

	/**
	 * Base64url encodes binary or JSON data without padding.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private static function base64url_encode( $value ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Transport encoding for opaque opt-in tokens, not code obfuscation.
		return rtrim( strtr( base64_encode( (string) $value ), '+/', '-_' ), '=' );
	}
}
