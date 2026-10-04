<?php
/**
 * Remote action repository helper trait.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds bounded, redacted remote action context payloads.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Context {


	/**
	 * Redacts context before local storage.
	 *
	 * @param array<string,mixed> $context Context.
	 * @return array<string,mixed>
	 */
	private function redacted_context( array $context ) {
		$clean = array();

		foreach ( $context as $key => $value ) {
			$key = sanitize_key( (string) $key );

			if ( '' === $key ) {
				continue;
			}

			if (
				preg_match(
					'/(secret|token|credential|password|authorization|cookie|nonce|signature|private|path|file|package|drime|url|sql)/',
					$key
				)
			) {
				$clean[ $key ] = '[redacted]';
				continue;
			}

			if ( is_bool( $value ) ) {
				$clean[ $key ] = $value;
			} elseif ( is_int( $value ) || is_float( $value ) ) {
				$clean[ $key ] = max( 0, (int) $value );
			} elseif ( is_scalar( $value ) ) {
				$clean[ $key ] = $this->bounded_text( (string) $value, 120 );
			}
		}

		return $clean;
	}

	/**
	 * Merges support-safe client action details into existing redacted context.
	 *
	 * @param int                 $action_id Action row ID.
	 * @param array<string,mixed> $client_action Client action.
	 * @return string
	 */
	private function merge_client_action_context_json( $action_id, array $client_action ) {
		$row = $this->row_by_id( $action_id );

		if ( ! is_array( $row ) ) {
			return '';
		}

		$context = $this->context_from_row( $row );

		if ( ! empty( $client_action['schedule_preview'] ) && is_array( $client_action['schedule_preview'] ) ) {
			$context['schedule_preview'] = $this->safe_schedule_preview_context( $client_action['schedule_preview'] );
		}

		if ( ! empty( $client_action['schedule_apply'] ) && is_array( $client_action['schedule_apply'] ) ) {
			$context['schedule_apply'] = $this->safe_schedule_apply_context( $client_action['schedule_apply'] );
		}

		if ( ! empty( $client_action['schedule_rollback_preview'] ) && is_array( $client_action['schedule_rollback_preview'] ) ) {
			$context['schedule_rollback_preview'] = $this->safe_schedule_rollback_preview_context( $client_action['schedule_rollback_preview'] );
		}

		if ( ! empty( $client_action['cleanup_preview'] ) && is_array( $client_action['cleanup_preview'] ) ) {
			$context['cleanup_preview'] = $this->safe_cleanup_preview_context( $client_action['cleanup_preview'] );
		}

		$encoded = wp_json_encode( $context, JSON_UNESCAPED_SLASHES );

		return false === $encoded ? '' : (string) $encoded;
	}

	/**
	 * Sanitizes client-reported action counts.
	 *
	 * @param mixed $counts Counts.
	 * @return array<string,int>
	 */
	private function client_action_counts( $counts ) {
		if ( ! is_array( $counts ) ) {
			return array();
		}

		$clean = array();

		foreach ( array( 'found', 'queued', 'already_known', 'upload_attempted', 'failed' ) as $key ) {
			$clean[ $key ] = isset( $counts[ $key ] ) ? max( 0, (int) $counts[ $key ] ) : 0;
		}

		return $clean;
	}

	/**
	 * Gets the best client-side timestamp available for an action report.
	 *
	 * @param array<string,mixed> $client_action Client action report.
	 * @return string
	 */
	private function client_action_updated_at( array $client_action ) {
		foreach ( array( 'updated_at', 'completed_at', 'requested_at' ) as $key ) {
			if ( empty( $client_action[ $key ] ) ) {
				continue;
			}

			$date = $this->date_or_default( (string) $client_action[ $key ], '' );

			if ( '' !== $date ) {
				return $date;
			}
		}

		return '';
	}
}
