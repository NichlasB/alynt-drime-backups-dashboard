<?php
/**
 * Diagnostics support summary section helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.59
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds support-safe diagnostics summary sections.
 *
 * @since 0.1.59
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Sections {

	/**
	 * Builds support-safe diagnostic summary labels.
	 *
	 * @param array<string,string> $summaries Diagnostic summary codes.
	 * @return array<string,array<string,string>>
	 */
	private function support_diagnostic_summaries( array $summaries ) {
		$attention_code = isset( $summaries['attention_history'] ) ? sanitize_key( $summaries['attention_history'] ) : 'unknown';

		return array(
			'attention_history' => array(
				'code'  => $attention_code,
				'label' => $this->support_attention_history_summary_label( $attention_code ),
			),
		);
	}

	/**
	 * Gets a support-safe English label for an attention-history summary code.
	 *
	 * Support-copy JSON is intentionally stable English diagnostic text rather
	 * than translated UI copy.
	 *
	 * @param string $code Summary code.
	 * @return string
	 */
	private function support_attention_history_summary_label( $code ) {
		switch ( (string) $code ) {
			case 'no_retained_history':
				return 'No retained snapshot history is available for aggregate attention/recovery interpretation yet.';
			case 'quiet_retained_history':
				return 'Retained snapshot history shows no recent transitions into attention states.';
			case 'recent_recoveries_seen':
				return 'Retained snapshot history shows recent recovery from attention states.';
			case 'repeated_attention_seen':
				return 'Retained snapshot history shows repeated transitions into attention states.';
			case 'attention_transitions_seen':
				return 'Retained snapshot history shows recent transitions into attention states.';
			default:
				return 'Attention/recovery history summary is unavailable.';
		}
	}

	/**
	 * Reduces remote-action aggregate data to support-safe fields.
	 *
	 * @param array<string,mixed> $remote_actions Remote action aggregate summary.
	 * @return array<string,mixed>
	 */
	private function support_remote_action_summary( array $remote_actions ) {
		return array(
			'total'                     => isset( $remote_actions['total'] ) ? max( 0, (int) $remote_actions['total'] ) : 0,
			'client_reconciled'         => isset( $remote_actions['client_reconciled'] ) ? max( 0, (int) $remote_actions['client_reconciled'] ) : 0,
			'stale'                     => isset( $remote_actions['stale'] ) ? max( 0, (int) $remote_actions['stale'] ) : 0,
			'awaiting_confirmation'     => isset( $remote_actions['awaiting_confirmation'] ) ? max( 0, (int) $remote_actions['awaiting_confirmation'] ) : 0,
			'schedule_apply'            => isset( $remote_actions['schedule_apply'] ) ? max( 0, (int) $remote_actions['schedule_apply'] ) : 0,
			'schedule_rollback_preview' => isset( $remote_actions['schedule_rollback_preview'] ) ? max( 0, (int) $remote_actions['schedule_rollback_preview'] ) : 0,
			'cleanup_preview'           => isset( $remote_actions['cleanup_preview'] ) ? max( 0, (int) $remote_actions['cleanup_preview'] ) : 0,
			'rollback_metadata'         => isset( $remote_actions['rollback_metadata'] ) ? max( 0, (int) $remote_actions['rollback_metadata'] ) : 0,
			'latest_updated_at'         => isset( $remote_actions['latest_updated_at'] ) ? sanitize_text_field( (string) $remote_actions['latest_updated_at'] ) : '',
		);
	}

	/**
	 * Builds support-safe logging summary without event context.
	 *
	 * @return array<string,mixed>
	 */
	private function support_logging_summary() {
		return $this->support_logging_summary_from_diagnostics(
			array(
				'settings' => $this->event_log->settings(),
				'summary'  => $this->event_log->summary(),
				'audit'    => array(
					'summary' => $this->event_log->audit_summary(),
				),
			)
		);
	}

	/**
	 * Builds support-safe logging summary from collected diagnostics.
	 *
	 * @param array<string,mixed> $logging Logging diagnostics.
	 * @return array<string,mixed>
	 */
	private function support_logging_summary_from_diagnostics( array $logging ) {
		$settings      = isset( $logging['settings'] ) && is_array( $logging['settings'] ) ? $logging['settings'] : array();
		$summary       = isset( $logging['summary'] ) && is_array( $logging['summary'] ) ? $logging['summary'] : array();
		$audit         = isset( $logging['audit'] ) && is_array( $logging['audit'] ) ? $logging['audit'] : array();
		$audit_summary = isset( $audit['summary'] ) && is_array( $audit['summary'] ) ? $audit['summary'] : array();

		return array(
			'enabled'        => ! empty( $settings['enabled'] ),
			'minimum_level'  => isset( $settings['minimum_level'] ) ? (string) $settings['minimum_level'] : 'warning',
			'retention_days' => isset( $settings['retention_days'] ) ? (int) $settings['retention_days'] : 14,
			'event_count'    => isset( $summary['total'] ) ? (int) $summary['total'] : 0,
			'last_event_at'  => isset( $summary['last_event_at'] ) ? (string) $summary['last_event_at'] : '',
			'audit_history'  => array(
				'event_count'    => isset( $audit_summary['total'] ) ? (int) $audit_summary['total'] : 0,
				'last_action_at' => isset( $audit_summary['last_action_at'] ) ? (string) $audit_summary['last_action_at'] : '',
				'retention_days' => Alynt_Drime_Backups_Dashboard_Event_Log::AUDIT_RETENTION_DAYS,
				'max_events'     => Alynt_Drime_Backups_Dashboard_Event_Log::AUDIT_MAX_EVENTS,
			),
		);
	}

	/**
	 * Reduces recent outcomes to support-safe fields.
	 *
	 * @param array<int,array<string,mixed>> $recent Recent outcomes.
	 * @return array<int,array<string,mixed>>
	 */
	private function support_recent_outcomes( array $recent ) {
		$safe = array();

		foreach ( $recent as $row ) {
			$safe[] = array(
				'site_id'              => isset( $row['site_id'] ) ? (int) $row['site_id'] : 0,
				'enrollment_status'    => isset( $row['enrollment_status'] ) ? sanitize_key( $row['enrollment_status'] ) : '',
				'overall_status'       => isset( $row['overall_status'] ) ? sanitize_key( $row['overall_status'] ) : '',
				'last_poll_attempt_at' => isset( $row['last_poll_attempt_at'] ) ? (string) $row['last_poll_attempt_at'] : '',
				'next_poll_at'         => isset( $row['next_poll_at'] ) ? (string) $row['next_poll_at'] : '',
				'consecutive_failures' => isset( $row['consecutive_failures'] ) ? max( 0, (int) $row['consecutive_failures'] ) : 0,
				'last_error_code'      => isset( $row['last_error_code'] ) ? sanitize_key( $row['last_error_code'] ) : '',
			);
		}

		return $safe;
	}
}
