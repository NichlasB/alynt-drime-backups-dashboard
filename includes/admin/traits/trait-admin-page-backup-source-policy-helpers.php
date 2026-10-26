<?php
/**
 * Admin page backup source policy helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides dashboard-local backup source freshness policy helpers.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Policy_Helpers {
	/**
	 * Gets effective freshness after applying dashboard display policy.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source summary.
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function source_effective_freshness_status( $source_key, array $source, array $site = array() ) {
		$freshness = isset( $source['freshness_status'] ) ? sanitize_key( $source['freshness_status'] ) : '';

		if ( $this->backup_source_is_external_optional( $site, $source_key ) ) {
			return 'external_optional';
		}

		if ( 'wpvivid' === $source_key && 'stale' === $freshness && ! empty( $source['has_upload_evidence'] ) ) {
			$age = isset( $source['latest_upload_age_seconds'] ) ? max( 0, (int) $source['latest_upload_age_seconds'] ) : 0;

			if ( $age > 0 && $age <= $this->source_policy_window_seconds( $source_key, $source ) ) {
				return 'within_policy';
			}
		}

		return $freshness;
	}

	/**
	 * Gets a human-readable dashboard freshness policy label.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source summary.
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function source_policy_label( $source_key, array $source, array $site = array() ) {
		if ( $this->backup_source_is_external_optional( $site, $source_key ) ) {
			return __( 'external / optional on this dashboard', 'alynt-drime-backups-dashboard' );
		}

		$seconds = $this->source_policy_window_seconds( $source_key, $source );

		if ( $seconds <= 0 ) {
			return __( 'Not reported', 'alynt-drime-backups-dashboard' );
		}

		if ( 'wpvivid' === $source_key ) {
			$policy = isset( $source['schedule_policy'] ) && is_array( $source['schedule_policy'] ) ? $source['schedule_policy'] : array();

			if ( ! empty( $policy['detected'] ) && ! empty( $policy['policy_window_seconds'] ) ) {
				return sprintf(
					/* translators: %s: human readable freshness window. */
					__( 'within %s (detected WPvivid schedule)', 'alynt-drime-backups-dashboard' ),
					$this->source_duration_label( $seconds )
				);
			}

			return sprintf(
				/* translators: %s: human readable freshness window. */
				__( 'within %s (dashboard fallback)', 'alynt-drime-backups-dashboard' ),
				$this->source_duration_label( $seconds )
			);
		}

		return sprintf(
			/* translators: %s: human readable freshness window. */
			__( 'within %s', 'alynt-drime-backups-dashboard' ),
			$this->source_duration_label( $seconds )
		);
	}

	/**
	 * Gets the dashboard freshness policy window for a source.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source summary.
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

			return max( 1296000, $reported );
		}

		return $reported;
	}

	/**
	 * Determines whether a source has a dashboard-local external/optional policy.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param string              $source_key Source key.
	 * @return bool
	 */
	private function backup_source_is_external_optional( array $site, $source_key ) {
		if ( ! isset( $this->source_policy ) || ! $this->source_policy instanceof Alynt_Drime_Backups_Dashboard_Source_Policy ) {
			return false;
		}

		return $this->source_policy->source_is_external_optional( $site, $source_key );
	}
}
