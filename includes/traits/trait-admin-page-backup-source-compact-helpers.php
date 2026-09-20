<?php
/**
 * Admin page backup source compact display helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.40
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides compact source-level backup evidence labels for Sites rows.
 *
 * @since 0.1.40
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Compact_Helpers {
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
}
