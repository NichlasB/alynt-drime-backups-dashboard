import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {spawnSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
import {RequestDiagnosticStream,REQUEST_DIAGNOSTIC_PREFIX} from '../../scripts/ci/source-policy-http-request-diagnostic.mjs';
import {setupFailureSummary} from '../../scripts/ci/source-policy-http-owned-controller.mjs';
const root=new URL('../../',import.meta.url),path=f=>fileURLToPath(new URL(f,root));
const observer=readFileSync(path('scripts/ci/source-policy-http-owned-observer.php'),'utf8');
const request=readFileSync(path('scripts/ci/source-policy-http-request-diagnostic.php'),'utf8');
const common=readFileSync(path('scripts/ci/source-policy-http-owned-diagnostic.php'),'utf8');
const assertion='a2d19317633ec1cec3dce5975aa0e3604cc4afba6aedcc747c06f07ca63effac';
function run(body,{o=observer,r=request,c=common}={}){
 const load=s=>`eval(base64_decode('${Buffer.from(s.replace(/^<\?php/,'')).toString('base64')}'));`;
 const code=load(c)+load(o)+load(r.replace("require_once __DIR__ . '/source-policy-http-owned-diagnostic.php';",''))+`function need($ok,$label){if(!$ok)throw new RuntimeException($label);}function adbh_check($ok,$label){if(!$ok){$GLOBALS['adbh_failure']=hash('sha256',$label);throw new RuntimeException('secret-sql-cookie');}}`+body;
 return spawnSync(process.env.PHP_BINARY??'php',['-r',code],{encoding:'utf8',timeout:5000,maxBuffer:8192});
}
function pass(r){assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stdout,'ok');}
const capture=String.raw`
define('ADB_HTTP_WORKER','request');$GLOBALS['adbh_stage']='core-bootstrap';$GLOBALS['adbh_private']=array('prefix'=>'adbh_abcdef123456_');$GLOBALS['adbh_ledger']=array('setup'=>0,'bootstrap'=>0,'auth'=>0);
$good="SELECT option_value FROM adbh_abcdef123456_options WHERE option_name='blogname'";
need($good===adbh_owned_observe($good)&&1===$GLOBALS['adbh_ledger']['bootstrap'],'positive-bytes-and-count');
try{adbh_owned_observe('TRUNCATE TABLE foreign');need(false,'throw-required');}catch(RuntimeException $e){$packet=adbh_request_diagnostic($e,1,'/owned');need(isset($packet['sql']),'request-capture');need(array('branch'=>'verb','caller'=>'unavailable','line'=>0)===$packet['sql'],'fixed-capture');need(adbh_request_report($e,1,'/owned'),'report-positive');}
$first=$GLOBALS['adbh_sql_refusal'];$GLOBALS['adbh_private']['prefix']='bad';try{adbh_owned_observe('SELECT 1');}catch(RuntimeException $e){}
need($first===$GLOBALS['adbh_sql_refusal'],'first-retained');need(1===$GLOBALS['adbh_ledger']['bootstrap'],'pre-accounting-refusal');echo 'ok';`;
test('actual request-bootstrap refusal reaches PHP/JS with first-only bounded facts while allowed bytes/counts stay unchanged',()=>{
 const r=run(capture);pass(r);const d=new RequestDiagnosticStream();d.push(Buffer.from(r.stderr));const row=d.evidence(1);assert.deepEqual(row.sql,{branch:'verb',caller:'unavailable',line:0});assert.equal(row.assertion,assertion);assert.equal(row.exception,'RuntimeException');assert.ok(Buffer.byteLength(r.stderr)<=1024);assert.doesNotMatch(r.stderr,/secret|TRUNCATE|foreign|\/owned/);const copy=d.evidence(1);copy.sql.branch='secret';assert.equal(d.evidence(1).sql.branch,'verb');
});
const sql={branch:'verb',caller:'option',line:1645},base={event:'owned-request-refused',exchange:1,stage:'core-bootstrap',exception:'RuntimeException',origin:'proof',line:22,assertion};
const decode=v=>{const d=new RequestDiagnosticStream();d.push(Buffer.from(REQUEST_DIAGNOSTIC_PREFIX+JSON.stringify(v)+'\n'));return d.evidence(1);};
test('legacy packets survive; optional nested SQL requires exact binding and rejects malformed or stale facts',()=>{
 assert.deepEqual(decode(base),base);assert.deepEqual(decode({...base,sql}),{...base,sql});
 for(const delta of [{stage:'login'},{stage:'shutdown'},{exception:'Error'},{assertion:'a'.repeat(64)},{exchange:0}])assert.equal(decode({...base,sql,...delta}),null);
 for(const delta of [{branch:'private'},{caller:'/private'},{line:100001},{line:-1},{line:'1'},{caller:'unavailable'},{raw:'private'}])assert.equal(decode({...base,sql:{...sql,...delta}}),null);
 for(const bad of [null,[],{},'private'])assert.equal(decode({...base,sql:bad}),null);
});
test('shared PHP SQL projection preserves setup behavior and refuses request attachments outside bootstrap or non-SQL failures',()=>{
 const body=String.raw`$GLOBALS['adbh_stage']='core-bootstrap';$GLOBALS['adbh_failure']=hash('sha256','sql-shape');$GLOBALS['adbh_sql_refusal']=array('branch'=>'verb','caller'=>'option','line'=>1645);$e=new RuntimeException('secret');
need(isset(adbh_setup_diagnostic($e,'/owned')['sql']),'setup-compatibility');need(isset(adbh_request_diagnostic($e,1,'/owned')['sql']),'request-positive');
foreach(array('route','login','bridge','shutdown','receipt') as $stage){$GLOBALS['adbh_stage']=$stage;need(!isset(adbh_request_diagnostic($e,1,'/owned')['sql']),'stage-binding');}
$GLOBALS['adbh_stage']='core-bootstrap';need(!isset(adbh_request_diagnostic(null,1,'/owned')['sql']),'no-error');need(!isset(adbh_request_diagnostic(new Error('secret'),1,'/owned')['sql']),'exception-binding');need(!isset(adbh_request_diagnostic($e,0,'/owned')['sql']),'exchange-binding');$GLOBALS['adbh_failure']=hash('sha256','other');need(!isset(adbh_request_diagnostic($e,1,'/owned')['sql']),'assertion-binding');echo 'ok';`;
 const r=run(body);pass(r);assert.equal(r.stderr,'');
 for(const stage of ['route','core-ready','login','bridge','shutdown']){const r=run(capture.slice(0,capture.indexOf('$good=')).replace("'core-bootstrap'",JSON.stringify(stage))+`try{adbh_owned_observe('TRUNCATE TABLE foreign');}catch(RuntimeException $e){}need(!isset($GLOBALS['adbh_sql_refusal']),'capture-stage-binding');echo 'ok';`);pass(r);assert.equal(r.stderr,'');}
});
test('all existing branch/caller labels round-trip in both packet types without introducing new labels',()=>{
 const branches=['prefix','tokens-input','tokens-escape','tokens-literal','tokens-skeleton','show-tables','expiry-shape','expiry-values','verb','tables','table-scope','write-scope'];
 const callers=['unavailable','wpdb','other-core','upgrade','schema','option','user','meta','functions','load','post','taxonomy','pluggable','capabilities','formatting'];
 for(const key of ['branch','caller'])for(const value of key==='branch'?branches:callers){const fact={...sql,[key]:value,line:key==='caller'&&value==='unavailable'?0:1645};const r=run(`$GLOBALS['adbh_stage']='core-bootstrap';$GLOBALS['adbh_failure']=hash('sha256','sql-shape');$GLOBALS['adbh_sql_refusal']=array('branch'=>'${fact.branch}','caller'=>'${fact.caller}','line'=>${fact.line});need(adbh_request_report(new RuntimeException('secret'),1,'/owned'),'available');echo 'ok';`);pass(r);assert.ok(Buffer.byteLength(r.stderr)<=1024);const d=new RequestDiagnosticStream();d.push(Buffer.from(r.stderr));assert.deepEqual(d.evidence(1).sql,fact);const setup={event:'owned-setup-refused',stage:'core-bootstrap',exception:'RuntimeException',origin:'proof',line:22,assertion,sql:fact};assert.deepEqual(setupFailureSummary(1,null,'',JSON.stringify(setup)).detail,setup);}
});
test('capture and attachment availability, stage binding and nested-copy controls are assertion-killed',async()=>{
 const blocked=run(capture,{o:observer.replace("'request' === ADB_HTTP_WORKER &&",'false &&')});assert.notEqual(blocked.status,0);assert.match(blocked.stderr,/RuntimeException: request-capture/);assert.doesNotMatch(blocked.stderr,/Parse error|syntax error/);
 const stale=run(`$GLOBALS['adbh_stage']='shutdown';$GLOBALS['adbh_failure']=hash('sha256','sql-shape');$GLOBALS['adbh_sql_refusal']=array('branch'=>'verb','caller'=>'option','line'=>1);need(!isset(adbh_request_diagnostic(new RuntimeException('secret'),1,'/owned')['sql']),'php-stage-binding');echo 'ok';`,{r:request.replace("'core-bootstrap' === $stage &&",'true &&')});assert.notEqual(stale.status,0);assert.match(stale.stderr,/RuntimeException: php-stage-binding/);assert.doesNotMatch(stale.stderr,/Parse error|syntax error/);
 const moduleSource=readFileSync(path('scripts/ci/source-policy-http-request-diagnostic.mjs'),'utf8');
 const controls=[
  ["row.stage!=='core-bootstrap'","false",m=>{const d=new m.RequestDiagnosticStream();d.push(Buffer.from(REQUEST_DIAGNOSTIC_PREFIX+JSON.stringify({...base,stage:'login',sql})+'\n'));assert.equal(d.evidence(1),null);}],
  ["exception!=='RuntimeException'","false",m=>{const d=new m.RequestDiagnosticStream();d.push(Buffer.from(REQUEST_DIAGNOSTIC_PREFIX+JSON.stringify({...base,exception:'Error',sql})+'\n'));assert.equal(d.evidence(1),null);}],
  ["assertion!=='a2d19317633ec1cec3dce5975aa0e3604cc4afba6aedcc747c06f07ca63effac'","false",m=>{const d=new m.RequestDiagnosticStream();d.push(Buffer.from(REQUEST_DIAGNOSTIC_PREFIX+JSON.stringify({...base,assertion:'a'.repeat(64),sql})+'\n'));assert.equal(d.evidence(1),null);}],
  ['sql:{...this.#first.sql}','sql:this.#first.sql',m=>{const d=new m.RequestDiagnosticStream();d.push(Buffer.from(REQUEST_DIAGNOSTIC_PREFIX+JSON.stringify({...base,sql})+'\n'));d.evidence(1).sql.branch='secret';assert.equal(d.evidence(1).sql.branch,'verb');}],
 ];
 for(const [before,after,oracle]of controls){assert.equal(moduleSource.split(before).length,2);const edited=moduleSource.replace(before,after).replace("'./source-policy-http-profile.mjs'",JSON.stringify(new URL('../../scripts/ci/source-policy-http-profile.mjs',import.meta.url).href));const m=await import('data:text/javascript;base64,'+Buffer.from(edited).toString('base64'));assert.throws(()=>oracle(m),e=>e.code==='ERR_ASSERTION');}
});
