<?php
/**
 * Snapshot display availability regressions.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/admin/traits/trait-admin-page-basic-detail-helpers.php';
require_once dirname( __DIR__ ) . '/includes/admin/traits/trait-admin-page-sites-table.php';
require_once __DIR__ . '/support/admin-page-sites-list-wordpress-shims.php';

/** Tests the real payload decoder and both summary consumers. */
class AdminPageSnapshotAvailabilityTest extends TestCase {
	/**
	 * Unreadable payloads must not masquerade as a healthy zero-count report.
	 *
	 * @return void
	 */
	public function test_unreadable_payloads_are_unavailable_in_both_views() {
		$harness = new Alynt_Drime_Backups_Dashboard_Snapshot_Availability_Harness();
		foreach ( array( '{broken', 'null', '"scalar"', '[]', '{}' ) as $json ) {
			foreach ( array( 'detail', 'table' ) as $view ) {
				$html = $harness->html( $view, array( 'payload_json' => $json ) );
				$this->assertStringContainsString( 'Stored snapshot summary is unavailable', $html );
				$this->assertStringNotContainsString( '<strong>0</strong>', $html );
				$this->assertStringNotContainsString( '<dt>Active upload</dt>', $html );
			}
		}
	}

	/**
	 * Genuine zero counts remain visible for raw and pre-decoded reports.
	 *
	 * @return void
	 */
	public function test_valid_zero_report_remains_visible() {
		$harness = new Alynt_Drime_Backups_Dashboard_Snapshot_Availability_Harness();
		foreach ( array( array( 'payload_json' => '{"queue_count":0}' ), array( 'decoded_payload' => array( 'queue_count' => 0 ) ) ) as $snapshot ) {
			$this->assertStringContainsString( '<dt>Queue</dt><dd>0</dd>', $harness->html( 'detail', $snapshot ) );
			$this->assertStringContainsString( 'Queue <strong>0</strong>', $harness->html( 'table', $snapshot ) );
		}
	}
}

/** Harness uses actual consumers/decoder, stubbing unrelated shell helpers only. */
class Alynt_Drime_Backups_Dashboard_Snapshot_Availability_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_Table;

	const MENU_SLUG = 'test';

	/**
	 * Renders one summary without repositories or actions.
	 *
	 * @param string $view View.
	 * @param array  $snapshot Snapshot.
	 * @return string
	 */
	public function html( $view, array $snapshot ) {
		ob_start();
		if ( 'detail' === $view ) {
			$this->render_latest_snapshot_summary( $snapshot );
		} else {
			$this->render_sites_table( array( array( 'id' => 1 ) ), array( 1 => $snapshot ), array( 1 => array() ) );
		}
		return (string) ob_get_clean();
	}

	/**
	 * Excludes unrelated labels, hints and action forms from this render proof.
	 *
	 * @param string $name Helper.
	 * @param array  $arguments Arguments.
	 * @return string
	 */
	public function __call( $name, array $arguments ) {
		return '';
	}
}
