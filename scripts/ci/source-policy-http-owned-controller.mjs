// One explicitly admitted ephemeral CI proof. Importing never launches or connects.
import {spawn} from 'node:child_process';
import {randomBytes} from 'node:crypto';
import {readFileSync,lstatSync,existsSync} from 'node:fs';
import {fileURLToPath} from 'node:url';
import {setTimeout as pause} from 'node:timers/promises';
import {HTTP_PROFILE,LIMITS,manifestSummary,record} from './source-policy-http-profile.mjs';
import {HttpDataLifecycle} from './source-policy-http-lifecycle.mjs';
import {HTTP_FLOW} from './source-policy-http-supervisor.mjs';
import {installerSidecarMatches} from './source-policy-http-installer.mjs';
import {OwnedCookieJar,encodeOwnedForm,ownedRequestPacket,sendOwnedPacket} from './source-policy-http-owned-wire.mjs';
import {need,sha,phpString,command,ownedHttpJob,createOwnedStage,stageSource,storageFor,writePrivate,frozenRows,removeOwnedStage} from './source-policy-http-owned-source.mjs';
import {OwnedHttpDatabase} from './source-policy-http-owned-database.mjs';
import {OwnedHttpClock,processStart,saveOwnership,readOwnership,processStillOwned,verifyRecoveryProcess,OWNED_PHP_ARGS as phpArgs} from './source-policy-http-owned-safety.mjs';
const initial='a:1:{i:9;a:1:{s:7:"wpvivid";s:17:"external_optional";}}';
const saved='a:2:{i:9;a:1:{s:7:"wpvivid";s:17:"external_optional";}i:7;a:1:{s:7:"wpvivid";s:17:"external_optional";}}';
export function setupFailureSummary(code,signal,out,err){
 const bounded=v=>typeof v==='string'&&Buffer.byteLength(v)<=8192?v:'';
 const stdout=bounded(out),stderr=bounded(err),match=/^Owned HTTP setup refused:([a-f0-9]{64})\n$/.exec(stderr);
 return {event:'owned-http-setup-failure',status:Number.isInteger(code)&&code>=0&&code<=255?code:null,signal:['SIGTERM','SIGKILL'].includes(signal)?signal:null,assertion:match?match[1]:null,stdout_bytes:typeof out==='string'?Buffer.byteLength(out):null,stderr_bytes:typeof err==='string'?Buffer.byteLength(err):null,stdout_digest:sha(stdout),stderr_digest:sha(stderr)};
}
export function physicalPolicy(raw,storage){
 const parts=raw.trimEnd().split('\t');need(parts.length===2&&parts.every(p=>/^(?:[A-F0-9]{2})+$/.test(p)),'policy-row');
 const bytes=Buffer.from(parts[0],'hex').toString(),autoload=Buffer.from(parts[1],'hex').toString();need(bytes===initial&&autoload==='off'||bytes===saved&&autoload==='off','policy-bytes');
 let framed='adbd-source-policy-save-v1';for(const p of [sha(storage.database+':'+storage.options),'1',bytes,autoload])framed+=Buffer.byteLength(p)+':'+p;
 return {digest:sha(bytes+':'+autoload),revision:sha(framed),mode:bytes===initial?'required':'external_optional',bytes,autoload};
}
function privateConfig(owner,id,s,password,challenge){
 const values={...id,...s,password,challenge};const pairs=Object.entries(values).map(([k,v])=>phpString(k)+' => '+(typeof v==='boolean'?(v?'true':'false'):phpString(v)));
 writePrivate(owner.stage+'/private.php','<?php return array('+pairs.join(',')+');');
 const constants={DB_NAME:s.database,DB_USER:s.user,DB_PASSWORD:password,DB_HOST:'localhost:'+s.socket,DB_CHARSET:'utf8',DB_COLLATE:'',WP_HOME:s.origin,WP_SITEURL:s.origin,WP_CONTENT_DIR:s.content_path,WP_CONTENT_URL:s.origin+'/wp-content',WP_MEMORY_LIMIT:'64M',WP_MAX_MEMORY_LIMIT:'64M',WPLANG:'en_US'};
 for(const scheme of ['AUTH','SECURE_AUTH','LOGGED_IN','NONCE'])for(const part of ['KEY','SALT'])constants[scheme+'_'+part]=randomBytes(32).toString('hex');
 const lines=['<?php'];for(const [k,v]of Object.entries(constants))lines.push('define('+phpString(k)+','+phpString(v)+');');
 for(const k of ['DISABLE_WP_CRON','WP_HTTP_BLOCK_EXTERNAL','AUTOMATIC_UPDATER_DISABLED','WP_INSTALLING']){if(k!=='WP_INSTALLING')lines.push('define('+phpString(k)+',true);');}
 for(const k of ['WP_DEBUG','WP_DEBUG_LOG','WP_DEBUG_DISPLAY','MULTISITE','SAVEQUERIES'])lines.push('define('+phpString(k)+',false);');
 lines.push('$table_prefix='+phpString(s.prefix)+';','require __DIR__ . "/wp-settings.php";');writePrivate(s.core_path+'/wp-config.php',lines.join('\n'));
}
function manifest(owner){const rows=frozenRows(owner),file=owner.stage+'/private.php',s=lstatSync(file);rows.push({path:'private.php',size:s.size,type:'file',digest:sha(readFileSync(file))});return rows.sort((a,b)=>a.path<b.path?-1:1);}
function readReceipt(owner,index){const path=owner.stage+'/receipts/'+index+'.json',s=lstatSync(path);need(s.isFile()&&!s.isSymbolicLink()&&s.uid===process.getuid()&&s.size<=8192,'receipt-file');return JSON.parse(readFileSync(path,'utf8'));}
function procIdentity(child){
 need(Number.isSafeInteger(child.pid)&&child.exitCode===null&&child.signalCode===null,'server-alive');return processStart(readFileSync('/proc/'+child.pid+'/stat','utf8'),child.pid);
}
async function stopChild(child,ended,left){
 if(!child)return;const deadline=performance.now()+left;if(child.exitCode===null&&child.signalCode===null)child.kill('SIGTERM');
 while(child.exitCode===null&&child.signalCode===null&&performance.now()<deadline-500)await pause(10);
 if(child.exitCode===null&&child.signalCode===null)child.kill('SIGKILL');
 let timer;await Promise.race([ended,new Promise((_,deny)=>{timer=setTimeout(()=>deny(Error('Owned child cleanup unconfirmed')),Math.max(1,deadline-performance.now()));})]).finally(()=>clearTimeout(timer));
 need(child.exitCode!==null||child.signalCode!==null,'server-reaped');
}
export async function main(){
 need(ownedHttpJob(process.env),'authorized-job');process.umask(0o077);
 const clock=new OwnedHttpClock(process.env.ADB_ORIGINAL_JOB_NS);clock.admit();const created=clock.now(),setupDeadline=created+LIMITS.setup_ms;
 const owner=createOwnedStage(process.env),db=new OwnedHttpDatabase(owner);saveOwnership(owner,null);let child=null,ended=null,model=null,s=null,failure=null,stage='source',cleaned=false,diagnosticBytes=0;
 const now=()=>clock.now(),left=deadline=>clock.left(deadline);
 const jars=Object.fromEntries(['primary','anonymous','incapable','other','second'].map(k=>[k,new OwnedCookieJar()]));
 try{
  stageSource(owner);const image=sha(JSON.stringify(frozenRows(owner))),bound=storageFor(owner,image,process.env.GITHUB_SHA);s=bound.storage;const id=bound.id,challenge=randomBytes(16).toString('hex'),password=randomBytes(32).toString('hex');
  privateConfig(owner,id,s,password,challenge);const rows=manifest(owner),expectation=manifestSummary(rows);need(expectation,'complete-manifest');writePrivate(owner.stage+'/manifest.json',JSON.stringify(rows));
  const unchanged=()=>need(JSON.stringify(manifest(owner))===JSON.stringify(rows),'complete-source-unchanged');
  model=new HttpDataLifecycle(id,created,challenge);model.admit(HTTP_PROFILE,s,rows,expectation,now());
  const env={PATH:process.env.PATH,ADB_HTTP_OWNED_APPROVED:'true',PHP_INI_SCAN_DIR:''};
  stage='database';await db.start(left(setupDeadline));db.create(s,password,left(setupDeadline));
  stage='install';saveOwnership(owner,{pending:true});
  child=spawn('php',[...phpArgs,owner.stage+'/proof/scripts/ci/source-policy-http-owned-setup.php','--owned-http-setup'],{env,stdio:['ignore','pipe','pipe']});
  let raw='',setupErr='',setupCode=null,setupSignal=null;
  ended=new Promise(resolve=>{child.on('error',()=>{failure=Error('Owned setup launch refused');resolve();});child.on('close',(code,signal)=>{setupCode=code;setupSignal=signal;resolve();});});
  saveOwnership(owner,{pid:child.pid,start:procIdentity(child),role:'setup',executable:sha(readFileSync('/proc/'+child.pid+'/exe'))});
  const boundSetup=()=>{if(Buffer.byteLength(raw)>8192||Buffer.byteLength(setupErr)>1024){failure=Error('Owned setup channel refused');child.kill('SIGKILL');}};
  child.stdout.on('data',b=>{raw+=b;boundSetup();});child.stderr.on('data',b=>{setupErr+=b;boundSetup();});
  const setupTimer=setTimeout(()=>{failure=Error('Owned setup deadline');child.kill('SIGKILL');},left(setupDeadline));await ended;clearTimeout(setupTimer);
  if(failure||setupCode!==0||setupSignal||setupErr!=='')process.stderr.write(JSON.stringify(setupFailureSummary(setupCode,setupSignal,raw,setupErr))+'\n');
  need(!failure&&setupCode===0&&!setupSignal&&setupErr==='','owned-setup-terminal');child=null;saveOwnership(owner,null);
  const setup=JSON.parse(raw);need(installerSidecarMatches(setup.containment,id,'setup'),'setup-sidecar');unchanged();db.reconcile(left(setupDeadline));db.seal(s,left(setupDeadline));model.setup(setup.ledger,now());
  // Fixed independent uncached rows; setup's non-autoloaded rows are modern off.
  const policy=()=>physicalPolicy(db.sql(`SELECT HEX(option_value),HEX(autoload) FROM ${s.database}.${s.options} WHERE BINARY option_name=BINARY 'alynt_drime_backups_dashboard_source_policies';`,Math.min(5000,left(LIMITS.job_ms-LIMITS.cleanup_ms))),s);
  const sentinel=()=>{const r=db.sql(`SELECT SHA2(CONCAT(option_value,':',autoload),256) FROM ${s.database}.${s.options} WHERE BINARY option_name=BINARY 'owned_non_target';`,5000).trim();need(/^[a-f0-9]{64}$/.test(r),'sentinel');return r;};
  const original=policy(),sentinelHash=sentinel();need(original.bytes===initial,'initial-physical');
  const invariant=()=>sha(db.sql(`SELECT option_id,HEX(option_name),HEX(option_value),HEX(autoload) FROM ${s.database}.${s.options} WHERE BINARY option_name<>BINARY 'alynt_drime_backups_dashboard_source_policies' ORDER BY option_id;SELECT umeta_id,user_id,HEX(meta_key),HEX(meta_value) FROM ${s.database}.${s.prefix}usermeta WHERE BINARY meta_key<>BINARY 'session_tokens' ORDER BY umeta_id;CHECKSUM TABLE ${['commentmeta','comments','links','postmeta','posts','term_relationships','term_taxonomy','termmeta','terms','users','alynt_drime_dashboard_sites'].map(t=>s.database+'.'+s.prefix+t).join(',')};`,Math.min(5000,left(LIMITS.job_ms-LIMITS.cleanup_ms))));
  const nonTarget=invariant();
  stage='startup';const startup=now()+LIMITS.transaction_ms;let ready=false;saveOwnership(owner,{pending:true});
  child=spawn('php',[...phpArgs,'-S','127.0.0.1:18777','-t',s.core_path,owner.stage+'/proof/scripts/ci/source-policy-http-owned-router.php'],{env,stdio:['ignore','pipe','pipe']});
  ended=new Promise(resolve=>{child.on('error',()=>{failure=Error('Owned server launch refused');resolve();});child.on('close',()=>resolve());});
  saveOwnership(owner,{pid:child.pid,start:procIdentity(child),role:'server',executable:sha(readFileSync('/proc/'+child.pid+'/exe'))});
  const diagnostic=b=>{diagnosticBytes+=b.length;if(diagnosticBytes>LIMITS.diagnostic_bytes){failure=Error('Owned diagnostics budget refused');child.kill('SIGKILL');}};
  child.stdout.on('data',diagnostic);child.stderr.on('data',b=>{diagnostic(b);if(b.toString().includes('PHP 8.5.12 Development Server (http://127.0.0.1:18777) started'))ready=true;});
  while(!ready){need(!failure&&now()<startup&&child.exitCode===null,'one-server-startup');await pause(10);}
  const start=procIdentity(child),server={...id,pid:child.pid,start,alive:true,manifest:expectation.digest};delete server.root;
  model.server(server,HTTP_PROFILE,now());const httpDeadline=now()+LIMITS.http_ms;let exchange=0,current=original,firstView=null,freshView=null,wrongAction=null,otherView=null,secondView=null,checks=0;
  for(const item of HTTP_FLOW){
   stage='request-'+String(exchange+1);need(!failure&&procIdentity(child)===start,'owned-server-current');unchanged();const before=policy();need(before.digest===current.digest&&sentinel()===sentinelHash,'state-before');
   const jar=jars[item.jar];let pairs=[];let path=item.kind.startsWith('login-')?'/wp-login.php':'/_adb-owned-source-policy';if(item.kind==='nonce-control')path+='?control=wrong-action';
   const method=['submit','login-submit'].includes(item.kind)?'POST':'GET';
   if(item.kind==='login-submit')pairs=[['log',item.jar==='incapable'?'owned_low':item.jar==='other'?'owned_two':'owned_one'],['pwd',password],['wp-submit','Log In'],['redirect_to',s.origin+'/_adb-owned-source-policy'],['testcookie','1']];
   if(item.kind==='submit'){
    let nonce=freshView?.nonce??firstView?.nonce??'',revision=freshView?.revision??firstView?.revision??'';
    if(item.intent==='initial-revision'||item.intent==='stale-revision')revision=firstView.revision;
    if(item.intent==='wrong-action')nonce=wrongAction.nonce;if(item.intent==='wrong-user')nonce=otherView.nonce;if(item.intent==='wrong-session')nonce=secondView.nonce;
    pairs=[['_wpnonce',nonce],['dashboard_site_id','7'],['source_key','wpvivid'],['source_mode','external_optional'],['source_policy_revision',item.intent==='bad-revision'?'bad':revision]];
    if(item.intent==='missing-nonce')pairs.shift();if(item.intent==='compound-nonce')pairs[0][0]='_wpnonce[]';if(item.intent==='compound-site')pairs[1][0]='dashboard_site_id[]';
   }
   const packet=ownedRequestPacket({method,path,challenge,exchange:++exchange,body:method==='POST'?encodeOwnedForm(pairs):Buffer.alloc(0),jar});model.begin(packet.meta,item.kind,item.index,now());const deadline=Math.min(now()+LIMITS.transaction_ms,httpDeadline);
   const response=await sendOwnedPacket(packet,Math.min(LIMITS.transaction_ms,left(deadline)));jar.accept(response.cookies,Date.now());const receipt=readReceipt(owner,exchange);
   need(receipt.run===id.run&&receipt.head===id.head&&receipt.image===id.image&&receipt.exchange===exchange&&JSON.stringify(receipt.meta)===JSON.stringify(packet.meta)&&installerSidecarMatches(receipt.containment,id,'request'),'request-sidecar');
   unchanged();const after=policy();need(sentinel()===sentinelHash&&invariant()===nonTarget,'non-target-preserved');
   const observed=item.index!==null||['view','nonce-control'].includes(item.kind);let result=null,expected=null;
   if(observed){
    const r=JSON.parse(response.body.toString());need(record(r,['status','result','mode','revision','nonce'])&&r.status===response.status,'result-shape');
    if(['view','nonce-control'].includes(item.kind)){need(r.revision===after.revision&&r.mode===after.mode&&typeof r.nonce==='string'&&r.nonce.length>0&&r.nonce.length<=128,'genuine-view');if(item.index===0)firstView=r;else if(item.kind==='nonce-control')wrongAction=r;else if(item.jar==='other')otherView=r;else if(item.jar==='second')secondView=r;else freshView=r;}
    const n=item.index===null||item.index<4?1:0;
    result={event:item.index===null?'http-view-complete':'http-case-complete',model:HTTP_PROFILE.model,...id,index:item.index,status:r.status,result:r.result,checks:receipt.checks,bootstrap:receipt.bootstrap.sql,auth:receipt.auth.sql,...receipt.counts,policy_reads:n,policy_writes:item.index===1?1:0,before:before.digest,after:after.digest,sentinel:sentinelHash,revision:r.revision,mode:r.mode,unknown_changes:0,production_ready:false};delete result.root;
    expected={...id,before:before.digest,after:after.digest,sentinel:sentinelHash,revision:item.index===0||item.index===null?after.revision:null};if(item.index===null)expected.mode=after.mode;
    need(item.index===1?after.bytes===saved&&before.bytes===initial:after.digest===before.digest,'exact-physical-transition');checks+=receipt.checks;
   }else need(after.digest===before.digest,'login-preserves-policy');
   const envelope={status:response.status,body_bytes:response.body_bytes,header_bytes:response.header_bytes,eof:response.eof,location:response.location};
   model.receive(envelope,result,expected,receipt.bootstrap,receipt.auth,now());db.reconcile(Math.min(5000,left(deadline)));need(procIdentity(child)===start,'server-live-after-release');model.release({...server,scope:'all-server',sessions:0,transactions:0},now());current=after;
  }
  stage='cleanup';const cleanupDeadline=clock.cleanupDeadline();await stopChild(child,ended,left(cleanupDeadline));child=null;saveOwnership(owner,null);db.reconcile(Math.min(5000,left(cleanupDeadline)));unchanged();db.cleanup(s,left(cleanupDeadline),LIMITS.diagnostic_bytes-diagnosticBytes);left(cleanupDeadline);removeOwnedStage(owner);cleaned=true;
  const terminal=model.close({run:id.run,head:id.head,image:id.image,ownership:true,processes:0,listeners:0,containers:0,schemas:0,accounts:0,files:0,sessions:0,transactions:0},now());need(terminal.completed&&checks>0,'complete-physical-proof');
  process.stdout.write(JSON.stringify({event:'owned-http-qualification-complete',head:id.head,run:id.run,cases:terminal.cases,transactions:terminal.transactions,checks,functional_only:true,pre_router_containment:false,production_ready:false,cleanup:'exact-owned-zero'})+'\n');
 }catch(error){failure=error;process.stderr.write(JSON.stringify({event:'owned-http-failure',stage,reason:sha(String(error.message))})+'\n');}
 finally{for(const jar of Object.values(jars))jar.clear();if(!cleaned){let bad=false;const deadline=clock.cleanupDeadline();try{await stopChild(child,ended,left(deadline));saveOwnership(owner,null);}catch{bad=true;}try{const fixed=s??storageFor(owner,'0'.repeat(64),process.env.GITHUB_SHA).storage;db.cleanup(fixed,left(deadline),LIMITS.diagnostic_bytes-diagnosticBytes);}catch{bad=true;}if(!bad){left(deadline);removeOwnedStage(owner);process.stdout.write('OWNED_HTTP_CLEANUP exact-owned-zero\n');}else{process.stderr.write('OWNED_HTTP_CLEANUP unknown\n');failure=Error('Owned cleanup unknown');}}}
 if(failure)throw Error('Owned functional HTTP qualification refused');
}
async function recover(){
 const o=readOwnership(process.env);if(!o){process.stdout.write('OWNED_HTTP_RECOVERY absent\n');return;}
 const deadline=performance.now()+LIMITS.cleanup_ms,left=()=>{const n=Math.floor(deadline-performance.now());need(n>0,'recovery-deadline');return n;};
 if(o.server&&processStillOwned(o.server)){
  verifyRecoveryProcess(o);process.kill(o.server.pid,'SIGTERM');while(processStillOwned(o.server)&&left()>500)await pause(10);
  if(processStillOwned(o.server)){verifyRecoveryProcess(o);process.kill(o.server.pid,'SIGKILL');while(processStillOwned(o.server)){left();await pause(10);}}
 }
 const db=new OwnedHttpDatabase(o),s=storageFor(o,'0'.repeat(64),process.env.GITHUB_SHA).storage;db.cleanup(s,left());left();removeOwnedStage(o);process.stdout.write('OWNED_HTTP_RECOVERY exact-owned-zero\n');
}
if(process.argv[1]===fileURLToPath(import.meta.url))(process.argv.length===3&&process.argv[2]==='--cleanup'?recover():process.argv.length===2?main():Promise.reject(Error('Fixed invocation required'))).catch(()=>{process.stderr.write('OWNED_HTTP refused or cleanup unknown\n');process.exitCode=1;});
