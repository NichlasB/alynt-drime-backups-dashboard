<?php
use PHPUnit\Framework\TestCase;

/** Class-owned ordinary positive construction in an explicitly catalog-rebound API image, not native proof. */
final class SourcePolicyRequestCompositionTest extends TestCase {
	/** @dataProvider cases */
	public function test_result_only_guarded_construction_and_terminal_cleanup($case) {
		$process=proc_open(array('node',__DIR__.'/support/source-policy-request-image.mjs',$case,PHP_BINARY),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),$err.$out);$this->assertSame('',$err);$this->assertMatchesRegularExpression('/\Achecks:[1-9][0-9]*\n\z/',$out);
	}
	public static function cases(){return array_map(static function($case){return array($case);},array('save','view','noop','stale','uncertain','close-error','terminal-fault','capability','nonce','site','revision','compound-nonce','compound-site','compound-mode','source-key','request-object','record','record-throw','native-policy','original-policy','source','root-controls'));}
}
