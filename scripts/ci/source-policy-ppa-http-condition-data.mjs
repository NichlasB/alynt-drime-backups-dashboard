// Pure condition projections. Never a request reader,callback,storage or native adapter.
import {createHash} from 'node:crypto';
import {record,hex,integer,LIMITS,BRIDGE} from './source-policy-http-profile.mjs';
import {HTTP_FLOW} from './source-policy-http-supervisor.mjs';
import {dataList,freezeData} from './source-policy-ppa-function-data.mjs';
import {CONDITION_CATALOG as C,CONDITION_CATALOG_DIGEST} from './source-policy-ppa-http-condition-catalog.mjs';
export const CONDITION_MODEL='ppa-http-condition-data-v1';
export const CONDITION_EXECUTION_ENABLED=false;
const sha=value=>createHash('sha256').update(JSON.stringify(value)).digest('hex');
const need=(ok,label)=>{if(!ok)throw Error('PPA condition DATA refused: '+label);};
const CLOSED={native_admitted:false,runtime_grant:false,http_qualified:false,production_ready:false,
  source_observed:false,pre_bootstrap_checked:false,effective_charset_observed:false,
  consumer_closure_verified:false,dependency_closure_verified:false};
const domains=['setup','bootstrap','auth'];
function sourceData(value){
  const rows=dataList(value,24);need(rows?.length===C.sources.length,'source-members');
  return rows.map((item,i)=>{
    const row=record(item,['path','size','digest']),pin=C.sources[i];
    need(row&&row.path===pin.path&&row.size===pin.size&&row.digest===pin.digest,'source-identity');return row;
  });
}
function originData(value){
  const row=record(value,['model','run','head','expected_head','image','profile','core_commit','catalog','role','exchange','started','deadline','used','sources']);
  need(row&&row.model===CONDITION_MODEL&&hex(row.run,32)&&hex(row.head,40)&&row.expected_head===row.head&&hex(row.image,64)
    &&row.profile===C.profile&&row.core_commit===C.core_commit&&row.catalog===CONDITION_CATALOG_DIGEST
    &&['installer','initializer','server'].includes(row.role),'origin');
  need(sha(HTTP_FLOW)===C.flow_digest,'fixed-flow');
  need(row.role==='server'?integer(row.exchange,25)&&row.exchange>0:row.exchange===null,'exchange-role');
  const width=row.role==='server'?LIMITS.http_ms:LIMITS.setup_ms;
  need(integer(row.started,LIMITS.job_ms)&&row.deadline===row.started+width&&row.deadline<=LIMITS.job_ms-LIMITS.cleanup_ms,'original-phase-credit');
  const used=record(row.used,domains);need(used&&domains.every(k=>integer(used[k],LIMITS[k+'_sql'])),'sql-origin');
  return freezeData({...row,used,sources:sourceData(row.sources)});
}
export function conditionRequestSpec(role,exchange){
  need(sha(HTTP_FLOW)===C.flow_digest,'fixed-flow'); // Exported API also refuses unreviewed flow changes.
  need(['installer','initializer','server'].includes(role),'role');
  if(role!=='server'){
    need(exchange===null,'cli-exchange');
    return freezeData({kind:'cli-no-request',jar:null,method:'GET',target:role==='installer'?'/wp-admin/install.php':'/',query_names:[],body_names:[]});
  }
  need(integer(exchange,25)&&exchange>0,'exchange');
  const step=HTTP_FLOW[exchange-1],login=step.kind.startsWith('login-');
  const method=['login-submit','submit'].includes(step.kind)?'POST':'GET';
  const names=step.kind==='login-submit'?['log','pwd','wp-submit','redirect_to','testcookie']:
    step.kind==='submit'?['_wpnonce','dashboard_site_id','source_key','source_mode','source_policy_revision']:[];
  if(step.intent==='missing-nonce')names.shift();
  if(step.intent==='compound-nonce')names[0]='_wpnonce[]';
  if(step.intent==='compound-site')names[1]='dashboard_site_id[]';
  return freezeData({kind:step.kind,jar:step.jar,method,target:login?'/wp-login.php':BRIDGE+(step.kind==='nonce-control'?'?control=wrong-action':''),
    query_names:step.kind==='nonce-control'?['control']:[],body_names:names});
}
function namesData(value,expected){
  const rows=dataList(value,8);need(rows&&rows.length===expected.length&&rows.every((name,i)=>name===expected[i]),'request-names');return rows;
}
function cookieNames(value,role){
  const rows=dataList(value,16);need(rows&&(role==='server'||rows.length===0),'cookie-members');
  let prior='';for(const name of rows){
    need(typeof name==='string'&&/^(?:wordpress_test_cookie|wordpress(?:_sec|_logged_in)?_[a-f0-9]{32}|wp-settings-(?:time-)?[1-3])$/.test(name)
      &&name>prior,'cookie-name');prior=name;
  }return rows;
}
function hookData(value){
  const hooks=dataList(value,7);need(hooks?.length===C.hooks.length,'hook-members');
  for(let i=0;i<hooks.length;i++){
    const hook=record(hooks[i],['name','callbacks']),callbacks=hook&&dataList(hook.callbacks,0);
    need(hook&&hook.name===C.hooks[i]&&callbacks?.length===0,'selected-hook-projection');
  }
}
function charsetState(value,phase){
  const row=record(value,['phase','at','key','value','autoload','rows','cache','hooks']);
  need(row&&row.phase===phase&&row.key==='blog_charset'&&row.value==='UTF-8'&&row.autoload==='on'&&row.rows===1
    &&[null,'UTF-8'].includes(row.cache),'charset-state');
  hookData(row.hooks);
  return {at:row.at,anchor:sha({key:row.key,value:row.value,autoload:row.autoload,rows:row.rows})};
}
export class PpaHttpConditionData {
  #origin;#binding;#state='origin';#last;#producer;#checked;
  constructor(value){this.#origin=originData(value);this.#binding=sha(this.#origin);this.#last=this.#origin.started;}
  binding(){return freezeData({model:CONDITION_MODEL,binding:this.#binding,catalog:CONDITION_CATALOG_DIGEST});}
  #require(ok,label){if(!ok){this.#state='failed';this.#producer=null;throw Error('PPA condition DATA refused: '+label);}}
  #at(at){this.#require(integer(at,LIMITS.job_ms)&&at>=this.#last&&at<this.#origin.deadline,'phase-time');this.#last=at;}
  producer(value,at){
    this.#require(this.#state==='origin','producer-order');this.#at(at);
    try{
      const row=record(value,['binding','kind','jar','method','target','cookie_names','query_names','body_names']);
      const spec=conditionRequestSpec(this.#origin.role,this.#origin.exchange);
      need(row&&row.binding===this.#binding&&['kind','jar','method','target'].every(k=>row[k]===spec[k]),'producer-binding');
      namesData(row.body_names,spec.body_names); // Validate,do not retain unneeded form-field metadata.
      this.#producer=freezeData({cookies:cookieNames(row.cookie_names,this.#origin.role),query:namesData(row.query_names,spec.query_names)});
      this.#state='produced';return freezeData({producer_data_complete:true,...CLOSED});
    }catch{this.#require(false,'producer-projection');}
  }
  beforeBootstrap(value,at){
    this.#require(this.#state==='produced','receiver-order');this.#at(at);
    try{
      const row=record(value,['binding','phase','method','target','cookie_names','query_names','body_fields']);
      const spec=conditionRequestSpec(this.#origin.role,this.#origin.exchange);
      need(row&&row.binding===this.#binding&&row.phase==='before-stock-bootstrap'&&row.method===spec.method&&row.target===spec.target,'receiver-binding');
      const cookies=cookieNames(row.cookie_names,this.#origin.role),query=namesData(row.query_names,spec.query_names);
      need(JSON.stringify(cookies)===JSON.stringify(this.#producer.cookies)&&JSON.stringify(query)===JSON.stringify(this.#producer.query),'producer-receiver-join');
      const fields=dataList(row.body_fields,8);need(fields?.length===spec.body_names.length,'parsed-body-members');
      for(let i=0;i<fields.length;i++){
        const field=record(fields[i],['name','kind']),name=spec.body_names[i];
        need(field&&field.name===name.replace(/\[\]$/,'')&&field.kind===(name.endsWith('[]')?'array':'scalar'),'parsed-body-shape');
      }
      this.#checked=at;this.#state='checked';return freezeData({pre_bootstrap_data_complete:true,...CLOSED});
    }catch{this.#require(false,'receiver-projection');}
  }
  charset(value,at){
    this.#require(this.#state==='checked','charset-order');this.#at(at);
    try{
      const row=record(value,['binding','before','after','effect','baseline']);need(row&&row.binding===this.#binding,'charset-binding');
      const role=this.#origin.role,before=charsetState(row.before,role==='installer'?'after-owned-install':role==='initializer'?'before-initializer':'before-request');
      const after=charsetState(row.after,role==='installer'?'after-installer-shutdown':role==='initializer'?'after-initializer':'after-request-shutdown');
      need(integer(before.at,LIMITS.job_ms)&&integer(after.at,LIMITS.job_ms)&&before.at>=this.#origin.started
        &&before.at<=after.at&&after.at<=at&&(role==='installer'?before.at>=this.#checked:before.at<=this.#checked)&&after.at>=this.#checked,'charset-time');
      const effect=record(row.effect,['phase','kind','key','encoding','value','at','hooks','cache','sql','protected_reads','protected_writes','protected_cache','unknown_changes']);
      need(effect&&effect.kind==='stock-get-option'&&effect.key==='blog_charset'&&effect.encoding===null&&effect.value==='UTF-8'
        &&effect.phase==='stock-option-call'&&[null,'UTF-8'].includes(effect.cache)
        &&integer(effect.at,LIMITS.job_ms)&&effect.at>=Math.max(before.at,this.#checked)&&effect.at<=after.at
        &&effect.protected_reads===0&&effect.protected_writes===0&&effect.protected_cache===false&&effect.unknown_changes===0,'stock-option-effect');
      hookData(effect.hooks); // Selected slots at the modeled call boundary,not only earlier/later snapshots.
      const sql=record(effect.sql,domains);need(sql,'sql-counts');
      for(const k of domains){
        need(integer(sql[k],LIMITS[k+'_sql'])&&sql[k]+this.#origin.used[k]<=LIMITS[k+'_sql']
          &&(role==='server'?k!=='setup'||sql[k]===0:k==='setup'||sql[k]===0),'sql-credit-domain');
      }
      const baseline=record(row.baseline,['at','digest','charset_anchor']);
      need(before.anchor===after.anchor&&baseline&&hex(baseline.digest,64)&&baseline.charset_anchor===after.anchor
        &&integer(baseline.at,LIMITS.job_ms)&&baseline.at>=after.at&&baseline.at<=at,'charset-baseline');
      this.#state='complete';return freezeData({condition_data_complete:true,binding:this.#binding,
        producer_data_complete:true,pre_bootstrap_data_complete:true,charset_effect_data_complete:true,
        option_effect:'required-stock-read',charset_anchor:after.anchor,...CLOSED});
    }catch{this.#require(false,'charset-projection');}
  }
  uncertain(){this.#state='failed';this.#producer=null;}
}
