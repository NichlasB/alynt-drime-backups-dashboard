<?php
/**
 * Enrollment REST route argument helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines REST route args for enrollment payload validation.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_REST_Route_Args {
	/**
	 * Builds route argument definitions for the enrollment endpoint.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function enrollment_route_args() {
		return array(
			'protocol_version'      => $this->integer_route_arg( array( $this, 'validate_protocol_version_arg' ) ),
			'status_schema_version' => $this->integer_route_arg( array( $this, 'validate_status_schema_version_arg' ) ),
			'enrollment_id'         => $this->uuid_route_arg(),
			'site_uuid'             => $this->uuid_route_arg(),
			'home_url'              => array(
				'required'          => true,
				'sanitize_callback' => array( $this, 'sanitize_public_origin_arg' ),
				'validate_callback' => array( $this, 'validate_public_origin_arg' ),
			),
			'status_endpoint'       => array(
				'required'          => true,
				'sanitize_callback' => array( $this, 'sanitize_status_endpoint_arg' ),
				'validate_callback' => array( $this, 'validate_status_endpoint_arg' ),
			),
			'uploader_version'      => array(
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => array( $this, 'validate_scalar_arg' ),
			),
		);
	}

	/**
	 * Sanitizes an integer route argument.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Value.
	 * @return int
	 */
	public function sanitize_integer_arg( $value ) {
		return absint( $value );
	}

	/**
	 * Validates the enrollment protocol version.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public function validate_protocol_version_arg( $value ) {
		return self::PROTOCOL_VERSION === absint( $value );
	}

	/**
	 * Validates the status schema version.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public function validate_status_schema_version_arg( $value ) {
		return self::STATUS_SCHEMA_VERSION === absint( $value );
	}

	/**
	 * Sanitizes a UUID argument.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	public function sanitize_uuid_arg( $value ) {
		return $this->sanitize_uuid( (string) $value );
	}

	/**
	 * Validates a UUID argument.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public function validate_uuid_arg( $value ) {
		return '' !== $this->sanitize_uuid_arg( $value );
	}

	/**
	 * Sanitizes a public HTTPS origin.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	public function sanitize_public_origin_arg( $value ) {
		return $this->origins->normalize_public_https_origin( (string) $value );
	}

	/**
	 * Validates a public HTTPS origin.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public function validate_public_origin_arg( $value ) {
		return '' !== $this->sanitize_public_origin_arg( $value );
	}

	/**
	 * Sanitizes a fixed uploader status endpoint.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	public function sanitize_status_endpoint_arg( $value ) {
		$endpoint = $this->canonical_status_endpoint( $value );

		return '' === $endpoint ? esc_url_raw( (string) $value ) : $endpoint;
	}

	/**
	 * Validates a fixed uploader status endpoint.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public function validate_status_endpoint_arg( $value ) {
		return '' !== $this->canonical_status_endpoint( $value );
	}

	/**
	 * Validates a scalar optional argument.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public function validate_scalar_arg( $value ) {
		return is_scalar( $value ) || null === $value;
	}

	/**
	 * Builds an integer argument definition.
	 *
	 * @param callable $validator Validator.
	 * @return array<string,mixed>
	 */
	private function integer_route_arg( $validator ) {
		return array(
			'required'          => true,
			'sanitize_callback' => array( $this, 'sanitize_integer_arg' ),
			'validate_callback' => $validator,
		);
	}

	/**
	 * Builds a UUID argument definition.
	 *
	 * @return array<string,mixed>
	 */
	private function uuid_route_arg() {
		return array(
			'required'          => true,
			'sanitize_callback' => array( $this, 'sanitize_uuid_arg' ),
			'validate_callback' => array( $this, 'validate_uuid_arg' ),
		);
	}

	/**
	 * Canonicalizes the fixed uploader status endpoint.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	private function canonical_status_endpoint( $value ) {
		$parts = parse_url( trim( (string) $value ) );

		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return '';
		}

		if ( isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] ) ) {
			return '';
		}

		$origin = $this->origins->normalize_public_https_origin( strtolower( $parts['scheme'] ) . '://' . strtolower( rtrim( (string) $parts['host'], '.' ) ) . ( isset( $parts['port'] ) ? ':' . absint( $parts['port'] ) : '' ) );

		return '' === $origin || '/wp-json/alynt-drime-backups-uploader/v1/status' !== ( isset( $parts['path'] ) ? (string) $parts['path'] : '' )
			? ''
			: $this->origins->status_endpoint_for_origin( $origin );
	}
}
