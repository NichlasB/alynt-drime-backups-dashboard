import {test} from 'node:test';
import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {readFileSync} from 'node:fs';
import {bootstrapCauseEvidence,failureEvidence} from '../../scripts/ci/source-policy-minimum-audit-contract.mjs';
const root=new URL('../../',import.meta.url),read=p=>readFileSync(new URL(p,root),'utf8');
const path=p=>new URL(p,root).pathname.replace(/^\/([A-Za-z]:\/)/,'$1');
const cause='ADBMBC1|RuntimeException|bootstrap|120|connected_unqualified\n';
const progress='ADBMBD1|1111|error|1|0|0|closed|closed|1|1|0|0|1\n',failure='ADBMF1|escape|native|470|0\n';
function child(args){const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-d','disable_functions=json_encode,json_decode',...args],{encoding:'utf8',timeout:5000,maxBuffer:8192});assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');return r.stdout;}

test('actual caught server refusal retains exact first location and pre-cleanup state without raw message or native reissue',()=>{
  const source=read('includes/traits/trait-source-policy-native-bootstrap.php');
  const line=source.slice(0,source.indexOf("throw new RuntimeException( 'Server profile unavailable' )")).split('\n').length;
  const row=bootstrapCauseEvidence(child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'cause-bad-server']));
  assert.deepEqual(row,{kind:'RuntimeException',source:'bootstrap',line,state_before_cleanup:'connected_unqualified',server_observation:'different'});
  const prefixed=bootstrapCauseEvidence(child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'cause-prefixed-server']));
  assert.equal(prefixed.server_observation,'legacy_greeting');
  assert.equal(child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'cause-positive']),'');
  const early=bootstrapCauseEvidence(child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'cause-bad-source']));
  assert.equal(early.state_before_cleanup,'never_opened');assert.equal(early.source,'bootstrap');assert.equal(early.server_observation,'unobserved');
  assert.equal(child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'cause-unknown']),'');
  assert.doesNotMatch(JSON.stringify(row),/password|private|owned|[\/\\]/);
});

test('new optional cause prefix preserves legacy/progress contracts and rejects duplicate, standalone, malformed or secret fields',()=>{
  assert.deepEqual(bootstrapCauseEvidence(cause),{kind:'RuntimeException',source:'bootstrap',line:120,state_before_cleanup:'connected_unqualified'});
  const parsed=failureEvidence(cause+progress+failure);assert.equal(parsed.bootstrap_cause.line,120);assert.equal(parsed.bootstrap.result,'error');assert.equal(parsed.checks,470);
  assert.equal(failureEvidence(cause),null);assert.equal(failureEvidence(cause+cause+progress+failure),null);assert.equal(failureEvidence(progress+cause+failure),null);
  assert.equal(failureEvidence(progress+failure).bootstrap_cause,undefined);
  for(const bad of [cause.trimEnd(),cause+'\n',cause.replace('|120|','|0120|'),cause.replace('|120|','|1000000|'),cause.replace('|bootstrap|','|/secret/path|'),cause.replace('RuntimeException','private-message'),cause.replace('connected_unqualified','arbitrary')])assert.equal(bootstrapCauseEvidence(bad),null);
  const names=Array.from({length:64},(_,i)=>('A'+String(i).padStart(2,'0')).padEnd(64,'x')).join(';');
  const maximum=`ADBMF2|escape|native|470|0|${'a'.repeat(64)}|RuntimeException|0|none|0|1111111|${names}\n`;
  assert.ok(Buffer.byteLength(cause+progress+maximum)<=4608);assert.equal(failureEvidence(cause+progress+maximum).inventory.names.length,64);
  const v2=cause.replace('ADBMBC1','ADBMBC2').replace('\n','|legacy_greeting\n');
  assert.equal(failureEvidence(v2+progress+maximum).bootstrap_cause.server_observation,'legacy_greeting');
  assert.ok(Buffer.byteLength(v2+progress+maximum)<=4608);
  for(const bad of [v2.replace('|legacy_greeting',''),v2.replace('legacy_greeting','private-message'),v2.replace('|legacy_greeting','|different|extra'),v2.replace('ADBMBC2','ADBMBC1')])assert.equal(bootstrapCauseEvidence(bad),null);
  assert.equal(failureEvidence(v2+cause+progress+failure),null);
});

test('private cause reader refuses malformed metadata and unknown identities without magic, messages, paths or SQL',()=>{
  const setup=`define('ABSPATH','${path('')}');require '${path('scripts/ci/source-policy-minimum-bootstrap-diagnostics.php')}';class UnknownOwner {public static $calls=0;function __get($x){++self::$calls;throw new RuntimeException('private');}}$o=new UnknownOwner();echo adbm_bootstrap_cause_wire($o,$o);if($o::$calls!==0)exit(2);`;
  assert.equal(child(['-r',setup]),'');
  const runtime=read('includes/traits/trait-source-policy-native-bootstrap.php');
  const start=runtime.search(/\$kind\s*=\s*get_class\( \$error \);/),end=runtime.indexOf("return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );",start);
  assert.ok(start>0&&end>start);const capture=runtime.slice(start,end);
  assert.doesNotMatch(capture,/getMessage|getTrace|mysqli_|->close\(|->bootstrap\(|->query\(/);
  assert.match(capture,/__FILE__ === \$error->getFile\(\)/);assert.match(capture,/getLine\(\) <= 999999/);
  assert.ok(end<runtime.indexOf('} finally {',end));
  const serverCheck=runtime.slice(runtime.indexOf('$expected_server'),runtime.indexOf("$identity = $this->bootstrap_probe( 'identity' )"));
  assert.equal((serverCheck.match(/mysqli_get_server_info\(/g)??[]).length,1);
  assert.match(serverCheck,/if \( \$expected_server !== \$actual_server \)/);
  for(const name of ['extra','line','object','kind'])assert.equal(child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'cause-malformed-'+name]),'');
  const fixture=body=>`class FixedCatchMetadataDouble {public $native_state='connecting',$bootstrap_failure=null,$bootstrap_server='unobserved';function capture($error){${body}}}$o=new FixedCatchMetadataDouble();$o->capture(new RuntimeException('never-export-private'));if(!is_array($o->bootstrap_failure)||array_keys($o->bootstrap_failure)!==array('kind','source','line','state','server')||$o->bootstrap_failure['kind']!=='RuntimeException'||$o->bootstrap_failure['state']!=='connecting')throw new RuntimeException('capture-assertion-control');echo 'captured';`;
  assert.equal(child(['-r',fixture(capture)]),'captured');
  const mutant=capture.replace('$this->bootstrap_failure','$ignored');assert.notEqual(mutant,capture);
  const killed=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',fixture(mutant)],{encoding:'utf8',timeout:5000,maxBuffer:8192});
  assert.notEqual(killed.status,0);assert.match(killed.stdout+killed.stderr,/RuntimeException: capture-assertion-control/);
});
