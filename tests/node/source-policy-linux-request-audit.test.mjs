import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {createHash} from 'node:crypto';
import {Script} from 'node:vm';
import {requestAuditResult} from '../../scripts/ci/source-policy-linux-qualification-controller.mjs';
import {wlLinuxJob,wlEnvelope,validRequestAudit,requestBatch,wlRequestFailure,parseRequestWire} from '../../scripts/ci/source-policy-wl-request-contract.mjs';

const original=readFileSync(new URL('../../scripts/ci/source-policy-linux-qualification-controller.mjs',import.meta.url),'utf8').replaceAll('\r\n','\n');
const job={QUALIFICATION_GENUINE_REQUEST_APPROVED:'true',GITHUB_ACTIONS:'true',GITHUB_EVENT_NAME:'workflow_dispatch',GITHUB_WORKFLOW_REF:'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-linux-qualification.yml@refs/heads/master',GITHUB_REPOSITORY:'NichlasB/alynt-drime-backups-dashboard',GITHUB_REF:'refs/heads/master',GITHUB_ACTOR:'NichlasB',GITHUB_RUN_ATTEMPT:'1',GITHUB_RUN_ID:'12345',GITHUB_SHA:'a'.repeat(40),EXPECTED_SHA:'a'.repeat(40),RUNNER_TEMP:'/owned-virtual'};
const run=wlEnvelope(job,'b'.repeat(64),'audit').WL_REQUEST_RUN;
const audit={event:'audit',profile:'l',run,image:'b'.repeat(64),modules:'c'.repeat(64),checks:29,native_connections:0,sql_queries:0};
const failure='ADBWLRF1|l|child|0|audit|RuntimeException|'+'d'.repeat(64)+'\n';
const refused=stderr=>({status:1,signal:null,error:null,stdout:'',stderr});

// Execute actual fixed main/audit source with inert IO, never an OS child or native session.
async function invoke(result,env={},source=original){
  const argsStart=source.indexOf('const args = ['),argsEnd=source.indexOf('\n\nfunction reconcile',argsStart);
  const mainStart=source.indexOf('async function main() {'),mainEnd=source.indexOf('\nif (process.argv[1]',mainStart);
  assert.ok(argsStart>0&&argsEnd>argsStart&&mainStart>argsEnd&&mainEnd>mainStart);
  const prelude=source.slice(0,argsStart).replace(/^import[^\n]+\n/gm,'').replace(/^export /gm,'');
  assert.doesNotMatch(prelude,/^import /m);
  const calls=[],output=[],effects=[];
  const forbidden=name=>()=>{effects.push(name);throw Error('Forbidden isolated IO: '+name);};
  const context={Buffer,createHash,wlLinuxJob,wlEnvelope,validRequestAudit,requestBatch,wlRequestFailure,parseRequestWire,
    process:{platform:'linux',argv:['node','/virtual/controller.mjs','audit'],env:{...job,...env},stdout:{write:s=>output.push(s)}},
    spawnSync:(command,args,options)=>{calls.push({command,args:Array.from(args),options});return result;},
    spawn:forbidden('spawn'),readFileSync:forbidden('read'),lstatSync:forbidden('stat'),reconcile:forbidden('SQL'),launchRequest:forbidden('request')};
  const program=new Script(prelude+source.slice(argsStart,argsEnd)+'\n'+source.slice(mainStart,mainEnd)+'\nmain();');
  let error=null;
  try{await program.runInNewContext(context,{timeout:1000});}catch(caught){
    if(['SyntaxError','ReferenceError'].includes(caught.name)||caught.code==='ERR_SCRIPT_EXECUTION_TIMEOUT')throw caught;
    error=caught.message;
  }
  assert.deepEqual(effects,[]);
  return {error,calls,output};
}
function summary(result){assert.match(result.error,/^Source audit refused: /);assert.deepEqual(result.output,[]);return JSON.parse(result.error.slice('Source audit refused: '.length));}

async function checkProjected(source=original){
  const result=await invoke(refused(failure),{},source),row=summary(result);
  assert.deepEqual(row.request_failure,{profile:'l',case_index:0,stage:'audit',exception:'RuntimeException',assertion:'d'.repeat(64)});
  assert.equal(row.status,1);assert.equal(row.worker,null);assert.equal(row.stdout.bytes,0);assert.equal(row.stderr.bytes,Buffer.byteLength(failure));
  assert.equal(result.calls.length,1);assert.equal(result.calls[0].command,'php');
  assert.deepEqual(result.calls[0].args.slice(-3),['scripts/ci/source-policy-wl-request-child.php','--owned-wl-request-child','0']);
  assert.equal(result.calls[0].options.timeout,20000);assert.equal(result.calls[0].options.maxBuffer,16384);
}

test('actual L audit caller projects only the bounded request failure and retains nonzero refusal',async()=>{
  await checkProjected();const label=createHash('sha256').update('finite actual include closure').digest('hex');
  const context='ADBWLIC1|l|0|audit|32|core|scope|'+'e'.repeat(64)+'\n'+failure.replace('d'.repeat(64),label);
  assert.deepEqual(summary(await invoke(refused(context))).request_failure.include,{ordinal:32,origin:'core',reason:'scope',path_digest:'e'.repeat(64)});
});

test('actual L audit has a non-vacuous positive and rejects changed or malformed success evidence',async()=>{
  const stdout=JSON.stringify(audit)+'\n',ok={status:0,signal:null,error:null,stdout,stderr:''};
  const result=await invoke(ok);assert.equal(result.error,null);assert.deepEqual(result.output,[stdout]);assert.equal(result.calls.length,1);
  for(const [key,value]of Object.entries({profile:'w',run:'f'.repeat(32),image:'bad',modules:null,checks:0,native_connections:1,sql_queries:1})){
    const bad=await invoke({...ok,stdout:JSON.stringify({...audit,[key]:value})+'\n'});assert.ok(bad.error);assert.deepEqual(bad.output,[]);
  }
  for(const stdout of [ok.stdout+'extra',ok.stdout.replace('"profile":"l"','"profile":"l","profile":"l"'),'',null]){
    const bad=await invoke({...ok,stdout});assert.ok(bad.error);assert.deepEqual(bad.output,[]);
  }
});

test('audit diagnostics reject foreign profile/case/phase and abnormal process outcomes without losing exit facts',async()=>{
  for(const invalidRun of [null,{},'short','B'.repeat(32)])assert.throws(()=>requestAuditResult(refused(failure),invalidRun),error=>JSON.parse(error.message.slice('Source audit refused: '.length)).request_failure===null);
  for(const stderr of [failure.replace('|l|','|w|'),failure.replace('|0|','|1|'),failure.replace('|audit|','|auth|'),failure.replace('|audit|','|cleanup|')])
    assert.equal(summary(await invoke(refused(stderr))).request_failure,null);
  for(const change of [{status:0},{status:2},{signal:'SIGKILL'},{error:{code:'ETIMEDOUT',message:'PRIVATE'}},{stdout:'PRIVATE'}]){
    const input={...refused(failure),...change},row=summary(await invoke(input));assert.equal(row.request_failure,null);
    assert.equal(row.status,input.status);assert.equal(JSON.stringify(row).includes('PRIVATE'),false);
    if(input.signal)assert.equal(row.signal,'SIGKILL');if(input.error)assert.equal(row.error,'ETIMEDOUT');
  }
});

test('malformed, legacy and secret-bearing channels remain digest-only and cannot become audit facts',async()=>{
  const legacy={event:'failure',stage:'source-audit',phase:'core-load',checks:1,groups:0,assertion:'d'.repeat(64),exception:'RuntimeException',diagnostic:null,escape:null,escape_release:null};
  for(const stderr of [failure+'PRIVATE',failure.replace('RuntimeException','PRIVATE'),failure.replace('d'.repeat(64),'PRIVATE'),failure.replace('\n','\r\n'),failure.repeat(2),'PRIVATE\0cookie','🌍'.repeat(5000),JSON.stringify(legacy)+'\n',null]){
    const row=summary(await invoke(refused(stderr)));assert.equal(row.request_failure,null);assert.equal(JSON.stringify(row).includes('PRIVATE'),false);
    if(typeof stderr==='string'){assert.equal(row.stderr.bytes,Buffer.byteLength(stderr));assert.equal(row.stderr.sha256_prefix,createHash('sha256').update(Buffer.from(stderr).subarray(0,16384)).digest('hex'));}
  }
});

test('actual job admission still rejects before any process call and preserves legacy audit behavior',async()=>{
  for(const env of [{GITHUB_ACTOR:'other'},{GITHUB_WORKFLOW_REF:'other'},{GITHUB_RUN_ATTEMPT:'2'},{EXPECTED_SHA:'f'.repeat(40)},{QUALIFICATION_GENUINE_REQUEST_APPROVED:'invalid'}]){
    const result=await invoke(refused(failure),env);assert.ok(result.error);assert.deepEqual(result.calls,[]);assert.deepEqual(result.output,[]);
  }
  const result=await invoke(refused(failure),{QUALIFICATION_GENUINE_REQUEST_APPROVED:'false'});
  assert.equal(Object.hasOwn(summary(result),'request_failure'),false);
  assert.deepEqual(result.calls[0].args.slice(-3),['scripts/ci/source-policy-linux-qualification.php','--owned-linux-qualification','audit']);
});

test('source-backed audit controls assertion-kill missing projection, foreign binding and always-unavailable audit',async()=>{
  await assert.rejects(()=>invoke(refused(failure),{},'const =;\n'+original),{name:'SyntaxError'});
  await assert.rejects(()=>invoke(refused(failure),{},original.replace('requestAuditResult(result,run);','undefinedAuditFunction(result,run);')),{name:'ReferenceError'});
  const controls=[
    s=>s.replace("fact.profile==='l'","true"),
    s=>s.replace('fact.case_index===0','true'),
    s=>s.replace("fact.stage==='audit'","true"),
    s=>s.replace('export function requestAuditResult(result, run) {',"export function requestAuditResult(result, run) { throw Error('Always refused');"),
    s=>s.replace('request_failure:bound?fact:null','request_failure:null'),
    s=>s.replace('result.status===1','true'),
    s=>s.replace("if(!validRequestAudit(row,'l',run))","if(false)")
  ];
  for(let index=0;index<controls.length;index++){
    const mutant=controls[index](original);assert.notEqual(mutant,original);
    const exercise=async()=>{
      if(index===3){const result=await invoke({status:0,signal:null,error:null,stdout:JSON.stringify(audit)+'\n',stderr:''},{},mutant);assert.equal(result.error,null);}
      else if(index===4)await checkProjected(mutant);
      else if(index===5)assert.equal(summary(await invoke({...refused(failure),status:2},{},mutant)).request_failure,null);
      else if(index===6){const result=await invoke({status:0,signal:null,error:null,stdout:JSON.stringify({...audit,image:'bad'})+'\n',stderr:''},{},mutant);assert.ok(result.error);}
      else{const foreign=[failure.replace('|l|','|w|'),failure.replace('|0|','|1|'),failure.replace('|audit|','|auth|')][index];assert.equal(summary(await invoke(refused(foreign),{},mutant)).request_failure,null);}
    };
    await assert.rejects(exercise,{code:'ERR_ASSERTION'});
  }
});
