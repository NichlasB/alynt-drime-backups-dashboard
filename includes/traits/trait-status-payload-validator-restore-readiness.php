<?php
/**
 * Status payload validator restore-readiness helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.52
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes optional restore-readiness evidence for status payload validation.
 *
 * @since 0.1.52
 */
trait Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Restore_Readiness {
	/**
	 * Sanitizes optional restore-readiness evidence.
	 *
	 * @param mixed $readiness Restore-readiness evidence.
	 * @return array<string,mixed>
	 */
	private function restore_readiness( $readiness ) {
		if ( ! is_array( $readiness ) ) {
			return array();
		}

		$candidates = array();
		$raw        = isset( $readiness['candidates'] ) && is_array( $readiness['candidates'] ) ? $readiness['candidates'] : array();

		foreach ( array_slice( $raw, 0, 6 ) as $candidate ) {
			if ( ! is_array( $candidate ) ) {
				continue;
			}

			$source = $this->source_status( isset( $candidate['source'] ) ? (string) $candidate['source'] : '', array( 'server', 'wpvivid' ) );

			if ( '' === $source ) {
				continue;
			}

			$candidates[] = array(
				'source'                    => $source,
				'candidate_ref'             => $this->safe_restore_candidate_ref( isset( $candidate['candidate_ref'] ) ? (string) $candidate['candidate_ref'] : '' ),
				'latest_backup_finished_at' => $this->bounded_text( isset( $candidate['latest_backup_finished_at'] ) ? (string) $candidate['latest_backup_finished_at'] : '', 40 ),
				'component_state'           => $this->restore_readiness_state( isset( $candidate['component_state'] ) ? (string) $candidate['component_state'] : '', array( 'complete', 'partial', 'missing', 'unknown' ) ),
				'checksum_state'            => $this->restore_readiness_state( isset( $candidate['checksum_state'] ) ? (string) $candidate['checksum_state'] : '', array( 'verified', 'failed', 'not_reported', 'unknown' ) ),
				'manifest_state'            => $this->restore_readiness_state( isset( $candidate['manifest_state'] ) ? (string) $candidate['manifest_state'] : '', array( 'compatible', 'incompatible', 'not_reported', 'unknown' ) ),
				'sidecar_state'             => $this->restore_readiness_state( isset( $candidate['sidecar_state'] ) ? (string) $candidate['sidecar_state'] : '', array( 'present', 'missing', 'not_reported', 'unknown' ) ),
				'age_seconds'               => $this->non_negative_int( $candidate, 'age_seconds' ),
				'warnings'                  => $this->restore_readiness_warnings( isset( $candidate['warnings'] ) ? $candidate['warnings'] : array() ),
			);
		}

		return array(
			'schema_version' => isset( $readiness['schema_version'] ) ? max( 1, absint( $readiness['schema_version'] ) ) : 1,
			'generated_at'   => $this->bounded_text( isset( $readiness['generated_at'] ) ? (string) $readiness['generated_at'] : '', 40 ),
			'overall_state'  => $this->restore_readiness_state( isset( $readiness['overall_state'] ) ? (string) $readiness['overall_state'] : '', array( 'not_reported', 'evidence_available', 'incomplete', 'stale', 'incompatible', 'unknown' ) ),
			'candidates'     => $candidates,
		);
	}

	/**
	 * Sanitizes restore-readiness state values, defaulting unknown values to unknown.
	 *
	 * @param string            $value Raw value.
	 * @param array<int,string> $allowed Allowed values.
	 * @return string
	 */
	private function restore_readiness_state( $value, array $allowed ) {
		$value = sanitize_key( $value );

		return in_array( $value, $allowed, true ) ? $value : 'unknown';
	}

	/**
	 * Sanitizes an opaque candidate reference.
	 *
	 * @param string $value Candidate reference.
	 * @return string
	 */
	private function safe_restore_candidate_ref( $value ) {
		$value = trim( (string) $value );

		if ( ! preg_match( '/^[A-Za-z0-9_-]{1,80}$/', $value ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * Sanitizes restore-readiness warning codes.
	 *
	 * @param mixed $warnings Warning records.
	 * @return array<int,string>
	 */
	private function restore_readiness_warnings( $warnings ) {
		if ( ! is_array( $warnings ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $warnings, 0, 10 ) as $warning ) {
			$code = is_array( $warning ) && isset( $warning['code'] ) ? sanitize_key( (string) $warning['code'] ) : sanitize_key( (string) $warning );

			if ( '' !== $code ) {
				$clean[] = $code;
			}
		}

		return $clean;
	}
}
