<?php
/**
 * Remote action repository maintenance tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-repository-test-bootstrap.php';

/**
 * Tests retention cleanup and stale reconciliation maintenance queries.
 */
class RemoteActionRepositoryMaintenanceTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_WPDB_Setup;

	/**
	 * Completed action retention cleanup is bounded and prepared.
	 *
	 * @return void
	 */
	public function test_cleanup_retention_deletes_completed_actions_in_bounded_batches() {
		$repository = $this->remote_action_repository();
		$cutoff     = gmdate( 'Y-m-d H:i:s', time() - ( 90 * 86400 ) );

		$this->assertSame( 2, $repository->cleanup_retention( 90, 500 ) );
		$this->assertStringContainsString( 'DELETE FROM wp_alynt_drime_dashboard_actions', $this->wpdb->last_query );
		$this->assertStringContainsString( 'completed_at IS NOT NULL', $this->wpdb->last_query );
		$this->assertStringContainsString( 'ORDER BY completed_at ASC, id ASC', $this->wpdb->last_query );
		$this->assertStringContainsString( 'LIMIT %d', $this->wpdb->last_query );
		$this->assertSame( array( $cutoff, 500 ), $this->wpdb->prepared_args );
	}

	/**
	 * Stale reconciliation is scoped to one dashboard site.
	 *
	 * @return void
	 */
	public function test_mark_unconfirmed_actions_stale_is_site_scoped() {
		$repository = $this->remote_action_repository();

		$this->assertSame( 2, $repository->mark_unconfirmed_actions_stale_for_site( 44, '2026-08-20 12:05:00' ) );
		$this->assertStringContainsString( "state = 'stale'", $this->wpdb->last_query );
		$this->assertStringContainsString( 'dashboard_site_id = %d', $this->wpdb->last_query );
		$this->assertStringContainsString( 'client_state IS NULL', $this->wpdb->last_query );
		$this->assertSame( 44, $this->wpdb->prepared_args[3] );
	}

}