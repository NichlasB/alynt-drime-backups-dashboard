import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync,writeFileSync,mkdtempSync,rmSync,existsSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve,sep} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';
import {GENUINE_REQUEST,genuineRequestJob,requestCoreFiles,requestSchemaSQL,AUTH_FILES,requestFailureEvidence} from '../../scripts/ci/source-policy-minimum-request-contract.mjs';
import {failureEvidence} from '../../scripts/ci/source-policy-minimum-audit-contract.mjs';
import {minimumOperation,PRODUCTION_JOIN} from '../../scripts/ci/source-policy-minimum-production-contract.mjs';
import {DISTRIBUTION_MODEL,DISTRIBUTION_WORKFLOW} from '../../scripts/ci/source-policy-minimum-distribution-contract.mjs';
import {terminal,MODEL} from '../../scripts/ci/source-policy-minimum-controller.mjs';
const root=fileURLToPath(new URL('../../',import.meta.url)),read=p=>readFileSync(join(root,p),'utf8'),php=process.env.PHP_BINARY??'php';
const literal=v=>typeof v==='string'?"'"+v.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'":v===null?'null':typeof v==='boolean'?(v?'true':'false'):Number.isSafeInteger(v)?String(v):'array('+Object.entries(v).map(([k,x])=>literal(k)+'=>'+literal(x)).join(',')+')';
function phpRun(code){const r=spawnSync(php,['-n','-d','disable_functions=json_encode,ctype_digit','-r',code],{encoding:'utf8',timeout:5000,maxBuffer:8192});assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');return r.stdout;}
const job={GITHUB_ACTIONS:'true',GITHUB_EVENT_NAME:'workflow_dispatch',GITHUB_REPOSITORY:'NichlasB/alynt-drime-backups-dashboard',GITHUB_REF:'refs/heads/master',GITHUB_WORKFLOW_REF:DISTRIBUTION_WORKFLOW,GITHUB_ACTOR:'NichlasB',RUNNER_OS:'Linux',RUNNER_ENVIRONMENT:'github-hosted',GITHUB_RUN_ATTEMPT:'1',GITHUB_RUN_ID:'123',GITHUB_SHA:'a'.repeat(40),EXPECTED_SHA:'a'.repeat(40),MINIMUM_MODEL:DISTRIBUTION_MODEL,DISTRIBUTION_EXECUTION_APPROVED:'true',GENUINE_REQUEST_APPROVED:'true',MINIMUM_OPERATION:GENUINE_REQUEST};
test('CLI doubles are definition-free when discovered by the ordinary PHP suite',()=>{
  const files=['auth','process','fixture'].map(name=>join(root,'tests/support/source-policy-minimum-request-'+name+'-model.php'));
  assert.equal(phpRun(files.map(file=>`require ${literal(file)};`).join('')+`if(function_exists('adbg_check')||function_exists('wp_set_current_user')||class_exists('WP_Hook',false)||defined('ADB_M_REQUEST_PROOF'))throw new RuntimeException('discovery leaked definitions');echo 'inert';`),'inert');
});
test('exact genuine route is separately default-off and PHP/JS reject malformed or cross-mode jobs',()=>{
  assert.equal(genuineRequestJob(job),true);assert.equal(minimumOperation(job),'genuine-request');assert.equal(minimumOperation({}),'adapter');
  const vectors=[job,...Object.keys(job).flatMap(k=>{const absent={...job};delete absent[k];return[{...job,[k]:'other'},absent];}),...['true',true,null,'other'].map(v=>({...job,PRODUCTION_JOIN_APPROVED:v}))];
  for(const [i,v]of vectors.entries()){
    assert.equal(genuineRequestJob(v),i===0);if(i)assert.equal(minimumOperation(v),null);
    assert.equal(phpRun(`require ${literal(join(root,'scripts/ci/source-policy-minimum-request-contract.php'))};echo adbg_entry(${literal(v)})?'yes':'no';`),i===0?'yes':'no');
  }
  assert.equal(minimumOperation({...job,GENUINE_REQUEST_APPROVED:'false',MINIMUM_OPERATION:PRODUCTION_JOIN,PRODUCTION_JOIN_APPROVED:'true'}),'production-join');
});
test('distinct receipts and twenty-file source/owned schema definitions cannot qualify old modes',()=>{
  const run='b'.repeat(32),image='c'.repeat(64),cid='d'.repeat(64),modules='e'.repeat(64),wire=`ADBMGR1|${run}|${image}|${cid}|1|${modules}|42|9|0|closed|${DISTRIBUTION_MODEL}|false|not-established|false|false\n`;
  assert.equal(terminal(wire,run,image,cid,DISTRIBUTION_MODEL,'genuine-request'),true);
  for(const [model,operation]of [[MODEL,'genuine-request'],[DISTRIBUTION_MODEL,'adapter'],[DISTRIBUTION_MODEL,'production-join']])assert.equal(terminal(wire,run,image,cid,model,operation),false);
  for(const bad of [wire+'noise',wire.replace('ADBMGR1','ADBMJP1'),wire.replace('|9|0|','|8|0|'),wire.replace('|closed|','|active|')])assert.equal(terminal(bad,run,image,cid,DISTRIBUTION_MODEL,'genuine-request'),false);
  assert.equal(requestCoreFiles('adapter').length,11);assert.equal(requestCoreFiles('genuine-request').length,20);assert.equal(new Set(requestCoreFiles('genuine-request')).size,20);assert.equal(AUTH_FILES.length,9);
  const prefix='adbm_123456abcdef_',schema=requestSchemaSQL(prefix);assert.ok(schema);assert.equal((schema.match(/CREATE TABLE/g)??[]).length,3);assert.equal((schema.match(/GRANT SELECT,INSERT,UPDATE,DELETE/g)??[]).length,3);assert.doesNotMatch(schema,/ALL PRIVILEGES|GRANT OPTION|DROP |ALTER |wp_/);
  for(const bad of ['',null,{},'wp_','adbm_123456abcdef_;DROP'])assert.equal(requestSchemaSQL(bad),null);
});

test('additional static pins exactly match the approved nine-file design, without loading core',()=>{
  const wire=phpRun(`$pins=require ${literal(join(root,'scripts/ci/source-policy-minimum-request-pins.php'))};foreach($pins as$name=>$row){echo $name,'|',$row[0],'|',$row[1],"\n";}`),pins=Object.fromEntries(wire.trimEnd().split('\n').map(line=>{const [file,bytes,digest]=line.split('|');assert.match(bytes,/^[1-9][0-9]{0,6}$/);return[file,[Number(bytes),digest]];})),design=read('docs/SOURCE_POLICY_GENUINE_REQUEST_CONSTRUCTION_DESIGN.md');
  assert.equal(wire.trimEnd().split('\n').length,9);
  assert.deepEqual(Object.keys(pins).sort(),[...AUTH_FILES].sort());
  for(const [file,[bytes,digest]]of Object.entries(pins)){assert.match(digest,/^[a-f0-9]{64}$/);assert.ok(bytes>0&&bytes<1048576);assert.ok(design.includes(`|${file}|${bytes}|${digest}|`));}
  const bootstrap=read('scripts/ci/source-policy-minimum-request-bootstrap.php');
  assert.doesNotMatch(bootstrap,/wp-settings\.php|default-filters\.php|class-phpass\.php/);assert.match(bootstrap,/unknown|source-policy-minimum-worker-source/);
});

test('library include scope retains the loaded version, not an injected version constant',()=>ownedModel(temporary=>{
  const source=read('scripts/ci/source-policy-minimum-request-bootstrap.php'),prelude=source.slice(source.indexOf('function adbg_load() {')+'function adbg_load() {'.length,source.indexOf('\tadbg_check(',source.indexOf('function adbg_load() {'))),file=join(temporary,'version-data.php');
  writeFileSync(file,"<?php $wp_version='owned-version-data';");
  const verify=body=>assert.equal(phpRun(`function modeled_include(){${body}require ${literal(file)};}modeled_include();echo $GLOBALS['wp_version']??'missing';`),'owned-version-data');
  verify(prelude);const mutant=prelude.replace('global $wp_version;','');assert.notEqual(mutant,prelude);assert.throws(()=>verify(mutant),assert.AssertionError);
  assert.match(source,/actual pinned core version/);assert.doesNotMatch(source,/\$GLOBALS\['wp_version'\]\s*=|\$wp_version\s*=/);
}));
test('auth-only SQL ledger accepts fixed core forms and denies policy/fallback/unknown effects',()=>{
  const p='adbm_123456abcdef_',source=join(root,'scripts/ci/source-policy-minimum-request-observer.php');
  const yes=[`SELECT * FROM ${p}users WHERE user_login = 'owned_one' LIMIT 1`,`SELECT * FROM ${p}users WHERE ID = '1' LIMIT 1`,`SELECT option_name, option_value FROM ${p}options WHERE autoload = 'yes'`,`SELECT option_value FROM ${p}options WHERE option_name = '${p}user_roles' LIMIT 1`,`SELECT user_id, meta_key, meta_value FROM ${p}usermeta WHERE user_id IN (1) ORDER BY umeta_id ASC`,`SELECT umeta_id FROM ${p}usermeta WHERE meta_key = 'session_tokens' AND user_id = 1`,`INSERT INTO \`${p}usermeta\` (\`user_id\`, \`meta_key\`, \`meta_value\`) VALUES ('1', 'session_tokens', 'a:0:{}')`,`UPDATE \`${p}usermeta\` SET \`meta_value\` = 'a:0:{}' WHERE \`user_id\` = '1' AND \`meta_key\` = 'session_tokens'`,`DELETE FROM ${p}usermeta WHERE umeta_id IN( 4,5 )`];
  const no=[...yes.map(s=>s.replaceAll(p,'foreign_')),`SELECT option_value FROM ${p}options WHERE option_name = 'alynt_drime_backups_dashboard_source_policies' LIMIT 1`,`SELECT option_value FROM ${p}options WHERE option_name = 'random_seed' LIMIT 1`,`UPDATE ${p}options SET option_value='x' WHERE option_name='secret'`,`SELECT * FROM ${p}users WHERE ID = 'owned_one' LIMIT 1`,yes[0]+'; DROP TABLE x',yes[0]+' -- hidden',yes[0].replace('owned_one','outside'),"SELECT 'unterminated",'x'.repeat(16385)];
  no.push(yes[5].replace('= 1','= ?'),yes[6].replace("'1'",'?'),yes[7].replace("'1'",'?'));
  assert.equal(phpRun(`require ${literal(source)};foreach(${literal(yes)}as$s){if(adbg_sql_class($s,${literal(p)})===null)throw new RuntimeException('positive shape refused');}foreach(${literal(no)}as$s){if(adbg_sql_class($s,${literal(p)})!==null)throw new RuntimeException('negative accepted');}echo 'ok';`),'ok');
});
function ownedModel(callback){
  const temporary=mkdtempSync(join(tmpdir(),'adb-genuine-prep-')),owned=()=>assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-genuine-prep-'));
  owned();try{return callback(temporary);}finally{owned();rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);}
}
test('authentication fixture driver has discriminating isolated API positives and negatives, not genuine auth',()=>ownedModel(temporary=>{
  const original=read('scripts/ci/source-policy-minimum-request-auth.php'),file=join(temporary,'auth.php');writeFileSync(file,original);
  const cases=['view','save','noop','stale','cookie-missing','cookie-malformed','cookie-expired','cookie-tampered','cookie-user','cookie-destroyed','incapable','nonce-action','nonce-user','nonce-session','nonce-empty','nonce-compound','nonce-expired','site-compound','site-padded','revision-bad','mode-compound','record-missing','record-wrong'];
  const run=scenario=>spawnSync(php,['-n',join(root,'tests/support/source-policy-minimum-request-auth-model.php'),scenario,file],{encoding:'utf8',timeout:5000,maxBuffer:4096});
  for(const scenario of cases){const r=run(scenario);assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.match(r.stdout,/^checks:[1-9][0-9]*\n$/);}
  for(const [before,after,scenario]of [['function adbg_authenticate( $cookie ) {','function adbg_authenticate( $cookie ) { return false;','save'],["wp_validate_auth_cookie( $cookie, 'logged_in' )",'1','cookie-missing'],['wp_set_current_user( $id )','wp_set_current_user( 1 )','incapable'],["'nonce-action' === $scenario ? 'other-owned-action' : $action",'$action','nonce-action'],["WP_Session_Tokens::get_instance( $user )->destroy( $token );",'/* omitted owned destroy */','cookie-destroyed']]){
    const mutant=original.replace(before,after);assert.notEqual(mutant,original,before);writeFileSync(file,mutant);try{const r=run(scenario);assert.notEqual(r.status,0);assert.match(r.stderr,/PHPUnit\\Framework\\ExpectationFailedException/);}finally{writeFileSync(file,original);assert.equal(readFileSync(file,'utf8'),original);}
  }
}));
test('fresh-child supervision and cleanup run against in-memory IO doubles only',()=>ownedModel(temporary=>{
  const original=read('scripts/ci/source-policy-minimum-request-process.php'),file=join(temporary,'process.php');let model=original;
  for(const fn of ['proc_open','proc_get_status','proc_terminate','proc_close'])model=model.replaceAll(fn+'(', 'adbg_model_'+fn+'(');
  writeFileSync(file,model);
  for(const scenario of ['positive','wrong-index','noise','oversize','stderr','sessions','exit','deadline','kill','never-exit','failure']){const r=spawnSync(php,['-n',join(root,'tests/support/source-policy-minimum-request-process-model.php'),scenario,file],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.match(r.stdout,/^checks:[1-9][0-9]*\n$/);}
  for(const [before,after,scenario]of [["'0' === $GLOBALS['wpdb']->get_var( 'SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()' )",'true','sessions'],['adbg_model_proc_close( $process );','/* omitted owned reap */','positive']]){
    const mutant=model.replace(before,after);assert.notEqual(mutant,model);writeFileSync(file,mutant);try{const r=spawnSync(php,['-n',join(root,'tests/support/source-policy-minimum-request-process-model.php'),scenario,file],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.error,undefined);assert.notEqual(r.status,0);assert.match(r.stderr,/PHPUnit\\Framework\\ExpectationFailedException/);}finally{writeFileSync(file,model);}
  }
}));

test('fixture bounds and non-target preservation are error-safe in-memory contracts, not storage proof',()=>{
  for(const scenario of ['positive','failed-large-read','large-value','failed-count','cardinality','per-user','failed-session-read','sessions','session-shape','failed-policy-read','policy-large','policy-malformed','autoload']){
    const r=spawnSync(php,['-n','-d','disable_functions=ctype_digit',join(root,'tests/support/source-policy-minimum-request-fixture-model.php'),scenario,join(root,'scripts/ci/source-policy-minimum-request-fixtures.php')],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.match(r.stdout,/^checks:[1-9][0-9]*\n$/);
  }
});

test('diagnostics carry only bounded enum/label-digest DATA and strict identities precede source load',()=>{
  const wire='ADBMGRF1|child|22|entry|TypeError|'+'a'.repeat(64)+'\n',contract=join(root,'scripts/ci/source-policy-minimum-request-contract.php');
  assert.deepEqual(failureEvidence(wire),requestFailureEvidence(wire));assert.equal(requestFailureEvidence(wire).case_index,22);
  const no=[wire+'noise',wire.replace('|22|','|23|'),wire.replace('|entry|','|cookie-secret|'),wire.replace('TypeError','PrivateException'),wire.replace('a'.repeat(64),'raw-query'),wire.replace('|child|','|other|'),null,{},'x'.repeat(257)];
  for(const bad of no){assert.equal(requestFailureEvidence(bad),null);assert.equal(phpRun(`require ${literal(contract)};echo adbg_failure_valid(${literal(bad)})?'yes':'no';`),'no');}
  assert.equal(phpRun(`require ${literal(contract)};echo adbg_failure_valid(${literal(wire)})?'yes':'no';`),'yes');
  assert.equal(phpRun(`require ${literal(contract)};echo adbg_failure('child',22,'entry',new TypeError('private-cookie-path-SQL'));`),wire.replace('a'.repeat(64),'0'.repeat(64)));
  for(const data of [{MINIMUM_IMAGE:'b'.repeat(64),MINIMUM_SERVER_CID:'c'.repeat(64)},{},{MINIMUM_IMAGE:'b'.repeat(64),MINIMUM_SERVER_CID:'private\nvalue'},{MINIMUM_IMAGE:[],MINIMUM_SERVER_CID:'c'.repeat(64)}])assert.equal(phpRun(`require ${literal(contract)};echo adbg_identity(${literal(data)})?'yes':'no';`),typeof data.MINIMUM_IMAGE==='string'&&data.MINIMUM_SERVER_CID==='c'.repeat(64)?'yes':'no');
});
test('actual entries refuse before core/native work and prepared path preserves sealed runtime and limits',()=>{
  for(const [file,args]of [['source-policy-minimum-request-worker.php',['--owned-minimum-qualification','proof']],['source-policy-minimum-request-child.php',['--owned-minimum-request-child','0']]]){
    const r=spawnSync(php,['-n',join(root,'scripts/ci',file),...args],{encoding:'utf8',timeout:5000,maxBuffer:4096,env:{PATH:process.env.PATH,SystemRoot:process.env.SystemRoot??'',GITHUB_EVENT_NAME:'push'}});assert.equal(r.status,1);assert.equal(r.stdout,'');assert.match(r.stderr,/Requires exact owned genuine request (job|child)\n/);
  }
  const child=read('scripts/ci/source-policy-minimum-request-child.php'),worker=read('scripts/ci/source-policy-minimum-request-worker.php'),flow=read('.github/workflows/source-policy-minimum-distribution.yml'),processSource=read('scripts/ci/source-policy-minimum-request-process.php');
  assert.match(flow,/execute_genuine_request:[\s\S]*?default: false/);assert.doesNotMatch(flow,/^  (push|pull_request|schedule):/m);assert.match(flow,/timeout-minutes: 10/);
  assert.ok(child.indexOf("remove_filter( 'query', 'adbg_observe_query'")<child.indexOf('$class::request_view'));assert.match(child,/\$class::request_submit\( \$request \)/);
  assert.doesNotMatch(child+worker,/newInstanceWithoutConstructor|setValue\(|Closure::bind|adbb_bind|Minimum_Lab_Scope/);
  assert.match(processSource,/20000000000/);assert.match(processSource,/proc_terminate/);assert.match(worker,/adbg_child\( \$index, \$started \)/);
});
