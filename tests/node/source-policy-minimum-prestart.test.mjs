import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {selections,containerProfile} from '../../scripts/ci/source-policy-minimum-controller.mjs';
import {serverConfigurationDiagnostics} from '../../scripts/ci/source-policy-minimum-configuration-diagnostics.mjs';
import {probePrestartServer,cleanupPrestartServer,prestartCreateArgs} from '../../scripts/ci/source-policy-minimum-prestart.mjs';

const owner='adb-minimum-prestart-12345-1',uid=1001,stage='/tmp/'+owner,cid='a'.repeat(64);
function positive() {
  return {entry:['/bin/sh'],image:selections.server.config,label:owner,user:'1001:1001',ro:true,network:'none',memory:536870912,cpu:1000000000,pids:64,
    privileged:false,ports:null,caps:['ALL'],security:['no-new-privileges'],devices:null,mounts:[],
    tmpfs:{'/var/lib/mysql':'rw,nosuid,nodev,noexec,size=256m,mode=700,uid=1001,gid=1001','/tmp':'rw,nosuid,nodev,noexec,size=16m,mode=700,uid=1001,gid=1001'},
    status:'created',running:false,pid:0,restarts:0};
}
test('positive diagnostics agree with the original predicate and have no failed clause',()=>{
  const report=serverConfigurationDiagnostics(positive(),owner,uid,stage);
  assert.equal(report.accepted,true);assert.equal(report.agreement,true);assert.deepEqual(report.failed,[]);
  assert.equal(Object.keys(report.checks).length,21);assert.equal(report.runtime_grant,false);assert.equal(report.native_qualification,false);
});
test('only null and a truly empty plain JSON binding map satisfy the no-bindings gate',()=>{
  for(const ports of [null,JSON.parse('{}')]) {
    const row={...positive(),ports};
    assert.equal(containerProfile(row,'server',owner,selections.server.config,null,uid,stage),true);
    const report=serverConfigurationDiagnostics(row,owner,uid,stage);
    assert.equal(report.accepted,true);assert.equal(report.agreement,true);assert.deepEqual(report.failed,[]);
  }
  const hidden=Object.defineProperty({},'3306/tcp',{value:[],enumerable:false});
  const symbolOnly={[Symbol('binding')]:[]};
  const accessor=Object.defineProperty({},'toJSON',{get(){assert.fail('No arbitrary getter/coercion');}});
  for(const ports of [undefined,false,true,0,'',[],new Date(),new Map(),Object.create(null),Object.create({binding:[]}),hidden,symbolOnly,accessor,{'3306/tcp':[]},{binding:undefined}]) {
    assert.equal(containerProfile({...positive(),ports},'server',owner,selections.server.config,null,uid,stage),false);
  }
});
test('each protected field change is independently identified without changing admission',()=>{
  const cases={image:['bad','image'],label:['bad','label'],user:['0:0','user'],ro:[false,'read_only'],entry:[['/bin/other'],'entrypoint'],
    network:['host','network'],memory:[999999999,'memory'],cpu:[2000000000,'cpu'],pids:[100,'pids'],privileged:[true,'privileged'],ports:[{'3306/tcp':[]},'port_bindings'],
    caps:[[],'capabilities'],security:[[],'security'],devices:[[{}],'devices'],tmpfs:[{},'data_tmpfs_options'],mounts:[[{Type:'bind'}],'bind_mounts']};
  for(const [field,[value,clause]]of Object.entries(cases)) {
    const row={...positive(),[field]:value},report=serverConfigurationDiagnostics(row,owner,uid,stage);
    assert.equal(containerProfile(row,'server',owner,selections.server.config,null,uid,stage),false,field);
    assert.equal(report.accepted,false,field);assert.equal(report.agreement,true,field);assert.ok(report.failed.includes(clause),field);
  }
});
test('null and empty plain maps are admitted while nonempty/malformed maps remain rejected',()=>{
  for(const [value,description,accepted]of [[null,'null',true],[{},'empty-object',true],[{'3306/tcp':[]},'nonempty-object',false],[[],'array',false]]) {
    const report=serverConfigurationDiagnostics({...positive(),ports:value},owner,uid,stage);
    assert.equal(report.observed.port_bindings_shape,description);assert.equal(report.accepted,accepted);assert.equal(report.agreement,true);
  }
});
test('malformed and multi-fault input produces bounded categories, never raw secret fields',()=>{
  const secret='SECRET-never-output';
  const row={...positive(),image:secret,entry:[secret],label:secret,user:secret,security:[secret],ports:{[secret]:secret},tmpfs:{[secret]:secret},Env:[secret],message:secret};
  const report=serverConfigurationDiagnostics(row,owner,uid,stage),wire=JSON.stringify(report);
  assert.equal(report.accepted,false);assert.equal(report.agreement,true);assert.ok(report.failed.length>=7);assert.ok(wire.length<4096);assert.equal(wire.includes(secret),false);
  for(const value of [null,{},[],{...positive(),mounts:null},{...positive(),tmpfs:null}]) {
    const bad=serverConfigurationDiagnostics(value,owner,uid,stage);assert.equal(bad.accepted,false);assert.equal(bad.agreement,true);
  }
  const thrown=serverConfigurationDiagnostics({...positive(),mounts:[null]},owner,uid,stage);
  assert.equal(thrown.admission_error,true);assert.equal(thrown.agreement,false);assert.equal(thrown.checks.mount_types,'invalid-data');
  for(const path of ['/var/lib/mysql','/tmp']) {
    const row=positive();row.tmpfs[path]+=',extra';const bad=serverConfigurationDiagnostics(row,owner,uid,stage);
    assert.equal(bad.accepted,false);assert.equal(bad.agreement,true);assert.deepEqual(bad.failed,[path==='/tmp'?'scratch_tmpfs_options':'data_tmpfs_options']);
  }
});
function fake(row,options={}) {
  let created=Boolean(options.preexisting);const calls=[];
  const docker=args=>{
    calls.push(args);
    switch(args[0]) {
      case 'ps':return created?cid+'\n':'';
      case 'pull':assert.equal(args.at(-1),selections.server.ref);return '';
      case 'image':return JSON.stringify({id:selections.server.config,os:'linux',arch:'amd64',volumes:{'/var/lib/mysql':{}},entry:['docker-entrypoint.sh'],cmd:['mariadbd']});
      case 'create':assert.deepEqual(args,prestartCreateArgs(owner,uid));created=true;return cid+'\n';
      case 'inspect':return JSON.stringify(options.inspectError?{...row,running:true,status:'running',pid:42}:row);
      case 'rm':assert.deepEqual(args,['rm','--volumes',cid]);if(options.removeError)throw Error('owned-rm-failure');created=false;return cid+'\n';
      default:assert.fail('Forbidden engine command '+args[0]);
    }
  };
  return {docker,calls,exists:()=>created};
}
test('one rejected-profile probe completes readback and cleanup without starting anything',()=>{
  const engine=fake({...positive(),ports:{'3306/tcp':[]}}),report=probePrestartServer(engine.docker,owner,uid,stage);
  assert.equal(report.diagnostic.accepted,false);assert.deepEqual(report.diagnostic.failed,['port_bindings']);assert.equal(report.native_qualification,false);
  assert.equal(report.server_started,false);assert.equal(report.php_executed,false);assert.equal(report.sql_executed,false);assert.equal(report.accounts_created,0);
  assert.equal(engine.exists(),false);assert.equal(engine.calls.filter(args=>args[0]==='create').length,1);assert.equal(engine.calls.filter(args=>args[0]==='rm').length,1);
  assert.equal(report.cleanup.removed,1);
  assert.ok(engine.calls.every(args=>['ps','pull','image','create','inspect','rm'].includes(args[0])));
});
test('positive modeled profile also completes without conferring a native/runtime grant',()=>{
  const engine=fake({...positive(),ports:{}}),report=probePrestartServer(engine.docker,owner,uid,stage);
  assert.equal(report.diagnostic.accepted,true);assert.equal(report.runtime_grant,false);assert.equal(report.production_ready,false);assert.equal(engine.exists(),false);
});
test('unexpected started fixture is refused and retained, never stopped or force-removed',()=>{
  const engine=fake(positive(),{inspectError:true});assert.throws(()=>probePrestartServer(engine.docker,owner,uid,stage),/cleanup-not-owned-unstarted/);
  assert.equal(engine.exists(),true);assert.equal(engine.calls.some(args=>['rm','stop','kill','start','exec'].includes(args[0])),false);
});
test('cleanup failures cannot emit a success receipt; foreign ownership is never removed',()=>{
  const engine=fake(positive(),{removeError:true}),observations=[];assert.throws(()=>probePrestartServer(engine.docker,owner,uid,stage,value=>observations.push(value)),/owned-rm-failure/);assert.equal(engine.exists(),true);
  assert.equal(observations.length,1);assert.equal(observations[0].unstarted,true);assert.equal(observations[0].diagnostic.accepted,true);
  const foreign=fake({...positive(),label:'foreign'});assert.throws(()=>probePrestartServer(foreign.docker,owner,uid,stage),/cleanup-not-owned-unstarted/);assert.equal(foreign.calls.some(args=>args[0]==='rm'),false);
  const empty=fake(positive());assert.equal(cleanupPrestartServer(empty.docker,owner,uid).removed,0);
  const prior=fake(positive(),{preexisting:true});assert.throws(()=>probePrestartServer(prior.docker,owner,uid,stage),/fresh-container-namespace/);
  assert.deepEqual(prior.calls.map(args=>args[0]),['ps']);assert.equal(prior.exists(),true);
});
test('fixture identity and resource command cannot select another image, credential or network',()=>{
  assert.throws(()=>prestartCreateArgs('other',uid),/fixture-identity/);assert.throws(()=>prestartCreateArgs(owner,0),/fixture-identity/);
  const args=prestartCreateArgs(owner,uid);assert.equal(args.at(-1),'exit 99');assert.equal(args.includes('--privileged'),false);assert.equal(args.includes('--env-file'),false);assert.equal(args.includes('--mount'),false);
  for(const flag of ['--read-only','--cap-drop','--tmpfs','--pids-limit','--memory'])assert.ok(args.includes(flag));
});
test('dedicated source/workflow has no startup/execution or native qualification dispatch path',()=>{
  const root=new URL('../../',import.meta.url),read=name=>readFileSync(new URL(name,root),'utf8');
  const probe=read('scripts/ci/source-policy-minimum-prestart.mjs'),flow=read('.github/workflows/source-policy-minimum-prestart.yml');
  assert.doesNotMatch(probe,/docker\(\[\s*['"](?:start|run|exec|stop|kill)['"]/);assert.doesNotMatch(probe,/spawn\(|randomBytes|rootSQL\(/);
  assert.match(probe,/row\.status==='created'/);assert.match(probe,/row\.pid===0/);assert.match(probe,/diagnostic\.agreement/);
  assert.match(probe,/retain-stage-for-container-recovery/);assert.match(probe,/engineOutputBytes<=65536/);
  assert.ok(probe.indexOf('requireFact(ownedIds(docker,owner).length===0,\'retain-stage-for-container-recovery\')')<probe.indexOf('cleanStage(stage,env.GITHUB_SHA,owner,uid,true)'));
  assert.match(flow,/workflow_dispatch:/);assert.match(flow,/if: always\(\)/);assert.doesNotMatch(flow,/^  (push|schedule|pull_request):/m);
  assert.doesNotMatch(flow,/source-policy-minimum-controller\.mjs|source-policy-minimum-qualification|WordPress\/WordPress|setup-php/);
  assert.ok(flow.indexOf('"$EXPECTED_SHA" == "$GITHUB_SHA"')<flow.indexOf('uses: actions/checkout@'));
});
