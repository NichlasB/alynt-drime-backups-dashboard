import test from 'node:test';
import assert from 'node:assert/strict';
import {createHash} from 'node:crypto';
import {readFileSync} from 'node:fs';
import * as D from '../../scripts/ci/source-policy-ppa-http-condition-data.mjs';
import * as C from '../../scripts/ci/source-policy-ppa-http-condition-catalog.mjs';
import * as F from '../../scripts/ci/source-policy-ppa-function-data.mjs';
import {HTTP_FLOW} from '../../scripts/ci/source-policy-http-supervisor.mjs';
const root=new URL('../../',import.meta.url),copy=v=>JSON.parse(JSON.stringify(v));
const sha=v=>createHash('sha256').update(JSON.stringify(v)).digest('hex');
function origin(role='initializer',exchange=null){
  return {model:D.CONDITION_MODEL,run:'a'.repeat(32),head:'b'.repeat(40),expected_head:'b'.repeat(40),image:'c'.repeat(64),
    profile:'owned-http-l714-initialized-v2',core_commit:'db96f5eaa77a8d3e8eec8923a518fa2326f0d46a',
    catalog:C.CONDITION_CATALOG_DIGEST,role,exchange,started:100,deadline:60100,
    used:{setup:0,bootstrap:0,auth:0},sources:copy(C.CONDITION_CATALOG.sources)};
}
function packets(model,input){
  const spec=D.conditionRequestSpec(input.role,input.exchange),binding=model.binding().binding;
  const producer={binding,...copy(spec),cookie_names:input.role==='server'?['wordpress_test_cookie']:[]};
  const receiver={binding,phase:'before-stock-bootstrap',method:spec.method,target:spec.target,
    cookie_names:copy(producer.cookie_names),query_names:copy(spec.query_names),
    body_fields:spec.body_names.map(name=>({name:name.replace(/\[\]$/,''),kind:name.endsWith('[]')?'array':'scalar'}))};
  const state=(phase,at)=>({phase,at,key:'blog_charset',value:'UTF-8',autoload:'on',rows:1,cache:null,
    hooks:C.CONDITION_CATALOG.hooks.map(name=>({name,callbacks:[]}))});
  const role=input.role,before=state(role==='installer'?'after-owned-install':role==='initializer'?'before-initializer':'before-request',role==='installer'?115:105);
  const after=state(role==='installer'?'after-installer-shutdown':role==='initializer'?'after-initializer':'after-request-shutdown',120);
  const anchor=sha({key:'blog_charset',value:'UTF-8',autoload:'on',rows:1});
  const charset={binding,before,after,effect:{phase:'stock-option-call',kind:'stock-get-option',key:'blog_charset',encoding:null,value:'UTF-8',at:118,
    hooks:copy(before.hooks),cache:null,
    sql:role==='server'?{setup:0,bootstrap:1,auth:0}:{setup:1,bootstrap:0,auth:0},
    protected_reads:0,protected_writes:0,protected_cache:false,unknown_changes:0},
    baseline:{at:122,digest:'d'.repeat(64),charset_anchor:anchor}};
  return {producer,receiver,charset};
}
function started(input=origin(),Module=D){
  const model=new Module.PpaHttpConditionData(input),p=packets(model,input);model.producer(p.producer,108);return {model,p,input};
}
function checked(input=origin(),Module=D){const v=started(input,Module);v.model.beforeBootstrap(v.p.receiver,110);return v;}
function ready(input=origin(),Module=D){const v=checked(input,Module);v.result=v.model.charset(v.p.charset,124);return v;}

test('all roles and all25 exchanges have positive condition DATA without any physical grant or handles',()=>{
  assert.equal(D.CONDITION_EXECUTION_ENABLED,false);
  for(const input of [origin('installer'),origin(),...Array.from({length:25},(_,i)=>origin('server',i+1))]){
    const {model,result}=ready(input);assert.equal(result.condition_data_complete,true);
    for(const key of ['native_admitted','runtime_grant','http_qualified','production_ready','source_observed',
      'pre_bootstrap_checked','effective_charset_observed','consumer_closure_verified','dependency_closure_verified'])assert.equal(result[key],false);
    assert.equal(result.option_effect,'required-stock-read');assert.ok(Object.isFrozen(result));
    for(const key of ['launch','execute','read','write','connect','activate','cleanup'])assert.equal(model[key],undefined);
  }
});
test('independent literal flow oracle preserves deliberate compound body fixtures,not compound cookies',()=>{
  const kinds=['login-form','login-submit','view','submit','view','submit','submit','submit','login-form','login-submit','submit','submit','nonce-control','submit','login-form','login-submit','view','submit','login-form','login-submit','view','submit','submit','submit','submit'];
  const jars=['primary','primary','primary','primary','primary','primary','primary','anonymous','incapable','incapable','incapable','primary','primary','primary','other','other','other','primary','second','second','second','primary','primary','primary','primary'];
  for(let i=0;i<25;i++){
    const spec=D.conditionRequestSpec('server',i+1);assert.equal(spec.kind,kinds[i]);assert.equal(spec.jar,jars[i]);
    assert.equal(spec.method,['login-submit','submit'].includes(kinds[i])?'POST':'GET');
    assert.equal(spec.target,kinds[i].startsWith('login-')?'/wp-login.php':'/_adb-owned-source-policy'+(i===12?'?control=wrong-action':''));
    assert.deepEqual(spec.query_names,i===12?['control']:[]);
  }
  assert.deepEqual(D.conditionRequestSpec('server',23).body_names,['_wpnonce[]','dashboard_site_id','source_key','source_mode','source_policy_revision']);
  assert.equal(D.conditionRequestSpec('server',24).body_names[1],'dashboard_site_id[]');
  assert.equal(D.conditionRequestSpec('server',12).body_names.length,4);
  assert.deepEqual(D.conditionRequestSpec('server',2).body_names,['log','pwd','wp-submit','redirect_to','testcookie']);
});
test('recorded catalog binds all24 sources and actual unchanged proof bytes,not a dynamic source provider',()=>{
  assert.equal(C.CONDITION_CATALOG.sources.length,24);
  assert.equal(C.CONDITION_CATALOG.proof_revision,'6bb399a8969f3c2c57febf2f2ec40d08b741a0a6');
  assert.equal(C.CONDITION_CATALOG.flow_digest,'403b05acb27d92390b68330625e82fc2ffbef32624c5bc698b5b768a6b85a394');
  assert.equal(sha(HTTP_FLOW),C.CONDITION_CATALOG.flow_digest);assert.equal(sha(C.CONDITION_CATALOG),C.CONDITION_CATALOG_DIGEST);
  assert.deepEqual(C.CONDITION_CATALOG.hooks,['pre_option_blog_charset','pre_option','default_option_blog_charset','option_blog_charset','pre_wp_load_alloptions','pre_cache_alloptions','alloptions']);
  const design=readFileSync(new URL('docs/SOURCE_POLICY_PPA_HTTP_CONSUMER_CONDITION_DESIGN.md',root),'utf8');
  for(const row of C.CONDITION_CATALOG.sources.slice(9))assert.ok(design.includes(row.digest)&&design.includes(String(row.size)));
  for(const row of C.CONDITION_CATALOG.sources.filter(r=>!r.path.startsWith('core/'))){
    assert.equal(row.path.startsWith('workspace/'),row.path.endsWith('.mjs'));
    const bytes=readFileSync(new URL(row.path.slice(row.path.indexOf('/')+1),root));assert.equal(bytes.length,row.size);
    assert.equal(createHash('sha256').update(bytes).digest('hex'),row.digest);
  }
  assert.throws(()=>C.CONDITION_CATALOG.sources[0].digest='f'.repeat(64));
});
test('exported request-spec API refuses a changed imported flow before any condition owner exists',async()=>{
  let text=readFileSync(new URL('scripts/ci/source-policy-ppa-http-condition-data.mjs',root),'utf8');
  const flow=copy(HTTP_FLOW);flow[0].jar='foreign';
  text=text.replace("'./source-policy-http-supervisor.mjs'",JSON.stringify('data:text/javascript;base64,'+Buffer.from('export const HTTP_FLOW='+JSON.stringify(flow)).toString('base64')));
  for(const name of ['source-policy-http-profile.mjs','source-policy-ppa-function-data.mjs','source-policy-ppa-http-condition-catalog.mjs']){
    text=text.replaceAll("'./"+name+"'",JSON.stringify(new URL('scripts/ci/'+name,root).href));
  }
  const Module=await import('data:text/javascript;base64,'+Buffer.from(text).toString('base64'));
  assert.throws(()=>Module.conditionRequestSpec('server',1),/fixed-flow/);
  assert.throws(()=>new Module.PpaHttpConditionData(origin()),/fixed-flow/);
});
test('foreign source/head/model/role,aliases,credit resets and extra fields refuse at origin',()=>{
  for(const change of [p=>p.head='e'.repeat(40),p=>p.core_commit='e'.repeat(40),p=>p.catalog='e'.repeat(64),
    p=>p.profile='owned-http-l714-v1',p=>p.role='setup',p=>p.exchange=1,p=>p.deadline++,p=>p.started=-1,
    p=>p.used.setup=-1,p=>p.used.bootstrap=257,p=>p.sources[0].digest='e'.repeat(64),p=>p.sources[0].size++,
    p=>p.sources[0].path='core/../wp-settings.php',p=>p.sources.reverse(),p=>p.sources.pop(),p=>p.native_admitted=true]){
    const input=origin();change(input);assert.throws(()=>new D.PpaHttpConditionData(input));
  }
  for(const exchange of [0,26,1.5,'1'])assert.throws(()=>new D.PpaHttpConditionData(origin('server',exchange)));
});
test('only finite canonical cookie names pass;recovery,aliases,duplicates and CLI inputs poison state',()=>{
  const names=['wordpress_rec_'+'a'.repeat(32),'wordpress.test.cookie','wordpress_test_cookie[]','wordpress%5Ftest_cookie',
    'wordpress_test_cookie=value','wordpress_test_cookie; other','wp-settings-4','wordpress_test_cookie\n'];
  for(const name of names){const input=origin('server',1),m=new D.PpaHttpConditionData(input),p=packets(m,input);
    p.producer.cookie_names=[name];assert.throws(()=>m.producer(p.producer,108));assert.throws(()=>m.producer(packets(m,input).producer,109));
  }
  for(const cookies of [['wordpress_test_cookie','wordpress_test_cookie'],['wp-settings-1','wordpress_test_cookie']]){
    const input=origin('server',1),m=new D.PpaHttpConditionData(input),p=packets(m,input);p.producer.cookie_names=cookies;assert.throws(()=>m.producer(p.producer,108));
  }
  for(const role of ['installer','initializer']){const input=origin(role),m=new D.PpaHttpConditionData(input),p=packets(m,input);
    p.producer.cookie_names=['wordpress_test_cookie'];assert.throws(()=>m.producer(p.producer,108));
  }
});
test('receiver must join the producer before core and retain exact role/query/parsed-body shapes',()=>{
  const changes=[p=>p.phase='after-stock-bootstrap',p=>p.binding='f'.repeat(64),p=>p.target='/wp-login.php?action=enter_recovery_mode',
    p=>p.cookie_names=[],p=>p.query_names=['action','rm_token','rm_key'],p=>p.body_fields[0].kind='array',p=>p.body_fields.pop(),p=>p.cookie_absent=true];
  for(const change of changes){const v=started(origin('server',2));change(v.p.receiver);assert.throws(()=>v.model.beforeBootstrap(v.p.receiver,110));assert.throws(()=>v.model.charset(v.p.charset,124));}
  const v=started(origin('server',23));v.p.receiver.body_fields[0].kind='scalar';assert.throws(()=>v.model.beforeBootstrap(v.p.receiver,110));
});
test('charset requires complete UTF-8 state,empty selected hook projections,effective-value agreement and protected absence',()=>{
  for(const change of [p=>p.before.value='latin1',p=>p.after.value='latin1',p=>p.before.rows=0,p=>p.after.rows=2,
    p=>p.before.autoload='off',p=>p.after.cache='latin1',p=>p.before.hooks.pop(),p=>p.before.hooks[0].name='foreign',
    p=>p.before.hooks[0].callbacks=['shadow'],p=>p.effect.value='latin1',p=>p.effect.encoding='UTF-8',
    p=>p.effect.hooks[0].callbacks=['shadow'],p=>p.effect.hooks.pop(),p=>p.effect.cache='latin1',p=>p.effect.phase='post-option-call',
    p=>p.effect.protected_reads=1,p=>p.effect.protected_writes=1,p=>p.effect.protected_cache=true,p=>p.effect.unknown_changes=1,
    p=>p.baseline.charset_anchor='f'.repeat(64),p=>p.baseline.digest='unknown']){
    const v=checked();change(v.p.charset);assert.throws(()=>v.model.charset(v.p.charset,124));assert.throws(()=>v.model.charset(packets(v.model,v.input).charset,125));
  }
  const v=checked();v.p.charset.effect.sql={setup:0,bootstrap:0,auth:0};assert.equal(v.model.charset(v.p.charset,124).option_effect,'required-stock-read');
});
test('role times and original SQL domains cannot be extended,borrowed or replayed',()=>{
  for(const change of [p=>p.before.at=111,p=>p.after.at=109,p=>p.effect.at=104,p=>p.baseline.at=119,
    p=>p.effect.sql.auth=1,p=>p.effect.sql.setup=1025]){const v=checked();change(v.p.charset);assert.throws(()=>v.model.charset(v.p.charset,124));}
  const input=origin();input.used.setup=1024;const exhausted=checked(input);assert.throws(()=>exhausted.model.charset(exhausted.p.charset,124));
  const server=checked(origin('server',1));server.p.charset.effect.sql.setup=1;assert.throws(()=>server.model.charset(server.p.charset,124));
  const installer=checked(origin('installer'));installer.p.charset.before.at=105;assert.throws(()=>installer.model.charset(installer.p.charset,124));
  const early=started();assert.throws(()=>early.model.beforeBootstrap(early.p.receiver,107));
  const late=started();assert.throws(()=>late.model.beforeBootstrap(late.p.receiver,60100));
  const skipped=new D.PpaHttpConditionData(origin());assert.throws(()=>skipped.beforeBootstrap(packets(skipped,origin()).receiver,110));
  const done=ready();assert.throws(()=>done.model.charset(done.p.charset,125));
  const unknown=checked();unknown.model.uncertain();assert.throws(()=>unknown.model.charset(unknown.p.charset,124));
});
test('nested accessors/raw values are refused without reading or invoking them',()=>{
  let calls=0;const getter=()=>{calls++;throw Error('private');};
  const o=origin();Object.defineProperty(o.sources[0],'digest',{get:getter});assert.throws(()=>new D.PpaHttpConditionData(o));
  const p=started(origin('server',1));Object.defineProperty(p.p.receiver.cookie_names,'0',{get:getter});assert.throws(()=>p.model.beforeBootstrap(p.p.receiver,110));
  const c=checked();Object.defineProperty(c.p.charset.before,'value',{get:getter});assert.throws(()=>c.model.charset(c.p.charset,124));
  const h=checked();h.p.charset.before.hooks[0].callbacks=[getter];assert.throws(()=>h.model.charset(h.p.charset,124));
  const raw=started();raw.p.receiver.raw_cookie='private';assert.throws(()=>raw.model.beforeBootstrap(raw.p.receiver,110));
  assert.equal(calls,0);
});
test('successful component DATA does not change the existing UNKNOWN catalog or install a consumer',()=>{
  ready();for(const role of ['installer','initializer','server'])assert.throws(()=>F.validateConsumerData(copy(F.consumerRequirements(role,'post-core')),role,'post-core'),/consumer-unresolved/);
  for(const name of ['source-policy-ppa-http-condition-data.mjs','source-policy-ppa-http-condition-catalog.mjs']){
    const text=readFileSync(new URL('scripts/ci/'+name,root),'utf8');assert.doesNotMatch(text,/node:(?:fs|net|http|child_process)|process\.|fetch\(|spawn\(/);
  }
  for(const name of ['source-policy-http-owned-controller.mjs','source-policy-http-owned-router.php','source-policy-ppa-launch-data.mjs']){
    assert.doesNotMatch(readFileSync(new URL('scripts/ci/'+name,root),'utf8'),/ppa-http-condition/);
  }
});
async function mutant(from,to){
  let text=readFileSync(new URL('scripts/ci/source-policy-ppa-http-condition-data.mjs',root),'utf8');assert.ok(text.includes(from));text=text.replace(from,to);
  for(const name of ['source-policy-http-profile.mjs','source-policy-http-supervisor.mjs','source-policy-ppa-function-data.mjs','source-policy-ppa-http-condition-catalog.mjs']){
    text=text.replaceAll("'./"+name+"'",JSON.stringify(new URL('scripts/ci/'+name,root).href));
  }return import('data:text/javascript;base64,'+Buffer.from(text).toString('base64'));
}
test('eighteen source-backed semantic controls are assertion-killed,not parse errors or unavailable-only success',async()=>{
  const controls=[
    ['row.digest===pin.digest','true',M=>{const p=origin();p.sources[0].digest='f'.repeat(64);assert.throws(()=>new M.PpaHttpConditionData(p));}],
    ['row.size===pin.size','true',M=>{const p=origin();p.sources[0].size++;assert.throws(()=>new M.PpaHttpConditionData(p));}],
    ['row.expected_head===row.head','true',M=>{const p=origin();p.expected_head='f'.repeat(40);assert.throws(()=>new M.PpaHttpConditionData(p));}],
    ["/^(?:wordpress_test_cookie|wordpress(?:_sec|_logged_in)?_[a-f0-9]{32}|wp-settings-(?:time-)?[1-3])$/.test(name)",'true',M=>{const input=origin('server',1),m=new M.PpaHttpConditionData(input),p=packets(m,input);p.producer.cookie_names=['wordpress_rec_'+'a'.repeat(32)];assert.throws(()=>m.producer(p.producer,108));}],
    ["row.phase==='before-stock-bootstrap'",'true',M=>{const v=started(origin(),M);v.p.receiver.phase='after-stock-bootstrap';assert.throws(()=>v.model.beforeBootstrap(v.p.receiver,110));}],
    ['JSON.stringify(cookies)===JSON.stringify(this.#producer.cookies)','true',M=>{const v=started(origin('server',1),M);v.p.receiver.cookie_names=[];assert.throws(()=>v.model.beforeBootstrap(v.p.receiver,110));}],
    ["field.kind===(name.endsWith('[]')?'array':'scalar')",'true',M=>{const v=started(origin('server',2),M);v.p.receiver.body_fields[0].kind='array';assert.throws(()=>v.model.beforeBootstrap(v.p.receiver,110));}],
    ['at>=this.#last','true',M=>{const v=started(origin(),M);assert.throws(()=>v.model.beforeBootstrap(v.p.receiver,107));}],
    ['row.rows===1','true',M=>{const v=checked(origin(),M);v.p.charset.before.rows=2;v.p.charset.after.rows=2;v.p.charset.baseline.charset_anchor=sha({key:'blog_charset',value:'UTF-8',autoload:'on',rows:2});assert.throws(()=>v.model.charset(v.p.charset,124));}],
    ['callbacks?.length===0','true',M=>{const v=checked(origin(),M);v.p.charset.before.hooks[0].callbacks=['shadow'];assert.throws(()=>v.model.charset(v.p.charset,124));}],
    ['hookData(effect.hooks);',';',M=>{const v=checked(origin(),M);v.p.charset.effect.hooks[0].callbacks=['shadow'];assert.throws(()=>v.model.charset(v.p.charset,124));}],
    ['effect.protected_cache===false','true',M=>{const v=checked(origin(),M);v.p.charset.effect.protected_cache=true;assert.throws(()=>v.model.charset(v.p.charset,124));}],
    ["sql[k]+this.#origin.used[k]<=LIMITS[k+'_sql']",'true',M=>{const p=origin();p.used.setup=1024;const v=checked(p,M);assert.throws(()=>v.model.charset(v.p.charset,124));}],
    ["(role==='server'?k!=='setup'||sql[k]===0:k==='setup'||sql[k]===0)",'true',M=>{const v=checked(origin('server',1),M);v.p.charset.effect.sql.setup=1;assert.throws(()=>v.model.charset(v.p.charset,124));}],
    ['baseline.charset_anchor===after.anchor','true',M=>{const v=checked(origin(),M);v.p.charset.baseline.charset_anchor='f'.repeat(64);assert.throws(()=>v.model.charset(v.p.charset,124));}],
    ['native_admitted:false','native_admitted:true',M=>assert.equal(ready(origin(),M).result.native_admitted,false)],
    ['condition_data_complete:true','condition_data_complete:false',M=>assert.equal(ready(origin(),M).result.condition_data_complete,true)],
  ];let killed=0;for(const [from,to,oracle]of controls){const M=await mutant(from,to);assert.throws(()=>oracle(M),e=>e.code==='ERR_ASSERTION',from);killed++;}assert.equal(killed,17);
  const M=await mutant("this.#state='complete';return freezeData", "throw Error('unavailable');return freezeData");
  assert.throws(()=>assert.doesNotThrow(()=>ready(origin(),M)),e=>e.code==='ERR_ASSERTION');
});
