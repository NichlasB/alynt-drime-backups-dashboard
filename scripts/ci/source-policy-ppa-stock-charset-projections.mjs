// Finite scalar projections only; no WP/cache/SQL reader, callback or function execution.
import {createHash} from 'node:crypto';
import {record,hex,integer,LIMITS} from './source-policy-http-profile.mjs';
import {dataList,freezeData} from './source-policy-ppa-function-data.mjs';
import {STOCK_CHARSET_CATALOG as C} from './source-policy-ppa-stock-charset-catalog.mjs';
export const need=(ok,label)=>{if(!ok)throw Error('PPA stock charset DATA refused: '+label);};
const digest=value=>createHash('sha256').update(JSON.stringify(value)).digest('hex');
export const ROW_MODEL='ppa-json-charset-row-v2',ANCHOR_MODEL='opaque-parent-initialization-anchor-data-v2';
export const INVARIANT_MODEL='opaque-parent-full-invariant-data-v1',BASELINE_MODEL='ppa-stock-charset-baseline-data-v2';
export function stockSources(value){
  const rows=dataList(value,30);need(rows?.length===C.sources.length,'source-members');
  return freezeData(rows.map((item,i)=>{const row=record(item,['path','size','digest']),pin=C.sources[i];
    need(row&&row.path===pin.path&&row.size===pin.size&&row.digest===pin.digest,'source-identity');return row;}));
}
export function stockHooks(value){
  const hooks=dataList(value,9);need(hooks?.length===C.hooks.length,'hook-members');
  return freezeData(hooks.map((item,i)=>{
    const row=record(item,['name','callbacks']),pin=C.hooks[i],callbacks=row&&dataList(row.callbacks,2);
    need(row&&row.name===pin.name&&callbacks?.length===pin.callbacks.length,'hook-shape');
    return {name:row.name,callbacks:pin.callbacks.map((expected,j)=>{
      const cb=record(callbacks[j],['name','priority','accepted_args','source','digest']);
      need(cb&&cb.name===expected.name&&cb.priority===expected.priority&&cb.accepted_args===expected.accepted_args
        &&cb.source===expected.source&&cb.digest===expected.digest,'stock-callback');return cb;
    })};
  }));
}
export function charsetCache(value){
  const row=record(value,['alloptions','options','notoptions','protected_cache']);
  need(row&&[null,'UTF-8'].includes(row.alloptions)&&[null,'UTF-8'].includes(row.options)
    &&row.notoptions===false&&row.protected_cache===false,'cache-projection');return freezeData(row);
}
export function charsetRow(value){
  const rows=dataList(value,1);need(rows?.length===1,'row-cardinality');
  const row=record(rows[0],['id','key','value','autoload']);
  need(row&&typeof row.id==='string'&&/^[1-9][0-9]{0,19}$/.test(row.id)
    &&(row.id.length<20||row.id<='18446744073709551615')&&row.key==='blog_charset'
    &&row.value==='UTF-8'&&row.autoload==='on','charset-row');
  return freezeData({model:ROW_MODEL,digest:digest({model:ROW_MODEL,row})});
}
export function typedDigest(value,model){
  need([ROW_MODEL,ANCHOR_MODEL,INVARIANT_MODEL].includes(model),'fixed-digest-domain');
  const row=record(value,['model','digest']);need(row&&row.model===model&&hex(row.digest,64),'digest-domain');return freezeData(row);
}
export function baselineData(value){
  const row=record(value,['model','at','charset','anchor','invariant']);
  need(row&&row.model===BASELINE_MODEL&&integer(row.at,LIMITS.job_ms),'baseline');
  return freezeData({...row,charset:typedDigest(row.charset,ROW_MODEL),anchor:typedDigest(row.anchor,ANCHOR_MODEL),
    invariant:typedDigest(row.invariant,INVARIANT_MODEL)});
}
