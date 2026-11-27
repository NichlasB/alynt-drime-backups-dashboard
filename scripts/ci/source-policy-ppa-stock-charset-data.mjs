// Dormant claimed DATA lifecycle. No physical observer, request owner or execution grant.
import {createHash} from 'node:crypto';
import {record,hex,integer,LIMITS} from './source-policy-http-profile.mjs';
import {freezeData} from './source-policy-ppa-function-data.mjs';
import {conditionRequestSpec} from './source-policy-ppa-http-condition-data.mjs';
import {STOCK_CHARSET_CATALOG as C,STOCK_CHARSET_CATALOG_DIGEST} from './source-policy-ppa-stock-charset-catalog.mjs';
import {need,stockSources,stockHooks,charsetCache,charsetRow,typedDigest,baselineData,ANCHOR_MODEL,INVARIANT_MODEL} from './source-policy-ppa-stock-charset-projections.mjs';
export const STOCK_CHARSET_MODEL='ppa-stock-charset-condition-data-v2',STOCK_CHARSET_EXECUTION_ENABLED=false;
const digest=value=>createHash('sha256').update(JSON.stringify(value)).digest('hex');
const domains=['setup','bootstrap','auth'];
const CLOSED={native_admitted:false,runtime_grant:false,http_qualified:false,production_ready:false,
  source_observed:false,pre_bootstrap_checked:false,effective_charset_observed:false,anchors_observed:false,
  registration_lifetime_verified:false,cache_projection_observed:false,consumer_closure_verified:false,
  dependency_closure_verified:false,hook_lifetime:'unknown',cache_reader:'unknown'};
const phases=role=>role==='installer'?['after-owned-install','after-installer-shutdown']:
  role==='initializer'?['before-initializer','after-initializer']:['before-request','after-request-shutdown'];
function originData(value){
  const row=record(value,['model','run','head','expected_head','image','profile','core_commit','catalog','role','exchange','started','deadline','used','sources','baseline']);
  need(row&&row.model===STOCK_CHARSET_MODEL&&hex(row.run,32)&&hex(row.head,40)&&row.head===row.expected_head
    &&hex(row.image,64)&&row.profile===C.profile&&row.core_commit===C.core_commit&&row.catalog===STOCK_CHARSET_CATALOG_DIGEST,'origin');
  conditionRequestSpec(row.role,row.exchange); // Reuse unchanged finite role/exchange and fixed-flow refusal.
  const width=row.role==='server'?LIMITS.http_ms:LIMITS.setup_ms;
  need(integer(row.started,LIMITS.job_ms)&&row.deadline===row.started+width&&row.deadline<=LIMITS.job_ms-LIMITS.cleanup_ms,'original-time');
  const used=record(row.used,domains);need(used&&domains.every(k=>integer(used[k],LIMITS[k+'_sql'])),'original-sql');
  const baseline=row.role==='server'?baselineData(row.baseline):null;
  need(row.role==='server'?baseline.at<=row.started:row.baseline===null,'baseline-origin');
  return freezeData({...row,used,sources:stockSources(row.sources),baseline});
}
export class PpaStockCharsetData {
  #origin;#binding;#last;#state='origin';#before;#after;
  constructor(value){this.#origin=originData(value);this.#binding=digest(this.#origin);this.#last=this.#origin.started;}
  binding(){return freezeData({model:STOCK_CHARSET_MODEL,binding:this.#binding,catalog:STOCK_CHARSET_CATALOG_DIGEST});}
  #at(at){need(integer(at,LIMITS.job_ms)&&at>=this.#last&&at<this.#origin.deadline,'phase-time');this.#last=at;}
  #step(expected,at,work){
    try{need(this.#state===expected,'step-order');this.#at(at);return work();}
    catch{this.#state='failed';this.#before=null;this.#after=null;throw Error('PPA stock charset DATA refused');}
  }
  #snapshot(value,phase,at){
    const row=record(value,['binding','phase','rows','hooks','cache']);
    need(row&&row.binding===this.#binding&&row.phase===phase,'snapshot-binding');
    stockHooks(row.hooks);charsetCache(row.cache);return {at,charset:charsetRow(row.rows)};
  }
  before(value,at){return this.#step('origin',at,()=>{
    this.#before=this.#snapshot(value,phases(this.#origin.role)[0],at);
    need(this.#origin.role!=='server'||this.#before.charset.digest===this.#origin.baseline.charset.digest,'frozen-row-origin');
    this.#state='before';return freezeData({before_data_complete:true,...CLOSED});
  });}
  effect(value,at){return this.#step('before',at,()=>{
    const row=record(value,['binding','phase','key','encoding','value','installing','hooks','cache','sql','protected_reads','protected_writes','unknown_changes']);
    need(row&&row.binding===this.#binding&&row.phase==='stock-option-call'&&row.key==='blog_charset'
      &&row.encoding===null&&row.value==='UTF-8'&&row.installing===false&&row.protected_reads===0
      &&row.protected_writes===0&&row.unknown_changes===0,'stock-effect');
    stockHooks(row.hooks);const cache=charsetCache(row.cache);
    const sql=record(row.sql,domains);need(sql,'sql-shape');
    for(const k of domains)need(integer(sql[k],LIMITS[k+'_sql'])&&sql[k]+this.#origin.used[k]<=LIMITS[k+'_sql']
      &&(this.#origin.role==='server'?k!=='setup'||sql[k]===0:k==='setup'||sql[k]===0),'sql-credit-domain');
    need(cache.alloptions!==null||cache.options!==null||domains.some(k=>sql[k]>0),'cold-cache-stock-read');
    this.#state='effect';return freezeData({effect_data_complete:true,option_effect:'required-stock-read',
      effective_value_basis:'source-derived-DATA-only',...CLOSED});
  });}
  after(value,at){return this.#step('effect',at,()=>{
    this.#after=this.#snapshot(value,phases(this.#origin.role)[1],at);
    need(this.#before.charset.digest===this.#after.charset.digest,'same-row-anchor');
    this.#state='after';return freezeData({after_data_complete:true,...CLOSED});
  });}
  #point(value,index){
    const row=record(value,['phase','at','charset','anchor','invariant']);
    need(row&&row.phase===phases(this.#origin.role)[index]&&integer(row.at,LIMITS.job_ms)
      &&row.at>=this.#origin.started&&row.at<this.#origin.deadline,'parent-phase');
    const charset=typedDigest(row.charset,this.#before.charset.model),anchor=typedDigest(row.anchor,ANCHOR_MODEL);
    need(charset.digest===this.#before.charset.digest,'parent-row-join');
    if(this.#origin.role==='server'){
      const invariant=typedDigest(row.invariant,INVARIANT_MODEL);
      need(invariant.digest===this.#origin.baseline.invariant.digest&&anchor.digest===this.#origin.baseline.anchor.digest,'frozen-baseline-join');
    }else need(row.invariant===null,'pre-freeze-domain');
    return {...row,charset,anchor};
  }
  join(value,at){return this.#step('after',at,()=>{
    const row=record(value,['model','binding','before','after','release','baseline']);
    need(row&&row.model==='ppa-charset-anchor-join-data-v2'&&row.binding===this.#binding,'join-binding');
    const before=this.#point(row.before,0),after=this.#point(row.after,1),release=record(row.release,
      ['phase','at','scope','sessions','transactions','stock_shutdown','source_unchanged','exit_code']);
    need(release&&release.phase===(this.#origin.role==='server'?'request-stock-shutdown':'child-exit')
      &&release.scope==='all-server'&&release.sessions===0&&release.transactions===0&&release.stock_shutdown===true
      &&release.source_unchanged===true&&release.exit_code===(this.#origin.role==='server'?null:0),'release-projection');
    need(integer(release.at,LIMITS.job_ms)&&release.at>=this.#after.at&&release.at<=after.at
      &&before.at<=this.#before.at&&after.at<=at&&before.anchor.digest===after.anchor.digest,'parent-order-anchor');
    let baselineLink='not-yet-frozen';
    if(this.#origin.role==='initializer'){
      const baseline=baselineData(row.baseline);
      need(baseline.at>=after.at&&baseline.at<=at&&baseline.charset.digest===after.charset.digest
        &&baseline.anchor.digest===after.anchor.digest,'new-baseline-join');baselineLink='post-initializer-DATA';
    }else if(this.#origin.role==='server'){
      const baseline=baselineData(row.baseline);
      need(digest(baseline)===digest(this.#origin.baseline),'same-frozen-baseline');baselineLink='existing-frozen-DATA';
    }else need(row.baseline===null,'installer-not-frozen');
    this.#state='complete';return freezeData({stock_projection_data_complete:true,anchor_join_data_complete:true,
      binding:this.#binding,charset:this.#before.charset,baseline_link:baselineLink,option_effect:'required-stock-read',
      effective_value_basis:'source-derived-DATA-only',...CLOSED});
  });}
  uncertain(){this.#state='failed';this.#before=null;this.#after=null;}
}
