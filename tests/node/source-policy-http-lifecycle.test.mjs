import test from 'node:test';
import assert from 'node:assert/strict';
import {HttpDataLifecycle} from '../../scripts/ci/source-policy-http-lifecycle.mjs';
import * as f from './source-policy-http-fixtures.mjs';
function prepared(){const m=new HttpDataLifecycle(f.identity(),0,f.challenge);m.admit(f.profile(),f.storage(),f.manifest(),f.manifestExpectation(),1);m.setup(f.ledger('setup'),2);m.server(f.server(),f.profile(),3);return m;}
function respond(m,index,at){const kind=index===0?'view':'submit';m.begin(f.meta(kind),kind,index,at);m.receive({...f.response(),status:f.result(index).status},f.result(index),f.expected(index),f.ledger(),f.ledger('auth'),at+1);m.release(f.release(),at+2);}
test('complete thirteen-case DATA transcript requires release/cleanup, retains no runtime grant',()=>{
 const m=prepared();m.begin(f.meta('login-submit'),'login-submit',null,4);
 m.receive({...f.response(),status:302,location:'http://127.0.0.1:18777/_adb-owned-source-policy'},null,null,f.ledger(),f.ledger('auth'),5);m.release(f.release(),6);
 for(let i=0;i<13;i++)respond(m,i,10+i*3);
 assert.equal(m.state,'cleanup');const end=m.close(f.cleanup(),60);
 assert.equal(end.completed,true);assert.equal(end.cases,13);assert.equal(end.transactions,14);assert.equal(end.runtime_grant,false);assert.equal(end.production_ready,false);assert.equal(end.qualification,'not-established');
 assert.throws(()=>m.admit(f.profile(),f.storage(),f.manifest(),f.manifestExpectation(),61));
 assert.throws(()=>m.close(f.cleanup(),62));assert.equal(m.state,'closed');
});
test('identity is copied; caller mutation cannot rewrite root/run or private challenge',()=>{
 const identity=f.identity(),m=new HttpDataLifecycle(identity,0,f.challenge);identity.run='0'.repeat(32);
 m.admit(f.profile(),f.storage(),f.manifest(),f.manifestExpectation(),1);m.fail(2);assert.equal(m.close(f.cleanup(),3).completed,false);
 assert.throws(()=>new HttpDataLifecycle(f.identity(),0,f.run));
});
test('out-of-order, forged runtime/source and lost process identity fail closed',()=>{
 for(const mutate of [r=>({...r,pid:0}),r=>({...r,alive:false}),r=>({...r,image:'0'.repeat(64)}),r=>({...r,manifest:'0'.repeat(64)})]){
  const m=new HttpDataLifecycle(f.identity(),0,f.challenge);m.admit(f.profile(),f.storage(),f.manifest(),f.manifestExpectation(),1);m.setup(f.ledger('setup'),2);
  assert.throws(()=>m.server(mutate(f.server()),f.profile(),3));assert.equal(m.state,'failed');assert.equal(m.close(f.cleanup(),4).completed,false);
 }
 const m=prepared();assert.throws(()=>m.begin(f.meta(),'submit',1,4));assert.equal(m.state,'failed');
});
test('no always-unavailable or mixed-head response becomes a completed positive',()=>{
 for(const bad of [{...f.result(0),result:'source_policy_unavailable'},{...f.result(0),head:'0'.repeat(40)}]){
  const m=prepared();m.begin(f.meta('view'),'view',0,4);
  assert.throws(()=>m.receive(f.response(),bad,f.expected(0),f.ledger(),f.ledger('auth'),5));assert.equal(m.state,'failed');assert.equal(m.close(f.cleanup(),6).completed,false);
 }
});
test('response, phase ledgers and all-server reconciliation cannot be omitted or replaced by DB-only evidence',()=>{
 const early=prepared();early.begin(f.meta('view'),'view',0,4);assert.throws(()=>early.release(f.release(),5));
 for(const change of [{sessions:1},{transactions:1},{scope:'fixture-db'},{pid:124},{start:'0'.repeat(64)},{alive:false}]){
  const m=prepared();m.begin(f.meta('view'),'view',0,4);m.receive(f.response(),f.result(0),f.expected(0),f.ledger(),f.ledger('auth'),5);
  assert.throws(()=>m.release({...f.release(),...change},6));assert.equal(m.state,'failed');
 }
 const m=prepared();m.begin(f.meta('view'),'view',0,4);assert.throws(()=>m.receive(f.response(),f.result(0),f.expected(0),{...f.ledger(),policy_reads:1},f.ledger('auth'),5));
});
test('original setup/startup/request/whole-phase/job deadlines and monotonic time never refresh',()=>{
 const m=new HttpDataLifecycle(f.identity(),0,f.challenge);assert.throws(()=>m.admit(f.profile(),f.storage(),f.manifest(),f.manifestExpectation(),60000));
 const start=new HttpDataLifecycle(f.identity(),0,f.challenge);start.admit(f.profile(),f.storage(),f.manifest(),f.manifestExpectation(),1);start.setup(f.ledger('setup'),2);assert.throws(()=>start.server(f.server(),f.profile(),5002));
 const request=prepared();request.begin(f.meta('view'),'view',0,4);assert.throws(()=>request.receive(f.response(),f.result(0),f.expected(0),f.ledger(),f.ledger('auth'),5004));
 const whole=prepared();assert.throws(()=>whole.begin(f.meta('view'),'view',0,60003));
 const clock=prepared();assert.throws(()=>clock.begin(f.meta('view'),'view',0,2));
 const job=new HttpDataLifecycle(f.identity(),599999,f.challenge);assert.throws(()=>job.admit(f.profile(),f.storage(),f.manifest(),f.manifestExpectation(),600000));
 const reserve=new HttpDataLifecycle(f.identity(),510000,f.challenge);reserve.admit(f.profile(),f.storage(),f.manifest(),f.manifestExpectation(),510001);reserve.setup(f.ledger('setup'),510002);assert.throws(()=>reserve.server(f.server(),f.profile(),510003));
});
test('max64 transaction budget includes auxiliary traffic; cases cannot be manufactured by logins',()=>{
 const m=prepared();for(let i=0;i<64;i++){const at=4+i*3;m.begin(f.meta('login-form'),'login-form',null,at);m.receive(f.response(),null,null,f.ledger(),f.ledger('auth'),at+1);m.release(f.release(),at+2);}
 assert.throws(()=>m.begin(f.meta('view'),'view',0,200));const terminal=m.close(f.cleanup(),201);assert.equal(terminal.cases,0);assert.equal(terminal.completed,false);
});
test('unknown routes cannot self-match false, and auxiliary traffic cannot hide a submit',()=>{
 const bad=prepared();assert.throws(()=>bad.begin({...f.meta(),target:'/wp-config.php'},false,null,4));
 const hidden=prepared();assert.throws(()=>hidden.begin(f.meta(),'submit',null,4));
});

test('an auxiliary guarded view cannot discard its protected read observation',()=>{
 const m=prepared();m.begin(f.meta('view'),'view',null,4);
 assert.throws(()=>m.receive(f.response(),null,null,f.ledger(),f.ledger('auth'),5));
});

test('observed phase counts cannot disagree with the retained bootstrap/auth ledger',()=>{
 const m=prepared();m.begin(f.meta('view'),'view',0,4);
 assert.throws(()=>m.receive(f.response(),{...f.result(0),bootstrap:99},f.expected(0),f.ledger(),f.ledger('auth'),5));
});
test('a saved policy observation cannot silently reset before later cases',()=>{
 const m=prepared();respond(m,0,4);respond(m,1,7);m.begin(f.meta(),'submit',2,10);
 const reverted={...f.result(2),before:f.digest,after:f.digest},wanted={...f.expected(2),before:f.digest,after:f.digest};
 assert.throws(()=>m.receive(f.response(),reverted,wanted,f.ledger(),f.ledger('auth'),11));
 const sentinel=prepared();respond(sentinel,0,4);sentinel.begin(f.meta(),'submit',1,7);
 assert.throws(()=>sentinel.receive(f.response(),{...f.result(1),sentinel:f.digest},{...f.expected(1),sentinel:f.digest},f.ledger(),f.ledger('auth'),8));
});
test('owned cleanup metadata is mandatory, unknown cleanup terminal cannot be replayed',()=>{
 for(const key of ['processes','listeners','containers','schemas','accounts','files','sessions','transactions']){
  const m=prepared();m.fail(4);assert.throws(()=>m.close({...f.cleanup(),[key]:1},5));assert.equal(m.state,'cleanup-unknown');assert.throws(()=>m.close(f.cleanup(),6));
 }
 const m=prepared();m.fail(4);assert.throws(()=>m.close(f.cleanup(),30004));assert.equal(m.state,'cleanup-unknown');
 const unknown=prepared();unknown.fail(4);assert.throws(()=>unknown.close({...f.cleanup(),ownership:false},5));
});
