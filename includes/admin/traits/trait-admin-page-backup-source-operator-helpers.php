<?php
/**
 * Admin page backup source operator detail helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.40
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides operator-facing source-level backup evidence labels.
 *
 * @since 0.1.40
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Operator_Helpers {
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
	 * Gets a source activity label.
	 *
	 * @param array<string,mixed> $source Source summary.
	 * @return string
	 */
	private function source_activity_label( array $source ) {
		$evidence = isset( $source['source_activity_evidence'] ) ? sanitize_key( (string) $source['source_activity_evidence'] ) : '';

		if ( 'wpvivid_local_archive' === $evidence ) {
			return __( 'Local WPvivid ZIP available', 'alynt-drime-backups-dashboard' );
		}

		if ( 'wpvivid_backup_log' === $evidence ) {
			return __( 'WPvivid backup log observed; no local ZIP may be available for Alynt upload', 'alynt-drime-backups-dashboard' );
		}

		return __( 'Not reported', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a concise operator-facing source summary.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source summary.
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function source_operator_reason_label( $source_key, array $source, array $site = array() ) {
		if ( empty( $source['configured'] ) ) {
			return __( 'This source is not configured on the client report.', 'alynt-drime-backups-dashboard' );
		}

		$freshness = $this->source_effective_freshness_status( $source_key, $source, $site );
		$failed    = isset( $source['failed_count'] ) ? max( 0, (int) $source['failed_count'] ) : 0;
		$queued    = isset( $source['queued_count'] ) ? max( 0, (int) $source['queued_count'] ) : 0;

		if ( $failed > 0 && $queued > 0 ) {
			return __( 'Failed uploads are still queued for this source.', 'alynt-drime-backups-dashboard' );
		}

		$warning = $this->source_operator_warning_label( $source, $freshness );

		if ( '' !== $warning ) {
			return $warning;
		}

		if ( 'external_optional' === $freshness ) {
			return __( 'This source is marked external/optional, so Alynt-uploaded evidence is not required on this dashboard.', 'alynt-drime-backups-dashboard' );
		}

		if ( 'within_policy' === $freshness ) {
			return __( 'The uploader marked this source stale, but the latest upload is still inside the dashboard freshness policy.', 'alynt-drime-backups-dashboard' );
		}

		if ( 'stale' === $freshness ) {
			return __( 'The latest upload is older than the expected freshness window.', 'alynt-drime-backups-dashboard' );
		}

		if ( 'no_upload_evidence' === $freshness ) {
			return __( 'No Alynt-uploaded backup evidence is reported for this source.', 'alynt-drime-backups-dashboard' );
		}

		if ( 'not_configured' === $freshness ) {
			return __( 'This source is reported as not configured.', 'alynt-drime-backups-dashboard' );
		}

		if ( $queued > 0 ) {
			return __( 'The latest upload is fresh; queued packages are waiting to upload.', 'alynt-drime-backups-dashboard' );
		}

		return __( 'The latest upload is within the expected freshness window.', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets the first current source warning that should be called out to operators.
	 *
	 * @param array<string,mixed> $source Source summary.
	 * @param string              $freshness Effective freshness.
	 * @return string
	 */
	private function source_operator_warning_label( array $source, $freshness ) {
		if ( empty( $source['warnings'] ) || ! is_array( $source['warnings'] ) ) {
			return '';
		}

		foreach ( $source['warnings'] as $warning ) {
			if ( ! is_array( $warning ) ) {
				continue;
			}

			$code = isset( $warning['code'] ) ? sanitize_key( (string) $warning['code'] ) : '';

			if ( '' === $code || 'source_queue_not_empty' === $code ) {
				continue;
			}

			if ( 'source_latest_upload_stale' === $code && 'within_policy' === $freshness ) {
				continue;
			}

			if ( in_array( $code, array( 'source_latest_upload_stale', 'source_no_upload_evidence' ), true ) && 'external_optional' === $freshness ) {
				continue;
			}

			$message = isset( $warning['message'] ) ? sanitize_text_field( (string) $warning['message'] ) : '';

			if ( '' !== $message ) {
				return $message;
			}

			return sprintf(
				/* translators: %s: source warning code. */
				__( 'The uploader reports source warning %s.', 'alynt-drime-backups-dashboard' ),
				$code
			);
		}

		return '';
	}
}
