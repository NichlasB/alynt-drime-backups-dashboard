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
Provides remote action dispatcher scalar helper methods.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Utils {


	/**
	 * Creates a UUID.
	 *
	 * @return string
	 */
	private function create_uuid() {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			return (string) wp_generate_uuid4();
		}

		$bytes    = random_bytes( 16 );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );
		$hex      = bin2hex( $bytes );

		return sprintf( '%s-%s-%s-%s-%s', substr( $hex, 0, 8 ), substr( $hex, 8, 4 ), substr( $hex, 12, 4 ), substr( $hex, 16, 4 ), substr( $hex, 20, 12 ) );
	}

	/**
	 * Creates a bounded idempotency key.
	 *
	 * @return string
	 */
	private function create_idempotency_key() {
		return 'adb-act-' . str_replace( '-', '', $this->create_uuid() );
	}

	/**
	 * Bounds a safe result summary.
	 *
	 * @param string $summary Summary.
	 * @return string
	 */
	private function bounded_summary( $summary ) {
		$summary = sanitize_text_field( (string) $summary );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $summary, 0, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::MAX_RESULT_SUMMARY_LENGTH );
		}

		return substr( $summary, 0, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::MAX_RESULT_SUMMARY_LENGTH );
	}
}
