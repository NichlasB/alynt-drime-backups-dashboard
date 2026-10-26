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
}
