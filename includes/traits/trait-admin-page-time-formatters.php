<?php
/**
 * Admin page time formatters.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats absolute and relative timestamps for admin output.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters {
	/**
	 * Builds a time element with relative and exact values.
	 *
	 * @param string $value UTC date value.
	 * @return string
	 */
	private function time_html( $value ) {
		if ( '' === (string) $value ) {
			return '<span aria-label="' . esc_attr__( 'Not available', 'alynt-drime-backups-dashboard' ) . '">-</span>';
		}

		try {
			$date      = new DateTimeImmutable( (string) $value, new DateTimeZone( 'UTC' ) );
			$timestamp = $date->getTimestamp();
		} catch ( Exception $exception ) {
			unset( $exception );
			return esc_html( (string) $value );
		}

		$date_format = function_exists( 'get_option' ) ? (string) get_option( 'date_format' ) : 'Y-m-d';
		$time_format = function_exists( 'get_option' ) ? (string) get_option( 'time_format' ) : 'H:i';
		$absolute    = function_exists( 'wp_date' ) ? wp_date( $date_format . ', ' . $time_format, $timestamp ) : gmdate( 'Y-m-d H:i', $timestamp ) . ' UTC';
		$now         = time();

		if ( function_exists( 'human_time_diff' ) ) {
			$difference = human_time_diff( min( $timestamp, $now ), max( $timestamp, $now ) );
			$relative   = $timestamp > $now
				? sprintf( /* translators: %s: human-readable duration. */ __( 'in %s', 'alynt-drime-backups-dashboard' ), $difference )
				: sprintf( /* translators: %s: human-readable duration. */ __( '%s ago', 'alynt-drime-backups-dashboard' ), $difference );
		} else {
			$relative = $absolute;
		}

		return sprintf(
			'<time datetime="%1$s"><span class="adbd-time-relative">%2$s</span><span class="adbd-time-exact">%3$s</span></time>',
			esc_attr( gmdate( 'c', $timestamp ) ),
			esc_html( $relative ),
			esc_html( $absolute )
		);
	}

	/**
	 * Formats a date-ish value or returns a dash.
	 *
	 * @param string $value Date value.
	 * @return string
	 */
	private function date_or_dash( $value ) {
		if ( '' === (string) $value ) {
			return '-';
		}

		return (string) $value;
	}
}
