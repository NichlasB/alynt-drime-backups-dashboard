<?php
/**
 * Client status payload validator.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates and allowlists uploader status schema v1 payloads.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Status_Payload_Validator {
	const SUPPORTED_SCHEMA_VERSION  = 1;
	const MAX_PLUGIN_VERSION_LENGTH = 64;
	const MAX_SOURCE_LABEL_LENGTH   = 80;

	/**
	 * Fields that must never be ingested by the dashboard.
	 *
	 * @var array<int,string>
	 */
	private $forbidden_fields = array(
		'server_outbox_path',
		'backup_path_override',
		'server_relative_path',
		'wpvivid_relative_path',
		'destination_relative_path',
		'path',
		'manifest_path',
		'checksum_path',
		'remote_index_path',
		'remote_catalog_path',
		'package_id',
		'backup_set_id',
		'file_entry_id',
		'workspace_id',
		'remote_name',
		'api_token',
		'authorization',
		'cookie',
		'nonce',
		'password',
		'presigned',
		'secret',
		'signed_url',
	);

	/**
	 * Validates and sanitizes a status payload.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string,mixed> $payload Raw decoded payload.
	 * @param string              $expected_site_uuid Expected site UUID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function validate( array $payload, $expected_site_uuid ) {
		if ( $this->contains_forbidden_field( $payload ) ) {
			return new WP_Error( 'payload_invalid', __( 'The client status payload contains a forbidden field.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( self::SUPPORTED_SCHEMA_VERSION !== absint( isset( $payload['schema_version'] ) ? $payload['schema_version'] : 0 ) ) {
			return new WP_Error( 'schema_unsupported', __( 'The client status schema is not supported.', 'alynt-drime-backups-dashboard' ) );
		}

		$site_uuid = $this->sanitize_uuid( isset( $payload['site_uuid'] ) ? (string) $payload['site_uuid'] : '' );

		if ( '' === $site_uuid || ! hash_equals( $this->sanitize_uuid( $expected_site_uuid ), $site_uuid ) ) {
			return new WP_Error( 'site_uuid_mismatch', __( 'The client status payload does not match the enrolled site UUID.', 'alynt-drime-backups-dashboard' ) );
		}

		$validated = array(
			'schema_version'              => self::SUPPORTED_SCHEMA_VERSION,
			'site_uuid'                   => $site_uuid,
			'plugin_version'              => $this->bounded_text( isset( $payload['plugin_version'] ) ? (string) $payload['plugin_version'] : '', self::MAX_PLUGIN_VERSION_LENGTH ),
			'queue_count'                 => $this->non_negative_int( $payload, 'queue_count' ),
			'uploaded_count'              => $this->non_negative_int( $payload, 'uploaded_count' ),
			'failed_count'                => $this->non_negative_int( $payload, 'failed_count' ),
			'active_upload'               => $this->bool_field( $payload, 'active_upload' ),
			'auto_scan_enabled'           => $this->bool_field( $payload, 'auto_scan_enabled' ),
			'server_cron_expected'        => $this->bool_field( $payload, 'server_cron_expected' ),
			'server_outbox_configured'    => $this->bool_field( $payload, 'server_outbox_configured' ),
			'server_outbox_readable'      => $this->bool_field( $payload, 'server_outbox_readable' ),
			'wpvivid_override_configured' => $this->bool_field( $payload, 'wpvivid_override_configured' ),
			'old_wpvivid_uploader_active' => $this->bool_field( $payload, 'old_wpvivid_uploader_active' ),
			'wp_cron_disabled'            => $this->bool_field( $payload, 'wp_cron_disabled' ),
			'cron_status'                 => isset( $payload['cron_status'] ) ? sanitize_key( (string) $payload['cron_status'] ) : '',
			'cron_reason'                 => isset( $payload['cron_reason'] ) ? sanitize_text_field( (string) $payload['cron_reason'] ) : '',
			'warning_count'               => $this->non_negative_int( $payload, 'warning_count' ),
			'warnings'                    => $this->warnings( isset( $payload['warnings'] ) ? $payload['warnings'] : array() ),
			'last_runner'                 => isset( $payload['last_runner'] ) ? sanitize_key( (string) $payload['last_runner'] ) : '',
			'last_runner_at'              => $this->non_negative_int( $payload, 'last_runner_at' ),
			'last_scheduled_scan_at'      => $this->non_negative_int( $payload, 'last_scheduled_scan_at' ),
			'last_wp_cli_scan_at'         => $this->non_negative_int( $payload, 'last_wp_cli_scan_at' ),
		);

		$backup_sources = $this->backup_sources( isset( $payload['backup_sources'] ) ? $payload['backup_sources'] : array() );

		if ( ! empty( $backup_sources ) ) {
			$validated['backup_sources'] = $backup_sources;
		}

		if ( '' === $validated['plugin_version'] || '' === $validated['cron_status'] ) {
			return new WP_Error( 'payload_invalid', __( 'The client status payload is missing required fields.', 'alynt-drime-backups-dashboard' ) );
		}

		return $validated;
	}

	/**
	 * Recursively detects forbidden keys anywhere in a payload.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return bool
	 */
	private function contains_forbidden_field( array $payload ) {
		foreach ( $payload as $key => $value ) {
			if ( in_array( sanitize_key( (string) $key ), $this->forbidden_fields, true ) ) {
				return true;
			}

			if ( is_array( $value ) && $this->contains_forbidden_field( $value ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sanitizes optional per-source backup freshness summaries.
	 *
	 * @param mixed $sources Source summaries.
	 * @return array<string,array<string,mixed>>
	 */
	private function backup_sources( $sources ) {
		if ( ! is_array( $sources ) ) {
			return array();
		}

		$clean = array();

		foreach ( array( 'server', 'wpvivid' ) as $source_key ) {
			if ( empty( $sources[ $source_key ] ) || ! is_array( $sources[ $source_key ] ) ) {
				continue;
			}

			$source               = $sources[ $source_key ];
			$warnings             = $this->warnings( isset( $source['warnings'] ) ? $source['warnings'] : array(), 10 );
			$clean[ $source_key ] = array(
				'source_key'                         => $source_key,
				'source_label'                       => $this->bounded_text( isset( $source['source_label'] ) ? (string) $source['source_label'] : '', self::MAX_SOURCE_LABEL_LENGTH ),
				'configured'                         => $this->bool_field( $source, 'configured' ),
				'has_upload_evidence'                => $this->bool_field( $source, 'has_upload_evidence' ),
				'queued_count'                       => $this->non_negative_int( $source, 'queued_count' ),
				'uploaded_count'                     => $this->non_negative_int( $source, 'uploaded_count' ),
				'failed_count'                       => $this->non_negative_int( $source, 'failed_count' ),
				'remote_registry_count'              => $this->non_negative_int( $source, 'remote_registry_count' ),
				'latest_created_at'                  => $this->non_negative_int( $source, 'latest_created_at' ),
				'latest_uploaded_at'                 => $this->non_negative_int( $source, 'latest_uploaded_at' ),
				'latest_upload_age_seconds'          => $this->non_negative_int( $source, 'latest_upload_age_seconds' ),
				'latest_remote_status'               => $this->source_status( isset( $source['latest_remote_status'] ) ? (string) $source['latest_remote_status'] : '', array( 'uploaded', 'trashed', '' ) ),
				'latest_inventory_count'             => $this->non_negative_int( $source, 'latest_inventory_count' ),
				'latest_inventory_evidence'          => $this->source_status( isset( $source['latest_inventory_evidence'] ) ? (string) $source['latest_inventory_evidence'] : '', array( 'generic_outbox_remote_catalog', 'generic_outbox_remote_index', 'local_upload_registry', '' ) ),
				'latest_source_activity_at'          => $this->non_negative_int( $source, 'latest_source_activity_at' ),
				'latest_source_activity_age_seconds' => $this->non_negative_int( $source, 'latest_source_activity_age_seconds' ),
				'source_activity_evidence'           => $this->source_status( isset( $source['source_activity_evidence'] ) ? (string) $source['source_activity_evidence'] : '', array( 'wpvivid_backup_log', 'wpvivid_local_archive', '' ) ),
				'local_candidate_count'              => $this->non_negative_int( $source, 'local_candidate_count' ),
				'freshness_status'                   => $this->source_status( isset( $source['freshness_status'] ) ? (string) $source['freshness_status'] : '', array( 'not_configured', 'no_upload_evidence', 'stale', 'fresh', '' ) ),
				'freshness_window_seconds'           => $this->non_negative_int( $source, 'freshness_window_seconds' ),
				'warning_count'                      => count( $warnings ),
				'warnings'                           => $warnings,
			);

			if ( 'wpvivid' === $source_key ) {
				$clean[ $source_key ]['schedule_policy'] = $this->schedule_policy( isset( $source['schedule_policy'] ) ? $source['schedule_policy'] : array() );
			}
		}

		return $clean;
	}

	/**
	 * Sanitizes optional redacted WPvivid schedule policy evidence.
	 *
	 * @param mixed $policy Schedule policy payload.
	 * @return array<string,mixed>
	 */
	private function schedule_policy( $policy ) {
		if ( ! is_array( $policy ) ) {
			$policy = array();
		}

		return array(
			'detected'              => $this->bool_field( $policy, 'detected' ),
			'basis'                 => $this->source_status( isset( $policy['basis'] ) ? (string) $policy['basis'] : '', array( 'wpvivid_schedule_setting', 'wp_cron_event', 'not_detected', '' ) ),
			'recurrence'            => isset( $policy['recurrence'] ) ? sanitize_key( (string) $policy['recurrence'] ) : '',
			'schedule_count'        => $this->non_negative_int( $policy, 'schedule_count' ),
			'interval_seconds'      => $this->non_negative_int( $policy, 'interval_seconds' ),
			'grace_seconds'         => $this->non_negative_int( $policy, 'grace_seconds' ),
			'policy_window_seconds' => $this->non_negative_int( $policy, 'policy_window_seconds' ),
		);
	}

	/**
	 * Sanitizes an allowlisted source status label.
	 *
	 * @param string            $value Raw value.
	 * @param array<int,string> $allowed Allowed labels.
	 * @return string
	 */
	private function source_status( $value, array $allowed ) {
		$value = sanitize_key( $value );

		return in_array( $value, $allowed, true ) ? $value : '';
	}

	/**
	 * Gets a non-negative integer field.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $field Field name.
	 * @return int
	 */
	private function non_negative_int( array $payload, $field ) {
		return isset( $payload[ $field ] ) ? max( 0, absint( $payload[ $field ] ) ) : 0;
	}

	/**
	 * Gets a boolean field.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $field Field name.
	 * @return bool
	 */
	private function bool_field( array $payload, $field ) {
		return ! empty( $payload[ $field ] );
	}

	/**
	 * Sanitizes warning records.
	 *
	 * @param mixed $warnings Warning records.
	 * @param int   $limit Maximum warning records.
	 * @return array<int,array<string,string>>
	 */
	private function warnings( $warnings, $limit = 20 ) {
		if ( ! is_array( $warnings ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $warnings, 0, max( 0, (int) $limit ) ) as $warning ) {
			if ( ! is_array( $warning ) ) {
				continue;
			}

			$clean[] = array(
				'code'    => isset( $warning['code'] ) ? sanitize_key( (string) $warning['code'] ) : '',
				'message' => isset( $warning['message'] ) ? sanitize_text_field( (string) $warning['message'] ) : '',
			);
		}

		return $clean;
	}

	/**
	 * Sanitizes a UUID.
	 *
	 * @param string $uuid UUID.
	 * @return string
	 */
	private function sanitize_uuid( $uuid ) {
		$uuid = strtolower( trim( (string) $uuid ) );

		return preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $uuid ) ? $uuid : '';
	}

	/**
	 * Sanitizes and bounds a text field before storing it in fixed-width columns.
	 *
	 * @param string $value Raw value.
	 * @param int    $max_length Maximum characters.
	 * @return string
	 */
	private function bounded_text( $value, $max_length ) {
		$value      = sanitize_text_field( (string) $value );
		$max_length = max( 1, (int) $max_length );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max_length );
		}

		return substr( $value, 0, $max_length );
	}
}
