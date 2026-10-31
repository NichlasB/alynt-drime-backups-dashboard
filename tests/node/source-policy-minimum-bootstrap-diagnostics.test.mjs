import {test} from 'node:test';
import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {readFileSync} from 'node:fs';
import {bootstrapEvidence,failureEvidence} from '../../scripts/ci/source-policy-minimum-audit-contract.mjs';
const root=new URL('../../',import.meta.url),read=p=>readFileSync(new URL(p,root),'utf8');
const path=p=>new URL(p,root).pathname.replace(/^\/([A-Za-z]:\/)/,'$1');
const progress='ADBMBD1|1111|error|1|0|0|closed|closed|1|1|0|0|1\n';
const failure='ADBMF1|escape|native|467|0\n';
function child(args){const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-d','disable_functions=json_encode,json_decode',...args],{encoding:'utf8',timeout:5000,maxBuffer:8192});assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');return r.stdout;}
test('actual observer on an isolated positive receiver records prerequisites and active progress without effects',()=>{
  const row=bootstrapEvidence(child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'positive']));
  assert.deepEqual(row.before,{settings:'1',source:'1',context:'1',scope:'1'});
  assert.equal(row.result,'true');assert.equal(row.after_internal_cleanup.native,'active');assert.equal(row.after_internal_cleanup.ready,'1');
  assert.equal(row.after_internal_cleanup.position,'8');assert.equal(row.after_internal_cleanup.credits,'12');
});
test('native-free modeled server/source/context refusals remain failures and distinguish retained progress from cleanup state',()=>{
  const server=bootstrapEvidence(child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'bad-server']));
  assert.equal(server.result,'error');assert.equal(server.after_internal_cleanup.position,'0');assert.equal(server.after_internal_cleanup.credits,'0');
  assert.equal(server.after_internal_cleanup.native,'closed');assert.equal(server.after_internal_cleanup.bootstrap_handle_present,'1');
  const source=bootstrapEvidence(child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'bad-source']));
  assert.equal(source.before.source,'0');assert.equal(source.after_internal_cleanup.bootstrap_handle_present,'0');assert.equal(source.result,'error');
  const context=bootstrapEvidence(child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'bad-context']));
  assert.equal(context.before.context,'0');assert.equal(context.result,'error');assert.equal(context.after_internal_cleanup.bootstrap_handle_present,'0');
});
test('unknown identities cannot invoke magic or expose objects, credentials or handles',()=>{
  const wire=child([path('tests/support/source-policy-minimum-diagnostics-worker.php'),'unknown']),row=bootstrapEvidence(wire);
  assert.deepEqual(row.before,{settings:'u',source:'u',context:'u',scope:'u'});
  assert.equal(row.after_internal_cleanup.ready,'u');assert.doesNotMatch(wire,/password|private|mysqli|source-policy|[\/\\]/);
});
test('optional progress prefix cannot become a receipt or erase the bounded original first failure',()=>{
  const row=failureEvidence(progress+failure);assert.equal(row.stage,'escape');assert.equal(row.checks,467);assert.equal(row.bootstrap.result,'error');
  assert.equal(failureEvidence(progress),null);assert.equal(failureEvidence(progress+progress+failure),null);assert.equal(failureEvidence(failure+progress),null);
  assert.deepEqual(failureEvidence(failure),{stage:'escape',phase:'native',checks:467,groups:0});
  const fields=progress.trimEnd().split('|');for(let i=0;i<fields.length;i++){const bad=[...fields];bad[i]='secret/path';assert.equal(failureEvidence(bad.join('|')+'\n'+failure),null,'field '+i);}
  for(const bad of [progress+'\n',progress.slice(0,-1),progress.replace('|0|0|closed','|9|17|closed'),progress.repeat(100)])assert.equal(bootstrapEvidence(bad),null);
});
test('extension-free PHP serializer refuses malformed scalar DATA without exposing a payload',()=>{
  const setup=`require '${path('scripts/ci/source-policy-minimum-bootstrap-diagnostics.php')}';$row=array('before'=>'1111','result'=>'error','attempted'=>'1','position'=>'0','credits'=>'0','native'=>'closed','context'=>'closed','close_attempted'=>'1','closed'=>'1','ready'=>'0','dbh'=>'0','handle'=>'1');`;
  assert.equal(child(['-r',setup+'echo adbm_bootstrap_wire($row);']),progress);
  for(const change of ["$row['position']='9';","$row['credits']='17';","$row['before']='11111';","$row['result']='private-message';","$row['native']=new stdClass();","$row['ready']=true;","$row['password']='never-print';"])
    assert.equal(child(['-r',setup+change+'echo adbm_bootstrap_wire($row);']),'');
  const v2=`ADBMF2|escape|native|467|0|${'a'.repeat(64)}|RuntimeException|0|none|0|1111111|Core;mysqli\n`;
  assert.equal(failureEvidence(progress+v2).assertion,'a'.repeat(64));
  const names=Array.from({length:64},(_,i)=>('A'+String(i).padStart(2,'0')).padEnd(64,'x')).join(';');
  const maximum=v2.replace('Core;mysqli',names);assert.ok(Buffer.byteLength(progress+maximum)<=4608);
  assert.equal(failureEvidence(progress+maximum).inventory.names.length,64);
});
test('actual wrapper preserves first diagnostic, rethrows the same error and invokes once per attempt',()=>{
  const op=read('scripts/ci/source-policy-minimum-native-operation.php');
  const a=op.indexOf('$before = adbm_bootstrap_before('),b=op.indexOf("\n\tadbb_check( 0 === ( new mysqli_driver() )->report_mode, 'owned initializer preserves OFF'",a);
  assert.ok(a>=0&&b>a);const source=op.slice(a,b);
  // PHP 7.4 treats a class-named method case-insensitively as an old-style constructor.
  const fixture=`class Owner {public $ready=false;}class WP_Error {}class FixedBootstrapInvocationDouble {public $calls=0,$mode='error',$error;function invoke($owner){++$this->calls;if($this->mode==='throw')throw $this->error;if($this->mode==='success'){$owner->ready=true;return true;}return new WP_Error();}}
    function adbm_bootstrap_before(...$args){return '1111';}function adbm_bootstrap_after(...$args){++$GLOBALS['snapshots'];return array('fixed'=>'first');}function adbb_check($ok,$label){if(!$ok)throw new RuntimeException('Refused');}
    $owner=new Owner();$context=new stdClass();$frame=$bound=array();$bootstrap=new FixedBootstrapInvocationDouble();$GLOBALS['snapshots']=0;`;
  const run=code=>child(['-r',fixture+code]);
  assert.equal(run(`try{${source}}catch(Throwable $e){}$bootstrap->mode='throw';$bootstrap->error=new Error('never-print');try{${source}}catch(Throwable $e){echo $e===$bootstrap->error?'same':'changed';}echo '|'.$bootstrap->calls.'|'.$GLOBALS['snapshots'].'|'.$GLOBALS['adbm_bootstrap_failure']['fixed'];`),'same|2|1|first');
  assert.equal(run(`$bootstrap->mode='success';${source}echo $bootstrap->calls.'|'.$GLOBALS['snapshots'].'|'.(isset($GLOBALS['adbm_bootstrap_failure'])?'bad':'none');`),'1|0|none');
  const replay=source.replace('$result = $bootstrap->invoke( $owner );','$result = $bootstrap->invoke( $owner );$bootstrap->invoke( $owner );');assert.notEqual(replay,source);
  assert.throws(()=>assert.equal(run(`$bootstrap->mode='success';${replay}echo $bootstrap->calls;`),'1'),assert.AssertionError);
});
test('source-backed integration observes one invocation, retains refusal and leaves runtime files uninstrumented',()=>{
  const op=read('scripts/ci/source-policy-minimum-native-operation.php'),worker=read('scripts/ci/source-policy-minimum-qualification.php');
  assert.equal((op.match(/\$bootstrap->invoke\( \$owner \)/g)??[]).length,1);
  assert.match(op,/adbb_check\( true === \$result && \$owner->ready, 'actual ambient-bound bootstrap' \)/);
  assert.match(op,/adbm_bootstrap_failure'\] \?\? adbm_bootstrap_after/);assert.match(op,/throw \$error;/);
  assert.ok(op.indexOf('$owners[] = $owner')<op.indexOf('adbm_bootstrap_before('));
  assert.ok(worker.indexOf("if ( 'audit' === $argv[2] )")<worker.indexOf("$stage             = 'ambient'"));
  const diagnostic=read('scripts/ci/source-policy-minimum-bootstrap-diagnostics.php');
  assert.doesNotMatch(diagnostic,/mysqli_(query|init|real_connect|close|report)\(|->bootstrap\(|->begin\(|->finish\(|->close\(|->setValue\(|json_encode\(/);
  assert.equal((diagnostic.match(/->invoke\(/g)??[]).length,1);assert.match(diagnostic,/new ReflectionMethod\( \$context, 'provenance' \)/);assert.match(diagnostic,/\$provenance->invoke\( \$context \)/);
  assert.match(diagnostic,/restore_error_handler\(\)/);assert.doesNotMatch(diagnostic,/getMessage\(|getTrace|dbpassword|\['password'\]/);
});
