<?php
use PHPUnit\Framework\TestCase;

/** Synthetic observations and API doubles only; not W/L genuine-core qualification. */
final class SourcePolicyNonlegacyJoinTest extends TestCase {
	/** @dataProvider cases */
	public function test_exact_candidates_and_actual_initializer_against_isolated_apis($case) {
		$process=proc_open(array(PHP_BINARY,'-n',__DIR__.'/support/source-policy-production-worker.php',$case),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),$err.$out);$this->assertSame('',$err);$this->assertMatchesRegularExpression('/\Achecks:[1-9][0-9]*\n\z/',$out);
	}
	public static function cases() {
		$cases=array('data','save','view','unchanged','stale','reentry','uncertain','close-error','preconnect','active-frame','active-client','active-table','active-query','active-close','active-runtime','capability','nonce','record','source','runtime-default','runtime-platform','runtime-arch','runtime-php','runtime-client','runtime-mode','runtime-extra','runtime-word_size','runtime-nts','runtime-32bit','runtime-zts','client','report','mixed-origin','query-origin','frame','binding','prefixed','double-prefix','newer','helper','schema','session-db','session-ac','session-tx','session-statement_limit','session-row_limit','session-metadata_limit','session-client_charset','session-connection_charset','session-result_charset','session-collation','session-mode','fault-object','fault-allocate','fault-option','fault-connect','fault-identity','fault-limits','fault-mode','fault-charset','fault-session','fault-table','fault-columns','fault-indexes','late-query','late-global','late-frame','late-client','late-mode','late-hook');
		$cases[]='runtime-observation';$result=array();foreach(array('w','l')as$profile)foreach($cases as$case)$result[$profile.':'.$case]=array($profile.':'.$case);return$result;
	}
}
