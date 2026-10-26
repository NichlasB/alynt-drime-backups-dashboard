<?php
/**
 * Admin page site formatters.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats site identity, enrollment, environment, credential, and safe-error values.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Site_Formatters {
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
}
