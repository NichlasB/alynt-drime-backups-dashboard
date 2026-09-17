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
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Backup_Sources;

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
		'private_key',
		'action_private_key',
		'raw_response',
		'command',
		'filename',
		'backup_id',
		'backup_name',
		'token',
	);

	/**
	 * Remote action capability sanitizer.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities
	 */
	private $remote_action_capabilities;

	/**
	 * Constructor.
	 *
	 * @since 0.1.15
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities|null $remote_action_capabilities Remote-action sanitizer.
	 */
	public function __construct( $remote_action_capabilities = null ) {
		$this->remote_action_capabilities = $remote_action_capabilities instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities ? $remote_action_capabilities : new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
	}

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

		$remote_actions = $this->remote_action_capabilities->sanitize( isset( $payload['remote_actions'] ) ? $payload['remote_actions'] : array() );

		if ( is_wp_error( $remote_actions ) ) {
			return $remote_actions;
		}

		if ( ! empty( $remote_actions ) ) {
			$validated['remote_actions'] = $remote_actions;
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
