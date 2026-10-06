<?php
/**
 * Status payload validator restore-readiness tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-payload-validator-test-bootstrap.php';

/**
 * Tests client status payload restore-readiness validation.
 */
class StatusPayloadValidatorRestoreReadinessTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures;

	/**
	 * Optional restore-readiness evidence is allowlisted and sanitized.
	 *
	 * @return void
	 */
	public function test_restore_readiness_is_allowlisted_and_sanitized() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'restore_readiness' => array(
						'schema_version' => 1,
						'generated_at'   => '2026-10-02T12:00:00Z',
						'overall_state'  => 'evidence_available',
						'extra_field'    => 'ignored',
						'candidates'     => array(
							array(
								'source'                    => 'server',
								'candidate_ref'             => 'opaque-client-ref_123',
								'latest_backup_finished_at' => '2026-10-02T01:30:00Z',
								'component_state'           => 'complete',
								'checksum_state'            => 'verified',
								'manifest_state'            => 'compatible',
								'sidecar_state'             => 'present',
								'age_seconds'               => 3600,
								'warnings'                  => array( 'restore_evidence_incomplete' ),
								'raw_name'                  => 'ignored',
							),
							array(
								'source'          => 'wpvivid',
								'candidate_ref'   => '../private/backup.zip',
								'component_state' => 'surprising',
								'checksum_state'  => 'not_reported',
								'manifest_state'  => 'not_reported',
								'sidecar_state'   => 'not_reported',
							),
							array(
								'source' => 'unsupported',
							),
						),
					),
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'restore_readiness', $result );
		$this->assertSame( 'evidence_available', $result['restore_readiness']['overall_state'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['restore_readiness'] );
		$this->assertCount( 2, $result['restore_readiness']['candidates'] );
		$this->assertSame( 'server', $result['restore_readiness']['candidates'][0]['source'] );
		$this->assertSame( 'opaque-client-ref_123', $result['restore_readiness']['candidates'][0]['candidate_ref'] );
		$this->assertSame( 'verified', $result['restore_readiness']['candidates'][0]['checksum_state'] );
		$this->assertSame( array( 'restore_evidence_incomplete' ), $result['restore_readiness']['candidates'][0]['warnings'] );
		$this->assertSame( '', $result['restore_readiness']['candidates'][1]['candidate_ref'] );
		$this->assertSame( 'unknown', $result['restore_readiness']['candidates'][1]['component_state'] );
		$this->assertArrayNotHasKey( 'raw_name', $result['restore_readiness']['candidates'][0] );
	}
}
