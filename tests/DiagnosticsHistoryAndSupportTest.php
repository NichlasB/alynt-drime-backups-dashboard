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

}
