import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {preparedHttpBatch,HTTP_FLOW} from '../../scripts/ci/source-policy-http-supervisor.mjs';
import * as f from './source-policy-http-fixtures.mjs';
const input=()=>({identity:f.identity(),profile:f.profile(),storage:f.storage(),manifest:f.manifest(),expectation:f.manifestExpectation(),challenge:f.challenge});
function fixture(scenario='positive'){
 let clock=0,policy=f.digest,last=null;const seen={prepare:0,start:0,live:0,exchange:0,reconcile:0,cleanup:0,budgets:[],order:[]};
 const io={kind:'isolated-http-io-v1',now:()=>clock,
  prepare:async left=>{seen.prepare++;seen.order.push('prepare');assert.ok(left>0&&left<=60000);clock++;if(scenario==='prepare-throw')throw Error('owned-private setup secret');return f.ledger('setup');},
  start:async left=>{seen.start++;seen.order.push('start');assert.ok(left>0&&left<=5000);clock++;return{server:{...f.server(),...(scenario==='server-image'?{image:'0'.repeat(64)}:{})},profile:f.profile()};},
  live:async left=>{seen.live++;assert.ok(left>0&&left<=60000);return{...f.server(),...(scenario==='dead'?{alive:false}:scenario==='live-pid'?{pid:124}:{})};},
  describe:async(item,left)=>{assert.ok(left>0&&left<=60000);return{...f.meta(item.kind),...(scenario==='challenge'?{challenge:f.run}:{})};},
  exchange:async(item,left)=>{
   seen.exchange++;seen.order.push('exchange');seen.budgets.push(['exchange',left]);assert.ok(left>0&&left<=5000);last=item;
   clock+=scenario==='deadline'?5000:scenario==='slow'?4000:2;
   if(scenario==='exchange-throw')throw Error('owned-private cookie/password');
   const observed=item.index!==null||['view','nonce-control'].includes(item.kind);
   let result=null,expected=null;
   if(observed){
    if(item.index!==null){result=f.result(item.index);expected=f.expected(item.index);policy=result.after;}
    else{expected={...f.identity(),before:policy,after:policy,sentinel:'f'.repeat(64),revision:'2'.repeat(64),mode:'external_optional'};result={...f.result(0),event:'http-view-complete',index:null,before:policy,after:policy,revision:expected.revision,mode:expected.mode};}
    if(scenario==='unavailable')result={...result,result:'source_policy_unavailable'};
    if(scenario==='auxiliary-write'&&item.index===null)result={...result,policy_writes:1};
   }
   const envelope={...f.response(),...(item.kind==='login-submit'?{status:302,location:'http://127.0.0.1:18777/_adb-owned-source-policy'}:result?{status:result.status}:{})};
   if(scenario==='eof')envelope.eof=false;
   return{envelope,result,expected,bootstrap:f.ledger(),auth:f.ledger('auth')};
  },
  reconcile:async left=>{seen.reconcile++;seen.order.push('release');seen.budgets.push(['release',left]);assert.ok(left>0&&left<=5000);clock++;return{...f.release(),...(scenario==='sessions'?{sessions:1}:scenario==='wrong-scope'?{scope:'owned-db'}:{})};},
  cleanup:async left=>{seen.cleanup++;seen.order.push('cleanup');assert.ok(left>=0&&left<=30000);clock++;if(scenario==='cleanup-throw')throw Error('owned-private cleanup');return{...f.cleanup(),...(scenario==='cleanup-unknown'?{files:1}:{})};}
 };
 return{io,seen,last:()=>last};
}
async function success(batch=preparedHttpBatch){
 const {io,seen}=fixture();const result=await batch(io,input());
 assert.equal(result.event,'http-preparation-closed');assert.equal(result.completed,true);assert.equal(result.cases,13);assert.equal(result.transactions,25);
 assert.equal(result.runtime_grant,false);assert.equal(result.production_ready,false);assert.equal(result.qualification,'not-established');assert.equal(result.isolated_only,true);
 assert.deepEqual([seen.prepare,seen.start,seen.exchange,seen.reconcile,seen.live,seen.cleanup],[1,1,25,25,50,1]);
 assert.equal(seen.order.at(-1),'cleanup');for(let i=2;i<seen.order.length-1;i+=2)assert.deepEqual(seen.order.slice(i,i+2),['exchange','release']);
}
test('fixed finite schedule distinguishes actual-jar/revision intents without minting any auth DATA',()=>{
 assert.equal(HTTP_FLOW.length,25);assert.ok(Object.isFrozen(HTTP_FLOW)&&HTTP_FLOW.every(Object.isFrozen));
 assert.deepEqual(HTTP_FLOW.filter(s=>s.index!==null).map(s=>s.index),Array.from({length:13},(_,i)=>i));
 const intent=index=>HTTP_FLOW.find(s=>s.index===index);
 assert.equal(intent(4).jar,'anonymous');assert.equal(intent(5).jar,'incapable');assert.equal(intent(1).intent,'initial-revision');assert.equal(intent(2).intent,'fresh-revision');assert.equal(intent(3).intent,'stale-revision');
 assert.equal(intent(8).intent,'wrong-user');assert.equal(intent(9).intent,'wrong-session');
 assert.ok(HTTP_FLOW.some(s=>s.kind==='view'&&s.jar==='other'));assert.ok(HTTP_FLOW.some(s=>s.kind==='view'&&s.jar==='second'));
});
test('preparation ships no launcher/network/bootstrap adapter or production/workflow caller',()=>{
 const read=file=>readFileSync(new URL('../../'+file,import.meta.url),'utf8');
 assert.doesNotMatch(read('scripts/ci/source-policy-http-supervisor.mjs'),/node:(?:fs|child_process|http|https|net)|process\.(?:env|argv)|fetch\(|setTimeout\(/);
 assert.doesNotMatch(read('scripts/ci/source-policy-http-bridge.php'),/\b(?:require|include)(?:_once)?\s*\(?\s*['"]|\$_(?:POST|GET|COOKIE|SERVER)|\b(?:add_action|add_filter|wp_set_current_user|update_option|mysqli_connect)\s*\(/);
 for(const file of ['alynt-drime-backups-dashboard.php','.github/workflows/ci.yml','.github/workflows/source-policy-linux-qualification.yml','.github/workflows/source-policy-minimum-distribution.yml'])assert.doesNotMatch(read(file),/source-policy-http-/);
});
test('actual preparation supervisor completes all cases only through live release and owned cleanup DATA',()=>success());
test('exact profile admission precedes every IO effect and does not treat callback labels as authority',async()=>{
 const {io,seen}=fixture();await assert.rejects(()=>preparedHttpBatch(io,{...input(),profile:{...f.profile(),sapi:'fpm-fcgi'}}));assert.equal(seen.prepare,0);assert.equal(seen.start,0);assert.equal(seen.cleanup,0);
 await assert.rejects(()=>preparedHttpBatch({...io,kind:'native'},input()));assert.equal(seen.prepare,0);
});
test('unavailable/dead/foreign/framing/eof/denial-accounting failures stop before another operation',async()=>{
 for(const scenario of ['server-image','dead','live-pid','challenge','unavailable','auxiliary-write','eof','sessions','wrong-scope','deadline','exchange-throw','prepare-throw']){
  const {io,seen}=fixture(scenario);await assert.rejects(()=>preparedHttpBatch(io,input()),error=>{assert.match(error.message,/^HTTP preparation failed: /);assert.doesNotMatch(error.message,/owned-private|cookie|password/);return true;});
  assert.ok(seen.exchange<25,scenario);assert.equal(seen.cleanup,1,scenario);
  if(['dead','live-pid','challenge','server-image'].includes(scenario))assert.equal(seen.exchange,0,scenario);
 }
});
test('remaining request credit includes exchange plus reconciliation; no fresh timeout at release',async()=>{
 const {io,seen}=fixture('slow');await assert.rejects(()=>preparedHttpBatch(io,input()));
 const first=seen.budgets.slice(0,2);assert.deepEqual(first,[['exchange',5000],['release',1000]]);assert.equal(seen.cleanup,1);
});
test('original safe failure stage is retained when cleanup also fails; unknown cleanup is never success',async()=>{
 for(const scenario of ['cleanup-unknown','cleanup-throw']){const {io,seen}=fixture(scenario);await assert.rejects(()=>preparedHttpBatch(io,input()),/cleanup-unknown/);assert.equal(seen.cleanup,1);}
 const {io,seen}=fixture('exchange-throw');io.cleanup=async()=>{seen.cleanup++;throw Error('owned-private second error');};
 await assert.rejects(()=>preparedHttpBatch(io,input()),error=>{assert.ok(error instanceof AggregateError);assert.deepEqual(error.errors.map(e=>e.message),['HTTP preparation failed: request','HTTP preparation failed: cleanup-unknown']);assert.doesNotMatch(JSON.stringify(error.errors.map(e=>e.message)),/owned-private/);return true;});assert.equal(seen.cleanup,1);
});
test('supervisor source controls kill removed live/reconciliation/cleanup and required-positive guards',async()=>{
 const path=new URL('../../scripts/ci/source-policy-http-supervisor.mjs',import.meta.url),original=readFileSync(path,'utf8');
 for(const[from,to]of [
  ["check(sameServer(await io.live(remaining(httpDeadline)),server),'live');",''],
  ['model.release(release,io.now());','model.release({...release,sessions:0},io.now());'],
  ['terminal=model.close(await io.cleanup(Math.max(0,Math.min(LIMITS.cleanup_ms,LIMITS.job_ms-io.now()))),io.now());','terminal=model.close({...await io.cleanup(30000),files:0},io.now());'],
  ["check(terminal?.completed===true,'incomplete');","check(false,'incomplete');"]
 ]){
  let code=original.replace(from,to);assert.notEqual(code,original);
  code=code.replaceAll("'./source-policy-http-lifecycle.mjs'",JSON.stringify(new URL('../../scripts/ci/source-policy-http-lifecycle.mjs',import.meta.url).href)).replaceAll("'./source-policy-http-profile.mjs'",JSON.stringify(new URL('../../scripts/ci/source-policy-http-profile.mjs',import.meta.url).href));
  const {preparedHttpBatch:mutant}=await import('data:text/javascript;base64,'+Buffer.from(code).toString('base64'));
  let killed=false;try{
   if(from.startsWith('check(terminal'))await assert.doesNotReject(()=>success(mutant));
   else{const scenario=from.startsWith('check(sameServer')?'dead':from.startsWith('model.release')?'sessions':'cleanup-unknown';const{io,seen}=fixture(scenario);await assert.rejects(()=>mutant(io,input()));if(scenario==='dead')assert.equal(seen.exchange,0);}
  }catch(error){assert.equal(error.code,'ERR_ASSERTION');killed=true;}
  assert.equal(killed,true,'Control must fail a semantic assertion, not import/setup');assert.equal(readFileSync(path,'utf8'),original);
 }
});
