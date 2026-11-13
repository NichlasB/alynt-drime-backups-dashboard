// Fixed original-job clock and durable ownership DATA; no IO on import.
import {readFileSync,lstatSync,statSync,existsSync,writeFileSync,renameSync,realpathSync} from 'node:fs';
import {need,sha,ownedHttpJob} from './source-policy-http-owned-source.mjs';
import {LIMITS} from './source-policy-http-profile.mjs';
export const OWNED_PHP_ARGS=['-n','-d','extension=mysqlnd','-d','extension=mysqli','-d','memory_limit=64M','-d','mysqli.allow_persistent=0','-d','default_socket_timeout=3','-d','mysqlnd.net_read_timeout=3','-d','display_errors=0','-d','log_errors=0'];
export function recoveryArguments(owner,role){
 need(['setup','server'].includes(role),'recovery-role');return role==='setup'?[...OWNED_PHP_ARGS,owner.stage+'/proof/scripts/ci/source-policy-http-owned-setup.php','--owned-http-setup']:[...OWNED_PHP_ARGS,'-S','127.0.0.1:18777','-t',owner.stage+'/core',owner.stage+'/proof/scripts/ci/source-policy-http-owned-router.php'];
}
export class OwnedHttpClock {
 #origin;#read;#cleanup=null;
 constructor(origin,read=()=>process.hrtime.bigint()){
  need(typeof origin==='string'&&/^[1-9][0-9]{0,19}$/.test(origin),'original-job-clock');this.#origin=BigInt(origin);this.#read=read;this.now();
 }
 now(){const delta=this.#read()-this.#origin;need(delta>=0n,'original-job-origin');const elapsed=delta/1000000n;need(elapsed<BigInt(LIMITS.job_ms),'original-job-deadline');return Number(elapsed);}
 left(deadline,max=LIMITS.job_ms){const n=Math.floor(Math.min(deadline-this.now(),max));need(n>0,'original-credit');return n;}
 admit(){need(LIMITS.job_ms-this.now()>=LIMITS.setup_ms+LIMITS.http_ms+LIMITS.cleanup_ms,'phase-and-cleanup-reserve');}
 cleanupDeadline(){if(this.#cleanup===null)this.#cleanup=Math.min(LIMITS.job_ms,this.now()+LIMITS.cleanup_ms);return this.#cleanup;}
}
export function processStart(stat,pid,allowDead=false){
 need(typeof stat==='string'&&stat.length<=8192&&stat.startsWith(pid+' ('),'process-stat');const parts=stat.slice(stat.lastIndexOf(')')+2).trim().split(/\s+/);
 need((allowDead||parts[0]!=='Z')&&/^[0-9]+$/.test(parts[19]??''),'process-start');return sha(pid+':'+parts[19]);
}
export function saveOwnership(owner,server){
 const path=owner.stage+'/ownership.json',next=path+'.next';need(!existsSync(next),'ownership-atomic-absence');
 writeFileSync(next,JSON.stringify({...owner,server}),{flag:'wx',mode:0o600});renameSync(next,path);
}
export function readOwnership(env){
 need(ownedHttpJob(env)&&process.platform==='linux'&&realpathSync(env.RUNNER_TEMP)===env.RUNNER_TEMP,'recovery-job');
 const run=sha(env.GITHUB_RUN_ID+':1').slice(0,32),stage=env.RUNNER_TEMP+'/adb-http-'+run;
 if(!existsSync(stage))return null;
 const st=lstatSync(stage),file=stage+'/ownership.json';need(st.isDirectory()&&!st.isSymbolicLink()&&st.uid===process.getuid()&&(st.mode&0o777)===0o700,'recovery-directory');
 const f=lstatSync(file);need(f.isFile()&&!f.isSymbolicLink()&&f.uid===st.uid&&f.size<=4096&&(f.mode&0o077)===0,'recovery-record');
 const o=JSON.parse(readFileSync(file,'utf8'));need(o.stage===stage&&o.root===env.RUNNER_TEMP&&o.owner==='adb-http-'+run&&o.run===run&&o.inode===st.ino&&o.device===st.dev&&realpathSync(stage)===stage,'recovery-binding');
 need(o.server===null||o.server&&Number.isSafeInteger(o.server.pid)&&o.server.pid>0&&/^[a-f0-9]{64}$/.test(o.server.start)&&/^[a-f0-9]{64}$/.test(o.server.executable)&&['setup','server'].includes(o.server.role),'recovery-server');return o;
}
export function verifyRecoveryProcess(owner){
 const s=owner.server,p='/proc/'+s.pid;need(lstatSync(p).uid===process.getuid(),'recovery-process-owner');
 const stat=statSync(p+'/exe');need(stat.isFile()&&stat.size<=LIMITS.file_bytes,'recovery-executable-size');
 need(sha(readFileSync(p+'/exe'))===s.executable,'recovery-executable');const raw=readFileSync(p+'/cmdline');need(raw.length<=8192,'recovery-command-budget');
 const argv=raw.toString().split('\0');need(argv.pop()===''&&argv.length>0,'recovery-command-shape');argv.shift();
 need(JSON.stringify(argv)===JSON.stringify(recoveryArguments(owner,s.role)),'recovery-exact-command');
}
export function processStillOwned(server,read=pid=>readFileSync('/proc/'+pid+'/stat','utf8')){
 try{const stat=read(server.pid),same=processStart(stat,server.pid,true)===server.start;return same&&stat.slice(stat.lastIndexOf(')')+2).split(' ')[0]!=='Z';}catch(e){if(e.code==='ENOENT')return false;throw e;}
}
