<?php
/**
 * Cross-plugin backup source status contract tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/traits/trait-status-payload-validator-backup-sources.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-status-payload-validator-sanitizers.php';
require_once dirname( __DIR__ ) . '/includes/class-status-payload-validator.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-status-classifier-backup-sources.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-status-classifier-helpers.php';
require_once dirname( __DIR__ ) . '/includes/class-status-classifier.php';

/**
 * Tests dashboard compatibility with uploader-produced backup_sources payloads.
 */
class CrossPluginBackupSourcesContractTest extends TestCase {
	/**
	 * Dashboard accepts the uploader schema-1 backup source fixture.
	 *
	 * @return void
	 */
	public function test_uploader_backup_sources_fixture_is_accepted() {
		$result = $this->validate_contract_payload( $this->fixture_payload() );

		$this->assertIsArray( $result );
		$this->assertSame( array( 'server', 'wpvivid' ), array_keys( $result['backup_sources'] ) );
		$this->assertSame( 'generic_outbox_remote_catalog', $result['backup_sources']['server']['latest_inventory_evidence'] );
		$this->assertSame( 'local_upload_registry', $result['backup_sources']['wpvivid']['latest_inventory_evidence'] );
		$this->assertSame( 'source_queue_not_empty', $result['backup_sources']['server']['warnings'][0]['code'] );
		$this->assert_payload_contains_no_forbidden_contract_keys( $result );
	}

	/**
	 * Dashboard rejects path-mode or identifier-like fields if they appear nested.
	 *
	 * @return void
	 */
	public function test_nested_forbidden_uploader_contract_field_is_rejected() {
		$payload                                                = $this->fixture_payload();
		$payload['backup_sources']['server']['remote_index_path'] = '/var/backups/should-not-pass.json';
		$result                                                 = $this->validate_contract_payload( $payload );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'payload_invalid', $result->get_error_code() );
	}

	/**
	 * Loads the uploader-shaped schema-1 fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function fixture_payload() {
		$fixture = file_get_contents( __DIR__ . '/fixtures/uploader-backup-sources-schema1.json' );
		$payload = json_decode( (string) $fixture, true );

		$this->assertIsArray( $payload );

		return $payload;
	}

	/**
	 * Loads and validates the uploader-shaped schema-1 fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function validated_fixture_payload() {
		$result = $this->validate_contract_payload( $this->fixture_payload() );

		$this->assertIsArray( $result );

		return $result;
	}

	/**
	 * Validates a contract payload against its embedded site UUID.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,mixed>|WP_Error
	 */
	private function validate_contract_payload( array $payload ) {
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();

		return $validator->validate( $payload, (string) $payload['site_uuid'] );
	}

	/**
	 * Asserts a status payload does not include forbidden cross-plugin keys.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return void
	 */
	private function assert_payload_contains_no_forbidden_contract_keys( array $payload ) {
		$encoded = strtolower( (string) wp_json_encode( $payload ) );

		foreach ( array( 'api_token', 'authorization', 'backup_set_id', 'cookie', 'file_entry_id', 'nonce', 'package_id', 'password', 'presigned', 'remote_index_path', 'remote_name', 'secret', 'server_outbox_path', 'signed_url', 'workspace_id', 'backup_path_override' ) as $needle ) {
			$this->assertStringNotContainsString( $needle, $encoded );
		}
	}
}
