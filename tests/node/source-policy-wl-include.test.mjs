import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync,writeFileSync,mkdirSync,mkdtempSync,rmSync,existsSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve,sep} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';
import {createHash} from 'node:crypto';
import {wlRequestFailure} from '../../scripts/ci/source-policy-wl-request-contract.mjs';
import {requestAuditResult} from '../../scripts/ci/source-policy-linux-qualification-controller.mjs';
const root=fileURLToPath(new URL('../../',import.meta.url)),php=process.env.PHP_BINARY??'php';
const source=readFileSync(join(root,'scripts/ci/source-policy-wl-request-source.php'),'utf8');
const hash=value=>createHash('sha256').update(value).digest('hex'),label=hash('finite actual include closure');
const fact={ordinal:32,origin:'core',reason:'scope',path_digest:hash('unknown.php')};
const wire=(f=fact,assertion=label)=>`ADBWLIC1|l|0|audit|${f.ordinal}|${f.origin}|${f.reason}|${f.path_digest}\nADBWLRF1|l|child|0|audit|RuntimeException|${assertion}\n`;
const literal=v=>typeof v==='string'?"'"+v.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'":v===null?'null':typeof v==='boolean'?(v?'true':'false'):Number.isSafeInteger(v)?String(v):'array('+Object.entries(v).map(([k,x])=>literal(k)+'=>'+literal(x)).join(',')+')';
function emit(f,profile='l',mode='audit',base=`ADBMGRF1|child|0|audit|RuntimeException|${label}\n`,prefixOnly=false){
  const code=`require ${literal(join(root,'scripts/ci/source-policy-wl-request-contract.php'))};putenv('WL_REQUEST_PROFILE=${profile}');putenv('WL_REQUEST_MODE=${mode}');$GLOBALS['adbwl_include_failure']=${literal(f)};echo ${prefixOnly?'adbwl_include_prefix':'adbwl_failure_wire'}(${literal(base)});`;
  const r=spawnSync(php,['-n','-d','disable_functions=json_encode,json_decode','-r',code],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr);assert.equal(r.stderr,'');return r.stdout;
}
function model(change=code=>code,scenarios=['positive','boundary','scope','first','nested','repo','boundary-path','foreign','missing','size','stat-false','stat-throw','w','proof','ordinal','overflow','late'],control=false){
  const temporary=mkdtempSync(join(tmpdir(),'adb-wl-include-')),folder=join(temporary,'scripts','ci'),file=join(folder,'source.php');
  assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-wl-include-'));
  try{
    mkdirSync(folder,{recursive:true});const changed=change(source);if(control)assert.notEqual(changed,source);
    writeFileSync(file,changed.replace('<?php','<?php\nnamespace AdbWlIncludeModel;'));
    for(const scenario of scenarios){const r=spawnSync(php,['-n',join(root,'tests/support/source-policy-wl-include-model.php'),scenario,file],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.error,undefined);if(control){assert.notEqual(r.status,0);assert.equal(r.stderr,'PHPUnit\\Framework\\ExpectationFailedException\n');}else{assert.equal(r.status,0,scenario+': '+r.stderr+r.stdout);assert.equal(r.stderr,'');assert.match(r.stdout,/^checks:[1-9][0-9]*\n$/);}}
  }finally{writeFileSync(file,source.replace('<?php','<?php\nnamespace AdbWlIncludeModel;'));assert.equal(readFileSync(file,'utf8'),source.replace('<?php','<?php\nnamespace AdbWlIncludeModel;'));rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);}
}

test('actual include guard has discriminating metadata-only positives, refusals and L-only first context',()=>model());
test('actual PHP failure formatter carries bounded include context only for the matching L audit refusal',()=>{
  assert.equal(emit(fact),wire());assert.deepEqual(wlRequestFailure(emit(fact),true).include,fact);
  for(const [profile,mode]of [['w','audit'],['l','proof']])assert.doesNotMatch(emit(fact,profile,mode),/ADBWLIC1/);
  for(const f of [{...fact,ordinal:512},{...fact,ordinal:'32'},{...fact,path_digest:'PRIVATE'},{...fact,origin:'PRIVATE'},{...fact,reason:'PRIVATE'},{...fact,extra:'PRIVATE'},{...fact,origin:'foreign',reason:'size'}])assert.doesNotMatch(emit(f),/ADBWLIC1/);
  for(const base of [`ADBMGRF1|child|1|audit|RuntimeException|${label}\n`,`ADBMGRF1|child|0|auth|RuntimeException|${label}\n`,`ADBMGRF1|child|0|audit|RuntimeException|${'f'.repeat(64)}\n`])assert.doesNotMatch(emit(fact,'l','audit',base),/ADBWLIC1/);
  for(const base of [null,{},'private malformed wire'])assert.equal(emit(fact,'l','audit',base,true),'');
});
test('strict receiver keeps legacy wire unchanged, refuses foreign/trailing context and never grants success',()=>{
  const value=wire();assert.ok(Buffer.byteLength(value)<256);assert.equal(wlRequestFailure(value),null);const parsed=wlRequestFailure(value,true);assert.deepEqual(parsed.include,fact);
  for(const bad of [value+'PRIVATE',value.replace('|l|child|0|','|w|child|0|'),value.replace('|l|child|0|','|l|child|1|'),value.replace('|child|0|audit|','|child|0|auth|'),value.replace('|32|','|032|'),value.replace('|32|','|512|'),value.replace('|core|scope|','|foreign|size|'),value.replace(label,'f'.repeat(64)),value.replace('\n','\r\n'),value+value])assert.equal(wlRequestFailure(bad,true),null);
  for(const origin of ['repo','core'])for(const reason of ['scope','size','stat'])assert.deepEqual(wlRequestFailure(wire({...fact,origin,reason}),true).include,{...fact,origin,reason});
  assert.ok(wlRequestFailure(wire({...fact,reason:'stat'},'0'.repeat(64)),true));assert.equal(wlRequestFailure(wire(fact,'0'.repeat(64)),true),null);
  let caught;try{requestAuditResult({status:1,signal:null,error:null,stdout:'',stderr:value},'a'.repeat(32));}catch(error){caught=error;}
  assert.ok(caught);const summary=JSON.parse(caught.message.slice('Source audit refused: '.length));assert.equal(summary.status,1);assert.deepEqual(summary.request_failure.include,fact);assert.equal(JSON.stringify(summary).includes('unknown.php'),false);
});
test('source-backed guard controls are assertion-killed, not parser/native launcher failures',()=>{
  for(const [change,scenario]of [
    [s=>s.replace("dirname( $file ) === $core &&",'true &&'),'nested'],
    [s=>s.replace('in_array( basename( $file ), $allowed, true )','true'),'scope'],
    [s=>s.replace('$bytes <= 1048576','true'),'size'],
    [s=>s.replace("unset( $GLOBALS['adbwl_include_failure'] );",'/* omitted context clearing */'),'positive'],
    [s=>s.replace('$scope && is_int( $bytes ) &&','$scope &&'),'stat-false'],
    [s=>s.replace(" && ! isset( $GLOBALS['adbwl_include_failure'] )",''),'first'],
    [s=>s.replace("'l' === getenv( 'WL_REQUEST_PROFILE' )",'true'),'w'],
    [s=>s.replace("'audit' === getenv( 'WL_REQUEST_MODE' )",'true'),'proof'],
    [s=>s.replace('$ordinal <= 511','true'),'overflow']
  ])model(change,[scenario],true);
});
