<?php
/**
 * Origin DNS/IP resolution validation tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-origin-validator.php';

/**
 * Tests DNS/IP validation for public HTTPS origins.
 */
class OriginValidatorResolutionTest extends TestCase {
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
