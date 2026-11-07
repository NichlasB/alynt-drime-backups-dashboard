// Dormant preparation with isolated IO doubles ONLY. No launcher/network adapter.
import {HttpDataLifecycle} from './source-policy-http-lifecycle.mjs';
import {LIMITS,record,profileMatches,integer} from './source-policy-http-profile.mjs';
const step=(kind,index=null,jar='primary',intent='normal')=>Object.freeze({kind,index,jar,intent});
export const HTTP_FLOW=Object.freeze([
 step('login-form'),step('login-submit'),step('view',0),step('submit',1,'primary','initial-revision'),
 step('view'),step('submit',2,'primary','fresh-revision'),step('submit',3,'primary','stale-revision'),
 step('submit',4,'anonymous'),step('login-form',null,'incapable'),step('login-submit',null,'incapable'),step('submit',5,'incapable'),
 step('submit',6,'primary','missing-nonce'),step('nonce-control'),step('submit',7,'primary','wrong-action'),
 step('login-form',null,'other'),step('login-submit',null,'other'),step('view',null,'other'),step('submit',8,'primary','wrong-user'),
 step('login-form',null,'second'),step('login-submit',null,'second'),step('view',null,'second'),step('submit',9,'primary','wrong-session'),
 step('submit',10,'primary','compound-nonce'),step('submit',11,'primary','compound-site'),step('submit',12,'primary','bad-revision')
]);
const check=(ok,stage)=>{if(!ok)throw Error('HTTP preparation refused: '+stage);};
const safe=stage=>Error('HTTP preparation failed: '+stage);
function sameServer(value,initial){
 const row=record(value,['run','head','image','pid','start','alive','manifest']);
 return row&&row.alive===true&&Object.keys(row).every(key=>row[key]===initial[key]);
}
/** Callback labels are a test convention, NOT authority/ownership attestation.
 * Virtual deadlines are checked on return; a future real adapter must bound IO
 * cancellation/reaping independently. This function cannot prove termination.
 */
export async function preparedHttpBatch(io,input){
 const callbacks=['now','prepare','start','live','describe','exchange','reconcile','cleanup'];
 check(io&&io.kind==='isolated-http-io-v1'&&callbacks.every(key=>typeof io[key]==='function'),'isolated-io');
 const created=io.now(),model=new HttpDataLifecycle(input.identity,created,input.challenge);
 const remaining=deadline=>{const at=io.now();check(integer(at,LIMITS.job_ms-1)&&at<deadline,'deadline');return Math.min(deadline-at,LIMITS.job_ms-at);};
 let stage='admission',owned=false,server=null,failed=null,terminal=null;
 try{
  model.admit(input.profile,input.storage,input.manifest,input.expectation,io.now());
  stage='setup';owned=true;
  model.setup(await io.prepare(remaining(created+LIMITS.setup_ms)),io.now());
  stage='startup';
  const started=await io.start(remaining(io.now()+LIMITS.transaction_ms));
  server=record(started?.server,['run','head','image','pid','start','alive','manifest']);
  check(server&&profileMatches(started.profile),'startup');
  model.server(server,started.profile,io.now());
  const httpDeadline=io.now()+LIMITS.http_ms;
  for(const item of HTTP_FLOW){
   stage='live';check(sameServer(await io.live(remaining(httpDeadline)),server),'live');
   stage='request';const meta=await io.describe(item,remaining(httpDeadline));
   model.begin(meta,item.kind,item.index,io.now());
   const requestDeadline=Math.min(io.now()+LIMITS.transaction_ms,httpDeadline);
   const response=await io.exchange(item,remaining(requestDeadline));
   stage='response';
   const row=record(response,['envelope','result','expected','bootstrap','auth']);check(row,'response-shape');
   model.receive(row.envelope,row.result,row.expected,row.bootstrap,row.auth,io.now());
   stage='release';const release=await io.reconcile(remaining(requestDeadline));
   check(sameServer(await io.live(remaining(requestDeadline)),server),'live-after-reconcile');
   model.release(release,io.now());
  }
 }catch{
  failed=safe(stage);
  try{model.fail(io.now());}catch{/* Already refused/deadline state: cleanup still required. */}
 }
 if(owned){
  try{terminal=model.close(await io.cleanup(Math.max(0,Math.min(LIMITS.cleanup_ms,LIMITS.job_ms-io.now()))),io.now());}
  catch{const cleanup=safe('cleanup-unknown');throw failed?new AggregateError([failed,cleanup],'HTTP preparation failed with unknown cleanup'):cleanup;}
 }
 if(failed)throw failed;
 check(terminal?.completed===true,'incomplete');
 return Object.freeze({...terminal,event:'http-preparation-closed',isolated_only:true});
}
