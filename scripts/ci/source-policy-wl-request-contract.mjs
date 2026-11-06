// Fixed W/L proof DATA only. Importing never acquires, launches or connects.
import {createHash} from 'node:crypto';
export const REQUEST_CASES=Object.freeze(['view','save','noop','stale','cookie-missing','cookie-malformed','cookie-expired','cookie-tampered','cookie-user','cookie-destroyed','incapable','nonce-action','nonce-user','nonce-session','nonce-empty','nonce-compound','nonce-expired','site-compound','site-padded','revision-bad','mode-compound','record-missing','record-wrong']);
const hex=(value,n)=>typeof value==='string'&&new RegExp('^[a-f0-9]{'+n+'}$').test(value);
export function wlLinuxJob(env){
  return Boolean(env&&typeof env==='object'&&!Array.isArray(env)&&env.QUALIFICATION_GENUINE_REQUEST_APPROVED==='true'&&env.GITHUB_ACTIONS==='true'&&env.GITHUB_EVENT_NAME==='workflow_dispatch'
	&&env.GITHUB_WORKFLOW_REF==='NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-linux-qualification.yml@refs/heads/master'
    &&env.GITHUB_REPOSITORY==='NichlasB/alynt-drime-backups-dashboard'&&env.GITHUB_REF==='refs/heads/master'&&env.GITHUB_ACTOR==='NichlasB'
    &&env.GITHUB_RUN_ATTEMPT==='1'&&typeof env.GITHUB_RUN_ID==='string'&&/^[1-9][0-9]*$/.test(env.GITHUB_RUN_ID)&&hex(env.GITHUB_SHA,40)&&env.EXPECTED_SHA===env.GITHUB_SHA);
}
export function wlRequestFailure(raw,allowInclude=false){
  if(typeof raw!=='string'||Buffer.byteLength(raw)>256)return null;
  let include=null;
  if(raw.startsWith('ADBWLIC1|')){
    if(allowInclude!==true)return null;
    const lines=raw.split('\n');if(lines.length!==3||lines[2]!=='')return null;
    const p=/^ADBWLIC1\|l\|0\|audit\|([0-9]|[1-9][0-9]|[1-4][0-9]{2}|50[0-9]|51[01])\|(repo|core|foreign|unresolved)\|(scope|size|stat)\|([a-f0-9]{64})$/.exec(lines[0]);
    if(!p||(p[3]!=='scope'&&!['repo','core'].includes(p[2])))return null;
    include={ordinal:Number(p[1]),origin:p[2],reason:p[3],path_digest:p[4]};raw=lines[1]+'\n';
  }
  const m=/^ADBWLRF1\|([wl])\|child\|([0-9]|1[0-9]|2[0-2])\|(audit|ambient|setup|auth|entry|verify|cleanup)\|(RuntimeException|Error|TypeError|ValueError|ParseError|ArgumentCountError|ReflectionException|other)\|([a-f0-9]{64})\n(?![\s\S])/.exec(raw);
  if(!m)return null;
  if(include&&(m[1]!=='l'||m[2]!=='0'||m[3]!=='audit'||(m[5]!==createHash('sha256').update('finite actual include closure').digest('hex')&&!(include.reason==='stat'&&m[5]==='0'.repeat(64)))))return null;
  const fact={profile:m[1],case_index:Number(m[2]),stage:m[3],exception:m[4],assertion:m[5]};
  return include?{...fact,include}:fact;
}
export function parseRequestWire(raw){
  if(typeof raw!=='string'||Buffer.byteLength(raw)>4096||!/^\{[^\r\n]+\}\n$/.test(raw))return null;
  try{const row=JSON.parse(raw);return JSON.stringify(row)+'\n'===raw?row:null;}catch{return null;}
}
export function wlEnvelope(env,image,mode,modules){
  if(mode==='audit'&&modules===undefined)modules='0'.repeat(64);
  if(!wlLinuxJob(env)||!hex(image,64)||!hex(modules,64)||!['audit','proof'].includes(mode))throw Error('Exact approved request job required');
  const run=createHash('sha256').update(env.GITHUB_RUN_ID+':1').digest('hex').slice(0,32);
  return {WL_REQUEST_PROFILE:'l',WL_REQUEST_APPROVED:'true',WL_REQUEST_HEAD:env.GITHUB_SHA,WL_REQUEST_RUN:run,WL_REQUEST_PREFIX:'adbm_'+run.slice(0,12)+'_',WL_REQUEST_IMAGE:image,WL_REQUEST_MODULES:modules,WL_REQUEST_MODE:mode};
}
export function validRequestAudit(row,profile,run){
  return Boolean(row&&Object.keys(row).join(',')==='event,profile,run,image,modules,checks,native_connections,sql_queries'&&row.event==='audit'
    &&['w','l'].includes(profile)&&row.profile===profile&&hex(run,32)&&row.run===run&&hex(row.image,64)&&hex(row.modules,64)
    &&Number.isSafeInteger(row.checks)&&row.checks>0&&row.checks<=99999&&row.native_connections===0&&row.sql_queries===0);
}
export function validRequestCase(row,profile,run,image,index,pid){
  return Boolean(row&&Object.keys(row).join(',')==='event,profile,run,image,pid,index,checks,setup,auth,records,connects,commands,closed'
    &&row.event==='request-closed'&&['w','l'].includes(profile)&&row.profile===profile&&hex(run,32)&&row.run===run&&hex(image,64)&&row.image===image
    &&Number.isSafeInteger(pid)&&pid>0&&row.pid===pid&&Number.isSafeInteger(index)&&index>=0&&index<REQUEST_CASES.length&&row.index===index&&row.closed===true
    &&['checks','setup','auth','records','connects','commands'].every(k=>Number.isSafeInteger(row[k])&&row[k]>=0)
    &&row.checks>0&&row.checks<=99999&&row.setup<=256&&row.auth<=256&&row.records<=2&&row.connects<=2&&row.commands<=9999
    &&(index<4?row.connects===(index===0?1:2)&&row.records===row.connects&&row.commands>row.records:row.connects===0&&row.records===(index>=21?1:0)&&row.commands===row.records));
}
// Existing L instance/account only; identifiers cannot select arbitrary resources.
export function wlSchemaSQL(prefix){
  if(typeof prefix!=='string'||!/^adbm_[a-f0-9]{12}_$/.test(prefix))throw Error('Exact owned request prefix required');
  const u=prefix+'users',m=prefix+'usermeta',s=prefix+'alynt_drime_dashboard_sites';
  return `CREATE TABLE adb_owned.${u} (ID bigint unsigned NOT NULL,user_login varchar(60) NOT NULL,user_pass varchar(255) NOT NULL,user_nicename varchar(50) NOT NULL,user_email varchar(100) NOT NULL,user_url varchar(100) NOT NULL,user_registered datetime NOT NULL,user_activation_key varchar(255) NOT NULL,user_status int NOT NULL,display_name varchar(250) NOT NULL,PRIMARY KEY(ID),UNIQUE KEY user_login(user_login)) ENGINE=InnoDB; CREATE TABLE adb_owned.${m} (umeta_id bigint unsigned NOT NULL AUTO_INCREMENT,user_id bigint unsigned NOT NULL,meta_key varchar(255),meta_value longtext,PRIMARY KEY(umeta_id),KEY user_id(user_id),KEY meta_key(meta_key(191))) ENGINE=InnoDB; CREATE TABLE adb_owned.${s} (id bigint unsigned NOT NULL,PRIMARY KEY(id)) ENGINE=InnoDB; ${[u,m,s].map(t=>`GRANT SELECT,INSERT,UPDATE,DELETE ON adb_owned.${t} TO 'adbp'@'localhost';`).join(' ')}`;
}
// Fixed 23-case supervision, tested only against isolated IO until native approval.
export async function requestBatch(io,identity){
  if(!identity||!['w','l'].includes(identity.profile)||!hex(identity.run,32)||!hex(identity.image,64))throw Error('Bound identity required');
  const started=io.now();let checks=0;
  for(let index=0;index<REQUEST_CASES.length;index++){
    const remaining=20000-(io.now()-started);if(remaining<=0)throw Error('Original request deadline exhausted');
    const child=await io.launch(index,remaining);
    let primary=null;
    try{
      if(!child.alive()||!validRequestCase(child.row,identity.profile,identity.run,identity.image,index,child.pid))throw Error('Exact held request terminal refused');
      await io.reconcile(20000-(io.now()-started));
      if(!child.alive())throw Error('Child exited during held-live reconciliation');
      const left=20000-(io.now()-started);if(left<=0)throw Error('Original request deadline exhausted');
      await child.release('ACK:'+identity.run+':'+identity.image+':'+index+'\n',left);
      await io.reconcile(20000-(io.now()-started));checks+=child.row.checks;
      if(checks>99999||io.now()-started>=20000)throw Error('Request aggregate/deadline refused');
    }catch(error){primary=error;throw error;}
    finally{try{await child.cleanup();}catch(error){throw primary?new AggregateError([primary,error],'Request refusal and exact owned cleanup unconfirmed'):error;}}
  }
  return {event:'request-batch-closed',profile:identity.profile,run:identity.run,image:identity.image,cases:23,checks,groups:9,independent_live:'zero',independent_post_exit:'zero',production_ready:false};
}
