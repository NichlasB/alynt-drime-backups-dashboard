import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {spawnSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
import * as L from '../../scripts/ci/source-policy-ppa-launch-data.mjs';
import * as F from '../../scripts/ci/source-policy-ppa-function-data.mjs';
import {HTTP_PROFILE} from '../../scripts/ci/source-policy-http-profile.mjs';

const root=new URL('../../',import.meta.url),read=path=>readFileSync(new URL(path,root),'utf8');
const copy=value=>JSON.parse(JSON.stringify(value));
function origin(){
  return {model:L.LAUNCH_MODEL,repository:'NichlasB/alynt-drime-backups-dashboard',
    workflow:'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-linux-qualification.yml@refs/heads/master',
    event:'workflow_dispatch',actor:'NichlasB',attempt:1,platform:'Linux',arch:'x86_64',run:'123',
    head:'a'.repeat(40),expected_head:'a'.repeat(40),root:'/owned',workspace:'/workspace',core_commit:HTTP_PROFILE.commit,
    image:'b'.repeat(64),modules:'c'.repeat(64),prefix_identity:'d'.repeat(64),ppa_identity:L.PPA_IDENTITY,
    resources:{...L.RESOURCE_DATA},core_sources:F.CORE_MEMBERS.map((path,i)=>({path,digest:String(i+1).repeat(64)}))};
}
function closed(model,input=origin()){
  return {binding:model.binding().binding,prefix_identity:input.prefix_identity,source_rounds:2,
    sources_closed:true,codecs_reaped:true,prefix_sealed:true,release:'closed',exec:'not-started'};
}
function packet(model,role,phase='pre-core',input=origin()){
  const required=F.functionRequirements(role,phase),b=model.binding();
  return {model:F.FUNCTION_MODEL,binding:b.binding,role,phase,php:'8.5.12',word_size:8,zts:false,
    sapi:role==='server'&&phase==='post-core'?'cli-server':'cli',catalog:b.catalog,
    sources:phase==='post-core'?copy(input.core_sources):[],
    algorithms:{sha256:true,sha384:true,bcrypt:role==='environment'?null:true},
    rows:[...required.native.map(name=>({name,available:true,provider:'native',source:'internal',digest:null,review:'required'})),
      ...required.core.map(name=>({name,available:true,provider:'core',source:input.core_sources[0].path,digest:input.core_sources[0].digest,review:'source-reviewed'})),
      ...required.absent.map(name=>({name,available:false,provider:'absent',source:null,digest:null,review:'gated'}))]};
}
function started(role='installer',Module=L,index=null){
  const input=origin(),model=new Module.PpaLaunchPreparation(input);model.sourcesClosed(closed(model,input));
  const description=model.description(role,index);return {input,model,description};
}
function ready(role='installer',Module=L,index=null){
  const value=started(role,Module,index);value.model.functions(packet(value.model,role));
  if(F.HTTP_ROLES.includes(role))value.model.functions(packet(value.model,role,'post-core'));
  value.result=value.model.complete();return value;
}

test('every literal role has a positive description with exact full recovery identity and no native grants',()=>{
  assert.equal(L.PPA_LAUNCH_EXECUTION_ENABLED,false);
  for(const role of F.LAUNCH_ROLES){
    const {model,description:d,result:r}=ready(role,L,role==='request'?0:null);
    assert.equal(r.description_complete,true,role);assert.equal(model.recovery(d),d);
    assert.equal(d.binary,'/owned/adb-ppa-runtime-123/php');assert.equal(d.argv[0],d.binary);
    assert.equal(d.argv[1],'-n');assert.deepEqual(d.argv.filter(arg=>arg.startsWith('extension=')),
      ['extension=/owned/adb-ppa-runtime-123/mysqlnd.so','extension=/owned/adb-ppa-runtime-123/mysqli.so']);
    for(const key of ['native_admitted','runtime_grant','http_qualified','dependency_closure_verified','production_ready']){
      assert.equal(r[key],false);assert.equal(d[key],false);
    }
    assert.equal(d.resources.native_role_resource_admitted,false);assert.equal(d.resources.codec_as,25165824);
    assert.equal(d.environment.PATH,'/usr/bin:/bin');assert.equal(d.environment.PHP_INI_SCAN_DIR,'');
    assert.ok(Object.isFrozen(d)&&Object.isFrozen(d.argv)&&Object.isFrozen(d.environment)&&Object.isFrozen(r));
    assert.throws(()=>d.argv.push('fallback'));assert.throws(()=>d.environment.LD_PRELOAD='unknown');
    for(const name of ['launch','execute','connect','cleanup','read','write'])assert.equal(model[name],undefined);
  }
});
test('fixed role tails and private-channel requirements preserve existing contracts without exporting secrets',()=>{
  const expected={environment:['source-policy-linux-preflight.php','--owned-profile-preflight'],
    audit:['source-policy-linux-qualification.php','--owned-linux-qualification','audit'],
    adapter:['source-policy-linux-qualification.php','--owned-linux-qualification','proof'],
    'request-audit':['source-policy-wl-request-child.php','--owned-wl-request-child','0'],
    installer:['source-policy-http-owned-setup.php','--owned-http-setup'],
    initializer:['source-policy-http-initialized-native.php','--owned-http-initializer']};
  for(const [role,tail]of Object.entries(expected)){
    const {description:d}=ready(role);assert.ok(d.argv.at(-tail.length).endsWith('/'+tail[0]));
    assert.deepEqual(d.argv.slice(-tail.length+1),tail.slice(1));
  }
  for(let index=0;index<23;index++){
    const d=ready('request',L,index).description;assert.equal(d.argv.at(-1),String(index));
    assert.equal(d.environment.WL_REQUEST_MODE,'proof');assert.deepEqual(d.private_channels,['QUALIFICATION_PASSWORD']);
    assert.equal(d.environment.QUALIFICATION_PASSWORD,undefined);
  }
  const audit=ready('request-audit').description;assert.equal(audit.environment.WL_REQUEST_IMAGE,'0'.repeat(64));
  assert.equal(audit.environment.WL_REQUEST_MODULES,'0'.repeat(64));assert.deepEqual(audit.private_channels,[]);
  assert.deepEqual(ready('initializer').description.private_channels,['private.php','stdin-ticket']);
  const server=ready('server').description;assert.deepEqual(server.argv.slice(-5,-1),['-S','127.0.0.1:18777','-t',server.argv.at(-2)]);
  assert.ok(server.argv.at(-1).endsWith('/source-policy-http-owned-router.php'));
});
test('identity constants stay tied to the actual Python package/payload policy, not another artifact cohort',()=>{
  const packages=read('scripts/ci/source_policy_deb_data.py'),payloads=read('scripts/ci/source_policy_ppa_prefix.py');
  assert.ok(packages.includes(L.PPA_IDENTITIES.revision));
  for(const [name,size,digest]of L.PPA_IDENTITIES.packages){assert.ok(packages.includes(name)&&packages.includes(String(size))&&packages.includes(digest));}
  for(const [name,size,digest]of L.PPA_IDENTITIES.payloads){assert.ok(payloads.includes(name)&&payloads.includes(String(size))&&payloads.includes(digest));}
});
test('request descriptions pass the unchanged genuine admission definition without core, credentials or native effects',()=>{
  const literal=value=>typeof value==='string'?"'"+value.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'":
    'array('+Object.entries(value).map(([key,item])=>literal(key)+'=>'+literal(item)).join(',')+')';
  const file=fileURLToPath(new URL('scripts/ci/source-policy-wl-request-contract.php',root));
  for(const [role,index]of [['request-audit',null],['request',0],['request',22]]){
    const d=ready(role,L,index).description;
    const body=`require ${literal(file)};$env=${literal(d.environment)};$args=${literal(d.argv.slice(-3))};if(!adbwl_entry($env,$args))throw new AssertionError('positive');$env['GITHUB_SHA']='foreign';if(adbwl_entry($env,$args))throw new AssertionError('stale');echo 'definition-only';`;
    const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',body],{encoding:'utf8',timeout:5000,maxBuffer:2048});
    assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr);assert.equal(r.stderr,'');assert.equal(r.stdout,'definition-only');
  }
});
test('exact origin rejects foreign policy, stale head/core/pins, path aliases and extra selectors',()=>{
  const mutations=[v=>v.model='owned-http-l714-initialized-v2',v=>v.head='b'.repeat(40),v=>v.core_commit='b'.repeat(40),
    v=>v.ppa_identity='0'.repeat(64),v=>v.attempt=2,v=>v.platform='Windows',v=>v.event='push',v=>v.actor='other',
    v=>v.workflow='other',v=>v.root='/',v=>v.root='/owned/../other',v=>v.workspace='/owned/subdir',
    v=>v.root='/owned//alias',v=>v.workspace='C:\\private',v=>v.run='01',v=>v.binary='/arbitrary',
    v=>v.environment={LD_PRELOAD:'secret'},v=>v.core_sources[0].path='core/foreign.php',v=>v.resources.codec_as=33554432];
  for(const change of mutations){const value=origin();change(value);assert.throws(()=>new L.PpaLaunchPreparation(value));}
});
test('record and list accessors are refused without invoking getters',()=>{
  let called=0;const value=origin();Object.defineProperty(value,'root',{get(){called++;throw Error('secret');}});
  assert.throws(()=>new L.PpaLaunchPreparation(value));assert.equal(called,0);
  const {model}=started();const p=packet(model,'installer');Object.defineProperty(p.rows,'0',{get(){called++;throw Error('secret');}});
  assert.throws(()=>model.functions(p));assert.equal(called,0);
  const sources=origin().core_sources;Object.defineProperty(sources[0],'digest',{get(){called++;throw Error('secret');}});
  const b=model.binding();assert.throws(()=>F.validateFunctionData(packet(model,'installer'),b.binding,'installer','pre-core',sources,b.catalog));
  assert.equal(called,0);
});
test('source/codec/prefix/unknown barriers must precede description and cannot be repaired after failure',()=>{
  for(const change of [p=>p.binding='0'.repeat(64),p=>p.prefix_identity='0'.repeat(64),p=>p.source_rounds=1,
    p=>p.sources_closed=false,p=>p.codecs_reaped=false,p=>p.prefix_sealed=false,p=>p.release='unknown',p=>p.exec='unknown',p=>p.extra=true]){
    const m=new L.PpaLaunchPreparation(origin()),p=closed(m);change(p);assert.throws(()=>m.sourcesClosed(p));
    assert.throws(()=>m.sourcesClosed(closed(m)));assert.throws(()=>m.description('installer'));
  }
  const m=new L.PpaLaunchPreparation(origin());assert.throws(()=>m.description('installer'));
});
test('role aliases, out-of-range request cases and arbitrary tails cannot select another command',()=>{
  for(const [role,index]of [['recovery',null],['setup',null],['initializer','0'],['request',-1],['request',23],['request',1.5],['request',null]]){
    const m=new L.PpaLaunchPreparation(origin());m.sourcesClosed(closed(m));assert.throws(()=>m.description(role,index));
    assert.throws(()=>m.description('installer'));
  }
});
test('HTTP readiness requires both phase profiles; copied receipts, repetition and unknown remain closed',()=>{
  const a=started();assert.throws(()=>a.model.complete());
  const b=started();b.model.functions(packet(b.model,'installer'));assert.throws(()=>b.model.complete());
  const c=started();assert.throws(()=>c.model.functions(packet(c.model,'installer','post-core')));
  const d=ready();d.model.uncertain();assert.throws(()=>d.model.complete());assert.throws(()=>d.model.recovery(d.description));
  const e=ready();assert.throws(()=>e.model.recovery(copy(e.description)));assert.throws(()=>e.model.recovery(e.description));
  const f=started('audit');f.model.functions(packet(f.model,'audit'));assert.throws(()=>f.model.functions(packet(f.model,'audit')));
});
test('function binding, NTS types, algorithms and native membership are exact, never truthiness or missing-function success',()=>{
  const changes=[p=>p.binding='0'.repeat(64),p=>p.catalog='0'.repeat(64),p=>p.role='server',p=>p.php='8.5.13',
    p=>p.zts=0,p=>p.zts=true,p=>p.sapi='cli-server',p=>p.algorithms.bcrypt=false,p=>p.rows.shift(),
    p=>p.rows[0].available=false,p=>p.rows[0].provider='core',p=>p.rows[0].extra='secret',p=>p.rows.push({...p.rows[0]})];
  for(const change of changes){const {model}=started();const p=packet(model,'installer');change(p);
    assert.throws(()=>model.functions(p));assert.throws(()=>model.functions(packet(model,'installer')));
  }
});
test('optional unreviewed or reachable missing functions do not turn into a gated catalog',()=>{
  for(const status of ['unreviewed','required','unknown']){
    const {model}=started();const p=packet(model,'installer');p.rows.at(-1).review=status;
    assert.throws(()=>model.functions(p));assert.throws(()=>model.complete());
  }
});
test('stock fallback claims require the same core source/helper manifest and post-core phase',()=>{
  const changes=[p=>p.sources[1].digest='f'.repeat(64),p=>p.rows.find(r=>r.name==='mb_strlen').digest='f'.repeat(64),
    p=>p.rows.find(r=>r.name==='mb_strlen').provider='native',p=>p.rows.find(r=>r.name==='mb_substr').source='core/foreign.php',
    p=>p.sources.pop(),p=>p.rows.find(r=>r.name==='mb_strlen').review='unreviewed'];
  for(const change of changes){const {model}=started();model.functions(packet(model,'installer'));
    const p=packet(model,'installer','post-core');change(p);assert.throws(()=>model.functions(p));assert.throws(()=>model.complete());
  }
});
test('imports and ordinary callers have no executor, storage handle, callback or workflow dispatch path',()=>{
  const files=['scripts/ci/source-policy-ppa-launch-data.mjs','scripts/ci/source-policy-ppa-function-data.mjs'];
  for(const file of files){const text=read(file);assert.doesNotMatch(text,/node:(?:fs|net|http|child_process)|process\.|fetch\(|execve|Popen|spawn\(/);}
  const text=read('scripts/ci/source-policy-http-owned-controller.mjs');assert.doesNotMatch(text,/ppa-launch-data|ppa-function-data/);
  assert.doesNotMatch(read('.github/workflows/source-policy-ppa-deb-static.yml'),/ppa-launch-data|ppa-function-data/);
});
async function mutated({launchFrom,launchTo,functionFrom,functionTo}){
  let functions=read('scripts/ci/source-policy-ppa-function-data.mjs');
  if(functionFrom){assert.ok(functions.includes(functionFrom));functions=functions.replace(functionFrom,functionTo);}
  functions=functions.replaceAll("'./source-policy-http-profile.mjs'",JSON.stringify(new URL('scripts/ci/source-policy-http-profile.mjs',root).href));
  let launch=read('scripts/ci/source-policy-ppa-launch-data.mjs');
  if(launchFrom){assert.ok(launch.includes(launchFrom));launch=launch.replace(launchFrom,launchTo);}
  for(const file of ['source-policy-http-profile.mjs','source-policy-wl-request-contract.mjs']){
    launch=launch.replaceAll("'./"+file+"'",JSON.stringify(new URL('scripts/ci/'+file,root).href));
  }
  launch=launch.replaceAll("'./source-policy-ppa-function-data.mjs'",JSON.stringify('data:text/javascript;base64,'+Buffer.from(functions).toString('base64')));
  return import('data:text/javascript;base64,'+Buffer.from(launch).toString('base64'));
}
test('required policy guards and availability have assertion-killed trusted-source semantic controls',async()=>{
  const controls=[
    [{launchFrom:'description_complete:true',launchTo:'description_complete:false'},M=>assert.equal(ready('audit',M).result.description_complete,true)],
    [{launchFrom:"this.#state='complete';return freezeData",launchTo:"throw Error('semantic-unavailable');return freezeData"},M=>assert.doesNotThrow(()=>ready('audit',M))],
    [{launchFrom:"'extension='+origin.prefix+'/mysqlnd.so'",launchTo:"'extension='+origin.prefix+'/mysqli.so'"},M=>assert.deepEqual(ready('audit',M).description.argv.filter(arg=>arg.startsWith('extension=')),['extension=/owned/adb-ppa-runtime-123/mysqlnd.so','extension=/owned/adb-ppa-runtime-123/mysqli.so'])],
    [{launchFrom:"row.release==='closed'",launchTo:'true'},M=>{const m=new M.PpaLaunchPreparation(origin());assert.throws(()=>m.sourcesClosed({...closed(m),release:'unknown'}));}],
    [{functionFrom:'row.binding===binding',functionTo:'true'},M=>{const {model}=started('installer',M);const p=packet(model,'installer');p.binding='0'.repeat(64);assert.throws(()=>model.functions(p));}],
    [{functionFrom:"item.review==='gated'",functionTo:'true'},M=>{const {model}=started('installer',M);const p=packet(model,'installer');p.rows.at(-1).review='unreviewed';assert.throws(()=>model.functions(p));}],
    [{functionFrom:'item.digest===sources[0].digest',functionTo:'true'},M=>{const {model}=started('installer',M);model.functions(packet(model,'installer'));const p=packet(model,'installer','post-core');p.rows.find(r=>r.name==='mb_strlen').digest='f'.repeat(64);assert.throws(()=>model.functions(p));}],
    [{launchFrom:'value===this.#description',launchTo:'true'},M=>{const v=ready('audit',M);assert.throws(()=>v.model.recovery(copy(v.description)));}],
    [{launchFrom:'limits[k]===RESOURCE_DATA[k]',launchTo:'true'},M=>{const p=origin();p.resources.codec_as=33554432;assert.throws(()=>new M.PpaLaunchPreparation(p));}],
    [{launchFrom:'index>=REQUEST_CASES.length',launchTo:'false'},M=>{const m=new M.PpaLaunchPreparation(origin());m.sourcesClosed(closed(m));assert.throws(()=>m.description('request',23));}],
  ];
  let killed=0;for(const [edit,oracle]of controls){const M=await mutated(edit);
    assert.throws(()=>oracle(M),error=>error.code==='ERR_ASSERTION');killed++;
  }assert.equal(killed,10);
});
