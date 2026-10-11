<?php
/**
 * Enrollment manager validation tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/enrollment-manager-test-harness.php';

/**
 * Tests pending enrollment input validation.
 */
class EnrollmentManagerValidationTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Enrollment_Manager_Test_Fixtures;

	/**
	 * Unsafe client origins are rejected before storage.
	 *
	 * @return void
	 */
	public function test_create_pending_site_rejects_unsafe_client_origin() {
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Site_Repository();
		$manager    = $this->manager( $repository );

		$result = $manager->create_pending_site(
			array(
				'site_label'      => 'Client Site',
				'expected_origin' => 'http://127.0.0.1',
			),
			'https://control.sitesmanage.com/',
			strtotime( '2099-01-01T00:00:00Z' )
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'expected_origin_invalid', $result->get_error_code() );
		$this->assertSame( array(), $repository->last_insert );
	}

	/**
	 * Overlong labels are rejected before database storage.
	 *
	 * @return void
	 */
	public function test_create_pending_site_rejects_overlong_label() {
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Site_Repository();
		$manager    = $this->manager( $repository );

		$result = $manager->create_pending_site(
			array(
				'site_label'      => str_repeat( 'A', 192 ),
				'expected_origin' => 'https://client.example.com',
			),
			'https://control.sitesmanage.com/',
			strtotime( '2099-01-01T00:00:00Z' )
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_label_too_long', $result->get_error_code() );
		$this->assertSame( array(), $repository->last_insert );
	}
}
