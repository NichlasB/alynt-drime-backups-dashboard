import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync,writeFileSync,mkdtempSync,rmSync,existsSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve,sep} from 'node:path';
import {pathToFileURL,fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';
import {wlLinuxJob,wlEnvelope,validRequestAudit,validRequestCase,wlSchemaSQL,requestBatch,wlRequestFailure,parseRequestWire} from '../../scripts/ci/source-policy-wl-request-contract.mjs';
const root=fileURLToPath(new URL('../../',import.meta.url)),php=process.env.PHP_BINARY??'php';
const read=p=>readFileSync(join(root,p),'utf8'),literal=v=>`json_decode('${JSON.stringify(v).replaceAll('\\','\\\\').replaceAll("'","\\'")}',true)`;
const runPHP=code=>{const r=spawnSync(php,['-n','-r',code],{encoding:'utf8',timeout:5000,maxBuffer:16384});assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');return r.stdout;};
const base={QUALIFICATION_GENUINE_REQUEST_APPROVED:'true',GITHUB_ACTIONS:'true',GITHUB_EVENT_NAME:'workflow_dispatch',GITHUB_WORKFLOW_REF:'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-linux-qualification.yml@refs/heads/master',GITHUB_REPOSITORY:'NichlasB/alynt-drime-backups-dashboard',GITHUB_REF:'refs/heads/master',GITHUB_ACTOR:'NichlasB',GITHUB_RUN_ATTEMPT:'1',GITHUB_RUN_ID:'12345',GITHUB_SHA:'a'.repeat(40),EXPECTED_SHA:'a'.repeat(40)};
const identity={profile:'l',run:wlEnvelope(base,'b'.repeat(64),'audit').WL_REQUEST_RUN,image:'b'.repeat(64)};
const contract=join(root,'scripts/ci/source-policy-wl-request-contract.php').replaceAll('\\','/');
const admit=(env,args=['child','--owned-wl-request-child','0'])=>runPHP(`require '${contract}';echo adbwl_entry(${literal(env)},${literal(args)})?'yes':'no';`);
test('PHP/JS exact separately approved W/L envelopes agree and mixed/unknown jobs refuse before effects',()=>{
  assert.equal(wlLinuxJob(base),true);const env={...base,...wlEnvelope(base,identity.image,'proof','c'.repeat(64))};assert.equal(admit(env),'yes');
  const w={...env,WL_REQUEST_PROFILE:'w',WL_REQUEST_TARGET:'plugin-tester-local-only'};assert.equal(admit(w),'yes');
  assert.throws(()=>wlEnvelope(base,identity.image,'proof'));
  for(const [key,values]of Object.entries({GITHUB_ACTOR:['other',null],GITHUB_WORKFLOW_REF:['other',null],GITHUB_RUN_ATTEMPT:['2',1],EXPECTED_SHA:['b'.repeat(40)],QUALIFICATION_GENUINE_REQUEST_APPROVED:['false',true],GITHUB_EVENT_NAME:['push'],GITHUB_REPOSITORY:['other'],GITHUB_REF:['refs/heads/other'],GITHUB_RUN_ID:['0','01',{},['12345']]}))for(const value of values){const bad={...base,[key]:value};assert.equal(wlLinuxJob(bad),false);assert.equal(admit({...bad,...wlEnvelope(base,identity.image,'proof','c'.repeat(64))}),'no');}
  for(const [key,values]of Object.entries({WL_REQUEST_PROFILE:['m',{},null],WL_REQUEST_APPROVED:['false',true],WL_REQUEST_MODE:['native',true],WL_REQUEST_HEAD:['a'.repeat(39),[]],WL_REQUEST_RUN:['x'.repeat(32),null],WL_REQUEST_IMAGE:['secret\n',{}],WL_REQUEST_MODULES:[null,[]],WL_REQUEST_PREFIX:['adbb_123456789abc_','adbm_ffffffffffff_']}))for(const value of values)assert.equal(admit({...env,[key]:value}),'no',key);
  for(const args of [['child','bad','0'],['child','--owned-wl-request-child','23'],['child','--owned-wl-request-child','00'],['child','--owned-wl-request-child',[]]])assert.equal(admit(env,args),'no');
  assert.equal(admit({...w,WL_REQUEST_TARGET:'live'}),'no');assert.equal(admit({...env,WL_REQUEST_MODE:'audit'},['child','--owned-wl-request-child','1']),'no');
});
test('nonlegacy source-derived autoload query is exact, core-bound and never broadens M or unknown SQL',()=>{
  const prefix='adbm_123456789abc_',query=`SELECT option_name, option_value FROM ${prefix}options WHERE autoload IN ( 'yes', 'on', 'auto-on', 'auto' )`;
  const observer=join(root,'scripts/ci/source-policy-minimum-request-observer.php').replaceAll('\\','/');
  assert.equal(runPHP(`require '${observer}';foreach(array('7.1.3','7.1.4')as$c){if(adbg_sql_class(${literal(query)},'${prefix}',$c)!=='role-read')throw new RuntimeException('positive');}foreach(array('6.0','7.1.5',null)as$c){if(adbg_sql_class(${literal(query)},'${prefix}',$c)!==null)throw new RuntimeException('wrong core');}echo 'ok';`),'ok');
  const bad=[query.replace("'auto'","'secret'"),query.replace('options','users'),query+' LIMIT 1',query+'; SELECT 1',query.replace('adbm_123456789abc_','adbm_ffffffffffff_'),`SELECT option_name, option_value FROM ${prefix}options`,`SELECT option_value FROM ${prefix}options WHERE option_name='alynt_drime_backups_dashboard_source_policies'`];
  assert.equal(runPHP(`require '${observer}';foreach(${literal(bad)}as$s){if(adbg_sql_class($s,'${prefix}','7.1.4')!==null)throw new RuntimeException('negative');}echo 'ok';`),'ok');
  assert.equal(runPHP(`require '${observer}';echo adbg_sql_class("SELECT option_name, option_value FROM ${prefix}options WHERE autoload = 'yes'",'${prefix}','7.1.4')===null?'ok':'bad';`),'ok');
});
test('both independently audited nonlegacy catalogs bind 21 distinct files; definitions do not load core',()=>{
  const bootstrap=join(root,'scripts/ci/source-policy-wl-request-bootstrap.php').replaceAll('\\','/');
  const rows=JSON.parse(runPHP(`define('ADB_WL_REQUEST_PROOF',true);define('ADB_M_REQUEST_PROOF',true);require '${bootstrap}';$out=array();foreach(array('w','l')as$p){putenv('WL_REQUEST_PROFILE='.$p);$out[$p]=adbwl_catalog();}if(defined('ABSPATH')||class_exists('wpdb',false)||function_exists('mysqli_init'))throw new RuntimeException('effects');echo json_encode($out);`));
  for(const row of Object.values(rows)){assert.equal(Object.keys(row.pins).length,12);assert.equal(Object.keys(row.auth).length,9);assert.equal(new Set([...Object.keys(row.pins),...Object.keys(row.auth)]).size,21);assert.equal(row.auth['pluggable.php'][1],'3a2482a65b50d62ebae75a728be98b78985d3a3f5a3e007cb2d0c1338bf05777');}
  assert.equal(rows.w.commit,'fc9832bef919c2a08541248db23af90d14ffb082');assert.equal(rows.l.commit,'db96f5eaa77a8d3e8eec8923a518fa2326f0d46a');assert.notEqual(rows.w.pins['version.php'],rows.l.pins['version.php']);assert.deepEqual(rows.w.auth,rows.l.auth);
  assert.equal(runPHP(`define('ADB_WL_REQUEST_PROOF',true);define('ADB_M_REQUEST_PROOF',true);require '${bootstrap}';putenv('WL_REQUEST_PROFILE=unknown');echo adbwl_catalog()===false?'closed':'bad';`),'closed');
  assert.match(read('scripts/ci/source-policy-wl-request-bootstrap.php'),/global \$wp_version;/);assert.doesNotMatch(read('scripts/ci/source-policy-wl-request-bootstrap.php'),/\$wp_version\s*=|newInstanceWithoutConstructor|setValue\(/);
});
test('schema statements cannot select another database/account, and diagnostics never retain private messages',()=>{
  const sql=wlSchemaSQL('adbm_123456789abc_');assert.equal((sql.match(/CREATE TABLE adb_owned\./g)??[]).length,3);assert.equal((sql.match(/GRANT SELECT,INSERT,UPDATE,DELETE ON adb_owned\./g)??[]).length,3);assert.doesNotMatch(sql,/DROP|ALL PRIVILEGES|WITH GRANT|adb_minimum/);
  for(const bad of [null,{},'adbb_123456789abc_','adbm_123456789abc_;DROP','adbm_ABCDEFabcdef_'])assert.throws(()=>wlSchemaSQL(bad));
  const wire='ADBWLRF1|l|child|22|auth|RuntimeException|'+'a'.repeat(64)+'\n';assert.equal(wlRequestFailure(wire).case_index,22);
  for(const bad of [wire+'secret',wire.replace('|l|','|m|'),wire.replace('|22|','|23|'),wire.replace('|auth|','|private-cookie|'),{},wire.replace('a'.repeat(64),'raw SQL')])assert.equal(wlRequestFailure(bad),null);
});
async function exercise(batch=requestBatch,scenario='positive',profile='l'){
  let clock=0,launches=0,reconciles=0,cleanups=0,releases=0;
  const state={...identity,profile},io={now:()=>clock,reconcile:async left=>{assert.ok(left>0&&left<=20000);reconciles++;},launch:async(index,left)=>{
    assert.ok(left>0&&left<=20000);launches++;clock+=scenario==='deadline'?1200:1;let alive=true;
    const n=index<4?(index===0?1:2):(index>=21?1:0),row={event:'request-closed',profile,run:identity.run,image:identity.image,pid:100+index,index,checks:10,setup:1,auth:1,records:n,connects:index<4?n:0,commands:index<4?20:n,closed:true};
    if(index===0){if(scenario==='image')row.image='f'.repeat(64);if(scenario==='unavailable'){row.connects=0;row.commands=0;}if(scenario==='dead')alive=false;}
    return {row,pid:100+index,alive:()=>alive,release:async ack=>{assert.equal(ack,`ACK:${identity.run}:${identity.image}:${index}\n`);releases++;alive=false;},cleanup:async()=>{cleanups++;alive=false;}};
  }};
  if(scenario==='positive'){const result=await batch(io,state);assert.equal(result.cases,23);assert.equal(result.checks,230);assert.equal(result.production_ready,false);assert.equal(launches,23);assert.equal(reconciles,46);assert.equal(releases,23);assert.equal(cleanups,23);}
  else{await assert.rejects(()=>batch(io,state));assert.ok(launches<23);assert.equal(cleanups,launches);}
}
test('actual fixed batch supervises all positives and refuses unavailable/foreign/dead/deadline cases with cleanup',async()=>{
  for(const profile of ['w','l'])for(const scenario of ['positive','image','unavailable','dead','deadline'])await exercise(requestBatch,scenario,profile);
  const cleanupFailure=new Error('owned cleanup refused');let attemptedCleanup=0;
  await assert.rejects(()=>requestBatch({now:()=>0,launch:async()=>({row:{},pid:100,alive:()=>true,cleanup:async()=>{attemptedCleanup++;throw cleanupFailure;}})},identity),error=>{
    assert.ok(error instanceof AggregateError);assert.equal(error.errors.length,2);assert.match(error.errors[0].message,/Exact held request terminal refused/);assert.equal(error.errors[1],cleanupFailure);return true;
  });assert.equal(attemptedCleanup,1);
  const audit={event:'audit',profile:'l',run:identity.run,image:identity.image,modules:'c'.repeat(64),checks:1,native_connections:0,sql_queries:0};assert.equal(validRequestAudit(audit,'l',identity.run),true);assert.equal(validRequestAudit({...audit,sql_queries:1},'l',identity.run),false);
  const wire=JSON.stringify(audit)+'\n';assert.deepEqual(parseRequestWire(wire),audit);assert.equal(parseRequestWire(wire.replace('"profile":"l"','"profile":"l","profile":"l"')),null);assert.equal(parseRequestWire(wire+'noise'),null);
  assert.equal(validRequestCase({},'l',identity.run,identity.image,0,100),false);
});
test('actual W/L seed/guard/readback boundaries have non-vacuous in-memory positives and failure controls',()=>{
  const temporary=mkdtempSync(join(tmpdir(),'adb-wl-fixtures-')),file=join(temporary,'fixtures.php'),original=read('scripts/ci/source-policy-wl-request-fixtures.php');
  const run=scenario=>spawnSync(php,['-n',join(root,'tests/support/source-policy-wl-request-fixture-model.php'),scenario,file],{encoding:'utf8',timeout:5000,maxBuffer:8192});
  try{
    writeFileSync(file,original);
    for(const scenario of ['positive','next','empty-read','nonempty','roles','nontarget','policy','autoload','verify-read','preflight']){const r=run(scenario);assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.match(r.stdout,/^checks:[1-9][0-9]*\n$/);}
    for(const [before,after,scenario]of [["'0' === $wpdb->get_var", "true || '0' === $wpdb->get_var",'nonempty'],['adbg_reset_case( $scenario );','/* omitted reset */','positive'],['adbg_preflight();','/* omitted bounded verification */','preflight'],['adbg_non_target() === $before','true','nontarget'],["'no' === $row['autoload']",'true','autoload']]){
      const mutant=original.replace(before,after);assert.notEqual(mutant,original,before);writeFileSync(file,mutant);try{const r=run(scenario);assert.equal(r.error,undefined);assert.notEqual(r.status,0);assert.match(r.stderr,/PHPUnit\\Framework\\ExpectationFailedException/);}finally{writeFileSync(file,original);assert.equal(readFileSync(file,'utf8'),original);}
    }
  }finally{assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-wl-fixtures-'));rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);}
});
test('source-backed supervisor controls assertion-kill terminal/live/reap/deadline bypasses and restore owned copies',async()=>{
  const temporary=mkdtempSync(join(tmpdir(),'adb-wl-controls-')),file=join(temporary,'contract.mjs'),original=read('scripts/ci/source-policy-wl-request-contract.mjs');
  assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-wl-controls-'));
  try{
    const controls=[
      [s=>s.replace('!validRequestCase(child.row,identity.profile,identity.run,identity.image,index,child.pid)','false'),'image'],
      [s=>s.replace('await io.reconcile(20000-(io.now()-started));','/* omitted live reconciliation */'),'positive'],
      [s=>s.replace('await child.cleanup();','/* omitted exact owned cleanup */'),'positive'],
      [s=>s.replace('const started=io.now();','let started=io.now();').replace('const remaining=20000-','started=io.now();const remaining=20000-'),'deadline'],
      [s=>s.replace('for(let index=0;index<REQUEST_CASES.length;index++){','for(let index=0;index<1;index++){'),'positive']
    ];
    for(let i=0;i<controls.length;i++){const [change,scenario]=controls[i],mutant=change(original);assert.notEqual(mutant,original);writeFileSync(file,mutant);const loaded=await import(pathToFileURL(file).href+'?control='+i);await assert.rejects(()=>exercise(loaded.requestBatch,scenario),{code:'ERR_ASSERTION'});writeFileSync(file,original);assert.equal(readFileSync(file,'utf8'),original);}
  }finally{assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-wl-controls-'));rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);}
});
test('actual entry refuses on the ordinary test runtime before core/site/native effects; routing stays manual/default-off',()=>{
  const result=spawnSync(php,['-n',join(root,'scripts/ci/source-policy-wl-request-child.php'),'--owned-wl-request-child','0'],{encoding:'utf8',timeout:5000,maxBuffer:4096,env:{PATH:process.env.PATH,SystemRoot:process.env.SystemRoot??''}});assert.equal(result.status,1);assert.equal(result.stdout,'');assert.match(result.stderr,/Requires exact owned W\/L genuine request child/);
  const flow=read('.github/workflows/source-policy-linux-qualification.yml'),w=read('scripts/manual/source-policy-wl-request-controller.ps1'),shared=read('scripts/ci/source-policy-minimum-request-child-body.php');
  assert.match(flow,/execute_genuine_request:[\s\S]*?default: false/);assert.doesNotMatch(flow,/^  (push|pull_request|schedule):/m);assert.match(flow,/QUALIFICATION_GENUINE_REQUEST_APPROVED: 'false'/);
  const shell=read('scripts/ci/source-policy-linux-qualification.sh');assert.ok(shell.indexOf('GITHUB_WORKFLOW_REF')<shell.indexOf('docker context inspect'));
  assert.match(read('scripts/ci/source-policy-linux-qualification-controller.mjs'),/alive:\(\)=>closed===null&&child\.exitCode===null&&child\.signalCode===null/);
  assert.match(w,/\$index -lt 23/);assert.doesNotMatch(w.split('for($index=0;')[1],/\.Restart\(/);assert.match(w,/fixture_cleanup='external-owned-scope-required'/);
  assert.ok(shared.indexOf("remove_filter( 'query', 'adbg_observe_query'")<shared.indexOf('$class::request_view'));assert.doesNotMatch(shared,/newInstanceWithoutConstructor|setValue\(|Closure::bind|proc_open\(/);
});
