<?php
/**
 * Admin page remote action history cleanup details helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides support-safe cleanup-preview detail labels for remote action history.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Cleanup_Details {
	/**
	 * Gets a cleanup-preview context from a history row.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return array<string,mixed>
	 */
	private function remote_action_cleanup_preview_context( array $row ) {
		$context = ! empty( $row['redacted_context_json'] ) ? json_decode( (string) $row['redacted_context_json'], true ) : array();

		if ( ! is_array( $context ) || empty( $context['cleanup_preview'] ) || ! is_array( $context['cleanup_preview'] ) ) {
			return array();
		}

		return $context['cleanup_preview'];
	}

	/**
	 * Gets a compact cleanup-preview details summary.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return string
	 */
	private function remote_action_cleanup_summary_label( array $row ) {
		$preview = $this->remote_action_cleanup_preview_context( $row );

		if ( empty( $preview ) ) {
			return '';
		}

		return sprintf(
			/* translators: 1: eligible item count, 2: approximate byte label. */
			__( 'Preview: %1$d eligible temporary items; approx %2$s', 'alynt-drime-backups-dashboard' ),
			isset( $preview['total_eligible_count'] ) ? max( 0, (int) $preview['total_eligible_count'] ) : 0,
			$this->remote_action_bytes_label( isset( $preview['total_approx_bytes'] ) ? (int) $preview['total_approx_bytes'] : 0 )
		);
	}

	/**
	 * Gets support-safe cleanup-preview details.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return string
	 */
	private function remote_action_cleanup_details_label( array $row ) {
		$preview = $this->remote_action_cleanup_preview_context( $row );

		if ( empty( $preview ) ) {
			return '';
		}

		$parts      = array( $this->remote_action_cleanup_summary_label( $row ) );
		$categories = isset( $preview['categories'] ) && is_array( $preview['categories'] ) ? $preview['categories'] : array();

		foreach ( $categories as $category ) {
			if ( ! is_array( $category ) ) {
				continue;
			}

			$parts[] = sprintf(
				/* translators: 1: cleanup category label, 2: eligible count, 3: approximate size, 4: age band, 5: reason code. */
				__( '%1$s: %2$d eligible; approx %3$s; age %4$s; reason %5$s', 'alynt-drime-backups-dashboard' ),
				$this->cleanup_category_label( isset( $category['category'] ) ? (string) $category['category'] : '' ),
				isset( $category['eligible_count'] ) ? max( 0, (int) $category['eligible_count'] ) : 0,
				$this->remote_action_bytes_label( isset( $category['approx_bytes'] ) ? (int) $category['approx_bytes'] : 0 ),
				isset( $category['age_band'] ) && '' !== (string) $category['age_band'] ? sanitize_key( (string) $category['age_band'] ) : __( 'unknown', 'alynt-drime-backups-dashboard' ),
				isset( $category['reason_code'] ) && '' !== (string) $category['reason_code'] ? sanitize_key( (string) $category['reason_code'] ) : __( 'not reported', 'alynt-drime-backups-dashboard' )
			);
		}

		if ( ! empty( $preview['expires_at'] ) ) {
			$parts[] = sprintf(
				/* translators: %s: preview expiry date/time. */
				__( 'Preview expires %s', 'alynt-drime-backups-dashboard' ),
				$this->datetime_label( (string) $preview['expires_at'] )
			);
		}

		$parts[] = __( 'Evidence only: no cleanup apply, delete, retention, restore, credential, or Drime action was requested', 'alynt-drime-backups-dashboard' );

		return implode( '; ', array_filter( $parts ) );
	}

	/**
	 * Gets a cleanup category operator label.
	 *
	 * @param string $category Category.
	 * @return string
	 */
	private function cleanup_category_label( $category ) {
		return Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::CLEANUP_CATEGORY_UPLOADER_TEMP === sanitize_key( $category )
			? __( 'Uploader temporary artifacts', 'alynt-drime-backups-dashboard' )
			: __( 'Unknown category', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a compact byte label without exposing paths or object identifiers.
	 *
	 * @param int $bytes Bytes.
	 * @return string
	 */
	private function remote_action_bytes_label( $bytes ) {
		$bytes = max( 0, (int) $bytes );

		if ( function_exists( 'size_format' ) ) {
			return size_format( $bytes );
		}

		if ( $bytes >= 1073741824 ) {
			return round( $bytes / 1073741824, 1 ) . ' GB';
		}

		if ( $bytes >= 1048576 ) {
			return round( $bytes / 1048576, 1 ) . ' MB';
		}

		if ( $bytes >= 1024 ) {
			return round( $bytes / 1024, 1 ) . ' KB';
		}

		return $bytes . ' B';
	}
}
