import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {spawnSync} from 'node:child_process';
import {distributionJob,DISTRIBUTION_MODEL,DISTRIBUTION_WORKFLOW,distributionServer,distributionContainment} from '../../scripts/ci/source-policy-minimum-distribution-contract.mjs';
import {terminal,imageProfile,selections} from '../../scripts/ci/source-policy-minimum-controller.mjs';
const root=new URL('../../',import.meta.url),read=p=>readFileSync(new URL(p,root),'utf8');
const head='a'.repeat(40),run='b'.repeat(32),image='c'.repeat(64),cid='d'.repeat(64),modules='e'.repeat(64);
const job={GITHUB_ACTIONS:'true',GITHUB_EVENT_NAME:'workflow_dispatch',GITHUB_REPOSITORY:'NichlasB/alynt-drime-backups-dashboard',GITHUB_REF:'refs/heads/master',GITHUB_WORKFLOW_REF:DISTRIBUTION_WORKFLOW,GITHUB_SHA:head,EXPECTED_SHA:head,GITHUB_RUN_ID:'123',GITHUB_RUN_ATTEMPT:'1',GITHUB_ACTOR:'NichlasB',RUNNER_OS:'Linux',RUNNER_ENVIRONMENT:'github-hosted',MINIMUM_MODEL:DISTRIBUTION_MODEL,DISTRIBUTION_EXECUTION_APPROVED:'true'};
test('only exact separately approved distribution job can select this model',()=>{
  assert.equal(distributionJob(job),true);
  for(const key of Object.keys(job)) {
    assert.equal(distributionJob({...job,[key]:'other'}),false,key);
    const missing={...job};delete missing[key];assert.equal(distributionJob(missing),false,key+' missing');
  }
  for(const change of [{DISTRIBUTION_EXECUTION_APPROVED:'false'},{GITHUB_RUN_ATTEMPT:'2'},{GITHUB_RUN_ID:'0'},{GITHUB_RUN_ID:123},{GITHUB_RUN_ID:'1'.repeat(21)},{MINIMUM_SCOPE:'audit-only-v1'},{DOCKER_HOST:'tcp://foreign'},{DOCKER_CONTEXT:'remote'}])assert.equal(distributionJob({...job,...change}),false);
});
test('new terminal is distinct, closed and cannot qualify through the old receiver',()=>{
  const wire=`ADBMD1|${run}|${image}|${cid}|1|${modules}|123|9|0|closed|${DISTRIBUTION_MODEL}|false|not-established|false|false\n`;
  assert.equal(terminal(wire,run,image,cid,DISTRIBUTION_MODEL),true);
  assert.equal(terminal(wire,run,image,cid),false);
  const fields=wire.trimEnd().split('|');
  for(let i=0;i<fields.length;i++){const bad=[...fields];bad[i]='other';assert.equal(terminal(bad.join('|')+'\n',run,image,cid,DISTRIBUTION_MODEL),false,'field '+i);}
  for(const bad of [wire+'\n',wire+'noise',wire.slice(0,-1),wire.replace('|123|','|0|'),wire.replace('|9|0|','|8|0|')])assert.equal(terminal(bad,run,image,cid,DISTRIBUTION_MODEL),false);
  assert.equal(terminal(wire,run,image,cid,'unknown'),false);
});
test('exact existing UBI selection cannot replace the old minimum candidate',()=>{
  const row={id:distributionServer.config,os:'linux',arch:'amd64',volumes:{'/var/lib/mysql':{}},entry:['docker-entrypoint.sh'],cmd:['mariadbd']};
  assert.equal(imageProfile(row,'server',DISTRIBUTION_MODEL),true);
  assert.equal(imageProfile(row,'server'),false);
  assert.equal(imageProfile({...row,id:selections.server.config},'server',DISTRIBUTION_MODEL),false);
  for(const [key,value]of Object.entries({id:'wrong',os:'windows',arch:'arm64',volumes:{'/extra':{}},entry:['sh'],cmd:['other']}))assert.equal(imageProfile({...row,[key]:value},'server',DISTRIBUTION_MODEL),false,key);
});
test('distribution containment requires exact builtin posture and bounded logging',()=>{
  const row={security:['no-new-privileges','apparmor=docker-default'],apparmor:'docker-default',cap_add:null,pid_mode:'',ipc_mode:'private',userns_mode:'',log:{Type:'local',Config:{'max-size':'16k','max-file':'1',compress:'false'}}};
  assert.equal(distributionContainment(row),true);
  for(const change of [{security:['no-new-privileges','seccomp=unconfined']},{security:[...row.security,'seccomp=/tmp/custom']},{apparmor:'unconfined'},{cap_add:['SYS_ADMIN']},{pid_mode:'host'},{ipc_mode:'host'},{userns_mode:'host'},{log:{Type:'json-file',Config:{}}}])assert.equal(distributionContainment({...row,...change}),false);
  for(const [key,value]of Object.entries({'max-size':'32k','max-file':'2',compress:'true'}))assert.equal(distributionContainment({...row,log:{...row.log,Config:{...row.log.Config,[key]:value}}}),false,key);
});
test('default-off manual route stages exact worker and never dispatches itself',()=>{
  const flow=read('.github/workflows/source-policy-minimum-distribution.yml'),controller=read('scripts/ci/source-policy-minimum-controller.mjs');
  assert.match(flow,/default: false/);assert.match(flow,/inputs\.execute_owned_proof == true/);
  assert.doesNotMatch(flow,/^  (push|pull_request|schedule):/m);
  assert.match(flow,/persist-credentials: false/);assert.match(flow,/timeout-minutes: 10/);
  assert.match(flow,/source-policy-minimum-controller\.mjs/);assert.match(flow,/if: always\(\)/);
  assert.ok(controller.indexOf('distributionJob(env)')<controller.indexOf('mkdirSync(stage'));
  for(const token of ['source-policy-minimum-distribution.yml','source-policy-minimum-distribution-worker.php','distributionContainment','complete_abi_attestation:false','10.6.23-MariaDB'])assert.ok(controller.includes(token),token);
  const op=read('scripts/ci/source-policy-minimum-native-operation.php');
  assert.ok(op.indexOf('adbm_minimum_binding_mode')<op.indexOf('adbb_construct( $class )'));
  assert.match(op,/if \( 'lab' === \$binding_mode \)/);
  assert.doesNotMatch(op,/bind\( \$owner, '(ready|dbh|qualification)'/);
});
test('in-memory JS semantic controls kill guard bypasses and an always-refused route',async()=>{
  const source=read('scripts/ci/source-policy-minimum-distribution-contract.mjs');
  for(const [before,after,verify]of [
    ["&&env.DISTRIBUTION_EXECUTION_APPROVED==='true'",'',m=>assert.equal(m.distributionJob({...job,DISTRIBUTION_EXECUTION_APPROVED:'false'}),false)],
    ['&&env.GITHUB_WORKFLOW_REF===DISTRIBUTION_WORKFLOW','',m=>assert.equal(m.distributionJob({...job,GITHUB_WORKFLOW_REF:'foreign'}),false)],
    ['&&env.EXPECTED_SHA===env.GITHUB_SHA','',m=>assert.equal(m.distributionJob({...job,EXPECTED_SHA:'f'.repeat(40)}),false)],
    ['return Boolean(env&&','return Boolean(false&&env&&',m=>assert.equal(m.distributionJob(job),true)]
  ]){
    const mutant=source.replace(before,after);assert.notEqual(mutant,source);
    const m=await import('data:text/javascript;base64,'+Buffer.from(mutant).toString('base64'));
    assert.throws(()=>verify(m),assert.AssertionError,'required control must kill '+before);
  }
});
test('PHP evidence controls kill source-substitution and cross-model construction bypasses',()=>{
  const source=read('scripts/ci/source-policy-minimum-distribution-contract.php').replace(/^<\?php\s*/,''),env={...job,MINIMUM_IMAGE:image};
  const evidence={model:DISTRIBUTION_MODEL,platform:'Linux',arch:'x86_64',php:'7.4.33',client:'mysqlnd 7.4.33',core_commit:'cc101b64012b16d087780657a2b828ccd7794a63',runtime_image:'sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4',server_image:'sha256:14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1',initial_mode:0,head,image,run:''};
  // PHP 7.4 can load JSON dynamically: -n fixtures must not depend on it.
  const literal=value=>typeof value==='string'?"'"+value.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'":Number.isSafeInteger(value)?String(value):assert.fail('primitive fixture only');
  const fixture=value=>'array('+Object.entries(value).map(([key,item])=>literal(key)+'=>'+literal(item)).join(',')+')';
  const invoke=(body,tail)=>{
    const script=body+`$j=${fixture(env)};$e=${fixture(evidence)};$e['run']=substr(hash('sha256','123:1'),0,32);`+tail;
    const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-d','disable_functions=json_encode,json_decode','-r',script],{encoding:'utf8',timeout:5000,maxBuffer:8192});
    assert.equal(r.status,0,r.stderr||r.stdout);assert.equal(r.stderr,'');return r.stdout;
  };
  const sourceCheck=body=>assert.equal(invoke(body,`echo adbm_distribution_evidence($e,$j)?'yes':'no';$j['MINIMUM_IMAGE']=str_repeat('f',64);echo adbm_distribution_evidence($e,$j)?'yes':'no';`),'yesno');
  sourceCheck(source);
  const mutant=source.replace("( $env['MINIMUM_IMAGE'] ?? null ) === $evidence['image']",'true');assert.notEqual(mutant,source);assert.throws(()=>sourceCheck(mutant),assert.AssertionError);
  const modeCheck=body=>assert.equal(invoke(body,`define('ADB_MINIMUM_DISTRIBUTION',true);foreach($j as$k=>$v)putenv($k.'='.$v);echo adbm_minimum_binding_mode($e);try{adbm_minimum_binding_mode(array('model'=>'trusted-lab-functional-v1'));echo 'BAD';}catch(RuntimeException $x){echo 'closed';}`),'defaultclosed');
  modeCheck(source);
  const modeMutant=source.replace("! $distribution && 'trusted-lab-functional-v1'","'trusted-lab-functional-v1'");assert.notEqual(modeMutant,source);assert.throws(()=>modeCheck(modeMutant),assert.AssertionError);
});
test('engine output accounting keeps a cleanup reserve without resetting consumed bytes',()=>{
  const source=read('scripts/ci/source-policy-minimum-controller.mjs');
  const body=source.slice(source.indexOf('function command('),source.indexOf('function inspect('));
  const exercise=Function('spawnSync','requireFact',`let distributionAccounting=true,distributionCleanup=false,distributionOutputBytes=49150;${body}
    let refused=false;try{command(['inspect']);}catch{refused=true;}distributionCleanup=true;command(['inspect']);return {refused,bytes:distributionOutputBytes};`);
  assert.deepEqual(exercise(()=>({stdout:'xxx',stderr:'',status:0}),ok=>{if(!ok)throw Error('bounded refusal');}),{refused:true,bytes:49156});
});
test('actual fixed worker entry refuses before loading core or reaching the native worker',()=>{
  const file=new URL('scripts/ci/source-policy-minimum-distribution-worker.php',root).pathname.replace(/^\/([A-Za-z]:\/)/,'$1');
  const r=spawnSync(process.env.PHP_BINARY??'php',['-n',file,'--owned-minimum-qualification','proof'],{encoding:'utf8',timeout:5000,maxBuffer:2048,env:{PATH:process.env.PATH,SystemRoot:process.env.SystemRoot??'',GITHUB_EVENT_NAME:'push'}});
  assert.equal(r.status,1);assert.equal(r.stdout,'');assert.equal(r.stderr,'Requires exact separately approved distribution job\n');
});
test('actual controller admission prefix refuses substituted jobs before engine reads',async()=>{
  const source=read('scripts/ci/source-policy-minimum-controller.mjs');
  const prefix=source.slice(source.indexOf('async function main() {'),source.indexOf('  const auditOnly=env.MINIMUM_SCOPE'));
  const build=Function('process','distributionJob','DISTRIBUTION_MODEL','MODEL','selections','distributionServer','requireFact','command',`let distributionAccounting=false;${prefix}return {distribution,model,fixed};}return main();`);
  const exercise=async env=>{
    const calls=[],process={env,platform:'linux',arch:'x64',getuid:()=>1001,cwd:()=>'/owned'};
    const invoke=()=>build(process,distributionJob,DISTRIBUTION_MODEL,'trusted-lab-functional-v1',selections,distributionServer,ok=>{if(!ok)throw Error('refused');},args=>{calls.push(args);return args[0]==='context'?'unix:///var/run/docker.sock\n':JSON.stringify(['name=apparmor','name=seccomp,profile=builtin']);});
    return {calls,invoke};
  };
  const positive=await exercise(job),accepted=await positive.invoke();
  assert.equal(accepted.distribution,true);assert.equal(accepted.model,DISTRIBUTION_MODEL);assert.equal(accepted.fixed.server,distributionServer);assert.equal(positive.calls.length,2);
  for(const key of Object.keys(job)){
    const probe=await exercise({...job,[key]:'other'});await assert.rejects(probe.invoke(),/refused/);assert.deepEqual(probe.calls,[],key+' before engine');
  }
});
