import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {spawnSync} from 'node:child_process';
import {createHash} from 'node:crypto';
import {AUDIT_WORKFLOW,executionPhases,failureEvidence,releaseIssues} from '../../scripts/ci/source-policy-minimum-audit-contract.mjs';
const root=new URL('../../',import.meta.url),read=p=>readFileSync(new URL(p,root),'utf8');
const digest=createHash('sha256').update('fixed dynamic module profile').digest('hex');
function precedes(source,a,b){assert.ok(source.indexOf(a)>=0,a);assert.ok(source.indexOf(b)>=0,b);assert.ok(source.indexOf(a)<source.indexOf(b),a+' before '+b);}
const record=`ADBMF2|source-audit|image|445|0|${digest}|RuntimeException|0|none|0|1111111|Core;date;mysqli;mysqlnd\n`;
function php(code) {
  const helper=new URL('scripts/ci/source-policy-minimum-failure-evidence.php',root).pathname.replace(/^\/([A-Za-z]:\/)/,'$1');
  const child=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',`require '${helper.replaceAll("'","\\'")}';${code}`],{encoding:'utf8',timeout:5000,maxBuffer:8192});
  assert.equal(child.status,0,child.stderr);assert.equal(child.stderr,'');return child.stdout;
}
const fixture=`$f=array('stage'=>'source-audit','phase'=>'image','checks'=>445,'groups'=>0,'assertion'=>'${digest}','exception'=>'RuntimeException','diagnostic'=>null);`;
test('first assertion metadata and inventory survive PHP -n without JSON or native execution',()=>{
  const out=php(fixture+`echo adbm_failure_wire($f,array('facts'=>'1111111','names'=>array('Core','date','mysqli','mysqlnd')));`);
  assert.equal(out,record);const row=failureEvidence(out);
  assert.equal(row.assertion,digest);assert.equal(row.checks,445);assert.equal(row.groups,0);
  assert.equal(row.inventory.php_root,'1');assert.deepEqual(row.inventory.names,['Core','date','mysqli','mysqlnd']);
});
test('actual first-capture function cannot be replaced by later cleanup failure',()=>{
  const worker=read('scripts/ci/source-policy-minimum-qualification.php');
  const capture=worker.slice(worker.indexOf('function adbb_capture('),worker.indexOf('/** Classifies existing acknowledgement'));
  const verify=source=>{
    const out=php(source+`$checks=445;$groups=0;$stage='source-audit';$phase='image';adbb_capture(new RuntimeException('DO-NOT-LOG /private/password'),'fixed dynamic module profile');$stage='positive';$phase='native';$checks=999;adbb_capture(new Error('later secret'),'other');echo adbm_failure_wire($GLOBALS['adbb_first_failure'],null);`);
    const row=failureEvidence(out);assert.equal(row.stage,'source-audit');assert.equal(row.phase,'image');assert.equal(row.checks,445);assert.equal(row.exception,'RuntimeException');assert.equal(row.assertion,digest);
    assert.doesNotMatch(out,/secret|private|password|later/);
  };
  verify(capture);
  const mutant=capture.replace(/if \( null !== \( \$GLOBALS\['adbb_first_failure'\] \?\? null \) \) \{\s*return;\s*\}/,'');
  assert.notEqual(mutant,capture);assert.throws(()=>verify(mutant),assert.AssertionError,'removing first-cause guard must be assertion-killed');
});
test('absent, malformed and oversized inventory cannot erase first cause or leak raw bytes',()=>{
  for(const inv of ['null',"array('facts'=>'1111111','names'=>array('/private/secret'))","array('facts'=>'1111111','names'=>array_fill(0,65,'Core'))","array('facts'=>'1111111','names'=>array('z','A'))","array('facts'=>'1111111','names'=>array('A','A'))"]) {
    const out=php(fixture+`echo adbm_failure_wire($f,${inv});`),row=failureEvidence(out);
    assert.equal(row.assertion,digest);assert.deepEqual(row.inventory,inv==='null'?null:{status:'invalid'});assert.doesNotMatch(out,/private|secret/);
  }
});
test('diagnostic severity/source/line categories are bounded and malformed failures are withheld',()=>{
  const out=php(fixture+`$f['diagnostic']=array('severity'=>2,'source'=>'core','line'=>123);echo adbm_failure_wire($f,null);`);
  assert.deepEqual(failureEvidence(out).diagnostic,{severity:2,source:'core',line:123});
  for(const mutation of ["$f['stage']='secret';","$f['checks']=100000;","$f['exception']='/private/error';","$f['diagnostic']=array('severity'=>3,'source'=>'core','line'=>1);","$f['diagnostic']=array('severity'=>2,'source'=>'/path','line'=>1);"])
    assert.equal(php(fixture+mutation+'echo adbm_failure_wire($f,null);'),'');
});
test('receiver refuses all malformed fields, suffixes, inventory overflow and injected diagnostics',()=>{
  const fields=record.trimEnd().split('|');
  for(let i=0;i<fields.length;i++){const bad=[...fields];bad[i]='untrusted/secret';assert.equal(failureEvidence(bad.join('|')+'\n'),null,'field '+i);}
  for(const bad of [record+'\n',record+'secret',record.slice(0,-1),record.replace('|0|none|0|','|2|none|1|'),record.replace('|0|none|0|','|3|core|1|'),record.replace('Core;date;mysqli;mysqlnd','date;Core'),record.replace('Core;date;mysqli;mysqlnd','A;A'),record.replace('Core;date;mysqli;mysqlnd',Array.from({length:65},(_,i)=>'n'+i).sort().join(';')),record.replace('Core;date;mysqli;mysqlnd','A'.repeat(65)),record.repeat(100)])assert.equal(failureEvidence(bad),null);
});
test('only sealed exact audit scope can remove proof from execution phases',()=>{
  assert.deepEqual(executionPhases(undefined,undefined,undefined),['audit','proof']);
  assert.deepEqual(executionPhases('audit-only-v1','--audit-only',AUDIT_WORKFLOW),['audit']);
  for(const [s,a,w]of [[undefined,undefined,AUDIT_WORKFLOW],[undefined,'--audit-only',AUDIT_WORKFLOW],['audit-only-v1',undefined,AUDIT_WORKFLOW],['other','--audit-only',AUDIT_WORKFLOW],['audit-only-v1','--audit-only',AUDIT_WORKFLOW+'evil']])assert.equal(executionPhases(s,a,w),null);
});
test('release classification never treats nonzero worker exit as clean removal proof',()=>{
  assert.deepEqual(releaseIssues('php',{oom:false,running:false,exit:0}),[]);
  assert.deepEqual(releaseIssues('php',{oom:false,running:false,exit:1}),['php-nonzero-exit']);
  assert.deepEqual(releaseIssues('server',{oom:true,running:true,exit:137}),['server-oom','server-still-running','server-nonzero-exit']);
});
test('isolated semantic controls kill audit fallback and nonzero-exit bypasses without editing source',async()=>{
  const source=read('scripts/ci/source-policy-minimum-audit-contract.mjs');
  for(const [original,mutated,verify]of [
    ['&&workflow!==AUDIT_WORKFLOW','',m=>assert.equal(m.executionPhases(undefined,undefined,AUDIT_WORKFLOW),null)],
    ["row.exit!==0?role+'-nonzero-exit':null",'null',m=>assert.deepEqual(m.releaseIssues('php',{oom:false,running:false,exit:1}),['php-nonzero-exit'])]
  ]) {
    const mutant=source.replace(original,mutated);assert.notEqual(mutant,source);
    const module=await import('data:text/javascript;base64,'+Buffer.from(mutant).toString('base64'));
    assert.throws(()=>verify(module),assert.AssertionError,'guard bypass must be assertion-killed');
  }
});
function modeledCleanup(change={}) {
  const source=read('scripts/ci/source-policy-minimum-controller.mjs');
  const body=source.slice(source.indexOf('function cleanup('),source.indexOf('async function worker('));
  const rows=new Map([['php',{network:'container:server',label:'owned',running:false,oom:false,exit:change.exit??0}],['server',{network:'none',label:change.foreign?'foreign':'owned',running:true,oom:false,exit:0,hostname:'fixed'}]]);
  let stage=true;const logs=[],sql=[],removed=[];
  const deps={
    ownedIds:()=>[...rows.keys()],inspect:id=>({...rows.get(id)}),rootSQL:(_id,text)=>{sql.push(text);return change.absence?'1\t0\n':'0\t0\n';},
    reconcile:()=>{if(change.reconcile)throw Error('private SQL error must not escape');},
    command:args=>{if(args[0]==='stop')rows.get(args.at(-1)).running=false;if(args[0]==='rm'){if(change.remove)throw Error('private engine error');rows.delete(args.at(-1));removed.push(args.at(-1));}return 'fixed safe log';},
    reviewLog:()=>{},requireFact:(ok,label)=>{if(!ok)throw Error(label);},releaseIssues,
    existsSync:()=>stage,realpathSync:p=>p,lstatSync:()=>({isSymbolicLink:()=>false,uid:1001}),rmSync:()=>{stage=false;},
    process:{getuid:()=>1001,stdout:{write:text=>logs.push(JSON.parse(text))}}
  };
  const cleanup=Function(...Object.keys(deps),'return ('+body+');')(...Object.values(deps));
  let error=null;try{cleanup('owned','/owned',true);}catch(e){error=e;}
  return {error,sql,removed,report:logs.at(-1).minimum_cleanup};
}
test('actual cleanup function distinguishes modeled complete removal from strict worker release failure',()=>{
  const positive=modeledCleanup();assert.equal(positive.error,null);
  assert.deepEqual(positive.report,{scope:'audit-only',issues:[],containers_removed:true,stage_removed:true,strict_release_complete:true});
  const failed=modeledCleanup({exit:1});assert.match(failed.error.message,/cleanup\/release evidence incomplete/);
  assert.deepEqual(failed.report,{scope:'audit-only',issues:['php-nonzero-exit'],containers_removed:true,stage_removed:true,strict_release_complete:false});
  for(const result of [positive,failed])for(const query of result.sql){assert.match(query,/^SELECT /);assert.doesNotMatch(query,/DROP|CREATE|GRANT|password/);}
});
test('actual cleanup function reports modeled reconciliation, absence, ownership and removal failures without a clean receipt',()=>{
  for(const [change,issue,containersRemoved]of [[{reconcile:true},'server-reconciliation',true],[{absence:true},'audit-absence-readback',true],[{foreign:true},'ownership-readback',false],[{remove:true},'php-remove',false]]) {
    const result=modeledCleanup(change);assert.ok(result.error);assert.ok(result.report.issues.includes(issue));assert.equal(result.report.containers_removed,containersRemoved);assert.equal(result.report.strict_release_complete,false);
    assert.doesNotMatch(JSON.stringify(result.report),/private|SQL error|engine error/);
    if(change.reconcile)assert.deepEqual(result.sql,[],'reconciliation refusal must not continue to fixture SQL');
    if(change.foreign)assert.ok(!result.removed.includes('server'),'foreign owner never removed');
  }
});
test('source-backed audit entry and cleanup cannot dispatch proof or erase primary failure',()=>{
  const flow=read('.github/workflows/source-policy-minimum-audit.yml'),controller=read('scripts/ci/source-policy-minimum-controller.mjs');
  const worker=read('scripts/ci/source-policy-minimum-qualification.php'),source=read('scripts/ci/source-policy-minimum-worker-source.php');
  assert.match(flow,/workflow_dispatch:/);assert.doesNotMatch(flow,/^  (push|pull_request|schedule):/m);
  precedes(flow,'"$EXPECTED_SHA" == "$GITHUB_SHA"','uses: actions/checkout@');
  assert.match(flow,/MINIMUM_SCOPE: audit-only-v1/);assert.match(flow,/controller\.mjs --audit-only/);assert.match(flow,/if: always\(\)/);assert.match(flow,/group: source-policy-minimum-qualification/);
  assert.match(controller,/for\(const phase of phases\)/);assert.match(controller,/if\(phase==='proof'\) \{\s*requireFact\(!auditOnly,'audit cannot create application fixtures'\)/);
  assert.match(controller,/password=auditOnly\?null:randomBytes/);assert.match(controller,/const removal=auditOnly\?'':/);
  assert.match(controller,/catch\(error\) \{primaryFailure=error;throw error;\}/);assert.match(controller,/if\(primaryFailure\).*original failure preserved/);
  assert.match(controller,/strict_release_complete:issues.size===0&&containersRemoved&&stageRemoved/);
  precedes(controller,'containersRemoved=true',"requireFact(issues.size===0,'cleanup/release evidence incomplete')");
  assert.match(worker,/'audit-only-v1' !== getenv\( 'MINIMUM_SCOPE' \).*'audit' !== \$argv\[2\].*false !== getenv\( 'QUALIFICATION_PASSWORD' \)/);
  precedes(worker,"if ( 'audit' === $argv[2] )","$phase = 'native'");
  assert.match(source,/\$GLOBALS\['adbm_inventory'\]\s*= array/);
  precedes(source,"$GLOBALS['adbm_inventory'] ","adbb_check( $php_root,");
  assert.match(source,/! extension_loaded\( 'pdo_mysql' \) && ! extension_loaded\( 'mbstring' \)/);
  assert.match(source,/count\( \$names \) <= 64/);assert.doesNotMatch(worker+source,/json_encode\(|mysqli_report\(/);
});
