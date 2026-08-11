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

	/**
	 * Validates enrollment payload shape and normalized values.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,string>|WP_Error
	 */
	private function validate_payload_shape( array $payload ) {
		if ( self::PROTOCOL_VERSION !== absint( isset( $payload['protocol_version'] ) ? $payload['protocol_version'] : 0 ) ) {
			return $this->error( 'protocol_unsupported', __( 'The enrollment protocol version is not supported.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		if ( self::STATUS_SCHEMA_VERSION !== absint( isset( $payload['status_schema_version'] ) ? $payload['status_schema_version'] : 0 ) ) {
			return $this->error( 'schema_unsupported', __( 'The client status schema version is not supported.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		$enrollment_id = $this->sanitize_uuid( isset( $payload['enrollment_id'] ) ? (string) $payload['enrollment_id'] : '' );
		$site_uuid     = $this->sanitize_uuid( isset( $payload['site_uuid'] ) ? (string) $payload['site_uuid'] : '' );
		$home_origin   = $this->origins->normalize_public_https_origin( isset( $payload['home_url'] ) ? (string) $payload['home_url'] : '' );
		$endpoint      = isset( $payload['status_endpoint'] ) ? esc_url_raw( (string) $payload['status_endpoint'] ) : '';

		if ( '' === $enrollment_id ) {
			return $this->error( 'pairing_invalid', __( 'The enrollment ID is not valid.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		if ( '' === $site_uuid ) {
			return $this->error( 'payload_invalid', __( 'The client site UUID is not valid.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		if ( '' === $home_origin ) {
			return $this->error( 'origin_mismatch', __( 'The client home URL is not a supported public HTTPS origin.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		if ( '' === $endpoint || $endpoint !== $this->origins->status_endpoint_for_origin( $home_origin ) ) {
			return $this->error( 'endpoint_invalid', __( 'The client status endpoint is not the fixed read-only route.', 'alynt-drime-backups-dashboard' ), 400 );
		}

		return array(
			'enrollment_id'    => $enrollment_id,
			'site_uuid'        => $site_uuid,
			'home_origin'      => $home_origin,
			'status_endpoint'  => $endpoint,
			'uploader_version' => $this->bounded_text( isset( $payload['uploader_version'] ) ? (string) $payload['uploader_version'] : '', self::MAX_UPLOADER_VERSION_LENGTH ),
		);
	}

	/**
	 * Extracts the bearer secret from an authorization header.
	 *
	 * @param string $authorization Header.
	 * @return string
	 */
	private function bearer_secret( $authorization ) {
		$authorization = trim( (string) $authorization );

		if ( ! preg_match( '/^Bearer\s+([A-Za-z0-9_-]{32,})$/', $authorization, $matches ) ) {
			return '';
		}

		return $matches[1];
	}

	/**
	 * Creates a polling key ID.
	 *
	 * @return string
	 */
	private function create_polling_key_id() {
		return 'pk_' . substr( Alynt_Drime_Backups_Dashboard_Pairing_Tokens::create_secret( 18 ), 0, 24 );
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

	/**
	 * Builds an enrollment failure error and increments the rate-limit counter.
	 *
	 * @param string                   $rate_limit_key Rate-limit transient key.
	 * @param WP_Error                 $error Enrollment error.
	 * @param array<string,mixed>|null $site Site row.
	 * @return WP_Error
	 */
	private function throttled_enrollment_error( $rate_limit_key, $error, $site = null ) {
		$this->record_enrollment_failure( $rate_limit_key );
		$this->log_enrollment_failure( $error, $site );

		return $error;
	}

	/**
	 * Builds a transient key for enrollment failure throttling.
	 *
	 * @param array<string,mixed> $payload Enrollment payload.
	 * @return string
	 */
	private function enrollment_rate_limit_key( array $payload ) {
		$enrollment_id = isset( $payload['enrollment_id'] ) ? $this->sanitize_uuid( (string) $payload['enrollment_id'] ) : '';
		$key_material  = '' === $enrollment_id ? 'missing-enrollment-id' : $enrollment_id;

		return self::RATE_LIMIT_TRANSIENT_PREFIX . hash( 'sha256', $key_material );
	}

	/**
	 * Determines whether an enrollment key is currently rate limited.
	 *
	 * @param string $key Rate-limit transient key.
	 * @return bool
	 */
	private function is_enrollment_rate_limited( $key ) {
		if ( ! function_exists( 'get_transient' ) ) {
			return false;
		}

		$state = get_transient( $key );
		$count = is_array( $state ) && isset( $state['count'] ) ? (int) $state['count'] : (int) $state;

		return $count >= self::RATE_LIMIT_FAILURE_THRESHOLD;
	}

	/**
	 * Records one failed enrollment attempt for throttling.
	 *
	 * @param string $key Rate-limit transient key.
	 * @return void
	 */
	private function record_enrollment_failure( $key ) {
		if ( ! function_exists( 'get_transient' ) || ! function_exists( 'set_transient' ) ) {
			return;
		}

		$state = get_transient( $key );
		$count = is_array( $state ) && isset( $state['count'] ) ? (int) $state['count'] : (int) $state;

		set_transient(
			$key,
			array(
				'count' => min( self::RATE_LIMIT_FAILURE_THRESHOLD, $count + 1 ),
			),
			self::RATE_LIMIT_WINDOW_SECONDS
		);
	}

	/**
	 * Clears enrollment failures after a successful enrollment.
	 *
	 * @param string $key Rate-limit transient key.
	 * @return void
	 */
	private function clear_enrollment_failures( $key ) {
		if ( function_exists( 'delete_transient' ) ) {
			delete_transient( $key );
		}
	}
}
