<?php
/**
 * Shared bootstrap for admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! function_exists( 'wp_nonce_field' ) ) {
	/**
	 * Minimal nonce-field shim.
	 *
	 * @param string $action Nonce action.
	 * @return void
	 */
	function wp_nonce_field( $action ) {
		echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( $action ) . '">';
	}
}

if ( ! function_exists( 'esc_html_e' ) ) {
	/**
	 * Minimal esc_html_e shim.
	 *
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return void
	 */
	function esc_html_e( $text, $domain = 'default' ) {
		echo esc_html__( $text, $domain );
	}
}

if ( ! function_exists( 'esc_attr_e' ) ) {
	/**
	 * Minimal esc_attr_e shim.
	 *
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return void
	 */
	function esc_attr_e( $text, $domain = 'default' ) {
		echo esc_attr__( $text, $domain );
	}
}
if ( ! function_exists( 'number_format_i18n' ) ) {
	/**
	 * Minimal number_format_i18n shim.
	 *
	 * @param float|int $number Number.
	 * @param int       $decimals Decimals.
	 * @return string
	 */
	function number_format_i18n( $number, $decimals = 0 ) {
		return number_format( (float) $number, (int) $decimals );
	}
}

require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-local-actions.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-archive-actions.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-capabilities.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-basic-detail-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-attention-recovery-history-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-status-history-detail-helpers.php';
require_once __DIR__ . '/admin-page-polling-state-rendering-counting-doubles.php';
require_once __DIR__ . '/admin-page-polling-state-rendering-test-harness.php';
require_once __DIR__ . '/admin-page-rendering-remote-action-double.php';
require_once __DIR__ . '/admin-page-remote-action-rendering-fixtures.php';
