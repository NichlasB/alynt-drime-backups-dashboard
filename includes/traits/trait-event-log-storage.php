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

		return (bool) update_option( self::OPTION_EVENTS, $events, false );
	}

	/**
	 * Gets stored events.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function stored_events() {
		$events = function_exists( 'get_option' ) ? get_option( self::OPTION_EVENTS, array() ) : array();

		return is_array( $events ) ? array_values( array_filter( $events, 'is_array' ) ) : array();
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
