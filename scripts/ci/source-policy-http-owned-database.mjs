// Same pinned database recipe; operations require the fresh owned stage.
import {readFileSync,lstatSync,mkdirSync,existsSync} from 'node:fs';
import {setTimeout as pause} from 'node:timers/promises';
import {HTTP_PROFILE} from './source-policy-http-profile.mjs';
import {command,need} from './source-policy-http-owned-source.mjs';
import {reviewLog} from './source-policy-linux-qualification-controller.mjs';
export class OwnedHttpDatabase {
 #owner;#cid=null;#uid=process.getuid();#gid=process.getgid();#deadline=Infinity;
 constructor(owner){this.#owner=owner;need(this.#uid!==0,'database-user');}
 #inspect(){
  need(/^[a-f0-9]{64}$/.test(this.#cid??''),'container-id');const rows=JSON.parse(command('docker',['container','inspect',this.#cid],{left:Math.ceil(Math.min(5000,this.#deadline-performance.now()))}));need(Array.isArray(rows)&&rows.length===1,'container-count');const r=rows[0];
  need(r.Id===this.#cid&&r.Config.Labels?.['com.alynt.http']===this.#owner.owner&&r.Config.User===this.#uid+':'+this.#gid&&r.HostConfig.NetworkMode==='none'&&r.HostConfig.ReadonlyRootfs===true&&r.HostConfig.PidsLimit===64&&r.HostConfig.Memory===536870912&&r.HostConfig.CapDrop?.join(',')==='ALL'&&r.HostConfig.SecurityOpt?.includes('no-new-privileges'),'container-owner');return r;
 }
 sql(statement,left){
  this.#deadline=performance.now()+left;
  need(typeof statement==='string'&&Buffer.byteLength(statement)<=65536&&this.#inspect().State.Running,'admin-statement');
  return command('docker',['exec','--interactive','--user',this.#uid+':'+this.#gid,this.#cid,'mariadb','--no-defaults','--protocol=SOCKET','--socket=/run/adb-owned/native.sock','--user=root','--skip-reconnect','--connect-timeout=2','--batch','--raw','--skip-column-names'],{input:statement,left:Math.floor(this.#deadline-performance.now()),maxBuffer:65536});
 }
 async start(left){
  const deadline=performance.now()+left,o=this.#owner,u=this.#uid,g=this.#gid;need(command('docker',['container','ls','--all','--filter','label=com.alynt.http='+o.owner,'--quiet','--no-trunc'])==='','container-absence');mkdirSync(o.stage+'/socket',{mode:0o700});
  const script='test ! -e /var/lib/mysql/mysql\ntimeout --signal=TERM --kill-after=2s 40s mariadb-install-db --no-defaults --basedir=/usr --datadir=/var/lib/mysql --auth-root-authentication-method=normal --skip-test-db --skip-name-resolve --skip-log-bin --innodb-buffer-pool-size=32M --innodb-log-file-size=8M --aria-pagecache-buffer-size=8M >/run/adb-owned/bootstrap.log 2>&1\nexec mariadbd --no-defaults --datadir=/var/lib/mysql --socket=/run/adb-owned/native.sock --pid-file=/run/adb-owned/native.pid --log-error=/run/adb-owned/server.log --skip-networking --skip-name-resolve --skip-log-bin --max-connections=10 --innodb-buffer-pool-size=32M --innodb-log-file-size=8M --aria-pagecache-buffer-size=8M --max-statement-time=2 --innodb-lock-wait-timeout=1 --lock-wait-timeout=1 --wait-timeout=15 --connect-timeout=2';
  command('docker',['run','--detach','--pull','never','--name',o.owner,'--cidfile',o.stage+'/owned.cid','--label','com.alynt.http='+o.owner,'--network','none','--read-only','--user',u+':'+g,'--cap-drop','ALL','--security-opt','no-new-privileges','--pids-limit','64','--memory','512m','--cpus','1','--no-healthcheck','--mount',`type=bind,source=${o.stage}/socket,destination=/run/adb-owned`,'--tmpfs',`/var/lib/mysql:rw,nosuid,nodev,noexec,size=256m,mode=700,uid=${u},gid=${g}`,'--tmpfs',`/tmp:rw,nosuid,nodev,noexec,size=16m,mode=700,uid=${u},gid=${g}`,'--entrypoint','/bin/sh',HTTP_PROFILE.server_image,'-ceu',script],{left:Math.min(15000,left)});
  this.#cid=readFileSync(o.stage+'/owned.cid','utf8').trim();
  while(true){need(performance.now()<deadline&&this.#inspect().State.Running,'database-startup');const file=o.stage+'/socket/server.log';if(existsSync(file)){need(lstatSync(file).size<=65536,'database-log-budget');if(readFileSync(file,'utf8').includes('ready for connections.'))break;}await pause(100);}
  const s=this.sql("SELECT VERSION(),@@skip_networking,@@wsrep_on,@@autocommit,@@in_transaction,(SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX),(SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME NOT IN ('mysql','information_schema','performance_schema','sys'));",Math.ceil(Math.min(5000,deadline-performance.now())));
  need(s===HTTP_PROFILE.server+'\t1\t0\t1\t0\t0\t0\t0\n','database-baseline');
 }
 create(s,password,left){need(/^adb_http_[a-f0-9]{12}$/.test(s.database)&&/^adbh_[a-f0-9]{12}$/.test(s.user)&&/^[a-f0-9]{64}$/.test(password),'database-names');this.sql(`CREATE DATABASE ${s.database} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;CREATE USER '${s.user}'@'localhost' IDENTIFIED BY '${password}';GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,ALTER,INDEX ON ${s.database}.* TO '${s.user}'@'localhost';`,left);}
 seal(s,left){this.sql(`REVOKE CREATE,ALTER,INDEX ON ${s.database}.* FROM '${s.user}'@'localhost';`,left);}
 reconcile(left){need(this.sql('SELECT (SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);',left)==='0\t0\n','all-server-release');}
 cleanup(s,left,diagnosticLimit=65536){
  const deadline=performance.now()+left,o=this.#owner;this.#deadline=deadline;let failed=false;
  if(!this.#cid&&existsSync(o.stage+'/owned.cid')){const f=lstatSync(o.stage+'/owned.cid');need(f.isFile()&&!f.isSymbolicLink()&&f.uid===this.#uid&&f.size<=65,'cid-owner');this.#cid=readFileSync(o.stage+'/owned.cid','utf8').trim();}
  if(!this.#cid){need(command('docker',['container','ls','--all','--filter','label=com.alynt.http='+o.owner,'--quiet','--no-trunc'])==='','partial-container');return;}
  this.#inspect();const credit=()=>Math.ceil(Math.min(5000,deadline-performance.now()));
  try{if(this.#inspect().State.Running){this.reconcile(credit());const out=this.sql(`DROP DATABASE IF EXISTS ${s.database};DROP USER IF EXISTS '${s.user}'@'localhost';SELECT (SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME NOT IN ('mysql','information_schema','performance_schema','sys')),(SELECT COUNT(*) FROM mysql.global_priv WHERE User='${s.user}'),(SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);`,credit());need(out==='0\t0\t0\t0\n','database-removal');command('docker',['stop','--time','5',this.#cid],{left:Math.ceil(Math.min(8000,deadline-performance.now()))});}
   const stopped=this.#inspect();need(!stopped.State.Running&&stopped.State.ExitCode===0&&!stopped.State.OOMKilled,'database-exit');let n=0;for(const leaf of ['bootstrap.log','server.log']){const f=o.stage+'/socket/'+leaf,st=lstatSync(f);need(st.isFile()&&!st.isSymbolicLink()&&st.uid===this.#uid&&st.size<=65536,'database-log-owner');n+=st.size;reviewLog(readFileSync(f,'utf8'),this.#cid.slice(0,12));}need(n<=diagnosticLimit,'aggregate-diagnostics');
  }catch{failed=true;}
  this.#inspect();command('docker',['container','rm','--force','--volumes',this.#cid],{left:Math.ceil(Math.max(1,Math.min(8000,deadline-performance.now())))});need(command('docker',['container','ls','--all','--filter','label=com.alynt.http='+o.owner,'--quiet','--no-trunc'])==='','container-absence-after');need(!failed,'database-cleanup-unknown');
 }
}
