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
		$date = $this->absolute_display_date( $value );
		if ( null === $date ) {
			return '<span aria-label="' . esc_attr__( 'Not available', 'alynt-drime-backups-dashboard' ) . '">-</span>';
		}

		$timestamp = $date->getTimestamp();

		$date_format = function_exists( 'get_option' ) ? (string) get_option( 'date_format', 'Y-m-d' ) : 'Y-m-d';
		$time_format = function_exists( 'get_option' ) ? (string) get_option( 'time_format', 'H:i' ) : 'H:i';
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
	 * Formats a date/time value for plain-text table summaries.
	 *
	 * @param string $value UTC date value.
	 * @return string
	 */
	private function datetime_label( $value ) {
		$date = $this->absolute_display_date( $value );
		if ( null === $date ) {
			return '-';
		}

		$timestamp = $date->getTimestamp();

		$date_format = function_exists( 'get_option' ) ? (string) get_option( 'date_format', 'Y-m-d' ) : 'Y-m-d';
		$time_format = function_exists( 'get_option' ) ? (string) get_option( 'time_format', 'H:i' ) : 'H:i';

		return function_exists( 'wp_date' ) ? wp_date( $date_format . ', ' . $time_format, $timestamp ) : gmdate( 'Y-m-d H:i', $timestamp ) . ' UTC';
	}

	/**
	 * Accepts complete absolute SQL/ISO timestamps for display only.
	 *
	 * @param string $value Stored timestamp, never a relative expression.
	 * @return DateTimeImmutable|null
	 */
	private function absolute_display_date( $value ) {
		if ( ! is_string( $value ) || ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})[ T](?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:\.\d{1,6})?(?:Z|[+-](?:[01]\d|2[0-3]):[0-5]\d)?$/D', $value, $parts ) || ! checkdate( (int) $parts[2], (int) $parts[3], (int) $parts[1] ) ) {
			return null;
		}

		try {
			return new DateTimeImmutable( $value, new DateTimeZone( 'UTC' ) );
		} catch ( Exception $exception ) {
			return null;
		}
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
