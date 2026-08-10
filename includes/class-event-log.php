<?php
/**
 * Structured diagnostics event log.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores bounded, redacted diagnostics events when explicitly enabled.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Event_Log {
	const OPTION_SETTINGS = 'alynt_drime_backups_dashboard_diagnostics_settings';
	const OPTION_EVENTS   = 'alynt_drime_backups_dashboard_diagnostics_events';

	/**
	 * Severity order.
	 *
	 * @var array<string,int>
	 */
	private $severity_order = array(
		'debug'    => 10,
		'info'     => 20,
		'warning'  => 30,
		'error'    => 40,
		'critical' => 50,
	);

	/**
	 * Sensitive context keys that must be removed or masked.
	 *
	 * @var array<int,string>
	 */
	private $sensitive_key_patterns = array(
		'password',
		'passwd',
		'secret',
		'token',
		'api_key',
		'apikey',
		'authorization',
		'cookie',
		'nonce',
		'salt',
		'ciphertext',
		'payload',
		'body',
		'raw',
		'path',
		'sql',
		'drime',
	);

	/**
	 * Gets sanitized diagnostics settings.
	 *
	 * @return array<string,mixed>
	 */
	public function settings() {
		$stored = function_exists( 'get_option' ) ? get_option( self::OPTION_SETTINGS, array() ) : array();

		return $this->sanitize_settings( is_array( $stored ) ? $stored : array() );
	}

	/**
	 * Gets supported severity levels.
	 *
	 * @return array<int,string>
	 */
	public function severity_levels() {
		return array_keys( $this->severity_order );
	}

	/**
	 * Saves diagnostics settings with autoload disabled.
	 *
	 * @param array<string,mixed> $settings Raw settings.
	 * @return bool
	 */
	public function update_settings( array $settings ) {
		if ( ! function_exists( 'update_option' ) ) {
			return false;
		}

		return (bool) update_option( self::OPTION_SETTINGS, $this->sanitize_settings( $settings ), false );
	}

	/**
	 * Returns whether structured diagnostics logging is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$settings = $this->settings();

		return ! empty( $settings['enabled'] );
	}

	/**
	 * Records a structured event when diagnostics logging is enabled.
	 *
	 * @param string              $level Severity level.
	 * @param string              $category Event category.
	 * @param string              $code Stable event code.
	 * @param string              $message Summary message.
	 * @param array<string,mixed> $context Context.
	 * @return bool
	 */
	public function log( $level, $category, $code, $message, array $context = array() ) {
		$settings = $this->settings();
		$level    = $this->normalize_level( $level );

		if ( empty( $settings['enabled'] ) || ! $this->meets_threshold( $level, (string) $settings['minimum_level'] ) ) {
			return false;
		}

		return $this->store_event(
			array(
				'timestamp' => gmdate( 'c' ),
				'level'     => $level,
				'category'  => sanitize_key( $category ),
				'code'      => sanitize_key( $code ),
				'message'   => $this->truncate( sanitize_text_field( $message ), 240 ),
				'context'   => $this->redact_context( $context ),
			)
		);
	}

	/**
	 * Gets recent retained events.
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
	 * @return bool
	 */
	public function clear() {
		if ( ! function_exists( 'update_option' ) ) {
			return false;
		}

		return (bool) update_option( self::OPTION_EVENTS, array(), false );
	}

	/**
	 * Deletes diagnostics options.
	 *
	 * @return void
	 */
	public static function uninstall() {
		if ( function_exists( 'delete_option' ) ) {
			delete_option( self::OPTION_SETTINGS );
			delete_option( self::OPTION_EVENTS );
		}
	}

	/**
	 * Sanitizes settings.
	 *
	 * @param array<string,mixed> $settings Raw settings.
	 * @return array<string,mixed>
	 */
	private function sanitize_settings( array $settings ) {
		$minimum_level = isset( $settings['minimum_level'] ) ? sanitize_key( $settings['minimum_level'] ) : 'warning';

		if ( ! isset( $this->severity_order[ $minimum_level ] ) ) {
			$minimum_level = 'warning';
		}

		return array(
			'enabled'        => ! empty( $settings['enabled'] ),
			'minimum_level'  => $minimum_level,
			'retention_days' => max( 1, min( 90, absint( isset( $settings['retention_days'] ) ? $settings['retention_days'] : 14 ) ) ),
			'max_events'     => max( 10, min( 1000, absint( isset( $settings['max_events'] ) ? $settings['max_events'] : 200 ) ) ),
		);
	}

	/**
	 * Normalizes severity.
	 *
	 * @param string $level Severity.
	 * @return string
	 */
	private function normalize_level( $level ) {
		$level = sanitize_key( $level );

		return isset( $this->severity_order[ $level ] ) ? $level : 'info';
	}

	/**
	 * Determines whether a level meets the configured threshold.
	 *
	 * @param string $level Level.
	 * @param string $minimum Minimum level.
	 * @return bool
	 */
	private function meets_threshold( $level, $minimum ) {
		return $this->severity_order[ $level ] >= $this->severity_order[ $this->normalize_level( $minimum ) ];
	}

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

	/**
	 * Redacts sensitive context before storage/export.
	 *
	 * @param array<string,mixed> $context Context.
	 * @return array<string,mixed>
	 */
	private function redact_context( array $context ) {
		$redacted = array();

		foreach ( $context as $key => $value ) {
			$key      = sanitize_key( (string) $key );
			$redacts  = $this->key_is_sensitive( $key );
			$safe_key = '' === $key ? 'context' : $key;

			$redacted[ $safe_key ] = $redacts ? '[redacted]' : $this->redact_value( $value );
		}

		return $redacted;
	}

	/**
	 * Redacts or normalizes a value.
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	private function redact_value( $value ) {
		if ( is_array( $value ) ) {
			return $this->redact_context( $value );
		}

		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
			return $value;
		}

		return $this->truncate( sanitize_text_field( (string) $value ), 240 );
	}

	/**
	 * Determines whether a context key is sensitive.
	 *
	 * @param string $key Key.
	 * @return bool
	 */
	private function key_is_sensitive( $key ) {
		foreach ( $this->sensitive_key_patterns as $pattern ) {
			if ( false !== strpos( $key, $pattern ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Truncates scalar text.
	 *
	 * @param string $value Value.
	 * @param int    $length Length.
	 * @return string
	 */
	private function truncate( $value, $length ) {
		$value = (string) $value;

		if ( strlen( $value ) <= $length ) {
			return $value;
		}

		return substr( $value, 0, max( 0, $length - 3 ) ) . '...';
	}
}
