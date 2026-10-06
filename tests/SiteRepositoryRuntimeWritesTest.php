<?php
/**
 * Site repository runtime write tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/site-repository-test-harness.php';

/**
 * Tests dashboard site repository runtime write behavior.
 */
class SiteRepositoryRuntimeWritesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Site_Repository_Test_Case;

	/**
	 * Pause polling stores a local pause timestamp and clears the next poll.
	 *
	 * @return void
	 */
	public function test_pause_polling_updates_local_pause_state() {
		$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$result     = $repository->pause_polling( 123 );

		$this->assertTrue( $result );
		$this->assertSame( 'wp_alynt_drime_dashboard_sites', $this->wpdb->updated_table );
		$this->assertSame( array( 'id' => 123 ), $this->wpdb->updated_where );
		$this->assertSame( '2099-01-01 00:00:00', $this->wpdb->updated_data['paused_at'] );
		$this->assertNull( $this->wpdb->updated_data['next_poll_at'] );
	}

	/**
	 * Resume polling clears the pause timestamp and makes the next poll due.
	 *
	 * @return void
	 */
	public function test_resume_polling_clears_pause_state_and_schedules_due_poll() {
		$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$result     = $repository->resume_polling( 123 );

		$this->assertTrue( $result );
		$this->assertSame( 'wp_alynt_drime_dashboard_sites', $this->wpdb->updated_table );
		$this->assertSame( array( 'id' => 123 ), $this->wpdb->updated_where );
		$this->assertNull( $this->wpdb->updated_data['paused_at'] );
		$this->assertSame( '2099-01-01 00:00:00', $this->wpdb->updated_data['next_poll_at'] );
	}
}
