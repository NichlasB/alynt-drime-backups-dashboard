<?php
/**
 * Dashboard status classifier helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides generic dashboard status classification helpers.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Status_Classifier_Helpers {
	/**
	 * Extracts decoded payload from a snapshot.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return array<string,mixed>
	 */
	private function payload_from_snapshot( array $snapshot ) {
		if ( isset( $snapshot['decoded_payload'] ) && is_array( $snapshot['decoded_payload'] ) ) {
			return $snapshot['decoded_payload'];
		}

		$payload_json = isset( $snapshot['payload_json'] ) ? $snapshot['payload_json'] : ( isset( $snapshot['status_payload'] ) ? $snapshot['status_payload'] : '' );

		if ( '' !== (string) $payload_json ) {
			$decoded = json_decode( (string) $payload_json, true );

			if ( is_array( $decoded ) ) {
				return $decoded;
			}
		}

		return array();
	}

	/**
	 * Determines whether the latest evidence is stale.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @param int                 $now Unix timestamp.
	 * @return bool
	 */
	private function is_stale( array $site, array $snapshot, $now ) {
		$candidates = array(
			isset( $site['last_seen_at'] ) ? $site['last_seen_at'] : '',
			isset( $snapshot['observed_at'] ) ? $snapshot['observed_at'] : ( isset( $snapshot['captured_at'] ) ? $snapshot['captured_at'] : '' ),
		);

		foreach ( $candidates as $candidate ) {
			$timestamp = $this->timestamp( $candidate );

			if ( $timestamp > 0 ) {
				return ( $now - $timestamp ) > self::DEFAULT_STALE_AFTER_SECONDS;
			}
		}

		return true;
	}

	/**
	 * Determines whether the payload indicates attention is needed.
	 *
	 * @param array<string,mixed> $payload Status payload.
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function attention_message( array $payload, array $site ) {
		if ( $this->payload_failed_uploads_need_attention( $payload ) ) {
			return __( 'The client reports failed backup uploads.', 'alynt-drime-backups-dashboard' );
		}

		if ( isset( $payload['warning_count'] ) && (int) $payload['warning_count'] > 0 ) {
			return __( 'The client reports uploader warnings.', 'alynt-drime-backups-dashboard' );
		}

		if ( ! empty( $payload['warnings'] ) && is_array( $payload['warnings'] ) ) {
			return __( 'The client reports uploader warnings.', 'alynt-drime-backups-dashboard' );
		}

		if ( isset( $payload['cron_status'] ) && in_array( $payload['cron_status'], array( 'error', 'missed', 'stale' ), true ) ) {
			return __( 'The client reports cron health that needs review.', 'alynt-drime-backups-dashboard' );
		}

		if ( $this->backup_source_needs_attention( $payload, $site ) ) {
			return __( 'One or more backup sources report stale or missing upload evidence.', 'alynt-drime-backups-dashboard' );
		}

		return '';
	}

	/**
	 * Determines whether top-level failed upload evidence represents a current attention condition.
	 *
	 * The uploader's failed counters are registry evidence and can include old failures that later
	 * recovered or were superseded by newer successful uploads. Treat failed counters as an alarm
	 * only when a queue is still present; otherwise source freshness and warning evidence decide
	 * whether the current state needs attention.
	 *
	 * @param array<string,mixed> $payload Status payload.
	 * @return bool
	 */
	private function payload_failed_uploads_need_attention( array $payload ) {
		$failed = isset( $payload['failed_count'] ) ? max( 0, (int) $payload['failed_count'] ) : 0;

		if ( 0 === $failed ) {
			return false;
		}

		$queued = isset( $payload['queue_count'] ) ? max( 0, (int) $payload['queue_count'] ) : 0;

		return $queued > 0;
	}

	/**
	 * Determines whether the payload proves no supported source is configured.
	 *
	 * @param array<string,mixed> $payload Status payload.
	 * @return bool
	 */
	private function is_not_configured( array $payload ) {
		if ( ! empty( $payload['backup_sources'] ) && is_array( $payload['backup_sources'] ) ) {
			$reported   = 0;
			$configured = 0;

			foreach ( $payload['backup_sources'] as $source ) {
				if ( ! is_array( $source ) ) {
					continue;
				}

				++$reported;

				if ( ! empty( $source['configured'] ) || ! empty( $source['has_upload_evidence'] ) ) {
					++$configured;
				}
			}

			if ( $reported > 0 ) {
				return 0 === $configured;
			}
		}

		$has_server_outbox = ! empty( $payload['server_outbox_configured'] );
		$has_wpvivid       = ! empty( $payload['wpvivid_override_configured'] ) || ! empty( $payload['old_wpvivid_uploader_active'] );

		return ! $has_server_outbox && ! $has_wpvivid;
	}

	/**
	 * Parses a timestamp.
	 *
	 * @param mixed $value Timestamp-like value.
	 * @return int
	 */
	private function timestamp( $value ) {
		if ( is_numeric( $value ) ) {
			return (int) $value;
		}

		if ( is_string( $value ) && '' !== $value ) {
			$timestamp = strtotime( $value );

			return false === $timestamp ? 0 : (int) $timestamp;
		}

		return 0;
	}
}
