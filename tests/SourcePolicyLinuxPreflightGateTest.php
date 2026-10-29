<?php
use PHPUnit\Framework\TestCase;

/** Repository-only gates/static contracts, not Linux/native qualification. */
final class SourcePolicyLinuxPreflightGateTest extends TestCase {
	/** @dataProvider invocations */
	public function test_extension_free_invocations_refuse_before_core_reads($arguments) {
		$directory=dirname(__DIR__).'/scripts/ci';$script=$directory.'/source-policy-linux-preflight.php';
		$process=proc_open(array_merge(array(PHP_BINARY,'-n','-d','open_basedir='.$directory,$script),$arguments),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(1,proc_close($process));$this->assertSame('',$out);$this->assertSame("Requires exact owned Linux CI environment-only preflight\n",$err);
	}
	public static function invocations(){return array(array(array()),array(array('--wrong')),array(array('--owned-profile-preflight')),array(array('--owned-profile-preflight','extra')));}
	public function test_source_pins_equal_existing_reviewed_closure_without_executing_it() {
		require_once dirname(__DIR__).'/scripts/manual/source-policy-supported-profile.php';
		$profile=adbp_profile('7.1.3');$expected=$profile['pins'];unset($expected['version.php']);
		$pins=json_decode(file_get_contents(dirname(__DIR__).'/scripts/ci/source-policy-linux-core-pins.json'),true);
		$this->assertCount(11,$expected);$this->assertSame($expected,$pins);
	}
	public function test_workflow_is_manual_exact_head_and_has_no_database_service_or_credentials() {
		$workflow=file_get_contents(dirname(__DIR__).'/.github/workflows/source-policy-linux-preflight.yml');
		$this->assertStringContainsString('workflow_dispatch:',$workflow);$this->assertStringContainsString('contents: read',$workflow);$this->assertStringContainsString('timeout-minutes: 10',$workflow);
		$this->assertStringContainsString('"$EXPECTED_SHA" == "$GITHUB_SHA"',$workflow);$this->assertStringContainsString('if: always()',$workflow);
		foreach(array('pull_request:','push:','schedule:','services:','secrets.','self-hosted','MARIADB_ROOT_PASSWORD','MYSQL_PWD') as $forbidden)$this->assertStringNotContainsString($forbidden,$workflow);
		preg_match_all('/uses: ([^\s]+)@([a-f0-9]{40})\b/',$workflow,$actions);$this->assertCount(3,$actions[0]);
	}
}
