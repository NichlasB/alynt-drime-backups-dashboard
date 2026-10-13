<?php
/**
 * Enrollment REST controller pairing-expiry tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/enrollment-rest-controller-bootstrap.php';

/**
 * Tests enrollment REST controller pairing-expiry behavior.
 */
class EnrollmentRestControllerPairingExpiryTest extends TestCase {
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
	 * Expired pairing token is rejected.
	 *
	 * @return void
	 */
	public function test_expired_pairing_is_rejected() {
		$secret     = $this->pairing_secret();
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( $secret, '2020-01-01 00:00:00' ) );
		$controller = $this->controller( $repository );

		$result = $controller->handle_enrollment( $this->payload(), 'Bearer ' . $secret, strtotime( '2099-01-01T00:00:00Z' ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'pairing_expired', $result->get_error_code() );
		$this->assertSame( array(), $repository->stored );
	}
}
