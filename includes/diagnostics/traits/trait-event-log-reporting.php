<?php
/**
 * Structured diagnostics event log reporting helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides diagnostics and audit event reporting helpers.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Event_Log_Reporting {
	/**
	 * Gets recent retained audit events.
	 *
	 * @since 0.1.0
	 *
	 * @param int $limit Maximum events.
	 * @return array<int,array<string,mixed>>
	 */
	public function recent_audit_events( $limit = 50 ) {
		$events = $this->stored_audit_events();
		$events = $this->filter_retained_events( $events, self::AUDIT_RETENTION_DAYS );

		return array_slice( $events, 0, max( 1, min( self::AUDIT_MAX_EVENTS, (int) $limit ) ) );
	}

	/**
	 * Gets audit event counts and last-action metadata.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string,mixed>
	 */
	public function audit_summary() {
		$events  = $this->recent_audit_events( self::AUDIT_MAX_EVENTS );
		$summary = array(
			'total'          => count( $events ),
			'last_action_at' => '',
			'actions'        => array(),
			'outcomes'       => array(),
		);

		foreach ( $events as $event ) {
			$action  = isset( $event['action'] ) ? (string) $event['action'] : 'unknown';
			$outcome = isset( $event['outcome'] ) ? (string) $event['outcome'] : 'unknown';

			if ( '' === $summary['last_action_at'] && ! empty( $event['timestamp'] ) ) {
				$summary['last_action_at'] = (string) $event['timestamp'];
			}

			if ( ! isset( $summary['actions'][ $action ] ) ) {
				$summary['actions'][ $action ] = 0;
			}

			if ( ! isset( $summary['outcomes'][ $outcome ] ) ) {
				$summary['outcomes'][ $outcome ] = 0;
			}

			++$summary['actions'][ $action ];
			++$summary['outcomes'][ $outcome ];
		}

		ksort( $summary['actions'] );
		ksort( $summary['outcomes'] );

		return $summary;
	}

	/**
	 * Gets recent retained events.
	 *
	 * @since 0.1.0
	 *
	 * @param int $limit Maximum events.
	 * @return array<int,array<string,mixed>>
	 */
	public function recent_events( $limit = 50 ) {
		$settings = $this->settings();
		$events   = $this->stored_events();
		$events   = $this->filter_retained_events( $events, (int) $settings['retention_days'] );

		return array_slice( $events, 0, max( 1, min( 200, (int) $limit ) ) );
	}

	/**
	 * Gets event counts and last-event metadata.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string,mixed>
	 */
	public function summary() {
		$events  = $this->recent_events( 1000 );
		$summary = array(
			'total'         => count( $events ),
			'last_event_at' => '',
			'levels'        => array(),
			'categories'    => array(),
		);

		foreach ( $events as $event ) {
			$level    = isset( $event['level'] ) ? (string) $event['level'] : 'unknown';
			$category = isset( $event['category'] ) ? (string) $event['category'] : 'unknown';

			if ( '' === $summary['last_event_at'] && ! empty( $event['timestamp'] ) ) {
				$summary['last_event_at'] = (string) $event['timestamp'];
			}

			if ( ! isset( $summary['levels'][ $level ] ) ) {
				$summary['levels'][ $level ] = 0;
			}

			if ( ! isset( $summary['categories'][ $category ] ) ) {
				$summary['categories'][ $category ] = 0;
			}

			++$summary['levels'][ $level ];
			++$summary['categories'][ $category ];
		}

		ksort( $summary['levels'] );
		ksort( $summary['categories'] );

		return $summary;
	}

	/**
	 * Clears all stored diagnostics events.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function clear() {
		if ( ! function_exists( 'update_option' ) ) {
			return false;
		}

		if ( array() === $this->stored_events() ) {
			$this->events_cache = array();
			return true;
		}

		$cleared = (bool) update_option( self::OPTION_EVENTS, array(), false );

		if ( $cleared ) {
			$this->events_cache = array();
		}

		return $cleared;
	}

	/**
	 * Deletes diagnostics and audit options.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function uninstall() {
		if ( function_exists( 'delete_option' ) ) {
			delete_option( self::OPTION_SETTINGS );
			delete_option( self::OPTION_EVENTS );
			delete_option( self::OPTION_AUDIT );
		}
	}
}
