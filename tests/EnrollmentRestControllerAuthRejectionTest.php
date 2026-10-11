<?php
/**
 * Enrollment REST controller authentication rejection tests.
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
 * Tests enrollment REST authentication and rate-limit rejection behavior.
 */
class EnrollmentRestControllerAuthRejectionTest extends TestCase {
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
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( $this->pairing_secret() ) );
		$controller = $this->controller( $repository );

		$result = $controller->handle_enrollment( $this->payload(), $this->bearer_secret( 'B' ), strtotime( '2099-01-01T00:00:00Z' ) );

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
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( $this->pairing_secret() ) );
		$controller = $this->controller( $repository );

		for ( $attempt = 0; $attempt < Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller::RATE_LIMIT_FAILURE_THRESHOLD; $attempt++ ) {
			$result = $controller->handle_enrollment( $this->payload(), $this->bearer_secret( 'B' ), strtotime( '2099-01-01T00:00:00Z' ) );

			$this->assertInstanceOf( WP_Error::class, $result );
			$this->assertSame( 'pairing_invalid', $result->get_error_code() );
		}

		$result = $controller->handle_enrollment( $this->payload(), $this->bearer_secret( 'C' ), strtotime( '2099-01-01T00:00:00Z' ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'rate_limited', $result->get_error_code() );
		$this->assertSame( 429, $result->get_error_data()['status'] );
		$this->assertSame( array(), $repository->stored );
	}
}
