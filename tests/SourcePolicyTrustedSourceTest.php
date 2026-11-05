<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/includes/class-source-policy-trusted-source.php';

/** Pin provenance parity is repository DATA evidence, not loaded-code attestation. */
final class SourcePolicyTrustedSourceTest extends TestCase {
	public function test_each_source_row_reuses_exact_previously_pinned_libraries() {
		if(!defined('WPINC'))define('WPINC','wp-includes');
		$rows=require dirname(__DIR__).'/scripts/manual/source-policy-supported-core-pins.php';
		foreach(array('6.0','7.1.3','7.1.4')as$core) {
			$policy=Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Source::policy($core);$expected='7.1.4'===$core?json_decode(file_get_contents(dirname(__DIR__).'/scripts/ci/source-policy-linux-core-pins.json'),true):$rows[$core]['pins'];
			unset($expected['version.php']);$this->assertSame($expected,$policy['pins']);$this->assertSame($core,$policy['core']);
			$this->assertSame('6.0'===$core?'wp-db.php':'class-wpdb.php',$policy['files']['core_file']);$this->assertNull($policy['driver']);
		}
		foreach(array(null,array(),'7.1','7.1.4 ','9.0')as$bad)$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Source::policy($bad));
	}
}
