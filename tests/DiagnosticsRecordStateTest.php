<?php
/**
 * Diagnostics record-state tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests support-safe aggregate diagnostics for non-polling records.
 */
class DiagnosticsRecordStateTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

	/**
	 * Diagnostics expose support-safe aggregate record states.
	 *
	 * @return void
	 */
	public function test_record_state_diagnostics_explain_non_polling_records() {
		$result = $this->collect_diagnostics(
			array(
				$this->site(
					1,
					array(
						'enrollment_status' => 'pending',
						'overall_status'    => 'pending',
						'polling_key_id'    => '',
					)
				),
				$this->site(
					2,
					array(
						'enrollment_status' => 'revoked',
						'overall_status'    => 'pending',
						'polling_key_id'    => '',
					)
				),
				$this->site(
					3,
					array(
						'enrollment_status' => '',
						'overall_status'    => 'pending',
						'polling_key_id'    => '',
					)
				),
				$this->site(
					4,
					array(
						'enrollment_status' => 'revoked',
						'overall_status'    => 'pending',
						'polling_key_id'    => '',
						'archived_at'       => '2026-09-19 18:30:00',
					)
				),
			)
		);

		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 4, $result['counts']['total_sites'] );
		$this->assertSame( 0, $result['counts']['polling_ready'] );
		$this->assertSame( 4, $result['counts']['not_polling'] );
		$this->assertSame( 1, $result['counts']['record_states']['pending'] );
		$this->assertSame( 1, $result['counts']['record_states']['revoked'] );
		$this->assertSame( 1, $result['counts']['record_states']['archived'] );
		$this->assertSame( 1, $result['counts']['record_states']['unknown'] );
		$this->assertStringContainsString( 'record_states', $encoded );
		$this->assertStringContainsString( 'not_polling', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
	}
}
