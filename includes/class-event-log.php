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
	use Alynt_Drime_Backups_Dashboard_Event_Log_Settings;

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
	 * Same-request settings cache.
	 *
	 * @var array<string,mixed>|null
	 */
	private $settings_cache = null;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Event_Log_Redactor|null $redactor Event redactor.
	 */
	public function __construct( $redactor = null ) {
		$this->redactor = $redactor instanceof Alynt_Drime_Backups_Dashboard_Event_Log_Redactor ? $redactor : new Alynt_Drime_Backups_Dashboard_Event_Log_Redactor();
	}

	/**
	 * Records a structured event when diagnostics logging is enabled.
	 *
	 * @since 0.1.0
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

		$cleared = (bool) update_option( self::OPTION_EVENTS, array(), false );

		if ( $cleared ) {
			$this->events_cache = array();
		}

		return $cleared;
	}

	/**
	 * Deletes diagnostics options.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function uninstall() {
		if ( function_exists( 'delete_option' ) ) {
			delete_option( self::OPTION_SETTINGS );
			delete_option( self::OPTION_EVENTS );
		}
	}
}
