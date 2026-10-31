// Dormant exact-head manual job only. Importing pure contracts performs no IO.
import {spawn, spawnSync} from 'node:child_process';
import {createHash, randomBytes} from 'node:crypto';
import {readFileSync, writeFileSync, mkdirSync, readdirSync, lstatSync, realpathSync, existsSync, rmSync, copyFileSync} from 'node:fs';
import {join, dirname, relative} from 'node:path';
import {fileURLToPath} from 'node:url';
import {reviewLog} from './source-policy-linux-qualification-controller.mjs';
import {AUDIT_WORKFLOW,executionPhases,failureEvidence,releaseIssues} from './source-policy-minimum-audit-contract.mjs';
export {failureEvidence} from './source-policy-minimum-audit-contract.mjs';

export const MODEL='trusted-lab-functional-v1';
export const selections=Object.freeze({
  php: {ref:'wordpress@sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4', config:'sha256:de3b9412d904e00792e7a1504f6bc38c17f44451c18bf75515b97e5f9d0bfe18', volume:'/var/www/html'},
  server: {ref:'mariadb@sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5', config:'sha256:b5077e20185da2defdc68763c9f87e9ce6c449207f9e8de120dee346b54ce696', volume:'/var/lib/mysql'}
});
const hash=bytes=>createHash('sha256').update(bytes).digest('hex');
const requireFact=(ok,label)=>{if(!ok){const error=Error(label);error.minimumReason=label;throw error;}};
export function terminal(wire,run,image,cid) {
  return typeof wire==='string' && typeof run==='string' && typeof image==='string' && typeof cid==='string' && /^[a-f0-9]{32}$/.test(run) && /^[a-f0-9]{64}$/.test(image) && /^[a-f0-9]{64}$/.test(cid)
    && new RegExp('^ADBMC1\\|'+run+'\\|'+image+'\\|'+cid+'\\|1\\|[a-f0-9]{64}\\|[1-9][0-9]{0,4}\\|9\\|0\\|closed\\|trusted-lab-functional-v1\\|false\\|not-established\\|false\\|false\\n(?![\\s\\S])').test(wire);
}
export function imageProfile(row,role) {
  const fixed=selections[role];
  return Boolean(fixed&&row&&row.id===fixed.config&&row.os==='linux'&&row.arch==='amd64'
    && JSON.stringify(row.volumes)===JSON.stringify({[fixed.volume]:{}})
    && JSON.stringify(row.entry)===JSON.stringify(['docker-entrypoint.sh'])
    && JSON.stringify(row.cmd)===JSON.stringify([role==='php'?'php-fpm':'mariadbd']));
}
// Approved representation compatibility only: no keys of any kind, no arrays/coercion/custom prototypes.
export function emptyPortBindings(value) {
  return value===null||(typeof value==='object'&&!Array.isArray(value)&&Object.getPrototypeOf(value)===Object.prototype&&Reflect.ownKeys(value).length===0);
}
export function containerProfile(row,role,owner,image,serverCid,uid,stage) {
  const php=role==='php', scratch=php?'/var/www/html':'/var/lib/mysql';
  return Boolean(row&&row.image===image&&row.label===owner&&row.user===uid+':'+uid&&row.ro===true
    && JSON.stringify(row.entry)===JSON.stringify([php?'/usr/local/bin/php':'/bin/sh'])&&(!php||row.workdir==='/proof')
    && row.network===(php?'container:'+serverCid:'none')&&row.memory===(php?134217728:536870912)
    && row.cpu===1000000000&&row.pids===(php?16:64)&&row.privileged===false&&emptyPortBindings(row.ports)
    && JSON.stringify(row.caps)===JSON.stringify(['ALL'])&&Array.isArray(row.security)&&row.security.includes('no-new-privileges')&&row.tmpfs&&Array.isArray(row.mounts)
    && (row.devices??[]).length===0&&Object.keys(row.tmpfs).sort().join(',')===[scratch,'/tmp'].sort().join(',')
    && row.tmpfs[scratch]===`${scratch==='\/var/www/html'?'rw,nosuid,nodev,noexec,size=4k':'rw,nosuid,nodev,noexec,size=256m'},mode=700,uid=${uid},gid=${uid}`
    && row.tmpfs['/tmp']===`rw,nosuid,nodev,noexec,size=${php?'4m':'16m'},mode=700,uid=${uid},gid=${uid}`
    && row.mounts.filter(m=>m.Type==='bind').length===(php?1:0)
    && (!php||row.mounts.some(m=>m.Destination==='/proof'&&m.Source===stage+'/source'&&m.RW===false))
    && row.mounts.every(m=>m.Type==='tmpfs'||(php&&m.Type==='bind'&&m.Destination==='/proof'&&m.Source===stage+'/source'&&m.RW===false)));
}
// Never print raw Docker diagnostics/inspect/Env, which can contain private payloads.
function command(args,input='',timeout=10000,limit=16384) {
  const r=spawnSync('docker',args,{input,encoding:'utf8',timeout,maxBuffer:limit,killSignal:'SIGKILL'});
  requireFact(!r.error&&!r.signal&&r.status===0&&Buffer.byteLength(r.stdout)+Buffer.byteLength(r.stderr)<=limit,'bounded engine command refused');
  if(args[0]==='logs')return r.stdout+r.stderr;
  requireFact(args[0]==='pull'||r.stderr==='','unexpected engine/client diagnostic');
  return r.stdout;
}
function inspect(cid) {
  return JSON.parse(command(['inspect','--format','{"entry":{{json .Config.Entrypoint}},"workdir":{{json .Config.WorkingDir}},"image":{{json .Image}},"label":{{json (index .Config.Labels "com.alynt.minimum")}},"user":{{json .Config.User}},"ro":{{json .HostConfig.ReadonlyRootfs}},"network":{{json .HostConfig.NetworkMode}},"memory":{{json .HostConfig.Memory}},"cpu":{{json .HostConfig.NanoCpus}},"pids":{{json .HostConfig.PidsLimit}},"privileged":{{json .HostConfig.Privileged}},"ports":{{json .HostConfig.PortBindings}},"caps":{{json .HostConfig.CapDrop}},"security":{{json .HostConfig.SecurityOpt}},"devices":{{json .HostConfig.Devices}},"tmpfs":{{json .HostConfig.Tmpfs}},"mounts":{{json .Mounts}},"pid":{{json .State.Pid}},"started":{{json .State.StartedAt}},"running":{{json .State.Running}},"exit":{{json .State.ExitCode}},"oom":{{json .State.OOMKilled}},"hostname":{{json .Config.Hostname}}}',cid]));
}
function rootSQL(cid,sql) {
  return command(['exec','--interactive',cid,'mariadb','--no-defaults','--protocol=SOCKET','--socket=/tmp/minimum.sock','--user=root','--skip-reconnect','--connect-timeout=2','--batch','--raw','--skip-column-names'],sql,5000,4096);
}
const reconcile=cid=>requireFact(rootSQL(cid,'SELECT (SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);')==='0\t0\n','independent all-server release refused');
function ownedIds(owner) {
  const wire=command(['ps','--all','--no-trunc','--quiet','--filter','label=com.alynt.minimum='+owner]);
  const ids=wire.trim()?wire.trim().split('\n'):[];
  requireFact(ids.length<=2&&ids.every(id=>/^[a-f0-9]{64}$/.test(id)),'unknown owned container set');return ids;
}
function cleanup(owner,stage,auditOnly=false) {
  const issues=new Set();let containersRemoved=false,stageRemoved=false,operation='owned-inventory';
  try {
  const ids=ownedIds(owner).sort((a,b)=>Number(inspect(a).network==='none')-Number(inspect(b).network==='none'));
  for(const cid of ids) {
    operation='ownership-readback';
    const row=inspect(cid);requireFact(row.label===owner,'cleanup ownership changed');
    const role=row.network==='none'?'server':'php';
    if(row.running&&row.network==='none') {
      let category='server-reconciliation';
      try {
        reconcile(cid);category=auditOnly?'audit-absence-readback':'fixture-cleanup-readback';
        const removal=auditOnly?'':"DROP DATABASE IF EXISTS adb_minimum; DROP USER IF EXISTS 'adbm'@'127.0.0.1'; ";
        requireFact(rootSQL(cid,removal+"SELECT (SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME NOT IN ('mysql','information_schema','performance_schema','sys')),(SELECT COUNT(*) FROM mysql.global_priv WHERE User='adbm');")==='0\t0\n','fixture cleanup readback');
      }catch{issues.add(category);}
    }
    operation=role+'-stop';if(row.running)command(['stop','--time','5',cid],'',8000);
    operation=role+'-exit-readback';
    const ended=inspect(cid);for(const issue of releaseIssues(role,ended))issues.add(issue);
    if(row.network==='none')try{reviewLog(command(['logs',cid],'',3000,16384),ended.hostname);}catch{issues.add('server-log-review');}
    operation=role+'-remove';command(['rm','--volumes',cid]);
  }
  operation='container-absence-readback';
  requireFact(ownedIds(owner).length===0,'container cleanup incomplete');
  containersRemoved=true;
  if(existsSync(stage)) {
    operation='stage-ownership-readback';
    requireFact(realpathSync(stage)===stage&&!lstatSync(stage).isSymbolicLink()&&lstatSync(stage).uid===process.getuid(),'stage ownership changed');
    operation='stage-remove';rmSync(stage,{recursive:true});requireFact(!existsSync(stage),'stage cleanup incomplete');
  }
  stageRemoved=!existsSync(stage);
  } catch(error) {issues.add(operation);throw error;}
  finally {process.stdout.write(JSON.stringify({minimum_cleanup:{scope:auditOnly?'audit-only':'qualification',issues:[...issues],containers_removed:containersRemoved,stage_removed:stageRemoved,strict_release_complete:issues.size===0&&containersRemoved&&stageRemoved}})+'\n');}
  requireFact(issues.size===0,'cleanup/release evidence incomplete');
}
async function worker(cid,run,image,serverCid,proof,modules=null) {
  const child=spawn('docker',['start','--attach','--interactive',cid],{stdio:['pipe','pipe','pipe']});
  let out='',err='',accepted=false,failure=null;
  const deadline=setTimeout(()=>{failure=Error('fixed child deadline');child.kill('SIGKILL');},25000);
  child.stdin.on('error',()=>{failure=Error('private acknowledgement failed');});
  child.stdout.on('data',bytes=>{
    out+=bytes.toString('utf8');
    if(Buffer.byteLength(out)>16384){failure=Error('worker output bound');child.kill('SIGKILL');return;}
    if(out.includes('\n')&&!accepted) {
      try {
        const identity=inspect(cid);requireFact(identity.running&&Number.isSafeInteger(identity.pid)&&identity.pid>0&&/^\d{4}-\d\d-\d\dT/.test(identity.started),'engine host PID/start identity unavailable');
        requireFact(proof?terminal(out,run,image,serverCid):new RegExp('^ADBMA1\\|'+run+'\\|'+image+'\\|[a-f0-9]{64}\\n$').test(out),'held terminal refused');
        if(proof){requireFact(out.split('|')[5]===modules,'audit/proof inventory mismatch');reconcile(serverCid);}
        process.stdout.write(JSON.stringify({phase:proof?'closed-proof':'connection-free-audit',head:process.env.GITHUB_SHA,image,run,worker_cid:cid,server_cid:serverCid,engine_host_pid:identity.pid,engine_started:identity.started,namespace_pid:1,evidence_model:MODEL,independent_loaded_object_attestation:false,mapping_consistency:'not-established',runtime_grant:false,production_ready:false})+'\n');
        accepted=true;child.stdin.end('ACK:'+run+':'+image+'\n');
      }
      catch(error){failure=error;child.kill('SIGKILL');}
    }
  });
  child.stderr.on('data',bytes=>{err+=bytes.toString('utf8');if(Buffer.byteLength(err)>16384){failure=Error('worker diagnostic bound');child.kill('SIGKILL');}});
  await new Promise((resolve,reject)=>{child.on('error',reject);child.on('close',(code,signal)=>{clearTimeout(deadline);if(failure||signal||code!==0||err|| (proof&&!accepted)){const evidence=failureEvidence(err);if(evidence)process.stderr.write(JSON.stringify({minimum_worker_failure:evidence})+'\n');reject(failure??Error('worker refused; diagnostic-sha256='+hash(err)));}else resolve();});});
  const row=inspect(cid);requireFact(!row.running&&!row.oom&&row.exit===0,'worker release state');
  if(proof)reconcile(serverCid);
  return out;
}
async function main() {
  const env=process.env, cwd=process.cwd(), uid=process.getuid();
  requireFact(process.platform==='linux'&&process.arch==='x64'&&uid>0&&env.RUNNER_OS==='Linux'&&env.GITHUB_ACTIONS==='true'&&env.GITHUB_EVENT_NAME==='workflow_dispatch'
    &&env.GITHUB_REPOSITORY==='NichlasB/alynt-drime-backups-dashboard'&&env.GITHUB_REF==='refs/heads/master'&&/^[a-f0-9]{40}$/.test(env.GITHUB_SHA??'')&&env.EXPECTED_SHA===env.GITHUB_SHA
    &&/^[0-9]+$/.test(env.GITHUB_RUN_ID??'')&&/^[0-9]+$/.test(env.GITHUB_RUN_ATTEMPT??'')&&env.MINIMUM_MODEL===MODEL&&!env.DOCKER_HOST&&!env.DOCKER_CONTEXT,'exact approved manual job required');
  requireFact(command(['context','inspect','--format','{{.Endpoints.docker.Host}}']).trim()==='unix:///var/run/docker.sock','local trusted engine only');
  const auditOnly=env.MINIMUM_SCOPE==='audit-only-v1',recover=process.argv.length===3&&process.argv[2]==='--cleanup';
  const phases=executionPhases(env.MINIMUM_SCOPE,recover?(auditOnly?'--audit-only':undefined):process.argv[2],env.GITHUB_WORKFLOW_REF);
  requireFact(phases!==null&&(!auditOnly||env.GITHUB_WORKFLOW_REF===AUDIT_WORKFLOW),'fixed execution scope required');
  const owner='adb-minimum-'+env.GITHUB_RUN_ID+'-'+env.GITHUB_RUN_ATTEMPT;
  const stage=join(realpathSync(env.RUNNER_TEMP),owner);
  requireFact(dirname(stage)===realpathSync(env.RUNNER_TEMP),'exact temporary child');
  if(recover){cleanup(owner,stage,auditOnly);return;}
  requireFact(process.argv.length===(auditOnly?3:2)&&cwd===env.GITHUB_WORKSPACE&&!existsSync(stage)&&ownedIds(owner).length===0,'fresh owned namespace only');
  const git=spawnSync('git',['rev-parse','HEAD'],{encoding:'utf8',timeout:5000});requireFact(git.status===0&&git.stdout.trim()===env.GITHUB_SHA,'exact source head');
  const coreHead=spawnSync('git',['-C','.adb-minimum-core','rev-parse','HEAD'],{encoding:'utf8',timeout:5000});requireFact(coreHead.status===0&&coreHead.stdout.trim()==='cc101b64012b16d087780657a2b828ccd7794a63','exact genuine core checkout');
  const coreTree=spawnSync('git',['-C','.adb-minimum-core','rev-parse','HEAD^{tree}'],{encoding:'utf8',timeout:5000});requireFact(coreTree.status===0&&coreTree.stdout.trim()==='a3bd7a4074a4c95c8af3b969346726273d68e167','exact genuine core tree');
  mkdirSync(stage,{mode:0o700});
  let primaryFailure=null;
  try {
    const source=stage+'/source';mkdirSync(source,{mode:0o700});
    const files=[];
    for(const dir of ['includes','scripts/ci'])for(const item of readdirSync(dir,{recursive:true,withFileTypes:true})) {
      if(item.isFile()&&(dir==='includes'||item.name.startsWith('source-policy-minimum-')))files.push(relative(cwd,join(item.parentPath??item.path,item.name)).replaceAll('\\','/'));
    }
    files.push('scripts/manual/source-policy-proof-reflection.php','scripts/manual/source-policy-supported-core-pins.php','scripts/ci/source-policy-linux-qualification-controller.mjs','.github/workflows/source-policy-minimum-qualification.yml','.github/workflows/source-policy-minimum-audit.yml');
    const core=['version.php','load.php','plugin.php','class-wp-hook.php','class-wp-error.php','wp-db.php','cache.php','class-wp-object-cache.php','formatting.php','functions.php','option.php'];
    let bytes=0;const rows=[];
    for(const file of files.sort()) {
      const info=lstatSync(file);requireFact(info.isFile()&&!info.isSymbolicLink()&&info.size<1048576,'finite regular source');bytes+=info.size;
      const target=source+'/'+file;mkdirSync(dirname(target),{recursive:true,mode:0o700});copyFileSync(file,target);rows.push(hash(readFileSync(target))+'  '+file);
    }
    for(const file of core) {const path='.adb-minimum-core/wp-includes/'+file;const info=lstatSync(path);requireFact(info.isFile()&&!info.isSymbolicLink()&&info.size<1048576,'finite core image');bytes+=info.size;const name='core/wp-includes/'+file;mkdirSync(dirname(source+'/'+name),{recursive:true,mode:0o700});copyFileSync(path,source+'/'+name);rows.push(hash(readFileSync(path))+'  '+name);}
    requireFact(bytes<16777216&&rows.length<512,'source-stage bound');mkdirSync(source+'/core/wp-content',{mode:0o700});
    const cohort=rows.sort().join('\n')+'\n', image=hash(cohort);writeFileSync(source+'/cohort.sha256',cohort,{mode:0o600,flag:'wx'});
    for(const role of ['php','server']) {
      command(['pull','--platform','linux/amd64',selections[role].ref],'',120000);
      const row=JSON.parse(command(['image','inspect','--format','{"id":{{json .Id}},"os":{{json .Os}},"arch":{{json .Architecture}},"volumes":{{json .Config.Volumes}},"entry":{{json .Config.Entrypoint}},"cmd":{{json .Config.Cmd}}}',selections[role].ref]));
      requireFact(imageProfile(row,role),'pinned engine image/config profile');
    }
    const common=['--pull','never','--read-only','--user',uid+':'+uid,'--cap-drop','ALL','--security-opt','no-new-privileges','--cpus','1','--no-healthcheck','--label','com.alynt.minimum='+owner];
    const init='test ! -e /var/lib/mysql/mysql; timeout --signal=TERM --kill-after=2s 40s mariadb-install-db --no-defaults --basedir=/usr --datadir=/var/lib/mysql --auth-root-authentication-method=normal --skip-test-db --skip-name-resolve --skip-log-bin --innodb-buffer-pool-size=32M --innodb-log-file-size=8M --aria-pagecache-buffer-size=8M >/tmp/bootstrap.log 2>&1; exec mariadbd --no-defaults --datadir=/var/lib/mysql --socket=/tmp/minimum.sock --pid-file=/tmp/minimum.pid --bind-address=127.0.0.1 --port=3306 --skip-name-resolve --skip-log-bin --max-connections=10 --innodb-buffer-pool-size=32M --innodb-log-file-size=8M --aria-pagecache-buffer-size=8M --max-statement-time=2 --innodb-lock-wait-timeout=1 --lock-wait-timeout=1 --wait-timeout=15 --connect-timeout=2';
    const serverCid=command(['create',...common,'--network','none','--memory','512m','--pids-limit','64','--tmpfs',`/var/lib/mysql:rw,nosuid,nodev,noexec,size=256m,mode=700,uid=${uid},gid=${uid}`,'--tmpfs',`/tmp:rw,nosuid,nodev,noexec,size=16m,mode=700,uid=${uid},gid=${uid}`,'--entrypoint','/bin/sh',selections.server.ref,'-ceu',init]).trim();
    requireFact(/^[a-f0-9]{64}$/.test(serverCid)&&containerProfile(inspect(serverCid),'server',owner,selections.server.config,null,uid,stage),'server configuration');command(['start',serverCid]);
    const start=Date.now();let ready=false;
    while(Date.now()-start<50000){const probe=spawnSync('docker',['exec',serverCid,'test','-S','/tmp/minimum.sock'],{timeout:2000,maxBuffer:1024});if(probe.status===0){ready=true;break;}requireFact(inspect(serverCid).running,'server startup stopped');await new Promise(resolve=>setTimeout(resolve,250));}
    requireFact(ready,'bounded server startup');
    reviewLog(command(['exec',serverCid,'cat','/tmp/bootstrap.log'],'',3000,16384),serverCid.slice(0,12));
    reviewLog(command(['logs',serverCid],'',3000,16384),serverCid.slice(0,12));
    requireFact(rootSQL(serverCid,'SELECT VERSION(),@@autocommit,@@in_transaction;')==='10.11.18-MariaDB-ubu2204\t1\t0\n','exact actual server');reconcile(serverCid);
    const password=auditOnly?null:randomBytes(32).toString('hex'),run=hash(env.GITHUB_RUN_ID+':'+env.GITHUB_RUN_ATTEMPT).slice(0,32);
    const vars=Object.fromEntries(['GITHUB_ACTIONS','GITHUB_EVENT_NAME','GITHUB_REPOSITORY','GITHUB_REF','GITHUB_SHA','GITHUB_RUN_ID','GITHUB_RUN_ATTEMPT','EXPECTED_SHA','MINIMUM_MODEL'].map(key=>[key,env[key]]));
    vars.MINIMUM_IMAGE=image;vars.MINIMUM_SERVER_CID=serverCid;vars.PHP_INI_SCAN_DIR='';
    if(auditOnly){vars.MINIMUM_SCOPE='audit-only-v1';vars.GITHUB_WORKFLOW_REF=AUDIT_WORKFLOW;}
    let moduleHash=null;
    for(const phase of phases) {
      if(phase==='proof') {
        requireFact(!auditOnly,'audit cannot create application fixtures');
        const table='adbm_'+run.slice(0,12)+'_options';
        rootSQL(serverCid,`CREATE DATABASE adb_minimum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci; CREATE TABLE adb_minimum.${table} (option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,option_name varchar(191) NOT NULL,option_value longtext NOT NULL,autoload varchar(20) NOT NULL,PRIMARY KEY(option_id),UNIQUE KEY option_name(option_name),KEY autoload(autoload)) ENGINE=InnoDB COLLATE=utf8mb4_unicode_520_ci; CREATE USER 'adbm'@'127.0.0.1' IDENTIFIED BY '${password}'; GRANT SELECT,INSERT,UPDATE,DELETE ON adb_minimum.${table} TO 'adbm'@'127.0.0.1';`);
        vars.QUALIFICATION_PASSWORD=password;
      }
      const envfile=stage+'/private.env';writeFileSync(envfile,Object.entries(vars).map(([key,value])=>key+'='+value).join('\n')+'\n',{mode:0o600,flag:'wx'});
      const cid=command(['create',...common,'--interactive','--network','container:'+serverCid,'--memory','128m','--pids-limit','16','--tmpfs',`/tmp:rw,nosuid,nodev,noexec,size=4m,mode=700,uid=${uid},gid=${uid}`,'--tmpfs',`/var/www/html:rw,nosuid,nodev,noexec,size=4k,mode=700,uid=${uid},gid=${uid}`,'--mount','type=bind,src='+source+',dst=/proof,readonly','--workdir','/proof','--env-file',envfile,'--entrypoint','/usr/local/bin/php',selections.php.ref,'-n','-d','extension=/usr/local/lib/php/extensions/no-debug-non-zts-20190902/mysqli.so','-d','memory_limit=64M','-d','mysqli.allow_persistent=0','-d','default_socket_timeout=3','-d','mysqlnd.net_read_timeout=3','scripts/ci/source-policy-minimum-qualification.php','--owned-minimum-qualification',phase]).trim();rmSync(envfile);
      requireFact(containerProfile(inspect(cid),'php',owner,selections.php.config,serverCid,uid,stage),'worker configuration');
      const out=await worker(cid,run,image,serverCid,phase==='proof',moduleHash);
      if(phase==='audit'){const match=new RegExp('^ADBMA1\\|'+run+'\\|'+image+'\\|([a-f0-9]{64})\\n$').exec(out);requireFact(match,'source/runtime audit refused');moduleHash=match[1];}
      else requireFact(moduleHash!==null&&terminal(out,run,image,serverCid),'ordered proof receipt');
      command(['rm','--volumes',cid]);
    }
    for(const row of rows){const [digest,file]=row.split('  ');requireFact(hash(readFileSync(source+'/'+file))===digest,'parent source readback');}
    process.stdout.write((auditOnly?'Minimum audit-only completed; qualification_body_executed=false application_fixtures_created=false; ':'Minimum trusted-lab functional body passed; ')+'independent_loaded_object_attestation=false mapping_consistency=not-established runtime_grant=false production_ready=false\n');
  } catch(error) {primaryFailure=error;throw error;}
  finally {try{cleanup(owner,stage,auditOnly);}catch(error){if(primaryFailure)process.stderr.write('Minimum cleanup also refused; original failure preserved.\n');else throw error;}}
}
if(process.argv[1]===fileURLToPath(import.meta.url))main().catch(error=>{const reason=typeof error.minimumReason==='string'&&/^[A-Za-z0-9 /-]{1,100}$/.test(error.minimumReason)?error.minimumReason:'unclassified-host-or-child-failure';process.stderr.write('Fixed minimum lab refused: '+reason+'; owned cleanup required if recovery failed.\n');process.exitCode=1;});
