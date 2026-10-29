import test from 'node:test';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { validTerminal, reviewLog } from '../../scripts/ci/source-policy-linux-qualification-controller.mjs';
import * as supervisor from '../../scripts/ci/source-policy-linux-qualification-controller.mjs';

const image='a'.repeat(64),run='b'.repeat(32),pid=123,host='a'.repeat(12);
const terminal=()=>({event:'closed',image,run,pid,checks:200,groups:9,mode:0,closed:true});

test('held terminal requires every exact source/run/PID/closure field',()=>{
  assert.equal(validTerminal(terminal(),image,run,pid),true);
  for(const [key,value] of Object.entries({event:'pending',image:'c'.repeat(64),run:'c'.repeat(32),pid:124,checks:0,groups:8,mode:3,closed:false,extra:true}))
    assert.equal(validTerminal({...terminal(),[key]:value},image,run,pid),false);
  for(const key of Object.keys(terminal())) { const row=terminal();delete row[key];assert.equal(validTerminal(row,image,run,pid),false); }
  for(const key of ['pid','checks','groups','mode']) assert.equal(validTerminal({...terminal(),[key]:String(terminal()[key])},image,run,pid),false);
  assert.equal(validTerminal(terminal(),image,run,'123'),false);
  assert.equal(validTerminal(terminal(),'a',run,pid),false);
});

test('diagnostics retain complete bounded raw context and a separately selected view',()=>{
  const raw='2026-10-29 08:00:00 0 [Note] InnoDB: native AIO context\n2026-10-29 08:00:00 0 [Warning] failed to retrieve the MAC address\n';
  const result=reviewLog(raw,host);
  assert.equal(result.bytes,Buffer.byteLength(raw));assert.match(result.sha256,/^[a-f0-9]{64}$/);
  assert.equal(result.full.length,2);assert.equal(result.selected.length,2);
});

test('warnings never authorize altered hostname, changed text, fatal or over-budget evidence',()=>{
  const warning=`2026-10-29 08:00:00 0 [Warning] 'user' entry 'root@${host}' ignored in --skip-name-resolve mode.\n`;
  assert.equal(reviewLog(warning,host).full.length,1);
  for(const raw of [warning.replace(host,'c'.repeat(12)),warning.replace('ignored','accepted'),'2026-10-29 08:00:00 0 [Warning] unknown\n','[ERROR] source\n','fatal failure','x'.repeat(65537),'x'.repeat(1025)+'\n','note\n'.repeat(129),warning.repeat(33),'bad\0bytes'])
    assert.throws(()=>reviewLog(raw,host));
});

test('only canonical zero-padded and space-padded MariaDB hours preserve the exact warning',()=>{
  const message='0 [Warning] failed to retrieve the MAC address\n';
  for(const hour of ['00','09','10','23',' 0',' 9']) {
    const raw='2026-10-29 '+hour+':31:02 '+message;
    assert.deepEqual(reviewLog(raw,host).full,[raw.trimEnd()]);
    assert.throws(()=>reviewLog(raw.replace('MAC address','other address'),host));
  }
  for(const hour of ['9',' 09','  9','24','99','-1']) assert.throws(()=>reviewLog('2026-10-29 '+hour+':31:02 '+message,host));
  assert.throws(()=>reviewLog('2026-10-29  9:61:02 '+message,host));
});

test('child failure summary retains safe stage/counts/status but never raw streams or errors',()=>{
  assert.equal(typeof supervisor.childFailureSummary,'function');
  const worker={event:'failure',stage:'source-audit',phase:'core-load',checks:17,groups:0,assertion:'c'.repeat(64),exception:'RuntimeException',diagnostic:{severity:2,source:'core',line:125}};
  const summary=supervisor.childFailureSummary({status:1,signal:null,error:null,stdout:'private-password-stdout',stderr:JSON.stringify(worker)+'\n'});
  assert.equal(summary.status,1);assert.deepEqual(summary.worker,worker);
  assert.equal(summary.stdout.bytes,23);assert.match(summary.stdout.sha256_prefix,/^[a-f0-9]{64}$/);
  assert.equal(JSON.stringify(summary).includes('private-password-stdout'),false);
  const unsafe={...worker,exception:'private-secret',raw:'do-not-emit'};
  const refused=supervisor.childFailureSummary({status:null,signal:'SIGKILL',error:{code:'ETIMEDOUT',message:'private-message'},stdout:'x'.repeat(17000),stderr:JSON.stringify(unsafe)+'\n'});
  assert.equal(refused.status,null);assert.equal(refused.signal,'SIGKILL');assert.equal(refused.error,'ETIMEDOUT');
  assert.equal(refused.worker,null);assert.equal(refused.stdout.truncated,true);
  const multibyte='🌍'.repeat(5000), bytes=Buffer.from(multibyte);
  const bounded=supervisor.childFailureSummary({status:1,stdout:multibyte});
  assert.equal(bounded.stdout.bytes,20000);assert.equal(bounded.stdout.truncated,true);
  assert.equal(bounded.stdout.sha256_prefix,createHash('sha256').update(bytes.subarray(0,16384)).digest('hex'));
  for(const token of ['private-message','private-secret','do-not-emit']) assert.equal(JSON.stringify(refused).includes(token),false);
  for(const [key,value] of Object.entries({stage:'secret',phase:'unknown',checks:-1,groups:10,assertion:'bad',exception:'secret',diagnostic:{severity:2,source:'private-path',line:125}}))
    assert.equal(supervisor.childFailureSummary({status:1,stderr:JSON.stringify({...worker,[key]:value})+'\n'}).worker,null);
});
