import test from 'node:test';
import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
import {readFileSync} from 'node:fs';
import {InitializedFixtureData,INITIALIZED_HTTP_PROFILE} from '../../scripts/ci/source-policy-http-initialization.mjs';
import {prepareInitializedWorkerController} from '../../scripts/ci/source-policy-http-initialized-controller.mjs';
import {manifestSummary} from '../../scripts/ci/source-policy-http-profile.mjs';
import * as f from './source-policy-http-fixtures.mjs';
const path=fileURLToPath(new URL('../../scripts/ci/source-policy-http-initialized-worker.php',import.meta.url));
const literal=v=>typeof v==='string'?"'"+v.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'":typeof v==='boolean'?(v?'true':'false'):typeof v==='number'?String(v):'array('+Object.entries(v).map(([k,x])=>literal(k)+'=>'+literal(x)).join(',')+')';
const input={identity:f.identity(),profile:{...INITIALIZED_HTTP_PROFILE},storage:f.storage(),manifest:f.manifest(),expectation:manifestSummary(f.manifest()),anchors:'8'.repeat(64)};
function ticket(){const m=new InitializedFixtureData(input,0);m.installed({...m.binding(),role:'installer',sql:1021,exited:true,sessions:0,transactions:0,ddl_sealed:true,fixtures_verified:true},1);return m.begin(2);}
const facts={sapi:'cli',before_bootstrap:true,installing_override:false,short_init_override:false,protected_cache_absent:true,protected_non_autoloaded:true,owner_absent:true,ddl_sealed:true};
const observed={full_bootstrap:true,installing:false,short_init:false,protected_reads:0,protected_writes:0,owner_allocations:0,session_writes:0,mail:0,outbound:0,file_writes:0,source_unchanged:true,anchors_unchanged:true};
const fixture=`$ticket=${literal(ticket())};$identity=${literal(f.identity())};$facts=${literal(facts)};$observed=${literal(observed)};`+String.raw`
$hooks=array();$at=2;$calls=array();$mode='positive';$executed=0;
function expect($ok,$label){if(!$ok)throw new AssertionError($label);}
function add_filter($hook,$cb,$priority=10,$args=1){global $hooks,$mode;$hooks[$hook][]=array($cb,$priority,$args);return 'register-fail'!==$mode;}
function has_filter($hook,$cb){global $hooks;foreach($hooks[$hook]??array() as $entry){if($entry[0]===$cb)return $entry[1];}return false;}
function remove_filter($hook,$cb,$priority=10){global $hooks,$mode;if('cleanup-fail'===$mode&&'query'===$hook)return false;foreach($hooks[$hook]??array() as $key=>$entry){if($entry[0]===$cb&&$entry[1]===$priority)unset($hooks[$hook][$key]);}if('late-effect'===$mode&&'pre_wp_mail'===$hook){try{$cb[0]->deny();}catch(RuntimeException $e){}}return true;}
function apply($hook,$value){global $hooks;foreach($hooks[$hook]??array() as $entry){$value=call_user_func($entry[0],$value);}return $value;}
function query($sql){global $at,$executed;$at++;$result=apply('query',$sql);expect($result===$sql,'same-query-bytes');++$executed;}
function owned_callbacks(){global $hooks;$n=0;foreach($hooks as $rows)foreach($rows as $row){if(is_array($row[0])&&$row[0][0] instanceof ADBH_Initialized_Worker_Preparation)$n++;}return $n;}
function api(){return array('kind'=>'isolated-initializer-worker-v2',
 'clock'=>static function(){return $GLOBALS['at'];},
 'bootstrap'=>static function(){global $calls,$mode;$calls[]='bootstrap';if('throw'===$mode)throw new RuntimeException('PRIVATE SQL/password');if('unavailable'===$mode)return false;
  if('protected'===$mode){query("SELECT option_value FROM adbh_aaaaaaaaaaaa_options WHERE option_name = 'alynt_drime_backups_dashboard_source_policies'");}
  if('swallowed'===$mode){try{apply('pre_http_request',false);}catch(RuntimeException $e){}}
  if('credit'===$mode){for($i=0;$i<4;$i++)query('SELECT VERSION()');}
  query('SELECT VERSION()');return true;},
 'shutdown'=>static function(){global $calls,$mode;$calls[]='shutdown';if('shutdown-effect'===$mode)apply('pre_wp_mail',null);if('shutdown-missing'===$mode)return false;query('SELECT FOUND_ROWS()');return true;},
 'observe'=>static function(){global $calls,$observed,$mode,$at;$calls[]='observe';if('expired'===$mode)$at=60000;return $observed;});}
function worker($api=null){global $ticket,$identity,$facts;return new ADBH_Initialized_Worker_Preparation($ticket,$identity,$facts,$api??api());}
function refused($fn,$label='refusal'){try{$fn();throw new AssertionError('assert-'.$label);}catch(RuntimeException $e){expect(false===strpos($e->getMessage(),'PRIVATE'),'safe-error');}}
`;
function run(code,source=null){
 const load=source===null?`require ${literal(path)};`:`require ${literal(fileURLToPath(new URL('../../scripts/ci/source-policy-http-initialization.php',import.meta.url)))};require ${literal(fileURLToPath(new URL('../../scripts/ci/source-policy-http-installer-data.php',import.meta.url)))};eval(base64_decode('${Buffer.from(source.replace(/^<\?php/,'').replaceAll("__DIR__ . '/source-policy-http-initialization.php'",literal(fileURLToPath(new URL('../../scripts/ci/source-policy-http-initialization.php',import.meta.url)))).replaceAll("__DIR__ . '/source-policy-http-installer-data.php'",literal(fileURLToPath(new URL('../../scripts/ci/source-policy-http-installer-data.php',import.meta.url))))).toString('base64')}'));`;
 return spawnSync(process.env.PHP_BINARY??'php',['-n','-d','disable_functions=json_encode,json_decode','-r',load+fixture+code],{encoding:'utf8',timeout:5000,maxBuffer:8192});
}
const passed=code=>{const r=run(code);assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');return r.stdout;};
const positive=String.raw`$w=worker();$row=$w->run();expect($row['event']==='initializer-worker-prepared-v2'&&$row['callbacks_closed']===true&&$row['runtime_grant']===false&&$row['production_ready']===false,'positive-envelope');expect($row['completion']['sql']===2,'positive-query-count');expect($calls===array('bootstrap','shutdown','observe'),'positive-order');expect(0===owned_callbacks(),'positive-cleanup');`;

test('definition import cannot register hooks or load WordPress/native APIs before fixtures exist',()=>{
 const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',`require ${literal(path)};if(ADBH_INITIALIZED_WORKER_EXECUTION_ENABLED!==false||function_exists('add_filter')||defined('ABSPATH')||class_exists('wpdb',false)||function_exists('mysqli_init'))throw new AssertionError('import-effect');echo 'definitions-only';`],{encoding:'utf8',timeout:5000,maxBuffer:4096});
 assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.equal(r.stdout,'definitions-only');
});

test('worker definitions are dormant; real guard/worker model completes a positive and retains no callback',()=>{
 assert.equal(passed(positive+`expect(ADBH_INITIALIZED_WORKER_EXECUTION_ENABLED===false&&!defined('ABSPATH')&&!class_exists('wpdb',false)&&!function_exists('mysqli_init'),'dormant');refused(static function()use($w){$w->run();},'replay');echo 'positive2/closed';`),'positive2/closed');
});
test('actual PHP worker packet round-trips through the controller and lifecycle without JSON or repaired fields',async()=>{
 let at=0,packet;
 const result=await prepareInitializedWorkerController({kind:'isolated-initialized-controller-v2',now:()=>at,
  install:b=>{at=1;return {...b,role:'installer',sql:1021,exited:true,sessions:0,transactions:0,ddl_sealed:true,fixtures_verified:true};},
  initialize:t=>{
   assert.deepEqual(t,ticket());
   const raw=passed(`$ticket=${literal(t)};`+positive+`foreach($row as $key=>$value){if($key==='completion'){foreach($value as $k=>$v){echo 'completion.'.$k.'='.(is_bool($v)?($v?'true':'false'):$v)."\n";}}else{echo $key.'='.(is_bool($value)?($value?'true':'false'):$value)."\n";}}`);
   const schema={...t,sql:0,...observed};packet={completion:{}};
   const envelopeSchema={event:'',callbacks_closed:false,runtime_grant:false,production_ready:false};
   for(const line of raw.trim().split('\n')){
    const parts=line.split('=');assert.equal(parts.length,2);const [key,value]=parts;
    const nested=key.startsWith('completion.'),target=nested?packet.completion:packet,k=key.replace(/^completion\./,'');
    const expected=nested?schema:envelopeSchema;assert.equal(Object.hasOwn(expected,k),true);assert.equal(Object.hasOwn(target,k),false);
    const type=typeof expected[k];if(type==='boolean')assert.ok(['true','false'].includes(value));if(type==='number')assert.match(value,/^(0|[1-9][0-9]*)$/);
    target[k]=type==='boolean'?value==='true':type==='number'?Number(value):value;
   }
   assert.equal(typeof packet.completion.anchors,'string');assert.equal(packet.completion.anchors,input.anchors);at=10;return packet;
  },
  reconcile:b=>({...b,role:'initializer-release',exited:true,sessions:0,transactions:0,anchors_unchanged:true,source_unchanged:true}),
  baseline:b=>({...b,role:'full-invariant',digest:'7'.repeat(64),all_options:true,all_cron:true,all_widgets:true,all_transients:true,non_session_usermeta:true}),
  cleanup:()=>f.cleanup()
 },input);
 assert.equal(result.prepared,true);assert.equal(result.total_setup_sql,1023);assert.equal(result.http_qualified,false);
 const model=new InitializedFixtureData(input,0);model.installed({...model.binding(),role:'installer',sql:1021,exited:true,sessions:0,transactions:0,ddl_sealed:true,fixtures_verified:true},1);model.begin(2);model.initialized(packet.completion,10);assert.equal(model.result().state,'initialized');
});
test('unavailable bootstrap/shutdown, protected SQL, swallowed effects and late observations fail with cleanup',()=>{
 for(const mode of ['throw','unavailable','protected','swallowed','shutdown-effect','shutdown-missing','expired','register-fail','late-effect','credit']){
  assert.equal(passed(`$mode=${literal(mode)};$w=worker();refused(static function()use($w){$w->run();});expect(0===owned_callbacks(),'failure-cleanup');echo 'refused';`),'refused',mode);
 }
 for(const key of Object.keys(observed))assert.equal(passed(`$observed[${literal(key)}]=${typeof observed[key]==='boolean'?literal(!observed[key]):1};$w=worker();refused(static function()use($w){$w->run();});expect(0===owned_callbacks(),'observation-cleanup');echo 'refused';`),'refused',key);
});
test('all effect hooks deny before a simulated effect and fixed registration priorities cannot be changed',()=>{
 for(const hook of ['pre_http_request','flush_rewrite_rules_hard','block_local_requests','pre_wp_mail']){
  assert.equal(passed(`$api=api();$api['bootstrap']=static function(){apply(${literal(hook)},false);$GLOBALS['executed']++;return true;};$w=worker($api);refused(static function()use($w){$w->run();});expect(0===$executed&&0===owned_callbacks(),'zero-effects');echo 'blocked';`),'blocked');
 }
 assert.equal(passed(`$api=api();$api['bootstrap']=static function(){foreach($GLOBALS['hooks']['query'] as $entry)expect($entry[1]===1&&$entry[2]===1,'query-priority');query('SELECT VERSION()');return true;};$w=worker($api);$w->run();echo 'priority1';`),'priority1');
});
test('wrong cohort/root/role and reconstruction refuse; unrelated callbacks survive exact owned cleanup',()=>{
 for(const key of ['run','head','image','root'])assert.equal(passed(`$identity[${literal(key)}]=${literal(key==='root'?'/other':key==='head'?'0'.repeat(40):key==='image'?'0'.repeat(64):'0'.repeat(32))};refused(static function(){worker();});expect(0===owned_callbacks(),'pre-registration');echo 'refused';`),'refused');
 assert.equal(passed(`$ticket['role']='installer';refused(static function(){worker();});expect(0===owned_callbacks(),'role-pre-registration');echo 'refused';`),'refused');
 assert.equal(passed(`$api=api();$api['clock']=static function(){throw new RuntimeException('PRIVATE clock');};refused(static function()use($api){worker($api);});expect(0===owned_callbacks(),'clock-pre-registration');echo 'refused';`),'refused');
 assert.equal(passed(`$w=worker();refused(static function()use($w,$ticket,$identity,$facts){$w->__construct($ticket,$identity,$facts,api());});echo 'refused';`),'refused');
 assert.equal(passed(`$foreign=static function($q){return $q;};add_filter('query',$foreign,0,1);${positive}expect(has_filter('query',$foreign)===0,'foreign-preserved');echo 'preserved';`),'preserved');
 assert.equal(passed(`$mode='cleanup-fail';$w=worker();try{$w->run();throw new AssertionError('cleanup-credited');}catch(RuntimeException $e){expect($e->getMessage()==='Initializer worker cleanup unconfirmed','cleanup-unknown');}expect(1===owned_callbacks(),'attempt-all-removals');echo 'unknown';`),'unknown');
 assert.equal(passed(`$mode='cleanup-fail';$api=api();$api['bootstrap']=static function(){throw new RuntimeException('PRIVATE');};$w=worker($api);try{$w->run();throw new AssertionError('both-failures-credited');}catch(RuntimeException $e){expect($e->getMessage()==='Initializer worker preparation refused; cleanup unconfirmed','both-failures');}echo 'both';`),'both');
});
test('worker guard, required bootstrap and shutdown omissions are assertion-killed semantic controls',()=>{
 const source=readFileSync(path,'utf8'),controls=[
  ["true === ( $this->api['bootstrap'] )()",'true',positive,'positive-query-count'],
  ["true === ( $this->api['shutdown'] )()",'true',positive,'positive-query-count'],
  ["$observed === $expected",'true',`$observed['protected_reads']=1;$w=worker();refused(static function()use($w){$w->run();},'protected-observation');`,'assert-protected-observation'],
  ["$at < $this->ticket['setup_deadline']",'true',`$mode='expired';$w=worker();refused(static function()use($w){$w->run();},'expired');`,'assert-expired'],
  ["! $failed && 'running' === $this->state",'true',`$mode='late-effect';$w=worker();refused(static function()use($w){$w->run();},'late-effect');`,'assert-late-effect'],
 ];
 for(const [before,after,code,label] of controls){assert.equal(source.split(before).length,2,before);const r=run(code,source.replace(before,after));assert.notEqual(r.status,0);assert.match(r.stdout+r.stderr,new RegExp('AssertionError: '+label));assert.doesNotMatch(r.stdout+r.stderr,/Parse error|syntax error|Failed opening/);}
});
