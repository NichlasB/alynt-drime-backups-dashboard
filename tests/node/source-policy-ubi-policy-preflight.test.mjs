import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {collectPolicy,parseRunc,parseKernelConfig,readCommand,SOURCES_V2} from '../../scripts/ci/source-policy-ubi-engine-audit.mjs';
const env={GITHUB_ACTIONS:'true',GITHUB_EVENT_NAME:'workflow_dispatch',GITHUB_REPOSITORY:'NichlasB/alynt-drime-backups-dashboard',
  GITHUB_REF:'refs/heads/master',GITHUB_WORKFLOW_REF:'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-ubi-engine-audit.yml@refs/heads/master',
  GITHUB_SHA:'a'.repeat(40),EXPECTED_SHA:'a'.repeat(40),GITHUB_RUN_ID:'123',GITHUB_RUN_ATTEMPT:'1',RUNNER_OS:'Linux',AUDIT_RUNNER_ENVIRONMENT:'github-hosted'};
const machine={platform:'linux',arch:'x64',release:'7.0.0-test'};
const runc='runc version 1.3.0\ncommit: v1.3.0-0-g123abc\nspec: 1.2.1\ngo: go1.24.1\nlibseccomp: 2.5.5\n';
const server={Version:'28.0.4',ApiVersion:'1.48',GitCommit:'6430e49',Os:'linux',Arch:'amd64',Components:[{Name:'runc',Version:'1.3.0',Details:{GitCommit:'v1.3.0-0-g123abc'}}]};
const client={Version:'28.0.4',GitCommit:'b8034c0',Os:'linux',Arch:'amd64'};
const info={ServerVersion:'28.0.4',OSType:'linux',Architecture:'x86_64',Driver:'overlay2',SecurityOptions:['name=seccomp,profile=builtin'],DefaultRuntime:'runc',Runtimes:{runc:{path:'runc'}}};
const missing=()=>({status:'unavailable',reason:'missing',boot_correspondence:false});
function reads(overrides={},calls=[]){
  const values={head:env.GITHUB_SHA,source:'','source-v2':'',context:'unix:///var/run/docker.sock',version:JSON.stringify(server),
    'version-full':JSON.stringify({Client:client,Server:server}),info:JSON.stringify(info),'runc-version':runc,...overrides};
  return key=>{calls.push(key);return values[key];};
}
test('positive prerequisite metadata binds source-selected client/runtime without making kernel or ABI claims',()=>{
  const result=collectPolicy(env,machine,reads(),missing);
  assert.equal(result.format,'ADBUENGINEAUDIT2');assert.equal(result.client.commit,'b8034c0');
  assert.equal(result.runtime.libseccomp,'2.5.5');assert.equal(result.runtime.loaded_binary_attested,false);
  assert.equal(result.kernel_config.status,'unavailable');assert.equal(result.abi_coverage,'unestablished');
  assert.equal(result.policy_application,false);assert.equal(result.qualification,false);assert.equal(result.resources_created,0);
  assert.equal(SOURCES_V2.length,5);
});
test('foreign admission/source/client/runtime must refuse before kernel metadata reads',()=>{
  assert.throws(()=>collectPolicy({...env,GITHUB_EVENT_NAME:'push'},machine,()=>assert.fail('protected read'),missing),/exact-read-only-job/);
  for(const change of [{'source-v2':'dirty'}, {'version-full':JSON.stringify({Client:{...client,GitCommit:'deadbee'},Server:server})},
    {info:JSON.stringify({...info,DefaultRuntime:'foreign'})}, {'runc-version':runc.replace('v1.3.0-0-g123abc','foreign')}]){
    assert.throws(()=>collectPolicy(env,machine,reads(change),()=>assert.fail('protected kernel read')),/policy-source|policy-client|policy-runtime/);
  }
});
test('malformed runc/duplicate fields/unknown diagnostics cannot become an admitted metadata packet',()=>{
  assert.equal(parseRunc(runc).version,'1.3.0');
  for(const raw of [runc+'warning\n',runc.replace('libseccomp: 2.5.5','commit: repeated'),runc.replace('2.5.5','secret value'),runc.replace('runc version','unknown'), 'x'.repeat(8193)]){
    assert.throws(()=>parseRunc(raw),/runc-metadata/);
  }
});
test('kernel config projection preserves missing/ambiguous states and never treats a file as boot attestation',()=>{
  const result=parseKernelConfig(Buffer.from('CONFIG_IA32_EMULATION=y\n# CONFIG_X86_X32_ABI is not set\nSECRET=never\n'));
  assert.deepEqual(result.settings,{CONFIG_IA32_EMULATION:'y',CONFIG_X86_X32_ABI:'n'});assert.equal(result.boot_correspondence,false);
  assert.ok(!JSON.stringify(result).includes('never'));
  const ambiguous=parseKernelConfig(Buffer.from('CONFIG_IA32_EMULATION=y\nCONFIG_IA32_EMULATION=n\n'));
  assert.equal(ambiguous.settings.CONFIG_IA32_EMULATION,'unestablished');assert.equal(ambiguous.settings.CONFIG_X86_X32_ABI,'unestablished');
  assert.throws(()=>parseKernelConfig(Buffer.alloc(1048577)),/kernel-config-size/);
});
test('new subprocess key is only the fixed existing runc version command, never arbitrary native arguments',()=>{
  assert.equal(readCommand('runc-version',{},(file,args,options)=>{
    assert.equal(file,'/usr/bin/runc');assert.deepEqual(args,['--version']);assert.equal(options.input,'');assert.equal(options.shell,false);
    return {status:0,stdout:runc,stderr:''};
  }),runc);
  assert.throws(()=>readCommand('runc-exec',{},()=>assert.fail('spawn')),/read-only-command/);
});
test('prerequisite mode is explicitly false by default and cannot invoke profile/candidate controllers',()=>{
  const workflow=readFileSync(new URL('../../.github/workflows/source-policy-ubi-engine-audit.yml',import.meta.url),'utf8');
  assert.match(workflow,/policy_preflight:[\s\S]*?default: false/);
  assert.ok(workflow.includes('--approved-read-only-policy-preflight'));assert.ok(workflow.includes('*) exit 1'));
  assert.ok(!workflow.includes('source-policy-minimum-controller'));assert.ok(!workflow.includes('source-policy-legacy-ubi-static.py'));
});
