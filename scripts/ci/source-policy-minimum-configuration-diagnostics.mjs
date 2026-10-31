// Diagnostic DATA only. The existing admission function remains authoritative and unchanged.
import {containerProfile, selections} from './source-policy-minimum-controller.mjs';

const shape=value=>value===null?'null':Array.isArray(value)?'array':typeof value==='object'?(Object.keys(value).length?'nonempty-object':'empty-object'):typeof value;
const count=value=>Array.isArray(value)&&value.length<=16?value.length:null;
const number=value=>Number.isSafeInteger(value)&&Math.abs(value)<=1099511627776?value:null;

export function serverConfigurationDiagnostics(row,owner,uid,stage) {
  const scratch='/var/lib/mysql';
  // Deliberately mirror named clauses without changing the authoritative conjunction.
  const predicates={
    row:()=>Boolean(row),
    image:()=>row.image===selections.server.config,
    label:()=>row.label===owner,
    user:()=>row.user===uid+':'+uid,
    read_only:()=>row.ro===true,
    entrypoint:()=>JSON.stringify(row.entry)===JSON.stringify(['/bin/sh']),
    network:()=>row.network==='none',
    memory:()=>row.memory===536870912,
    cpu:()=>row.cpu===1000000000,
    pids:()=>row.pids===64,
    privileged:()=>row.privileged===false,
    port_bindings:()=>row.ports===null,
    capabilities:()=>JSON.stringify(row.caps)===JSON.stringify(['ALL']),
    security:()=>Array.isArray(row.security)&&row.security.includes('no-new-privileges'),
    devices:()=> (row.devices??[]).length===0,
    tmpfs_shape:()=>Boolean(row.tmpfs)&&Array.isArray(row.mounts),
    tmpfs_paths:()=>Object.keys(row.tmpfs).sort().join(',')===[scratch,'/tmp'].sort().join(','),
    data_tmpfs_options:()=>row.tmpfs[scratch]===`rw,nosuid,nodev,noexec,size=256m,mode=700,uid=${uid},gid=${uid}`,
    scratch_tmpfs_options:()=>row.tmpfs['/tmp']===`rw,nosuid,nodev,noexec,size=16m,mode=700,uid=${uid},gid=${uid}`,
    bind_mounts:()=>row.mounts.filter(m=>m.Type==='bind').length===0,
    mount_types:()=>row.mounts.every(m=>m.Type==='tmpfs')
  };
  const checks={};
  for(const [name,predicate]of Object.entries(predicates)) {
    try{checks[name]=predicate()?'pass':'fail';}catch{checks[name]='invalid-data';}
  }
  let accepted=false,admissionError=false;
  try{accepted=containerProfile(row,'server',owner,selections.server.config,null,uid,stage);}catch{admissionError=true;}
  // No raw strings, object contents, paths, Env, messages or exception traces leave this boundary.
  const observed={
    port_bindings_shape:shape(row?.ports),
    port_binding_keys:row?.ports&&typeof row.ports==='object'&&!Array.isArray(row.ports)?Math.min(Object.keys(row.ports).length,17):null,
    memory_bytes:number(row?.memory),cpu_nanos:number(row?.cpu),pids:number(row?.pids),
    entrypoint_shape:shape(row?.entry),security_shape:shape(row?.security),devices_shape:shape(row?.devices),
    capabilities_count:count(row?.caps),security_count:count(row?.security),devices_count:count(row?.devices),
    mounts_count:count(row?.mounts),tmpfs_shape:shape(row?.tmpfs),
    tmpfs_paths_count:row?.tmpfs&&typeof row.tmpfs==='object'?Math.min(Object.keys(row.tmpfs).length,17):null,
    data_tmpfs_shape:shape(row?.tmpfs?.[scratch]),scratch_tmpfs_shape:shape(row?.tmpfs?.['/tmp'])
  };
  return {format:'ADBMCD1',accepted,agreement:!admissionError&&accepted===Object.values(checks).every(value=>value==='pass'),
    admission_error:admissionError,checks,failed:Object.keys(checks).filter(key=>checks[key]!=='pass'),observed,
    runtime_grant:false,production_ready:false,native_qualification:false};
}
