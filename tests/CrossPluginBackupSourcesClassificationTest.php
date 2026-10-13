<?php
/**
 * Cross-plugin backup source status classification tests.
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
 * Tests dashboard classification of uploader-produced backup_sources payloads.
 */
class CrossPluginBackupSourcesClassificationTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Cross_Plugin_Backup_Sources_Contract_Fixtures;

	/**
	 * Queue-only source warnings do not convert fresh source evidence to attention.
	 *
	 * @return void
	 */
	public function test_uploader_fresh_queue_only_fixture_classifies_as_working() {
		$result = $this->classify_contract_payload( $this->validated_fixture_payload() );

		$this->assertSame( 'working', $result['category'] );
	}
}
