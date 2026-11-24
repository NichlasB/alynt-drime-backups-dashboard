import test from 'node:test';
import assert from 'node:assert/strict';
import {createHash} from 'node:crypto';
import {readFileSync} from 'node:fs';
import * as C from '../../scripts/ci/source-policy-ppa-function-catalog.mjs';
import * as F from '../../scripts/ci/source-policy-ppa-function-data.mjs';
import {HTTP_PROFILE} from '../../scripts/ci/source-policy-http-profile.mjs';

const root=new URL('../../',import.meta.url),copy=v=>JSON.parse(JSON.stringify(v));
const sources=()=>copy(C.SOURCE_CATALOG.anchors);
const algorithms=()=>({hash:['sha256','sha384'],hmac:['md5','sha256','sha384'],password:['bcrypt']});
const fallback=()=>({encoding:'UTF-8',rows:['mb_strlen','mb_substr'].map(name=>({name,available:true,provider:'core',
  source:'core/wp-includes/compat.php',digest:'6687dd4bf958e127a9d9df3fcc70545d8a0bea2d94410dc2a38b6ed2ee7df58b',review:'catalog-bound'}))});
function profile(role='audit',phase='pre-core'){
  const required=F.functionRequirements(role,phase);
  return {model:F.FUNCTION_MODEL,binding:'a'.repeat(64),role,phase,php:'8.5.12',word_size:8,zts:false,
    sapi:role==='server'&&phase==='post-core'?'cli-server':'cli',catalog:C.SOURCE_CATALOG_DIGEST,
    sources:phase==='post-core'?sources():[],algorithms:role==='environment'?{hash:['sha256','sha384'],hmac:['sha256','sha384'],password:[]}:algorithms(),
    encoding:phase==='post-core'?'UTF-8':null,consumers:copy(F.consumerRequirements(role,phase)),
    rows:[...required.native.map(name=>({name,available:true,provider:'native',source:'internal',digest:null,review:'required'})),
      ...(phase==='post-core'?fallback().rows:[]),
      ...required.absent.map(name=>({name,available:false,provider:'absent',source:null,digest:null,review:'catalog-absence'}))]};
}
const validate=(p,Module=F,catalog=C.SOURCE_CATALOG_DIGEST)=>Module.validateFunctionData(p,p.binding,p.role,p.phase,sources(),catalog);

test('catalog anchors match independent audited literals, not caller-generated hashes',()=>{
  const expected=[
    ['compat.php',22479,'6687dd4bf958e127a9d9df3fcc70545d8a0bea2d94410dc2a38b6ed2ee7df58b'],
    ['compat-utf8.php',19386,'7a922c1d15f8d1464c18d005d6e094126c4c82a85f672b1b9d8616db67f700ea'],
    ['class-wp-recovery-mode-cookie-service.php',6877,'0290d5e6b418968e1bc03b2618593a1822e148a6e19e4b7accd35aefd58f6b59'],
    ['class-wp-recovery-mode.php',11460,'cbb58ae15fd0cd848408313f2aab7eb05c94b0d1a983dcf6fe9f9e4bc76d4dbc'],
    ['pluggable.php',127919,'3a2482a65b50d62ebae75a728be98b78985d3a3f5a3e007cb2d0c1338bf05777'],
    ['class-wp-session-tokens.php',7319,'0708de7171949675139eda1e476935763f65e8a3a0cf40fc534dfffa00fb04cb'],
    ['class-wpdb.php',121437,'e15403e90032dd0508811301505e491d4212de5152581a98f51b744a1a3903bd'],
    ['option.php',105246,'fe3f0b7e115b2a50c31162ed6428abe4088c080b0f71b9678d84806168fe90d0'],
    ['../wp-settings.php',33152,'2e3185ee44bcdd9d9c4bef8ce4891827711dec2b5d5d4c08c951738ccef51f8d'],
  ].map(([name,size,digest])=>({path:name==='../wp-settings.php'?'core/wp-settings.php':'core/wp-includes/'+name,size,digest}));
  assert.deepEqual(C.SOURCE_CATALOG.anchors,expected);
  assert.equal(C.CATALOG_MODEL,'ppa-source-function-catalog-v1');assert.equal(C.SOURCE_CATALOG.model,C.CATALOG_MODEL);
  assert.equal(C.SOURCE_CATALOG.commit,'db96f5eaa77a8d3e8eec8923a518fa2326f0d46a');
  assert.equal(C.SOURCE_CATALOG.commit,HTTP_PROFILE.commit);
  assert.equal(C.SOURCE_CATALOG.manifest,'3b0b19969bfaf09c13eb89c1cf8a335eee2c3c984012e15cfb783a9f983725b8');
  const digest=v=>createHash('sha256').update(JSON.stringify(v)).digest('hex');
  assert.equal(C.SOURCE_CATALOG_DIGEST,digest(C.SOURCE_CATALOG));
  assert.notEqual(C.SOURCE_CATALOG_DIGEST,digest(C.SOURCE_CATALOG.anchors));
  assert.throws(()=>C.SOURCE_CATALOG.post_core[1].status='excluded');
  assert.throws(()=>C.SOURCE_CATALOG.algorithms.hmac.pop());
});
test('fixed sources have a positive copied immutable result and reject drift, aliases, omissions and order changes',()=>{
  const input=sources(),out=F.coreSourceData(input);input[0].digest='f'.repeat(64);
  assert.equal(out[0].digest,C.SOURCE_CATALOG.anchors[0].digest);assert.ok(Object.isFrozen(out[0]));
  for(const change of [s=>s[0].digest='f'.repeat(64),s=>s[0].size++,s=>s[0].path='core/wp-includes/../compat.php',
    s=>s.reverse(),s=>s.pop(),s=>s.push(s[0]),s=>s[0].commit='other',s=>s[0].size=String(s[0].size)]){
    const input=sources();change(input);assert.throws(()=>F.coreSourceData(input));
  }
});
test('algorithm DATA has independent positive cases and exact hash, HMAC and password domains',()=>{
  for(const role of F.LAUNCH_ROLES){
    const p=algorithms();if(role==='environment'){p.password=[];p.hmac.shift();}
    assert.equal(F.validateAlgorithmData(p,role).algorithm_data_complete,true);
    assert.equal(F.validateAlgorithmData(p,role).native_admitted,false);
  }
  assert.deepEqual(C.SOURCE_CATALOG.algorithms,algorithms());
  for(const change of [p=>p.hmac.shift(),p=>p.hmac[0]='sha1',p=>p.hmac=['sha256','sha384'],
    p=>p.hash=['md5','sha256'],p=>p.password=[],p=>p.password=['argon2id'],p=>p.hmac.push('md5'),
    p=>p.hmac.reverse(),p=>p.hmac=true,p=>p.bcrypt=true,p=>p.hash[0]=true]){
    const p=algorithms();change(p);assert.throws(()=>F.validateAlgorithmData(p,'request'));
  }
  assert.throws(()=>F.validateAlgorithmData(algorithms(),'setup'));
});
test('pre-core phase has a positive exclusion DATA case without physical closure or grants',()=>{
  for(const role of F.LAUNCH_ROLES){
    const uses=F.consumerRequirements(role,'pre-core');
    assert.deepEqual(uses.map(u=>[u.id,u.status,u.condition]),[['stock-bootstrap','excluded','pre-core-only']]);
    const result=F.validateConsumerData(copy(uses),role,'pre-core');
    assert.equal(result.consumer_data_complete,true);assert.equal(result.consumer_closure_verified,false);
    assert.equal(result.native_admitted,false);
  }
  const result=validate(profile());assert.equal(result.profile_complete,true);
  assert.equal(result.source_observed,false);assert.equal(result.consumer_closure_verified,false);
});
test('post-core catalog distinguishes required, guarded and UNKNOWN; invented exclusions and incomplete lists refuse',()=>{
  for(const role of ['installer','initializer','server']){
    const uses=F.consumerRequirements(role,'post-core');
    assert.deepEqual(uses.map(u=>[u.id,u.status]),[['mb-explicit-utf8','required'],['mb-default-encoding','unknown'],
      ['recovery-cookie-ctype','unknown'],['iconv-guarded-use','guarded'],['other-core-vendor-uses','unknown']]);
    assert.throws(()=>F.validateConsumerData(copy(uses),role,'post-core'),/consumer-unresolved/);
    assert.throws(()=>validate(profile(role,'post-core')),/consumer-unresolved/);
    for(const change of [v=>v[2].status='excluded',v=>v[2].condition='cookie-absent',v=>v[2].digest='f'.repeat(64),
      v=>v.pop(),v=>v.shift(),v=>v.reverse(),v=>v.push({...v[0]})]){
      const changed=copy(uses);change(changed);assert.throws(()=>F.validateConsumerData(changed,role,'post-core'));
    }
  }
  assert.throws(()=>F.consumerRequirements('audit','post-core'));
});
test('fallback subset is explicitly UTF-8; null/default encodings cannot imply zero option effects',()=>{
  const result=F.validateFallbackData(fallback(),sources());
  assert.equal(result.fallback_data_complete,true);assert.equal(result.source_observed,false);assert.equal(result.native_admitted,false);
  for(const encoding of [null,undefined,'utf8','8bit','UTF-16',{},true]){
    const p=fallback();p.encoding=encoding;assert.throws(()=>F.validateFallbackData(p,sources()));
  }
  for(const change of [p=>p.rows.reverse(),p=>p.rows[0].available=false,p=>p.rows[1].provider='native',
    p=>p.rows[0].source='core/wp-includes/compat-utf8.php',p=>p.rows[0].review='source-reviewed',p=>p.extra=true]){
    const p=fallback();change(p);assert.throws(()=>F.validateFallbackData(p,sources()));
  }
});
test('old model, caller-derived catalog digest, wrong phase and hand-written global gating never migrate silently',()=>{
  for(const change of [p=>p.model='ppa-function-preparation-v1',p=>p.catalog='f'.repeat(64),
    p=>p.encoding='UTF-8',p=>p.consumers=F.consumerRequirements('installer','post-core'),
    p=>p.rows.at(-1).review='gated',p=>p.rows.at(-1).available=true]){
    const p=profile();change(p);assert.throws(()=>validate(p));
  }
  const p=profile();p.catalog='f'.repeat(64);assert.throws(()=>validate(p,F,p.catalog));
});
test('nested DATA getters and exotic lists are rejected without invocation',()=>{
  let calls=0;const getter=()=>{calls++;throw Error('private');};
  for(const target of ['algorithm-record','algorithm-list','consumer-record','consumer-list','source-size','fallback-row']){
    if(target==='algorithm-record'||target==='algorithm-list'){
      const p=algorithms();Object.defineProperty(target==='algorithm-record'?p:p.hmac,target==='algorithm-record'?'hmac':'0',{get:getter});
      assert.throws(()=>F.validateAlgorithmData(p,'audit'));
    }else if(target.startsWith('consumer')){
      const p=copy(F.consumerRequirements('audit','pre-core'));
      Object.defineProperty(target==='consumer-record'?p[0]:p,target==='consumer-record'?'condition':'0',{get:getter});
      assert.throws(()=>F.validateConsumerData(p,'audit','pre-core'));
    }else if(target==='source-size'){
      const p=sources();Object.defineProperty(p[0],'size',{get:getter});assert.throws(()=>F.coreSourceData(p));
    }else{const p=fallback();Object.defineProperty(p.rows[0],'digest',{get:getter});assert.throws(()=>F.validateFallbackData(p,sources()));}
  }
  const p=algorithms();Object.setPrototypeOf(p.hmac,null);assert.throws(()=>F.validateAlgorithmData(p,'audit'));
  assert.equal(calls,0);
});
async function mutant(from,to){
  let text=readFileSync(new URL('scripts/ci/source-policy-ppa-function-data.mjs',root),'utf8');
  assert.ok(text.includes(from));text=text.replace(from,to);
  for(const name of ['source-policy-http-profile.mjs','source-policy-ppa-function-catalog.mjs']){
    text=text.replaceAll("'./"+name+"'",JSON.stringify(new URL('scripts/ci/'+name,root).href));
  }
  return import('data:text/javascript;base64,'+Buffer.from(text).toString('base64'));
}
test('eleven source-backed semantic controls are assertion-killed,including always-unavailable and UNKNOWN bypass',async()=>{
  const controls=[
    ['row.digest===pin.digest','true',M=>{const p=sources();p[0].digest='f'.repeat(64);assert.throws(()=>M.coreSourceData(p));}],
    ['row.size===pin.size','true',M=>{const p=sources();p[0].size++;assert.throws(()=>M.coreSourceData(p));}],
    ['catalog===SOURCE_CATALOG_DIGEST','true',M=>{const p=profile();p.catalog='f'.repeat(64);assert.throws(()=>validate(p,M,p.catalog));}],
    ["for(const domain of ['hash','hmac','password'])","for(const domain of ['hash','password'])",M=>{const p=algorithms();p.hmac.shift();assert.throws(()=>M.validateAlgorithmData(p,'audit'));}],
    ['names.every((name,i)=>name===expected[domain][i])','true',M=>{const p=algorithms();p.password=['argon2id'];assert.throws(()=>M.validateAlgorithmData(p,'audit'));}],
    ["row.encoding==='UTF-8'",'true',M=>{const p=fallback();p.encoding=null;assert.throws(()=>M.validateFallbackData(p,sources()));}],
    ["item.provider==='core'",'true',M=>{const p=fallback();p.rows[0].provider='native';assert.throws(()=>M.validateFallbackData(p,sources()));}],
    ['Object.keys(expected[i]).every(key=>row[key]===expected[i][key])','true',M=>{const p=copy(F.consumerRequirements('audit','pre-core'));p[0].digest='f'.repeat(64);assert.throws(()=>M.validateConsumerData(p,'audit','pre-core'));}],
    ["!expected.some(use=>use.status==='unknown')",'true',M=>assert.throws(()=>validate(profile('installer','post-core'),M))],
    ['profile_complete:true','profile_complete:false',M=>assert.equal(validate(profile(),M).profile_complete,true)],
  ];
  let killed=0;for(const [from,to,oracle]of controls){const M=await mutant(from,to);
    assert.throws(()=>oracle(M),error=>error.code==='ERR_ASSERTION',from);killed++;
  }assert.equal(killed,10);
  const M=await mutant('algorithm_data_complete:true',"algorithm_data_complete:(()=>{throw Error('unavailable')})()");
  assert.throws(()=>assert.doesNotThrow(()=>M.validateAlgorithmData(algorithms(),'audit')),error=>error.code==='ERR_ASSERTION');
});
