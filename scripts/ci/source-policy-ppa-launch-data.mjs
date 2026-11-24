// Dormant role/recovery descriptions over supplied DATA. No IO or environment reads.
import {createHash} from 'node:crypto';
import {record,hex,HTTP_PROFILE} from './source-policy-http-profile.mjs';
import {REQUEST_CASES} from './source-policy-wl-request-contract.mjs';
import {freezeData,coreSourceData,validateFunctionData,LAUNCH_ROLES,HTTP_ROLES} from './source-policy-ppa-function-data.mjs';

export const PPA_LAUNCH_EXECUTION_ENABLED=false;
export const LAUNCH_MODEL='ppa-initialized-launch-preparation-v1';
const REPO='NichlasB/alynt-drime-backups-dashboard';
const WORKFLOW=REPO+'/.github/workflows/source-policy-linux-qualification.yml@refs/heads/master';
const sha=value=>createHash('sha256').update(value).digest('hex');
export const PPA_IDENTITIES=freezeData({revision:'8.5.12-1+ubuntu24.04.1+deb.sury.org+1',
  packages:[['php8.5-cli',3165930,'9ee42a60bccc4b2fb91624a05535702d9c45699e61d20ace18e39d69bf7e53c8'],
    ['php8.5-common',850622,'fc3adc82865c0046614cd577ad816db44a8923672236f7637eb7256673efc7d1'],
    ['php8.5-mysql',129750,'81cf7b2d0330ce86d9287bed2bdbb7210f41ab3846bee9c161babdecdf436279']],
  payloads:[['php',12282976,'eb85560c0bea6c5d1c06a93a602697efe94ad9803da400211469529f041929fa'],
    ['mysqlnd.so',218952,'837b346b8f11c34669bca7874bfc027c866ebe7ae1d954c2e496ce83341d0b20'],
    ['mysqli.so',170248,'6dfd427a916d649b7404d4f9c9aea94caaa1821d08c7c7c8852606d42c35fcd5']]});
export const PPA_IDENTITY=sha(JSON.stringify(PPA_IDENTITIES));
export const RESOURCE_DATA=freezeData({profile:'ppa-connection-free-as-40-128-v1',parent_soft:41943040,parent_hard:134217728,
  candidate_as:134217728,codec_as:25165824,cpu:60,core:0,ppa_work:201326592,http_manifest:201326592,
  job_ms:600000,ppa_ms:540000,cleanup_ms:30000,php_heap:67108864});
const CLOSED={native_admitted:false,runtime_grant:false,http_qualified:false,dependency_closure_verified:false,production_ready:false};
const fail=label=>Error('PPA launch DATA refused: '+label);
const posix=value=>typeof value==='string'&&value.length<=180&&value.startsWith('/')&&value!=='/'
  &&!/[\x00-\x20\x7f\\:]/.test(value)&&value.slice(1).split('/').every(p=>p!==''&&p!=='.'&&p!=='..');

function originData(value){
  const row=record(value,['model','repository','workflow','event','actor','attempt','platform','arch','run','head','expected_head',
    'root','workspace','core_commit','image','modules','prefix_identity','ppa_identity','resources','core_sources']);
  if(!row||row.model!==LAUNCH_MODEL||row.repository!==REPO||row.workflow!==WORKFLOW||row.event!=='workflow_dispatch'
    ||row.actor!=='NichlasB'||row.attempt!==1||row.platform!=='Linux'||row.arch!=='x86_64'
    ||typeof row.run!=='string'||!(/^[1-9][0-9]{0,19}$/).test(row.run)||!hex(row.head,40)||row.expected_head!==row.head
    ||!posix(row.root)||!posix(row.workspace)||row.root===row.workspace||row.root.startsWith(row.workspace+'/')
    ||row.workspace.startsWith(row.root+'/')||row.core_commit!==HTTP_PROFILE.commit
    ||![row.image,row.modules,row.prefix_identity].every(v=>hex(v,64))||row.ppa_identity!==PPA_IDENTITY)throw fail('origin');
  const limits=record(row.resources,Object.keys(RESOURCE_DATA));
  if(!limits||!Object.keys(RESOURCE_DATA).every(k=>limits[k]===RESOURCE_DATA[k]))throw fail('resource-profile');
  const sources=coreSourceData(row.core_sources),run=sha(row.run+':1').slice(0,32);
  const stage=row.root+'/adb-http-'+run,prefix=row.root+'/adb-ppa-runtime-'+row.run;
  if(stage.length>77||prefix.length>256)throw fail('derived-path-bound');
  return freezeData({...row,resources:{...limits},core_sources:sources,run_key:run,stage,prefix});
}
function roleTail(origin,role,index){
  const workspace=origin.workspace+'/scripts/ci/',proof=origin.stage+'/proof/scripts/ci/';
  switch(role){
    case 'environment':return [workspace+'source-policy-linux-preflight.php','--owned-profile-preflight'];
    case 'audit':case 'adapter':return [workspace+'source-policy-linux-qualification.php','--owned-linux-qualification',role==='audit'?'audit':'proof'];
    case 'request-audit':case 'request':return [workspace+'source-policy-wl-request-child.php','--owned-wl-request-child',String(role==='request-audit'?0:index)];
    case 'installer':return [proof+'source-policy-http-owned-setup.php','--owned-http-setup'];
    case 'initializer':return [proof+'source-policy-http-initialized-native.php','--owned-http-initializer'];
    case 'server':return ['-S','127.0.0.1:18777','-t',origin.stage+'/core',proof+'source-policy-http-owned-router.php'];
    default:throw fail('role');
  }
}
function describe(origin,binding,role,index){
  if(!LAUNCH_ROLES.includes(role)||(role==='request'?(!Number.isSafeInteger(index)||index<0||index>=REQUEST_CASES.length):index!==null))throw fail('role-index');
  const binary=origin.prefix+'/php',base={PATH:'/usr/bin:/bin',LANG:'C',LC_ALL:'C',PHP_INI_SCAN_DIR:''};
  let channels=[];
  if(HTTP_ROLES.includes(role)){
    base.ADB_HTTP_OWNED_APPROVED='true';base.ADB_HTTP_INITIALIZED_APPROVED='true';
    channels=role==='initializer'?['private.php','stdin-ticket']:['private.php'];
  }else{
    Object.assign(base,{GITHUB_ACTIONS:'true',GITHUB_EVENT_NAME:origin.event,GITHUB_REPOSITORY:origin.repository,
      GITHUB_REF:'refs/heads/master',GITHUB_ACTOR:origin.actor,GITHUB_WORKFLOW_REF:origin.workflow,
      GITHUB_RUN_ATTEMPT:'1',GITHUB_RUN_ID:origin.run,GITHUB_SHA:origin.head,EXPECTED_SHA:origin.head,RUNNER_TEMP:origin.root});
    if(role==='adapter'){base.QUALIFICATION_IMAGE=origin.image;channels=['QUALIFICATION_PASSWORD'];}
    if(role==='request'||role==='request-audit'){
      Object.assign(base,{QUALIFICATION_GENUINE_REQUEST_APPROVED:'true',WL_REQUEST_PROFILE:'l',WL_REQUEST_APPROVED:'true',
        WL_REQUEST_HEAD:origin.head,WL_REQUEST_RUN:origin.run_key,WL_REQUEST_PREFIX:'adbm_'+origin.run_key.slice(0,12)+'_',
        WL_REQUEST_IMAGE:role==='request-audit'?'0'.repeat(64):origin.image,
        WL_REQUEST_MODULES:role==='request-audit'?'0'.repeat(64):origin.modules,WL_REQUEST_MODE:role==='request-audit'?'audit':'proof'});
      if(role==='request')channels=['QUALIFICATION_PASSWORD'];
    }
  }
  const args=[binary,'-n','-d','extension_dir='+origin.prefix,'-d','extension='+origin.prefix+'/mysqlnd.so',
    '-d','extension='+origin.prefix+'/mysqli.so','-d','auto_prepend_file=','-d','auto_append_file=',
    '-d','display_errors=0','-d','log_errors=0','-d','mysqli.allow_persistent=0','-d','memory_limit=64M',
    '-d','default_socket_timeout=3','-d','mysqlnd.net_read_timeout=3',...roleTail(origin,role,index)];
  return freezeData({model:LAUNCH_MODEL,binding,role,index,binary,argv:args,environment:base,private_channels:channels,
    resources:{...RESOURCE_DATA,native_role_resource_admitted:false},...CLOSED});
}

export class PpaLaunchPreparation {
  #origin;#binding;#catalog;#state='origin';#description;#phase;
  constructor(value){
    this.#origin=originData(value);this.#binding=sha(JSON.stringify(this.#origin));
    this.#catalog=sha(JSON.stringify(this.#origin.core_sources));
  }
  #require(ok,label){if(!ok){this.#state='failed';throw fail(label);}}
  binding(){return freezeData({model:LAUNCH_MODEL,binding:this.#binding,catalog:this.#catalog});}
  sourcesClosed(value){
    this.#require(this.#state==='origin','source-order');
    const row=record(value,['binding','prefix_identity','source_rounds','sources_closed','codecs_reaped','prefix_sealed','release','exec']);
    this.#require(row&&row.binding===this.#binding&&row.prefix_identity===this.#origin.prefix_identity&&row.source_rounds===2
      &&row.sources_closed===true&&row.codecs_reaped===true&&row.prefix_sealed===true&&row.release==='closed'&&row.exec==='not-started','source-barrier');
    this.#state='sources-closed';
  }
  description(role,index=null){
    this.#require(this.#state==='sources-closed','description-order');
    try{this.#description=describe(this.#origin,this.#binding,role,index);}catch{this.#state='failed';throw fail('role-index');}
    this.#state='described';return this.#description;
  }
  functions(value){
    const phase=this.#state==='described'?'pre-core':'post-core';
    this.#require(this.#state==='described'||this.#state==='pre-profiled'&&HTTP_ROLES.includes(this.#description.role),'function-order');
    try{validateFunctionData(value,this.#binding,this.#description.role,phase,this.#origin.core_sources,this.#catalog);}
    catch{this.#state='failed';throw fail('function-profile');}
    this.#phase=phase;this.#state=phase==='pre-core'?'pre-profiled':'post-profiled';
  }
  complete(){
    this.#require(this.#description&&(HTTP_ROLES.includes(this.#description.role)?this.#state==='post-profiled':this.#state==='pre-profiled'),'completion-order');
    this.#state='complete';return freezeData({model:LAUNCH_MODEL,binding:this.#binding,description_complete:true,
      phase:this.#phase,description:this.#description,...CLOSED});
  }
  recovery(value){
    this.#require(this.#state==='complete'&&value===this.#description,'recovery-origin');
    return this.#description; // Same immutable full argv0+tail/env DATA,not a native handle.
  }
  uncertain(){this.#state='failed';} // Sticky unknown;no cleanup or launch method exists.
}
