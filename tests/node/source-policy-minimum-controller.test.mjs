import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {terminal,imageProfile,containerProfile,selections,failureEvidence} from '../../scripts/ci/source-policy-minimum-controller.mjs';
const run='a'.repeat(32),image='b'.repeat(64),cid='c'.repeat(64),modules='d'.repeat(64);
const good=`ADBMC1|${run}|${image}|${cid}|1|${modules}|120|9|0|closed|trusted-lab-functional-v1|false|not-established|false|false\n`;
test('fixed positive terminal and every field tamper are discriminating',()=>{
  assert.equal(terminal(good,run,image,cid),true);
  const fields=good.trimEnd().split('|');
  for(let i=0;i<fields.length;i++){const bad=[...fields];bad[i]='other';assert.equal(terminal(bad.join('|')+'\n',run,image,cid),false,'field '+i);}
  for(const bad of [good+'\n',good+'noise',good.replace(/\n$/,''),good.replace('|120|','|0|'),good.replace('|120|','|100000|'),good.replace('|false|not-established|','|true|established|')])assert.equal(terminal(bad,run,image,cid),false);
  assert.equal(terminal(good,run,image,'e'.repeat(64)),false);
});
for(const role of ['php','server']) {
  test(role+' exact image/config pins and volumes',()=>{
    const row={id:selections[role].config,os:'linux',arch:'amd64',volumes:{[selections[role].volume]:{}},entry:['docker-entrypoint.sh'],cmd:[role==='php'?'php-fpm':'mariadbd']};
    assert.equal(imageProfile(row,role),true);
    for(const [key,value]of Object.entries({id:'sha256:'+image,os:'windows',arch:'arm64',volumes:{'/extra':{}},entry:['/bin/sh'],cmd:['other']}))assert.equal(imageProfile({...row,[key]:value},role),false,key);
  });
  test(role+' resource/topology/source profile refuses expanded authority',()=>{
    const php=role==='php',uid=1001,stage='/tmp/owned',scratch=php?'/var/www/html':'/var/lib/mysql';
    const row={entry:[php?'/usr/local/bin/php':'/bin/sh'],workdir:php?'/proof':'',image:selections[role].config,label:'owned',user:'1001:1001',ro:true,network:php?'container:'+cid:'none',memory:php?134217728:536870912,cpu:1000000000,pids:php?16:64,privileged:false,ports:null,caps:['ALL'],security:['no-new-privileges'],devices:null,
      tmpfs:{[scratch]:`rw,nosuid,nodev,noexec,size=${php?'4k':'256m'},mode=700,uid=1001,gid=1001`,'/tmp':`rw,nosuid,nodev,noexec,size=${php?'4m':'16m'},mode=700,uid=1001,gid=1001`},
      mounts:php?[{Type:'bind',Destination:'/proof',Source:stage+'/source',RW:false}]:[]};
    assert.equal(containerProfile(row,role,'owned',selections[role].config,cid,uid,stage),true);
    for(const [key,value]of Object.entries({image:'wrong',label:'foreign',user:'0:0',ro:false,network:'host',memory:999999999,cpu:2000000000,pids:100,privileged:true,ports:{3306:[]},caps:[],security:[],devices:[{}],tmpfs:{'/extra':'rw'},mounts:[{Type:'volume',Destination:'/var/www/html',RW:true}]}))assert.equal(containerProfile({...row,[key]:value},role,'owned',selections[role].config,cid,uid,stage),false,key);
  });
}
test('worker/controller/workflow closure: source-backed prohibitions and actual paths',()=>{
  const root=new URL('../../',import.meta.url),read=path=>readFileSync(new URL(path,root),'utf8');
  const worker=read('scripts/ci/source-policy-minimum-qualification.php'),op=read('scripts/ci/source-policy-minimum-native-operation.php'),controller=read('scripts/ci/source-policy-minimum-controller.mjs'),flow=read('.github/workflows/source-policy-minimum-qualification.yml');
  assert.match(flow,/workflow_dispatch:/);assert.doesNotMatch(flow,/^  (push|pull_request|schedule):/m);
  assert.ok(flow.indexOf('Admit exact approved job')<flow.indexOf('uses: actions/checkout@'));
  assert.ok(flow.indexOf('"$EXPECTED_SHA" == "$GITHUB_SHA"')<flow.indexOf('Checkout genuine pinned core'));
  assert.match(flow,/cc101b64012b16d087780657a2b828ccd7794a63/);assert.match(flow,/if: always\(\)/);
  for(const token of ["'7.4.33'",'mysqlnd 7.4.33',"'core'      => '6.0'",'adb0_cases();','genuine owned quote/backslash/NUL/percent/utf8mb4 round trip','20000000000'])assert.ok(worker.includes(token),token);
  assert.doesNotMatch(worker,/json_encode\(|json_decode\(|mysqli_report\(/);
  assert.match(op,/minimum_lab_scope/);assert.doesNotMatch(op,/bind\( \$owner, '(ready|dbh|qualification)'/);
  assert.match(controller,/reconcile\(serverCid\)/);assert.match(controller,/MINIMUM_MODEL===MODEL/);assert.match(controller,/--env-file/);
  assert.doesNotMatch(controller,/--privileged|--network','host|docker.sock:/);
});
test('only finite worker failure evidence is exposed, never raw errors or secrets',()=>{
  assert.deepEqual(failureEvidence('ADBMF1|positive|native|120|1\n'),{stage:'positive',phase:'native',checks:120,groups:1});
  for(const raw of ['',null,'secret password\n','ADBMF1|unknown|native|120|1\n','ADBMF1|positive|raw-path|120|1\n','ADBMF1|positive|native|100000|1\n','ADBMF1|positive|native|120|10\n','ADBMF1|positive|native|120|1\n\n'])assert.equal(failureEvidence(raw),null);
});
