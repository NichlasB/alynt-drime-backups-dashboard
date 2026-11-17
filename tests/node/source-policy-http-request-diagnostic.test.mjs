import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {spawnSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
import {RequestDiagnosticStream,readRequiredReceipt,requestFailureSummary,REQUEST_DIAGNOSTIC_PREFIX} from '../../scripts/ci/source-policy-http-request-diagnostic.mjs';
const phpFile=fileURLToPath(new URL('../../scripts/ci/source-policy-http-request-diagnostic.php',import.meta.url));
const phpSource=()=>readFileSync(phpFile,'utf8');
const wire={event:'owned-request-refused',exchange:1,stage:'core-bootstrap',exception:'RuntimeException',origin:'proof',line:22,assertion:'a'.repeat(64)};
const frame=v=>Buffer.from(REQUEST_DIAGNOSTIC_PREFIX+JSON.stringify(v)+'\n');
function run(body,source=null,ini=[]){const dependency=fileURLToPath(new URL('../../scripts/ci/source-policy-http-owned-diagnostic.php',import.meta.url));const load=source===null?`require ${JSON.stringify(phpFile)};`:`eval(base64_decode('${Buffer.from(source.replace(/^<\?php/,'').replace("__DIR__ . '/source-policy-http-owned-diagnostic.php'",JSON.stringify(dependency))).toString('base64')}'));`;return spawnSync(process.env.PHP_BINARY??'php',[...ini,'-r',load+body],{encoding:'utf8',timeout:5000,maxBuffer:8192});}
function passed(r){assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);}
test('actual PHP packet and first-only stderr report expose fixed DATA, never error text or raw paths',()=>{
 const r=run(`$GLOBALS['adbh_stage']='core-bootstrap';$GLOBALS['adbh_failure']=str_repeat('a',64);$GLOBALS['adbh_diagnostic_location']=array('origin'=>'proof','line'=>22);$e=new RuntimeException('secret-sql-cookie-password');if(!adbh_request_report($e,1,'/owned'))throw new RuntimeException('first-report');$GLOBALS['adbh_stage']='shutdown';if(adbh_request_report(new Error('secret'),1,'/owned'))throw new RuntimeException('first-only');echo 'ok';`);passed(r);assert.equal(r.stdout,'ok');assert.ok(Buffer.byteLength(r.stderr)<=1024);assert.deepEqual(JSON.parse(r.stderr.slice(REQUEST_DIAGNOSTIC_PREFIX.length)),wire);assert.doesNotMatch(r.stderr,/secret|\/owned/);
 const stream=new RequestDiagnosticStream();stream.push(Buffer.from(r.stderr));assert.deepEqual(stream.evidence(1),wire);
});
test('actual router catch bodies preserve first diagnostics and 503 without producing a receipt; output failure stays unavailable',()=>{
 const router=readFileSync(new URL('../../scripts/ci/source-policy-http-owned-router.php',import.meta.url),'utf8');
 const catches=[...router.matchAll(/\} catch \( Throwable \$error \) \{\s*(adbh_request_report\([^]*?http_response_code\( 503 \);)/g)];assert.equal(catches.length,2);
 for(const [i,m]of catches.entries()){const r=run(`$root='/owned';$exchange='1';$GLOBALS['adbh_stage']='${i===0?'shutdown':'core-bootstrap'}';$error=new RuntimeException('secret');${m[1]}if(503!==http_response_code())throw new RuntimeException('status-preserved');echo 'no-receipt';`);passed(r);assert.equal(r.stdout,'no-receipt');const fact=JSON.parse(r.stderr.slice(REQUEST_DIAGNOSTIC_PREFIX.length));assert.equal(fact.stage,i===0?'shutdown':'core-bootstrap');assert.equal(fact.exception,'RuntimeException');assert.equal(fact.exchange,1);assert.equal(fact.assertion,null);}
 const r=run(`$GLOBALS['adbh_stage']='login';if(adbh_request_report(new Error('secret'),1,'/owned'))throw new RuntimeException('output-unavailable');echo 'unavailable';`,null,['-d','disable_functions=file_put_contents']);passed(r);assert.equal(r.stdout,'unavailable');assert.equal(r.stderr,'');
});
test('PHP stages/types/ranges are allowlisted and malformed location/assertion inputs cannot escape',()=>{
 const stages=['exchange','route','early-hooks','core-bootstrap','core-ready','login','bridge','shutdown','receipt','unavailable'];
 for(const stage of stages){const r=run(`$GLOBALS['adbh_stage']=${JSON.stringify(stage)};$p=adbh_request_diagnostic(null,64,'/owned');echo $p['stage'].'|'.$p['exchange'].'|'.$p['exception'].'|'.$p['line'];`);passed(r);assert.equal(r.stderr,'');assert.equal(r.stdout,stage+'|64|unavailable|0');}
 const r=run(`$GLOBALS['adbh_stage']='secret';$GLOBALS['adbh_failure']='secret';$GLOBALS['adbh_diagnostic_location']=array('origin'=>'/private','line'=>100001);echo implode('|',array_map(static function($v){return null===$v?'null':$v;},adbh_request_diagnostic(new RuntimeException('secret'),65,'/owned')));`);passed(r);assert.equal(r.stderr,'');assert.equal(r.stdout,'owned-request-refused|0|unavailable|RuntimeException|other|0|null');
});
test('stream decoder handles every chunk boundary and retains only the first exact matching-exchange fact',()=>{
 const bytes=frame(wire);for(let i=0;i<=bytes.length;i++){const d=new RequestDiagnosticStream();d.push(bytes.subarray(0,i));d.push(bytes.subarray(i));assert.deepEqual(d.evidence(1),wire);assert.equal(d.evidence(2),null);d.push(frame({...wire,stage:'shutdown'}));assert.deepEqual(d.evidence(1),wire);const copy=d.evidence(1);copy.stage='secret';assert.deepEqual(d.evidence(1),wire);}
 const d=new RequestDiagnosticStream();d.push(Buffer.from('private server log\n'+ 'x'.repeat(2048)+'\n'));d.push(frame(wire));assert.deepEqual(d.evidence(1),wire);
});
test('unknown/malformed/extra/stale fields and overlong frames never become diagnostics or grants',()=>{
 for(const delta of [{event:'secret'},{exchange:65},{exchange:'1'},{stage:'secret'},{exception:'secret'},{origin:'/private'},{line:100001},{line:'22'},{assertion:'secret'},{raw:'secret'}]){const d=new RequestDiagnosticStream();d.push(frame({...wire,...delta}));assert.equal(d.evidence(1),null);}
 for(const stage of ['exchange','route','early-hooks','core-bootstrap','core-ready','login','bridge','shutdown','receipt','unavailable']){const d=new RequestDiagnosticStream();d.push(frame({...wire,stage}));assert.equal(d.evidence(1).stage,stage);}
 const d=new RequestDiagnosticStream();d.push(Buffer.from(REQUEST_DIAGNOSTIC_PREFIX+' '.repeat(1024)+JSON.stringify(wire)+'\n'));assert.equal(d.evidence(1),null);d.push(frame(wire));assert.deepEqual(d.evidence(1),wire);
});
const response={status:503,body_bytes:0,header_bytes:128,eof:true,body:Buffer.from('secret'),cookies:['secret'],location:'secret'};
test('required receipt remains fail-closed with identical exception; summary contains counts/status only',()=>{
 const receipt={genuine:'fixture'},events=[];assert.equal(readRequiredReceipt(()=>receipt,1,response,e=>events.push(e)),receipt);assert.equal(events.length,0);
 for(const [error,label] of [[Object.assign(Error('private-path'),{code:'ENOENT'}),'absent'],[new SyntaxError('private-json'),'invalid-json'],[Object.assign(Error('secret'),{code:'EACCES'}),'unavailable']]){assert.throws(()=>readRequiredReceipt(()=>{throw error;},1,response,e=>events.push(e)),e=>e===error);const row=events.at(-1);assert.deepEqual(row,{event:'owned-http-receipt-failure',exchange:1,receipt:label,status:503,body_bytes:0,header_bytes:128,eof:true});assert.doesNotMatch(JSON.stringify(row),/secret|private/);}
 const hostile={};Object.defineProperty(hostile,'status',{get(){throw Error('getter');}});assert.equal(requestFailureSummary(65,hostile,Error()).status,null);assert.equal(requestFailureSummary(65,hostile,Error()).exchange,0);
 const original=Error('original');assert.throws(()=>readRequiredReceipt(()=>{throw original;},1,response,()=>{throw Error('diagnostic');}),e=>e===original);
});
test('first retention, fixed fields, framing cap and required receipt rethrow have semantic controls',async()=>{
 const file=new URL('../../scripts/ci/source-policy-http-request-diagnostic.mjs',import.meta.url),source=readFileSync(file,'utf8');
 const controls=[
  ['if(this.#first)return;','',m=>{const d=new m.RequestDiagnosticStream();d.push(frame(wire));d.push(frame({...wire,stage:'shutdown'}));assert.deepEqual(d.evidence(1),wire);}],
  ['row.event===\'owned-request-refused\'','true',m=>{const d=new m.RequestDiagnosticStream();d.push(frame({...wire,event:'secret'}));assert.equal(d.evidence(1),null);}],
  ['this.#line.length<1023','this.#line.length<4096',m=>{const d=new m.RequestDiagnosticStream();d.push(Buffer.from(REQUEST_DIAGNOSTIC_PREFIX+' '.repeat(1024)+JSON.stringify(wire)+'\n'));assert.equal(d.evidence(1),null);}],
  ['throw error;','return null;',m=>assert.throws(()=>m.readRequiredReceipt(()=>{throw Error('secret');},1,response,()=>{}))],
  ['this.#first?.exchange===exchange','this.#first!==null',m=>{const d=new m.RequestDiagnosticStream();d.push(frame(wire));assert.equal(d.evidence(2),null);}],
 ];
 for(const [before,after,oracle]of controls){assert.equal(source.split(before).length,2);const edited=source.replace(before,after).replace("'./source-policy-http-profile.mjs'",JSON.stringify(new URL('../../scripts/ci/source-policy-http-profile.mjs',import.meta.url).href));const m=await import('data:text/javascript;base64,'+Buffer.from(edited).toString('base64'));assert.throws(()=>oracle(m),e=>e.code==='ERR_ASSERTION');}
 const changed=phpSource().replace("if ( ! empty( $GLOBALS['adbh_request_reported'] ) )","if ( false )");assert.notEqual(changed,phpSource());const r=run(`$GLOBALS['adbh_stage']='login';adbh_request_report(null,1,'/owned');if(adbh_request_report(null,1,'/owned'))throw new RuntimeException('first-only');`,changed);assert.notEqual(r.status,0);assert.match(r.stderr+r.stdout,/RuntimeException: first-only/);assert.doesNotMatch(r.stderr+r.stdout,/Parse error|syntax error/);
});
test('actual router failure sites and parent integration preserve required receipts, original caps and cleanup ordering',()=>{
 const read=f=>readFileSync(new URL('../../scripts/ci/'+f,import.meta.url),'utf8'),router=read('source-policy-http-owned-router.php'),parent=read('source-policy-http-owned-controller.mjs');
 assert.equal((router.match(/adbh_request_report\(/g)||[]).length,4);assert.match(router,/\$GLOBALS\['adbh_stage'\] = 'shutdown'/);assert.match(router,/\$GLOBALS\['adbh_stage'\] = 'receipt'/);
 assert.match(parent,/readRequiredReceipt\(\(\)=>readReceipt\(owner,exchange\)/);assert.match(parent,/diagnosticBytes<=LIMITS\.diagnostic_bytes/);assert.ok(parent.indexOf('await stopChild(child,ended,left(deadline))')<parent.indexOf('requestDiagnostics.evidence(receiptFailure.exchange)'));assert.match(parent,/installerSidecarMatches\(receipt\.containment,id,'request'\)/);
 const report=parent.match(/try\{if\(receiptFailure\)[^\r\n]+/)[0],execute=Function('receiptFailure','requestDiagnostics','process',report+';return "cleanup-reached";');
 let line;assert.equal(execute(requestFailureSummary(1,response,Error()),{evidence:()=>wire},{stderr:{write:s=>{line=s;}}}),'cleanup-reached');assert.deepEqual(JSON.parse(line).diagnostic,wire);
 assert.equal(execute(requestFailureSummary(1,response,Error()),{evidence:()=>wire},{stderr:{write:()=>{throw Error('closed output');}}}),'cleanup-reached');
});
