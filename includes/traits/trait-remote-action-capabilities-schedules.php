<?php
/**
 * Remote action capability helper trait.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Sanitizes schedule-management capability payload sections and scalar values.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedules {


	/**
	 * Sanitizes preview-only schedule-management capability reporting.
	 *
	 * @param mixed $capability Schedule-management capability payload.
	 * @return array<string,mixed>
	 */
	private function schedule_management( $capability ) {
		if ( ! is_array( $capability ) ) {
			return array();
		}

		if ( self::PROTOCOL_VERSION !== absint( isset( $capability['protocol_version'] ) ? $capability['protocol_version'] : 0 ) ) {
			return array();
		}

		$schedules          = $this->schedules( isset( $capability['schedules'] ) ? $capability['schedules'] : array() );
		$rollback_supported = ! empty( $capability['rollback_supported'] );
		$apply_supported    = ! empty( $capability['apply_supported'] ) && ! $rollback_supported;
		$preview_only       = ! empty( $capability['preview_only'] ) && ! $apply_supported && ! $rollback_supported;

		return array(
			'protocol_version'   => self::PROTOCOL_VERSION,
			'capability_version' => $this->non_negative_int( $capability, 'capability_version' ),
			'enabled'            => ! empty( $capability['enabled'] ) && ! $rollback_supported && ! empty( $schedules ),
			'preview_only'       => $preview_only,
			'apply_supported'    => $apply_supported,
			'rollback_supported' => false,
			'schedules'          => $schedules,
		);
	}

	/**
	 * Sanitizes schedule summaries.
	 *
	 * @param mixed $schedules Schedule summaries.
	 * @return array<int,array<string,mixed>>
	 */
	private function schedules( $schedules ) {
		if ( ! is_array( $schedules ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $schedules, 0, self::MAX_SCHEDULES ) as $schedule ) {
			if ( ! is_array( $schedule ) ) {
				continue;
			}

			$schedule_id = isset( $schedule['schedule_id'] ) ? sanitize_key( (string) $schedule['schedule_id'] ) : '';

			if ( self::SCHEDULE_SCAN_UPLOAD !== $schedule_id ) {
				continue;
			}

			$clean[] = array(
				'schedule_id'                    => $schedule_id,
				'label'                          => $this->bounded_text( isset( $schedule['label'] ) ? (string) $schedule['label'] : '', self::MAX_SCHEDULE_LABEL_LENGTH ),
				'owner'                          => $this->sanitize_schedule_owner( isset( $schedule['owner'] ) ? (string) $schedule['owner'] : '' ),
				'manageable'                     => ! empty( $schedule['manageable'] ),
				'current_cadence'                => isset( $schedule['current_cadence'] ) ? sanitize_key( (string) $schedule['current_cadence'] ) : '',
				'current_interval_seconds'       => $this->non_negative_int( $schedule, 'current_interval_seconds' ),
				'current_next_run_at'            => isset( $schedule['current_next_run_at'] ) ? sanitize_text_field( (string) $schedule['current_next_run_at'] ) : '',
				'supported_cadences'             => $this->schedule_cadences( isset( $schedule['supported_cadences'] ) ? $schedule['supported_cadences'] : array() ),
				'minimum_interval_seconds'       => $this->non_negative_int( $schedule, 'minimum_interval_seconds' ),
				'can_disable'                    => ! empty( $schedule['can_disable'] ),
				'requires_high_friction_disable' => ! empty( $schedule['requires_high_friction_disable'] ),
				'rollback_supported'             => false,
			);
		}

		return $clean;
	}

	/**
	 * Sanitizes schedule owner labels.
	 *
	 * @param string $owner Owner.
	 * @return string
	 */
	private function sanitize_schedule_owner( $owner ) {
		$owner   = sanitize_key( $owner );
		$allowed = array(
			'alynt_uploader',
			'wpvivid',
			'wordpress',
			'unknown',
			'',
		);

		return in_array( $owner, $allowed, true ) ? $owner : 'unknown';
	}

	/**
	 * Sanitizes supported cadence labels.
	 *
	 * @param mixed $cadences Cadence list.
	 * @return array<int,string>
	 */
	private function schedule_cadences( $cadences ) {
		if ( ! is_array( $cadences ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $cadences, 0, self::MAX_SCHEDULE_CADENCES ) as $cadence ) {
			$cadence = sanitize_key( (string) $cadence );

			if ( '' !== $cadence && ! in_array( $cadence, $clean, true ) ) {
				$clean[] = $cadence;
			}
		}

		return $clean;
	}

	/**
	 * Sanitizes bounded count summaries.
	 *
	 * @param mixed $counts Counts.
	 * @return array<string,int>
	 */
	private function counts( $counts ) {
		if ( ! is_array( $counts ) ) {
			return array();
		}

		$clean = array();

		foreach ( array( 'found', 'queued', 'already_known', 'upload_attempted', 'failed' ) as $key ) {
			$clean[ $key ] = $this->non_negative_int( $counts, $key );
		}

		return $clean;
	}

	/**
	 * Gets a non-negative integer field.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $field Field.
	 * @return int
	 */
	private function non_negative_int( array $payload, $field ) {
		return isset( $payload[ $field ] ) ? max( 0, (int) $payload[ $field ] ) : 0;
	}

	/**
	 * Sanitizes a bounded identifier.
	 *
	 * @param string $value Raw value.
	 * @param int    $max_length Maximum length.
	 * @return string
	 */
	private function bounded_identifier( $value, $max_length ) {
		return substr( preg_replace( '/[^A-Za-z0-9_\-\.]/', '', (string) $value ), 0, max( 1, (int) $max_length ) );
	}

	/**
	 * Sanitizes a UUID.
	 *
	 * @param string $uuid UUID.
	 * @return string
	 */
	private function sanitize_uuid( $uuid ) {
		$uuid = strtolower( trim( (string) $uuid ) );

		return preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $uuid ) ? $uuid : '';
	}

	/**
	 * Keeps valid SHA-256 fingerprints only.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	private function sha256_or_empty( $value ) {
		return preg_match( '/^[a-f0-9]{64}$/', (string) $value ) ? (string) $value : '';
	}

	/**
	 * Sanitizes and bounds text.
	 *
	 * @param string $value Raw value.
	 * @param int    $max_length Maximum length.
	 * @return string
	 */
	private function bounded_text( $value, $max_length ) {
		$value      = sanitize_text_field( (string) $value );
		$max_length = max( 1, (int) $max_length );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max_length );
		}

		return substr( $value, 0, $max_length );
	}
}
