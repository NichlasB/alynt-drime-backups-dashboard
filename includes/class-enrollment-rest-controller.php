<?php
/**
 * Dashboard enrollment REST endpoint.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles uploader enrollment completion requests.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller {
	use Alynt_Drime_Backups_Dashboard_Enrollment_REST_Responses;
	use Alynt_Drime_Backups_Dashboard_Enrollment_REST_Route_Args;
	use Alynt_Drime_Backups_Dashboard_Enrollment_REST_Validation;
	use Alynt_Drime_Backups_Dashboard_Enrollment_REST_Rate_Limits;

	const REST_NAMESPACE                        = 'alynt-drime-backups-dashboard/v1';
	const REST_ROUTE                            = '/enroll';
	const PROTOCOL_VERSION                      = 1;
	const STATUS_SCHEMA_VERSION                 = 1;
	const ENROLLMENT_STATUS_AWAITING_FIRST_POLL = 'awaiting_first_poll';
	const MAX_UPLOADER_VERSION_LENGTH           = 64;
	const RATE_LIMIT_TRANSIENT_PREFIX           = 'alynt_drime_backups_dashboard_enroll_fail_';
	const RATE_LIMIT_FAILURE_THRESHOLD          = 10;
	const RATE_LIMIT_WINDOW_SECONDS             = 300;

	/**
	 * Site repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Site_Repository
	 */
	private $sites;

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
	 * Structured event log.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Event_Log
	 */
	private $event_log;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null  $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Origin_Validator|null $origins Origin validator.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault|null $vault Credential vault.
	 * @param Alynt_Drime_Backups_Dashboard_Event_Log|null        $event_log Event log.
	 */
	public function __construct( $sites = null, $origins = null, $vault = null, $event_log = null ) {
		$this->sites     = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->origins   = $origins instanceof Alynt_Drime_Backups_Dashboard_Origin_Validator ? $origins : new Alynt_Drime_Backups_Dashboard_Origin_Validator();
		$this->vault     = $vault instanceof Alynt_Drime_Backups_Dashboard_Credential_Vault ? $vault : new Alynt_Drime_Backups_Dashboard_Credential_Vault();
		$this->event_log = $event_log instanceof Alynt_Drime_Backups_Dashboard_Event_Log ? $event_log : new Alynt_Drime_Backups_Dashboard_Event_Log();
	}

	/**
	 * Registers REST routes.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_enroll_request' ),
				'permission_callback' => array( $this, 'permission_callback' ),
				'args'                => $this->enrollment_route_args(),
			)
		);
	}

	/**
	 * Checks the public enrollment request has bearer credential shape.
	 *
	 * The one-time pairing secret is validated in the endpoint handler so
	 * failures can be logged, throttled, and matched to a pending enrollment
	 * record without using WordPress cookies or nonces for this client opt-in
	 * exchange.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true|WP_Error
	 */
	public function permission_callback( $request ) {
		$authorization = method_exists( $request, 'get_header' ) ? (string) $request->get_header( 'authorization' ) : '';

		if ( '' === $this->bearer_secret( $authorization ) ) {
			return $this->error( 'auth_missing', __( 'The enrollment request is missing a valid bearer credential.', 'alynt-drime-backups-dashboard' ), 401 );
		}

		return true;
	}

	/**
	 * Handles the REST request object.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_enroll_request( $request ) {
		$authorization = method_exists( $request, 'get_header' ) ? (string) $request->get_header( 'authorization' ) : '';
		$payload       = method_exists( $request, 'get_json_params' ) ? $request->get_json_params() : array();

		return $this->handle_enrollment( is_array( $payload ) ? $payload : array(), $authorization );
	}

	/**
	 * Handles an enrollment payload.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string,mixed> $payload Enrollment payload.
	 * @param string              $authorization Authorization header.
	 * @param int|null            $now Current timestamp override.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_enrollment( array $payload, $authorization, $now = null ) {
		$now            = null === $now ? time() : (int) $now;
		$rate_limit_key = $this->enrollment_rate_limit_key( $payload );

		if ( $this->is_enrollment_rate_limited( $rate_limit_key ) ) {
			$error = $this->error( 'rate_limited', __( 'Too many failed enrollment attempts. Please wait before trying again.', 'alynt-drime-backups-dashboard' ), 429 );
			$this->log_enrollment_failure( $error );
			return $error;
		}

		$secret     = $this->bearer_secret( $authorization );
		$enrollment = $this->validate_payload_shape( $payload );

		if ( is_wp_error( $enrollment ) ) {
			$this->record_enrollment_failure( $rate_limit_key );
			$this->log_enrollment_failure( $enrollment );
			return $enrollment;
		}

		$site = $this->sites->get_pending_by_public_id( $enrollment['enrollment_id'] );

		if ( ! $site ) {
			return $this->throttled_enrollment_error( $rate_limit_key, $this->error( 'pairing_invalid', __( 'The pairing enrollment is not valid.', 'alynt-drime-backups-dashboard' ), 403 ) );
		}

		if ( empty( $site['pairing_secret_hash'] ) || empty( $site['pairing_expires_at'] ) ) {
			return $this->throttled_enrollment_error( $rate_limit_key, $this->error( 'pairing_used', __( 'The pairing token has already been consumed.', 'alynt-drime-backups-dashboard' ), 409 ), $site );
		}

		if ( strtotime( (string) $site['pairing_expires_at'] ) <= $now ) {
			return $this->throttled_enrollment_error( $rate_limit_key, $this->error( 'pairing_expired', __( 'The pairing token has expired.', 'alynt-drime-backups-dashboard' ), 410 ), $site );
		}

		if ( '' === $secret || ! hash_equals( (string) $site['pairing_secret_hash'], Alynt_Drime_Backups_Dashboard_Pairing_Tokens::hash_secret( $secret ) ) ) {
			return $this->throttled_enrollment_error( $rate_limit_key, $this->error( 'pairing_invalid', __( 'The pairing credential is not valid.', 'alynt-drime-backups-dashboard' ), 403 ), $site );
		}

		if ( ! hash_equals( (string) $site['expected_origin'], $enrollment['home_origin'] ) ) {
			return $this->throttled_enrollment_error( $rate_limit_key, $this->error( 'origin_mismatch', __( 'The client origin does not match the pending dashboard record.', 'alynt-drime-backups-dashboard' ), 409 ), $site );
		}

		$expected_endpoint = $this->origins->status_endpoint_for_origin( $site['expected_origin'] );

		if ( ! hash_equals( $expected_endpoint, $enrollment['status_endpoint'] ) ) {
			return $this->throttled_enrollment_error( $rate_limit_key, $this->error( 'endpoint_invalid', __( 'The client status endpoint is not the fixed read-only route.', 'alynt-drime-backups-dashboard' ), 400 ), $site );
		}

		$polling_key_id = $this->create_polling_key_id();
		$polling_secret = Alynt_Drime_Backups_Dashboard_Pairing_Tokens::create_secret();
		$ciphertext     = $this->vault->encrypt( $polling_secret, 'site:' . $site['public_id'] );

		if ( is_wp_error( $ciphertext ) ) {
			$this->record_enrollment_failure( $rate_limit_key );
			$this->log_enrollment_failure( $ciphertext, $site );
			return $ciphertext;
		}

		$stored = $this->sites->complete_enrollment_pending_first_poll(
			(int) $site['id'],
			array(
				'site_uuid'                 => $enrollment['site_uuid'],
				'polling_key_id'            => $polling_key_id,
				'polling_secret_ciphertext' => $ciphertext,
				'plugin_version'            => $enrollment['uploader_version'],
				'payload_schema_version'    => self::STATUS_SCHEMA_VERSION,
			)
		);

		if ( ! $stored ) {
			return $this->throttled_enrollment_error( $rate_limit_key, $this->error( 'enrollment_store_failed', __( 'The dashboard could not store the enrollment state.', 'alynt-drime-backups-dashboard' ), 500 ), $site );
		}

		$this->clear_enrollment_failures( $rate_limit_key );

		return $this->response(
			array(
				'protocol_version'         => self::PROTOCOL_VERSION,
				'dashboard_site_public_id' => (string) $site['public_id'],
				'polling_key_id'           => $polling_key_id,
				'polling_secret'           => $polling_secret,
				'polling_auth_scheme'      => 'Bearer adb-poll-v1.' . $polling_key_id . '.' . $polling_secret,
				'first_poll_required'      => true,
			),
			201
		);
	}
}
