<?php
/**
 * Enrollment REST validation helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates and normalizes enrollment request payloads and bearer secrets.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_REST_Validation {
	/**
	 * Validates enrollment payload shape and normalized values.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,string>|WP_Error
	 */
	private function validate_payload_shape( array $payload ) {
		if ( self::PROTOCOL_VERSION !== absint( isset( $payload['protocol_version'] ) ? $payload['protocol_version'] : 0 ) ) {
			return $this->error( 'protocol_unsupported', __( 'The enrollment protocol version is not supported.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		if ( self::STATUS_SCHEMA_VERSION !== absint( isset( $payload['status_schema_version'] ) ? $payload['status_schema_version'] : 0 ) ) {
			return $this->error( 'schema_unsupported', __( 'The client status schema version is not supported.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		$enrollment_id = $this->sanitize_uuid( isset( $payload['enrollment_id'] ) ? (string) $payload['enrollment_id'] : '' );
		$site_uuid     = $this->sanitize_uuid( isset( $payload['site_uuid'] ) ? (string) $payload['site_uuid'] : '' );
		$home_origin   = $this->origins->normalize_public_https_origin( isset( $payload['home_url'] ) ? (string) $payload['home_url'] : '' );
		$endpoint      = isset( $payload['status_endpoint'] ) ? esc_url_raw( (string) $payload['status_endpoint'] ) : '';

		if ( '' === $enrollment_id ) {
			return $this->error( 'pairing_invalid', __( 'The enrollment ID is not valid.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		if ( '' === $site_uuid ) {
			return $this->error( 'payload_invalid', __( 'The client site UUID is not valid.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		if ( '' === $home_origin ) {
			return $this->error( 'origin_mismatch', __( 'The client home URL is not a supported public HTTPS origin.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		if ( '' === $endpoint || $endpoint !== $this->origins->status_endpoint_for_origin( $home_origin ) ) {
			return $this->error( 'endpoint_invalid', __( 'The client status endpoint is not the fixed read-only route.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		return array(
			'enrollment_id'    => $enrollment_id,
			'site_uuid'        => $site_uuid,
			'home_origin'      => $home_origin,
			'status_endpoint'  => $endpoint,
			'uploader_version' => $this->bounded_text( isset( $payload['uploader_version'] ) ? (string) $payload['uploader_version'] : '', self::MAX_UPLOADER_VERSION_LENGTH ),
		);
	}

	/**
	 * Extracts the bearer secret from an authorization header.
	 *
	 * @param string $authorization Header.
	 * @return string
	 */
	private function bearer_secret( $authorization ) {
		$authorization = trim( (string) $authorization );

		if ( ! preg_match( '/^Bearer\s+([A-Za-z0-9_-]{32,})$/', $authorization, $matches ) ) {
			return '';
		}

		return $matches[1];
	}

	/**
	 * Creates a polling key ID.
	 *
	 * @return string
	 */
	private function create_polling_key_id() {
		return 'pk_' . substr( Alynt_Drime_Backups_Dashboard_Pairing_Tokens::create_secret( 18 ), 0, 24 );
	}

	/**
	 * Sanitizes a UUID.
	 *
	 * @param string $uuid UUID.
	 * @return string
	 */
	private function sanitize_uuid( $uuid ) {
		$uuid = strtolower( trim( (string) $uuid ) );

		return preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $uuid ) ? $uuid : '';
	}

	/**
	 * Sanitizes and bounds a text field before storing it in fixed-width columns.
	 *
	 * @param string $value Raw value.
	 * @param int    $max_length Maximum characters.
	 * @return string
	 */
	private function bounded_text( $value, $max_length ) {
		$value      = sanitize_text_field( (string) $value );
		$max_length = max( 1, (int) $max_length );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max_length );
		}

		return substr( $value, 0, $max_length );
	}
}
