import test from 'node:test';
import assert from 'node:assert/strict';
import {createHash} from 'node:crypto';
import {readFileSync} from 'node:fs';
import * as D from '../../scripts/ci/source-policy-ppa-stock-charset-data.mjs';
import * as P from '../../scripts/ci/source-policy-ppa-stock-charset-projections.mjs';
import * as C from '../../scripts/ci/source-policy-ppa-stock-charset-catalog.mjs';
import * as V1 from '../../scripts/ci/source-policy-ppa-http-condition-data.mjs';
import {CONDITION_CATALOG,CONDITION_CATALOG_DIGEST} from '../../scripts/ci/source-policy-ppa-http-condition-catalog.mjs';
import {consumerRequirements,validateConsumerData} from '../../scripts/ci/source-policy-ppa-function-data.mjs';
const root=new URL('../../',import.meta.url),copy=v=>JSON.parse(JSON.stringify(v));
const sha=v=>createHash('sha256').update(JSON.stringify(v)).digest('hex');
const row={id:'17',key:'blog_charset',value:'UTF-8',autoload:'on'};
const charset={model:'ppa-json-charset-row-v2',digest:sha({model:'ppa-json-charset-row-v2',row})};
const anchor={model:'opaque-parent-initialization-anchor-data-v2',digest:'e'.repeat(64)};
const invariant={model:'opaque-parent-full-invariant-data-v1',digest:'f'.repeat(64)};
const baseline=(at=90)=>({model:'ppa-stock-charset-baseline-data-v2',at,charset:copy(charset),anchor:copy(anchor),invariant:copy(invariant)});
const hooks=()=>['pre_option_blog_charset','pre_option','default_option_blog_charset','option_blog_charset',
  'pre_wp_load_alloptions','pre_cache_alloptions','alloptions','all','wp_autoload_values_to_autoload'].map(name=>({name,callbacks:name!=='option_blog_charset'?[]:[
    {name:'_wp_specialchars',priority:10,accepted_args:1,source:'core/wp-includes/formatting.php',digest:'350f6b32303915df970bb0e3ea360fcd54bb0b8a7ecc3d504c3d860ab9468d32'},
    {name:'_canonical_charset',priority:10,accepted_args:1,source:'core/wp-includes/functions.php',digest:'cac8a17e293df99f0683a88debd3ba5e5cce274183a4e9fd3c9f18375747f41c'},
  ]}));
const cache=()=>({alloptions:'UTF-8',options:null,notoptions:false,protected_cache:false});
function origin(role='initializer',exchange=null){return {
  model:'ppa-stock-charset-condition-data-v2',run:'a'.repeat(32),head:'b'.repeat(40),expected_head:'b'.repeat(40),image:'c'.repeat(64),
  profile:'owned-http-l714-initialized-v2',core_commit:'db96f5eaa77a8d3e8eec8923a518fa2326f0d46a',catalog:C.STOCK_CHARSET_CATALOG_DIGEST,
  role,exchange,started:100,deadline:60100,used:{setup:0,bootstrap:0,auth:0},sources:copy(C.STOCK_CHARSET_CATALOG.sources),
  baseline:role==='server'?baseline():null,
};}
function fixture(input=origin(),Module=D){
  const model=new Module.PpaStockCharsetData(input),binding=model.binding().binding;
  const phases=input.role==='installer'?['after-owned-install','after-installer-shutdown']:
    input.role==='initializer'?['before-initializer','after-initializer']:['before-request','after-request-shutdown'];
  const snapshot=phase=>({binding,phase,rows:[copy(row)],hooks:hooks(),cache:cache()});
  const before=snapshot(phases[0]),after=snapshot(phases[1]);
  const effect={binding,phase:'stock-option-call',key:'blog_charset',encoding:null,value:'UTF-8',installing:false,hooks:hooks(),cache:cache(),
    sql:{setup:0,bootstrap:0,auth:0},protected_reads:0,protected_writes:0,unknown_changes:0};
  const point=(i,at)=>({phase:phases[i],at,charset:copy(charset),anchor:copy(anchor),invariant:input.role==='server'?copy(invariant):null});
  const join={model:'ppa-charset-anchor-join-data-v2',binding,before:point(0,109),after:point(1,123),
    release:{phase:input.role==='server'?'request-stock-shutdown':'child-exit',at:122,scope:'all-server',sessions:0,transactions:0,stock_shutdown:true,source_unchanged:true,exit_code:input.role==='server'?null:0},
    baseline:input.role==='initializer'?baseline(124):input.role==='server'?baseline():null};
  return {model,input,before,after,effect,join};
}
function after(v=fixture()){v.model.before(v.before,110);v.model.effect(v.effect,115);v.model.after(v.after,120);return v;}
function complete(input=origin(),Module=D){const v=after(fixture(input,Module));v.result=v.model.join(v.join,125);return v;}
const refused=(change,stage='join',input=origin())=>{
  const v=fixture(input);change(v);
  assert.throws(()=>{v.model.before(v.before,110);if(stage==='before')return;v.model.effect(v.effect,115);if(stage==='effect')return;
    v.model.after(v.after,120);if(stage==='after')return;v.model.join(v.join,125);});
  assert.throws(()=>v.model.before(fixture(input).before,126));
};
test('stock DATA has genuine positives for three roles and25 exchanges,never native readiness or handles',()=>{
  assert.equal(D.STOCK_CHARSET_EXECUTION_ENABLED,false);assert.equal(D.STOCK_CHARSET_MODEL,'ppa-stock-charset-condition-data-v2');
  for(const input of [origin('installer'),origin(),...Array.from({length:25},(_,i)=>origin('server',i+1))]){
    const {model,result}=complete(input);assert.equal(result.stock_projection_data_complete,true);assert.equal(result.anchor_join_data_complete,true);
    assert.deepEqual(result.charset,charset);assert.equal(result.option_effect,'required-stock-read');assert.equal(result.effective_value_basis,'source-derived-DATA-only');
    assert.equal(result.hook_lifetime,'unknown');assert.equal(result.cache_reader,'unknown');
    for(const flag of ['native_admitted','runtime_grant','http_qualified','production_ready','source_observed','pre_bootstrap_checked',
      'effective_charset_observed','anchors_observed','registration_lifetime_verified','cache_projection_observed','consumer_closure_verified','dependency_closure_verified'])assert.equal(result[flag],false);
    for(const name of ['launch','execute','read','write','connect','observe','activate','cleanup'])assert.equal(model[name],undefined);
    assert.equal(result.baseline_link,input.role==='installer'?'not-yet-frozen':input.role==='initializer'?'post-initializer-DATA':'existing-frozen-DATA');
    assert.ok(Object.isFrozen(result)&&Object.isFrozen(result.charset));
  }
});
test('catalog preserves old24 identities and explicitly binds six audited source rows and exact stock callbacks',()=>{
  const c=C.STOCK_CHARSET_CATALOG;assert.equal(c.sources.length,30);assert.deepEqual(c.sources.slice(0,24),CONDITION_CATALOG.sources);
  assert.equal(c.previous_catalog,CONDITION_CATALOG_DIGEST);assert.equal(c.design_revision,'4b8030b0c43063e603883b2efc3941ec676e68e6');
  assert.equal(c.flow_digest,'403b05acb27d92390b68330625e82fc2ffbef32624c5bc698b5b768a6b85a394');assert.deepEqual(c.hooks,hooks());
  assert.equal(sha(c),C.STOCK_CHARSET_CATALOG_DIGEST);assert.notEqual(C.STOCK_CHARSET_CATALOG_DIGEST,CONDITION_CATALOG_DIGEST);
  assert.equal(C.STOCK_CHARSET_CATALOG_DIGEST,'ad26930278221754f56e43d9d1074d29a3f69b2a7c1195618e541e886a134e10');
  const design=readFileSync(new URL('docs/SOURCE_POLICY_PPA_PRIVATE_CONDITION_INTEGRATION_DESIGN.md',root),'utf8');
  const paths=['default-filters.php','plugin.php','class-wp-hook.php','class-wp-filter-sentinel.php','formatting.php','functions.php'];
  assert.deepEqual(c.sources.slice(24).map(r=>r.path),paths.map(p=>'core/wp-includes/'+p));
  for(const member of c.sources.slice(24))assert.ok(design.includes(member.digest)&&design.includes(String(member.size)));
  assert.throws(()=>c.hooks[3].callbacks.reverse());
});
test('origin rejects wrong version,source,head,role,flow exchange,credit and invented authority',()=>{
  for(const change of [o=>o.model='ppa-http-condition-data-v1',o=>o.expected_head='f'.repeat(40),o=>o.core_commit='f'.repeat(40),o=>o.catalog=CONDITION_CATALOG_DIGEST,
    o=>o.sources[29].digest='f'.repeat(64),o=>o.sources[29].size++,o=>o.sources.reverse(),o=>o.sources.pop(),o=>o.sources[0].path='core/../wp-settings.php',
    o=>o.role='setup',o=>o.exchange=1,o=>o.deadline++,o=>o.started=-1,o=>o.used.setup=1025,o=>o.used.auth=-1,o=>o.native_admitted=true]){
    const input=origin();change(input);assert.throws(()=>new D.PpaStockCharsetData(input));
  }
  for(const exchange of [0,26,'1',1.5])assert.throws(()=>new D.PpaStockCharsetData(origin('server',exchange)));
  const late=origin('server',1);late.baseline.at=101;assert.throws(()=>new D.PpaStockCharsetData(late));
});
test('all stock hook dimensions are exact,including all/autoload,at every modeled boundary',()=>{
  const changes=[h=>h.pop(),h=>h.reverse(),h=>h[0].name='foreign',h=>h[3].callbacks=[],h=>h[3].callbacks.reverse(),
    h=>h[3].callbacks[0].name='foreign',h=>h[3].callbacks[0].priority=9,h=>h[3].callbacks[0].accepted_args=2,
    h=>h[3].callbacks[0].source='core/foreign.php',h=>h[3].callbacks[0].digest='f'.repeat(64),
    h=>h[7].callbacks=[copy(h[3].callbacks[0])],h=>h[8].callbacks=[copy(h[3].callbacks[0])]];
  for(const stage of ['before','effect','after'])for(const change of changes)refused(v=>change(v[stage].hooks),stage);
});
test('cache projections distinguish entry absence,UTF-8 and notoptions/protected conflicts without readers',()=>{
  for(const stage of ['before','effect','after'])for(const change of [c=>c.alloptions='latin1',c=>c.options='utf8',c=>c.notoptions=true,
    c=>c.protected_cache=true,c=>delete c.options,c=>c.alloptions={blog_charset:'UTF-8'},c=>c.raw_options='private'])refused(v=>change(v[stage].cache),stage);
  for(const alloptions of [null,'UTF-8'])for(const options of [null,'UTF-8']){
    const v=fixture();for(const stage of ['before','effect','after'])Object.assign(v[stage].cache,{alloptions,options});
    v.effect.sql.setup=alloptions===null&&options===null?1:0;after(v);assert.equal(v.model.join(v.join,125).option_effect,'required-stock-read');
  }
  refused(v=>{v.effect.cache.alloptions=null;v.effect.cache.options=null;},'effect');
});
test('single binary row identity is fixed;missing-both,duplicates,normalization and post-state drift refuse',()=>{
  for(const change of [v=>{v.before.rows=[];v.after.rows=[];},v=>v.before.rows.push(copy(row)),v=>v.before.rows[0].id='017',
    v=>v.before.rows[0].id='18446744073709551616',v=>v.before.rows[0].key='BLOG_CHARSET',v=>v.before.rows[0].value='utf8',
    v=>v.before.rows[0].autoload='yes'])refused(change,'before');
  refused(v=>v.after.rows[0].id='18','after');
  const v=fixture();v.before.rows[0].id='18446744073709551615';assert.doesNotThrow(()=>v.model.before(v.before,110));
});
test('stock-effect scope,existing SQL domains and consumed setup credit cannot be relaxed',()=>{
  for(const change of [e=>e.phase='after-option-call',e=>e.key='home',e=>e.encoding='UTF-8',e=>e.value='latin1',e=>e.installing=true,
    e=>e.protected_reads=1,e=>e.protected_writes=1,e=>e.unknown_changes=1,e=>e.sql.auth=1,e=>e.sql.setup=1025])refused(v=>change(v.effect),'effect');
  const input=origin();input.used.setup=1024;refused(v=>v.effect.sql.setup=1,'effect',input);
  refused(v=>v.effect.sql.setup=1,'effect',origin('server',1));
});
test('typed parent anchors and row projections join only exact domains,phases,identity and released boundaries',()=>{
  for(const change of [j=>j.model='v1',j=>j.binding='f'.repeat(64),j=>j.before.charset=copy(anchor),j=>j.before.anchor=copy(charset),
    j=>j.before.charset.digest='f'.repeat(64),j=>j.after.anchor.digest='d'.repeat(64),j=>j.before.phase='before-request',
    j=>j.before.at=111,j=>j.after.at=119,j=>j.release.sessions=1,j=>j.release.transactions=1,j=>j.release.scope='child-only',
    j=>j.release.stock_shutdown=false,j=>j.release.exit_code=1,j=>j.release.source_unchanged=false,j=>j.release.at=119,
    j=>j.baseline.at=122,j=>j.baseline.charset.digest='d'.repeat(64),j=>j.baseline.anchor.digest='d'.repeat(64)])refused(v=>change(v.join));
  refused(v=>v.join.baseline=baseline(),'join',origin('installer'));
});
test('server uses the prior frozen baseline,not a new or relabeled post-request digest',()=>{
  for(const change of [v=>v.join.before.invariant.digest='d'.repeat(64),v=>v.join.after.invariant.digest='d'.repeat(64),
    v=>v.join.baseline=baseline(124),v=>v.join.before.anchor.digest='d'.repeat(64)])refused(change,'join',origin('server',1));
  refused(v=>{v.before.rows[0].id='18';v.after.rows[0].id='18';},'before',origin('server',1));
});
test('state ordering,original deadlines,uncertainty,replay and copied immutable inputs fail closed',()=>{
  const skipped=fixture();assert.throws(()=>skipped.model.effect(skipped.effect,115));assert.throws(()=>skipped.model.before(skipped.before,116));
  const backward=fixture();backward.model.before(backward.before,110);assert.throws(()=>backward.model.effect(backward.effect,109));
  const expired=fixture();assert.throws(()=>expired.model.before(expired.before,60100));
  const uncertain=after();uncertain.model.uncertain();assert.throws(()=>uncertain.model.join(uncertain.join,125));
  const done=complete();assert.throws(()=>done.model.join(done.join,126));
  const input=origin('server',1),v=fixture(input);input.baseline.invariant.digest='d'.repeat(64);input.used.setup=1024;
  assert.doesNotThrow(()=>{after(v);v.model.join(v.join,125);});assert.throws(()=>v.model.binding().catalog='f'.repeat(64));
});
test('nested getters,functions,exotic records and raw secrets are rejected without invocation',()=>{
  let calls=0;const getter=()=>{calls++;throw Error('secret');};
  const input=origin();Object.defineProperty(input.sources[0],'digest',{get:getter});assert.throws(()=>new D.PpaStockCharsetData(input));
  const v=fixture();Object.defineProperty(v.before.hooks[3].callbacks[0],'name',{get:getter});assert.throws(()=>v.model.before(v.before,110));
  const c=fixture();Object.defineProperty(c.before.cache,'alloptions',{get:getter});assert.throws(()=>c.model.before(c.before,110));
  const h=fixture();h.before.hooks[3].callbacks[0].name=getter;assert.throws(()=>h.model.before(h.before,110));
  for(const change of [p=>p.before.raw_cookie='secret',p=>Object.setPrototypeOf(p.before.rows[0],null),
    p=>Object.defineProperty(p.before.rows,'0',{get:getter}),p=>p.before.rows.push(copy(row))])refused(change,'before');
  assert.equal(calls,0);
  assert.equal(P.digest,undefined);assert.throws(()=>P.typedDigest({model:'foreign',digest:'a'.repeat(64)},'foreign'));
});
function v1Complete(Module=V1,stock=false){
  const input=origin();delete input.baseline;input.model='ppa-http-condition-data-v1';input.catalog=CONDITION_CATALOG_DIGEST;input.sources=copy(CONDITION_CATALOG.sources);
  const old=new Module.PpaHttpConditionData(input),binding=old.binding().binding,spec=Module.conditionRequestSpec('initializer',null);
  old.producer({binding,...spec,cookie_names:[]},108);old.beforeBootstrap({binding,phase:'before-stock-bootstrap',method:'GET',target:'/',cookie_names:[],query_names:[],body_fields:[]},110);
  const empty=hooks().slice(0,7).map(h=>({name:h.name,callbacks:[]}));
  const state=(phase,at)=>({phase,at,key:'blog_charset',value:'UTF-8',autoload:'on',rows:1,cache:null,hooks:copy(empty)});
  const before=state('before-initializer',105);if(stock)before.hooks=hooks().slice(0,7);
  return old.charset({binding,before,after:state('after-initializer',120),effect:{phase:'stock-option-call',kind:'stock-get-option',key:'blog_charset',encoding:null,value:'UTF-8',at:115,
    hooks:copy(empty),cache:null,sql:{setup:0,bootstrap:0,auth:0},protected_reads:0,protected_writes:0,protected_cache:false,unknown_changes:0},
    baseline:{at:122,digest:'d'.repeat(64),charset_anchor:sha({key:'blog_charset',value:'UTF-8',autoload:'on',rows:1})}},124);
}
test('v1 still refuses stock callbacks for the right reason;post-core UNKNOWN refuses and no owner selects v2',async()=>{
  assert.equal(v1Complete().condition_data_complete,true);assert.throws(()=>v1Complete(V1,true));
  let source=readFileSync(new URL('scripts/ci/source-policy-ppa-http-condition-data.mjs',root),'utf8');
  assert.equal(source.split('callbacks?.length===0').length,2);source=source.replace('callbacks?.length===0','true');
  source=source.replace(/'\.\/([^']+\.mjs)'/g,(_,name)=>JSON.stringify(new URL('scripts/ci/'+name,root).href));
  const bypass=await import('data:text/javascript;base64,'+Buffer.from(source).toString('base64'));
  assert.throws(()=>assert.throws(()=>v1Complete(bypass,true)),e=>e.code==='ERR_ASSERTION');
  complete();for(const role of ['installer','initializer','server'])assert.throws(()=>validateConsumerData(copy(consumerRequirements(role,'post-core')),role,'post-core'),/consumer-unresolved/);
  for(const file of ['catalog','projections','data'])assert.doesNotMatch(readFileSync(new URL('scripts/ci/source-policy-ppa-stock-charset-'+file+'.mjs',root),'utf8'),/node:(?:fs|net|http|child_process)|process\.|fetch\(|spawn\(/);
  for(const file of ['source-policy-http-owned-controller.mjs','source-policy-http-owned-router.php','source-policy-ppa-launch-data.mjs'])assert.doesNotMatch(readFileSync(new URL('scripts/ci/'+file,root),'utf8'),/ppa-stock-charset/);
});
async function mutant(file,from,to){
  const path='scripts/ci/source-policy-ppa-stock-charset-'+file+'.mjs';let text=readFileSync(new URL(path,root),'utf8');
  assert.equal(text.split(from).length,2,'unique source-backed control');text=text.replace(from,to);
  text=text.replace(/'\.\/([^']+\.mjs)'/g,(_,name)=>JSON.stringify(new URL('scripts/ci/'+name,root).href));
  const url='data:text/javascript;base64,'+Buffer.from(text).toString('base64');
  if(file==='data')return import(url);
  let owner=readFileSync(new URL('scripts/ci/source-policy-ppa-stock-charset-data.mjs',root),'utf8');
  owner=owner.replace("'./source-policy-ppa-stock-charset-projections.mjs'",JSON.stringify(url));
  owner=owner.replace(/'\.\/([^']+\.mjs)'/g,(_,name)=>JSON.stringify(new URL('scripts/ci/'+name,root).href));
  return import('data:text/javascript;base64,'+Buffer.from(owner).toString('base64'));
}
test('required source/stock/cache/anchor/credit guards have assertion-killed controls and a positive availability oracle',async()=>{
  const checks=[
    ['projections','row.digest===pin.digest','true',M=>{const o=origin();o.sources[29].digest='f'.repeat(64);assert.throws(()=>fixture(o,M));}],
    ['projections','cb.name===expected.name','true',M=>{const v=fixture(origin(),M);v.before.hooks[3].callbacks[0].name='foreign';assert.throws(()=>v.model.before(v.before,110));}],
    ['projections','cb.priority===expected.priority','true',M=>{const v=fixture(origin(),M);v.before.hooks[3].callbacks[0].priority=9;assert.throws(()=>v.model.before(v.before,110));}],
    ['projections','cb.accepted_args===expected.accepted_args','true',M=>{const v=fixture(origin(),M);v.before.hooks[3].callbacks[0].accepted_args=2;assert.throws(()=>v.model.before(v.before,110));}],
    ['projections','cb.source===expected.source','true',M=>{const v=fixture(origin(),M);v.before.hooks[3].callbacks[0].source='core/foreign.php';assert.throws(()=>v.model.before(v.before,110));}],
    ['projections','cb.digest===expected.digest','true',M=>{const v=fixture(origin(),M);v.before.hooks[3].callbacks[0].digest='f'.repeat(64);assert.throws(()=>v.model.before(v.before,110));}],
    ['projections','callbacks?.length===pin.callbacks.length','true',M=>{const v=fixture(origin(),M);v.before.hooks[7].callbacks=[copy(hooks()[3].callbacks[0])];assert.throws(()=>v.model.before(v.before,110));}],
    ['projections','row.notoptions===false','true',M=>{const v=fixture(origin(),M);v.before.cache.notoptions=true;assert.throws(()=>v.model.before(v.before,110));}],
    ['projections','row.protected_cache===false','true',M=>{const v=fixture(origin(),M);v.before.cache.protected_cache=true;assert.throws(()=>v.model.before(v.before,110));}],
    ['projections',"const rows=dataList(value,1);need(rows?.length===1,'row-cardinality');","const rows=dataList(value,2);",M=>{const v=fixture(origin(),M);v.before.rows.push(copy(row));assert.throws(()=>v.model.before(v.before,110));}],
    ['projections','row.model===model','true',M=>{const v=after(fixture(origin(),M));v.join.before.anchor.model='ppa-json-charset-row-v2';assert.throws(()=>v.model.join(v.join,125));}],
    ['data','at>=this.#last','true',M=>{const v=fixture(origin(),M);v.model.before(v.before,110);assert.throws(()=>v.model.effect(v.effect,109));}],
    ['data','stockHooks(row.hooks);const cache=charsetCache(row.cache);','const cache=charsetCache(row.cache);',M=>{const v=fixture(origin(),M);v.model.before(v.before,110);v.effect.hooks[3].callbacks=[];assert.throws(()=>v.model.effect(v.effect,115));}],
    ['data',"sql[k]+this.#origin.used[k]<=LIMITS[k+'_sql']",'true',M=>{const o=origin();o.used.setup=1024;const v=fixture(o,M);v.model.before(v.before,110);v.effect.sql.setup=1;assert.throws(()=>v.model.effect(v.effect,115));}],
    ['data',"(this.#origin.role==='server'?k!=='setup'||sql[k]===0:k==='setup'||sql[k]===0)",'true',M=>{const v=fixture(origin('server',1),M);v.model.before(v.before,110);v.effect.sql.setup=1;assert.throws(()=>v.model.effect(v.effect,115));}],
    ['data',"cache.alloptions!==null||cache.options!==null||domains.some(k=>sql[k]>0)",'true',M=>{const v=fixture(origin(),M);v.model.before(v.before,110);v.effect.cache.alloptions=null;assert.throws(()=>v.model.effect(v.effect,115));}],
    ['data','this.#before.charset.digest===this.#after.charset.digest','true',M=>{const v=fixture(origin(),M);v.model.before(v.before,110);v.model.effect(v.effect,115);v.after.rows[0].id='18';assert.throws(()=>v.model.after(v.after,120));}],
    ['data','invariant.digest===this.#origin.baseline.invariant.digest','true',M=>{const v=after(fixture(origin('server',1),M));v.join.after.invariant.digest='d'.repeat(64);assert.throws(()=>v.model.join(v.join,125));}],
    ['data','release.sessions===0','true',M=>{const v=after(fixture(origin(),M));v.join.release.sessions=1;assert.throws(()=>v.model.join(v.join,125));}],
    ['data','baseline.at<=row.started','true',M=>{const o=origin('server',1);o.baseline.at=101;assert.throws(()=>fixture(o,M));}],
    ['data','this.#state===expected','true',M=>{const v=complete(origin(),M);assert.throws(()=>v.model.join(v.join,126));}],
    ['data','native_admitted:false','native_admitted:true',M=>assert.equal(complete(origin(),M).result.native_admitted,false)],
    ['data','stock_projection_data_complete:true','stock_projection_data_complete:false',M=>assert.equal(complete(origin(),M).result.stock_projection_data_complete,true)],
  ];
  for(const [file,from,to,oracle]of checks){const Module=await mutant(file,from,to);assert.throws(()=>oracle(Module),e=>e.code==='ERR_ASSERTION',from);}
  const unavailable=await mutant('data',"this.#state='complete';return freezeData","throw Error('unavailable');return freezeData");
  assert.throws(()=>assert.doesNotThrow(()=>complete(origin(),unavailable)),e=>e.code==='ERR_ASSERTION');assert.equal(checks.length+1,24);
});
