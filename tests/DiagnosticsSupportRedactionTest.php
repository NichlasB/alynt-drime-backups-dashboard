<?php
/**
 * Diagnostics support redaction tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests recent diagnostics and support-summary redaction.
 */
class DiagnosticsSupportRedactionTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

	/**
	 * Recent diagnostics omit stored credential fields.
	 *
	 * @return void
	 */
	public function test_recent_poll_outcomes_are_redacted() {
		$result = $this->collect_diagnostics(
			array(
				$this->site(
					1,
					array(
						'last_poll_attempt_at'      => '2026-08-10 08:00:00',
						'last_seen_at'              => '2026-08-10 08:00:00',
						'polling_key_id'            => 'pk_example_0000000000000000',
						'polling_secret_ciphertext' => 'adbv1.secret-ciphertext',
						'pairing_secret_hash'       => str_repeat( 'a', 64 ),
						'latest_payload_json'       => '{"unsafe":"raw"}',
					)
				),
			)
		);

		$encoded = wp_json_encode( $result['recent'] );

		$this->assertNotFalse( $encoded );
		$this->assertStringNotContainsString( 'polling_secret_ciphertext', $encoded );
		$this->assertStringNotContainsString( 'pairing_secret_hash', $encoded );
		$this->assertStringNotContainsString( 'latest_payload_json', $encoded );
		$this->assertStringNotContainsString( 'secret-ciphertext', $encoded );
		$this->assertStringContainsString( 'last_poll_attempt_at', $encoded );
	}

	/**
	 * Support-copy summary omits client-identifying and secret-bearing fields.
	 *
	 * @return void
	 */
	public function test_support_summary_is_support_safe() {
		$result = $this->collect_diagnostics(
			array(
				$this->site(
					9,
					array(
						'site_label'                => 'Very Private Client',
						'expected_origin'           => 'https://private-client.example.com',
						'last_poll_attempt_at'      => '2026-08-10 08:00:00',
						'last_seen_at'              => '2026-08-10 08:00:00',
						'next_poll_at'              => '2026-08-10 08:15:00',
						'polling_key_id'            => 'pk_example_private',
						'polling_secret_ciphertext' => 'adbv1.private-ciphertext',
						'pairing_secret_hash'       => str_repeat( 'b', 64 ),
						'latest_payload_json'       => '{"raw":"payload"}',
						'last_error_code'           => 'transport_failed',
						'last_error_summary'        => 'Sanitized failure summary.',
					)
				),
			)
		);

		$encoded = wp_json_encode( $result['support'] );

		$this->assertNotFalse( $encoded );
		$this->assertStringContainsString( 'recent_safe', $encoded );
		$this->assertStringContainsString( 'transport_failed', $encoded );
		$this->assertStringNotContainsString( 'Very Private Client', $encoded );
		$this->assertStringNotContainsString( 'private-client.example.com', $encoded );
		$this->assertStringNotContainsString( 'polling_key_id', $encoded );
		$this->assertStringNotContainsString( 'polling_secret_ciphertext', $encoded );
		$this->assertStringNotContainsString( 'pairing_secret_hash', $encoded );
		$this->assertStringNotContainsString( 'latest_payload_json', $encoded );
		$this->assertStringNotContainsString( 'private-ciphertext', $encoded );
		$this->assertStringNotContainsString( 'Sanitized failure summary.', $encoded );
	}
}
