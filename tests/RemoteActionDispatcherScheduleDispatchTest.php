<?php
/**
 * Remote action dispatcher schedule dispatch tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-dispatcher-test-bootstrap.php';

/**
 * Tests signed remote schedule action dispatch.
 */
class RemoteActionDispatcherScheduleDispatchTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_WPDB_Setup;

	public function test_schedule_preview_dispatch_posts_signed_preview_intent() {
		$captured = array();
		$http     = $this->accepted_http_response(
			$captured,
			'Schedule preview accepted safely.',
			'code',
			'summary'
		);

		$result = $this->dispatcher( $http )->request_schedule_preview( 9, 'alynt_scan_upload', 'every_30_minutes', 7 );

		$this->assertIsArray( $result );
		$this->assertSame( 'schedule_preview', $result['action'] );
		$this->assertSame( 'accepted', $result['remote_state'] );
		$request_body = json_decode( $captured['args']['body'], true );
		$this->assertSame( 'schedule_preview', $request_body['action_type'] );
		$this->assertSame( 'alynt_scan_upload', $request_body['schedule_preview']['schedule_id'] );
		$this->assertSame( 'every_30_minutes', $request_body['schedule_preview']['proposed_cadence'] );
		$this->assertSame( 1, $request_body['schedule_preview']['capability_version'] );
		$this->assertSame( 'schedule_preview', $this->wpdb->inserted_data['action_type'] );
		$this->assertStringContainsString( 'every_30_minutes', $this->wpdb->inserted_data['redacted_context_json'] );
		$this->assertStringNotContainsString( 'private-key', wp_json_encode( $this->wpdb->inserted_data ) );
	}

	/**
	 * Builds an accepted remote-action HTTP response callback.
	 *
	 * @param array<string,mixed> $captured Captured request details.
	 * @param string              $summary Response summary.
	 * @param string              $code_key Result code field name.
	 * @param string              $summary_key Result summary field name.
	 * @return callable
	 */
	private function accepted_http_response( array &$captured, $summary, $code_key = 'result_code', $summary_key = 'result_summary' ) {
		return function ( $url, $args ) use ( &$captured, $summary, $code_key, $summary_key ) {
			$captured = array(
				'url'  => $url,
				'args' => $args,
			);
			$body     = json_decode( $args['body'], true );

			return array(
				'response' => array(
					'code' => 202,
				),
				'body'     => wp_json_encode(
					array(
						'protocol_version' => 2,
						'action_id'        => $body['action_id'],
						'state'            => 'accepted',
						$code_key          => 'action_accepted',
						$summary_key       => $summary,
						'retry_after'      => 0,
					)
				),
			);
		};
	}
}
