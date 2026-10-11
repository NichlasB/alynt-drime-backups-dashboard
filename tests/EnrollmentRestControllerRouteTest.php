<?php
/**
 * Enrollment REST controller route contract tests.
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
 * Tests enrollment REST route permission and argument contracts.
 */
class EnrollmentRestControllerRouteTest extends TestCase {
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
