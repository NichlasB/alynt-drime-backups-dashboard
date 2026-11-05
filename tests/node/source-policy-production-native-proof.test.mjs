import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {minimumOperation,PRODUCTION_JOIN} from '../../scripts/ci/source-policy-minimum-production-contract.mjs';
import {DISTRIBUTION_MODEL,DISTRIBUTION_WORKFLOW} from '../../scripts/ci/source-policy-minimum-distribution-contract.mjs';
import {terminal,MODEL} from '../../scripts/ci/source-policy-minimum-controller.mjs';
const read=p=>readFileSync(new URL('../../'+p,import.meta.url),'utf8');
const head='a'.repeat(40),run='b'.repeat(32),image='c'.repeat(64),cid='d'.repeat(64),modules='e'.repeat(64);
const job={GITHUB_ACTIONS:'true',GITHUB_EVENT_NAME:'workflow_dispatch',GITHUB_REPOSITORY:'NichlasB/alynt-drime-backups-dashboard',GITHUB_REF:'refs/heads/master',GITHUB_WORKFLOW_REF:DISTRIBUTION_WORKFLOW,GITHUB_ACTOR:'NichlasB',RUNNER_OS:'Linux',RUNNER_ENVIRONMENT:'github-hosted',GITHUB_RUN_ATTEMPT:'1',GITHUB_RUN_ID:'123',GITHUB_SHA:head,EXPECTED_SHA:head,MINIMUM_MODEL:DISTRIBUTION_MODEL,DISTRIBUTION_EXECUTION_APPROVED:'true',PRODUCTION_JOIN_APPROVED:'true',MINIMUM_OPERATION:PRODUCTION_JOIN};
test('production proof mode requires the complete exact manual job, never falls back on malformed selection',()=>{
  assert.equal(minimumOperation({}),'adapter');assert.equal(minimumOperation({MINIMUM_OPERATION:'',PRODUCTION_JOIN_APPROVED:'false'}),'adapter');assert.equal(minimumOperation(job),'production-join');
  for(const key of Object.keys(job)){assert.equal(minimumOperation({...job,[key]:'other'}),null,key);const missing={...job};delete missing[key];assert.equal(minimumOperation(missing),null,key+' missing');}
  for(const patch of [{MINIMUM_SCOPE:'audit-only-v1'},{GITHUB_RUN_ATTEMPT:'2'},{DOCKER_HOST:'tcp://remote'},{DOCKER_CONTEXT:'other'},{PRODUCTION_JOIN_APPROVED:true}])assert.equal(minimumOperation({...job,...patch}),null);
});
test('join proof receipts cannot substitute for old lab/default receipts or the converse',()=>{
  const wire=`ADBMJP1|${run}|${image}|${cid}|1|${modules}|123|9|0|closed|${DISTRIBUTION_MODEL}|false|not-established|false|false\n`;
  assert.equal(terminal(wire,run,image,cid,DISTRIBUTION_MODEL,'production-join'),true);
  assert.equal(terminal(wire,run,image,cid,DISTRIBUTION_MODEL),false);assert.equal(terminal(wire,run,image,cid,MODEL,'production-join'),false);
  for(const [before,after]of [['ADBMJP1','ADBMD1'],['|123|9|','|123|8|'],['|closed|','|active|'],['|false|false\n','|false|true\n'],[image,'f'.repeat(64)]])assert.equal(terminal(wire.replaceAll(before,after),run,image,cid,DISTRIBUTION_MODEL,'production-join'),false);
});
test('native join composition supplies only handle-free fixtures, not ready facts or source/runtime policy',()=>{
  const owner=read('scripts/ci/source-policy-minimum-production-owner.php');
  assert.match(owner,/\$context->capture_bootstrap\(\)/);assert.match(owner,/\$adapter->initialize_context\( \$context \)/);assert.match(owner,/\$provider->view_source_mode/);
  assert.doesNotMatch(owner,/adbb_bind\([^;]*(?:'ready'|'qualification'|'production_runtime'|'policy'|'minimum_lab_scope'|'dbh')/);
  assert.doesNotMatch(owner,/Minimum_Lab_Scope|mysqli_(?:init|real_connect|query)|->ready\s*=/);
  assert.match(owner,/0 ===|null === \$owner->profile\(\)/);assert.match(owner,/12 === adbp_read\( \$owner, 'bootstrap_credits' \)/);
  const worker=read('scripts/ci/source-policy-minimum-qualification.php');assert.match(worker,/\$adbm_production_join \? 'ADBMJA1\|'/);assert.match(worker,/\$adbm_production_join \? 'ADBMJP1\|'/);
  assert.match(worker,/if \( \$adbm_production_join \) \{\s*adbp_cases\(\);\s*\} else \{\s*adb0_cases\(\);/);
});
test('manual workflow remains separately default-off, same contained images and zero production changes',()=>{
  const workflow=read('.github/workflows/source-policy-minimum-distribution.yml');
  assert.match(workflow,/execute_production_join:[\s\S]*?type: boolean\s+default: false/);assert.match(workflow,/PRODUCTION_JOIN_APPROVED: \$\{\{ inputs.execute_production_join \}\}/);
  assert.match(workflow,/if: always\(\)/);assert.match(workflow,/permissions:\s+contents: read/);assert.match(workflow,/timeout-minutes: 10/);
  const controller=read('scripts/ci/source-policy-minimum-controller.mjs');assert.match(controller,/const operation=minimumOperation\(env\);requireFact\(operation!==null/);assert.ok(controller.includes("checks:proof?Number(out.split('|')[6]):null"));
});
