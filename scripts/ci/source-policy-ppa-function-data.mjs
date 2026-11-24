// Socket-free function provenance DATA. No core loading, callbacks or native grant.
import {record,hex} from './source-policy-http-profile.mjs';
import {SOURCE_CATALOG,SOURCE_CATALOG_DIGEST} from './source-policy-ppa-function-catalog.mjs';

export const FUNCTION_MODEL='ppa-function-preparation-v2';
export const CORE_MEMBERS=Object.freeze(SOURCE_CATALOG.anchors.map(row=>row.path));
export const LAUNCH_ROLES=SOURCE_CATALOG.roles;
export const HTTP_ROLES=SOURCE_CATALOG.http_roles;
export const BASE_FUNCTIONS=Object.freeze(['json_encode','json_decode','hash','hash_equals','hash_hmac','random_bytes','preg_match','serialize','unserialize','hrtime','hash_algos','hash_hmac_algos']);
const AUTH_FUNCTIONS=['password_hash','password_verify','password_needs_rehash','password_algos','random_int'];
const need=(ok,label)=>{if(!ok)throw Error('PPA function DATA refused: '+label);};

export function freezeData(value){
  if(value&&typeof value==='object'){for(const item of Object.values(value))freezeData(item);Object.freeze(value);}
  return value; // Only code-created validated copies,never raw caller objects.
}
export function dataList(value,max=64){
  try{
    if(!Array.isArray(value)||Object.getPrototypeOf(value)!==Array.prototype)return null;
    const n=Object.getOwnPropertyDescriptor(value,'length').value;
    if(!Number.isSafeInteger(n)||n<0||n>max||Reflect.ownKeys(value).length!==n+1)return null;
    const copy=[];for(let i=0;i<n;i++){
      const item=Object.getOwnPropertyDescriptor(value,String(i));if(!item||!Object.hasOwn(item,'value'))return null;
      copy.push(item.value);
    }return copy;
  }catch{return null;}
}
export function coreSourceData(value){
  const rows=dataList(value,9);need(rows?.length===SOURCE_CATALOG.anchors.length,'core-sources');
  return freezeData(rows.map((item,i)=>{
    const row=record(item,['path','size','digest']),pin=SOURCE_CATALOG.anchors[i];
    need(row&&row.path===pin.path&&row.size===pin.size&&row.digest===pin.digest,'core-source-binding');return row;
  }));
}
export function functionRequirements(role,phase){
  need(LAUNCH_ROLES.includes(role)&&['pre-core','post-core'].includes(phase),'role-phase');
  need(phase!=='post-core'||HTTP_ROLES.includes(role),'post-core-role');
  const native=[...BASE_FUNCTIONS,...(role==='environment'?[]:AUTH_FUNCTIONS),
    ...(phase==='post-core'?SOURCE_CATALOG.fallback.native:[]),...(role==='server'&&phase==='post-core'?['getallheaders']:[])];
  return freezeData({native,core:phase==='post-core'?['mb_strlen','mb_substr']:[],
    absent:phase==='pre-core'?['ctype_digit','iconv','mb_strlen','mb_substr']:['ctype_digit','iconv']});
}
export function validateAlgorithmData(value,role){
  need(LAUNCH_ROLES.includes(role),'algorithm-role');
  const row=record(value,['hash','hmac','password']);need(row,'algorithm-shape');
  const expected=role==='environment'?SOURCE_CATALOG.environment_algorithms:SOURCE_CATALOG.algorithms;
  for(const domain of ['hash','hmac','password']){
    const names=dataList(row[domain],8);
    need(names&&names.length===expected[domain].length&&names.every((name,i)=>name===expected[domain][i]),'algorithm-'+domain);
  }
  return freezeData({algorithm_data_complete:true,native_admitted:false});
}
export function consumerRequirements(role,phase){
  functionRequirements(role,phase); // Reject aliases and unsupported role/phase pairs first.
  return freezeData((phase==='pre-core'?SOURCE_CATALOG.pre_core:SOURCE_CATALOG.post_core).map(use=>{
    const pin=use.anchor===null?{path:'pinned-source-manifest',digest:SOURCE_CATALOG.manifest}:SOURCE_CATALOG.anchors[use.anchor];
    return {id:use.id,status:use.status,condition:use.condition,source:pin.path,digest:pin.digest};
  }));
}
export function validateConsumerData(value,role,phase){
  const expected=consumerRequirements(role,phase),rows=dataList(value,8);
  need(rows&&rows.length===expected.length,'consumer-membership');
  for(let i=0;i<rows.length;i++){
    const row=record(rows[i],['id','status','condition','source','digest']);
    need(row&&Object.keys(expected[i]).every(key=>row[key]===expected[i][key]),'consumer-source-condition');
  }
  need(!expected.some(use=>use.status==='unknown'),'consumer-unresolved');
  return freezeData({consumer_data_complete:true,consumer_closure_verified:false,native_admitted:false});
}
export function validateFallbackData(value,sources){
  sources=coreSourceData(sources);
  const row=record(value,['encoding','rows']),rows=row&&dataList(row.rows,2);
  need(row&&row.encoding==='UTF-8'&&rows?.length===2,'fallback-encoding-members');
  for(let i=0;i<rows.length;i++){
    const item=record(rows[i],['name','available','provider','source','digest','review']);
    need(item&&item.name===SOURCE_CATALOG.fallback.functions[i]&&item.available===true&&item.provider==='core'
      &&item.source===sources[0].path&&item.digest===sources[0].digest&&item.review==='catalog-bound','core-function');
  }
  return freezeData({fallback_data_complete:true,source_observed:false,native_admitted:false});
}
export function validateFunctionData(value,binding,role,phase,sources,catalog){
  need(hex(binding,64)&&catalog===SOURCE_CATALOG_DIGEST,'function-origin');
  sources=coreSourceData(sources); // Exported helper also rejects unsafe/non-DATA source arguments.
  const row=record(value,['model','binding','role','phase','php','word_size','zts','sapi','catalog','sources','algorithms','encoding','consumers','rows']);
  need(row&&row.model===FUNCTION_MODEL&&row.binding===binding&&row.role===role&&row.phase===phase
    &&row.catalog===catalog&&row.php==='8.5.12'&&row.word_size===8&&row.zts===false
    &&row.sapi===(role==='server'&&phase==='post-core'?'cli-server':'cli'),'function-binding');
  const required=functionRequirements(role,phase),members=dataList(row.sources,9);
  const expectedSources=phase==='post-core'?sources:[];
  need(members&&members.length===expectedSources.length,'function-sources');
  for(let i=0;i<members.length;i++){
    const item=record(members[i],['path','size','digest']);
    need(item&&item.path===expectedSources[i].path&&item.size===expectedSources[i].size&&item.digest===expectedSources[i].digest,'function-source-drift');
  }
  validateAlgorithmData(row.algorithms,role);
  const names=[...required.native,...required.core,...required.absent],entries=dataList(row.rows,32);
  need(entries&&entries.length===names.length,'function-membership');
  for(let i=0;i<entries.length;i++){
    const item=record(entries[i],['name','available','provider','source','digest','review']);
    need(item&&item.name===names[i],'function-order');
    if(required.native.includes(item.name)){
      need(item.available===true&&item.provider==='native'&&item.source==='internal'
        &&item.digest===null&&item.review==='required','native-function');
    }else if(!required.core.includes(item.name)){
      need(item.available===false&&item.provider==='absent'&&item.source===null&&item.digest===null
        &&item.review==='catalog-absence','optional-function-review');
    }
  }
  // Core rows are checked together,with fixed helper membership and explicit encoding.
  if(phase==='post-core')validateFallbackData({encoding:row.encoding,rows:entries.slice(required.native.length,required.native.length+2)},sources);
  else need(row.encoding===null,'pre-core-encoding');
  validateConsumerData(row.consumers,role,phase); // UNKNOWN cannot mint HTTP completion.
  return freezeData({model:FUNCTION_MODEL,binding,role,phase,catalog,profile_complete:true,
    consumer_closure_verified:false,source_observed:false,native_admitted:false});
}
