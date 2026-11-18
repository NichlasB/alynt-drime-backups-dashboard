import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {INITIALIZED_HTTP_PROFILE} from '../../scripts/ci/source-policy-http-initialization.mjs';
import {manifestSummary} from '../../scripts/ci/source-policy-http-profile.mjs';
import {prepareInitializedWorkerController,INITIALIZED_CONTROLLER_EXECUTION_ENABLED} from '../../scripts/ci/source-policy-http-initialized-controller.mjs';
import * as f from './source-policy-http-fixtures.mjs';

const anchors='8'.repeat(64),digest='7'.repeat(64);
function fixture(){
 const rows=f.manifest(),input={identity:f.identity(),profile:{...INITIALIZED_HTTP_PROFILE},storage:f.storage(),manifest:rows,expectation:manifestSummary(rows),anchors};
 const calls=[];let at=0,binding;
 const io={kind:'isolated-initialized-controller-v2',now:()=>at,
  install:async(b,left)=>{binding=b;calls.push('install');assert.equal(Object.isFrozen(b),true);assert.equal(left,60000);at=100;return {...b,role:'installer',sql:100,exited:true,sessions:0,transactions:0,ddl_sealed:true,fixtures_verified:true};},
  initialize:async(t,left)=>{calls.push('initialize');assert.equal(left,59900);assert.equal(t.sql_remaining,924);at=200;return {event:'initializer-worker-prepared-v2',completion:{...t,sql:3,full_bootstrap:true,installing:false,short_init:false,protected_reads:0,protected_writes:0,owner_allocations:0,session_writes:0,mail:0,outbound:0,file_writes:0,source_unchanged:true,anchors_unchanged:true},callbacks_closed:true,runtime_grant:false,production_ready:false};},
  reconcile:async(b,left)=>{calls.push('release');assert.equal(b,binding);assert.equal(left,59800);at=300;return {...b,role:'initializer-release',exited:true,sessions:0,transactions:0,anchors_unchanged:true,source_unchanged:true};},
  baseline:async b=>{calls.push('baseline');at=400;return {...b,role:'full-invariant',digest,all_options:true,all_cron:true,all_widgets:true,all_transients:true,non_session_usermeta:true};},
  cleanup:async(b,left)=>{calls.push('cleanup');if(binding)assert.equal(b,binding);assert.equal(b.run,input.identity.run);assert.equal(left,30000);at=500;return f.cleanup();}
 };
 return {input,io,calls,setTime:n=>{at=n;}};
}

test('dormant controller consumes one worker packet, separate release, full baseline and exact cleanup',async()=>{
 const {io,input,calls}=fixture(),result=await prepareInitializedWorkerController(io,input);
 assert.equal(INITIALIZED_CONTROLLER_EXECUTION_ENABLED,false);assert.equal(result.prepared,true);assert.equal(result.total_setup_sql,103);assert.equal(result.baseline,digest);
 assert.equal(result.cleanup,'exact-owned-zero');assert.equal(result.isolated_only,true);assert.equal(result.runtime_grant,false);assert.equal(result.production_ready,false);assert.equal(result.http_qualified,false);
 assert.deepEqual(calls,['install','initialize','release','baseline','cleanup']);assert.equal(Object.isFrozen(result),true);
});
test('invalid admission stops before callbacks and does not claim owned cleanup',async()=>{
 for(const mutate of [v=>v.profile.model='owned-http-l714-v1',v=>v.storage.existing_application=true,v=>v.identity.head='invalid']){
  const {io,input,calls}=fixture();mutate(input);await assert.rejects(prepareInitializedWorkerController(io,input));assert.deepEqual(calls,[]);
 }
 const {io,input,calls,setTime}=fixture();setTime(450001);await assert.rejects(prepareInitializedWorkerController(io,input),/reserve/);assert.deepEqual(calls,[]);
 const broken=fixture();broken.io.now=()=>{throw Error('PRIVATE clock failure');};await assert.rejects(prepareInitializedWorkerController(broken.io,broken.input),{message:'Initialized preparation failed: clock'});assert.deepEqual(broken.calls,[]);
});
test('worker availability, callback closure and exact role/cohort/credit are mandatory before baseline',async()=>{
 for(const mutate of [()=>null,r=>({...r,event:'http-case-complete'}),r=>({...r,callbacks_closed:false}),r=>({...r,runtime_grant:true}),r=>({...r,extra:true}),r=>({...r,completion:{...r.completion,role:'installer'}}),r=>({...r,completion:{...r.completion,head:'0'.repeat(40)}}),r=>({...r,completion:{...r.completion,sql_remaining:1024}}),r=>({...r,completion:{...r.completion,protected_reads:1}})]){
  const {io,input,calls}=fixture(),initialize=io.initialize;io.initialize=async(...args)=>mutate(await initialize(...args));
  await assert.rejects(prepareInitializedWorkerController(io,input),/initializer/);assert.deepEqual(calls,['install','initialize','cleanup']);
 }
});
test('failures preserve the first safe stage and still attempt owned cleanup exactly once',async()=>{
 for(const method of ['install','initialize','reconcile','baseline']){
  const {io,input,calls}=fixture();io[method]=async()=>{calls.push(method);throw Error('PRIVATE raw SQL/secret');};
  await assert.rejects(prepareInitializedWorkerController(io,input),error=>{assert.match(error.message,/Initialized preparation failed:/);assert.doesNotMatch(error.message,/PRIVATE/);return true;});
  assert.equal(calls.filter(k=>k==='cleanup').length,1);
 }
 const {io,input}=fixture();io.initialize=async()=>{throw Error('PRIVATE');};io.cleanup=async()=>{throw Error('PRIVATE cleanup');};
 await assert.rejects(prepareInitializedWorkerController(io,input),e=>e instanceof AggregateError&&e.errors[0].message==='Initialized preparation failed: initializer'&&e.errors[1].message==='Initialized preparation failed: cleanup-unknown');
});
test('late worker/release and false or expired cleanup cannot produce a ready handoff',async()=>{
 for(const method of ['initialize','reconcile']){
  const {io,input,calls,setTime}=fixture(),original=io[method];io[method]=async(...args)=>{const row=await original(...args);setTime(60000);return row;};
  await assert.rejects(prepareInitializedWorkerController(io,input));assert.equal(calls.at(-1),'cleanup');assert.equal(calls.includes('baseline'),false);
 }
 for(const mutate of [r=>({...r,sessions:1}),r=>({...r,ownership:false}),r=>({...r,head:'0'.repeat(40)}),()=>null]){
  const {io,input}=fixture(),cleanup=io.cleanup;io.cleanup=async(...args)=>mutate(await cleanup(...args));await assert.rejects(prepareInitializedWorkerController(io,input),/cleanup-unknown/);
 }
 const {io,input,setTime}=fixture(),cleanup=io.cleanup;io.cleanup=async(...args)=>{const row=await cleanup(...args);setTime(30400);return row;};await assert.rejects(prepareInitializedWorkerController(io,input),/cleanup-unknown/);
});
test('worker receipt/release/cleanup omission controls fail behavioral assertions',async()=>{
 const source=readFileSync(new URL('../../scripts/ci/source-policy-http-initialized-controller.mjs',import.meta.url),'utf8');
 const controls=[
  ['packet.callbacks_closed===true','true',io=>{const original=io.initialize;io.initialize=async(...args)=>({...await original(...args),callbacks_closed:false});}],
  ['model.released(await callbacks.reconcile(binding,remaining(setupDeadline)),now());','model.released({...binding,role:"initializer-release",exited:true,sessions:0,transactions:0,anchors_unchanged:true,source_unchanged:true},now());',io=>{io.reconcile=async()=>{throw Error('missing-independent-release');};}],
  ['cleanupMatches(cleanup,id)','true',io=>{io.cleanup=async()=>({...f.cleanup(),sessions:1});}],
 ];
 for(const [before,after,mutate] of controls){
  assert.equal(source.split(before).length,2,before);let changed=source.replace(before,after);
  for(const file of ['source-policy-http-profile.mjs','source-policy-http-initialization.mjs'])changed=changed.replace("'./"+file+"'",JSON.stringify(new URL('../../scripts/ci/'+file,import.meta.url).href));
  const api=await import('data:text/javascript;base64,'+Buffer.from(changed).toString('base64')),{io,input}=fixture();mutate(io);
  await assert.rejects(async()=>assert.rejects(api.prepareInitializedWorkerController(io,input)),{name:'AssertionError'});
 }
});
