<?php
/**
 * Admin page helper split.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Provides remote schedule summary and label helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Label_Helpers {


	/**
	 * Gets sanitized remote-action capability summary from a payload.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,mixed>
	 */
	private function remote_actions_from_payload( array $payload ) {
		return isset( $payload['remote_actions'] ) && is_array( $payload['remote_actions'] ) ? $payload['remote_actions'] : array();
	}

	/**
	 * Gets sanitized schedule-management capability from a payload.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,mixed>
	 */
	private function schedule_management_summary( array $payload ) {
		$remote_actions = $this->remote_actions_from_payload( $payload );

		return isset( $remote_actions['schedule_management'] ) && is_array( $remote_actions['schedule_management'] ) ? $remote_actions['schedule_management'] : array();
	}

	/**
	 * Gets a schedule label.
	 *
	 * @param array<string,mixed> $schedule Schedule summary.
	 * @return string
	 */
	private function schedule_label( array $schedule ) {
		if ( ! empty( $schedule['label'] ) ) {
			return (string) $schedule['label'];
		}

		if ( ! empty( $schedule['schedule_id'] ) ) {
			return (string) $schedule['schedule_id'];
		}

		return __( 'Reported schedule', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a human-readable schedule owner label.
	 *
	 * @param string $owner Owner.
	 * @return string
	 */
	private function schedule_owner_label( $owner ) {
		$labels = array(
			'alynt_uploader' => __( 'Alynt uploader', 'alynt-drime-backups-dashboard' ),
			'wpvivid'        => __( 'WPvivid', 'alynt-drime-backups-dashboard' ),
			'wordpress'      => __( 'WordPress', 'alynt-drime-backups-dashboard' ),
			'unknown'        => __( 'Unknown', 'alynt-drime-backups-dashboard' ),
			''               => __( 'Unknown', 'alynt-drime-backups-dashboard' ),
		);
		$owner  = sanitize_key( $owner );

		return isset( $labels[ $owner ] ) ? $labels[ $owner ] : __( 'Unknown', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a human-readable cadence label.
	 *
	 * @param string $cadence Cadence key.
	 * @return string
	 */
	private function schedule_cadence_label( $cadence ) {
		$labels  = array(
			'every_15_minutes' => __( 'every 15 minutes', 'alynt-drime-backups-dashboard' ),
			'every_30_minutes' => __( 'every 30 minutes', 'alynt-drime-backups-dashboard' ),
			'hourly'           => __( 'hourly', 'alynt-drime-backups-dashboard' ),
			'daily'            => __( 'daily', 'alynt-drime-backups-dashboard' ),
			'weekly'           => __( 'weekly', 'alynt-drime-backups-dashboard' ),
			'unknown'          => __( 'unknown', 'alynt-drime-backups-dashboard' ),
			''                 => __( 'not reported', 'alynt-drime-backups-dashboard' ),
		);
		$cadence = sanitize_key( $cadence );

		return isset( $labels[ $cadence ] ) ? $labels[ $cadence ] : str_replace( '_', ' ', $cadence );
	}

	/**
	 * Gets a comma-separated cadence label list.
	 *
	 * @param mixed $cadences Cadence list.
	 * @return string
	 */
	private function schedule_cadences_label( $cadences ) {
		if ( ! is_array( $cadences ) || empty( $cadences ) ) {
			return __( 'Not reported', 'alynt-drime-backups-dashboard' );
		}

		$labels = array();

		foreach ( $cadences as $cadence ) {
			$labels[] = $this->schedule_cadence_label( (string) $cadence );
		}

		return implode( ', ', array_unique( $labels ) );
	}

	/**
	 * Formats a schedule interval.
	 *
	 * @param int $seconds Interval seconds.
	 * @return string
	 */
	private function schedule_interval_label( $seconds ) {
		$seconds = max( 0, (int) $seconds );

		if ( $seconds <= 0 ) {
			return __( 'Not reported', 'alynt-drime-backups-dashboard' );
		}

		if ( method_exists( $this, 'source_duration_label' ) ) {
			return $this->source_duration_label( $seconds );
		}

		return sprintf(
			/* translators: %d: number of seconds. */
			_n( '%d second', '%d seconds', $seconds, 'alynt-drime-backups-dashboard' ),
			$seconds
		);
	}
}
