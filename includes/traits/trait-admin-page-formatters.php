<?php
/**
 * Admin page scalar formatters.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides status and scalar formatting helpers for admin page sections.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Formatters {
	/**
	 * Builds an escaped status cell with plain-language guidance.
	 *
	 * @param array<string,string> $status Status classification.
	 * @return string
	 */
	private function status_cell( array $status ) {
		$message  = isset( $status['message'] ) ? (string) $status['message'] : '';
		$category = isset( $status['category'] ) ? (string) $status['category'] : '';
		$guidance = $this->status_guidance( $category );

		return sprintf(
			'%1$s<p class="adbd-status-message">%2$s</p>%3$s',
			$this->status_badge( $status ),
			esc_html( $message ),
			'' === $guidance ? '' : '<p class="description adbd-next-step">' . esc_html( $guidance ) . '</p>'
		);
	}

	/**
	 * Builds an accessible status badge.
	 *
	 * @param array<string,string> $status Status classification.
	 * @return string
	 */
	private function status_badge( array $status ) {
		$category = isset( $status['category'] ) ? sanitize_key( $status['category'] ) : '';
		$label    = isset( $status['label'] ) ? (string) $status['label'] : $this->classifier->label( $category );
		$icons    = array(
			'working'         => 'yes-alt',
			'pending'         => 'clock',
			'paused'          => 'controls-pause',
			'incompatible'    => 'warning',
			'not_reporting'   => 'dismiss',
			'needs_attention' => 'warning',
			'not_configured'  => 'admin-generic',
		);
		$icon     = isset( $icons[ $category ] ) ? $icons[ $category ] : 'marker';

		return sprintf(
			'<span class="adbd-status is-%1$s"><span class="dashicons dashicons-%2$s" aria-hidden="true"></span><span>%3$s</span></span>',
			esc_attr( $category ),
			esc_attr( $icon ),
			esc_html( $label )
		);
	}

	/**
	 * Gets a display name for a site.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function site_name( array $site ) {
		if ( ! empty( $site['site_label'] ) ) {
			return (string) $site['site_label'];
		}

		if ( ! empty( $site['expected_origin'] ) ) {
			return (string) $site['expected_origin'];
		}

		return __( 'Unnamed site', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a translated environment label.
	 *
	 * @param string $environment Environment slug.
	 * @return string
	 */
	private function environment_label( $environment ) {
		$labels = array(
			'production'  => __( 'Production', 'alynt-drime-backups-dashboard' ),
			'staging'     => __( 'Staging', 'alynt-drime-backups-dashboard' ),
			'development' => __( 'Development', 'alynt-drime-backups-dashboard' ),
			'other'       => __( 'Other', 'alynt-drime-backups-dashboard' ),
		);
		$key    = sanitize_key( $environment );

		return isset( $labels[ $key ] ) ? $labels[ $key ] : __( 'Unspecified', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a translated enrollment-state label.
	 *
	 * @param string $state Enrollment state.
	 * @return string
	 */
	private function enrollment_label( $state ) {
		$labels = array(
			'pending'             => __( 'Awaiting client opt-in', 'alynt-drime-backups-dashboard' ),
			'awaiting_first_poll' => __( 'Awaiting first valid report', 'alynt-drime-backups-dashboard' ),
			'active'              => __( 'Enrolled and polling', 'alynt-drime-backups-dashboard' ),
			'revoked'             => __( 'Revoked locally', 'alynt-drime-backups-dashboard' ),
		);
		$key    = sanitize_key( $state );

		return isset( $labels[ $key ] ) ? $labels[ $key ] : ( '' === $key ? __( 'Unspecified', 'alynt-drime-backups-dashboard' ) : $key );
	}

	/**
	 * Gets a safe polling credential state.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function credential_state( array $site ) {
		if ( ! empty( $site['polling_key_id'] ) && ! empty( $site['polling_secret_ciphertext'] ) ) {
			return __( 'Encrypted per-site polling credential material is stored. Its plaintext is never displayed.', 'alynt-drime-backups-dashboard' );
		}

		if ( isset( $site['enrollment_status'] ) && 'pending' === $site['enrollment_status'] ) {
			return __( 'A verifier for the display-once pairing token is stored while client opt-in is pending.', 'alynt-drime-backups-dashboard' );
		}

		if ( isset( $site['enrollment_status'] ) && 'revoked' === $site['enrollment_status'] ) {
			return __( 'Pairing and polling credential fields were cleared locally.', 'alynt-drime-backups-dashboard' );
		}

		return __( 'Polling credential material is missing; a new client opt-in pairing may be required.', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a safe error label.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function safe_error_label( array $site ) {
		$code    = isset( $site['last_error_code'] ) ? sanitize_key( $site['last_error_code'] ) : '';
		$summary = isset( $site['last_error_summary'] ) ? sanitize_text_field( $site['last_error_summary'] ) : '';
		$error   = trim( $code . ' ' . $summary );

		return '' === $error ? '-' : $error;
	}

	/**
	 * Gets plain-language operator guidance for a status category.
	 *
	 * @param string $category Status category.
	 * @return string
	 */
	private function status_guidance( $category ) {
		$guidance = array(
			'pending'         => __( 'Next step: complete client opt-in pairing, then wait for the first read-only status check.', 'alynt-drime-backups-dashboard' ),
			'paused'          => __( 'Next step: review why polling was paused locally before resuming.', 'alynt-drime-backups-dashboard' ),
			'incompatible'    => __( 'Next step: ask the site owner to update the uploader so it publishes supported schema version 1.', 'alynt-drime-backups-dashboard' ),
			'not_reporting'   => __( 'Next step: confirm the site is reachable and the uploader pairing remains active.', 'alynt-drime-backups-dashboard' ),
			'needs_attention' => __( 'Next step: ask the site owner to review the uploader warnings, failed queue, and WP-Cron status.', 'alynt-drime-backups-dashboard' ),
			'not_configured'  => __( 'Next step: ask the site owner to configure a supported backup source in the uploader.', 'alynt-drime-backups-dashboard' ),
			'working'         => __( 'No action is currently indicated by the latest redacted report.', 'alynt-drime-backups-dashboard' ),
		);

		return isset( $guidance[ $category ] ) ? $guidance[ $category ] : '';
	}

	/**
	 * Maps a status category to a WordPress notice tone.
	 *
	 * @param string $category Status category.
	 * @return string
	 */
	private function status_notice_tone( $category ) {
		if ( 'working' === $category ) {
			return 'success';
		}

		if ( in_array( $category, array( 'needs_attention', 'incompatible' ), true ) ) {
			return 'error';
		}

		return in_array( $category, array( 'not_reporting', 'not_configured' ), true ) ? 'warning' : 'info';
	}

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
