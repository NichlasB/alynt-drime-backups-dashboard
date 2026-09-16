<?php
/**
 * Admin page backup source evidence rendering.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders allowlisted source-level backup freshness and inventory evidence.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Evidence {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Evidence_Helpers;

	/**
	 * Renders detailed source-level backup evidence.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param array<string,mixed> $site Site row.
	 * @return void
	 */
	private function render_backup_sources_detail( array $payload, array $site = array() ) {
		$sources = $this->backup_sources_from_payload( $payload );

		echo '<div class="adbd-backup-sources">';
		echo '<h4>' . esc_html__( 'Backup Sources', 'alynt-drime-backups-dashboard' ) . '</h4>';

		if ( empty( $sources ) ) {
			echo '<p class="description">' . esc_html__( 'This uploader version has not reported source-level backup freshness evidence yet.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
			return;
		}

		echo '<div class="adbd-source-grid">';

		foreach ( $sources as $source_key => $source ) {
			echo '<section class="adbd-source-card is-' . esc_attr( $source_key ) . '" aria-label="' . esc_attr( $this->backup_source_label( $source_key, $source ) ) . '">';
			echo '<h5>' . esc_html( $this->backup_source_label( $source_key, $source ) ) . ' ' . $this->source_freshness_badge( $source_key, $source, $site ) . '</h5>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge helper returns escaped markup.
			echo '<dl class="adbd-detail-list adbd-source-list">';
			$this->render_detail_item( __( 'Configured', 'alynt-drime-backups-dashboard' ), ! empty( $source['configured'] ) ? __( 'Yes', 'alynt-drime-backups-dashboard' ) : __( 'No', 'alynt-drime-backups-dashboard' ) );
			$this->render_detail_item( __( 'Expected freshness', 'alynt-drime-backups-dashboard' ), $this->source_policy_label( $source_key, $source, $site ) );
			$this->render_detail_item( __( 'Latest backup/package', 'alynt-drime-backups-dashboard' ), $this->source_timestamp_html( isset( $source['latest_created_at'] ) ? $source['latest_created_at'] : 0 ), true );
			$this->render_detail_item( __( 'Latest upload', 'alynt-drime-backups-dashboard' ), $this->source_timestamp_html( isset( $source['latest_uploaded_at'] ) ? $source['latest_uploaded_at'] : 0 ), true );
			$this->render_detail_item( __( 'Current remote inventory', 'alynt-drime-backups-dashboard' ), $this->source_inventory_label( $source ) );
			$this->render_detail_item( __( 'Queued / Failed', 'alynt-drime-backups-dashboard' ), sprintf( '%1$d / %2$d', isset( $source['queued_count'] ) ? max( 0, (int) $source['queued_count'] ) : 0, isset( $source['failed_count'] ) ? max( 0, (int) $source['failed_count'] ) : 0 ) );
			$this->render_detail_item( __( 'Evidence type', 'alynt-drime-backups-dashboard' ), $this->source_inventory_evidence_label( isset( $source['latest_inventory_evidence'] ) ? (string) $source['latest_inventory_evidence'] : '' ) );
			if ( 'wpvivid' === $source_key ) {
				$this->render_detail_item( __( 'WPvivid activity', 'alynt-drime-backups-dashboard' ), $this->source_activity_label( $source ) );
				$this->render_detail_item( __( 'Latest WPvivid activity', 'alynt-drime-backups-dashboard' ), $this->source_timestamp_html( isset( $source['latest_source_activity_at'] ) ? $source['latest_source_activity_at'] : 0 ), true );
				$this->render_detail_item( __( 'Local WPvivid ZIPs', 'alynt-drime-backups-dashboard' ), sprintf( '%d', isset( $source['local_candidate_count'] ) ? max( 0, (int) $source['local_candidate_count'] ) : 0 ) );
			}
			echo '</dl>';
			$this->render_source_warnings( $source );
			echo '</section>';
		}

		echo '</div>';
		echo '<p class="description">' . esc_html__( 'Source evidence is reported by the client uploader as a redacted operational hint. This dashboard does not receive Drime credentials and does not perform a direct Drime inventory audit.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Builds compact escaped backup-source evidence for table rows.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function backup_sources_compact_html( array $payload, array $site = array() ) {
		$sources = $this->backup_sources_from_payload( $payload );

		if ( empty( $sources ) ) {
			return '<span class="adbd-row-meta">' . esc_html__( 'Source evidence: not reported', 'alynt-drime-backups-dashboard' ) . '</span>';
		}

		$html = '<ul class="adbd-source-summary">';

		foreach ( $sources as $source_key => $source ) {
			$html .= '<li><strong>' . esc_html( $this->backup_source_label( $source_key, $source ) ) . ':</strong> ';
			$html .= esc_html( $this->source_freshness_label( $this->source_effective_freshness_status( $source_key, $source, $site ) ) );
			$html .= '<span class="adbd-row-meta adbd-source-line"><span class="adbd-source-line-label">' . esc_html__( 'Expected', 'alynt-drime-backups-dashboard' ) . ':</span> ' . esc_html( $this->source_policy_label( $source_key, $source, $site ) ) . '</span>';
			$html .= '<span class="adbd-row-meta adbd-source-line">' . esc_html( $this->source_inventory_label( $source ) ) . '</span>';
			$html .= $this->source_compact_timestamp_html( __( 'Latest backup/package', 'alynt-drime-backups-dashboard' ), isset( $source['latest_created_at'] ) ? $source['latest_created_at'] : 0 );
			$html .= $this->source_compact_timestamp_html( __( 'Latest upload', 'alynt-drime-backups-dashboard' ), isset( $source['latest_uploaded_at'] ) ? $source['latest_uploaded_at'] : 0 );
			if ( 'wpvivid' === $source_key ) {
				$html .= '<span class="adbd-row-meta adbd-source-line"><span class="adbd-source-line-label">' . esc_html__( 'WPvivid activity', 'alynt-drime-backups-dashboard' ) . ':</span> ' . esc_html( $this->source_activity_label( $source ) ) . '</span>';
				$html .= $this->source_compact_timestamp_html( __( 'Latest WPvivid activity', 'alynt-drime-backups-dashboard' ), isset( $source['latest_source_activity_at'] ) ? $source['latest_source_activity_at'] : 0 );
			}
			$html .= '</li>';
		}

		return $html . '</ul>';
	}

	/**
	 * Renders source warning summaries.
	 *
	 * @param array<string,mixed> $source Source summary.
	 * @return void
	 */
	private function render_source_warnings( array $source ) {
		if ( empty( $source['warnings'] ) || ! is_array( $source['warnings'] ) ) {
			return;
		}

		echo '<ul class="adbd-source-warnings">';

		foreach ( $source['warnings'] as $warning ) {
			if ( ! is_array( $warning ) ) {
				continue;
			}

			$code    = isset( $warning['code'] ) ? sanitize_key( $warning['code'] ) : '';
			$message = isset( $warning['message'] ) ? sanitize_text_field( $warning['message'] ) : '';

			echo '<li><code>' . esc_html( $code ) . '</code> ' . esc_html( $message ) . '</li>';
		}

		echo '</ul>';
	}
}
