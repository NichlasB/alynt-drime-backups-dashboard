import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {HttpDataLifecycle} from '../../scripts/ci/source-policy-http-lifecycle.mjs';
import * as f from './source-policy-http-fixtures.mjs';
const base=new URL('../../scripts/ci/',import.meta.url);
const sources=Object.fromEntries(['profile','contract','lifecycle'].map(name=>[name,readFileSync(new URL('source-policy-http-'+name+'.mjs',base),'utf8')]));
function prepare(Class=HttpDataLifecycle){const m=new Class(f.identity(),0,f.challenge);m.admit(f.profile(),f.storage(),f.manifest(),f.manifestExpectation(),1);m.setup(f.ledger('setup'),2);m.server(f.server(),f.profile(),3);return m;}
async function killed(name,from,to,oracle){
 const original=sources[name],changed=original.replace(from,to);assert.notEqual(changed,original,'Control target must change actual source');
 const code=changed.replaceAll("'./source-policy-http-profile.mjs'",JSON.stringify(new URL('source-policy-http-profile.mjs',base).href)).replaceAll("'./source-policy-http-contract.mjs'",JSON.stringify(new URL('source-policy-http-contract.mjs',base).href));
 // Parsing/import/setup failures occur outside the semantic-kill capture.
 const namespace=await import('data:text/javascript;base64,'+Buffer.from(code).toString('base64'));
 let failure;try{oracle(namespace);}catch(error){failure=error;}
 assert.equal(failure?.code,'ERR_ASSERTION','Actual guard bypass must fail an assertion');
 const valueMismatch=failure.operator==='strictEqual'&&((typeof failure.actual==='boolean'&&typeof failure.expected==='boolean')||(['login-form','login-submit','view','submit','nonce-control'].includes(failure.actual)&&failure.expected===false));
 assert.ok(valueMismatch||(failure.operator==='throws'&&failure.actual===undefined),'Only bounded boolean/route-enum or missing-guard failures count, not unexpected exceptions');
 assert.equal(readFileSync(new URL('source-policy-http-'+name+'.mjs',base),'utf8'),original,'Working source remains unchanged');
}
test('actual DATA guards and required availability are assertion-killed by bounded RAM source controls',async()=>{
 await killed('profile','Object.keys(HTTP_PROFILE).every(k=>row[k]===HTTP_PROFILE[k])','true',m=>{assert.equal(m.profileMatches(f.profile()),true);assert.equal(m.profileMatches({...f.profile(),workers:2}),false);});
 await killed('profile','keys.every(k=>row[k]===expected[k])','true',m=>{assert.equal(m.storageMatches(f.storage(),f.identity()),true);assert.equal(m.storageMatches({...f.storage(),options:'wp_options'},f.identity()),false);});
 await killed('profile','actual.digest===expected.digest','true',m=>{assert.equal(m.manifestMatches(f.manifest(),f.manifestExpectation()),true);assert.equal(m.manifestMatches(f.manifest(),{...f.manifestExpectation(),digest:'0'.repeat(64)}),false);});
 await killed('contract','row.challenge!==challenge||','',m=>{assert.equal(m.route(f.meta(),f.challenge),'submit');assert.equal(m.route({...f.meta(),challenge:f.run},f.challenge),false);});
 await killed('contract',"row.target===BRIDGE+'?control=wrong-action'?'nonce-control':false","row.target===BRIDGE+'?control=wrong-action'?'nonce-control':'view'",m=>assert.equal(m.route({...f.meta('view'),target:'/wp-config.php'},f.challenge),false));
 await killed('contract','row.policy_reads===0&&row.policy_writes===0','true',m=>assert.equal(m.ledgerMatches({...f.ledger(),policy_reads:1},'bootstrap'),false));
 await killed('contract','row.records!==n||row.connects!==n','row.records!==n',m=>assert.equal(m.caseMatches({...f.result(4),connects:1},f.expected(4),4),false));
 await killed('contract','export function caseMatches(value,expected,index){','export function caseMatches(value,expected,index){return false;',m=>assert.equal(m.caseMatches(f.result(1),f.expected(1),1),true));
});
test('actual lifecycle release, budget, deadline and cleanup guards have independent semantic kills',async()=>{
 await killed('lifecycle','row.pid===this.#pid&&','',m=>{const model=prepare(m.HttpDataLifecycle);model.begin(f.meta('view'),'view',0,4);model.receive(f.response(),f.result(0),f.expected(0),f.ledger(),f.ledger('auth'),5);assert.throws(()=>model.release({...f.release(),pid:124},6));});
 await killed('lifecycle','this.#transactions<LIMITS.transactions&&','',m=>{const model=prepare(m.HttpDataLifecycle);for(let i=0;i<64;i++){const at=4+i*3;model.begin(f.meta('login-form'),'login-form',null,at);model.receive(f.response(),null,null,f.ledger(),f.ledger('auth'),at+1);model.release(f.release(),at+2);}assert.throws(()=>model.begin(f.meta('view'),'view',0,200));});
 await killed('lifecycle','at-this.#started<LIMITS.http_ms','true',m=>{const model=prepare(m.HttpDataLifecycle);assert.throws(()=>model.begin(f.meta('view'),'view',0,60003));});
 await killed('lifecycle','integer(at,LIMITS.job_ms-1)&&at>=this.#last','integer(at,Number.MAX_SAFE_INTEGER)&&at>=this.#last',m=>{const model=new m.HttpDataLifecycle(f.identity(),599999,f.challenge);assert.throws(()=>model.admit(f.profile(),f.storage(),f.manifest(),f.manifestExpectation(),600000));});
 await killed('lifecycle','row.ownership===true&&','',m=>{const model=prepare(m.HttpDataLifecycle);model.fail(4);assert.throws(()=>model.close({...f.cleanup(),ownership:false},5));});
 await killed('lifecycle','this.#policy===null||result.before===this.#policy','true',m=>{
  const model=prepare(m.HttpDataLifecycle);for(let i=0;i<2;i++){const at=4+i*3;model.begin(f.meta(i===0?'view':'submit'),i===0?'view':'submit',i,at);model.receive(f.response(),f.result(i),f.expected(i),f.ledger(),f.ledger('auth'),at+1);model.release(f.release(),at+2);}
  model.begin(f.meta(),'submit',2,10);assert.throws(()=>model.receive(f.response(),{...f.result(2),before:f.digest,after:f.digest},{...f.expected(2),before:f.digest,after:f.digest},f.ledger(),f.ledger('auth'),11));
 });
 await killed('lifecycle','this.#sentinel===null||result.sentinel===this.#sentinel','true',m=>{
  const model=prepare(m.HttpDataLifecycle);model.begin(f.meta('view'),'view',0,4);model.receive(f.response(),f.result(0),f.expected(0),f.ledger(),f.ledger('auth'),5);model.release(f.release(),6);model.begin(f.meta(),'submit',1,7);
  assert.throws(()=>model.receive(f.response(),{...f.result(1),sentinel:f.digest},{...f.expected(1),sentinel:f.digest},f.ledger(),f.ledger('auth'),8));
 });
 await killed('lifecycle',"['login-form','login-submit','view','submit','nonce-control'].includes(kind)&&",'',m=>{const model=prepare(m.HttpDataLifecycle);assert.throws(()=>model.begin({...f.meta(),target:'/wp-config.php'},false,null,4));});
 await killed('lifecycle',"index===null?kind!=='submit':",'index===null?true:',m=>{const model=prepare(m.HttpDataLifecycle);assert.throws(()=>model.begin(f.meta(),'submit',null,4));});
 await killed('lifecycle','result.bootstrap===bootstrap.sql&&result.auth===auth.sql','true',m=>{const model=prepare(m.HttpDataLifecycle);model.begin(f.meta('view'),'view',0,4);assert.throws(()=>model.receive(f.response(),{...f.result(0),bootstrap:99},f.expected(0),f.ledger(),f.ledger('auth'),5));});
 await killed('lifecycle','viewObservationMatches(result,expected)','true',m=>{const model=prepare(m.HttpDataLifecycle);model.begin(f.meta('view'),'view',null,4);assert.throws(()=>model.receive(f.response(),{...f.result(0),event:'http-view-complete',index:null,policy_writes:1},{...f.expected(0),mode:'required'},f.ledger(),f.ledger('auth'),5));});
});
test('dormant imports contain no executor/IO adapter or caller and old CLI/production entry stays unchanged',()=>{
 for(const source of Object.values(sources)){
  assert.doesNotMatch(source,/from ['"]node:(?:fs|child_process|net|http|https|worker_threads)|process\.(?:env|argv)|fetch\(|setTimeout\(|Date\.now\(/);
 }
 const main=readFileSync(new URL('../../alynt-drime-backups-dashboard.php',import.meta.url),'utf8');assert.doesNotMatch(main,/source-policy-http-/);
 const child=readFileSync(new URL('../../scripts/ci/source-policy-wl-request-child.php',import.meta.url),'utf8');assert.ok(child.includes("PHP_SAPI !== 'cli'"));assert.doesNotMatch(child,/source-policy-http-/);
});
