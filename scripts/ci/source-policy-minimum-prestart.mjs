// One approved unstarted-container readback only. No server, candidate PHP, SQL or credentials.
import {spawnSync} from 'node:child_process';
import {createHash} from 'node:crypto';
import {existsSync,lstatSync,mkdirSync,readFileSync,readdirSync,realpathSync,rmdirSync,unlinkSync,writeFileSync} from 'node:fs';
import {dirname,join} from 'node:path';
import {fileURLToPath} from 'node:url';
import {imageProfile,MODEL,selections} from './source-policy-minimum-controller.mjs';
import {serverConfigurationDiagnostics} from './source-policy-minimum-configuration-diagnostics.mjs';

const fixed=selections.server,contract='minimum-prestart-readback-v1';
const cidPattern=/^[a-f0-9]{64}$/;
const ownerPattern=/^adb-minimum-prestart-[0-9]+-[0-9]+$/;
const requireFact=(ok,code)=>{if(!ok){const error=Error(code);error.prestartReason=code;throw error;}};
const hash=bytes=>createHash('sha256').update(bytes).digest('hex');
// Minimal exact projection: never request Config.Env, command payloads or whole inspect objects.
const projection='{"entry":{{json .Config.Entrypoint}},"image":{{json .Image}},"label":{{json (index .Config.Labels "com.alynt.minimum.prestart")}},"user":{{json .Config.User}},"ro":{{json .HostConfig.ReadonlyRootfs}},"network":{{json .HostConfig.NetworkMode}},"memory":{{json .HostConfig.Memory}},"cpu":{{json .HostConfig.NanoCpus}},"pids":{{json .HostConfig.PidsLimit}},"privileged":{{json .HostConfig.Privileged}},"ports":{{json .HostConfig.PortBindings}},"caps":{{json .HostConfig.CapDrop}},"security":{{json .HostConfig.SecurityOpt}},"devices":{{json .HostConfig.Devices}},"tmpfs":{{json .HostConfig.Tmpfs}},"mounts":{{json .Mounts}},"status":{{json .State.Status}},"running":{{json .State.Running}},"pid":{{json .State.Pid}},"restarts":{{json .RestartCount}}}';
const imageProjection='{"id":{{json .Id}},"os":{{json .Os}},"arch":{{json .Architecture}},"volumes":{{json .Config.Volumes}},"entry":{{json .Config.Entrypoint}},"cmd":{{json .Config.Cmd}}}';

export function prestartCreateArgs(owner,uid) {
  requireFact(ownerPattern.test(owner)&&Number.isSafeInteger(uid)&&uid>0&&uid<=2147483647,'fixture-identity');
  return ['create','--pull','never','--name',owner,'--read-only','--user',uid+':'+uid,'--cap-drop','ALL','--security-opt','no-new-privileges','--cpus','1','--no-healthcheck',
    '--label','com.alynt.minimum.prestart='+owner,'--network','none','--memory','512m','--pids-limit','64',
    '--tmpfs',`/var/lib/mysql:rw,nosuid,nodev,noexec,size=256m,mode=700,uid=${uid},gid=${uid}`,
    '--tmpfs',`/tmp:rw,nosuid,nodev,noexec,size=16m,mode=700,uid=${uid},gid=${uid}`,
    '--entrypoint','/bin/sh',fixed.ref,'-ceu','exit 99']; // Inert Cmd; none of the admission predicates inspects Cmd.
}
function ownedIds(docker,owner) {
  requireFact(ownerPattern.test(owner),'owner-name');
  const raw=docker(['ps','--all','--quiet','--no-trunc','--filter','label=com.alynt.minimum.prestart='+owner]);
  const ids=raw.trim()?raw.trim().split('\n'):[];
  requireFact(ids.length<=1&&ids.every(id=>cidPattern.test(id)),'owner-membership');return ids;
}
function snapshot(docker,cid) {requireFact(cidPattern.test(cid),'container-id');return JSON.parse(docker(['inspect','--format',projection,cid]));}
function unstarted(row,owner,uid) {
  return row&&row.label===owner&&row.image===fixed.config&&row.user===uid+':'+uid
    &&row.status==='created'&&row.running===false&&row.pid===0&&row.restarts===0;
}
export function cleanupPrestartServer(docker,owner,uid) {
  const ids=ownedIds(docker,owner);
  for(const cid of ids) {
    requireFact(unstarted(snapshot(docker,cid),owner,uid),'cleanup-not-owned-unstarted');
    docker(['rm','--volumes',cid]); // No force, stop, kill, exec, start or process operation.
  }
  requireFact(ownedIds(docker,owner).length===0,'cleanup-container-readback');
  return {containers:0,removed:ids.length,server_started:false,php_executed:false,sql_executed:false};
}
export function probePrestartServer(docker,owner,uid,stage,observe=()=>{}) {
  prestartCreateArgs(owner,uid);
  requireFact(ownedIds(docker,owner).length===0,'fresh-container-namespace');
  let observation,cleanup;
  try {
    docker(['pull','--platform','linux/amd64',fixed.ref]);
    const image=JSON.parse(docker(['image','inspect','--format',imageProjection,fixed.ref]));
    requireFact(imageProfile(image,'server'),'pinned-server-image');
    const cid=docker(prestartCreateArgs(owner,uid)).trim();
    const row=snapshot(docker,cid);
    const diagnostic=serverConfigurationDiagnostics(row,owner,uid,stage);
    observation={container:cid,unstarted:unstarted(row,owner,uid),diagnostic};
    observe(observation); // Bounded diagnostic survives a subsequent cleanup refusal; never a success terminal.
    requireFact(observation.unstarted,'created-state-readback');
    requireFact(diagnostic.agreement,'diagnostic-admission-disagreement');
    requireFact(JSON.stringify(snapshot(docker,cid))===JSON.stringify(row),'stable-unstarted-readback');
    requireFact(JSON.stringify(ownedIds(docker,owner))===JSON.stringify([cid]),'exact-created-membership');
  } finally {cleanup=cleanupPrestartServer(docker,owner,uid);}
  return {format:'ADBMPC1',...observation,cleanup:{...cleanup,unstarted:true},
    server_started:false,php_executed:false,sql_executed:false,accounts_created:0,runtime_grant:false,production_ready:false,native_qualification:false};
}

let engineOutputBytes=0;
function docker(args) {
  requireFact(['context','ps','pull','image','create','inspect','rm'].includes(args[0])&&!args.includes('--force')&&!args.includes('--privileged'),'forbidden-engine-verb');
  const result=spawnSync('docker',args,{encoding:'utf8',timeout:args[0]==='pull'?120000:10000,maxBuffer:16384,killSignal:'SIGKILL',input:''});
  requireFact(!result.error&&!result.signal&&result.status===0&&Buffer.byteLength(result.stdout)+Buffer.byteLength(result.stderr)<=16384,'bounded-engine-command');
  engineOutputBytes+=Buffer.byteLength(result.stdout)+Buffer.byteLength(result.stderr);
  requireFact(engineOutputBytes<=65536,'engine-phase-output-bound');
  requireFact(args[0]==='pull'||result.stderr==='','unexpected-engine-diagnostic');return result.stdout;
}
function sourceIdentity() {
  const files=['scripts/ci/source-policy-minimum-controller.mjs','scripts/ci/source-policy-linux-qualification-controller.mjs',
    'scripts/ci/source-policy-minimum-configuration-diagnostics.mjs','scripts/ci/source-policy-minimum-prestart.mjs','.github/workflows/source-policy-minimum-prestart.yml'];
  return hash(files.map(file=>{const stat=lstatSync(file);requireFact(stat.isFile()&&!stat.isSymbolicLink()&&stat.size<=65536,'finite-source-file');return hash(readFileSync(file))+'  '+file;}).join('\n')+'\n');
}
function stageLease(stage,head,owner,uid,allowEmpty=false) {
  const info=lstatSync(stage);
  requireFact(info.isDirectory()&&!info.isSymbolicLink()&&info.uid===uid&&realpathSync(stage)===stage&&(info.mode&0o777)===0o700,'stage-ownership');
  const names=readdirSync(stage);
  requireFact(names.length<=1&&names.every(name=>name==='source.sha256')&&(allowEmpty||names.length===1),'stage-membership');
  if(names.length) {
    const marker=join(stage,'source.sha256'),stat=lstatSync(marker);
    requireFact(stat.isFile()&&!stat.isSymbolicLink()&&stat.nlink===1&&stat.uid===uid&&stat.size<=256,'marker-ownership');
    const wire=readFileSync(marker,'utf8');
    requireFact(new RegExp('^ADBMPS1\\|'+head+'\\|[a-f0-9]{64}\\|'+owner+'\\|'+uid+'\\n(?![\\s\\S])').test(wire),'marker-identity');
    return wire;
  }
  return null;
}
function cleanStage(stage,head,owner,uid,allowEmpty=false) {
  if(!existsSync(stage))return;
  if(stageLease(stage,head,owner,uid,allowEmpty)!==null)unlinkSync(join(stage,'source.sha256'));
  rmdirSync(stage);requireFact(!existsSync(stage),'stage-removal-readback');
}
function main() {
  const env=process.env;
  requireFact(process.platform==='linux'&&process.arch==='x64'&&env.RUNNER_OS==='Linux'&&env.GITHUB_ACTIONS==='true'&&env.GITHUB_EVENT_NAME==='workflow_dispatch'
    &&env.GITHUB_REPOSITORY==='NichlasB/alynt-drime-backups-dashboard'&&env.GITHUB_REF==='refs/heads/master'
    &&env.GITHUB_WORKFLOW_REF==='NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-minimum-prestart.yml@refs/heads/master'
    &&/^[a-f0-9]{40}$/.test(env.GITHUB_SHA??'')&&env.GITHUB_SHA===env.EXPECTED_SHA&&env.MINIMUM_MODEL===MODEL&&env.PRESTART_CONTRACT===contract
    &&/^[0-9]+$/.test(env.GITHUB_RUN_ID??'')&&/^[0-9]+$/.test(env.GITHUB_RUN_ATTEMPT??'')&&!env.DOCKER_HOST&&!env.DOCKER_CONTEXT,'exact-prestart-job');
  const uid=process.getuid();requireFact(uid>0&&uid===process.getgid()&&process.cwd()===env.GITHUB_WORKSPACE,'nonroot-fixed-workspace');
  requireFact(docker(['context','inspect','--format','{{.Endpoints.docker.Host}}']).trim()==='unix:///var/run/docker.sock','local-trusted-engine');
  const parent=realpathSync(env.RUNNER_TEMP),owner='adb-minimum-prestart-'+env.GITHUB_RUN_ID+'-'+env.GITHUB_RUN_ATTEMPT,stage=join(parent,owner);
  requireFact(dirname(stage)===parent,'exact-stage-child');
  if(process.argv.length===3&&process.argv[2]==='--cleanup') {
    if(!existsSync(stage))requireFact(ownedIds(docker,owner).length===0,'missing-stage-lease');
    else { // Verify the lease without removing it before container ownership readback.
      const wire=stageLease(stage,env.GITHUB_SHA,owner,uid);
      requireFact(wire===`ADBMPS1|${env.GITHUB_SHA}|${sourceIdentity()}|${owner}|${uid}\n`,'recovery-marker');
      cleanupPrestartServer(docker,owner,uid);cleanStage(stage,env.GITHUB_SHA,owner,uid);
    }
    process.stdout.write('ADBMPC_CLEANUP containers:0 stage:0 server_started:false php_executed:false sql_executed:false\n');return;
  }
  requireFact(process.argv.length===2&&!existsSync(stage)&&ownedIds(docker,owner).length===0,'fresh-stage-and-namespace');
  const git=spawnSync('git',['rev-parse','HEAD'],{encoding:'utf8',timeout:5000});requireFact(git.status===0&&git.stdout.trim()===env.GITHUB_SHA,'exact-source-head');
  const source=sourceIdentity();mkdirSync(stage,{mode:0o700});let report;
  try {
    writeFileSync(join(stage,'source.sha256'),`ADBMPS1|${env.GITHUB_SHA}|${source}|${owner}|${uid}\n`,{mode:0o600,flag:'wx'});
    report=probePrestartServer(docker,owner,uid,stage,observation=>{
      const wire=JSON.stringify({format:'ADBMPC_OBSERVATION',head:env.GITHUB_SHA,source,...observation})+'\n';
      requireFact(Buffer.byteLength(wire)<=16384,'bounded-observation');process.stdout.write(wire);
    });
    requireFact(sourceIdentity()===source,'source-readback');
  } finally {
    requireFact(ownedIds(docker,owner).length===0,'retain-stage-for-container-recovery');
    cleanStage(stage,env.GITHUB_SHA,owner,uid,true);
  }
  const result={...report,head:env.GITHUB_SHA,run:env.GITHUB_RUN_ID,attempt:env.GITHUB_RUN_ATTEMPT,source,evidence_model:MODEL,stage_removed:true,engine_output_bytes:engineOutputBytes};
  const wire=JSON.stringify(result)+'\n';requireFact(Buffer.byteLength(wire)<=16384,'bounded-record');process.stdout.write(wire);
}
if(process.argv[1]===fileURLToPath(import.meta.url)) {
  try{main();}catch(error){process.stderr.write('ADBMPC_REFUSED|'+(typeof error.prestartReason==='string'&&/^[a-z-]{1,64}$/.test(error.prestartReason)?error.prestartReason:'unclassified-host-failure')+'\n');process.exitCode=1;}
}
