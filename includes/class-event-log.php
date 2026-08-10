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
	use Alynt_Drime_Backups_Dashboard_Event_Log_Storage;

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
	 * Event redactor.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Event_Log_Redactor
	 */
	private $redactor;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Event_Log_Redactor|null $redactor Event redactor.
	 */
	public function __construct( $redactor = null ) {
		$this->redactor = $redactor instanceof Alynt_Drime_Backups_Dashboard_Event_Log_Redactor ? $redactor : new Alynt_Drime_Backups_Dashboard_Event_Log_Redactor();
	}

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

		$sanitized = $this->sanitize_settings( $settings );

		if ( $sanitized === $this->settings() ) {
			return true;
		}

		return (bool) update_option( self::OPTION_SETTINGS, $sanitized, false );
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
				'message'   => $this->redactor->truncate( sanitize_text_field( $message ), 240 ),
				'context'   => $this->redactor->redact_context( $context ),
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
}
