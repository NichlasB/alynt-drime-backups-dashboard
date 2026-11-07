import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync,writeFileSync,mkdtempSync,rmSync,existsSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';
import {createHash} from 'node:crypto';
import {installerSidecarMatches,preparedContainedHttpBatch,INSTALLER_EXECUTION_ENABLED} from '../../scripts/ci/source-policy-http-installer.mjs';
import * as f from './source-policy-http-fixtures.mjs';
const root=new URL('../../',import.meta.url),php=process.env.PHP_BINARY??'php';
const files=['scripts/ci/source-policy-http-installer.php','scripts/ci/source-policy-http-installer-data.php','tests/support/source-policy-http-installer-api.php','tests/support/source-policy-http-installer-cases.php'];
const originals=files.map(file=>readFileSync(new URL(file,root),'utf8'));
const identity={run:'1'.repeat(32),head:'2'.repeat(40),image:'3'.repeat(64),root:'/tmp/owned'};
const literal=s=>"'"+s.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'";
function image(callback){
 const base=resolve(tmpdir()),prefix=join(base,'adb-http-installer-'),temporary=mkdtempSync(prefix);
 assert.ok(resolve(temporary).startsWith(prefix)&&resolve(temporary)!==base);
 try{
  const paths=files.map((file,i)=>{const p=join(temporary,file.split('/').at(-1));writeFileSync(p,originals[i]);return p;});
  const execute=(scenario,extra='')=>spawnSync(php,['-n','-d','display_errors=stderr','-d','disable_functions=json_encode,json_decode','-r',`define('ADBH_INSTALLER_ISOLATED_API',true);require ${literal(paths[0])};require ${literal(paths[2])};${extra}require ${literal(paths[3])};`,scenario],{encoding:'utf8',timeout:5000,maxBuffer:32768});
  return callback({paths,execute,temporary});
 }finally{rmSync(temporary,{recursive:true,force:true});assert.equal(existsSync(temporary),false);}
}
const passed=r=>{assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.match(r.stdout,/checks:[1-9][0-9]*\n$/);return r.stdout;};
function sidecar(output){
 const line=output.split('\n').find(s=>s.startsWith('sidecar:'));assert.ok(line);const v=line.slice(8).split('|');assert.equal(v.length,13);
 const keys=['model','run','head','image','owner','stage','http','hard','mail','violation','completed','runtime_grant','production_ready'];
 return Object.fromEntries(keys.map((k,i)=>[k,i<6?v[i]:i<9?Number(v[i]):v[i]==='true']));
}
test('installer definitions import without core, hooks, authentication, storage or executor',()=>{
 const path=fileURLToPath(new URL(files[0],root));
 const r=spawnSync(php,['-n','-r',`require ${literal(path)};if(class_exists('WP_Hook',false)||class_exists('WP_Error',false)||defined('ABSPATH'))throw new RuntimeException('effect');try{(new Adbh_Installer_Containment(array('run'=>str_repeat('1',32),'head'=>str_repeat('2',40),'image'=>str_repeat('3',64),'root'=>'/tmp/owned'),'setup'))->register();throw new AssertionError('registered');}catch(RuntimeException $e){echo 'dormant';}`],{encoding:'utf8',timeout:5000});
 assert.equal(r.status,0,r.stderr);assert.equal(r.stdout,'dormant');assert.equal(r.stderr,'');assert.equal(INSTALLER_EXECUTION_ENABLED,false);
});
test('positive actual callback algorithms produce compatible setup/request sidecars with no fake IO effects',()=>image(({execute})=>{
 for(const purpose of ['positive','request']){const row=sidecar(passed(execute(purpose)));assert.equal(installerSidecarMatches(row,identity,purpose==='positive'?'setup':'request'),true);}
}));
test('unarmed or application-present fixture discovery cannot redeclare APIs or run cases',()=>{
 const api=fileURLToPath(new URL(files[2],root)),cases=fileURLToPath(new URL(files[3],root));
 for(const prelude of ['',"define('ADBH_INSTALLER_ISOLATED_API',true);define('ABSPATH','fixture-only');"]){
  const code=`${prelude}class WP_Hook{};function add_filter(){};require ${literal(api)};require ${literal(cases)};if(function_exists('fresh')||isset($GLOBALS['effects']))throw new AssertionError('fixture armed');echo 'discovery-safe';`;
  const r=spawnSync(php,['-n','-r',code],{encoding:'utf8',timeout:5000});assert.equal(r.status,0,r.stderr);assert.equal(r.stderr,'');assert.equal(r.stdout,'discovery-safe');
 }
 let owned;assert.throws(()=>image(({temporary})=>{owned=temporary;throw Error('fixture-error');}),/fixture-error/);assert.equal(existsSync(owned),false);
});
test('actual callbacks deny malformed, unknown, missing, reordered, stale and late events without becoming success',()=>image(({execute})=>{
 const cases=['data','post-mismatch','short','mail','late','unknown','order','preemption','no-error','foreign','foreign-late','registry','repeat','purpose','registration','local',...['permalinks','files_equal','installing','source_matches'].map(s=>'fact-'+s),...['method','timeout','redirection','httpversion','user-agent','reject_unsafe_urls','blocking','headers','cookies','body','compress','decompress','sslverify','sslcertificates','stream','filename','limit_response_size','_redirection','extra','missing','object'].map(s=>'arg-'+s),...['query','encoded','host','port','order','long','object'].map(s=>'url-'+s)];
 for(const scenario of cases)passed(execute(scenario));
}));
test('sidecar exact identity/types/stage/completion are DATA consistency, never a runtime grant',()=>image(({execute})=>{
 const row=sidecar(passed(execute('positive')));
 for(const [key,value]of Object.entries(row)){const bad={...row,[key]:typeof value==='string'?value+'x':typeof value==='number'?value+1:!value};assert.equal(installerSidecarMatches(bad,identity,'setup'),false,key);}
 for(const bad of [null,{},[],{...row,extra:true},Object.assign(Object.create(null),row)])assert.equal(installerSidecarMatches(bad,identity,'setup'),false);
 assert.equal(installerSidecarMatches(row,{...identity,root:'/tmp/foreign'},'setup'),false);assert.equal(installerSidecarMatches(row,identity,'request'),false);
 const hostile={...row};Object.defineProperty(hostile,'http',{get(){throw Error('getter');},enumerable:true});assert.equal(installerSidecarMatches(hostile,identity,'setup'),false);
}));
test('required callback guards have assertion-killed source controls, not parser-failure credit',()=>image(({paths,execute})=>{
 const source=originals[0].replaceAll('\r\n','\n');const controls=[
  [[['return new WP_Error( \'adbh_http_blocked\' );','return false;']],'positive'],
  [[['\t\treturn false;\n\t}\n\n\t/**\n\t * Defense','\t\treturn true;\n\t}\n\n\t/**\n\t * Defense']],'positive'],
  [[["$this->state['bad'] = true;","$this->state['bad'] = false;"]],'late'],
  [[["adbh_installer_urls( $post ) !== $this->urls",'false']],'post-mismatch'],
  [[["'hard', 'http', 'hard', 'http', 'hard', 'hard'","'hard', 'http', 'hard', 'http', 'hard'"],["6 !== $this->state['step'] || 2 !==", "5 !== $this->state['step'] || 2 !=="],["4 !== $this->state['hard']", "3 !== $this->state['hard']"]],'short'],
  [[['$this->ready();','/* test-only registry bypass */']],'foreign-late'],
  [[['$args === $expected','true']],'arg-timeout'],
  [[['! adbh_installer_identity( $identity )','false']],'data'],
  [[["'id'      => $captured","'id'      => $identity"]],'data']
 ];
 for(const [edits,scenario]of controls){let changed=source;for(const[from,to]of edits){assert.ok(changed.includes(from),from);changed=changed.replaceAll(from,to);}writeFileSync(paths[0],changed);try{const r=execute(scenario);assert.equal(r.error,undefined);assert.notEqual(r.status,0);assert.match(r.stderr,/AssertionError/);assert.doesNotMatch(r.stderr,/Parse error|TypeError/);}finally{writeFileSync(paths[0],source);assert.equal(readFileSync(paths[0],'utf8'),source);}}
 const data=originals[1],changed=data.replace(/('compress'\s*=> )false/,'$1true');assert.notEqual(changed,data);writeFileSync(paths[1],changed);
 try{const r=execute('positive');assert.equal(r.error,undefined);assert.notEqual(r.status,0);assert.match(r.stderr,/AssertionError/);assert.doesNotMatch(r.stderr,/Parse error|TypeError/);}finally{writeFileSync(paths[1],data);assert.equal(readFileSync(paths[1],'utf8'),data);}
}));
test('sidecar owner/count/availability checks independently kill semantic bypass controls',async()=>{
 const source=installerModule();
 const row=image(({execute})=>sidecar(passed(execute('positive'))));
  for(const [from,to,key,value]of [['row.owner===owner','true','owner','f'.repeat(64)],["row.hard===(stage==='setup'?4:0)",'true','hard',3],['row.completed===true','true','completed',false]]){
   assert.ok(source.includes(from));const url='data:text/javascript;base64,'+Buffer.from(source.replace(from,to)).toString('base64');const mod=await import(url);
   assert.throws(()=>assert.equal(mod.installerSidecarMatches({...row,[key]:value},identity,'setup'),false),assert.AssertionError);
  }
 for(let i=0;i<files.length;i++)assert.equal(readFileSync(new URL(files[i],root),'utf8'),originals[i]);
});
function installerModule(){
 let source=readFileSync(new URL('scripts/ci/source-policy-http-installer.mjs',root),'utf8');
 for(const file of ['source-policy-http-profile.mjs','source-policy-http-supervisor.mjs'])source=source.replace("'./"+file+"'",JSON.stringify(new URL('scripts/ci/'+file,root).href));
 return source;
}
function containedFixture(){
 const input={identity:f.identity(),profile:f.profile(),storage:f.storage(),manifest:f.manifest(),expectation:f.manifestExpectation(),challenge:f.challenge};
 const seen={prepare:0,start:0,exchange:0,release:0,cleanup:0},id=f.identity();let time=0,policy=f.digest;
 const packet=stage=>({model:'http-installer-containment-v1',run:id.run,head:id.head,image:id.image,owner:createHash('sha256').update([id.run,id.head,id.image,id.root].join(':')).digest('hex'),stage,http:stage==='setup'?2:0,hard:stage==='setup'?4:0,mail:stage==='setup'?1:0,violation:false,completed:true,runtime_grant:false,production_ready:false});
 const io={kind:'isolated-installer-http-io-v1',now:()=>time,
  prepare:async left=>{seen.prepare++;assert.ok(left>0&&left<=60000);time++;return{ledger:f.ledger('setup'),containment:packet('setup')};},
  start:async left=>{seen.start++;assert.ok(left>0&&left<=5000);time++;return{server:f.server(),profile:f.profile()};},
  live:async()=>f.server(),describe:async item=>f.meta(item.kind),
  exchange:async(item,left)=>{
   seen.exchange++;assert.ok(left>0&&left<=5000);time++;
   let result=null,expected=null;
   if(item.index!==null){result=f.result(item.index);expected=f.expected(item.index);policy=result.after;}
   else if(['view','nonce-control'].includes(item.kind)){
    expected={...id,before:policy,after:policy,sentinel:'f'.repeat(64),revision:'2'.repeat(64),mode:'external_optional'};
    result={...f.result(0),event:'http-view-complete',index:null,before:policy,after:policy,revision:expected.revision,mode:expected.mode};
   }
   const envelope={...f.response(),...(item.kind==='login-submit'?{status:302,location:'http://127.0.0.1:18777/_adb-owned-source-policy'}:result?{status:result.status}:{})};
   return{envelope,result,expected,bootstrap:f.ledger(),auth:f.ledger('auth'),containment:packet('request')};
  },reconcile:async()=>{seen.release++;return f.release();},cleanup:async()=>{seen.cleanup++;return f.cleanup();}
 };
 return{io,input,seen};
}
test('contained isolated composition requires both sidecars and preserves all 13 cases/25 exchanges/release/cleanup',async()=>{
 const {io,input,seen}=containedFixture(),row=await preparedContainedHttpBatch(io,input);
 assert.equal(row.completed,true);assert.equal(row.cases,13);assert.equal(row.transactions,25);assert.equal(row.runtime_grant,false);assert.equal(row.qualification,'not-established');
 assert.deepEqual(seen,{prepare:1,start:1,exchange:25,release:25,cleanup:1});
});
test('missing/forged/late sidecars stop before another exchange and still use original owned cleanup',async()=>{
 for(const stage of ['setup','request'])for(const value of [null,{violation:true},{http:99},{owner:'0'.repeat(64)}]){
  const {io,input,seen}=containedFixture(),method=stage==='setup'?'prepare':'exchange',original=io[method];
  io[method]=async(...args)=>{const row=await original(...args);row.containment=value===null?null:{...row.containment,...value};return row;};
  await assert.rejects(()=>preparedContainedHttpBatch(io,input),/HTTP preparation failed: /);assert.equal(seen.cleanup,1);assert.equal(seen.exchange,stage==='setup'?0:1);
 }
 const {io,input,seen}=containedFixture();await assert.rejects(()=>preparedContainedHttpBatch(io,{...input,profile:{...f.profile(),sapi:'fpm-fcgi'}}));assert.equal(seen.prepare,0);assert.equal(seen.cleanup,0);
});
test('composition sidecar omission at either actual call site is assertion-killed',async()=>{
 const source=installerModule();
 for(const stage of ['setup','request']){
  const from=`!installerSidecarMatches(row.containment,id,'${stage}')`;assert.ok(source.includes(from));
  const code=source.replace(from,'false'),mod=await import('data:text/javascript;base64,'+Buffer.from(code).toString('base64'));
  const {io,input}=containedFixture(),method=stage==='setup'?'prepare':'exchange',original=io[method];
  io[method]=async(...args)=>{const row=await original(...args);row.containment.violation=true;return row;};
  await assert.rejects(()=>assert.rejects(()=>mod.preparedContainedHttpBatch(io,input)),assert.AssertionError);
 }
});
