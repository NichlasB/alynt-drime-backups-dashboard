import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {admit,collect,readCommand,SOURCES} from '../../scripts/ci/source-policy-ubi-engine-audit.mjs';

const env={GITHUB_ACTIONS:'true',GITHUB_EVENT_NAME:'workflow_dispatch',GITHUB_REPOSITORY:'NichlasB/alynt-drime-backups-dashboard',
  GITHUB_REF:'refs/heads/master',GITHUB_WORKFLOW_REF:'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-ubi-engine-audit.yml@refs/heads/master',
  GITHUB_SHA:'a'.repeat(40),EXPECTED_SHA:'a'.repeat(40),GITHUB_RUN_ID:'123',GITHUB_RUN_ATTEMPT:'1',RUNNER_OS:'Linux',AUDIT_RUNNER_ENVIRONMENT:'github-hosted'};
const machine={platform:'linux',arch:'x64',release:'6.11.0-1018-azure'};
const version={Version:'28.0.4',ApiVersion:'1.48',GitCommit:'123abcd',Os:'linux',Arch:'amd64'};
const info={ServerVersion:'28.0.4',OSType:'linux',Architecture:'x86_64',Driver:'overlay2',SecurityOptions:['name=apparmor','name=seccomp,profile=builtin','name=cgroupns'],
  HTTPProxy:'secret-never-output',Name:'secret-hostname'};
function reads(overrides={},calls=[]){
  const values={head:env.GITHUB_SHA+'\n',source:'',context:'unix:///var/run/docker.sock\n',version:JSON.stringify(version),info:JSON.stringify(info),...overrides};
  return key=>{calls.push(key);return values[key];};
}

test('actual read-only collector has a positive bounded projection without sensitive raw metadata or grants',()=>{
  const calls=[],result=collect(env,machine,reads({},calls));
  assert.equal(result.engine.version,'28.0.4');assert.equal(result.engine.commit,'123abcd');
  assert.equal(result.default_policy_source_established,false);assert.equal(result.qualification,false);
  assert.equal(result.image_loading,false);assert.equal(result.candidate_execution,false);assert.equal(result.resources_created,0);
  assert.equal(result.cleanup,'not-required-no-resources-created');
  assert.ok(!JSON.stringify(result).includes('secret'));
  assert.deepEqual(calls,['head','source','context','version','info','head','source']);
});

test('job/head/workflow/host/override guards reject before any protected read',()=>{
  for(const [key,value] of Object.entries({GITHUB_EVENT_NAME:'push',GITHUB_REPOSITORY:'foreign',GITHUB_REF:'refs/heads/other',
    GITHUB_WORKFLOW_REF:'foreign',EXPECTED_SHA:'b'.repeat(40),GITHUB_RUN_ID:'123\n',GITHUB_RUN_ATTEMPT:'0',
    AUDIT_RUNNER_ENVIRONMENT:'self-hosted',DOCKER_HOST:'unix:///foreign',DOCKER_CONTEXT:'default',DOCKER_CONFIG:'/foreign'})){
    assert.throws(()=>collect({...env,[key]:value},machine,()=>assert.fail('protected read')),/exact-read-only-job/);
  }
  assert.throws(()=>admit(env,{...machine,platform:'win32'}),/exact-read-only-job/);
});

test('foreign local endpoint and changed checkout cannot reach further engine reads',()=>{
  for(const [key,value,reason] of [['head','b'.repeat(40),'checked-out-head'],['source','dirty','checked-out-source'],['context','tcp://remote','local-engine-only']]){
    const calls=[];assert.throws(()=>collect(env,machine,reads({[key]:value},calls)),new RegExp(reason));
    assert.ok(!calls.includes('version'));assert.ok(!calls.includes('info'));
  }
  let count=0;const read=reads();
  assert.throws(()=>collect(env,machine,key=>key==='head'&&++count===2?'b'.repeat(40):read(key)),/source-readback/);
});

test('unknown engine/profile and malformed or excessive documents refuse without echoing raw payloads',()=>{
  for(const raw of ['{', 'x'.repeat(1048577),JSON.stringify({...version,GitCommit:'secret'}),JSON.stringify({...version,Arch:'arm64'})]){
    assert.throws(()=>collect(env,machine,reads({version:raw})),/metadata-size|metadata-json|engine-version/);
  }
  for(const change of [{SecurityOptions:['name=seccomp,profile=unconfined']},{ServerVersion:'99.0.0'},
    {SecurityOptions:['secret-userinfo']},{SecurityOptions:Array(17).fill('name=cgroupns')}]){
    assert.throws(()=>collect(env,machine,reads({info:JSON.stringify({...info,...change})})),/builtin-seccomp-unavailable/);
  }
});

test('fixed command runner uses only readonly argv, closed input, bounded buffers/time and no inherited secrets',()=>{
  const called=[];
  for(const key of ['head','source','context','version','info']){
    assert.equal(readCommand(key,{PATH:'path',HOME:'home',SECRET:'never'},(file,args,options)=>{
      called.push([file,args]);assert.equal(options.shell,false);assert.equal(options.input,'');
      assert.equal(options.timeout,10000);assert.equal(options.maxBuffer,1048576);
      assert.deepEqual(options.env,{PATH:'path',HOME:'home',LANG:'C.UTF-8'});
      return {status:0,stdout:'ok',stderr:''};
    }),'ok');
  }
  assert.deepEqual(called.map(row=>row[1][0]),['rev-parse','diff','context','version','info']);
  assert.throws(()=>readCommand('pull',{},()=>assert.fail('spawn')),/read-only-command/);
  for(const result of [{status:1,stdout:'',stderr:''},{status:0,stdout:'',stderr:'secret'},
    {status:0,stdout:'x'.repeat(1048577),stderr:''},{status:0,stdout:'',stderr:'',signal:'SIGKILL'}]){
    assert.throws(()=>readCommand('info',{},()=>result),/bounded-read-only-command/);
  }
});

test('source/workflow contract is separately manual, exact-head and incapable of invoking the static/native controllers',()=>{
  const source=readFileSync(new URL('../../scripts/ci/source-policy-ubi-engine-audit.mjs',import.meta.url),'utf8');
  const workflow=readFileSync(new URL('../../.github/workflows/source-policy-ubi-engine-audit.yml',import.meta.url),'utf8');
  assert.deepEqual(SOURCES,['scripts/ci/source-policy-ubi-engine-audit.mjs','.github/workflows/source-policy-ubi-engine-audit.yml','scripts/manual/source_policy_ubi_runtime_contract.py']);
  assert.ok(workflow.includes('persist-credentials: false'));assert.ok(workflow.includes('contents: read'));
  assert.ok(workflow.includes('runner.environment'));assert.ok(workflow.includes('timeout-minutes: 2'));
  assert.equal((workflow.match(/run:/g)||[]).length,1);
  for(const forbidden of ['source-policy-minimum-controller','source-policy-legacy-ubi-static.py','docker run','docker pull','docker load','docker create','docker start']){
    assert.ok(!source.includes(forbidden));assert.ok(!workflow.includes(forbidden));
  }
});

test('source-backed job-order and endpoint guard removals are assertion-killed without real engine calls',async()=>{
  const source=readFileSync(new URL('../../scripts/ci/source-policy-ubi-engine-audit.mjs',import.meta.url),'utf8');
  for(const [old,replacement,witness] of [
    ['admit(env,machine); // No engine/source reads before fixed job admission.','// removed job admission',
      module=>assert.throws(()=>module.collect({...env,GITHUB_EVENT_NAME:'push'},machine,()=>assert.fail('protected read')),/exact-read-only-job/)],
    ["read('context').trim()==='unix:///var/run/docker.sock'",'true',
      module=>assert.throws(()=>module.collect(env,machine,reads({context:'tcp://remote'})),/local-engine-only/)],
  ]){
    assert.equal(source.split(old).length-1,1);
    witness({collect});
    const mutant=await import('data:text/javascript;base64,'+Buffer.from(source.replace(old,replacement)).toString('base64'));
    assert.throws(()=>witness(mutant),assert.AssertionError);
  }
});
