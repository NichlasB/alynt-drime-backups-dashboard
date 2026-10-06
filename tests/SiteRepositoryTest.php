<?php
/**
 * Site repository tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/site-repository-test-harness.php';

/**
 * Tests dashboard site repository behavior.
 */
class SiteRepositoryTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Site_Repository_Test_Case;

	/**
	 * Active pending lookup is bounded to same-origin, pending, non-expired pairings.
	 *
	 * @return void
	 */
	public function test_active_pending_lookup_uses_origin_pending_status_and_expiry_window() {
		$this->wpdb->row = array(
			'id'              => 44,
			'expected_origin' => 'https://client.example.com',
		);

		$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$result     = $repository->get_active_pending_by_expected_origin(
			'https://client.example.com',
			'2099-01-01 00:05:00'
		);

		$this->assertSame( $this->wpdb->row, $result );
		$this->assertSame( ARRAY_A, $this->wpdb->last_output );
		$this->assertStringContainsString( 'FROM wp_alynt_drime_dashboard_sites', $this->wpdb->last_query );
		$this->assertStringContainsString( 'expected_origin = %s', $this->wpdb->last_query );
		$this->assertStringContainsString( 'enrollment_status = %s', $this->wpdb->last_query );
		$this->assertStringContainsString( 'pairing_expires_at > %s', $this->wpdb->last_query );
		$this->assertStringContainsString( 'LIMIT 1', $this->wpdb->last_query );
		$this->assertSame(
			array(
				'https://client.example.com',
				'pending',
				'2099-01-01 00:05:00',
			),
			$this->wpdb->prepared_args
		);
	}

	/**
	 * Active pending lookup falls back to current UTC time when no time is supplied.
	 *
	 * @return void
	 */
	public function test_active_pending_lookup_uses_current_time_when_not_supplied() {
		$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$result     = $repository->get_active_pending_by_expected_origin( 'https://client.example.com' );

		$this->assertNull( $result );
		$this->assertSame( '2099-01-01 00:00:00', $this->wpdb->prepared_args[2] );
	}

}
