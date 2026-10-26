<?php
/**
 * Admin page backup source timestamp helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.29
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides timestamp formatting helpers for source-level backup evidence.
 *
 * @since 0.1.29
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Timestamp_Helpers {
	/**
	 * Builds timestamp HTML from a source Unix timestamp.
	 *
	 * @param mixed $timestamp Timestamp.
	 * @return string
	 */
	private function source_timestamp_html( $timestamp ) {
		$timestamp = max( 0, (int) $timestamp );

		if ( $timestamp <= 0 ) {
			return '<span aria-label="' . esc_attr__( 'Not available', 'alynt-drime-backups-dashboard' ) . '">-</span>';
		}

		return $this->time_html( gmdate( 'Y-m-d H:i:s', $timestamp ) );
	}

	/**
	 * Builds compact timestamp evidence for the Sites table.
	 *
	 * @param string $label Timestamp label.
	 * @param mixed  $timestamp Unix timestamp.
	 * @return string
	 */
	private function source_compact_timestamp_html( $label, $timestamp ) {
		return sprintf(
			'<span class="adbd-row-meta adbd-source-line"><span class="adbd-source-line-label">%1$s:</span> %2$s</span>',
			esc_html( $label ),
			$this->source_timestamp_html( $timestamp )
		);
	}
}
