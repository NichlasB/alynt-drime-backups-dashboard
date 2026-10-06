<?php
/**
 * Enrollment REST controller rejection tests.
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
 * Tests enrollment REST controller rejection behavior.
 */
class EnrollmentRestControllerRejectionTest extends TestCase {
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
	 * Repeated failed attempts for the same enrollment are rate limited.
	 *
	 * @return void
	 */
	public function test_repeated_invalid_pairing_attempts_are_rate_limited() {
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( str_repeat( 'A', 43 ) ) );
		$controller = $this->controller( $repository );

		for ( $attempt = 0; $attempt < Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller::RATE_LIMIT_FAILURE_THRESHOLD; $attempt++ ) {
			$result = $controller->handle_enrollment( $this->payload(), 'Bearer ' . str_repeat( 'B', 43 ), strtotime( '2099-01-01T00:00:00Z' ) );

			$this->assertInstanceOf( WP_Error::class, $result );
			$this->assertSame( 'pairing_invalid', $result->get_error_code() );
		}

		$result = $controller->handle_enrollment( $this->payload(), 'Bearer ' . str_repeat( 'C', 43 ), strtotime( '2099-01-01T00:00:00Z' ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'rate_limited', $result->get_error_code() );
		$this->assertSame( 429, $result->get_error_data()['status'] );
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
}
