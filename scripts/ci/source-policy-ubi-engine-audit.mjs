// Read-only prerequisite only: no acquisition, import, containers or controls.
import { spawnSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { lstatSync, readFileSync, realpathSync } from 'node:fs';
import { arch, platform, release } from 'node:os';
import { join } from 'node:path';
import { pathToFileURL } from 'node:url';

const REPO='NichlasB/alynt-drime-backups-dashboard';
const WORKFLOW=`${REPO}/.github/workflows/source-policy-ubi-engine-audit.yml@refs/heads/master`;
export const SOURCES=Object.freeze(['scripts/ci/source-policy-ubi-engine-audit.mjs',
  '.github/workflows/source-policy-ubi-engine-audit.yml','scripts/manual/source_policy_ubi_runtime_contract.py']);
const COMMANDS=Object.freeze({
  context:['docker',['context','inspect','--format','{{.Endpoints.docker.Host}}']],
  version:['docker',['version','--format','{{json .Server}}']],
  info:['docker',['info','--format','{{json .}}']],
  head:['git',['rev-parse','HEAD']],
  source:['git',['diff','--no-ext-diff','--no-textconv','--quiet','HEAD','--',...SOURCES]],
});
const REASONS=new Set(['metadata-size','metadata-json','exact-read-only-job','checked-out-head','checked-out-source',
  'local-engine-only','engine-version','builtin-seccomp-unavailable','kernel-shape','source-readback',
  'read-only-command','bounded-read-only-command','regular-audit-source','fixed-audit-entry','frozen-source-changed','summary-size']);
function requireFact(value, reason){if(!value)throw new Error(reason);}
function text(value,pattern){return typeof value==='string'&&pattern.test(value);}
function parse(raw){requireFact(typeof raw==='string'&&Buffer.byteLength(raw)<=1048576,'metadata-size');try{return JSON.parse(raw);}catch{throw new Error('metadata-json');}}

export function admit(env,machine){
  requireFact(machine.platform==='linux'&&machine.arch==='x64'&&env.RUNNER_OS==='Linux'
    &&env.AUDIT_RUNNER_ENVIRONMENT==='github-hosted'&&env.GITHUB_ACTIONS==='true'
    &&env.GITHUB_EVENT_NAME==='workflow_dispatch'&&env.GITHUB_REPOSITORY===REPO
    &&env.GITHUB_REF==='refs/heads/master'&&env.GITHUB_WORKFLOW_REF===WORKFLOW
    &&text(env.GITHUB_SHA,/^[a-f0-9]{40}$/)&&env.EXPECTED_SHA===env.GITHUB_SHA
    &&text(env.GITHUB_RUN_ID,/^[1-9][0-9]{0,19}$/)&&text(env.GITHUB_RUN_ATTEMPT,/^[1-9][0-9]{0,5}$/)
    &&!Object.keys(env).some(key=>key.startsWith('DOCKER_')),'exact-read-only-job');
}

export function collect(env,machine,read){
  admit(env,machine); // No engine/source reads before fixed job admission.
  requireFact(read('head').trim()===env.GITHUB_SHA,'checked-out-head');
  requireFact(read('source')==='','checked-out-source');
  requireFact(read('context').trim()==='unix:///var/run/docker.sock','local-engine-only');
  const version=parse(read('version')),info=parse(read('info'));
  requireFact(version&&typeof version==='object'&&!Array.isArray(version)
    &&text(version.Version,/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/)
    &&text(version.ApiVersion,/^[0-9]{1,3}\.[0-9]{1,3}$/)
    &&text(version.GitCommit,/^[a-f0-9]{7,40}$/)&&version.Os==='linux'&&version.Arch==='amd64','engine-version');
  requireFact(info&&typeof info==='object'&&!Array.isArray(info)&&info.ServerVersion===version.Version
    &&info.OSType==='linux'&&info.Architecture==='x86_64'&&text(info.Driver,/^[a-z0-9_-]{1,32}$/)
    &&Array.isArray(info.SecurityOptions)&&info.SecurityOptions.length<=16
    &&info.SecurityOptions.every(item=>text(item,/^name=[a-z0-9_-]+(?:,profile=[a-z0-9_-]+)?$/)&&item.length<=96)
    &&info.SecurityOptions.includes('name=seccomp,profile=builtin'),'builtin-seccomp-unavailable');
  requireFact(text(machine.release,/^[a-zA-Z0-9.+_-]{1,96}$/),'kernel-shape');
  requireFact(read('head').trim()===env.GITHUB_SHA&&read('source')==='','source-readback');
  return {format:'ADBUENGINEAUDIT1',scope:'read-only-metadata',head:env.GITHUB_SHA,
    run:env.GITHUB_RUN_ID,attempt:env.GITHUB_RUN_ATTEMPT,
    engine:{version:version.Version,api:version.ApiVersion,commit:version.GitCommit,os:version.Os,arch:version.Arch,
      storage_driver:info.Driver,security_options:info.SecurityOptions.slice(),kernel:machine.release},
    default_policy_source_established:false,qualification:false,runtime_grant:false,
    candidate_execution:false,image_loading:false,database_operations:false,resources_created:0,
    cleanup:'not-required-no-resources-created'};
}

export function readCommand(key,env,spawn=spawnSync){
  requireFact(Object.hasOwn(COMMANDS,key),'read-only-command');
  const [file,args]=COMMANDS[key];
  const result=spawn(file,args.slice(),{encoding:'utf8',timeout:10000,maxBuffer:1048576,killSignal:'SIGKILL',
    input:'',shell:false,env:{PATH:env.PATH,HOME:env.HOME,LANG:'C.UTF-8'}});
  requireFact(!result.error&&!result.signal&&result.status===0&&typeof result.stdout==='string'
    &&result.stderr===''&&Buffer.byteLength(result.stdout)<=1048576,'bounded-read-only-command');
  return result.stdout;
}

function freezeSource(root){
  const rows=SOURCES.map(path=>{
    const full=join(root,path),stat=lstatSync(full);
    requireFact(stat.isFile()&&!stat.isSymbolicLink()&&stat.size<=131072&&realpathSync(full)===full,'regular-audit-source');
    return [path,createHash('sha256').update(readFileSync(full)).digest('hex')];
  });
  return createHash('sha256').update(JSON.stringify(rows)).digest('hex');
}

function main(){
  const env=process.env,machine={platform:platform(),arch:arch(),release:release()};
  admit(env,machine);
  requireFact(process.argv.length===3&&process.argv[2]==='--approved-read-only-engine-audit'
    &&process.cwd()===env.GITHUB_WORKSPACE,'fixed-audit-entry');
  const frozen=freezeSource(process.cwd());
  const summary=collect(env,machine,key=>readCommand(key,env));
  requireFact(freezeSource(process.cwd())===frozen,'frozen-source-changed');
  summary.audit_source_sha=frozen;
  const output=JSON.stringify(summary);requireFact(Buffer.byteLength(output)<=65536,'summary-size');
  process.stdout.write(output+'\n');
}
if(process.argv[1]&&import.meta.url===pathToFileURL(process.argv[1]).href){
  try{main();}catch(error){
    const reason=error instanceof Error&&REASONS.has(error.message)?error.message:'unclassified-audit-failure';
    process.stderr.write(JSON.stringify({status:'read-only-engine-audit-refused',reason,details_withheld:true})+'\n');process.exitCode=1;
  }
}
