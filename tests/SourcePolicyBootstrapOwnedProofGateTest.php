<?php
use PHPUnit\Framework\TestCase;

/** Hosted QA must never read the installed site or enter the manual native proof. */
final class SourcePolicyBootstrapOwnedProofGateTest extends TestCase {
	/** @dataProvider refused_invocations */
	public function test_cli_gate_refuses_before_target_or_native_access($arguments) {
		$script=dirname(__DIR__).'/scripts/manual/source-policy-bootstrap-owned-proof.php';
		$process=proc_open(array_merge(array(PHP_BINARY,'-n',$script),$arguments),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process); fclose($pipes[0]);
		$output=stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
		$this->assertSame(1,proc_close($process)); $this->assertSame('',$output);
		$this->assertSame("Requires confirmed Plugin Tester owned bootstrap proof scope\n",$error);
	}
	public static function refused_invocations() {
		return array(array(array()),array(array('--wrong-scope')),array(array('--confirmed-plugin-tester-owned-bootstrap-proof','wp_','proof')));
	}
	/** @dataProvider refused_invocations */
	public function test_error_audit_gate_refuses_before_target_access($arguments) {
		$script=dirname(__DIR__).'/scripts/manual/source-policy-bootstrap-error-audit.php';
		$process=proc_open(array_merge(array(PHP_BINARY,'-n',$script),$arguments),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process); fclose($pipes[0]);
		$output=stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
		$this->assertSame(1,proc_close($process)); $this->assertSame('',$output);
		$this->assertSame("Requires confirmed Plugin Tester zero-SQL error-hook audit scope\n",$error);
	}
}
