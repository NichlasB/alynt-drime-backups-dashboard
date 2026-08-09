<?php
/**
 * Enrollment manager tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-origin-validator.php';
require_once dirname( __DIR__ ) . '/includes/class-pairing-tokens.php';
require_once dirname( __DIR__ ) . '/includes/class-site-repository.php';
require_once dirname( __DIR__ ) . '/includes/class-enrollment-manager.php';

/**
 * Fake repository for enrollment manager tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Last inserted data.
	 *
	 * @var array<string,mixed>
	 */
	public $last_insert = array();

	/**
	 * Creates a pending fake row.
	 *
	 * @param array $data Site data.
	 * @return int
	 */
	public function create_pending( array $data ) {
		$this->last_insert = $data;

		return 123;
	}
}

/**
 * Tests pending enrollment creation.
 */
class EnrollmentManagerTest extends TestCase {
	/**
	 * Pending enrollment stores only token metadata and verifier.
	 *
	 * @return void
	 */
	public function test_create_pending_site_returns_display_token_and_stores_only_verifier() {
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Site_Repository();
		$manager    = new Alynt_Drime_Backups_Dashboard_Enrollment_Manager(
			$repository,
			new Alynt_Drime_Backups_Dashboard_Origin_Validator()
		);

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
		$manager    = new Alynt_Drime_Backups_Dashboard_Enrollment_Manager(
			$repository,
			new Alynt_Drime_Backups_Dashboard_Origin_Validator()
		);

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
	 * Extracts the plaintext secret from a display token for test assertions.
	 *
	 * @param string $token Pairing token.
	 * @return string
	 */
	private function secret_from_token( $token ) {
		$encoded = substr( $token, strlen( Alynt_Drime_Backups_Dashboard_Pairing_Tokens::TOKEN_PREFIX ) );
		$padded  = $encoded . str_repeat( '=', ( 4 - strlen( $encoded ) % 4 ) % 4 );
		$json    = base64_decode( strtr( $padded, '-_', '+/' ) );
		$payload = json_decode( $json, true );

		return is_array( $payload ) && isset( $payload['secret'] ) ? (string) $payload['secret'] : '';
	}
}
