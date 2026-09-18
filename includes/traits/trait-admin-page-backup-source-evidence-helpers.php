<?php
/**
 * Admin page backup source evidence helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides allowlisted source-level backup evidence labels and formatting helpers.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Evidence_Helpers {
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
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source summary.
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function source_freshness_badge( $source_key, array $source, array $site = array() ) {
		$freshness = $this->source_effective_freshness_status( $source_key, $source, $site );

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
			'within_policy'      => __( 'Within policy', 'alynt-drime-backups-dashboard' ),
			'stale'              => __( 'Stale', 'alynt-drime-backups-dashboard' ),
			'no_upload_evidence' => __( 'No upload evidence', 'alynt-drime-backups-dashboard' ),
			'not_configured'     => __( 'Not configured', 'alynt-drime-backups-dashboard' ),
			'external_optional'  => __( 'External / optional', 'alynt-drime-backups-dashboard' ),
		);
		$key    = sanitize_key( $freshness );

		return isset( $labels[ $key ] ) ? $labels[ $key ] : __( 'Unknown', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Formats a duration for source freshness policy display.
	 *
	 * @param int $seconds Duration in seconds.
	 * @return string
	 */
	private function source_duration_label( $seconds ) {
		$seconds = max( 0, (int) $seconds );
		$day     = 86400;
		$hour    = 3600;
		$minute  = 60;

		if ( $seconds >= $day && 0 === $seconds % $day ) {
			return sprintf(
				/* translators: %d: number of days. */
				_n( '%d day', '%d days', (int) ( $seconds / $day ), 'alynt-drime-backups-dashboard' ),
				(int) ( $seconds / $day )
			);
		}

		if ( $seconds >= $hour && 0 === $seconds % $hour ) {
			return sprintf(
				/* translators: %d: number of hours. */
				_n( '%d hour', '%d hours', (int) ( $seconds / $hour ), 'alynt-drime-backups-dashboard' ),
				(int) ( $seconds / $hour )
			);
		}

		if ( $seconds >= $minute && 0 === $seconds % $minute ) {
			return sprintf(
				/* translators: %d: number of minutes. */
				_n( '%d minute', '%d minutes', (int) ( $seconds / $minute ), 'alynt-drime-backups-dashboard' ),
				(int) ( $seconds / $minute )
			);
		}

		return sprintf(
			/* translators: %d: number of seconds. */
			_n( '%d second', '%d seconds', $seconds, 'alynt-drime-backups-dashboard' ),
			$seconds
		);
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
	 * Gets a row-level backup health summary for the Sites list.
	 *
	 * @param array<string,array<string,mixed>> $sources Backup source summaries.
	 * @param array<string,mixed>               $site Site row.
	 * @return array{label:string,state:string}
	 */
	private function backup_sources_compact_health_summary( array $sources, array $site = array() ) {
		$unknown          = false;
		$missing_evidence = false;
		$overdue_label    = '';

		foreach ( $sources as $source_key => $source ) {
			$freshness = $this->source_effective_freshness_status( $source_key, $source, $site );

			if ( 'stale' === $freshness ) {
				$overdue_label = sprintf(
					/* translators: %s: backup source label, such as Server or WPvivid. */
					__( '%s overdue', 'alynt-drime-backups-dashboard' ),
					$this->backup_source_short_label( $source_key )
				);
				break;
			}

			if ( in_array( $freshness, array( 'no_upload_evidence', 'not_configured' ), true ) ) {
				$missing_evidence = true;
				continue;
			}

			if ( ! in_array( $freshness, array( 'fresh', 'within_policy', 'external_optional' ), true ) ) {
				$unknown = true;
			}
		}

		if ( '' !== $overdue_label ) {
			return array(
				'label' => $overdue_label,
				'state' => 'warning',
			);
		}

		if ( $missing_evidence ) {
			return array(
				'label' => __( 'Missing evidence', 'alynt-drime-backups-dashboard' ),
				'state' => 'warning',
			);
		}

		if ( $unknown ) {
			return array(
				'label' => __( 'Unknown', 'alynt-drime-backups-dashboard' ),
				'state' => 'unknown',
			);
		}

		return array(
			'label' => __( 'On schedule', 'alynt-drime-backups-dashboard' ),
			'state' => 'ok',
		);
	}

	/**
	 * Gets a short allowlisted source label for at-a-glance summaries.
	 *
	 * @param string $source_key Source key.
	 * @return string
	 */
	private function backup_source_short_label( $source_key ) {
		return 'wpvivid' === $source_key ? __( 'WPvivid', 'alynt-drime-backups-dashboard' ) : __( 'Server', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a compact upload-age label.
	 *
	 * @param array<string,mixed> $source Source summary.
	 * @return string
	 */
	private function source_compact_upload_age_label( array $source ) {
		$age = isset( $source['latest_upload_age_seconds'] ) ? max( 0, (int) $source['latest_upload_age_seconds'] ) : 0;

		if ( $age <= 0 && ! empty( $source['latest_uploaded_at'] ) ) {
			$timestamp = max( 0, (int) $source['latest_uploaded_at'] );
			$age       = $timestamp > 0 ? max( 0, time() - $timestamp ) : 0;
		}

		if ( $age <= 0 ) {
			return __( 'no upload time', 'alynt-drime-backups-dashboard' );
		}

		return sprintf(
			/* translators: %s: human-readable upload age. */
			__( '%s ago', 'alynt-drime-backups-dashboard' ),
			$this->source_compact_age_duration_label( $age )
		);
	}

	/**
	 * Formats an upload age for compact row display.
	 *
	 * Unlike policy labels, upload ages should be quickly scannable even when
	 * the reported age is not an exact minute, hour, or day boundary.
	 *
	 * @param int $seconds Upload age in seconds.
	 * @return string
	 */
	private function source_compact_age_duration_label( $seconds ) {
		$seconds = max( 0, (int) $seconds );
		$day     = 86400;
		$hour    = 3600;
		$minute  = 60;

		if ( $seconds >= $day ) {
			$days = (int) floor( $seconds / $day );

			return sprintf(
				/* translators: %d: number of days. */
				_n( '%d day', '%d days', $days, 'alynt-drime-backups-dashboard' ),
				$days
			);
		}

		if ( $seconds >= $hour ) {
			$hours = (int) floor( $seconds / $hour );

			return sprintf(
				/* translators: %d: number of hours. */
				_n( '%d hour', '%d hours', $hours, 'alynt-drime-backups-dashboard' ),
				$hours
			);
		}

		if ( $seconds >= $minute ) {
			$minutes = (int) floor( $seconds / $minute );

			return sprintf(
				/* translators: %d: number of minutes. */
				_n( '%d minute', '%d minutes', $minutes, 'alynt-drime-backups-dashboard' ),
				$minutes
			);
		}

		return sprintf(
			/* translators: %d: number of seconds. */
			_n( '%d second', '%d seconds', $seconds, 'alynt-drime-backups-dashboard' ),
			$seconds
		);
	}

	/**
	 * Gets a compact inventory count label.
	 *
	 * @param array<string,mixed> $source Source summary.
	 * @return string
	 */
	private function source_inventory_compact_label( array $source ) {
		$count = isset( $source['latest_inventory_count'] ) ? max( 0, (int) $source['latest_inventory_count'] ) : 0;

		return sprintf(
			/* translators: %d: number of current backup sets. */
			_n( '%d set', '%d sets', $count, 'alynt-drime-backups-dashboard' ),
			$count
		);
	}

	/**
	 * Gets a compact expected freshness label.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source summary.
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function source_policy_compact_label( $source_key, array $source, array $site = array() ) {
		if ( $this->backup_source_is_external_optional( $site, $source_key ) ) {
			return __( 'external / optional', 'alynt-drime-backups-dashboard' );
		}

		$seconds = $this->source_policy_window_seconds( $source_key, $source );

		if ( $seconds <= 0 ) {
			return __( 'expected unknown', 'alynt-drime-backups-dashboard' );
		}

		return sprintf(
			/* translators: %s: human-readable freshness window. */
			__( 'expected ≤%s', 'alynt-drime-backups-dashboard' ),
			$this->source_duration_label( $seconds )
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
