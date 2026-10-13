<?php
/**
 * Cross-plugin stale backup source status classification tests.
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
require_once __DIR__ . '/support/cross-plugin-backup-sources-contract-fixtures.php';

/**
 * Tests dashboard classification of stale uploader-produced backup_sources payloads.
 */
class CrossPluginBackupSourcesStaleClassificationTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Cross_Plugin_Backup_Sources_Contract_Fixtures;

	/**
	 * Stale source evidence from the uploader is surfaced as attention.
	 *
	 * @return void
	 */
	public function test_uploader_stale_source_fixture_classifies_as_needs_attention() {
		$payload                                                   = $this->validated_fixture_payload();
		$payload['backup_sources']['server']['freshness_status']   = 'stale';
		$payload['backup_sources']['server']['warning_count']      = 1;
		$payload['backup_sources']['server']['warnings']           = array(
			array(
				'code'    => 'source_latest_upload_stale',
				'message' => 'The latest uploaded backup evidence is older than the default freshness window.',
			),
		);
		$result                                                    = $this->classify_contract_payload( $payload );

		$this->assertSame( 'needs_attention', $result['category'] );
	}
}
