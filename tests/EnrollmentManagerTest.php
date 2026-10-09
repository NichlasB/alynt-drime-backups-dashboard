<?php
/**
 * Enrollment manager tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/enrollment-manager-test-harness.php';

/**
 * Tests pending enrollment creation.
 */
class EnrollmentManagerTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Enrollment_Manager_Test_Fixtures;

	/**
	 * Pending enrollment stores only token metadata and verifier.
	 *
	 * @return void
	 */
	public function test_create_pending_site_returns_display_token_and_stores_only_verifier() {
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Site_Repository();
		$manager    = $this->manager( $repository );

		$result = $manager->create_pending_site(
			array(
				'site_label'      => 'Client Site',
				'expected_origin' => 'https://Client.Example.com/',
				'environment'     => 'staging',
			),
			'https://control.sitesmanage.com/',
			strtotime( '2099-01-01T00:00:00Z' )
		);

		$this->assertIsArray( $result );
		$this->assertSame( 123, $result['site_id'] );
		$this->assertStringStartsWith( 'adb1.', $result['pairing_token'] );
		$this->assertSame( 'https://client.example.com', $repository->last_insert['expected_origin'] );
		$this->assertNull( $repository->last_insert['site_uuid'] );
		$this->assertSame( 'staging', $repository->last_insert['environment'] );
		$this->assertSame( 'pending', $repository->last_insert['enrollment_status'] );
		$this->assertSame( '2099-01-01 00:15:00', $repository->last_insert['pairing_expires_at'] );
		$this->assertSame( '2099-01-01T00:15:00+00:00', $result['pairing_expires_at'] );
		$this->assertArrayHasKey( 'pairing_secret_hash', $repository->last_insert );
		$this->assertArrayNotHasKey( 'pairing_token', $repository->last_insert );
		$this->assertStringNotContainsString( $this->secret_from_token( $result['pairing_token'] ), wp_json_encode( $repository->last_insert ) );
	}

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

	/**
	 * Existing non-expired pending pairings prevent duplicate display-once tokens.
	 *
	 * @return void
	 */
	public function test_create_pending_site_rejects_duplicate_active_pending_origin() {
		$repository                 = new Alynt_Drime_Backups_Dashboard_Test_Site_Repository();
		$repository->active_pending = array(
			'id'              => 44,
			'expected_origin' => 'https://client.example.com',
		);
		$manager                    = $this->manager( $repository );

		$result = $manager->create_pending_site(
			array(
				'site_label'      => 'Client Site',
				'expected_origin' => 'https://client.example.com',
			),
			'https://control.sitesmanage.com/',
			strtotime( '2099-01-01T00:00:00Z' )
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'pending_site_exists', $result->get_error_code() );
		$this->assertSame( array(), $repository->last_insert );
	}

	/**
	 * Storage failure does not return a display-once token.
	 *
	 * @return void
	 */
	public function test_create_pending_site_returns_error_when_storage_fails() {
		$repository                = new Alynt_Drime_Backups_Dashboard_Test_Site_Repository();
		$repository->create_result = new WP_Error( 'site_create_failed', 'Could not store site.' );
		$manager                   = $this->manager( $repository );

		$result = $manager->create_pending_site(
			array(
				'site_label'      => 'Client Site',
				'expected_origin' => 'https://client.example.com',
			),
			'https://control.sitesmanage.com/',
			strtotime( '2099-01-01T00:00:00Z' )
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_create_failed', $result->get_error_code() );
	}

}
