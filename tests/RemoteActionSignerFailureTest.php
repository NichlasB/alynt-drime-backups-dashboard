<?php
/**
 * Remote action signer failure-path tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

/**
 * Tests V2 remote-action signing fail-closed behavior.
 */
class RemoteActionSignerFailureTest extends TestCase {
	/**
	 * Unsupported runtimes fail closed without generating or accepting action signatures.
	 *
	 * @return void
	 */
	public function test_unsupported_runtime_returns_errors_and_refuses_verification() {
		$signer = new class() extends Alynt_Drime_Backups_Dashboard_Remote_Action_Signer {
			/**
			 * Forces the unsupported branch for deterministic testing.
			 *
			 * @return bool
			 */
			public function is_supported() {
				return false;
			}
		};

		$key_pair = $signer->create_key_pair();
		$signed   = $signer->sign( 'private-key', 'input' );

		$this->assertInstanceOf( WP_Error::class, $key_pair );
		$this->assertSame( 'action_signing_unavailable', $key_pair->get_error_code() );
		$this->assertInstanceOf( WP_Error::class, $signed );
		$this->assertSame( 'action_signing_unavailable', $signed->get_error_code() );
		$this->assertFalse( $signer->verify( 'public-key', 'input', 'signature' ) );
	}

	/**
	 * Malformed signature material is refused before any verification can succeed.
	 *
	 * @return void
	 */
	public function test_malformed_signature_material_never_verifies() {
		$signer = new class() extends Alynt_Drime_Backups_Dashboard_Remote_Action_Signer {
			/**
			 * Forces the validation branch without requiring a sodium-enabled runtime.
			 *
			 * @return bool
			 */
			public function is_supported() {
				return true;
			}
		};

		$this->assertFalse( $signer->verify( 'not valid!', 'input', 'signature' ) );
		$this->assertFalse( $signer->verify( 'public-key', 'input', 'not valid!' ) );
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
