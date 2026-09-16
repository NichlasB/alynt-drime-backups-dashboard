<?php
/**
 * Remote action repository helper trait.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Provides scalar sanitizer and identifier helper methods.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Sanitizers {


	/**
	 * Creates a UUID.
	 *
	 * @return string
	 */
	private function create_uuid() {
		$bytes    = random_bytes( 16 );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );
		$hex      = bin2hex( $bytes );

		return sprintf(
			'%s-%s-%s-%s-%s',
			substr( $hex, 0, 8 ),
			substr( $hex, 8, 4 ),
			substr( $hex, 12, 4 ),
			substr( $hex, 16, 4 ),
			substr( $hex, 20, 12 )
		);
	}

	/**
	 * Sanitizes an identifier.
	 *
	 * @param string $value Raw value.
	 * @param int    $max_length Max length.
	 * @return string
	 */
	private function bounded_identifier( $value, $max_length ) {
		return substr( preg_replace( '/[^A-Za-z0-9_\-\.]/', '', (string) $value ), 0, max( 1, (int) $max_length ) );
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
	 * Sanitizes and bounds text.
	 *
	 * @param string $value Raw value.
	 * @param int    $max_length Max length.
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

	/**
	 * Keeps valid SHA-256 fingerprints only.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	private function sha256_or_empty( $value ) {
		return preg_match( '/^[a-f0-9]{64}$/', (string) $value ) ? (string) $value : '';
	}

	/**
	 * Uses a date value or fallback.
	 *
	 * @param string $date Date.
	 * @param string $fallback Fallback.
	 * @return string
	 */
	private function date_or_default( $date, $fallback ) {
		$timestamp = strtotime( (string) $date );

		return false === $timestamp ? $fallback : gmdate( 'Y-m-d H:i:s', $timestamp );
	}
}
