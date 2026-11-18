// Dormant initialized-fixture DATA only. Supplied receipts are not native proof.
import {createHash} from 'node:crypto';
import {HTTP_PROFILE,LIMITS,record,integer,hex,identityData,storageMatches,manifestMatches} from './source-policy-http-profile.mjs';

export const INITIALIZATION_EXECUTION_ENABLED=false;
export const INITIALIZED_HTTP_PROFILE=Object.freeze({...HTTP_PROFILE,model:'owned-http-l714-initialized-v2'});
const bindingKeys=['model','run','head','image','owner','manifest','anchors'];
const ticketKeys=[...bindingKeys,'role','setup_sql_used','sql_remaining','setup_started','setup_deadline'];
const completionKeys=[...ticketKeys,'sql','full_bootstrap','installing','short_init','protected_reads','protected_writes','owner_allocations','session_writes','mail','outbound','file_writes','source_unchanged','anchors_unchanged'];
const same=(row,expected)=>row!==null&&Object.keys(expected).every(key=>row[key]===expected[key]);

/** Single-use setup protocol; original elapsed clock and SQL credits never reset.
 * No worker, SQL executor, authentication, cleanup authority or HTTP lifecycle.
 */
export class InitializedFixtureData{
 #binding;#state='installing';#start;#last;#installed=0;#initialized=0;#ticket;#baseline=null;
 constructor(value,at){
  const row=record(value,['identity','profile','storage','manifest','expectation','anchors']);
  const id=row&&identityData(row.identity),profile=row&&record(row.profile,Object.keys(INITIALIZED_HTTP_PROFILE));
  const expectation=row&&record(row.expectation,['files','bytes','digest']);
  if(!row||!id||!same(profile,INITIALIZED_HTTP_PROFILE)||!storageMatches(row.storage,id)||!expectation||!manifestMatches(row.manifest,expectation)||!hex(row.anchors,64)||!integer(at,LIMITS.job_ms-LIMITS.cleanup_ms-LIMITS.setup_ms))throw Error('initialization-admission');
  this.#start=at;this.#last=at;
  this.#binding=Object.freeze({model:INITIALIZED_HTTP_PROFILE.model,run:id.run,head:id.head,image:id.image,owner:createHash('sha256').update(JSON.stringify(id)).digest('hex'),manifest:expectation.digest,anchors:row.anchors});
 }
 #require(ok,label){if(!ok){this.#state='failed';throw Error(label);}}
 #step(state,at){
  this.#require(this.#state===state,'initialization-order');
  this.#require(integer(at,Number.MAX_SAFE_INTEGER)&&at>=this.#last&&at<this.#start+LIMITS.setup_ms,'initialization-time');this.#last=at;
 }
 binding(){return this.#binding;}
 installed(value,at){
  this.#step('installing',at);
  const row=record(value,[...bindingKeys,'role','sql','exited','sessions','transactions','ddl_sealed','fixtures_verified']);
  this.#require(same(row,{...this.#binding,role:'installer',exited:true,sessions:0,transactions:0,ddl_sealed:true,fixtures_verified:true})&&integer(row.sql,LIMITS.setup_sql-1)&&row.sql>0,'initialization-installer');
  this.#installed=row.sql;this.#state='installed';
 }
 begin(at){
  this.#step('installed',at);
  this.#ticket=Object.freeze({...this.#binding,role:'post-install-bootstrap',setup_sql_used:this.#installed,sql_remaining:LIMITS.setup_sql-this.#installed,setup_started:this.#start,setup_deadline:this.#start+LIMITS.setup_ms});
  this.#state='initializing';return this.#ticket;
 }
 initialized(value,at){
  this.#step('initializing',at);
  const row=record(value,completionKeys);
  this.#require(same(row,{...this.#ticket,full_bootstrap:true,installing:false,short_init:false,protected_reads:0,protected_writes:0,owner_allocations:0,session_writes:0,mail:0,outbound:0,file_writes:0,source_unchanged:true,anchors_unchanged:true})&&integer(row.sql,this.#ticket.sql_remaining)&&row.sql>0,'initialization-receipt');
  this.#initialized=row.sql;this.#state='initialized';
 }
 released(value,at){
  this.#step('initialized',at);
  const row=record(value,[...bindingKeys,'role','exited','sessions','transactions','anchors_unchanged','source_unchanged']);
  this.#require(same(row,{...this.#binding,role:'initializer-release',exited:true,sessions:0,transactions:0,anchors_unchanged:true,source_unchanged:true}),'initialization-release');this.#state='released';
 }
 freeze(value,at){
  this.#step('released',at);
  const row=record(value,[...bindingKeys,'role','digest','all_options','all_cron','all_widgets','all_transients','non_session_usermeta']);
  this.#require(same(row,{...this.#binding,role:'full-invariant',all_options:true,all_cron:true,all_widgets:true,all_transients:true,non_session_usermeta:true})&&hex(row.digest,64),'initialization-baseline');
  this.#baseline=row.digest;this.#state='ready';
 }
 result(){return Object.freeze({state:this.#state,total_setup_sql:this.#installed+this.#initialized,baseline:this.#state==='ready'?this.#baseline:null,isolated_only:true,runtime_grant:false,production_ready:false,http_qualified:false});}
}

/** Isolated callback ordering only; kind labels/receipts cannot confer authority.
 * A future owned native supervisor must implement independent effect/release and
 * failure cleanup proofs. This helper deliberately accepts no native adapter.
 */
export async function prepareInitializedFixture(io,value){
 const callbacks=record(io,['kind','now','install','initialize','reconcile','baseline']);
 if(!callbacks||callbacks.kind!=='isolated-initialization-io-v2'||['now','install','initialize','reconcile','baseline'].some(k=>typeof callbacks[k]!=='function'))throw Error('initialization-io');
 const model=new InitializedFixtureData(value,callbacks.now());
 model.installed(await callbacks.install(model.binding()),callbacks.now());
 const ticket=model.begin(callbacks.now());
 model.initialized(await callbacks.initialize(ticket),callbacks.now());
 model.released(await callbacks.reconcile(model.binding()),callbacks.now());
 model.freeze(await callbacks.baseline(model.binding()),callbacks.now());
 return model.result();
}
