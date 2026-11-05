<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/ci/source-policy-minimum-distribution-contract.php';

/** Pure job/evidence DATA, not WordPress authentication or native qualification.
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyDistributionContractTest extends TestCase {
	private function job() {
		return array('GITHUB_ACTIONS'=>'true','GITHUB_EVENT_NAME'=>'workflow_dispatch','GITHUB_REPOSITORY'=>'NichlasB/alynt-drime-backups-dashboard','GITHUB_REF'=>'refs/heads/master','GITHUB_WORKFLOW_REF'=>'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-minimum-distribution.yml@refs/heads/master','GITHUB_ACTOR'=>'NichlasB','RUNNER_OS'=>'Linux','RUNNER_ENVIRONMENT'=>'github-hosted','GITHUB_RUN_ATTEMPT'=>'1','GITHUB_RUN_ID'=>'123','GITHUB_SHA'=>str_repeat('a',40),'EXPECTED_SHA'=>str_repeat('a',40),'MINIMUM_MODEL'=>'trusted-distribution-legacy-v1','DISTRIBUTION_EXECUTION_APPROVED'=>'true','MINIMUM_IMAGE'=>str_repeat('c',64));
	}
	private function evidence() {
		return array('model'=>'trusted-distribution-legacy-v1','platform'=>'Linux','arch'=>'x86_64','php'=>'7.4.33','client'=>'mysqlnd 7.4.33','core_commit'=>'cc101b64012b16d087780657a2b828ccd7794a63','runtime_image'=>'sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4','server_image'=>'sha256:14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1','initial_mode'=>0,'head'=>str_repeat('a',40),'image'=>str_repeat('c',64),'run'=>substr(hash('sha256','123:1'),0,32));
	}
	public function test_job_rejects_missing_or_mutated_guards_before_any_owner_exists() {
		$job=$this->job();$this->assertTrue(adbm_distribution_entry($job));
		foreach($job as$key=>$value){if('MINIMUM_IMAGE'===$key)continue;$bad=$job;$bad[$key]='other';$this->assertFalse(adbm_distribution_entry($bad),$key);unset($bad[$key]);$this->assertFalse(adbm_distribution_entry($bad),$key.' missing');}
		foreach(array('MINIMUM_SCOPE'=>'audit-only-v1','DOCKER_HOST'=>'tcp://foreign','DOCKER_CONTEXT'=>'remote','GITHUB_RUN_ATTEMPT'=>'2','DISTRIBUTION_EXECUTION_APPROVED'=>true)as$key=>$value){$bad=$job;$bad[$key]=$value;$this->assertFalse(adbm_distribution_entry($bad),$key);}
	}
	public function test_every_evidence_field_and_correlation_is_discriminating() {
		$job=$this->job();$evidence=$this->evidence();$this->assertTrue(adbm_distribution_evidence($evidence,$job));
		foreach($evidence as$key=>$value){$bad=$evidence;$bad[$key]=is_int($value)?3:$value.'x';$this->assertFalse(adbm_distribution_evidence($bad,$job),$key);unset($bad[$key]);$this->assertFalse(adbm_distribution_evidence($bad,$job),$key.' missing');}
		foreach(array('head','image','run')as$key){$bad=$evidence;$bad[$key]=str_repeat('f',strlen($bad[$key]));$this->assertFalse(adbm_distribution_evidence($bad,$job),'valid-shaped foreign '.$key);}
		$bad=$evidence;$bad['extra']='ignored';$this->assertFalse(adbm_distribution_evidence($bad,$job));
		$job['MINIMUM_IMAGE']=str_repeat('d',64);$this->assertFalse(adbm_distribution_evidence($evidence,$job));
	}
	public function test_known_old_mode_stays_old_and_new_mode_cannot_self_activate() {
		$this->assertSame('lab',adbm_minimum_binding_mode(array('model'=>'trusted-lab-functional-v1')));
		$this->expectException(RuntimeException::class);adbm_minimum_binding_mode($this->evidence());
	}
	public function test_explicit_wrapper_and_bound_evidence_select_only_private_distribution_row() {
		define('ADB_MINIMUM_DISTRIBUTION',true);
		$prior=array();foreach($this->job()as$key=>$value){$prior[$key]=getenv($key);putenv($key.'='.$value);}
		try{
			$this->assertSame('distribution',adbm_minimum_binding_mode($this->evidence()));
			foreach(array(array('model'=>'trusted-lab-functional-v1'),array('model'=>'unknown'))as$bad){$refused=false;try{adbm_minimum_binding_mode($bad);}catch(RuntimeException $error){$refused=true;}$this->assertTrue($refused,'no cross-model fallback');}
			putenv('MINIMUM_IMAGE='.str_repeat('d',64));$refused=false;try{adbm_minimum_binding_mode($this->evidence());}catch(RuntimeException $error){$refused=true;}$this->assertTrue($refused,'source changed before construction');
		}finally{foreach($prior as$key=>$value)putenv(false===$value?$key:$key.'='.$value);}
	}
}
