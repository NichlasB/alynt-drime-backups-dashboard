<?php
use PHPUnit\Framework\TestCase;

/** Pure parser/source gates only; never an owned Linux observer attempt. */
final class SourcePolicyLinuxObserverAuditTest extends TestCase {
	public function test_offline_observer_contracts_without_process_or_proc_access() {
		$python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';$this->assertFileExists($python);
		$process=proc_open(array($python,'-I','-S','-B',__DIR__.'/support/source_policy_linux_observer_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),substr($err,0,3000));$this->assertSame('',$err);
		$this->assertSame(array('tests'=>10,'failures'=>0,'errors'=>0),json_decode($out,true));
	}
	/** @dataProvider invocations */
	public function test_wrong_context_refuses_before_proc_or_owned_files($arguments) {
		$python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
		$process=proc_open(array_merge(array($python,'-I','-S','-B',dirname(__DIR__).'/scripts/ci/source-policy-linux-observer-audit.py'),$arguments),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(2,proc_close($process));$this->assertSame('',$out);$this->assertSame("Requires exact approved manual Linux host audit\n",$err);
	}
	public static function invocations(){return array(array(array()),array(array('--wrong')),array(array('--audit')),array(array('--cleanup')));}
	public function test_workflow_is_manual_exact_head_existing_tools_and_always_cleanup() {
		$root=dirname(__DIR__);$workflow=file_get_contents($root.'/.github/workflows/source-policy-linux-observer-audit.yml');
		foreach(array('workflow_dispatch:','contents: read','ubuntu-24.04','"$EXPECTED_SHA" == "$GITHUB_SHA"','if: always()','--audit','--cleanup','/usr/bin/python3 -I -S -B') as $required)$this->assertStringContainsString($required,$workflow);
		foreach(array('push:','pull_request:','schedule:','services:','secrets.','setup-php','setup-python','sudo','docker ','apt-get','pip install','self-hosted') as $forbidden)$this->assertStringNotContainsString($forbidden,$workflow);
		preg_match_all('/uses: [^\s]+@([a-f0-9]{40})\b/',$workflow,$actions);$this->assertCount(1,$actions[0]);
		$source=file_get_contents($root.'/scripts/ci/source-policy-linux-observer-audit.py');
		foreach(array('ctypes','setuid(','setgid(','chmod(','os.system','shell=True','killpg(','socket.') as $forbidden)$this->assertStringNotContainsString($forbidden,$source);
		$this->assertStringNotContainsString('observer-audit',file_get_contents($root.'/alynt-drime-backups-dashboard.php'));
	}
}
