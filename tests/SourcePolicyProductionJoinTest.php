<?php
use PHPUnit\Framework\TestCase;

/** Isolated actual-initializer tests, not genuine WordPress or native qualification. */
final class SourcePolicyProductionJoinTest extends TestCase {
	public function test_expectation_is_exact_non_secret_data_only() { $this->test_two_phase_capture_and_actual_initializer('data'); }
	public function test_legacy_observation_retains_exact_five_field_schema() { $this->test_two_phase_capture_and_actual_initializer('runtime-observation'); }
	/** @dataProvider cases */
	public function test_two_phase_capture_and_actual_initializer($case) {
		$process=proc_open(array(PHP_BINARY,'-n',__DIR__.'/support/source-policy-production-worker.php',$case),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),$err.$out);$this->assertSame('',$err);$this->assertMatchesRegularExpression('/\Achecks:[1-9][0-9]*\n\z/',$out);
	}
	public static function cases() {
		return array_map(static function($case){return array($case);},array('save','view','unchanged','stale','reentry','uncertain','close-error','preconnect','active-frame','active-client','active-table','active-query','active-close','capability','nonce','record','source','runtime-default','runtime-platform','runtime-arch','runtime-php','runtime-client','runtime-mode','runtime-extra','client','report','mixed-origin','query-origin','frame','binding','unprefixed','double-prefix','newer','helper','schema','session-db','session-ac','session-tx','session-statement_limit','session-row_limit','session-metadata_limit','session-client_charset','session-connection_charset','session-result_charset','session-collation','session-mode','fault-object','fault-allocate','fault-option','fault-connect','fault-identity','fault-limits','fault-mode','fault-charset','fault-session','fault-table','fault-columns','fault-indexes','late-query','late-global','late-frame','late-client','late-mode','late-hook'));
	}
}
