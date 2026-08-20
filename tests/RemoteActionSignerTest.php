<?php
/**
 * Remote action signer tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

/**
 * Tests V2 remote-action signing helpers.
 */
class RemoteActionSignerTest extends TestCase {
	/**
	 * Canonical JSON is deterministic for equivalent payloads.
	 *
	 * @return void
	 */
	public function test_canonical_json_sorts_nested_keys() {
		$signer = new Alynt_Drime_Backups_Dashboard_Remote_Action_Signer();

		$first = $signer->canonical_json(
			array(
				'z' => 2,
				'a' => array(
					'b' => true,
					'a' => 'first',
				),
			)
		);

		$second = $signer->canonical_json(
			array(
				'a' => array(
					'a' => 'first',
					'b' => true,
				),
				'z' => 2,
			)
		);

		$this->assertSame( $first, $second );
		$this->assertSame( '{"a":{"a":"first","b":true},"z":2}', $first );
	}

	/**
	 * Sodium signatures round-trip against the deterministic signing input.
	 *
	 * @return void
	 */
	public function test_signatures_verify_round_trip_when_sodium_is_available() {
		$signer = new Alynt_Drime_Backups_Dashboard_Remote_Action_Signer();

		if ( ! $signer->is_supported() ) {
			$this->markTestSkipped( 'Sodium signing is not available in this PHP runtime.' );
		}

		$keys = $signer->create_key_pair();
		$body = $signer->canonical_json(
			array(
				'action_id'   => '11111111-1111-4111-8111-111111111111',
				'action_type' => 'scan_upload_now',
			)
		);

		$this->assertIsArray( $keys );
		$this->assertIsString( $body );

		$input     = $signer->signing_input(
			'post',
			'/wp-json/alynt-drime-backups-uploader/v2/action-intents',
			'https://Example.com/',
			$body,
			'2026-08-20T12:00:00+00:00'
		);
		$signature = $signer->sign( $keys['private_key'], $input );

		$this->assertIsString( $signature );
		$this->assertTrue( $signer->verify( $keys['public_key'], $input, $signature ) );
		$this->assertFalse( $signer->verify( $keys['public_key'], $input . '-changed', $signature ) );
	}

	/**
	 * Invalid encoded keys are refused.
	 *
	 * @return void
	 */
	public function test_invalid_private_key_returns_error() {
		$signer = new Alynt_Drime_Backups_Dashboard_Remote_Action_Signer();

		if ( ! $signer->is_supported() ) {
			$this->markTestSkipped( 'Sodium signing is not available in this PHP runtime.' );
		}

		$result = $signer->sign( 'not-valid-base64url!', 'input' );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'action_private_key_invalid', $result->get_error_code() );
	}
}
