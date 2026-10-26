<?php
/**
 * Isolated storage lifecycle tests, not database/core qualification.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/schema-lifecycle-process.php';

/** Tests actual storage methods with in-memory API outcomes. */
class StorageInstallFailureTest extends TestCase {
	/**
	 * Installation must not certify failed or incomplete schemas.
	 *
	 * @return void
	 */
	public function test_failures_preserve_retry_and_hide_internal_details() {
		foreach ( array( 'owned_alynt_drime_dashboard_sites', 'owned_alynt_drime_dashboard_snapshots', 'owned_alynt_drime_dashboard_actions', 'pending', 'pending2', 'pending3', 'metadata', 'metadata_stale', 'metadata_throw', 'probe_error', 'malformed', 'throw', 'write' ) as $scenario ) {
			$result = alynt_dashboard_schema_fixture( $scenario );
			$this->assertTrue( $result['error'], $scenario );
			$this->assertSame( '7', $result['state']['version'], $scenario );
			$this->assertStringNotContainsString( 'private SQL', $result['message'] );
		}
	}

	/**
	 * Real positive, retry and fast-path outcomes remain possible.
	 *
	 * @return void
	 */
	public function test_success_retry_reinstall_and_current_version() {
		foreach ( array( 'success', 'retry', 'reinstall', 'numeric_reinstall', 'corrupt', 'noop' ) as $scenario ) {
			$result = alynt_dashboard_schema_fixture( $scenario );
			$this->assertTrue( $result['success'], $scenario );
			$this->assertSame( '8', $result['state']['version'], $scenario );
			if ( 'noop' === $scenario ) {
				$this->assertSame( array(), $result['state']['delta'] );
				$this->assertSame( array(), $result['state']['writes'] );
			} elseif ( 'retry' === $scenario ) {
				$this->assertSame( array( 'error' => true, 'version' => '7' ), $result['first'] );
			} else {
				$this->assertSame( 3, $result['metadata_reads'] );
				$this->assertCount( 6, $result['state']['delta'] );
				$write = end( $result['state']['writes'] );
				$this->assertSame( array( false, 3, 3 ), array_slice( $write, 2 ), 'Completion follows all metadata/probes, with autoload disabled' );
			}
		}
	}

	/**
	 * Forced reinstallation cannot retain a misleading current marker on failure.
	 *
	 * @return void
	 */
	public function test_reinstall_invalidation_failure_stops_before_ddl() {
		$result = alynt_dashboard_schema_fixture( 'invalidate' );
		$this->assertTrue( $result['error'] );
		$this->assertSame( array(), $result['state']['delta'] );
		$result = alynt_dashboard_schema_fixture( 'reinstall_failed' );
		$this->assertTrue( $result['error'] );
		$this->assertSame( '', $result['state']['version'] );
	}

	/**
	 * Callers may not register normal runtime hooks or schedule after failure.
	 *
	 * @return void
	 */
	public function test_failed_bootstrap_and_activation_stop_normal_initialization() {
		$result = alynt_dashboard_schema_fixture( 'bootstrap' );
		$this->assertFalse( $result['initialized'] );
		$this->assertSame( array( 'admin_notices' ), $result['state']['hooks'] );
		$this->assertSame( '', $result['unauthorized_notice'] );
		$this->assertStringContainsString( 'notice-error', $result['notice'] );
		$this->assertStringContainsString( 'role="alert"', $result['notice'] );
		$this->assertStringNotContainsString( 'private SQL', $result['notice'] );
		$result = alynt_dashboard_schema_fixture( 'activation' );
		$this->assertTrue( $result['error'] );
		$this->assertSame( 0, $result['state']['scheduled'] );
	}

	/**
	 * Positive callers prove the owner is not simply always unavailable.
	 *
	 * @return void
	 */
	public function test_valid_storage_allows_bootstrap_and_activation() {
		$result = alynt_dashboard_schema_fixture( 'bootstrap_ok' );
		$this->assertTrue( $result['initialized'] );
		$this->assertContains( 'rest_api_init', $result['state']['hooks'] );
		$this->assertContains( 'alynt_drime_backups_dashboard_poll_sites', $result['state']['hooks'] );
		$result = alynt_dashboard_schema_fixture( 'activation_ok' );
		$this->assertFalse( $result['error'] );
		$this->assertSame( 2, $result['state']['scheduled'] );
	}

	/**
	 * The three actual DDL strings stay byte-identical to the approved baseline.
	 *
	 * @return void
	 */
	public function test_existing_schema_definitions_are_unchanged() {
		$result = alynt_dashboard_schema_fixture( 'success' );
		$this->assertSame( array(
			'68ad97607cc8da558a7bdba8de54b0ae2059c107739881ca0a4b29199a47574d',
			'37f17065c7d45f0c1ec55806bb1c351a5c9bfb456e1a0b969bf7ca4e05dd1448',
			'5e056fac11470a5b275e31654eab471642a9380e5e30ba8fd3d163ce8ebbe212',
		), array_column( array_slice( $result['state']['delta'], 0, 3 ), 'hash' ) );
	}
}
