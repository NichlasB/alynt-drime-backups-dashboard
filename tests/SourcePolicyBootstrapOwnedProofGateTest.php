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
	/** @dataProvider diagnostic_modes */
	public function test_diagnostic_mode_requires_audited_driver_before_site_access($mode) {
		$script=dirname(__DIR__).'/scripts/manual/source-policy-bootstrap-owned-proof.php';
		$process=proc_open(array(PHP_BINARY,'-n',$script,'--confirmed-plugin-tester-owned-bootstrap-proof','adbb_0123456789ab_',$mode),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process); fclose($pipes[0]);
		$output=stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
		$this->assertSame(1,proc_close($process)); $this->assertSame('',$output);
		$this->assertSame("Requires the audited bundled PHP8.5.12 mysqli-only profile\n",$error);
	}
	public static function diagnostic_modes() {
		return array(array('diagnose-raw'),array('diagnose-owner'));
	}
	/** @dataProvider diagnostic_counters */
	public function test_socket_free_diagnostic_counter_format($value,$expected) {
		$worker=__DIR__.'/support/source-policy-diagnostic-counter-worker.php';
		$process=proc_open(array(PHP_BINARY,'-n','-r','define("ADB_DIAGNOSTIC_COUNTER_WORKER",true); require $argv[1];',$worker,$value),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process); fclose($pipes[0]);
		$output=stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
		$this->assertSame(0,proc_close($process)); $this->assertSame('',$error); $this->assertSame($expected,$output);
	}
	public static function diagnostic_counters() {
		return array(array('0','accepted:0'),array('-1','accepted:-1'),array('18446744073709551615','accepted:18446744073709551615'),array('float','accepted:double'),array('1.844674407371E+19','accepted:1.844674407371E+19'),array('184467440737095516150','counter-rejected'),array('counter-secret','counter-rejected'),array('array','counter-rejected'),array('exception-marker',"diagnostic-exception stage:raw-errno class:Error\n"));
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
