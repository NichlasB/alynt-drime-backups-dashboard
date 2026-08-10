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
		$label    = isset( $status['label'] ) ? $status['label'] : '';
		$message  = isset( $status['message'] ) ? $status['message'] : '';
		$category = isset( $status['category'] ) ? $status['category'] : '';
		$guidance = $this->status_guidance( $category );

		return sprintf(
			'<span aria-label="%1$s">%2$s</span><br><span class="description">%3$s</span>%4$s',
			esc_attr( trim( $label . '. ' . $message . ' ' . $guidance ) ),
			esc_html( $label ),
			esc_html( $message ),
			'' === $guidance ? '' : '<br><span class="description">' . esc_html( $guidance ) . '</span>'
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
	 * Gets a safe polling credential state.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function credential_state( array $site ) {
		if ( ! empty( $site['polling_key_id'] ) && ! empty( $site['polling_secret_ciphertext'] ) ) {
			return __( 'Stored encrypted polling credential metadata is present.', 'alynt-drime-backups-dashboard' );
		}

		if ( isset( $site['enrollment_status'] ) && 'pending' === $site['enrollment_status'] ) {
			return __( 'Waiting for client opt-in pairing.', 'alynt-drime-backups-dashboard' );
		}

		return __( 'Polling credential metadata is missing; re-pairing may be required.', 'alynt-drime-backups-dashboard' );
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
			'pending'         => __( 'Next step: complete client opt-in pairing, then run the first read-only status check.', 'alynt-drime-backups-dashboard' ),
			'paused'          => __( 'Next step: review why polling was paused locally before resuming.', 'alynt-drime-backups-dashboard' ),
			'incompatible'    => __( 'Next step: update the client uploader or dashboard protocol before relying on this status.', 'alynt-drime-backups-dashboard' ),
			'not_reporting'   => __( 'Next step: check pairing, credentials, site reachability, and WP-Cron timing.', 'alynt-drime-backups-dashboard' ),
			'needs_attention' => __( 'Next step: review the latest redacted counts and safe error summary.', 'alynt-drime-backups-dashboard' ),
			'not_configured'  => __( 'Next step: configure a supported backup source on the client site.', 'alynt-drime-backups-dashboard' ),
			'working'         => __( 'No action needed from the dashboard.', 'alynt-drime-backups-dashboard' ),
		);

		return isset( $guidance[ $category ] ) ? $guidance[ $category ] : '';
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
