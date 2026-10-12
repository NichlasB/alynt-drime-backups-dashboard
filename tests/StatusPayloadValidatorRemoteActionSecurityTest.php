<?php
/**
 * Status payload validator remote-action security tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-payload-validator-test-bootstrap.php';

/**
 * Tests client status payload remote-action security rejection behavior.
 */
class StatusPayloadValidatorRemoteActionSecurityTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures;

	/**
	 * Remote-action summaries containing secrets or path-mode fields are rejected.
	 *
	 * @return void
	 */
	public function test_remote_action_forbidden_field_is_rejected() {
		$result = $this->validate_payload(
			array_merge(
				$this->payload(),
				array(
					'remote_actions' => array(
						'protocol_version' => 2,
						'enabled'          => true,
						'last_action'      => array(
							'action_id' => '11111111-1111-4111-8111-111111111111',
							'file'      => '/private/backup.zip',
						),
					),
				)
			)
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'payload_invalid', $result->get_error_code() );
	}
}
