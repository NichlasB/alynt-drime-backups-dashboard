<?php
/**
 * Diagnostics backup source metrics.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds support-safe aggregate metrics from source-level backup evidence.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Backup_Source_Metrics {
	/**
	 * Builds support-safe aggregate source diagnostics from one snapshot.
	 *
	 * @param array<string,mixed>|null $snapshot Snapshot.
	 * @return array<string,int>
	 */
	private function backup_source_diagnostics( $snapshot ) {
		$counts = array(
			'reporting_sites'            => 0,
			'stale_sources'              => 0,
			'no_upload_evidence_sources' => 0,
			'not_configured_sources'     => 0,
		);

		if ( empty( $snapshot ) || ! is_array( $snapshot ) ) {
			return $counts;
		}

		$payload = $this->diagnostic_payload_from_snapshot( $snapshot );

		if ( empty( $payload['backup_sources'] ) || ! is_array( $payload['backup_sources'] ) ) {
			return $counts;
		}

		$counts['reporting_sites'] = 1;

		foreach ( $payload['backup_sources'] as $source ) {
			if ( ! is_array( $source ) ) {
				continue;
			}

			$freshness = isset( $source['freshness_status'] ) ? sanitize_key( $source['freshness_status'] ) : '';

			if ( 'stale' === $freshness ) {
				++$counts['stale_sources'];
			} elseif ( 'no_upload_evidence' === $freshness ) {
				++$counts['no_upload_evidence_sources'];
			} elseif ( 'not_configured' === $freshness ) {
				++$counts['not_configured_sources'];
			}
		}

		return $counts;
	}

	/**
	 * Gets a decoded payload from a diagnostics snapshot.
	 *
	 * @param array<string,mixed> $snapshot Snapshot.
	 * @return array<string,mixed>
	 */
	private function diagnostic_payload_from_snapshot( array $snapshot ) {
		if ( isset( $snapshot['decoded_payload'] ) && is_array( $snapshot['decoded_payload'] ) ) {
			return $snapshot['decoded_payload'];
		}

		if ( empty( $snapshot['payload_json'] ) ) {
			return array();
		}

		$decoded = json_decode( (string) $snapshot['payload_json'], true );

		return is_array( $decoded ) ? $decoded : array();
	}
}
