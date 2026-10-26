<?php
/**
 * Positive, unchanged-row and storage-error completion contracts.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/poll-completion-fixtures.php';

class PollCompletionStorageTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Poll_Completion_Fixtures;

	/** @dataProvider completions */
	public function test_valid_completion_and_verified_unchanged_row( $failure ) {
		$this->assertTrue( $this->complete( $failure ) );
		$this->assertCount( 1, $this->database->queries );
		$this->assertSame( $failure ? 'needs_attention' : 'working', $this->database->row['overall_status'] );
		$this->assertTrue( $this->complete( $failure ) );
		$this->assertCount( 3, $this->database->queries );
		$this->assertSame( 'Cipher-A', $this->database->row['polling_secret_ciphertext'] );
	}

	public function test_first_poll_can_promote_same_generation_and_repeat() {
		$this->captured['enrollment_status'] = 'awaiting_first_poll';
		$this->database->row = $this->captured;
		$this->assertTrue( $this->complete() );
		$this->assertSame( 'active', $this->database->row['enrollment_status'] );
		$this->assertTrue( $this->complete() );
	}

	public function test_missing_credentials_can_record_auth_failure_but_not_success() {
		$this->captured['polling_key_id'] = null;
		$this->captured['polling_secret_ciphertext'] = null;
		$this->database->row = $this->captured;
		$this->assertStale( $this->complete() );
		$this->assertSame( array(), $this->database->queries );
		$this->assertTrue( $this->complete( true ) );
		$this->assertTrue( $this->repository->revoke_local( 7 ) );
		$this->assertStale( $this->complete( true ) );
	}

	/** @dataProvider completions */
	public function test_write_and_unchanged_row_read_errors_fail( $failure ) {
		$this->database->fail_write = true;
		$this->assertFalse( $this->complete( $failure ) );
		$this->database->fail_write = false;
		$this->assertTrue( $this->complete( $failure ) );
		$this->database->fail_read = true;
		$this->assertFalse( $this->complete( $failure ) );
	}

	/** @dataProvider completions */
	public function test_revocation_between_zero_write_and_readback_is_not_success( $failure ) {
		$this->assertTrue( $this->complete( $failure ) );
		$this->database->before_read = function () {
			$this->repository->revoke_local( 7 );
		};
		$this->assertStale( $this->complete( $failure ) );
	}

	public function test_unchanged_row_readback_must_match_the_complete_intended_state() {
		$this->assertTrue( $this->complete() );
		$this->database->before_read = function ( $database ) {
			$database->row['plugin_version'] = 'different';
		};
		$this->assertStale( $this->complete() );
	}

	public static function completions(): array {
		return array( 'success' => array( false ), 'failure' => array( true ) );
	}
}
