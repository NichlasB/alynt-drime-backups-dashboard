<?php
/**
 * Status payload validator backup source helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes schema-v1 backup source evidence for status payload validation.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Backup_Sources {
	/**
	 * Sanitizes optional per-source backup freshness summaries.
	 *
	 * @param mixed $sources Source summaries.
	 * @return array<string,array<string,mixed>>
	 */
	private function backup_sources( $sources ) {
		if ( ! is_array( $sources ) ) {
			return array();
		}

		$clean = array();

		foreach ( array( 'server', 'wpvivid' ) as $source_key ) {
			if ( empty( $sources[ $source_key ] ) || ! is_array( $sources[ $source_key ] ) ) {
				continue;
			}

			$source               = $sources[ $source_key ];
			$warnings             = $this->warnings( isset( $source['warnings'] ) ? $source['warnings'] : array(), 10 );
			$clean[ $source_key ] = array(
				'source_key'                         => $source_key,
				'source_label'                       => $this->bounded_text( isset( $source['source_label'] ) ? (string) $source['source_label'] : '', self::MAX_SOURCE_LABEL_LENGTH ),
				'configured'                         => $this->bool_field( $source, 'configured' ),
				'has_upload_evidence'                => $this->bool_field( $source, 'has_upload_evidence' ),
				'queued_count'                       => $this->non_negative_int( $source, 'queued_count' ),
				'uploaded_count'                     => $this->non_negative_int( $source, 'uploaded_count' ),
				'failed_count'                       => $this->non_negative_int( $source, 'failed_count' ),
				'remote_registry_count'              => $this->non_negative_int( $source, 'remote_registry_count' ),
				'latest_created_at'                  => $this->non_negative_int( $source, 'latest_created_at' ),
				'latest_uploaded_at'                 => $this->non_negative_int( $source, 'latest_uploaded_at' ),
				'latest_upload_age_seconds'          => $this->non_negative_int( $source, 'latest_upload_age_seconds' ),
				'latest_remote_status'               => $this->source_status( isset( $source['latest_remote_status'] ) ? (string) $source['latest_remote_status'] : '', array( 'uploaded', 'trashed', '' ) ),
				'latest_inventory_count'             => $this->non_negative_int( $source, 'latest_inventory_count' ),
				'latest_inventory_evidence'          => $this->source_status( isset( $source['latest_inventory_evidence'] ) ? (string) $source['latest_inventory_evidence'] : '', array( 'generic_outbox_remote_catalog', 'generic_outbox_remote_index', 'local_upload_registry', '' ) ),
				'latest_source_activity_at'          => $this->non_negative_int( $source, 'latest_source_activity_at' ),
				'latest_source_activity_age_seconds' => $this->non_negative_int( $source, 'latest_source_activity_age_seconds' ),
				'source_activity_evidence'           => $this->source_status( isset( $source['source_activity_evidence'] ) ? (string) $source['source_activity_evidence'] : '', array( 'wpvivid_backup_log', 'wpvivid_local_archive', '' ) ),
				'local_candidate_count'              => $this->non_negative_int( $source, 'local_candidate_count' ),
				'freshness_status'                   => $this->source_status( isset( $source['freshness_status'] ) ? (string) $source['freshness_status'] : '', array( 'not_configured', 'no_upload_evidence', 'stale', 'fresh', '' ) ),
				'freshness_window_seconds'           => $this->non_negative_int( $source, 'freshness_window_seconds' ),
				'warning_count'                      => count( $warnings ),
				'warnings'                           => $warnings,
			);

			if ( 'wpvivid' === $source_key ) {
				$clean[ $source_key ]['schedule_policy'] = $this->schedule_policy( isset( $source['schedule_policy'] ) ? $source['schedule_policy'] : array() );
			}
		}

		return $clean;
	}

	/**
	 * Sanitizes optional redacted WPvivid schedule policy evidence.
	 *
	 * @param mixed $policy Schedule policy payload.
	 * @return array<string,mixed>
	 */
	private function schedule_policy( $policy ) {
		if ( ! is_array( $policy ) ) {
			$policy = array();
		}

		return array(
			'detected'              => $this->bool_field( $policy, 'detected' ),
			'basis'                 => $this->source_status( isset( $policy['basis'] ) ? (string) $policy['basis'] : '', array( 'wpvivid_schedule_setting', 'wpvivid_schedule_addon_setting', 'wpvivid_incremental_schedules', 'wp_cron_event', 'not_detected', '' ) ),
			'recurrence'            => isset( $policy['recurrence'] ) ? sanitize_key( (string) $policy['recurrence'] ) : '',
			'schedule_count'        => $this->non_negative_int( $policy, 'schedule_count' ),
			'interval_seconds'      => $this->non_negative_int( $policy, 'interval_seconds' ),
			'grace_seconds'         => $this->non_negative_int( $policy, 'grace_seconds' ),
			'policy_window_seconds' => $this->non_negative_int( $policy, 'policy_window_seconds' ),
		);
	}

	/**
	 * Sanitizes an allowlisted source status label.
	 *
	 * @param string            $value Raw value.
	 * @param array<int,string> $allowed Allowed labels.
	 * @return string
	 */
	private function source_status( $value, array $allowed ) {
		$value = sanitize_key( $value );

		return in_array( $value, $allowed, true ) ? $value : '';
	}
}
