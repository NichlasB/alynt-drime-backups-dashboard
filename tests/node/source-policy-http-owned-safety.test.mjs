import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {pathToFileURL} from 'node:url';
import {OwnedHttpClock,processStart,processStillOwned} from '../../scripts/ci/source-policy-http-owned-safety.mjs';
import {physicalPolicy} from '../../scripts/ci/source-policy-http-owned-controller.mjs';
import {ownedHttpJob} from '../../scripts/ci/source-policy-http-owned-source.mjs';
test('original job age and a single cleanup deadline cannot be reset by phases',()=>{
 let ns=1000000000n;const c=new OwnedHttpClock('1000000000',()=>ns);c.admit();ns+=400000000000n;assert.equal(c.now(),400000);c.admit();
 assert.equal(c.cleanupDeadline(),430000);ns+=10000000000n;assert.equal(c.cleanupDeadline(),430000);assert.equal(c.left(430000),20000);
 ns+=20000000000n;assert.throws(()=>c.left(430000));ns+=170000000000n;assert.throws(()=>c.now());
 assert.throws(()=>new OwnedHttpClock(undefined));assert.throws(()=>new OwnedHttpClock('1000000001',()=>1000000000n));
 let old=451001000000n;assert.throws(()=>new OwnedHttpClock('1000000000',()=>old).admit());
});
const stat=(pid,start,state='S')=>pid+' (owned php) '+state+' '+Array(18).fill('0').join(' ')+' '+start+' 0';
test('recovery binds exact PID/start and never signals a reused PID or missing process',()=>{
 const s={pid:321,start:processStart(stat(321,'123'),321)};
 assert.equal(processStillOwned(s,()=>stat(321,'123')),true);assert.equal(processStillOwned(s,()=>stat(321,'124')),false);
 assert.equal(processStillOwned(s,()=>stat(321,'123','Z')),false);assert.throws(()=>processStart(stat(321,'123','Z'),321));
 assert.equal(processStillOwned(s,()=>{throw Object.assign(Error(),{code:'ENOENT'});}),false);
 assert.throws(()=>processStillOwned(s,()=>{throw Object.assign(Error(),{code:'EACCES'});}));assert.throws(()=>processStart('unobserved',321));
});
test('physical policy revision requires exact bytes/autoload rather than a synthetic success',()=>{
 const initial='a:1:{i:9;a:1:{s:7:"wpvivid";s:17:"external_optional";}}',s={database:'adb_http_abcdef123456',options:'adbh_abcdef123456_options'};
 const wire=Buffer.from(initial).toString('hex').toUpperCase()+'\t'+Buffer.from('off').toString('hex').toUpperCase()+'\n';
 assert.match(physicalPolicy(wire,s).revision,/^[a-f0-9]{64}$/);assert.equal(physicalPolicy(wire,s).mode,'required');assert.throws(()=>physicalPolicy(wire.replace(/\t.*/,'\t6E6F'),s));assert.throws(()=>physicalPolicy('unknown',s));
});
test('HTTP dispatch requires both explicit flags and fixed actor/head/attempt',()=>{
 const e={GITHUB_ACTIONS:'true',GITHUB_EVENT_NAME:'workflow_dispatch',GITHUB_REPOSITORY:'NichlasB/alynt-drime-backups-dashboard',GITHUB_REF:'refs/heads/master',GITHUB_ACTOR:'NichlasB',GITHUB_RUN_ATTEMPT:'1',GITHUB_WORKFLOW_REF:'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-linux-qualification.yml@refs/heads/master',QUALIFICATION_HTTP_APPROVED:'true',QUALIFICATION_GENUINE_REQUEST_APPROVED:'true',GITHUB_RUN_ID:'123',GITHUB_SHA:'a'.repeat(40),EXPECTED_SHA:'a'.repeat(40),RUNNER_OS:'Linux'};
 assert.equal(ownedHttpJob(e),true);for(const k of Object.keys(e))assert.equal(ownedHttpJob({...e,[k]:'other'}),false,k);
 const workflow=readFileSync(new URL('../../.github/workflows/source-policy-linux-qualification.yml',import.meta.url),'utf8');assert.match(workflow,/ADB_ORIGINAL_JOB_NS/);assert.match(workflow,/always\(\).*execute_genuine_http/);assert.match(workflow,/controller\.mjs --cleanup/);
});
test('required deadline/reserve/cleanup guards have assertion-killed RAM controls',async()=>{
 const file=new URL('../../scripts/ci/source-policy-http-owned-safety.mjs',import.meta.url),source=readFileSync(file,'utf8');let killed=0;
 const controls=[['elapsed<BigInt(LIMITS.job_ms)','true',m=>{let ns=1000000000n;const c=new m.OwnedHttpClock('1000000000',()=>ns);ns+=600000000000n;assert.throws(()=>c.now());}],
 ['if(this.#cleanup===null)','if(true)',m=>{let ns=1000000000n;const c=new m.OwnedHttpClock('1000000000',()=>ns);assert.equal(c.cleanupDeadline(),30000);ns+=1000000000n;assert.equal(c.cleanupDeadline(),30000);}],
 ['>=LIMITS.setup_ms+LIMITS.http_ms+LIMITS.cleanup_ms','>=0',m=>assert.throws(()=>new m.OwnedHttpClock('1000000000',()=>500001000000n).admit())]];
 for(const [from,to,oracle]of controls){assert.ok(source.includes(from));let edited=source.replace(from,to).replaceAll("'./source-policy-http-owned-source.mjs'",JSON.stringify(new URL('../../scripts/ci/source-policy-http-owned-source.mjs',import.meta.url).href)).replaceAll("'./source-policy-http-profile.mjs'",JSON.stringify(new URL('../../scripts/ci/source-policy-http-profile.mjs',import.meta.url).href));const module=await import('data:text/javascript;base64,'+Buffer.from(edited).toString('base64'));try{oracle(module);assert.fail('Control survived');}catch(e){assert.equal(e.code,'ERR_ASSERTION');assert.notEqual(e.message,'Control survived');killed++;}}
 assert.equal(killed,3);
});
