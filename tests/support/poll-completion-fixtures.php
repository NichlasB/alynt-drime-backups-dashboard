<?php
/**
 * Real repository methods with a deterministic SQL-expression model, not a DB.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/poller-test-harness.php';
require_once __DIR__ . '/poll-completion-wpdb.php';

trait Alynt_Drime_Backups_Dashboard_Test_Poll_Completion_Fixtures {

	private $database;
	private $previous_database;
	private $repository;
	private $captured;

	protected function setUp(): void {
		$this->previous_database = $GLOBALS['wpdb'] ?? null;
		$this->captured = array(
			'id' => 7,
			'enrollment_status' => 'active',
			'public_id' => 'Public-A',
			'polling_key_id' => 'Key-A',
			'polling_secret_ciphertext' => 'Cipher-A',
		);
		$this->database = new Alynt_Drime_Backups_Dashboard_Test_Poll_Completion_WPDB();
		$this->database->row = $this->captured;
		$GLOBALS['wpdb'] = $this->database;
		$this->repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
	}

	protected function tearDown(): void {
		$GLOBALS['wpdb'] = $this->previous_database;
	}

	private function complete( $failure = false, $context = null ) {
		$context = null === $context ? $this->captured : $context;
		return $failure
			? $this->repository->mark_poll_failure( 7, 'transport_failed', 'Unavailable', '', 2, $context )
			: $this->repository->mark_poll_success( 7, 'working', '1.2.3', '', $context );
	}

	private function assertStale( $result ) {
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'poll_completion_stale', $result->get_error_code() );
		$this->assertNull( $result->get_error_data() );
	}
}
