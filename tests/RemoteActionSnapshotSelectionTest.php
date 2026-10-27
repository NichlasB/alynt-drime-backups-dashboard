<?php
/**
 * Capability admission must use the highest-ID snapshot for the selected site.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-dispatcher-test-bootstrap.php';
require_once __DIR__ . '/support/snapshot-selection-wpdb-double.php';

/**
 * Real dispatcher/repository with isolated SQL, crypto, DNS and HTTP doubles.
 */
class RemoteActionSnapshotSelectionTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures;

	/**
	 * Old enabled reports cannot override a later disabled/rotated report.
	 *
	 * @dataProvider capability_changes
	 * @param array  $new_capability Newer report's overrides.
	 * @param bool   $old_enabled Old report enablement.
	 * @param string $expected Expected error, or accepted.
	 * @param string|null $payload_json Optional corrupt newest payload.
	 * @return void
	 */
	public function test_dispatcher_consumes_current_identity_without_weakening_admission( array $new_capability, $old_enabled, $expected, $payload_json = null ) {
		global $wpdb;
		$previous = $wpdb;
		$db       = new Alynt_Drime_Backups_Dashboard_Test_Snapshot_Selection_WPDB();
		$db->site = $this->site_row();
		$db->rows = array(
			array_merge( $this->snapshot_row( array( 'enabled' => $old_enabled ) ), array( 'id' => 100, 'dashboard_site_id' => 9, 'observed_at' => '2026-08-02 00:00:00' ) ),
			array_merge( $this->snapshot_row( $new_capability ), array( 'id' => 101, 'dashboard_site_id' => 9, 'observed_at' => '2026-08-01 00:00:00' ) ),
			array_merge( $this->snapshot_row(), array( 'id' => 999, 'dashboard_site_id' => 7, 'observed_at' => '2026-08-03 00:00:00' ) ),
		);
		if ( null !== $payload_json ) {
			$db->rows[1]['payload_json'] = $payload_json;
		}
		$wpdb     = $db;
		$requests = 0;
		$http     = function ( $url, $args ) use ( &$requests ) {
			unset( $url );
			++$requests;
			$body = json_decode( $args['body'], true );
			return array(
				'response' => array( 'code' => 202 ),
				'body'     => wp_json_encode( array( 'protocol_version' => 2, 'action_id' => $body['action_id'], 'state' => 'accepted', 'code' => 'action_accepted', 'summary' => 'Isolated response.', 'retry_after' => 0 ) ),
			);
		};
		try {
			$result = $this->dispatcher( $http )->request_scan_upload_now( 9, 7 );
			if ( 'accepted' === $expected ) {
				$this->assertIsArray( $result );
				$this->assertSame( 'accepted', $result['remote_state'] );
				$this->assertSame( 1, $requests );
				$this->assertSame( 9, $db->inserted_data['dashboard_site_id'] );
			} else {
				$this->assertInstanceOf( WP_Error::class, $result );
				$this->assertSame( $expected, $result->get_error_code() );
				$this->assertSame( 0, $requests );
				$this->assertSame( array(), $db->inserted_data );
			}
			$this->assertStringContainsString( 'WHERE dashboard_site_id = %d ORDER BY id DESC LIMIT 1', $db->last_query );
		} finally {
			$wpdb = $previous;
		}
	}

	/**
	 * @return array<string,array<int,mixed>> Report transitions.
	 */
	public function capability_changes() {
		return array(
			'disabled' => array( array( 'enabled' => false ), true, 'remote_action_capability_missing' ),
			'rotated'  => array( array( 'key_id' => 'different-key' ), true, 'remote_action_key_mismatch' ),
			'enabled'  => array( array(), false, 'accepted' ),
			'no actions' => array( array( 'allowed_actions' => array() ), true, 'remote_action_capability_missing' ),
			'bad JSON' => array( array(), true, 'remote_action_capability_missing', '{' ),
			'scalar JSON' => array( array(), true, 'remote_action_capability_missing', 'false' ),
		);
	}
}
