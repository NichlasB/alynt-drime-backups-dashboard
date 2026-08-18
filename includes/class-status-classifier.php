<?php
/**
 * Dashboard status classifier.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classifies redacted uploader status without performing HTTP transport.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Status_Classifier {
	const CATEGORY_PENDING         = 'pending';
	const CATEGORY_PAUSED          = 'paused';
	const CATEGORY_INCOMPATIBLE    = 'incompatible';
	const CATEGORY_NOT_REPORTING   = 'not_reporting';
	const CATEGORY_NEEDS_ATTENTION = 'needs_attention';
	const CATEGORY_NOT_CONFIGURED  = 'not_configured';
	const CATEGORY_WORKING         = 'working';

	const SUPPORTED_SCHEMA_VERSION    = 1;
	const DEFAULT_STALE_AFTER_SECONDS = 3600;
	const WPVIVID_POLICY_WINDOW       = 1296000; // 15 days.

	/**
	 * Classifies one site using its latest snapshot.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string,mixed>      $site Site row.
	 * @param array<string,mixed>|null $snapshot Latest snapshot row.
	 * @param int|null                 $now Unix timestamp.
	 * @return array<string,string>
	 */
	public function classify( array $site, $snapshot = null, $now = null ) {
		$now = null === $now ? time() : (int) $now;

		if ( ! empty( $site['paused_at'] ) ) {
			return $this->result( self::CATEGORY_PAUSED, __( 'Polling is paused for this site.', 'alynt-drime-backups-dashboard' ) );
		}

		$site_status = isset( $site['overall_status'] ) ? (string) $site['overall_status'] : ( isset( $site['status'] ) ? (string) $site['status'] : '' );

		if ( self::CATEGORY_PENDING === $site_status ) {
			return $this->result( self::CATEGORY_PENDING, __( 'Waiting for the client site to opt in and complete pairing.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( empty( $snapshot ) ) {
			return $this->result( self::CATEGORY_NOT_REPORTING, __( 'No status snapshot has been received yet.', 'alynt-drime-backups-dashboard' ) );
		}

		$payload = $this->payload_from_snapshot( $snapshot );

		if ( empty( $payload ) ) {
			return $this->result( self::CATEGORY_NOT_REPORTING, __( 'The latest status snapshot could not be decoded.', 'alynt-drime-backups-dashboard' ) );
		}

		$schema = isset( $payload['schema_version'] ) ? (int) $payload['schema_version'] : ( isset( $snapshot['schema_version'] ) ? (int) $snapshot['schema_version'] : 0 );

		if ( self::SUPPORTED_SCHEMA_VERSION !== $schema ) {
			return $this->result( self::CATEGORY_INCOMPATIBLE, __( 'The client status schema is not supported by this dashboard version.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( $this->is_stale( $site, $snapshot, $now ) ) {
			return $this->result( self::CATEGORY_NOT_REPORTING, __( 'The last status snapshot is stale.', 'alynt-drime-backups-dashboard' ) );
		}

		$attention_message = $this->attention_message( $payload );

		if ( '' !== $attention_message ) {
			return $this->result( self::CATEGORY_NEEDS_ATTENTION, $attention_message );
		}

		if ( $this->is_not_configured( $payload ) ) {
			return $this->result( self::CATEGORY_NOT_CONFIGURED, __( 'No supported backup source appears configured on the client site.', 'alynt-drime-backups-dashboard' ) );
		}

		return $this->result( self::CATEGORY_WORKING, __( 'The latest redacted status payload looks healthy.', 'alynt-drime-backups-dashboard' ) );
	}

	/**
	 * Builds a result.
	 *
	 * @param string $category Category slug.
	 * @param string $message Human-readable message.
	 * @return array<string,string>
	 */
	private function result( $category, $message ) {
		return array(
			'category' => $category,
			'label'    => $this->label( $category ),
			'message'  => $message,
		);
	}

	/**
	 * Gets a label for a category.
	 *
	 * @since 0.1.0
	 *
	 * @param string $category Category slug.
	 * @return string
	 */
	public function label( $category ) {
		$labels = array(
			self::CATEGORY_PENDING         => __( 'Pending', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_PAUSED          => __( 'Paused', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_INCOMPATIBLE    => __( 'Incompatible', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_NOT_REPORTING   => __( 'Not reporting', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_NEEDS_ATTENTION => __( 'Needs attention', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_NOT_CONFIGURED  => __( 'Not configured', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_WORKING         => __( 'Working', 'alynt-drime-backups-dashboard' ),
		);

		return isset( $labels[ $category ] ) ? $labels[ $category ] : $category;
	}

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
	 * @return bool
	 */
	private function attention_message( array $payload ) {
		if ( isset( $payload['failed_count'] ) && (int) $payload['failed_count'] > 0 ) {
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

		if ( $this->backup_source_needs_attention( $payload ) ) {
			return __( 'One or more backup sources report stale or missing upload evidence.', 'alynt-drime-backups-dashboard' );
		}

		return '';
	}

	/**
	 * Determines whether source-level freshness evidence needs attention.
	 *
	 * @param array<string,mixed> $payload Status payload.
	 * @return bool
	 */
	private function backup_source_needs_attention( array $payload ) {
		if ( empty( $payload['backup_sources'] ) || ! is_array( $payload['backup_sources'] ) ) {
			return false;
		}

		foreach ( $payload['backup_sources'] as $source_key => $source ) {
			if ( ! is_array( $source ) ) {
				continue;
			}

			if ( $this->source_needs_attention( sanitize_key( (string) $source_key ), $source ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines whether one source-level evidence summary needs attention.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source evidence.
	 * @return bool
	 */
	private function source_needs_attention( $source_key, array $source ) {
		$configured = ! empty( $source['configured'] );

		if ( ! $configured ) {
			return false;
		}

		if ( isset( $source['failed_count'] ) && (int) $source['failed_count'] > 0 ) {
			return true;
		}

		$freshness = isset( $source['freshness_status'] ) ? sanitize_key( (string) $source['freshness_status'] ) : '';

		if ( 'no_upload_evidence' === $freshness ) {
			return true;
		}

		if ( 'stale' === $freshness && $this->source_is_outside_dashboard_policy( $source_key, $source ) ) {
			return true;
		}

		if ( empty( $source['warnings'] ) || ! is_array( $source['warnings'] ) ) {
			return false;
		}

		foreach ( $source['warnings'] as $warning ) {
			if ( ! is_array( $warning ) ) {
				continue;
			}

			$code = isset( $warning['code'] ) ? sanitize_key( (string) $warning['code'] ) : '';

			if ( '' === $code || 'source_queue_not_empty' === $code ) {
				continue;
			}

			if ( 'source_latest_upload_stale' === $code && ! $this->source_is_outside_dashboard_policy( $source_key, $source ) ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Determines whether source age is outside the dashboard policy.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source evidence.
	 * @return bool
	 */
	private function source_is_outside_dashboard_policy( $source_key, array $source ) {
		if ( 'wpvivid' !== $source_key ) {
			return true;
		}

		if ( empty( $source['has_upload_evidence'] ) ) {
			return true;
		}

		$age = isset( $source['latest_upload_age_seconds'] ) ? max( 0, (int) $source['latest_upload_age_seconds'] ) : 0;

		if ( $age <= 0 ) {
			return true;
		}

		return $age > $this->source_policy_window_seconds( $source_key, $source );
	}

	/**
	 * Gets the dashboard policy window for source-level evidence.
	 *
	 * @param string              $source_key Source key.
	 * @param array<string,mixed> $source Source evidence.
	 * @return int
	 */
	private function source_policy_window_seconds( $source_key, array $source ) {
		$reported = isset( $source['freshness_window_seconds'] ) ? max( 0, (int) $source['freshness_window_seconds'] ) : 0;

		if ( 'wpvivid' === $source_key ) {
			$schedule_policy = isset( $source['schedule_policy'] ) && is_array( $source['schedule_policy'] ) ? $source['schedule_policy'] : array();
			$detected_window = ! empty( $schedule_policy['detected'] ) && isset( $schedule_policy['policy_window_seconds'] ) ? max( 0, (int) $schedule_policy['policy_window_seconds'] ) : 0;

			if ( $detected_window > 0 ) {
				return max( $detected_window, $reported );
			}

			return max( self::WPVIVID_POLICY_WINDOW, $reported );
		}

		return $reported;
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
