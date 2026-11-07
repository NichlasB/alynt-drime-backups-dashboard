import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync,writeFileSync,mkdirSync,mkdtempSync,rmSync,existsSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve,sep} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';
import {createHash} from 'node:crypto';
import {wlRequestFailure} from '../../scripts/ci/source-policy-wl-request-contract.mjs';
const root=fileURLToPath(new URL('../../',import.meta.url)),php=process.env.PHP_BINARY??'php';
const source=readFileSync(join(root,'scripts/ci/source-policy-wl-request-contract.php'),'utf8').replaceAll('\r\n','\n');
const body=readFileSync(join(root,'scripts/ci/source-policy-minimum-request-child-body.php'),'utf8').replaceAll('\r\n','\n');
const digest=createHash('sha256').update('genuine same-observation view available').digest('hex');
const literal=v=>typeof v==='string'?"'"+v.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'":v===null?'null':typeof v==='boolean'?(v?'true':'false'):Number.isSafeInteger(v)?String(v):'array('+Object.entries(v).map(([k,x])=>literal(k)+'=>'+literal(x)).join(',')+')';
const fact={index:0,result:'error',code:'unavailable',records:1,connects:0,commands:1};
const wire=(f=fact)=>`ADBWLRF${f.failed_connects===undefined?'3':'4'}|l|child|${f.index}|entry|RuntimeException|${digest}|${f.result}|${f.code}|${f.records}|${f.connects}|${f.commands}${f.failed_connects===undefined?'':'|'+f.failed_connects}\n`;
function emit({data=fact,capture=false,profile='l',mode='proof',base=`ADBMGRF1|child|0|entry|RuntimeException|${digest}\n`,view="new WP_Error('source_policy_unavailable')",again=false,failures=0,change=s=>s,changeBody=s=>s}={}){
  const temporary=mkdtempSync(join(tmpdir(),'adb-wl-entry-failure-')),copy=join(temporary,'contract.php');assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-wl-entry-failure-'));
  try{
    const changed=change(source).replace("__DIR__ . '/source-policy-minimum-request-contract.php'",literal(join(root,'scripts/ci/source-policy-minimum-request-contract.php')));writeFileSync(copy,changed);
    const changedBody=changeBody(body),start=changedBody.indexOf("\t\tif ( defined( 'ADB_WL_REQUEST_PROOF' ) ) {",changedBody.indexOf('$view = $class::request_view')),end=changedBody.indexOf('\t\tadbg_check( is_array( $view )',start);
    const block=capture?(assert.ok(start>0&&end>start),changedBody.slice(start,end)):'';
    const code=`require ${literal(copy)};define('ADB_WL_REQUEST_PROOF',true);putenv('WL_REQUEST_PROFILE=${profile}');putenv('WL_REQUEST_MODE=${mode}');class WP_Error{private $code;function __construct($code){$this->code=$code;}function get_error_code(){$GLOBALS['getters']=($GLOBALS['getters']??0)+1;return $this->code;}}class Foreign_Error extends WP_Error{function get_error_code(){throw new LogicException('PRIVATE getter');}}function mysqli_get_client_stats(){return array('connect_success'=>'2','com_query'=>'11','connect_failure'=>${literal(String(failures))});}$argv=array('child','flag','0');$ambient=(object)array('num_queries'=>5);$before_queries=4;$before=array('connect_success'=>'2','com_query'=>'10','connect_failure'=>'0');$view=${view};${capture?block:"$GLOBALS['adbwl_entry_failure']="+literal(data)+';'}${again?"adbwl_entry_capture(null,0,9,9,9);":''}echo adbwl_failure_wire(${literal(base)});if(class_exists('wpdb',false)||extension_loaded('mysqli'))throw new LogicException('native');`;
    const r=spawnSync(php,['-n','-d','disable_functions=json_encode,json_decode','-r',code],{encoding:'utf8',timeout:5000,maxBuffer:4096});if(r.error||r.status!==0||r.stderr!=='')throw Error('Diagnostic setup/parser failure, not assertion kill: '+r.stderr);assert.equal(r.stdout.includes('PRIVATE'),false);return r.stdout;
  }finally{writeFileSync(copy,source);assert.equal(readFileSync(copy,'utf8'),source);rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);}
}

test('strict actual formatter binds a value-free entry refusal to its original assertion',()=>{
  assert.equal(emit(),wire());
  for(const data of [{...fact,extra:'PRIVATE'},{...fact,index:'0'},{...fact,index:4},{...fact,result:'PRIVATE'},{...fact,code:'PRIVATE'},{...fact,result:'array'},{...fact,records:-1},{...fact,commands:4097},{...fact,connects:'0'}])assert.doesNotMatch(emit({data}),/ADBWLRF3/);
  for(const options of [{profile:'w'},{mode:'audit'},{base:`ADBMGRF1|child|1|entry|RuntimeException|${digest}\n`},{base:`ADBMGRF1|child|0|auth|RuntimeException|${digest}\n`},{base:`ADBMGRF1|child|0|entry|Error|${digest}\n`},{base:`ADBMGRF1|child|0|entry|RuntimeException|${'f'.repeat(64)}\n`}])assert.doesNotMatch(emit(options),/ADBWLRF3/);
});

test('actual immediate post-view caller captures closed result/effect DATA with first-cause retention and no foreign getter',()=>{
  const captured={...fact,failed_connects:0};assert.equal(emit({capture:true}),wire(captured));assert.equal(emit({capture:true,again:true}),wire(captured));assert.equal(emit({capture:true,failures:1}),wire({...captured,failed_connects:1}));
  for(const [view,result,code]of [["array('PRIVATE'=>new stdClass())",'array','none'],["new WP_Error('source_policy_forbidden')",'error','forbidden'],["new WP_Error('source_policy_invalid')",'error','invalid'],["new WP_Error('PRIVATE SQL/token')",'error','other'],["new Foreign_Error('PRIVATE')",'other','none'],['null','other','none']])assert.equal(emit({capture:true,view}),wire({...captured,result,code}));
  for(const control of [{changeBody:s=>s.replace(/^.*adbwl_entry_capture\( \$view.*$/m,'/* omitted capture */')},{change:s=>s.replace("isset( $GLOBALS['adbwl_entry_failure'] )",'false'),again:true},{changeBody:s=>s.replace("(int) $entry_stats['connect_failure'] - (int) $before['connect_failure']",'0'),failures:1}])assert.throws(()=>assert.equal(emit({...control,capture:true}),wire({...captured,failed_connects:control.failures??0})),{code:'ERR_ASSERTION'});
  for(const options of [{profile:'w',change:s=>s.replaceAll("'l' !== getenv( 'WL_REQUEST_PROFILE' )",'false')},{mode:'audit',change:s=>s.replaceAll("'proof' !== getenv( 'WL_REQUEST_MODE' )",'false')}]){assert.doesNotMatch(emit({capture:true,profile:options.profile??'l',mode:options.mode??'proof'}),/ADBWLRF[34]/);assert.throws(()=>assert.doesNotMatch(emit({...options,capture:true}),/ADBWLRF[34]/),{code:'ERR_ASSERTION'});}
});

test('strict proof-only receiver refuses wrong identity, counter/type/code combinations, suffixes and overlong output',()=>{
  assert.equal(wlRequestFailure(wire()),null);assert.equal(wlRequestFailure(wire(),true),null);const expected={profile:'l',case_index:0,stage:'entry',exception:'RuntimeException',assertion:digest,entry:{result:'error',code:'unavailable',records:1,connects:0,commands:1}};assert.deepEqual(wlRequestFailure(wire(),false,true),expected);
  for(const bad of [wire()+'PRIVATE',wire()+wire(),wire().replace('\n','\r\n'),wire().replace('|l|','|w|'),wire().replace('|0|entry|','|4|entry|'),wire().replace(digest,'0'.repeat(64)),wire().replace('|entry|','|auth|'),wire().replace('|error|','|array|'),wire().replace('|unavailable|','|none|'),wire().replace('|1|0|1\n','|01|0|1\n'),wire().replace('|1|0|1\n','|4097|0|1\n')])assert.equal(wlRequestFailure(bad,false,true),null);
  assert.ok(Buffer.byteLength(wire())<=256);
  for(const count of [0,1,4096])assert.deepEqual(wlRequestFailure(wire({...fact,failed_connects:count}),false,true),{...expected,entry:{...expected.entry,failed_connects:count}});
  for(const count of [-1,4097,'01','PRIVATE']){assert.equal(wlRequestFailure(wire({...fact,failed_connects:count}),false,true),null);assert.doesNotMatch(emit({data:{...fact,failed_connects:count}}),/ADBWLRF[34]/);}
});
