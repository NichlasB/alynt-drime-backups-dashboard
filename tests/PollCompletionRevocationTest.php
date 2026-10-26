<?php
/**
 * Deterministic captured-row/revocation interleavings, not physical concurrency.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/poll-completion-fixtures.php';

class PollCompletionRevocationTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Poll_Completion_Fixtures;

	/** @dataProvider completions */
	public function test_revocation_wins_over_captured_completion( $failure ) {
		$this->assertTrue( $this->repository->revoke_local( 7 ) );
		$revoked = $this->database->row;
		$this->assertStale( $this->complete( $failure ) );
		$this->assertSame( $revoked, $this->database->row );
		$this->assertSame( 'revoked', $this->database->row['enrollment_status'] );
		$this->assertNull( $this->database->row['polling_secret_ciphertext'] );
	}

	/** @dataProvider changedContexts */
	public function test_changed_context_cannot_be_overwritten( $field, $value, $failure ) {
		$this->database->row[ $field ] = $value;
		$changed = $this->database->row;
		$this->assertStale( $this->complete( $failure ) );
		$this->assertSame( $changed, $this->database->row );
	}

	/** @dataProvider completions */
	public function test_missing_row_is_not_success( $failure ) {
		$this->database->row = null;
		$this->assertStale( $this->complete( $failure ) );
		$this->assertNull( $this->database->row );
	}

	public function test_malformed_current_state_is_not_a_pollable_state() {
		$this->database->row['enrollment_status'] = 'ACTIVE';
		$this->assertStale( $this->complete() );
	}

	public function test_another_row_with_identical_credentials_is_not_the_target() {
		$this->database->row['id'] = 8;
		$this->assertStale( $this->complete() );
		$this->assertSame( 8, $this->database->row['id'] );
		$this->assertArrayNotHasKey( 'last_seen_at', $this->database->row );
	}

	public function test_legacy_id_only_calls_have_no_write_authority() {
		$this->assertStale( $this->repository->mark_poll_success( 7, 'working' ) );
		$this->assertStale( $this->repository->mark_poll_failure( 7, 'failed' ) );
		$this->assertSame( array(), $this->database->queries );
	}

	/** @dataProvider invalidContexts */
	public function test_invalid_context_fails_before_sql( $context ) {
		$this->assertStale( $this->complete( false, $context ) );
		$this->assertStale( $this->complete( true, $context ) );
		$this->assertSame( array(), $this->database->queries );
	}

	public static function completions(): array {
		return array( 'success' => array( false ), 'failure' => array( true ) );
	}

	public static function changedContexts(): array {
		$cases = array();
		foreach ( array( false, true ) as $failure ) {
			foreach ( array( 'enrollment_status' => 'pending', 'public_id' => 'public-a', 'polling_key_id' => 'key-a', 'polling_secret_ciphertext' => 'cipher-a' ) as $field => $value ) {
				$cases[ $field . ( $failure ? '-failure' : '-success' ) ] = array( $field, $value, $failure );
			}
		}
		return $cases;
	}

	public static function invalidContexts(): array {
		$base = array( 'id' => 7, 'enrollment_status' => 'active', 'public_id' => 'Public-A', 'polling_key_id' => 'Key-A', 'polling_secret_ciphertext' => 'Cipher-A' );
		$cases = array( 'absent' => array( array() ) );
		foreach ( array( 'id' => array( 8, array(), true, '7.0' ), 'enrollment_status' => array( 'revoked', array() ), 'public_id' => array( '', null, array() ), 'polling_key_id' => array( array() ), 'polling_secret_ciphertext' => array( array() ) ) as $field => $values ) {
			foreach ( $values as $index => $value ) {
				$cases[ $field . '-' . $index ] = array( array_merge( $base, array( $field => $value ) ) );
			}
			$missing = $base;
			unset( $missing[ $field ] );
			$cases[ 'missing-' . $field ] = array( $missing );
		}
		return $cases;
	}
}
