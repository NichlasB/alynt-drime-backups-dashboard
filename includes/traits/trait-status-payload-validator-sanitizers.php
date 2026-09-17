<?php
/**
 * Client status payload validator sanitizer helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes allowlisted uploader status schema v1 payload fields.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Sanitizers {
	/**
	 * Recursively detects forbidden keys anywhere in a payload.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return bool
	 */
	private function contains_forbidden_field( array $payload ) {
		foreach ( $payload as $key => $value ) {
			if ( in_array( sanitize_key( (string) $key ), $this->forbidden_fields, true ) ) {
				return true;
			}

			if ( is_array( $value ) && $this->contains_forbidden_field( $value ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets a non-negative integer field.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $field Field name.
	 * @return int
	 */
	private function non_negative_int( array $payload, $field ) {
		return isset( $payload[ $field ] ) ? max( 0, absint( $payload[ $field ] ) ) : 0;
	}

	/**
	 * Gets a boolean field.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $field Field name.
	 * @return bool
	 */
	private function bool_field( array $payload, $field ) {
		return ! empty( $payload[ $field ] );
	}

	/**
	 * Sanitizes warning records.
	 *
	 * @param mixed $warnings Warning records.
	 * @param int   $limit Maximum warning records.
	 * @return array<int,array<string,string>>
	 */
	private function warnings( $warnings, $limit = 20 ) {
		if ( ! is_array( $warnings ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $warnings, 0, max( 0, (int) $limit ) ) as $warning ) {
			if ( ! is_array( $warning ) ) {
				continue;
			}

			$clean[] = array(
				'code'    => isset( $warning['code'] ) ? sanitize_key( (string) $warning['code'] ) : '',
				'message' => isset( $warning['message'] ) ? sanitize_text_field( (string) $warning['message'] ) : '',
			);
		}

		return $clean;
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
