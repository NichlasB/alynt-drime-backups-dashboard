<?php
/**
 * Admin page diagnostic formatters.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats scalar diagnostic values for operator-facing tables.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostic_Formatters {
	/**
	 * Gets a scalar diagnostic value.
	 *
	 * @param array<string,mixed> $data Diagnostic data.
	 * @param string              $key Data key.
	 * @return string
	 */
	private function diagnostic_value( array $data, $key ) {
		if ( ! isset( $data[ $key ] ) || is_array( $data[ $key ] ) || is_object( $data[ $key ] ) ) {
			return '';
		}

		return (string) $data[ $key ];
	}

	/**
	 * Gets an integer diagnostic value.
	 *
	 * @param array<string,mixed> $data Diagnostic data.
	 * @param string              $key Data key.
	 * @return int
	 */
	private function diagnostic_int( array $data, $key ) {
		return isset( $data[ $key ] ) ? max( 0, (int) $data[ $key ] ) : 0;
	}

	/**
	 * Formats seconds for operator display.
	 *
	 * @param int $seconds Seconds.
	 * @return string
	 */
	private function seconds_label( $seconds ) {
		$seconds = max( 0, (int) $seconds );

		if ( 0 === $seconds ) {
			return '-';
		}

		if ( 0 === $seconds % 3600 ) {
			return sprintf(
				/* translators: %d: hours. */
				__( '%d hours', 'alynt-drime-backups-dashboard' ),
				(int) ( $seconds / 3600 )
			);
		}

		if ( 0 === $seconds % 60 ) {
			return sprintf(
				/* translators: %d: minutes. */
				__( '%d minutes', 'alynt-drime-backups-dashboard' ),
				(int) ( $seconds / 60 )
			);
		}

		return sprintf(
			/* translators: %d: seconds. */
			__( '%d seconds', 'alynt-drime-backups-dashboard' ),
			$seconds
		);
	}

	/**
	 * Formats the scheduler lock state.
	 *
	 * @param mixed $value Lock value.
	 * @return string
	 */
	private function lock_label( $value ) {
		if ( null === $value ) {
			return __( 'Unavailable outside WordPress runtime', 'alynt-drime-backups-dashboard' );
		}

		return $value ? __( 'Active', 'alynt-drime-backups-dashboard' ) : __( 'Not active', 'alynt-drime-backups-dashboard' );
	}
}
