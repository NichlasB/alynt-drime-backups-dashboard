// Socket-free function provenance DATA. No core loading, callbacks or native grant.
import {record,hex} from './source-policy-http-profile.mjs';

export const FUNCTION_MODEL='ppa-function-preparation-v1';
export const CORE_MEMBERS=Object.freeze(['core/wp-includes/compat.php','core/wp-includes/compat-utf8.php']);
export const LAUNCH_ROLES=Object.freeze(['environment','audit','adapter','request-audit','request','installer','initializer','server']);
export const HTTP_ROLES=Object.freeze(['installer','initializer','server']);
export const BASE_FUNCTIONS=Object.freeze(['json_encode','json_decode','hash','hash_equals','hash_hmac','random_bytes','preg_match','serialize','unserialize','hrtime']);
const AUTH_FUNCTIONS=['password_hash','password_verify','password_needs_rehash','password_algos','hash_algos','hash_hmac_algos'];
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
  const rows=dataList(value,2);need(rows?.length===2,'core-sources');
  return freezeData(rows.map((item,i)=>{
    const row=record(item,['path','digest']);
    need(row&&row.path===CORE_MEMBERS[i]&&hex(row.digest,64),'core-source-binding');return row;
  }));
}
export function functionRequirements(role,phase){
  need(LAUNCH_ROLES.includes(role)&&['pre-core','post-core'].includes(phase),'role-phase');
  need(phase!=='post-core'||HTTP_ROLES.includes(role),'post-core-role');
  const native=[...BASE_FUNCTIONS,...(role==='environment'?[]:AUTH_FUNCTIONS),...(role==='server'&&phase==='post-core'?['getallheaders']:[])];
  return freezeData({native,core:phase==='post-core'?['mb_strlen','mb_substr']:[],
    absent:phase==='pre-core'?['ctype_digit','iconv','mb_strlen']:['ctype_digit','iconv']});
}
export function validateFunctionData(value,binding,role,phase,sources,catalog){
  need(hex(binding,64)&&hex(catalog,64),'function-origin');
  sources=coreSourceData(sources); // Exported helper also rejects unsafe/non-DATA source arguments.
  const row=record(value,['model','binding','role','phase','php','word_size','zts','sapi','catalog','sources','algorithms','rows']);
  need(row&&row.model===FUNCTION_MODEL&&row.binding===binding&&row.role===role&&row.phase===phase
    &&row.catalog===catalog&&row.php==='8.5.12'&&row.word_size===8&&row.zts===false
    &&row.sapi===(role==='server'&&phase==='post-core'?'cli-server':'cli'),'function-binding');
  const required=functionRequirements(role,phase),members=dataList(row.sources,2);
  const expectedSources=phase==='post-core'?sources:[];
  need(members&&members.length===expectedSources.length,'function-sources');
  for(let i=0;i<members.length;i++){
    const item=record(members[i],['path','digest']);
    need(item&&item.path===expectedSources[i].path&&item.digest===expectedSources[i].digest,'function-source-drift');
  }
  const algorithms=record(row.algorithms,['sha256','sha384','bcrypt']);
  need(algorithms&&algorithms.sha256===true&&algorithms.sha384===true
    &&algorithms.bcrypt===(role==='environment'?null:true),'function-algorithms');
  const names=[...required.native,...required.core,...required.absent],entries=dataList(row.rows,32);
  need(entries&&entries.length===names.length,'function-membership');
  for(let i=0;i<entries.length;i++){
    const item=record(entries[i],['name','available','provider','source','digest','review']);
    need(item&&item.name===names[i],'function-order');
    if(required.native.includes(item.name)){
      need(item.available===true&&item.provider==='native'&&item.source==='internal'
        &&item.digest===null&&item.review==='required','native-function');
    }else if(required.core.includes(item.name)){
      need(item.available===true&&item.provider==='core'&&item.source===sources[0].path
        &&item.digest===sources[0].digest&&item.review==='source-reviewed','core-function');
    }else{
      need(item.available===false&&item.provider==='absent'&&item.source===null&&item.digest===null
        &&item.review==='gated','optional-function-review');
    }
  }
  return freezeData({model:FUNCTION_MODEL,binding,role,phase,catalog,profile_complete:true,native_admitted:false});
}
