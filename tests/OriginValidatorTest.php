<?php
/**
 * Origin validation tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-origin-validator.php';

/**
 * Tests public HTTPS origin validation.
 */
class OriginValidatorTest extends TestCase {
	/**
	 * Public HTTPS origins normalize to scheme and host only.
	 *
	 * @return void
	 */
	public function test_public_https_origin_normalizes() {
		$validator = new Alynt_Drime_Backups_Dashboard_Origin_Validator();

		$this->assertSame( 'https://example.com', $validator->normalize_public_https_origin( 'https://Example.COM/' ) );
		$this->assertSame( 'https://example.com/wp-json/alynt-drime-backups-uploader/v1/status', $validator->status_endpoint_for_origin( 'https://Example.COM/' ) );
	}

	/**
	 * Unsafe origins fail closed.
	 *
	 * @return void
	 */
	public function test_unsafe_origins_are_rejected() {
		$validator = new Alynt_Drime_Backups_Dashboard_Origin_Validator();
		$origins   = array(
			'http://example.com',
			'https://127.0.0.1',
			'https://localhost',
			'https://client.local',
			'https://example.com:8443',
			'https://example.com/path',
			'https://user@example.com',
			'https://example.com?token=secret',
			'https://' . str_repeat( 'a', 64 ) . '.example.com',
			'https://' . str_repeat( 'a', 250 ) . '.com',
		);

		foreach ( $origins as $origin ) {
			$this->assertSame( '', $validator->normalize_public_https_origin( $origin ), $origin );
		}
	}

	/**
	 * DNS/IP validation fails closed when any resolved address is private.
	 *
	 * @return void
	 */
	public function test_resolved_origin_rejects_private_addresses() {
		$validator = new Alynt_Drime_Backups_Dashboard_Origin_Validator();

		$this->assertFalse(
			$validator->resolved_origin_is_public(
				'https://client.example.com',
				function () {
					return array( '10.0.0.5' );
				}
			)
		);

		$this->assertFalse(
			$validator->resolved_origin_is_public(
				'https://client.example.com',
				function () {
					return array(
						array(
							'ipv6' => '::1',
						),
					);
				}
			)
		);
	}

	/**
	 * DNS/IP validation accepts public resolved addresses.
	 *
	 * @return void
	 */
	public function test_resolved_origin_accepts_public_addresses() {
		$validator = new Alynt_Drime_Backups_Dashboard_Origin_Validator();

		$this->assertTrue(
			$validator->resolved_origin_is_public(
				'https://client.example.com',
				function () {
					return array(
						array(
							'ip' => '93.184.216.34',
						),
						array(
							'ipv6' => '2606:2800:220:1:248:1893:25c8:1946',
						),
					);
				}
			)
		);
	}
}
