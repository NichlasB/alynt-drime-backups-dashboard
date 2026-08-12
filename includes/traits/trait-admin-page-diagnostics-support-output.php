<?php
/**
 * Admin page diagnostics support output.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the redacted support-copy output.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Support_Output {
	/**
	 * Renders support-copy diagnostics.
	 *
	 * @param array<string,mixed> $support Support summary.
	 * @return void
	 */
	private function render_support_copy_output( array $support ) {
		$encoded = wp_json_encode( $support, JSON_PRETTY_PRINT );

		echo '<div class="adbd-panel adbd-support-panel"><h3>' . esc_html__( 'Support Copy', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'Copy this redacted summary when support needs scheduler and polling context. It intentionally omits client domains, site labels, pairing tokens, polling secrets, authorization headers, raw payloads, and raw response bodies.', 'alynt-drime-backups-dashboard' ) . '</p>';
		if ( false === $encoded ) {
			echo '<div class="notice notice-error inline" role="alert"><p>' . esc_html__( 'The support summary could not be prepared. Please try again after refreshing the page.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
		}
		echo '<textarea id="adbd-support-copy" class="large-text code" rows="12" readonly="readonly" aria-label="' . esc_attr__( 'Redacted support summary', 'alynt-drime-backups-dashboard' ) . '">';
		echo esc_textarea( false === $encoded ? '{}' : $encoded );
		echo '</textarea><p class="adbd-actions"><button type="button" class="button button-primary adbd-copy-button" hidden data-copy-target="adbd-support-copy" data-success-message="' . esc_attr__( 'Redacted support summary copied to the clipboard.', 'alynt-drime-backups-dashboard' ) . '" data-error-message="' . esc_attr__( 'The summary could not be copied automatically. Select it and copy it manually.', 'alynt-drime-backups-dashboard' ) . '">' . esc_html__( 'Copy Support Summary', 'alynt-drime-backups-dashboard' ) . '</button></p><p class="adbd-copy-status" role="status" aria-live="polite"></p></div></div>';
	}
}
