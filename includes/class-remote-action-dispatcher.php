<?php
/**
 * Remote action dispatcher.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates, signs, dispatches, and records V2.1 remote action intents.
 *
 * @since 0.1.15
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher {
	const ACTION_ROUTE       = '/wp-json/alynt-drime-backups-uploader/v2/action-intents';
	const DEFAULT_TIMEOUT    = 15;
	const MAX_RESPONSE_BYTES = 32768;
	const INTENT_TTL_SECONDS = 300;

	/**
	 * Site repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Site_Repository
	 */
	private $sites;

	/**
	 * Snapshot repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Snapshot_Repository
	 */
	private $snapshots;

	/**
	 * Action repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
	 */
	private $actions;

	/**
	 * Origin validator.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Origin_Validator
	 */
	private $origins;

	/**
	 * Credential vault.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Credential_Vault
	 */
	private $vault;

	/**
	 * Signer.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Signer
	 */
	private $signer;

	/**
	 * Capabilities.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities
	 */
	private $capabilities;

	/**
	 * Optional HTTP client override.
	 *
	 * @var callable|null
	 */
	private $http_client;

	/**
	 * Optional DNS resolver override.
	 *
	 * @var callable|null
	 */
	private $resolver;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null            $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Snapshot_Repository|null        $snapshots Snapshot repository.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Repository|null   $actions Action repository.
	 * @param Alynt_Drime_Backups_Dashboard_Origin_Validator|null           $origins Origin validator.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault|null           $vault Credential vault.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Signer|null       $signer Signer.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities|null $capabilities Capabilities.
	 * @param callable|null                                                 $http_client HTTP client.
	 * @param callable|null                                                 $resolver DNS resolver.
	 */
	public function __construct( $sites = null, $snapshots = null, $actions = null, $origins = null, $vault = null, $signer = null, $capabilities = null, $http_client = null, $resolver = null ) {
		$this->sites        = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->snapshots    = $snapshots instanceof Alynt_Drime_Backups_Dashboard_Snapshot_Repository ? $snapshots : new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$this->actions      = $actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository ? $actions : new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$this->origins      = $origins instanceof Alynt_Drime_Backups_Dashboard_Origin_Validator ? $origins : new Alynt_Drime_Backups_Dashboard_Origin_Validator();
		$this->vault        = $vault instanceof Alynt_Drime_Backups_Dashboard_Credential_Vault ? $vault : new Alynt_Drime_Backups_Dashboard_Credential_Vault();
		$this->signer       = $signer instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Signer ? $signer : new Alynt_Drime_Backups_Dashboard_Remote_Action_Signer();
		$this->capabilities = $capabilities instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities ? $capabilities : new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$this->http_client  = is_callable( $http_client ) ? $http_client : null;
		$this->resolver     = is_callable( $resolver ) ? $resolver : null;
	}

	/**
	 * Requests scan/upload-now on one opted-in client site.
	 *
	 * @param int $site_id Site ID.
	 * @param int $requested_by User ID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function request_scan_upload_now( $site_id, $requested_by = 0 ) {
		$site_id = absint( $site_id );

		if ( 0 === $site_id ) {
			return new WP_Error( 'remote_action_site_required', __( 'Choose an enrolled site before requesting a remote action.', 'alynt-drime-backups-dashboard' ) );
		}

		$site = $this->sites->get( $site_id );
		if ( ! is_array( $site ) ) {
			return new WP_Error( 'remote_action_site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );
		}

		$capabilities = $this->latest_capabilities( $site_id );
		if ( is_wp_error( $capabilities ) ) {
			return $capabilities;
		}

		$prepared = $this->prepare_signed_intent( $site, $capabilities );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$action_id = $this->actions->create_request(
			$site_id,
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW,
			$requested_by,
			$prepared['body']['idempotency_key'],
			$prepared['key_id'],
			gmdate( 'Y-m-d H:i:s', strtotime( $prepared['body']['expires_at'] ) ),
			$prepared['request_fingerprint'],
			array(
				'capability_reported'   => true,
				'min_interval_seconds'  => isset( $capabilities['min_interval_seconds'] ) ? absint( $capabilities['min_interval_seconds'] ) : 0,
				'requested_action_type' => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW,
			)
		);

		if ( is_wp_error( $action_id ) ) {
			return $action_id;
		}

		if ( method_exists( $this->actions, 'mark_dispatched' ) ) {
			$this->actions->mark_dispatched( $action_id );
		}

		$response = $this->post_intent( $prepared );

		if ( is_wp_error( $response ) ) {
			$this->actions->mark_state( $action_id, 'dispatch_failed', $response->get_error_code(), __( 'The signed request could not be delivered to the client site.', 'alynt-drime-backups-dashboard' ) );
			return $response;
		}

		$recorded = $this->actions->mark_state(
			$action_id,
			$response['state'],
			$response['code'],
			$response['summary'],
			$response['retry_after']
		);

		if ( ! $recorded ) {
			return new WP_Error( 'remote_action_state_store_failed', __( 'The dashboard could not store the remote action response.', 'alynt-drime-backups-dashboard' ) );
		}

		return array(
			'action'         => 'request_backup_now',
			'site_id'        => $site_id,
			'action_id'      => $action_id,
			'remote_state'   => $response['state'],
			'result_code'    => $response['code'],
			'result_summary' => $response['summary'],
			'retry_after'    => $response['retry_after'],
		);
	}

	/**
	 * Gets latest remote action capabilities.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|WP_Error
	 */
	private function latest_capabilities( $site_id ) {
		$snapshot = $this->snapshots->latest_for_site( $site_id );
		$payload  = is_array( $snapshot ) && isset( $snapshot['decoded_payload'] ) && is_array( $snapshot['decoded_payload'] ) ? $snapshot['decoded_payload'] : array();
		$remote   = isset( $payload['remote_actions'] ) && is_array( $payload['remote_actions'] ) ? $payload['remote_actions'] : array();
		$clean    = $this->capabilities->sanitize( $remote );

		if ( is_wp_error( $clean ) ) {
			return $clean;
		}

		if ( ! $this->capabilities->supports_scan_upload_now( $clean ) ) {
			return new WP_Error( 'remote_action_capability_missing', __( 'The latest client report does not allow Request Backup Now. Complete V2 opt-in and run Check Now first.', 'alynt-drime-backups-dashboard' ) );
		}

		return $clean;
	}

	/**
	 * Builds a signed request descriptor.
	 *
	 * @param array<string,mixed> $site Site.
	 * @param array<string,mixed> $capabilities Capabilities.
	 * @return array<string,mixed>|WP_Error
	 */
	private function prepare_signed_intent( array $site, array $capabilities ) {
		if ( empty( $site['polling_key_id'] ) || empty( $site['polling_secret_ciphertext'] ) ) {
			return new WP_Error( 'remote_action_requires_pairing', __( 'Active read-only pairing is required before requesting a remote action.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( empty( $site['public_id'] ) || empty( $site['site_uuid'] ) || empty( $site['expected_origin'] ) || empty( $site['action_key_id'] ) || empty( $site['action_private_key_ciphertext'] ) ) {
			return new WP_Error( 'remote_action_key_missing', __( 'Generate and complete a V2 action opt-in token before requesting this action.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( empty( $capabilities['key_id'] ) || ! hash_equals( (string) $site['action_key_id'], (string) $capabilities['key_id'] ) ) {
			return new WP_Error( 'remote_action_key_mismatch', __( 'The latest client capability report does not match the dashboard action key. Regenerate the V2 opt-in token and run Check Now.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( ! $this->signer->is_supported() ) {
			return new WP_Error( 'action_signing_unavailable', __( 'Remote action signing is unavailable because PHP Sodium support is missing.', 'alynt-drime-backups-dashboard' ) );
		}

		$origin = $this->origins->normalize_public_https_origin( (string) $site['expected_origin'] );
		if ( '' === $origin ) {
			return new WP_Error( 'remote_action_destination_invalid', __( 'The client action destination is not a supported public HTTPS origin.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( ! $this->origins->resolved_origin_is_public( $origin, $this->resolver ) ) {
			return new WP_Error( 'remote_action_destination_unsafe', __( 'The client action destination did not resolve to a public IP address.', 'alynt-drime-backups-dashboard' ) );
		}

		$private_key = $this->vault->decrypt( (string) $site['action_private_key_ciphertext'], 'action:' . (string) $site['public_id'] );
		if ( is_wp_error( $private_key ) ) {
			return $private_key;
		}

		$now       = time();
		$body      = array(
			'protocol_version'         => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::PROTOCOL_VERSION,
			'action_id'                => $this->create_uuid(),
			'dashboard_site_public_id' => sanitize_text_field( (string) $site['public_id'] ),
			'site_uuid'                => sanitize_text_field( (string) $site['site_uuid'] ),
			'action_type'              => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW,
			'requested_at'             => gmdate( 'c', $now ),
			'expires_at'               => gmdate( 'c', $now + self::INTENT_TTL_SECONDS ),
			'idempotency_key'          => $this->create_idempotency_key(),
		);
		$body_json = $this->signer->canonical_json( $body );

		if ( is_wp_error( $body_json ) ) {
			return $body_json;
		}

		$signed_at     = gmdate( 'c', $now );
		$signing_input = $this->signer->signing_input( 'POST', self::ACTION_ROUTE, $origin, $body_json, $signed_at );
		$signature     = $this->signer->sign( $private_key, $signing_input );

		if ( is_wp_error( $signature ) ) {
			return $signature;
		}

		return array(
			'url'                 => $origin . self::ACTION_ROUTE,
			'origin'              => $origin,
			'route'               => self::ACTION_ROUTE,
			'key_id'              => (string) $site['action_key_id'],
			'signed_at'           => $signed_at,
			'signature'           => $signature,
			'body'                => $body,
			'body_json'           => $body_json,
			'request_fingerprint' => hash( 'sha256', $body_json ),
		);
	}

	/**
	 * Posts a signed intent and returns the safe response.
	 *
	 * @param array<string,mixed> $prepared Prepared request.
	 * @return array<string,mixed>|WP_Error
	 */
	private function post_intent( array $prepared ) {
		$http_client = $this->http_client;

		if ( null === $http_client ) {
			if ( ! function_exists( 'wp_safe_remote_post' ) ) {
				return new WP_Error( 'transport_unavailable', __( 'WordPress HTTP transport is not available.', 'alynt-drime-backups-dashboard' ) );
			}

			$http_client = 'wp_safe_remote_post';
		}

		$response = call_user_func(
			$http_client,
			(string) $prepared['url'],
			array(
				'method'              => 'POST',
				'timeout'             => self::DEFAULT_TIMEOUT,
				'redirection'         => 0,
				'limit_response_size' => self::MAX_RESPONSE_BYTES,
				'reject_unsafe_urls'  => true,
				'headers'             => array(
					'Accept'                  => 'application/json',
					'Content-Type'            => 'application/json',
					'Cache-Control'           => 'no-store',
					'X-Adbd-Action-Key-Id'    => (string) $prepared['key_id'],
					'X-Adbd-Action-Signature' => (string) $prepared['signature'],
					'X-Adbd-Action-Signed-At' => (string) $prepared['signed_at'],
				),
				'body'                => (string) $prepared['body_json'],
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( $response->get_error_code(), __( 'The signed request could not reach the client action endpoint.', 'alynt-drime-backups-dashboard' ) );
		}

		$code = $this->response_code( $response );
		$body = $this->response_body( $response );

		if ( strlen( $body ) > self::MAX_RESPONSE_BYTES ) {
			return new WP_Error( 'remote_action_response_too_large', __( 'The client action response exceeded the dashboard size limit.', 'alynt-drime-backups-dashboard' ) );
		}

		$payload = json_decode( $body, true, 32 );
		if ( ! is_array( $payload ) ) {
			return new WP_Error( 'remote_action_response_invalid', __( 'The client action response was not valid JSON.', 'alynt-drime-backups-dashboard' ) );
		}

		$expected_action_id = isset( $prepared['body']['action_id'] ) ? (string) $prepared['body']['action_id'] : '';
		$response_action_id = isset( $payload['action_id'] ) ? sanitize_text_field( (string) $payload['action_id'] ) : '';

		if ( '' === $expected_action_id || ! hash_equals( $expected_action_id, $response_action_id ) ) {
			return new WP_Error( 'remote_action_response_mismatch', __( 'The client action response did not match the dashboard request.', 'alynt-drime-backups-dashboard' ) );
		}

		$state = $this->capabilities->sanitize_state( isset( $payload['state'] ) ? (string) $payload['state'] : '' );
		if ( 'queued_for_dispatch' === $state ) {
			return new WP_Error( 'remote_action_response_invalid', __( 'The client action response did not include a supported action state.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( $code < 200 || $code >= 500 ) {
			return new WP_Error( 'remote_action_http_status', __( 'The client action endpoint returned an unsupported HTTP status.', 'alynt-drime-backups-dashboard' ) );
		}

		return array(
			'state'       => $state,
			'code'        => isset( $payload['code'] ) ? sanitize_key( (string) $payload['code'] ) : '',
			'summary'     => isset( $payload['summary'] ) ? $this->bounded_summary( (string) $payload['summary'] ) : '',
			'retry_after' => isset( $payload['retry_after'] ) ? max( 0, absint( $payload['retry_after'] ) ) : 0,
		);
	}

	/**
	 * Extracts HTTP response code.
	 *
	 * @param mixed $response Response.
	 * @return int
	 */
	private function response_code( $response ) {
		if ( function_exists( 'wp_remote_retrieve_response_code' ) ) {
			return (int) wp_remote_retrieve_response_code( $response );
		}

		return isset( $response['response']['code'] ) ? (int) $response['response']['code'] : 0;
	}

	/**
	 * Extracts HTTP response body.
	 *
	 * @param mixed $response Response.
	 * @return string
	 */
	private function response_body( $response ) {
		if ( function_exists( 'wp_remote_retrieve_body' ) ) {
			return (string) wp_remote_retrieve_body( $response );
		}

		return isset( $response['body'] ) ? (string) $response['body'] : '';
	}

	/**
	 * Creates a UUID.
	 *
	 * @return string
	 */
	private function create_uuid() {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			return (string) wp_generate_uuid4();
		}

		$bytes    = random_bytes( 16 );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );
		$hex      = bin2hex( $bytes );

		return sprintf( '%s-%s-%s-%s-%s', substr( $hex, 0, 8 ), substr( $hex, 8, 4 ), substr( $hex, 12, 4 ), substr( $hex, 16, 4 ), substr( $hex, 20, 12 ) );
	}

	/**
	 * Creates a bounded idempotency key.
	 *
	 * @return string
	 */
	private function create_idempotency_key() {
		return 'adb-act-' . str_replace( '-', '', $this->create_uuid() );
	}

	/**
	 * Bounds a safe result summary.
	 *
	 * @param string $summary Summary.
	 * @return string
	 */
	private function bounded_summary( $summary ) {
		$summary = sanitize_text_field( (string) $summary );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $summary, 0, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::MAX_RESULT_SUMMARY_LENGTH );
		}

		return substr( $summary, 0, Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::MAX_RESULT_SUMMARY_LENGTH );
	}
}
