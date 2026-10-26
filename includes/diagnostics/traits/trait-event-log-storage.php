<?php
/**
 * Structured diagnostics event storage helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles bounded event persistence and retention filtering.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Event_Log_Storage {
	/**
	 * Same-request stored event cache.
	 *
	 * @var array<int,array<string,mixed>>|null
	 */
	private $events_cache = null;

	/**
	 * Same-request audit event cache.
	 *
	 * @var array<int,array<string,mixed>>|null
	 */
	private $audit_events_cache = null;

	/**
	 * Stores an event in the bounded ring buffer.
	 *
	 * @param array<string,mixed> $event Event.
	 * @return bool
	 */
	private function store_event( array $event ) {
		if ( ! function_exists( 'update_option' ) ) {
			return false;
		}

		$settings = $this->settings();
		$events   = $this->filter_retained_events( $this->stored_events(), (int) $settings['retention_days'] );

		array_unshift( $events, $event );
		$events = array_slice( $events, 0, (int) $settings['max_events'] );

		$stored = (bool) update_option( self::OPTION_EVENTS, $events, false );

		if ( $stored ) {
			$this->events_cache = $events;
		}

		return $stored;
	}

	/**
	 * Stores an audit event in a separate bounded ring buffer.
	 *
	 * @param array<string,mixed> $event Event.
	 * @return bool
	 */
	private function store_audit_event( array $event ) {
		if ( ! function_exists( 'update_option' ) ) {
			return false;
		}

		$events = $this->filter_retained_events( $this->stored_audit_events(), Alynt_Drime_Backups_Dashboard_Event_Log::AUDIT_RETENTION_DAYS );

		array_unshift( $events, $event );
		$events = array_slice( $events, 0, Alynt_Drime_Backups_Dashboard_Event_Log::AUDIT_MAX_EVENTS );

		$stored = (bool) update_option( Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_AUDIT, $events, false );

		if ( $stored ) {
			$this->audit_events_cache = $events;
		}

		return $stored;
	}

	/**
	 * Gets stored events.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function stored_events() {
		if ( null !== $this->events_cache ) {
			return $this->events_cache;
		}

		$events = function_exists( 'get_option' ) ? get_option( self::OPTION_EVENTS, array() ) : array();

		$this->events_cache = is_array( $events ) ? array_values( array_filter( $events, 'is_array' ) ) : array();

		return $this->events_cache;
	}

	/**
	 * Gets stored audit events.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function stored_audit_events() {
		if ( null !== $this->audit_events_cache ) {
			return $this->audit_events_cache;
		}

		$events = function_exists( 'get_option' ) ? get_option( Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_AUDIT, array() ) : array();

		$this->audit_events_cache = is_array( $events ) ? array_values( array_filter( $events, 'is_array' ) ) : array();

		return $this->audit_events_cache;
	}

	/**
	 * Filters events beyond retention.
	 *
	 * @param array<int,array<string,mixed>> $events Events.
	 * @param int                            $retention_days Retention days.
	 * @return array<int,array<string,mixed>>
	 */
	private function filter_retained_events( array $events, $retention_days ) {
		$cutoff = time() - ( max( 1, (int) $retention_days ) * 86400 );

		return array_values(
			array_filter(
				$events,
				function ( $event ) use ( $cutoff ) {
					$timestamp = ! empty( $event['timestamp'] ) ? strtotime( (string) $event['timestamp'] ) : 0;

					return $timestamp <= 0 || $timestamp >= $cutoff;
				}
			)
		);
	}
}
