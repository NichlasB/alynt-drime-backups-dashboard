import test from 'node:test';
import assert from 'node:assert/strict';
import {EventEmitter} from 'node:events';
import {OwnedCookieJar,encodeOwnedForm,ownedRequestPacket,parseOwnedResponse,sendOwnedPacket} from '../../scripts/ci/source-policy-http-owned-wire.mjs';
const challenge='a'.repeat(32);
const packet=()=>ownedRequestPacket({method:'POST',path:'/_adb-owned-source-policy',challenge,exchange:1,jar:new OwnedCookieJar(),body:encodeOwnedForm([['_wpnonce','actual'],['dashboard_site_id[]','7']])});
test('owned finite form and raw framing preserve array syntax without repairing intent',()=>{
 const p=packet();assert.equal(p.meta.body_bytes,Buffer.byteLength('_wpnonce=actual&dashboard_site_id%5B%5D=7'));
 assert.equal(p.meta.content_length,String(p.meta.body_bytes));assert.match(p.wire.toString(),/Content-Length: [0-9]+\r\n/);assert.match(p.wire.toString(),/dashboard_site_id%5B%5D/);
 for(const pairs of [[['bad\r\nname','x']],[['x','a'.repeat(1025)]],Array(9).fill(['x','a']),[['x',{}]]])assert.throws(()=>encodeOwnedForm(pairs));
});
test('private jars retain stock cookie/path semantics without sharing user sessions',()=>{
 const one=new OwnedCookieJar(),two=new OwnedCookieJar(),name='wordpress_logged_in_'+'b'.repeat(32);
 one.accept([name+'=private%7Ctoken; Path=/; HttpOnly'],1000);assert.equal(one.header('/_adb-owned-source-policy',1000),name+'=private%7Ctoken');assert.equal(two.header('/',1000),'');
 one.accept([name+'=deleted; Path=/; Max-Age=0'],1000);assert.equal(one.header('/',1001),'');
 assert.throws(()=>one.accept(['foreign=secret; Path=/'],1000));assert.throws(()=>one.accept([name+'=x; Domain=example.com'],1000));
});
test('bounded EOF parsing refuses incomplete framing, false lengths and foreign redirects',()=>{
 const raw=s=>Buffer.from(s),valid='HTTP/1.1 200 OK\r\nContent-Length: 2\r\n\r\n{}';assert.equal(parseOwnedResponse(raw(valid)).body.toString(),'{}');
 for(const s of [valid.replace('Length: 2','Length: 3'),valid.replace('Content-Length: 2','Transfer-Encoding: chunked'),valid.replace('200 OK','302 Found').replace('Content-Length: 2','Location: https://example.com'),valid.replace('\r\n\r\n','\r\n'),valid.replace('Content-Length: 2','Content-Length: 2\r\nContent-Length: 2')])assert.throws(()=>parseOwnedResponse(raw(s)));
});
test('actual pre-send guards reject changed wire and budgets with zero socket calls',async()=>{
 let connects=0;const options={connect:()=>{connects++;throw Error('Must never connect');}};
 for(const mutate of [p=>({...p,wire:Buffer.from(p.wire.toString().replace('Host: 127.0.0.1:18777','Host: example.com'))}),p=>({...p,meta:{...p.meta,body_bytes:16385}}),p=>({...p,wire:Buffer.from(p.wire.toString().replace('Content-Length:','Transfer-Encoding:'))}),p=>({...p,meta:{...p.meta,challenge:'b'.repeat(32)}})])await assert.rejects(sendOwnedPacket(mutate(packet()),5000,options));
 await assert.rejects(sendOwnedPacket(packet(),5001,options));assert.equal(connects,0);
});
test('actual send algorithm emits the exact packet once and requires complete EOF',async()=>{
 let calls=0,written=null;const p=packet();
 const connect=options=>{calls++;assert.deepEqual(options,{host:'127.0.0.1',port:18777});const s=new EventEmitter();s.destroy=()=>{};s.write=b=>{written=Buffer.from(b);queueMicrotask(()=>{s.emit('data',Buffer.from('HTTP/1.1 200 OK\r\nContent-Length: 2\r\n\r\n{}'));s.emit('end');});};queueMicrotask(()=>s.emit('connect'));return s;};
 const r=await sendOwnedPacket(p,1000,{connect});assert.equal(calls,1);assert.deepEqual(written,p.wire);assert.equal(r.eof,true);assert.equal(r.body.toString(),'{}');
});
