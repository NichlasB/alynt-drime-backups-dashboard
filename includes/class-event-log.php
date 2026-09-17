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
 * Stores bounded, redacted diagnostics events and local audit history.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Event_Log {
	use Alynt_Drime_Backups_Dashboard_Event_Log_Storage;
	use Alynt_Drime_Backups_Dashboard_Event_Log_Settings;
	use Alynt_Drime_Backups_Dashboard_Event_Log_Reporting;

	const OPTION_SETTINGS = 'alynt_drime_backups_dashboard_diagnostics_settings';
	const OPTION_EVENTS   = 'alynt_drime_backups_dashboard_diagnostics_events';
	const OPTION_AUDIT    = 'alynt_drime_backups_dashboard_audit_events';

	const AUDIT_RETENTION_DAYS = 90;
	const AUDIT_MAX_EVENTS     = 500;

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
	 * Records an always-on redacted operator audit event.
	 *
	 * @since 0.1.0
	 *
	 * @param string              $action Stable dashboard action identifier.
	 * @param string              $outcome Action outcome.
	 * @param array<string,mixed> $context Redacted context.
	 * @return bool
	 */
	public function audit_action( $action, $outcome, array $context = array() ) {
		$action  = sanitize_key( $action );
		$outcome = sanitize_key( $outcome );

		if ( '' === $action ) {
			$action = 'dashboard_action';
		}

		if ( '' === $outcome ) {
			$outcome = 'recorded';
		}

		return $this->store_audit_event(
			array(
				'timestamp' => gmdate( 'c' ),
				'actor_id'  => function_exists( 'get_current_user_id' ) ? max( 0, (int) get_current_user_id() ) : 0,
				'action'    => $this->redactor->truncate( $action, 80 ),
				'outcome'   => $this->redactor->truncate( $outcome, 40 ),
				'context'   => $this->redactor->redact_context( $context ),
			)
		);
	}
}
