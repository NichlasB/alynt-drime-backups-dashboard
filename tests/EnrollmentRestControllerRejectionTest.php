<?php
/**
 * Enrollment REST controller rejection tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/enrollment-rest-controller-bootstrap.php';

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
	 * Origin mismatch is rejected after authentication.
	 *
	 * @return void
	 */
	public function test_origin_mismatch_is_rejected() {
		$secret     = $this->pairing_secret();
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
		$secret     = $this->pairing_secret();
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
		$secret     = $this->pairing_secret();
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository( $this->pending_site( $secret ) );
		$controller = $this->controller( $repository );

		$result = $controller->handle_enrollment( $this->payload( array( 'status_schema_version' => 2 ) ), 'Bearer ' . $secret, strtotime( '2099-01-01T00:00:00Z' ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'schema_unsupported', $result->get_error_code() );
		$this->assertSame( array(), $repository->stored );
	}

}
