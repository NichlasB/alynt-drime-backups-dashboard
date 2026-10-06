<?php
/**
 * Site repository enrollment write tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/site-repository-test-harness.php';

/**
 * Tests dashboard site repository enrollment write behavior.
 */
class SiteRepositoryEnrollmentWritesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Site_Repository_Test_Case;

	/**
	 * Revocation fails when the target row no longer exists or was already changed.
	 *
	 * @return void
	 */
	public function test_revoke_local_requires_changed_row() {
		$this->wpdb->update_result = 0;

		$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$result     = $repository->revoke_local( 123 );

		$this->assertFalse( $result );
		$this->assertSame( 'wp_alynt_drime_dashboard_sites', $this->wpdb->updated_table );
		$this->assertSame( array( 'id' => 123 ), $this->wpdb->updated_where );
		$this->assertSame( 'revoked', $this->wpdb->updated_data['enrollment_status'] );
	}

	/**
	 * Enrollment completion fails when another request already consumed the pending row.
	 *
	 * @return void
	 */
	public function test_complete_enrollment_pending_first_poll_requires_changed_pending_row() {
		$this->wpdb->update_result = 0;

		$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$result     = $repository->complete_enrollment_pending_first_poll(
			123,
			array(
				'site_uuid'                 => 'site-uuid',
				'polling_key_id'            => 'key-id',
				'polling_secret_ciphertext' => 'ciphertext',
				'plugin_version'            => '0.1.0',
				'payload_schema_version'    => 1,
			)
		);

		$this->assertFalse( $result );
		$this->assertSame( 'wp_alynt_drime_dashboard_sites', $this->wpdb->updated_table );
		$this->assertSame(
			array(
				'id'                => 123,
				'enrollment_status' => 'pending',
			),
			$this->wpdb->updated_where
		);
		$this->assertSame( 'awaiting_first_poll', $this->wpdb->updated_data['enrollment_status'] );
	}
}
