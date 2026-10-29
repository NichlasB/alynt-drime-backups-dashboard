import test from 'node:test';
import assert from 'node:assert/strict';
import { validTerminal, reviewLog } from '../../scripts/ci/source-policy-linux-qualification-controller.mjs';

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
