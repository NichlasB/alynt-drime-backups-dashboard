import test from 'node:test';
import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
import {readFileSync} from 'node:fs';
import {HTTP_PROFILE} from '../../scripts/ci/source-policy-http-profile.mjs';
import {INITIALIZED_HTTP_PROFILE,InitializedFixtureData} from '../../scripts/ci/source-policy-http-initialization.mjs';
import {HttpDataLifecycle} from '../../scripts/ci/source-policy-http-lifecycle.mjs';
import {httpExecutionProfile} from '../../scripts/ci/source-policy-http-owned-controller.mjs';
import {initializationAnchorSql,nativeInitializerCompletion} from '../../scripts/ci/source-policy-http-initialized-evidence.mjs';
import {recoveryArguments,OWNED_PHP_ARGS} from '../../scripts/ci/source-policy-http-owned-safety.mjs';
import {HTTP_FLOW} from '../../scripts/ci/source-policy-http-supervisor.mjs';
import * as f from './source-policy-http-fixtures.mjs';
const root=new URL('../../',import.meta.url),read=file=>readFileSync(new URL(file,root),'utf8').replaceAll('\r\n','\n');
const phpLiteral=v=>typeof v==='string'?"'"+v.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'":typeof v==='boolean'?(v?'true':'false'):typeof v==='number'?String(v):'array('+Object.entries(v).map(([k,x])=>phpLiteral(k)+'=>'+phpLiteral(x)).join(',')+')';
const env=()=>({GITHUB_ACTIONS:'true',GITHUB_EVENT_NAME:'workflow_dispatch',GITHUB_REPOSITORY:'NichlasB/alynt-drime-backups-dashboard',GITHUB_REF:'refs/heads/master',GITHUB_ACTOR:'NichlasB',GITHUB_RUN_ATTEMPT:'1',GITHUB_WORKFLOW_REF:'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-linux-qualification.yml@refs/heads/master',QUALIFICATION_HTTP_APPROVED:'true',QUALIFICATION_GENUINE_REQUEST_APPROVED:'true',GITHUB_RUN_ID:'123',GITHUB_SHA:f.head,EXPECTED_SHA:f.head,RUNNER_OS:'Linux'});
function initialized(){
 const m=new InitializedFixtureData({identity:f.identity(),profile:{...INITIALIZED_HTTP_PROFILE},storage:f.storage(),manifest:f.manifest(),expectation:f.manifestExpectation(),anchors:f.digest},0),b=m.binding();
 m.installed({...b,role:'installer',sql:10,exited:true,sessions:0,transactions:0,ddl_sealed:true,fixtures_verified:true},1);const ticket=m.begin(2);
 const completion={...ticket,sql:3,full_bootstrap:true,installing:false,short_init:false,protected_reads:0,protected_writes:0,owner_allocations:0,session_writes:0,mail:0,outbound:0,file_writes:0,source_unchanged:true};
 m.initialized(nativeInitializerCompletion({event:'owned-http-initializer-complete',stock_shutdown:true,completion},ticket,f.digest,f.digest),3);
 m.released({...b,role:'initializer-release',exited:true,sessions:0,transactions:0,anchors_unchanged:true,source_unchanged:true},4);
 m.freeze({...b,role:'full-invariant',digest:'e'.repeat(64),all_options:true,all_cron:true,all_widgets:true,all_transients:true,non_session_usermeta:true},5);
 return {packet:{binding:b,result:m.result()},ticket,completion};
}
test('actual native selection remains v1/default-off and exact-head/actor/attempt gated for v2',()=>{
 assert.equal(httpExecutionProfile(env()),HTTP_PROFILE);assert.equal(httpExecutionProfile({...env(),QUALIFICATION_HTTP_INITIALIZED_APPROVED:'false'}),HTTP_PROFILE);
 assert.equal(httpExecutionProfile({...env(),QUALIFICATION_HTTP_INITIALIZED_APPROVED:'true'}),INITIALIZED_HTTP_PROFILE);
 for(const key of Object.keys(env()))assert.throws(()=>httpExecutionProfile({...env(),QUALIFICATION_HTTP_INITIALIZED_APPROVED:'true',[key]:'wrong'}),undefined,key);
 for(const flag of ['TRUE','unknown',true,null])assert.throws(()=>httpExecutionProfile({...env(),QUALIFICATION_HTTP_INITIALIZED_APPROVED:flag}));
 assert.throws(()=>httpExecutionProfile({...env(),QUALIFICATION_HTTP_INITIALIZED_APPROVED:'true',QUALIFICATION_HTTP_SETUP_DIAGNOSTIC:'true'}));
});
test('v2 cannot start HTTP on installer/v1 credit or missing/forged initializer release/baseline',()=>{
 const setup={...f.ledger('setup'),sql:13};
 for(const packet of [null,{},initialized().packet&&{...initialized().packet,result:{...initialized().packet.result,state:'initialized'}},{...initialized().packet,binding:{...initialized().packet.binding,head:'0'.repeat(40)}}]){
  const m=new HttpDataLifecycle(f.identity(),0,f.challenge,INITIALIZED_HTTP_PROFILE);m.admit(INITIALIZED_HTTP_PROFILE,f.storage(),f.manifest(),f.manifestExpectation(),1);assert.throws(()=>m.setup(setup,6,packet));
 }
 const m=new HttpDataLifecycle(f.identity(),0,f.challenge);assert.throws(()=>m.admit(INITIALIZED_HTTP_PROFILE,f.storage(),f.manifest(),f.manifestExpectation(),1));
});
test('v2 preserves all 13 cases/25 exchanges and refuses v1 receipts rather than relabeling them',()=>{
 const model=new HttpDataLifecycle(f.identity(),0,f.challenge,INITIALIZED_HTTP_PROFILE),packet=initialized().packet;
 model.admit(INITIALIZED_HTTP_PROFILE,f.storage(),f.manifest(),f.manifestExpectation(),1);model.setup({...f.ledger('setup'),sql:13},6,packet);model.server(f.server(),INITIALIZED_HTTP_PROFILE,7);let at=10,policy=f.digest;
 for(const item of HTTP_FLOW){
  model.begin(f.meta(item.kind),item.kind,item.index,at++);let result=null,expected=null;
  if(item.index!==null){result={...f.result(item.index),model:INITIALIZED_HTTP_PROFILE.model};expected=f.expected(item.index);policy=result.after;}
  else if(['view','nonce-control'].includes(item.kind)){expected={...f.identity(),before:policy,after:policy,sentinel:'f'.repeat(64),revision:'2'.repeat(64),mode:'external_optional'};result={...f.result(0),model:INITIALIZED_HTTP_PROFILE.model,event:'http-view-complete',index:null,before:policy,after:policy,revision:expected.revision,mode:expected.mode};}
  const response={...f.response(),...(item.kind==='login-submit'?{status:302,location:'http://127.0.0.1:18777/_adb-owned-source-policy'}:result?{status:result.status}:{})};model.receive(response,result,expected,f.ledger(),f.ledger('auth'),at++);model.release(f.release(),at++);
 }
 const result=model.close(f.cleanup(),at);assert.equal(result.completed,true);assert.equal(result.cases,13);assert.equal(result.transactions,25);assert.equal(result.model,INITIALIZED_HTTP_PROFILE.model);
 const bad=new HttpDataLifecycle(f.identity(),0,f.challenge,INITIALIZED_HTTP_PROFILE);bad.admit(INITIALIZED_HTTP_PROFILE,f.storage(),f.manifest(),f.manifestExpectation(),1);bad.setup({...f.ledger('setup'),sql:13},6,packet);bad.server(f.server(),INITIALIZED_HTTP_PROFILE,7);bad.begin(f.meta('view'),'view',0,8);assert.throws(()=>bad.receive(f.response(),f.result(0),f.expected(0),f.ledger(),f.ledger('auth'),9));
});
test('native receipt requires stock shutdown and independent exact protected-anchor equality',()=>{
 const {ticket,completion}=initialized(),row={event:'owned-http-initializer-complete',stock_shutdown:true,completion};
 assert.equal(nativeInitializerCompletion(row,ticket,f.digest,f.digest).anchors_unchanged,true);
 for(const bad of [null,{...row,event:'initializer-worker-prepared-v2'},{...row,stock_shutdown:false},{...row,completion:{...completion,role:'installer'}},{...row,extra:true}])assert.throws(()=>nativeInitializerCompletion(bad,ticket,f.digest,f.digest));
 assert.throws(()=>nativeInitializerCompletion(row,ticket,f.digest,'0'.repeat(64)));
 const sql=initializationAnchorSql(f.storage());for(const token of ['users','usermeta','alynt_drime_dashboard_sites','user_roles','SCHEMA','COLUMNS','STATISTICS','owned_non_target'])assert.ok(sql.includes(token));assert.doesNotMatch(sql,/AUTO_INCREMENT|WHERE BINARY option_name<>/);assert.throws(()=>initializationAnchorSql({...f.storage(),database:'foreign'}));
});
test('shared PHP original clock and pre-query installer fence never reset across children',()=>{
 const file=fileURLToPath(new URL('../../scripts/ci/source-policy-http-initialized-clock.php',import.meta.url));
 const php=`require ${JSON.stringify(file)};$ns=1100000000;$p=array('origin_ns'=>'1000000000','setup_started'=>'100','setup_deadline'=>'60100');$read=static function()use(&$ns){return $ns;};$c=adbh_initialized_clock($p,$read);if($c()!==100)throw new AssertionError('positive');$GLOBALS['adbh_initialized_clock']=$c;if(adbh_initialized_setup_deadline('SELECT VERSION()')!=='SELECT VERSION()')throw new AssertionError('bytes');$ns=61100000000;try{adbh_initialized_setup_deadline('SELECT VERSION()');throw new AssertionError('expired');}catch(RuntimeException $e){}$d=adbh_initialized_clock($p,$read);try{$d();throw new AssertionError('fresh-child-reset');}catch(RuntimeException $e){}echo 'shared-expiry';`;
 const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',php],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.equal(r.stdout,'shared-expiry');
});
test('actual worker private-model predicate is closed and requires explicit native v2 authority',()=>{
 const file=fileURLToPath(new URL('../../scripts/ci/source-policy-http-initialized-clock.php',import.meta.url));
 const php=`require ${JSON.stringify(file)};if(adbh_initialized_model(array(),false)!=='owned-http-l714-v1'||adbh_initialized_model(array('model'=>'owned-http-l714-initialized-v2'),'true')!=='owned-http-l714-initialized-v2')throw new AssertionError('positive-profile');foreach(array(false,true,null,'false','TRUE','other') as $flag){if(adbh_initialized_model(array('model'=>'owned-http-l714-initialized-v2'),$flag)!==false)throw new AssertionError('native-approval');}foreach(array(null,array('model'=>'unknown'),array('model'=>true)) as $p){if(adbh_initialized_model($p,'true')!==false)throw new AssertionError('unknown-model');}echo 'closed-profile';`;
 const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',php],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.equal(r.stdout,'closed-profile');
});
test('native query registry rejects missing observers, late rewrites and foreign placeholder origins',()=>{
 const file=fileURLToPath(new URL('../../scripts/ci/source-policy-http-initialized-native-data.php',import.meta.url));
 const php=`require ${JSON.stringify(file)};class wpdb{public function remove_placeholder_escape($q){return $q;}}$own=array('function'=>'adbh_initializer_query','accepted_args'=>1);$db=new wpdb();$zero=array('function'=>array($db,'remove_placeholder_escape'),'accepted_args'=>1);$map=array(0=>array('zero'=>$zero),1=>array('own'=>$own));$origin=(new ReflectionMethod($db,'remove_placeholder_escape'))->getFileName();if(!adbh_initialized_query_registry($map,$origin)||!adbh_initialized_query_registry(array(1=>array('own'=>$own)),$origin))throw new AssertionError('positive');foreach(array(array(),array(0=>array('zero'=>$zero)),array(1=>array('own'=>$own),2=>array('late'=>$own)),array(1=>array('own'=>$own,'extra'=>$own)),array(1=>array('bad'=>array('function'=>'other','accepted_args'=>1)))) as $bad){if(adbh_initialized_query_registry($bad,$origin))throw new AssertionError('foreign-registry');}if(adbh_initialized_query_registry($map,'foreign.php'))throw new AssertionError('foreign-origin');echo 'closed-registry';`;
 const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',php],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.equal(r.stdout,'closed-registry');
});
test('actual native query refusal cannot be caught into a successful child, including after sidecar sealing',()=>{
 const native=read('scripts/ci/source-policy-http-initialized-native.php'),start=native.indexOf('function adbh_initializer_refuse('),end=native.indexOf('\ntry {\n',start);assert.ok(start>0&&end>start);
 const functions=native.slice(start,end),facts={sapi:'cli',before_bootstrap:true,installing_override:false,short_init_override:false,protected_cache_absent:true,protected_non_autoloaded:true,owner_absent:true,ddl_sealed:true};
 const load=['source-policy-http-initialization.php','source-policy-http-initialized-native-data.php','source-policy-http-owned-diagnostic.php'].map(name=>'require '+phpLiteral(fileURLToPath(new URL('../../scripts/ci/'+name,import.meta.url)))+';').join('');
 const setup=load+`if(!function_exists('json_encode')){function json_encode($x){return 'isolated-json-transport';}}function adbh_check($ok,$label){if(!$ok)throw new RuntimeException('isolated-check');}class WP_Hook{public $callbacks=array(1=>array('owned'=>array('function'=>'adbh_initializer_query','accepted_args'=>1)));}$GLOBALS['wp_filter']=array('query'=>new WP_Hook());$GLOBALS['adbh_initialized_clock']=static function(){return 5;};$GLOBALS['adbh_initializer_guard']=new ADBH_Initialization_Query_Guard(${phpLiteral(initialized().ticket)},${phpLiteral(facts)},2);`;
 const run=(body,code=functions)=>spawnSync(process.env.PHP_BINARY??'php',['-n','-r',setup+`eval(base64_decode('${Buffer.from(code).toString('base64')}'));`+body],{encoding:'utf8',timeout:5000,maxBuffer:8192});
 const positive=run(`echo adbh_initializer_query('SELECT VERSION()');`);assert.equal(positive.status,0,positive.stderr);assert.equal(positive.stdout,'SELECT VERSION()');
 const probe=`try{adbh_initializer_query("SELECT option_value FROM adbh_aaaaaaaaaaaa_options WHERE option_name = 'alynt_drime_backups_dashboard_source_policies'");}catch(Throwable $e){echo 'CAUGHT';}echo 'SURVIVED';`;
 for(const body of [probe,`$GLOBALS['adbh_initializer_guard']->filter('SELECT VERSION()',3);$GLOBALS['adbh_initializer_guard']->finish();`+probe]){const r=run(body);assert.equal(r.error,undefined);assert.equal(r.status,1,r.stderr+r.stdout);assert.equal(r.stdout,'');assert.doesNotMatch(r.stderr,/Parse error|Fatal error|source_policies|CAUGHT|SURVIVED/);}
 assert.equal(functions.split('exit( 1 );').length,2);const mutant=run(probe,functions.replace('exit( 1 );',"throw new RuntimeException('caught');"));assert.equal(mutant.status,0,mutant.stderr);assert.equal(mutant.stdout,'CAUGHTSURVIVED');
});
test('shared deadline and native-approval predicate have assertion-killed source controls',()=>{
 const source=read('scripts/ci/source-policy-http-initialized-clock.php').replace(/^<\?php/,'');
 const controls=[
  ["'true' === $approval",'true',`if(adbh_initialized_model(array('model'=>'owned-http-l714-initialized-v2'),'false')!==false)throw new AssertionError('approval-bypass');`,'approval-bypass'],
  ['$at >= $deadline','false',`$c=adbh_initialized_clock(array('origin_ns'=>'1000000000','setup_started'=>'0','setup_deadline'=>'60000'),static function(){return 61000000000;});try{$c();throw new AssertionError('deadline-bypass');}catch(RuntimeException $e){}`,'deadline-bypass']
 ];
 for(const [before,after,probe,label] of controls){assert.equal(source.split(before).length,2);const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',`eval(base64_decode('${Buffer.from(source.replace(before,after)).toString('base64')}'));${probe}`],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.notEqual(r.status,0);assert.match(r.stdout+r.stderr,new RegExp('AssertionError: '+label));assert.doesNotMatch(r.stdout+r.stderr,/Parse error|syntax error/);}
});
test('native entry and recovery are fixed; the installed entry and minimum workflow remain outside v2',()=>{
 const args=recoveryArguments({stage:'/tmp/owned'},'initializer');assert.deepEqual(args,[...OWNED_PHP_ARGS,'/tmp/owned/proof/scripts/ci/source-policy-http-initialized-native.php','--owned-http-initializer']);assert.throws(()=>recoveryArguments({stage:'/tmp/owned'},'arbitrary'));
 const native=read('scripts/ci/source-policy-http-initialized-native.php');assert.match(native,/--owned-http-initializer/);assert.match(native,/ADB_HTTP_INITIALIZED_APPROVED/);assert.match(native,/require \$root \. '\/core\/wp-load.php'/);assert.match(native,/1 === did_action\( 'shutdown' \)/);assert.doesNotMatch(native,/wp_install\(|wp_set_current_user|remove_filter|adbh_bootstrap\( 'setup'/);
 for(const file of ['alynt-drime-backups-dashboard.php','.github/workflows/source-policy-minimum-qualification.yml'])assert.doesNotMatch(read(file),/source-policy-http-initialized|owned-http-l714-initialized-v2/);
 const workflow=read('.github/workflows/source-policy-linux-qualification.yml');assert.match(workflow,/execute_initialized_http:[\s\S]*?default: false/);assert.match(workflow,/inputs\.execute_initialized_http != true \|\| \(inputs\.execute_genuine_http == true && inputs\.execute_genuine_request == true && inputs\.http_setup_diagnostic_only != true/);
});
