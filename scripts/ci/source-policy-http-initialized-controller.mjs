// Dormant worker/controller preparation. No launcher, SQL, filesystem or HTTP IO.
import {LIMITS,record,integer,identityData} from './source-policy-http-profile.mjs';
import {InitializedFixtureData} from './source-policy-http-initialization.mjs';
export const INITIALIZED_CONTROLLER_EXECUTION_ENABLED=false;
const safe=stage=>Error('Initialized preparation failed: '+stage);
const check=(ok,stage)=>{if(!ok)throw safe(stage);};
function cleanupMatches(value,id){
 const row=record(value,['run','head','image','ownership','processes','listeners','containers','schemas','accounts','files','sessions','transactions']);
 return row&&row.run===id.run&&row.head===id.head&&row.image===id.image&&row.ownership===true&&['processes','listeners','containers','schemas','accounts','files','sessions','transactions'].every(k=>row[k]===0);
}
/** Isolated callback composition, not ownership or execution authority. A future
 * native adapter must bound/reap operations independently; return-time checks
 * here do not prove cancellation or physical cleanup. No adapter exists here.
 */
export async function prepareInitializedWorkerController(io,input){
 const methods=['now','install','initialize','reconcile','baseline','cleanup'];
 const callbacks=record(io,['kind',...methods]);
 check(callbacks&&callbacks.kind==='isolated-initialized-controller-v2'&&methods.every(k=>typeof callbacks[k]==='function'),'isolated-io');
 const row=record(input,['identity','profile','storage','manifest','expectation','anchors']);
 const id=row&&identityData(row.identity);check(id,'admission');
 let last=-1;
 const now=()=>{let at;try{at=callbacks.now();}catch{throw safe('clock');}check(integer(at,LIMITS.job_ms-1)&&at>=last,'clock');last=at;return at;};
 const created=now();check(LIMITS.job_ms-created>=LIMITS.setup_ms+LIMITS.http_ms+LIMITS.cleanup_ms,'reserve');
 const model=new InitializedFixtureData(row,created),binding=model.binding(),setupDeadline=created+LIMITS.setup_ms;
 const remaining=deadline=>{const left=deadline-now();check(left>0,'deadline');return left;};
 let owned=false,stage='installer',failure=null,prepared=null;
 try{
  owned=true;
  model.installed(await callbacks.install(binding,remaining(setupDeadline)),now());
  stage='initializer';const ticket=model.begin(now());
  const packet=record(await callbacks.initialize(ticket,remaining(setupDeadline)),['event','completion','callbacks_closed','runtime_grant','production_ready']);
  check(packet&&packet.event==='initializer-worker-prepared-v2'&&packet.callbacks_closed===true&&packet.runtime_grant===false&&packet.production_ready===false,'initializer-packet');
  model.initialized(packet.completion,now());
  stage='initializer-release';model.released(await callbacks.reconcile(binding,remaining(setupDeadline)),now());
  stage='baseline';model.freeze(await callbacks.baseline(binding,remaining(setupDeadline)),now());
  prepared=model.result();
 }catch{failure=safe(stage);}
 if(owned){
  try{
   const cleanupDeadline=Math.min(LIMITS.job_ms,now()+LIMITS.cleanup_ms);
   const cleanup=await callbacks.cleanup(binding,remaining(cleanupDeadline));
   remaining(cleanupDeadline);check(cleanupMatches(cleanup,id),'cleanup-unknown');
  }catch{const unknown=safe('cleanup-unknown');throw failure?new AggregateError([failure,unknown],'Initialized preparation failed with unknown cleanup'):unknown;}
 }
 if(failure)throw failure;
 check(prepared?.state==='ready','incomplete');
 return Object.freeze({...prepared,event:'initialized-controller-preparation-closed',prepared:true,cleanup:'exact-owned-zero'});
}
