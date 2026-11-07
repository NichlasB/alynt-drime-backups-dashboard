// Virtual original-job elapsed milliseconds ONLY (not Date.now). No callbacks/IO.
import {HTTP_PROFILE,LIMITS,record,integer,hex,identityData,profileMatches,storageMatches,manifestMatches} from './source-policy-http-profile.mjs';
import {route,ledgerMatches,responseMatches,caseMatches,HTTP_CASES} from './source-policy-http-contract.mjs';
export class HttpDataLifecycle {
 #id;#challenge;#state='fresh';#created;#last;#setupAt;#started;#requestAt;#cleanupAt;
 #manifest;#pid;#start;#kind;#pending=null;#index=0;#transactions=0;#refused=false;
 #policy=null;#sentinel=null;#pendingAfter=null;
 constructor(identity,at,challenge){
  const id=identityData(identity);
  if(!id||!integer(at,LIMITS.job_ms-1)||!hex(challenge,32)||challenge===id.run)throw Error('HTTP DATA identity refused');
  this.#id=id;this.#created=at;this.#last=at;this.#challenge=challenge;
 }
 get state(){return this.#state;}
 #require(condition){
  if(condition)return;
  if(!['closed','cleanup-unknown'].includes(this.#state)){this.#state='failed';this.#refused=true;this.#cleanupAt??=this.#last;}
  throw Error('HTTP DATA model refused');
 }
 #time(at){this.#require(integer(at,LIMITS.job_ms-1)&&at>=this.#last);this.#last=at;}
 #httpTime(at){this.#time(at);this.#require(at-this.#started<LIMITS.http_ms);}
 admit(candidate,storage,manifest,expectation,at){
  this.#time(at);this.#require(this.#state==='fresh'&&at-this.#created<LIMITS.setup_ms);
  this.#require(profileMatches(candidate)&&storageMatches(storage,this.#id)&&manifestMatches(manifest,expectation));
  this.#manifest=expectation.digest;this.#state='profiled';
 }
 setup(ledger,at){
  this.#time(at);this.#require(this.#state==='profiled'&&at-this.#created<LIMITS.setup_ms&&ledgerMatches(ledger,'setup'));
  this.#setupAt=at;this.#state='prepared';
 }
 #owned(value){
  return value&&value.run===this.#id.run&&value.head===this.#id.head&&value.image===this.#id.image&&value.manifest===this.#manifest&&integer(value.pid,2147483647)&&value.pid>0&&hex(value.start,64)&&value.alive===true;
 }
 server(value,profile,at){
  this.#time(at);const row=record(value,['run','head','image','pid','start','alive','manifest']);
  this.#require(this.#state==='prepared'&&at-this.#setupAt<LIMITS.transaction_ms&&LIMITS.job_ms-at>=LIMITS.http_ms+LIMITS.cleanup_ms);
  this.#require(this.#owned(row)&&profileMatches(profile));this.#pid=row.pid;this.#start=row.start;this.#started=at;this.#state='http';
 }
 begin(meta,kind,index,at){
  this.#httpTime(at);this.#require(this.#state==='http'&&this.#transactions<LIMITS.transactions&&['login-form','login-submit','view','submit','nonce-control'].includes(kind)&&route(meta,this.#challenge)===kind);
  this.#require(index===null?kind!=='submit':index===this.#index&&integer(index,HTTP_CASES.length-1)&&kind===(index===0?'view':'submit'));
  this.#transactions++;this.#pending=index;this.#kind=kind;this.#requestAt=at;this.#state='response';
 }
 receive(envelope,result,expected,bootstrap,auth,at){
  this.#httpTime(at);this.#require(this.#state==='response'&&at-this.#requestAt<LIMITS.transaction_ms);
  this.#require(responseMatches(envelope,this.#kind)&&ledgerMatches(bootstrap,'bootstrap')&&ledgerMatches(auth,'auth'));
  if(this.#pending===null)this.#require(result===null&&expected===null);
  else{
   this.#require(caseMatches(result,expected,this.#pending));
   this.#require(expected.run===this.#id.run&&expected.head===this.#id.head&&expected.image===this.#id.image&&expected.root===this.#id.root&&envelope.status===result.status);
   this.#require((this.#policy===null||result.before===this.#policy)&&(this.#sentinel===null||result.sentinel===this.#sentinel));
   this.#pendingAfter=result.after;this.#sentinel=result.sentinel;
  }
  this.#state='release';
 }
 release(value,at){
  this.#httpTime(at);const row=record(value,['run','head','image','pid','start','alive','manifest','scope','sessions','transactions']);
  this.#require(this.#state==='release'&&at-this.#requestAt<LIMITS.transaction_ms);
  this.#require(this.#owned(row)&&row.pid===this.#pid&&row.start===this.#start&&row.scope==='all-server'&&row.sessions===0&&row.transactions===0);
  if(this.#pending!==null){this.#index++;this.#policy=this.#pendingAfter;}
  this.#pendingAfter=null;
  this.#pending=null;this.#kind=null;
  this.#state=this.#index===HTTP_CASES.length?'cleanup':'http';if(this.#state==='cleanup')this.#cleanupAt=at;
 }
 fail(at){this.#time(at);this.#require(!['closed','cleanup-unknown'].includes(this.#state));this.#refused=true;this.#cleanupAt??=at;this.#state='failed';}
 close(value,at){
  if(['closed','cleanup-unknown'].includes(this.#state))throw Error('HTTP DATA cleanup terminal cannot replay');
  const row=record(value,['run','head','image','ownership','processes','listeners','containers','schemas','accounts','files','sessions','transactions']);
  try{
   this.#time(at);this.#require(['cleanup','failed'].includes(this.#state)&&at-this.#cleanupAt<LIMITS.cleanup_ms);
   this.#require(row&&row.run===this.#id.run&&row.head===this.#id.head&&row.image===this.#id.image&&row.ownership===true&&['processes','listeners','containers','schemas','accounts','files','sessions','transactions'].every(k=>row[k]===0));
  }catch{this.#state='cleanup-unknown';throw Error('HTTP DATA cleanup unconfirmed');}
  this.#state='closed';
  return Object.freeze({event:'http-data-model-closed',model:HTTP_PROFILE.model,run:this.#id.run,head:this.#id.head,image:this.#id.image,cases:this.#index,transactions:this.#transactions,completed:!this.#refused&&this.#index===HTTP_CASES.length,runtime_grant:false,production_ready:false,qualification:'not-established'});
 }
}
