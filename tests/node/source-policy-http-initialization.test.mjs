import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {createHash} from 'node:crypto';
import {INITIALIZED_HTTP_PROFILE,INITIALIZATION_EXECUTION_ENABLED,InitializedFixtureData,prepareInitializedFixture} from '../../scripts/ci/source-policy-http-initialization.mjs';
import {HTTP_PROFILE,profileMatches,manifestSummary} from '../../scripts/ci/source-policy-http-profile.mjs';
import {identity,storage,manifest} from './source-policy-http-fixtures.mjs';

const anchors='8'.repeat(64),digest='7'.repeat(64);
function input(){const rows=manifest();return {identity:identity(),profile:{...INITIALIZED_HTTP_PROFILE},storage:storage(),manifest:rows,expectation:{...manifestSummary(rows)},anchors};}
function bound(model){return {...(typeof model.binding==='function'?model.binding():model)};}
function installer(model,sql=12){return {...bound(model),role:'installer',sql,exited:true,sessions:0,transactions:0,ddl_sealed:true,fixtures_verified:true};}
function initialized(ticket,sql=3){return {...ticket,sql,full_bootstrap:true,installing:false,short_init:false,protected_reads:0,protected_writes:0,owner_allocations:0,session_writes:0,mail:0,outbound:0,file_writes:0,source_unchanged:true,anchors_unchanged:true};}
function release(model){return {...bound(model),role:'initializer-release',exited:true,sessions:0,transactions:0,anchors_unchanged:true,source_unchanged:true};}
function baseline(model){return {...bound(model),role:'full-invariant',digest,all_options:true,all_cron:true,all_widgets:true,all_transients:true,non_session_usermeta:true};}
function ready(){const model=new InitializedFixtureData(input(),0);model.installed(installer(model),10);const ticket=model.begin(20);model.initialized(initialized(ticket),30);model.released(release(model),40);model.freeze(baseline(model),50);return model;}

test('v2 is a distinct dormant DATA contract, not an accepted v1 native profile',()=>{
 assert.equal(INITIALIZATION_EXECUTION_ENABLED,false);assert.equal(profileMatches(INITIALIZED_HTTP_PROFILE),false);
 assert.equal(INITIALIZED_HTTP_PROFILE.model,'owned-http-l714-initialized-v2');
 assert.deepEqual({...INITIALIZED_HTTP_PROFILE,model:HTTP_PROFILE.model},HTTP_PROFILE);
 const result=ready().result();assert.equal(result.state,'ready');assert.equal(result.total_setup_sql,15);assert.equal(result.baseline,digest);
 assert.equal(result.runtime_grant,false);assert.equal(result.production_ready,false);assert.equal(result.http_qualified,false);assert.equal(result.isolated_only,true);
 assert.equal(Object.isFrozen(result),true);
});

test('the isolated composition invokes exactly one initializer, release and full baseline in order',async()=>{
 const calls=[];let clock=0,model;
 const io={kind:'isolated-initialization-io-v2',now:()=>++clock,
  install:m=>{model=m;assert.equal(m.installed,undefined);assert.equal(Object.isFrozen(m),true);calls.push('install');return installer(m);},
  initialize:t=>{calls.push('initialize');assert.equal(t.sql_remaining,1012);assert.equal(t.setup_deadline,60001);return initialized(t);},
  reconcile:()=>{calls.push('release');return release(model);},
  baseline:()=>{calls.push('baseline');return baseline(model);}};
 const result=await prepareInitializedFixture(io,input());
 assert.equal(result.state,'ready');assert.deepEqual(calls,['install','initialize','release','baseline']);
});

test('malformed profiles, getters, unknown fields and stale source identity refuse before callbacks',async()=>{
 for(const mutate of [v=>v.profile.model=HTTP_PROFILE.model,v=>v.identity.head='x',v=>v.storage.existing_application=true,v=>v.expectation.digest=digest,v=>v.extra=1,v=>Object.defineProperty(v,'anchors',{get(){throw Error('getter-ran');}})]){
  const value=input();mutate(value);let calls=0;
  await assert.rejects(prepareInitializedFixture({kind:'isolated-initialization-io-v2',now:()=>0,install:()=>calls++,initialize:()=>calls++,reconcile:()=>calls++,baseline:()=>calls++},value),/initialization-admission/);assert.equal(calls,0);
 }
});

test('skipped initialization, second child, replay and early baseline poison the model',()=>{
 for(const attempt of [m=>m.begin(1),m=>m.freeze(baseline(m),1),m=>m.released(release(m),1),m=>m.initialized({},1)]){
  const m=new InitializedFixtureData(input(),0);assert.throws(()=>attempt(m));assert.equal(m.result().state,'failed');assert.throws(()=>m.installed(installer(m),2));
 }
 const m=new InitializedFixtureData(input(),0);m.installed(installer(m),1);m.begin(2);assert.throws(()=>m.begin(3),/initialization-order/);
 const done=ready();assert.throws(()=>done.begin(60));assert.equal(done.result().state,'failed');
});

test('initializer receipts bind role, version, cohort, anchors and unreset shared credit',()=>{
 for(const [key,value] of Object.entries({role:'installer',model:HTTP_PROFILE.model,run:'0'.repeat(32),head:'0'.repeat(40),image:'0'.repeat(64),owner:'0'.repeat(64),manifest:digest,anchors:digest,setup_sql_used:0,sql_remaining:1024,setup_deadline:60001,sql:1013,full_bootstrap:false,installing:true,short_init:true,protected_reads:1,protected_writes:1,owner_allocations:1,session_writes:1,mail:1,outbound:1,file_writes:1,source_unchanged:false,anchors_unchanged:false})){
  const m=new InitializedFixtureData(input(),0);m.installed(installer(m),1);const ticket=m.begin(2),packet=initialized(ticket);packet[key]=value;
  assert.throws(()=>m.initialized(packet,3),/initialization-receipt/,key);assert.equal(m.result().state,'failed');
 }
});

test('shared deadlines and credit do not restart at child boundaries',()=>{
 const m=new InitializedFixtureData(input(),100);m.installed(installer(m,1023),60098);const ticket=m.begin(60099);
 assert.equal(ticket.sql_remaining,1);assert.equal(ticket.setup_deadline,60100);
 assert.throws(()=>m.initialized(initialized(ticket,1),60100),/initialization-time/);
 for(const sql of [0,1024,1025,-1,1.5,NaN]){const n=new InitializedFixtureData(input(),0);assert.throws(()=>n.installed(installer(n,sql),1));}
 const n=new InitializedFixtureData(input(),0);n.installed(installer(n),3);assert.throws(()=>n.begin(2),/initialization-time/);
});

test('release must precede an immutable complete baseline, with no namespace exclusions',()=>{
 for(const [key,value] of Object.entries({exited:false,sessions:1,transactions:1,anchors_unchanged:false,source_unchanged:false,role:'installer',head:'0'.repeat(40)})){
  const m=new InitializedFixtureData(input(),0);m.installed(installer(m),1);m.initialized(initialized(m.begin(2)),3);const row=release(m);row[key]=value;assert.throws(()=>m.released(row,4));
 }
 for(const key of ['all_options','all_cron','all_widgets','all_transients','non_session_usermeta']){
  const m=new InitializedFixtureData(input(),0);m.installed(installer(m),1);m.initialized(initialized(m.begin(2)),3);m.released(release(m),4);const row=baseline(m);row[key]=false;assert.throws(()=>m.freeze(row,5));
 }
});

test('unknown/failed initializer never obtains baseline or ready credit',async()=>{
 for(const mode of ['throw','unavailable','expired']){
  let baselineCalls=0,releaseCalls=0,clock=0;
  await assert.rejects(prepareInitializedFixture({kind:'isolated-initialization-io-v2',now:()=>clock++,install:m=>installer(m),initialize:()=>{if(mode==='throw')throw Error('owned-test-failure');if(mode==='expired')clock=60000;return null;},reconcile:()=>releaseCalls++,baseline:()=>baselineCalls++},input()));
  assert.equal(baselineCalls,0);assert.equal(releaseCalls,0);
 }
});

test('all original native callers remain covered: SQL identity and explicit v2 gates, no implicit activation',()=>{
 const root=new URL('../../',import.meta.url);
 const hashes={
  'scripts/ci/source-policy-http-owned-observer.php':'03792be06bfed2ddb0962ab50cc37d5e2e09fc5082b14d9ecf5fddee19e87418',
 };
 for(const [path,hash] of Object.entries(hashes))assert.equal(createHash('sha256').update(readFileSync(new URL(path,root),'utf8').replace(/\r\n/g,'\n')).digest('hex'),hash);
 for(const path of ['scripts/ci/source-policy-http-owned-controller.mjs','scripts/ci/source-policy-http-owned-worker.php','scripts/ci/source-policy-http-owned-setup.php','.github/workflows/source-policy-minimum-qualification.yml']){
  const source=readFileSync(new URL(path,root),'utf8');
  // The user now authorized explicit native integration. Preserve every file in
  // this guard's coverage; replace the historical absence expectation with the
  // actual default-off/version binding tested in initialized-native.test.mjs.
  if(path.endsWith('owned-controller.mjs')){
   const admission=source.indexOf('const profile=httpExecutionProfile(process.env)');assert.ok(admission>0&&admission<source.indexOf('const owner=createOwnedStage'));
   assert.match(source,/initialized\?INITIALIZED_HTTP_PROFILE:HTTP_PROFILE/);
   assert.match(source,/need\(!initialized\|\|receipt.model===profile.model,'initialized-request-model'\)/);
  }else if(path.endsWith('owned-worker.php')){
   assert.match(source,/adbh_initialized_model\( \$p, getenv\( 'ADB_HTTP_INITIALIZED_APPROVED' \) \)/);
   assert.match(source,/adbh_check\( false !== \$model, 'owned-worker-model' \)/);
   assert.match(source,/'setup' === \$purpose && 'owned-http-l714-initialized-v2' === \$model/);
  }else assert.doesNotMatch(source,/source-policy-http-initialization|owned-http-l714-initialized-v2/);
 }
 const installed=readFileSync(new URL('alynt-drime-backups-dashboard.php',root),'utf8');assert.doesNotMatch(installed,/source-policy-http-/);
});

test('five semantic controls fail assertions instead of passing on unavailable receipts',async()=>{
 const source=readFileSync(new URL('../../scripts/ci/source-policy-http-initialization.mjs',import.meta.url),'utf8');
 const dependency=new URL('../../scripts/ci/source-policy-http-profile.mjs',import.meta.url).href;
 const controls=[
  ["model.initialized(await callbacks.initialize(ticket),callbacks.now());","model.initialized(null,callbacks.now());",async api=>{let calls=0,model;await api.prepareInitializedFixture({kind:'isolated-initialization-io-v2',now:()=>0,install:m=>{model=m;return installer(m);},initialize:t=>{calls++;return initialized(t);},reconcile:()=>release(model),baseline:()=>baseline(model)},input());assert.equal(calls,1);} ],
  ["this.#state===state","true",api=>{const m=new api.InitializedFixtureData(input(),0);assert.throws(()=>m.begin(1));}],
  ['protected_reads:0','protected_reads:1',api=>{const m=new api.InitializedFixtureData(input(),0);m.installed(installer(m),1);const row=initialized(m.begin(2));row.protected_reads=1;assert.throws(()=>m.initialized(row,3));}],
  ['LIMITS.setup_sql-this.#installed','LIMITS.setup_sql',api=>{const m=new api.InitializedFixtureData(input(),0);m.installed(installer(m),1);assert.equal(m.begin(2).sql_remaining,1012);}],
  ['at<this.#start+LIMITS.setup_ms','true',api=>{const m=new api.InitializedFixtureData(input(),0);assert.throws(()=>m.installed(installer(m),60000));}],
 ];
 for(const [before,after,oracle] of controls){
  assert.equal(source.split(before).length,2,before);
  const mutant=source.replace(before,after).replace("'./source-policy-http-profile.mjs'",JSON.stringify(dependency));
  const api=await import('data:text/javascript;base64,'+Buffer.from(mutant).toString('base64'));
  // The first oracle requires the positive callback path, not just any rejection.
  if(before.startsWith('model.initialized'))await assert.rejects(oracle(api),/initialization-receipt/);
  else await assert.rejects(async()=>oracle(api),{name:'AssertionError'});
 }
});
