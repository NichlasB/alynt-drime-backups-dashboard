import test from 'node:test';
import assert from 'node:assert/strict';
import {HTTP_PROFILE,LIMITS,HTTP_EXECUTION_ENABLED,hex,profileMatches,storageMatches,manifestMatches} from '../../scripts/ci/source-policy-http-profile.mjs';
import {route,loginFields,ledgerMatches,caseMatches,responseMatches} from '../../scripts/ci/source-policy-http-contract.mjs';
import * as f from './source-policy-http-fixtures.mjs';

test('finite HTTP profile/storage match DATA only; exact typed positives and immutable limits',()=>{
 assert.equal(HTTP_EXECUTION_ENABLED,false);assert.ok(Object.isFrozen(HTTP_PROFILE)&&Object.isFrozen(LIMITS));
 assert.equal(profileMatches(f.profile()),true);assert.equal(storageMatches(f.storage(),f.identity()),true);
 assert.equal(LIMITS.http_ms,60000);assert.equal(LIMITS.memory_bytes,67108864);
 for(const [key,value]of Object.entries(f.profile())){
  const bad={...f.profile(),[key]:typeof value==='boolean'?!value:typeof value==='number'?value+1:value+'-wrong'};
  assert.equal(profileMatches(bad),false,key);
 }
 for(const key of Object.keys(f.profile())){const bad=f.profile();delete bad[key];assert.equal(profileMatches(bad),false,key);}
 assert.equal(profileMatches({...f.profile(),ready:true}),false);
 for(const key of Object.keys(f.storage())){assert.equal(storageMatches({...f.storage(),[key]:null},f.identity()),false,key);}
 for(const [key,value]of Object.entries({database:'local',prefix:'wp_',before_bootstrap:false,existing_application:true,options:'wp_options',socket:'/tmp/../native.sock',core_path:'/installed',origin:'http://localhost:18777'}))assert.equal(storageMatches({...f.storage(),[key]:value},f.identity()),false,key);
});
test('hostile record keys/accessors/types refuse without invoking a getter',()=>{
 assert.equal(hex(f.run+'\n',32),false);assert.equal(hex(f.run+'\r\n',32),false);
 let reads=0;const object=f.profile();Object.defineProperty(object,'php',{get(){reads++;return '8.5.12';}});
 assert.equal(profileMatches(object),false);assert.equal(reads,0);
 for(const bad of [null,[],true,'profile',Object.create(f.profile()),{...f.profile(),[Symbol('hidden')]:1}])assert.equal(profileMatches(bad),false);
});
test('manifest exact independent digest, sorted uniqueness and bounded regular source DATA',()=>{
 assert.equal(manifestMatches(f.manifest(),f.manifestExpectation()),true);
 for(const mutate of [r=>r.reverse(),r=>r.concat(r[0]),r=>(r[0].type='symlink',r),r=>(r[0].path='../wp-config.php',r),r=>(r[0].path='x//y',r),r=>(r[0].size=LIMITS.file_bytes+1,r),r=>(r[0].digest='F'.repeat(64),r)]){
  const rows=mutate(f.manifest());assert.equal(manifestMatches(rows,f.manifestExpectation()),false);
 }
 assert.equal(manifestMatches(f.manifest(),{...f.manifestExpectation(),digest:'0'.repeat(64)}),false);
 assert.equal(manifestMatches(f.manifest(),{...f.manifestExpectation(),files:'2'}),false);
 const large=Array.from({length:13},(_,i)=>({path:'file-'+String(i).padStart(2,'0'),size:LIMITS.file_bytes,type:'file',digest:f.digest}));
 assert.equal(manifestMatches(large,f.manifestExpectation(large)),false);
});
test('routing never delegates unknown paths, hosts, methods, encodings or conflicting framing',()=>{
 for(const name of ['login-form','login-submit','view','submit','nonce-control'])assert.equal(route(f.meta(name),f.challenge),name);
 for(const [key,value]of Object.entries({host:'localhost:18777',method:'DELETE',target:'/wp-admin/',challenge:'b'.repeat(32),transfer_encoding:'chunked',content_length:'010',body_bytes:16385,header_bytes:32769,content_type:'application/json'}))assert.equal(route({...f.meta(),[key]:value},f.challenge),false,key);
 for(const target of ['/wp-login.php?action=register','//wp-login.php','/%77p-login.php','/_adb-owned-source-policy/','/_adb-owned-source-policy?control=other','/wp-config.php'])assert.equal(route({...f.meta('view'),target},f.challenge),false);
 assert.equal(route({...f.meta('view'),body_bytes:1},f.challenge),false);
 assert.equal(route({...f.meta(),extra:1},f.challenge),false);
});
test('fixed login form DATA cannot select alternate actions or destinations; not an auth grant',()=>{
 assert.equal(loginFields(f.login()),true);
 for(const [key,value]of Object.entries({redirect_to:'https://outside.invalid/',testcookie:'0',pwd:[],log:'',action:'register'}))assert.equal(loginFields({...f.login(),[key]:value}),false,key);
 assert.equal(loginFields({...f.login(),pwd:'x'.repeat(257)}),false);
 assert.equal(loginFields({...f.login(),log:'owned\n'}),false);
});
test('setup/bootstrap/auth ledgers separate permitted fixture writes from forbidden owner effects',()=>{
 for(const phase of ['setup','bootstrap','auth'])assert.equal(ledgerMatches(f.ledger(phase),phase),true);
 for(const phase of ['bootstrap','auth'])for(const key of ['policy_reads','policy_writes','owner_allocations','unknown_changes','mail','outbound'])assert.equal(ledgerMatches({...f.ledger(phase),[key]:1},phase),false,key);
 assert.equal(ledgerMatches({...f.ledger('setup'),sql:1025},'setup'),false);
 assert.equal(ledgerMatches({...f.ledger(),sql:257},'bootstrap'),false);
 assert.equal(ledgerMatches({...f.ledger(),sql:'2'},'bootstrap'),false);
 assert.equal(ledgerMatches({...f.ledger('setup'),sql:0},'setup'),false);
});
test('all thirteen actual-result models need positive availability, identity, policy and denial invariants',()=>{
 for(let i=0;i<13;i++){
  assert.equal(caseMatches(f.result(i),f.expected(i),i),true,'positive '+i);
  for(const [key,value]of Object.entries({result:'source_policy_unavailable',checks:0,run:'0'.repeat(32),image:'0'.repeat(64),sentinel:'0'.repeat(64),after:'2'.repeat(64),unknown_changes:1,production_ready:true}))assert.equal(caseMatches({...f.result(i),[key]:value},f.expected(i),i),false,key+' '+i);
 }
 for(let i=4;i<13;i++)for(const key of ['records','connects','commands','policy_reads','policy_writes'])assert.equal(caseMatches({...f.result(i),[key]:1},f.expected(i),i),false,key);
 assert.equal(caseMatches({...f.result(1),after:f.digest},f.expected(1),1),false);
 assert.equal(caseMatches({...f.result(0),mode:'other'},f.expected(0),0),false);
 assert.equal(caseMatches(f.result(0),{...f.expected(0),root:'/tmp/../foreign'},0),false);
});
test('response EOF and exact redirect boundary are required, not HTTP200 alone',()=>{
 assert.equal(responseMatches(f.response(),'submit'),true);
 assert.equal(responseMatches({...f.response(),status:302,location:'http://127.0.0.1:18777/_adb-owned-source-policy'},'login-submit'),true);
 for(const [key,value]of Object.entries({status:500,eof:false,body_bytes:65537,header_bytes:32769,location:'https://outside.invalid/'}))assert.equal(responseMatches({...f.response(),[key]:value},'submit'),false,key);
 assert.equal(responseMatches({...f.response(),status:302,location:'/wp-admin/'},'login-submit'),false);
 assert.equal(responseMatches({...f.response(),header_bytes:0},'submit'),false);
});
