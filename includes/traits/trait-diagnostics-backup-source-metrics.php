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
			'reporting_sites'                  => 0,
			'preview_only_sites'               => 0,
			'apply_sites'                      => 0,
			'unavailable_sites'                => 0,
			'reported_schedules'               => 0,
			'rollback_preview_supported_sites' => 0,
			'rollback_preview_hidden_sites'    => 0,
			'rollback_apply_advertised_sites'  => 0,
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
			&& empty( $schedule_management['rollback_supported'] )
		) {
			if ( ! empty( $schedule_management['apply_supported'] ) ) {
				$counts['apply_sites'] = 1;
			} else {
				$counts['preview_only_sites'] = 1;
			}

			if ( ! empty( $schedule_management['rollback_preview_supported'] ) ) {
				$counts['rollback_preview_supported_sites'] = 1;
			} else {
				$counts['rollback_preview_hidden_sites'] = 1;
			}
		} else {
			$counts['unavailable_sites'] = 1;
		}

		if ( ! empty( $schedule_management['rollback_supported'] ) ) {
			$counts['rollback_apply_advertised_sites'] = 1;
		}

		return $counts;
	}

	/**
	 * Builds support-safe aggregate cleanup-preview diagnostics from one snapshot.
	 *
	 * @since 0.1.51
	 *
	 * @param array<string,mixed>|null $snapshot Snapshot.
	 * @return array<string,int>
	 */
	private function cleanup_preview_diagnostics( $snapshot ) {
		$counts = array(
			'reporting_sites'                    => 0,
			'preview_supported_sites'            => 0,
			'unavailable_sites'                  => 0,
			'apply_or_mutation_advertised_sites' => 0,
			'supported_categories'               => 0,
		);

		if ( empty( $snapshot ) || ! is_array( $snapshot ) ) {
			return $counts;
		}

		$payload        = $this->diagnostic_payload_from_snapshot( $snapshot );
		$remote_actions = isset( $payload['remote_actions'] ) && is_array( $payload['remote_actions'] ) ? $payload['remote_actions'] : array();

		if ( empty( $remote_actions['cleanup_management'] ) || ! is_array( $remote_actions['cleanup_management'] ) ) {
			$counts['unavailable_sites'] = 1;
			return $counts;
		}

		$cleanup_management             = $remote_actions['cleanup_management'];
		$counts['reporting_sites']      = 1;
		$categories                     = isset( $cleanup_management['supported_categories'] ) && is_array( $cleanup_management['supported_categories'] ) ? $cleanup_management['supported_categories'] : array();
		$counts['supported_categories'] = count( $categories );

		if (
			! empty( $cleanup_management['enabled'] )
			&& ! empty( $cleanup_management['preview_supported'] )
			&& empty( $cleanup_management['apply_supported'] )
		) {
			$counts['preview_supported_sites'] = 1;
		} else {
			$counts['unavailable_sites'] = 1;
		}

		if (
			! empty( $cleanup_management['apply_supported'] )
			|| ! empty( $cleanup_management['remote_cleanup_available'] )
			|| ! empty( $cleanup_management['cleanup_apply_available'] )
			|| ! empty( $cleanup_management['drime_cleanup_available'] )
			|| ! empty( $cleanup_management['backup_deletion_available'] )
		) {
			$counts['apply_or_mutation_advertised_sites'] = 1;
		}

		return $counts;
	}

	/**
	 * Builds support-safe aggregate restore-readiness diagnostics from one snapshot.
	 *
	 * @since 0.1.53
	 *
	 * @param array<string,mixed>|null $snapshot Snapshot.
	 * @return array<string,int>
	 */
	private function restore_readiness_diagnostics( $snapshot ) {
		$counts = array(
			'reporting_sites'       => 0,
			'evidence_sites'        => 0,
			'incomplete_sites'      => 0,
			'stale_sites'           => 0,
			'incompatible_sites'    => 0,
			'unknown_sites'         => 0,
			'reported_candidates'   => 0,
			'complete_candidates'   => 0,
			'incomplete_candidates' => 0,
			'server_candidates'     => 0,
			'server_complete'       => 0,
			'server_incomplete'     => 0,
			'wpvivid_candidates'    => 0,
			'wpvivid_complete'      => 0,
			'wpvivid_incomplete'    => 0,
		);

		if ( empty( $snapshot ) || ! is_array( $snapshot ) ) {
			return $counts;
		}

		$payload = $this->diagnostic_payload_from_snapshot( $snapshot );

		if ( empty( $payload['restore_readiness'] ) || ! is_array( $payload['restore_readiness'] ) ) {
			return $counts;
		}

		$readiness                 = $payload['restore_readiness'];
		$counts['reporting_sites'] = 1;
		$overall_state             = isset( $readiness['overall_state'] ) ? sanitize_key( $readiness['overall_state'] ) : 'unknown';

		switch ( $overall_state ) {
			case 'evidence_available':
				$counts['evidence_sites'] = 1;
				break;
			case 'incomplete':
				$counts['incomplete_sites'] = 1;
				break;
			case 'stale':
				$counts['stale_sites'] = 1;
				break;
			case 'incompatible':
				$counts['incompatible_sites'] = 1;
				break;
			default:
				$counts['unknown_sites'] = 1;
				break;
		}

		$candidates                    = isset( $readiness['candidates'] ) && is_array( $readiness['candidates'] ) ? $readiness['candidates'] : array();
		$counts['reported_candidates'] = count( $candidates );

		foreach ( $candidates as $candidate ) {
			if ( ! is_array( $candidate ) ) {
				continue;
			}

			$component_state = isset( $candidate['component_state'] ) ? sanitize_key( $candidate['component_state'] ) : 'unknown';
			$checksum_state  = isset( $candidate['checksum_state'] ) ? sanitize_key( $candidate['checksum_state'] ) : 'unknown';
			$manifest_state  = isset( $candidate['manifest_state'] ) ? sanitize_key( $candidate['manifest_state'] ) : 'unknown';
			$sidecar_state   = isset( $candidate['sidecar_state'] ) ? sanitize_key( $candidate['sidecar_state'] ) : 'unknown';
			$source          = isset( $candidate['source'] ) ? sanitize_key( $candidate['source'] ) : '';
			$is_complete     = (
				'complete' === $component_state
				&& 'verified' === $checksum_state
				&& 'compatible' === $manifest_state
				&& 'present' === $sidecar_state
			);

			if ( $is_complete ) {
				++$counts['complete_candidates'];
			} else {
				++$counts['incomplete_candidates'];
			}

			if ( in_array( $source, array( 'server', 'wpvivid' ), true ) ) {
				++$counts[ $source . '_candidates' ];
				++$counts[ $source . ( $is_complete ? '_complete' : '_incomplete' ) ];
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
