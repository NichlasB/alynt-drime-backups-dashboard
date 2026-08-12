<?php
/**
 * Admin page status formatters.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats dashboard status badges, cells, guidance, and notice tones.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Status_Formatters {
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
}
