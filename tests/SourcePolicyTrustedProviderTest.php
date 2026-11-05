<?php
use PHPUnit\Framework\TestCase;

/** Extension-free API tests only, not real authentication/storage/native proof. */
final class SourcePolicyTrustedProviderTest extends TestCase {
	/** @dataProvider cases */
	public function test_actual_capture_guard_dispatch_and_cleanup($case) {
		$process=proc_open(array(PHP_BINARY,'-n',__DIR__.'/support/source-policy-trusted-worker.php',$case),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),$err.$out);$this->assertSame('',$err);$this->assertMatchesRegularExpression('/\Achecks:[1-9][0-9]*\n\z/',$out);
	}
	public static function cases() {
		return array_map(static function($case){return array($case);},array('positive-view','positive-save','positive-legacy-parser','unchanged','stale','capability','nonce','nonce-domain','nonce-compound','site-compound','site-negative','site-overflow','site-padded','site-long','source-long','mode-long','revision-long','revision-upper','record','default-policy','source','parser-source','parser-warning','foreign-class','foreign-owner','unknown-hook','registry-shape','late-global','late-core','late-table','late-hook','reentry','read-error','uncertain','close-error','serialize','view-capability','view-record','view-invalid','closed-factories','record-error','missing-facts'));
	}
}
