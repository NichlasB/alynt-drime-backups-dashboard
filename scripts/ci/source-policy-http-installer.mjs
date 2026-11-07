// Sidecar DATA only. No callback registration, source discovery or execution.
import {createHash} from 'node:crypto';
import {record,identityData} from './source-policy-http-profile.mjs';
import {preparedHttpBatch} from './source-policy-http-supervisor.mjs';
export const INSTALLER_EXECUTION_ENABLED=false;
export function installerSidecarMatches(value,identity,stage){
 const id=identityData(identity),row=record(value,['model','run','head','image','owner','stage','http','hard','mail','violation','completed','runtime_grant','production_ready']);
 if(!id||!row||!['setup','request'].includes(stage))return false;
 const owner=createHash('sha256').update([id.run,id.head,id.image,id.root].join(':')).digest('hex');
 return row.model==='http-installer-containment-v1'&&row.run===id.run&&row.head===id.head&&row.image===id.image&&row.owner===owner&&row.stage===stage
  &&row.http===(stage==='setup'?2:0)&&row.hard===(stage==='setup'?4:0)&&Number.isInteger(row.mail)&&row.mail>=0&&row.mail<=(stage==='setup'?1:0)
  &&row.violation===false&&row.completed===true&&row.runtime_grant===false&&row.production_ready===false;
}

// Explicit isolated IO composition, NOT a real adapter or ownership authority.
// Future physical producer must supply current sidecars after normal shutdown.
export async function preparedContainedHttpBatch(io,input){
 const id=identityData(input?.identity),callbacks=['now','prepare','start','live','describe','exchange','reconcile','cleanup'];
 if(!id||io?.kind!=='isolated-installer-http-io-v1'||!callbacks.every(key=>typeof io[key]==='function'))throw Error('HTTP installer IO refused');
 return preparedHttpBatch({...io,kind:'isolated-http-io-v1',
  prepare:async left=>{
   const row=record(await io.prepare(left),['ledger','containment']);
   if(!row||!installerSidecarMatches(row.containment,id,'setup'))throw Error('HTTP installer sidecar refused');
   return row.ledger;
  },
  exchange:async(item,left)=>{
   const row=record(await io.exchange(item,left),['envelope','result','expected','bootstrap','auth','containment']);
   if(!row||!installerSidecarMatches(row.containment,id,'request'))throw Error('HTTP installer sidecar refused');
   const {containment,...reply}=row;return reply;
  }
 },{...input,identity:id});
}
