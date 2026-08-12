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
	/**
	 * Renders detailed source-level backup evidence.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return void
	 */
	private function render_backup_sources_detail( array $payload ) {
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
			echo '<h5>' . esc_html( $this->backup_source_label( $source_key, $source ) ) . ' ' . $this->source_freshness_badge( $source ) . '</h5>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge helper returns escaped markup.
			echo '<dl class="adbd-detail-list adbd-source-list">';
			$this->render_detail_item( __( 'Configured', 'alynt-drime-backups-dashboard' ), ! empty( $source['configured'] ) ? __( 'Yes', 'alynt-drime-backups-dashboard' ) : __( 'No', 'alynt-drime-backups-dashboard' ) );
			$this->render_detail_item( __( 'Latest upload', 'alynt-drime-backups-dashboard' ), $this->source_timestamp_html( isset( $source['latest_uploaded_at'] ) ? $source['latest_uploaded_at'] : 0 ), true );
			$this->render_detail_item( __( 'Current remote inventory', 'alynt-drime-backups-dashboard' ), $this->source_inventory_label( $source ) );
			$this->render_detail_item( __( 'Queued / Failed', 'alynt-drime-backups-dashboard' ), sprintf( '%1$d / %2$d', isset( $source['queued_count'] ) ? max( 0, (int) $source['queued_count'] ) : 0, isset( $source['failed_count'] ) ? max( 0, (int) $source['failed_count'] ) : 0 ) );
			$this->render_detail_item( __( 'Evidence type', 'alynt-drime-backups-dashboard' ), $this->source_inventory_evidence_label( isset( $source['latest_inventory_evidence'] ) ? (string) $source['latest_inventory_evidence'] : '' ) );
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
	 * @return string
	 */
	private function backup_sources_compact_html( array $payload ) {
		$sources = $this->backup_sources_from_payload( $payload );

		if ( empty( $sources ) ) {
			return '<span class="adbd-row-meta">' . esc_html__( 'Source evidence: not reported', 'alynt-drime-backups-dashboard' ) . '</span>';
		}

		$html = '<ul class="adbd-source-summary">';

		foreach ( $sources as $source_key => $source ) {
			$html .= '<li><strong>' . esc_html( $this->backup_source_label( $source_key, $source ) ) . ':</strong> ';
			$html .= esc_html( $this->source_freshness_label( isset( $source['freshness_status'] ) ? (string) $source['freshness_status'] : '' ) );
			$html .= ' <span class="adbd-row-meta">' . esc_html( $this->source_inventory_label( $source ) ) . '</span></li>';
		}

		return $html . '</ul>';
	}

	/**
	 * Gets allowlisted backup sources from a payload.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,array<string,mixed>>
	 */
	private function backup_sources_from_payload( array $payload ) {
		if ( empty( $payload['backup_sources'] ) || ! is_array( $payload['backup_sources'] ) ) {
			return array();
		}

		$sources = array();

		foreach ( array( 'server', 'wpvivid' ) as $source_key ) {
			if ( ! empty( $payload['backup_sources'][ $source_key ] ) && is_array( $payload['backup_sources'][ $source_key ] ) ) {
				$sources[ $source_key ] = $payload['backup_sources'][ $source_key ];
			}
		}

		return $sources;
	}

	/**
	 * Gets a source label.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source summary.
	 * @return string
	 */
	private function backup_source_label( $source_key, array $source ) {
		if ( ! empty( $source['source_label'] ) ) {
			return (string) $source['source_label'];
		}

		return 'wpvivid' === $source_key ? __( 'WPvivid', 'alynt-drime-backups-dashboard' ) : __( 'Server', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Builds a source freshness badge.
	 *
	 * @param array<string,mixed> $source Source summary.
	 * @return string
	 */
	private function source_freshness_badge( array $source ) {
		$freshness = isset( $source['freshness_status'] ) ? sanitize_key( $source['freshness_status'] ) : '';

		return '<span class="adbd-source-freshness is-' . esc_attr( $freshness ) . '">' . esc_html( $this->source_freshness_label( $freshness ) ) . '</span>';
	}

	/**
	 * Gets a source freshness label.
	 *
	 * @param string $freshness Freshness status.
	 * @return string
	 */
	private function source_freshness_label( $freshness ) {
		$labels = array(
			'fresh'              => __( 'Fresh', 'alynt-drime-backups-dashboard' ),
			'stale'              => __( 'Stale', 'alynt-drime-backups-dashboard' ),
			'no_upload_evidence' => __( 'No upload evidence', 'alynt-drime-backups-dashboard' ),
			'not_configured'     => __( 'Not configured', 'alynt-drime-backups-dashboard' ),
		);
		$key    = sanitize_key( $freshness );

		return isset( $labels[ $key ] ) ? $labels[ $key ] : __( 'Unknown', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a source inventory label.
	 *
	 * @param array<string,mixed> $source Source summary.
	 * @return string
	 */
	private function source_inventory_label( array $source ) {
		$count = isset( $source['latest_inventory_count'] ) ? max( 0, (int) $source['latest_inventory_count'] ) : 0;

		return sprintf(
			/* translators: %d: number of current package sets. */
			_n( '%d current package set', '%d current package sets', $count, 'alynt-drime-backups-dashboard' ),
			$count
		);
	}

	/**
	 * Gets an inventory-evidence label.
	 *
	 * @param string $evidence Evidence key.
	 * @return string
	 */
	private function source_inventory_evidence_label( $evidence ) {
		$labels = array(
			'generic_outbox_remote_catalog' => __( 'Server remote catalog sidecar', 'alynt-drime-backups-dashboard' ),
			'generic_outbox_remote_index'   => __( 'Server remote index sidecar', 'alynt-drime-backups-dashboard' ),
			'local_upload_registry'         => __( 'Uploader local upload registry', 'alynt-drime-backups-dashboard' ),
			''                              => __( 'Not reported', 'alynt-drime-backups-dashboard' ),
		);
		$key    = sanitize_key( $evidence );

		return isset( $labels[ $key ] ) ? $labels[ $key ] : __( 'Not reported', 'alynt-drime-backups-dashboard' );
	}

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
