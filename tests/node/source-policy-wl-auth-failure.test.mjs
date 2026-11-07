import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync,writeFileSync,mkdirSync,mkdtempSync,rmSync,existsSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve,sep} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';
import {createHash} from 'node:crypto';
import {Script} from 'node:vm';
import {EventEmitter} from 'node:events';
import {wlRequestFailure,parseRequestWire} from '../../scripts/ci/source-policy-wl-request-contract.mjs';
const root=fileURLToPath(new URL('../../',import.meta.url)),php=process.env.PHP_BINARY??'php';
const contract=readFileSync(join(root,'scripts/ci/source-policy-wl-request-contract.php'),'utf8').replaceAll('\r\n','\n');
const body=readFileSync(join(root,'scripts/ci/source-policy-minimum-request-child-body.php'),'utf8').replaceAll('\r\n','\n');
const literal=v=>typeof v==='string'?"'"+v.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'":v===null?'null':typeof v==='boolean'?(v?'true':'false'):Number.isSafeInteger(v)?String(v):'array('+Object.entries(v).map(([k,x])=>literal(k)+'=>'+literal(x)).join(',')+')';
const digest=createHash('sha256').update('class-wp-user.php').digest('hex'),zeros='0'.repeat(64);
const wire=(index=0,phase='setup',source='core',line=3,hash=digest)=>`ADBWLRF2|l|child|${index}|auth|Error|${zeros}|${phase}|${source}|${line}|${hash}\n`;
function emit({profile='l',mode='proof',stage='auth',index=0,phase='setup',origin='core',kind='Error',assertion=null,names=['class-wp-user.php'],fact=null,change=s=>s,changeBody=s=>s,again=false}={}){
  const temporary=mkdtempSync(join(tmpdir(),'adb-wl-auth-')),folder=join(temporary,'scripts','ci'),copy=join(folder,'contract.php'),core=join(temporary,'core','wp-includes');
  assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-wl-auth-'));
  try{
    mkdirSync(folder,{recursive:true});mkdirSync(core,{recursive:true});let source=change(contract);
    source=source.replace("__DIR__ . '/source-policy-minimum-request-contract.php'",literal(join(root,'scripts/ci/source-policy-minimum-request-contract.php')));writeFileSync(copy,source);
    const errorFile=origin==='core'?join(core,'class-wp-user.php'):origin==='helper'?join(folder,'source-policy-minimum-request-auth.php'):join(temporary,'PRIVATE.php');
    writeFileSync(errorFile,`<?php\n// owned synthetic Error, never real core\nthrow new \\${kind}('PRIVATE cookie/token/SQL');\n`);
    const changed=changeBody(body),start=changed.indexOf('} catch ( Throwable $error ) {'),end=changed.indexOf('} finally {',start);assert.ok(start>0&&end>start);
    const caught=changed.slice(start+'} catch ( Throwable $error ) {'.length,end);
    const code=`require ${literal(copy)};define('ADB_WL_REQUEST_PROOF',true);define('ABSPATH',${literal(join(temporary,'core').replaceAll('\\','/')+'/')});define('WPINC','wp-includes');putenv('WL_REQUEST_PROFILE=${profile}');putenv('WL_REQUEST_MODE=${mode}');$GLOBALS['adbwl_auth_sources']=${literal(names)};$GLOBALS['adbg_auth_phase']=${literal(phase)};${assertion===null?'':"$GLOBALS['adbg_assertion']="+literal(assertion)+';'}$argv=array('child','--owned-wl-request-child',${literal(String(index))});$stage=${literal(stage)};try{require ${literal(errorFile)};}catch(\\Throwable $error){${caught}}${again?"adbwl_auth_capture('auth',0,new \\Error('PRIVATE cleanup'));":''}${fact===null?'':"$GLOBALS['adbwl_auth_failure']="+literal(fact)+';'}echo adbwl_failure_wire($failure);if(class_exists('wpdb',false)||function_exists('mysqli_init'))throw new \\RuntimeException('native');`;
    const r=spawnSync(php,['-n','-d','disable_functions=json_encode,json_decode','-r',code],{encoding:'utf8',timeout:5000,maxBuffer:4096});if(r.error||r.status!==0||r.stderr!=='')throw Error('Isolated PHP setup/execution failure, not a semantic kill');assert.equal(r.stdout.includes('PRIVATE'),false);return r.stdout;
  }finally{writeFileSync(copy,contract);assert.equal(readFileSync(copy,'utf8'),contract);rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);}
}
test('actual first auth catch and formatter retain only bounded engine Error source facts before cleanup',()=>{
  assert.equal(emit(),wire());assert.equal(emit({again:true}),wire());
  assert.equal(emit({phase:'auth',index:22}),wire(22,'auth'));assert.equal(emit({phase:['PRIVATE']}),wire(0,'none'));
  assert.equal(emit({origin:'foreign'}),wire(0,'setup','other',0,zeros));
  assert.equal(emit({names:[]}),wire(0,'setup','other',0,zeros));assert.equal(emit({names:['PRIVATE/path.php']}),wire(0,'setup','other',0,zeros));
  const helperHash=createHash('sha256').update('source-policy-minimum-request-auth.php').digest('hex');assert.equal(emit({origin:'helper'}),wire(0,'setup','helper',3,helperHash));
  for(const options of [{profile:'w'},{mode:'audit'},{stage:'cleanup'},{kind:'RuntimeException'},{assertion:'f'.repeat(64)}])assert.doesNotMatch(emit(options),/ADBWLRF2/);
  const good={index:0,phase:'setup',source:'core',line:3,path_digest:digest};
  for(const fact of [{...good,extra:'PRIVATE'},{...good,index:'0'},{...good,index:23},{...good,phase:{}},{...good,source:'PRIVATE'},{...good,line:-1},{...good,line:100001},{...good,path_digest:'PRIVATE'},{...good,source:'other'},{...good,line:0}])assert.doesNotMatch(emit({fact}),/ADBWLRF2/);
});
const supervisor=readFileSync(join(root,'scripts/ci/source-policy-linux-qualification-controller.mjs'),'utf8').replaceAll('\r\n','\n');
async function supervise(raw,source=supervisor){
  const start=source.indexOf('async function launchRequest('),end=source.indexOf('\nasync function main()',start);assert.ok(start>0&&end>start);
  const child=new EventEmitter();child.pid=42;child.exitCode=null;child.signalCode=null;child.stdout=new EventEmitter();child.stderr=new EventEmitter();child.stdin=new EventEmitter();let killed=0,destroyed=0;const output=[],budgets=[];
  child.kill=()=>{killed++;queueMicrotask(()=>{child.signalCode='SIGKILL';child.emit('close',null,'SIGKILL');});};child.stdin.destroy=()=>destroyed++;child.stdin.end=()=>queueMicrotask(()=>{child.exitCode=0;child.emit('close',0,null);});
  const context={Buffer,parseRequestWire,wlRequestFailure,process:{stderr:{write:s=>output.push(s)}},args:['php','-n','worker','flag'],spawn:()=>child,setTimeout:(fn,ms)=>{budgets.push(ms);return budgets.length;},clearTimeout:()=>{}};
  new Script(source.slice(start,end)+'\nglobalThis.launch=launchRequest;').runInNewContext(context,{timeout:1000});
  const pending=context.launch(0,{},1000);
  if(raw===null){child.stdout.emit('data',Buffer.from('{"event":"synthetic-positive"}\n'));const result=await pending;assert.equal(result.alive(),true);await result.release('fixed-owned-ack');await result.cleanup();assert.equal(killed,0);}
  else{const half=Math.floor(raw.length/2);child.stderr.emit('data',Buffer.from(raw.slice(0,half)));assert.equal(killed,0);child.stderr.emit('data',Buffer.from(raw.slice(half)));await assert.rejects(()=>pending,/Private request child refused/);assert.ok(killed>0);}
  assert.equal(destroyed,1);assert.ok(budgets.every(ms=>ms===1000));return output.map(s=>JSON.parse(s));
}
test('actual proof stderr caller projects the new fact and still rejects/reaps without native IO or success grants',async()=>{
  assert.deepEqual(await supervise(null),[]);const facts=await supervise(wire());assert.equal(facts.length,1);assert.equal(facts[0].request_failure.auth.line,3);
  const entry='ADBWLRF3|l|child|0|entry|RuntimeException|113bc67f76c37d203efc9ce41814ae27055775e06935d86a4eb235af6900e1a5|error|unavailable|1|0|1\n';assert.equal((await supervise(entry))[0].request_failure.entry.records,1);for(const bad of [entry.replace('|0|entry|','|1|entry|'),entry+'PRIVATE'])assert.deepEqual(await supervise(bad),[]);
  const extended=entry.replace('ADBWLRF3|','ADBWLRF4|').replace('\n','|1\n');assert.equal((await supervise(extended))[0].request_failure.entry.failed_connects,1);assert.deepEqual(await supervise(extended.replace('|1\n','|4097\n')),[]);
  for(const bad of [wire(1),wire()+'PRIVATE',wire().replace('|l|','|w|')])assert.deepEqual(await supervise(bad),[]);
  const mutant=supervisor.replace('wlRequestFailure(err,false,true)','wlRequestFailure(err)');assert.notEqual(mutant,supervisor);await assert.rejects(async()=>assert.equal((await supervise(wire(),mutant)).length,1),{code:'ERR_ASSERTION'});
});
test('auth receiver is explicit opt-in, identity-bound, single-line and never a success channel',()=>{
  assert.ok(Buffer.byteLength(wire())<=256);assert.equal(wlRequestFailure(wire()),null);assert.equal(wlRequestFailure(wire(),true),null);
  const expected={profile:'l',case_index:0,stage:'auth',exception:'Error',assertion:zeros,auth:{phase:'setup',source:'core',line:3,path_digest:digest}};
  assert.deepEqual(wlRequestFailure(wire(),false,true),expected);
  for(const bad of [wire()+'PRIVATE',wire().replace('|l|','|w|'),wire().replace('|0|auth|','|23|auth|'),wire().replace('|auth|Error|','|audit|Error|'),wire().replace('|auth|Error|','|auth|RuntimeException|'),wire().replace(zeros,'f'.repeat(64)),wire().replace('|3|','|03|'),wire().replace('|3|','|100001|'),wire().replace('|setup|','|PRIVATE|'),wire(0,'setup','other',3),wire(0,'setup','core',0),wire()+wire(),wire().replace('\n','\r\n')])assert.equal(wlRequestFailure(bad,false,true),null);
  assert.ok(wlRequestFailure(wire(0,'none','other',0,zeros),false,true));
});
test('source-backed auth diagnostic controls cannot erase first cause, profile/mode boundaries or actual caller capture',()=>{
  const controls=[
    {changeBody:s=>s.replace('adbwl_auth_capture( $stage, (int) $argv[2], $error );','/* omitted capture */')},
    {change:s=>s.replace("isset( $GLOBALS['adbwl_auth_failure'] )",'false'),again:true},
    {change:s=>s.replaceAll("'l' !== getenv( 'WL_REQUEST_PROFILE' )",'false'),profile:'w'},
    {change:s=>s.replaceAll("'proof' !== getenv( 'WL_REQUEST_MODE' )",'false'),mode:'audit'}
  ];
  for(const c of controls){if(c.change)assert.notEqual(c.change(contract),contract);if(c.changeBody)assert.notEqual(c.changeBody(body),body);assert.throws(()=>{const actual=emit(c);assert.equal(actual,c.profile==='w'||c.mode==='audit'?emit({profile:c.profile??'l',mode:c.mode??'proof'}):wire());},{code:'ERR_ASSERTION'});}
});
