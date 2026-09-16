<?php
/**
 * Status classifier backup source helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles backup-source freshness decisions for the status classifier.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Status_Classifier_Backup_Sources {
	/**
	 * Determines whether source-level freshness evidence needs attention.
	 *
	 * @param array<string,mixed> $payload Status payload.
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function backup_source_needs_attention( array $payload, array $site ) {
		if ( empty( $payload['backup_sources'] ) || ! is_array( $payload['backup_sources'] ) ) {
			return false;
		}

		foreach ( $payload['backup_sources'] as $source_key => $source ) {
			if ( ! is_array( $source ) ) {
				continue;
			}

			if ( $this->source_needs_attention( sanitize_key( (string) $source_key ), $source, $site ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines whether one source-level evidence summary needs attention.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source evidence.
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function source_needs_attention( $source_key, array $source, array $site ) {
		$configured               = ! empty( $source['configured'] );
		$requires_upload_evidence = $this->source_requires_upload_evidence( $source_key, $site );

		if ( ! $configured ) {
			return false;
		}

		if ( $this->source_failed_uploads_need_attention( $source ) ) {
			return true;
		}

		$freshness = isset( $source['freshness_status'] ) ? sanitize_key( (string) $source['freshness_status'] ) : '';

		if ( 'no_upload_evidence' === $freshness && $requires_upload_evidence ) {
			return true;
		}

		if ( 'stale' === $freshness && $this->source_is_outside_dashboard_policy( $source_key, $source, $site ) ) {
			return true;
		}

		if ( empty( $source['warnings'] ) || ! is_array( $source['warnings'] ) ) {
			return false;
		}

		foreach ( $source['warnings'] as $warning ) {
			if ( ! is_array( $warning ) ) {
				continue;
			}

			$code = isset( $warning['code'] ) ? sanitize_key( (string) $warning['code'] ) : '';

			if ( '' === $code || 'source_queue_not_empty' === $code ) {
				continue;
			}

			if ( in_array( $code, array( 'source_latest_upload_stale', 'source_no_upload_evidence' ), true ) && ! $requires_upload_evidence ) {
				continue;
			}

			if ( 'source_latest_upload_stale' === $code && ! $this->source_is_outside_dashboard_policy( $source_key, $source, $site ) ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Determines whether source failed upload evidence represents a current attention condition.
	 *
	 * @param array<string,mixed> $source Source evidence.
	 * @return bool
	 */
	private function source_failed_uploads_need_attention( array $source ) {
		$failed = isset( $source['failed_count'] ) ? max( 0, (int) $source['failed_count'] ) : 0;

		if ( 0 === $failed ) {
			return false;
		}

		$queued = isset( $source['queued_count'] ) ? max( 0, (int) $source['queued_count'] ) : 0;

		return $queued > 0;
	}

	/**
	 * Determines whether source age is outside the dashboard policy.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source evidence.
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function source_is_outside_dashboard_policy( $source_key, array $source, array $site ) {
		if ( 'wpvivid' !== $source_key ) {
			return true;
		}

		if ( ! $this->source_requires_upload_evidence( $source_key, $site ) ) {
			return false;
		}

		if ( empty( $source['has_upload_evidence'] ) ) {
			return true;
		}

		$age = isset( $source['latest_upload_age_seconds'] ) ? max( 0, (int) $source['latest_upload_age_seconds'] ) : 0;

		if ( $age <= 0 ) {
			return true;
		}

		return $age > $this->source_policy_window_seconds( $source_key, $source );
	}

	/**
	 * Determines whether the dashboard should require Alynt-uploaded evidence.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function source_requires_upload_evidence( $source_key, array $site ) {
		if ( 'wpvivid' !== $source_key ) {
			return true;
		}

		return ! $this->source_policy->source_is_external_optional( $site, $source_key );
	}

	/**
	 * Gets the dashboard policy window for source-level evidence.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source evidence.
	 * @return int
	 */
	private function source_policy_window_seconds( $source_key, array $source ) {
		$reported = isset( $source['freshness_window_seconds'] ) ? max( 0, (int) $source['freshness_window_seconds'] ) : 0;

		if ( 'wpvivid' === $source_key ) {
			$schedule_policy = isset( $source['schedule_policy'] ) && is_array( $source['schedule_policy'] ) ? $source['schedule_policy'] : array();
			$detected_window = ! empty( $schedule_policy['detected'] ) && isset( $schedule_policy['policy_window_seconds'] ) ? max( 0, (int) $schedule_policy['policy_window_seconds'] ) : 0;

			if ( $detected_window > 0 ) {
				return max( $detected_window, $reported );
			}

			return max( self::WPVIVID_POLICY_WINDOW, $reported );
		}

		return $reported;
	}
}
