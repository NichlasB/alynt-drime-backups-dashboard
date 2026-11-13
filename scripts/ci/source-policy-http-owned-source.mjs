// Owned CI filesystem/process primitives only. Importing has no effects.
import {createHash} from 'node:crypto';
import {spawnSync} from 'node:child_process';
import {lstatSync,realpathSync,readdirSync,readFileSync,writeFileSync,mkdirSync,copyFileSync,chmodSync,existsSync,rmSync} from 'node:fs';
import {join,relative,resolve} from 'node:path';
import {HTTP_PROFILE,LIMITS,identityData,storageMatches,hex} from './source-policy-http-profile.mjs';
export const need=(ok,label)=>{if(!ok)throw Error('Owned HTTP refused: '+label);};
export const sha=b=>createHash('sha256').update(b).digest('hex');
export const phpString=s=>"'"+s.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'";
export function command(binary,args,{left=5000,input,maxBuffer=65536,env}={}){
 need(Number.isSafeInteger(left)&&left>0,'command-credit');
 const r=spawnSync(binary,args,{input,encoding:'utf8',timeout:left,killSignal:'SIGKILL',maxBuffer,env});
 need(!r.error&&!r.signal&&r.status===0&&r.stderr==='','command');return r.stdout;
}
export function ownedHttpJob(e){return !!(e&&e.GITHUB_ACTIONS==='true'&&e.GITHUB_EVENT_NAME==='workflow_dispatch'&&e.GITHUB_REPOSITORY==='NichlasB/alynt-drime-backups-dashboard'&&e.GITHUB_REF==='refs/heads/master'&&e.GITHUB_ACTOR==='NichlasB'&&e.GITHUB_RUN_ATTEMPT==='1'&&e.GITHUB_WORKFLOW_REF==='NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-linux-qualification.yml@refs/heads/master'&&e.QUALIFICATION_HTTP_APPROVED==='true'&&e.QUALIFICATION_GENUINE_REQUEST_APPROVED==='true'&&/^[1-9][0-9]*$/.test(e.GITHUB_RUN_ID??'')&&hex(e.GITHUB_SHA,40)&&e.EXPECTED_SHA===e.GITHUB_SHA&&e.RUNNER_OS==='Linux');}
export function sourceRows(root,skipGit=false){
 const rows=[];let total=0,entries=0;
 const walk=(dir,depth)=>{need(depth<=32,'source-depth');for(const leaf of readdirSync(dir).sort()){
  if(skipGit&&leaf==='.git')continue;
  need(++entries<=LIMITS.files*2,'source-entries');const file=join(dir,leaf),s=lstatSync(file);need(!s.isSymbolicLink()&&s.uid===process.getuid(),'source-owner');
  if(s.isDirectory())walk(file,depth+1);else{need(s.isFile()&&s.nlink===1&&s.size<=LIMITS.file_bytes,'source-file');total+=s.size;need(total<=LIMITS.source_bytes&&rows.length<LIMITS.files,'source-budget');rows.push({path:relative(root,file).replaceAll('\\','/'),size:s.size,type:'file',digest:sha(readFileSync(file))});}
 }};walk(root,0);return rows.sort((a,b)=>a.path<b.path?-1:1);
}
function copyTree(from,to,depth=0){
 need(depth<=32&&!existsSync(to),'copy-absence');mkdirSync(to,{mode:0o700});
 for(const leaf of readdirSync(from).sort()){if(leaf==='.git')continue;const a=join(from,leaf),b=join(to,leaf),s=lstatSync(a);need(!s.isSymbolicLink()&&s.uid===process.getuid(),'copy-owner');if(s.isDirectory())copyTree(a,b,depth+1);else{need(s.isFile()&&s.size<=LIMITS.file_bytes,'copy-file');copyFileSync(a,b);chmodSync(b,0o400);}}
}
export function createOwnedStage(e){
 need(ownedHttpJob(e)&&process.platform==='linux'&&process.arch==='x64'&&process.getuid()!==0,'exact-job');
 need(resolve(process.cwd())===e.GITHUB_WORKSPACE&&realpathSync(e.RUNNER_TEMP)===e.RUNNER_TEMP&&!e.DOCKER_HOST&&!e.DOCKER_CONTEXT,'fixed-host');
 const status=command('git',['status','--porcelain']).trim();need(command('git',['rev-parse','HEAD']).trim()===e.GITHUB_SHA&&(status===''||status==='?? .adb-linux-preflight-core/'),'exact-head');
 need(command('docker',['context','inspect','--format','{{.Endpoints.docker.Host}}']).trim()==='unix:///var/run/docker.sock','docker-context');
 const run=sha(e.GITHUB_RUN_ID+':1').slice(0,32),stage=e.RUNNER_TEMP+'/adb-http-'+run;
 need(stage.length<=77&&!existsSync(stage),'stage-absence');mkdirSync(stage,{mode:0o700});const s=lstatSync(stage);
 return {run,stage,root:e.RUNNER_TEMP,owner:'adb-http-'+run,inode:s.ino,device:s.dev};
}
export function storageFor(owner,image,head){
 const id=identityData({run:owner.run,head,image,root:owner.root}),tag=owner.run.slice(0,12),prefix='adbh_'+tag+'_';
 const storage={...id,stage:owner.stage,core_path:owner.stage+'/core',content_path:owner.stage+'/content',socket:owner.stage+'/socket/native.sock',database:'adb_http_'+tag,user:'adbh_'+tag,prefix,options:prefix+'options',sites:prefix+'alynt_drime_dashboard_sites',origin:'http://127.0.0.1:18777',before_bootstrap:true,existing_application:false};
 need(id&&storageMatches(storage,id),'storage');return {id,storage};
}
export function stageSource(owner){
 const checkout=process.cwd()+'/.adb-linux-preflight-core';need(realpathSync(checkout)===checkout&&command('git',['-C',checkout,'rev-parse','HEAD']).trim()===HTTP_PROFILE.commit&&command('git',['-C',checkout,'status','--porcelain'])==='','core-head');
 sourceRows(checkout,true); // Bound the complete pinned tree before copying.
 copyTree(checkout,owner.stage+'/core');mkdirSync(owner.stage+'/content',{mode:0o700});mkdirSync(owner.stage+'/content/themes',{mode:0o700});
 need(command('git',['-C',checkout,'rev-parse','HEAD:wp-content/themes/twentytwentyfive']).trim()===HTTP_PROFILE.theme_tree,'theme-tree');
 copyTree(checkout+'/wp-content/themes/twentytwentyfive',owner.stage+'/content/themes/twentytwentyfive');for(const dir of ['plugins','mu-plugins'])mkdirSync(owner.stage+'/content/'+dir,{mode:0o700});
 mkdirSync(owner.stage+'/proof',{mode:0o700});copyTree(process.cwd()+'/includes',owner.stage+'/proof/includes');mkdirSync(owner.stage+'/proof/scripts',{mode:0o700});mkdirSync(owner.stage+'/proof/scripts/ci',{mode:0o700});
 for(const f of readdirSync('scripts/ci').filter(f=>/^source-policy-http-[a-z-]+\.php$/.test(f)))copyFileSync('scripts/ci/'+f,owner.stage+'/proof/scripts/ci/'+f);
 mkdirSync(owner.stage+'/receipts',{mode:0o700});
}
export function writePrivate(path,bytes){need(!existsSync(path),'private-file-absence');writeFileSync(path,bytes,{flag:'wx',mode:0o400});}
export function frozenRows(owner){return ['core','content','proof'].flatMap(dir=>sourceRows(owner.stage+'/'+dir).map(r=>({...r,path:dir+'/'+r.path}))).sort((a,b)=>a.path<b.path?-1:1);}
export function removeOwnedStage(owner){
 need(resolve(owner.stage)===owner.root+'/adb-http-'+owner.run&&realpathSync(owner.stage)===owner.stage,'cleanup-path');const s=lstatSync(owner.stage);
 need(!s.isSymbolicLink()&&s.uid===process.getuid()&&s.ino===owner.inode&&s.dev===owner.device,'cleanup-identity');
 rmSync(owner.stage,{recursive:true,force:false});need(!existsSync(owner.stage),'cleanup-absence');
}
