<?php
/**
 * Enrollment REST controller tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-origin-validator.php';
require_once dirname( __DIR__ ) . '/includes/class-pairing-tokens.php';
require_once dirname( __DIR__ ) . '/includes/class-credential-vault.php';
require_once dirname( __DIR__ ) . '/includes/class-site-repository.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-enrollment-rest-responses.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-enrollment-rest-route-args.php';
require_once dirname( __DIR__ ) . '/includes/class-enrollment-rest-controller.php';

/**
 * Fake repository for enrollment REST tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Pending site.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site;

	/**
	 * Stored enrollment data.
	 *
	 * @var array<string,mixed>
	 */
	public $stored = array();

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed>|null $site Site.
	 */
	public function __construct( $site = null ) {
		$this->site = $site;
	}

	/**
	 * Gets a pending site by public ID.
	 *
	 * @param string $public_id Public ID.
	 * @return array<string,mixed>|null
	 */
	public function get_pending_by_public_id( $public_id ) {
		if ( ! $this->site || $public_id !== $this->site['public_id'] || 'pending' !== $this->site['enrollment_status'] ) {
			return null;
		}

		return $this->site;
	}

	/**
	 * Stores enrollment state.
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $data Data.
	 * @return bool
	 */
	public function complete_enrollment_pending_first_poll( $site_id, array $data ) {
		$this->stored = array_merge(
			array(
				'site_id' => $site_id,
			),
			$data
		);

		return true;
	}
}

/**
 * Tests enrollment REST controller behavior.
 */
class EnrollmentRestControllerTest extends TestCase {
	/**
	 * Successful enrollment returns polling credential once and stores ciphertext only.
	 *
	 * @return void
	 */
	public function test_successful_enrollment_returns_polling_credential_and_stores_ciphertext() {
		$secret     = str_repeat( 'A', 43 );
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( $secret ) );
		$vault      = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$controller = $this->controller( $repository, $vault );

		$result = $controller->handle_enrollment( $this->payload(), 'Bearer ' . $secret, strtotime( '2099-01-01T00:00:00Z' ) );

		$this->assertIsArray( $result );
		$this->assertSame( 201, $result['status'] );
		$this->assertSame( 'no-store', $result['headers']['Cache-Control'] );
		$this->assertSame( 1, $result['data']['protocol_version'] );
		$this->assertSame( '00000000-0000-4000-8000-000000000000', $result['data']['dashboard_site_public_id'] );
		$this->assertStringStartsWith( 'pk_', $result['data']['polling_key_id'] );
		$this->assertStringStartsWith( 'Bearer adb-poll-v1.', $result['data']['polling_auth_scheme'] );
		$this->assertTrue( $result['data']['first_poll_required'] );
		$this->assertSame( 77, $repository->stored['site_id'] );
		$this->assertSame( '11111111-1111-4111-8111-111111111111', $repository->stored['site_uuid'] );
		$this->assertSame( '0.5.3', $repository->stored['plugin_version'] );
		$this->assertSame( 1, $repository->stored['payload_schema_version'] );
		$this->assertStringStartsWith( 'adbv1.', $repository->stored['polling_secret_ciphertext'] );
		$this->assertStringNotContainsString( $result['data']['polling_secret'], $repository->stored['polling_secret_ciphertext'] );
		$this->assertSame( $result['data']['polling_secret'], $vault->decrypt( $repository->stored['polling_secret_ciphertext'], 'site:00000000-0000-4000-8000-000000000000' ) );
	}

	/**
	 * Wrong bearer secret is rejected and not stored.
	 *
	 * @return void
	 */
	public function test_wrong_secret_is_rejected() {
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( str_repeat( 'A', 43 ) ) );
		$controller = $this->controller( $repository );

		$result = $controller->handle_enrollment( $this->payload(), 'Bearer ' . str_repeat( 'B', 43 ), strtotime( '2099-01-01T00:00:00Z' ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'pairing_invalid', $result->get_error_code() );
		$this->assertSame( array(), $repository->stored );
	}

	/**
	 * Origin mismatch is rejected after authentication.
	 *
	 * @return void
	 */
	public function test_origin_mismatch_is_rejected() {
		$secret     = str_repeat( 'A', 43 );
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( $secret ) );
		$controller = $this->controller( $repository );
		$payload    = $this->payload(
			array(
				'home_url'        => 'https://other.example.com',
				'status_endpoint' => 'https://other.example.com/wp-json/alynt-drime-backups-uploader/v1/status',
			)
		);

		$result = $controller->handle_enrollment( $payload, 'Bearer ' . $secret, strtotime( '2099-01-01T00:00:00Z' ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'origin_mismatch', $result->get_error_code() );
		$this->assertSame( array(), $repository->stored );
	}

	/**
	 * Endpoint mismatch is rejected before storage.
	 *
	 * @return void
	 */
	public function test_endpoint_mismatch_is_rejected() {
		$secret     = str_repeat( 'A', 43 );
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( $secret ) );
		$controller = $this->controller( $repository );
		$payload    = $this->payload(
			array(
				'status_endpoint' => 'https://client.example.com/wp-json/not-the-fixed-route',
			)
		);

		$result = $controller->handle_enrollment( $payload, 'Bearer ' . $secret, strtotime( '2099-01-01T00:00:00Z' ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'endpoint_invalid', $result->get_error_code() );
		$this->assertSame( array(), $repository->stored );
	}

	/**
	 * Unsupported schema is rejected.
	 *
	 * @return void
	 */
	public function test_unsupported_schema_is_rejected() {
		$secret     = str_repeat( 'A', 43 );
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( $secret ) );
		$controller = $this->controller( $repository );

		$result = $controller->handle_enrollment( $this->payload( array( 'status_schema_version' => 2 ) ), 'Bearer ' . $secret, strtotime( '2099-01-01T00:00:00Z' ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'schema_unsupported', $result->get_error_code() );
		$this->assertSame( array(), $repository->stored );
	}

	/**
	 * Expired pairing token is rejected.
	 *
	 * @return void
	 */
	public function test_expired_pairing_is_rejected() {
		$secret     = str_repeat( 'A', 43 );
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( $secret, '2020-01-01 00:00:00' ) );
		$controller = $this->controller( $repository );

		$result = $controller->handle_enrollment( $this->payload(), 'Bearer ' . $secret, strtotime( '2099-01-01T00:00:00Z' ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'pairing_expired', $result->get_error_code() );
		$this->assertSame( array(), $repository->stored );
	}

	/**
	 * Route args define sanitizers and validators for expected JSON fields.
	 *
	 * @return void
	 */
	public function test_enrollment_route_args_define_sanitizers_and_validators() {
		$controller = $this->controller( new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository() );
		$args       = $controller->enrollment_route_args();

		foreach ( array( 'protocol_version', 'status_schema_version', 'enrollment_id', 'site_uuid', 'home_url', 'status_endpoint', 'uploader_version' ) as $field ) {
			$this->assertArrayHasKey( $field, $args );
			$this->assertArrayHasKey( 'sanitize_callback', $args[ $field ] );
			$this->assertArrayHasKey( 'validate_callback', $args[ $field ] );
		}

		$this->assertTrue( $controller->validate_protocol_version_arg( 1 ) );
		$this->assertFalse( $controller->validate_protocol_version_arg( 2 ) );
		$this->assertSame( '11111111-1111-4111-8111-111111111111', $controller->sanitize_uuid_arg( '11111111-1111-4111-8111-111111111111' ) );
		$this->assertFalse( $controller->validate_uuid_arg( 'not-a-uuid' ) );
		$this->assertSame( 'https://client.example.com', $controller->sanitize_public_origin_arg( 'https://Client.Example.com/' ) );
		$this->assertSame( 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v1/status', $controller->sanitize_status_endpoint_arg( 'https://Client.Example.com/wp-json/alynt-drime-backups-uploader/v1/status' ) );
		$this->assertFalse( $controller->validate_status_endpoint_arg( 'https://client.example.com/wp-json/not-the-fixed-route' ) );
	}

	/**
	 * Creates the controller.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository $repository Repository.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault|null          $vault Vault.
	 * @return Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller
	 */
	private function controller( $repository, $vault = null ) {
		return new Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller(
			$repository,
			new Alynt_Drime_Backups_Dashboard_Origin_Validator(),
			$vault instanceof Alynt_Drime_Backups_Dashboard_Credential_Vault ? $vault : new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) )
		);
	}

	/**
	 * Creates a pending site row.
	 *
	 * @param string $secret Plain pairing secret.
	 * @param string $expires_at Expiry.
	 * @return array<string,mixed>
	 */
	private function pending_site( $secret, $expires_at = '2099-01-01 00:15:00' ) {
		return array(
			'id'                  => 77,
			'public_id'           => '00000000-0000-4000-8000-000000000000',
			'expected_origin'     => 'https://client.example.com',
			'enrollment_status'   => 'pending',
			'pairing_secret_hash' => Alynt_Drime_Backups_Dashboard_Pairing_Tokens::hash_secret( $secret ),
			'pairing_expires_at'  => $expires_at,
		);
	}

	/**
	 * Creates an enrollment payload.
	 *
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function payload( array $overrides = array() ) {
		return array_merge(
			array(
				'protocol_version'      => 1,
				'enrollment_id'        => '00000000-0000-4000-8000-000000000000',
				'site_uuid'            => '11111111-1111-4111-8111-111111111111',
				'home_url'             => 'https://client.example.com',
				'status_endpoint'      => 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v1/status',
				'uploader_version'     => '0.5.3',
				'status_schema_version' => 1,
			),
			$overrides
		);
	}
}
