<?php
/**
 * Diagnostics history and support-summary tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests attention history, poll outcomes, and support summary redaction.
 */
class DiagnosticsHistoryAndSupportTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

	/**
	 * Attention-history diagnostics are aggregate-only and support safe.
	 *
	 * @return void
	 */
	public function test_attention_history_diagnostics_are_aggregate_only() {
		$result = $this->collect_diagnostics(
			array(
				$this->site( 1 ),
				$this->site( 2 ),
				$this->site(
					3,
					array(
						'archived_at' => '2026-09-29 10:00:00',
					)
				),
			),
			array(),
			array(
				1 => array(
					$this->snapshot_row( 'working', '2026-09-29 11:00:00' ),
					$this->snapshot_row( 'needs_attention', '2026-09-29 10:00:00' ),
					$this->snapshot_row( 'working', '2026-09-29 09:00:00' ),
				),
				2 => array(
					$this->snapshot_row( 'working', '2026-09-29 11:00:00' ),
					$this->snapshot_row( 'needs_attention', '2026-09-29 10:00:00' ),
					$this->snapshot_row( 'working', '2026-09-29 09:00:00' ),
					$this->snapshot_row( 'not_reporting', '2026-09-29 08:00:00' ),
					$this->snapshot_row( 'working', '2026-09-29 07:00:00' ),
				),
				3 => array(
					$this->snapshot_row( 'working', '2026-09-29 11:00:00' ),
					$this->snapshot_row( 'needs_attention', '2026-09-29 10:00:00' ),
				),
			)
		);

		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 2, $result['counts']['attention_history']['records_with_history'] );
		$this->assertSame( 2, $result['counts']['attention_history']['recently_recovered_records'] );
		$this->assertSame( 1, $result['counts']['attention_history']['repeated_attention_records'] );
		$this->assertSame( 3, $result['counts']['attention_history']['recent_attention_transitions'] );
		$this->assertSame( 'repeated_attention_seen', $result['summaries']['attention_history'] );
		$this->assertStringContainsString( 'attention_history', $encoded );
		$this->assertStringContainsString( 'recently_recovered_records', $encoded );
		$this->assertStringContainsString( 'repeated_attention_seen', $encoded );
		$this->assertStringContainsString( 'Retained snapshot history shows repeated transitions into attention states.', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'needs_attention -&gt; working', $encoded );
	}

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
