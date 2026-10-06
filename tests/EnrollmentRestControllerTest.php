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
require_once dirname( __DIR__ ) . '/includes/traits/trait-site-repository-reads.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-site-repository-writes.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-site-repository-runtime-writes.php';
require_once dirname( __DIR__ ) . '/includes/class-site-repository.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-settings.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-reporting.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-enrollment-rest-responses.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-enrollment-rest-route-args.php';
require_once dirname( __DIR__ ) . '/includes/class-enrollment-rest-controller.php';
require_once __DIR__ . '/support/enrollment-rest-controller-test-harness.php';

/**
 * Tests enrollment REST controller behavior.
 */
class EnrollmentRestControllerTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller_Test_Fixtures;

	/**
	 * Resets test transients.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$GLOBALS['alynt_drime_backups_dashboard_test_transients'] = array();
	}

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
	 * Overlong uploader versions are bounded before fixed-width storage.
	 *
	 * @return void
	 */
	public function test_overlong_uploader_version_is_bounded_before_storage() {
		$secret     = str_repeat( 'A', 43 );
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( $secret ) );
		$controller = $this->controller( $repository );
		$result     = $controller->handle_enrollment(
			$this->payload(
				array(
					'uploader_version' => str_repeat( '1', 100 ),
				)
			),
			'Bearer ' . $secret,
			strtotime( '2099-01-01T00:00:00Z' )
		);

		$this->assertIsArray( $result );
		$this->assertSame( 64, strlen( $repository->stored['plugin_version'] ) );
	}

	/**
	 * Permission callback documents the public bearer-auth boundary.
	 *
	 * @return void
	 */
	public function test_permission_callback_requires_bearer_shape() {
		$controller = $this->controller( new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository() );

		$missing = $controller->permission_callback( $this->request_with_authorization( '' ) );

		$this->assertInstanceOf( WP_Error::class, $missing );
		$this->assertSame( 'auth_missing', $missing->get_error_code() );
		$this->assertSame( 401, $missing->get_error_data()['status'] );
		$this->assertTrue( $controller->permission_callback( $this->request_with_authorization( 'Bearer ' . str_repeat( 'A', 43 ) ) ) );
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

}
