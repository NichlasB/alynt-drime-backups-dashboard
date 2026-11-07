// Dormant proof DATA only. No IO, environment observation, launch or runtime grant.
import {createHash} from 'node:crypto';
export const HTTP_EXECUTION_ENABLED=false;
export const HTTP_PROFILE=Object.freeze({model:'owned-http-l714-v1',platform:'Linux',arch:'x86_64',php:'8.5.12',client:'mysqlnd 8.5.12',nts:true,word_size:8,sapi:'cli-server',core:'7.1.4',commit:'db96f5eaa77a8d3e8eec8923a518fa2326f0d46a',theme:'twentytwentyfive',theme_tree:'8602b07f7973be84dec0238215aeb16251dead5e',server:'10.11.18-MariaDB-ubu2204',server_image:'mariadb@sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5',transport:'unix_socket',charset:'utf8',collate:'',report_mode:0,external_cache:false,dropins:false,plugins:0,mu_plugins:0,workers:1,host_ini:false,prepend:false,append:false,display_errors:false});
export const LIMITS=Object.freeze({job_ms:600000,setup_ms:60000,http_ms:60000,cleanup_ms:30000,transaction_ms:5000,transactions:64,memory_bytes:67108864,body_bytes:16384,header_bytes:32768,response_bytes:65536,diagnostic_bytes:65536,setup_sql:1024,bootstrap_sql:256,auth_sql:256,files:8192,source_bytes:201326592,file_bytes:16777216});
export const ORIGIN='http://127.0.0.1:18777',HOST='127.0.0.1:18777',BRIDGE='/_adb-owned-source-policy';
export const integer=(n,max)=>Number.isSafeInteger(n)&&n>=0&&n<=max;
export const hex=(s,n)=>typeof s==='string'&&new RegExp('^[a-f0-9]{'+n+'}$').test(s);
// Bounded JSON-like records, not arbitrary objects/accessors or an authority DTO.
export function record(value,keys){
 try{
  if(!value||typeof value!=='object'||Object.getPrototypeOf(value)!==Object.prototype)return null;
  const names=Reflect.ownKeys(value);if(names.length!==keys.length||names.some(k=>typeof k!=='string'||!keys.includes(k)))return null;
  const copy={};for(const key of keys){const d=Object.getOwnPropertyDescriptor(value,key);if(!d||!Object.hasOwn(d,'value'))return null;copy[key]=d.value;}return copy;
 }catch{return null;}
}
function list(value,max){
 try{
  if(!Array.isArray(value)||Object.getPrototypeOf(value)!==Array.prototype)return null;
  const length=Object.getOwnPropertyDescriptor(value,'length').value;
  if(!integer(length,max)||Reflect.ownKeys(value).length!==length+1)return null;
  const copy=[];for(let i=0;i<length;i++){const d=Object.getOwnPropertyDescriptor(value,String(i));if(!d||!Object.hasOwn(d,'value'))return null;copy.push(d.value);}return copy;
 }catch{return null;}
}
export function profileMatches(value){
 const row=record(value,Object.keys(HTTP_PROFILE));
 return row!==null&&Object.keys(HTTP_PROFILE).every(k=>row[k]===HTTP_PROFILE[k]);
}
const posix=s=>typeof s==='string'&&s.length<=256&&s.startsWith('/')&&!/[\x00-\x20\x7f\\:]/.test(s)&&s.split('/').slice(1).every(p=>p!==''&&p!=='.'&&p!=='..');
export function identityData(value){
 const row=record(value,['run','head','image','root']);
 return row&&hex(row.run,32)&&hex(row.head,40)&&hex(row.image,64)&&posix(row.root)?Object.freeze(row):null;
}
export function storageMatches(value,identity){
 const id=identityData(identity),keys=['run','head','image','root','stage','core_path','content_path','socket','database','user','prefix','options','sites','origin','before_bootstrap','existing_application'];
 const row=record(value,keys);if(!id||!row)return false;
 const stage=id.root+'/adb-http-'+id.run,prefix='adbh_'+id.run.slice(0,12)+'_';
 const expected={...id,stage,core_path:stage+'/core',content_path:stage+'/content',socket:stage+'/socket/native.sock',database:'adb_http_'+id.run.slice(0,12),user:'adbh_'+id.run.slice(0,12),prefix,options:prefix+'options',sites:prefix+'alynt_drime_dashboard_sites',origin:ORIGIN,before_bootstrap:true,existing_application:false};
 return stage.length<=77&&Buffer.byteLength(expected.socket)<=107&&keys.every(k=>row[k]===expected[k]);
}
// This compares supplied manifest DATA, not disk identity or independent attestation.
export function manifestSummary(value){
 const rows=list(value,LIMITS.files);if(!rows||rows.length===0)return null;
 let bytes=0,prior='';const canonical=[];
 for(const item of rows){
  const row=record(item,['path','size','type','digest']);if(!row)return null;
  if(typeof row.path!=='string'||row.path.length>512||/[\x00-\x1f\x7f\\:]/.test(row.path)||row.path.split('/').some(p=>p===''||p==='.'||p==='..')||row.path<=prior||row.type!=='file'||!integer(row.size,LIMITS.file_bytes)||!hex(row.digest,64))return null;
  bytes+=row.size;if(bytes>LIMITS.source_bytes)return null;prior=row.path;canonical.push(row);
 }
 return Object.freeze({files:canonical.length,bytes,digest:createHash('sha256').update(JSON.stringify(canonical)).digest('hex')});
}
export function manifestMatches(rows,value){
 const expected=record(value,['files','bytes','digest']),actual=manifestSummary(rows);
 return !!(actual&&expected&&integer(expected.files,LIMITS.files)&&expected.files>0&&integer(expected.bytes,LIMITS.source_bytes)&&hex(expected.digest,64)&&actual.files===expected.files&&actual.bytes===expected.bytes&&actual.digest===expected.digest);
}
