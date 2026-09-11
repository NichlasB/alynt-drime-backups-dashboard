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
	 * Builds support-safe aggregate schedule-management diagnostics from one snapshot.
	 *
	 * @param array<string,mixed>|null $snapshot Snapshot.
	 * @return array<string,int>
	 */
	private function schedule_management_diagnostics( $snapshot ) {
		$counts = array(
			'reporting_sites'    => 0,
			'preview_only_sites' => 0,
			'unavailable_sites'  => 0,
			'reported_schedules' => 0,
		);

		if ( empty( $snapshot ) || ! is_array( $snapshot ) ) {
			return $counts;
		}

		$payload        = $this->diagnostic_payload_from_snapshot( $snapshot );
		$remote_actions = isset( $payload['remote_actions'] ) && is_array( $payload['remote_actions'] ) ? $payload['remote_actions'] : array();

		if ( empty( $remote_actions['schedule_management'] ) || ! is_array( $remote_actions['schedule_management'] ) ) {
			$counts['unavailable_sites'] = 1;
			return $counts;
		}

		$schedule_management          = $remote_actions['schedule_management'];
		$counts['reporting_sites']    = 1;
		$schedules                    = isset( $schedule_management['schedules'] ) && is_array( $schedule_management['schedules'] ) ? $schedule_management['schedules'] : array();
		$counts['reported_schedules'] = count( $schedules );

		if (
			! empty( $schedule_management['enabled'] )
			&& ! empty( $schedule_management['preview_only'] )
			&& empty( $schedule_management['apply_supported'] )
			&& empty( $schedule_management['rollback_supported'] )
		) {
			$counts['preview_only_sites'] = 1;
		} else {
			$counts['unavailable_sites'] = 1;
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
