// Explicit owned-lab IO only; importing never connects. No redirects or retries.
import {createConnection} from 'node:net';
import {LIMITS,HOST,ORIGIN,BRIDGE,hex,integer} from './source-policy-http-profile.mjs';
import {route} from './source-policy-http-contract.mjs';
const check=ok=>{if(!ok)throw Error('Owned HTTP wire refused');};
export class OwnedCookieJar {
 #cookies=[];
 accept(lines,now){
  check(Array.isArray(lines)&&lines.length<=16&&Number.isFinite(now));
  for(const line of lines){
   check(typeof line==='string'&&Buffer.byteLength(line)<=8192&&!/[\r\n\x00]/.test(line));
   const pieces=line.split(';'),first=pieces.shift(),equal=first.indexOf('=');check(equal>0);
   const name=first.slice(0,equal),value=first.slice(equal+1),attrs={};
   check(/^(?:wordpress_test_cookie|wordpress(?:_sec|_logged_in)?_[a-f0-9]{32}|wp-settings-(?:time-)?[1-3])$/.test(name)&&value.length<=4096&&!/[\x00-\x20\x7f;,]/.test(value));
   for(const piece of pieces){const [key,...tail]=piece.trim().split('='),lower=key.toLowerCase();check(lower&&!Object.hasOwn(attrs,lower));attrs[lower]=tail.length?tail.join('='):true;}
   check(Object.keys(attrs).every(k=>['path','domain','expires','max-age','secure','httponly','samesite'].includes(k))&&(!attrs.domain||attrs.domain==='127.0.0.1'));
   const path=attrs.path??'/';check(typeof path==='string'&&path.startsWith('/')&&path.length<=128&&!/[\x00-\x20\x7f\\]/.test(path)&&!path.includes('..'));
   if(attrs.secure)continue;
   let expires=Infinity;
   if(attrs.expires){expires=Date.parse(attrs.expires);check(Number.isFinite(expires));}
   if(attrs['max-age']!==undefined){check(typeof attrs['max-age']==='string'&&/^-?[0-9]{1,10}$/.test(attrs['max-age']));expires=now+Number(attrs['max-age'])*1000;}
   this.#cookies=this.#cookies.filter(c=>c.name!==name||c.path!==path);
   if(expires>now)this.#cookies.push({name,value,path,expires});check(this.#cookies.length<=16);
  }
 }
 header(path,now){
  check(typeof path==='string'&&Number.isFinite(now));this.#cookies=this.#cookies.filter(c=>c.expires>now);
  const out=this.#cookies.filter(c=>path===c.path||path.startsWith(c.path.endsWith('/')?c.path:c.path+'/')).sort((a,b)=>b.path.length-a.path.length).map(c=>c.name+'='+c.value).join('; ');
  check(Buffer.byteLength(out)<=16384);return out;
 }
 clear(){this.#cookies=[];}
}
export function encodeOwnedForm(pairs){
 check(Array.isArray(pairs)&&pairs.length<=8);let result='';
 for(const pair of pairs){check(Array.isArray(pair)&&pair.length===2);const [key,value]=pair;check(typeof key==='string'&&/^[A-Za-z0-9_\[\]-]{1,64}$/.test(key)&&typeof value==='string'&&Buffer.byteLength(value)<=1024&&!/[\x00\r\n]/.test(value));
  const next=(result?'&':'')+encodeURIComponent(key)+'='+encodeURIComponent(value);check(Buffer.byteLength(result)+Buffer.byteLength(next)<=LIMITS.body_bytes);result+=next;
 }return Buffer.from(result);
}
export function ownedRequestPacket({method,path,challenge,exchange,body=Buffer.alloc(0),jar,now=Date.now()}){
 check(Buffer.isBuffer(body)&&body.length<=LIMITS.body_bytes&&integer(exchange,64)&&exchange>0&&hex(challenge,32));
 const headers=[['Host',HOST],['X-ADB-Challenge',challenge],['X-ADB-Exchange',String(exchange)],['Connection','close'],['User-Agent','ADB-owned-HTTP-proof']];
 if(method==='POST')headers.push(['Content-Type','application/x-www-form-urlencoded'],['Content-Length',String(body.length)]);
 const cookie=jar.header(path.split('?')[0],now);if(cookie)headers.push(['Cookie',cookie]);
 const block=headers.map(([k,v])=>k+': '+v+'\r\n').join('')+'\r\n';
 const meta={host:HOST,method,target:path,challenge,header_bytes:Buffer.byteLength(block),body_bytes:body.length,content_length:method==='POST'?String(body.length):null,transfer_encoding:null,content_type:method==='POST'?'application/x-www-form-urlencoded':null};
 check(route(meta,challenge)!==false);const wire=Buffer.concat([Buffer.from(method+' '+path+' HTTP/1.1\r\n'+block),body]);
 return {meta,wire};
}
export function parseOwnedResponse(raw){
 check(Buffer.isBuffer(raw)&&raw.length<=LIMITS.header_bytes+LIMITS.response_bytes);
 const end=raw.indexOf('\r\n\r\n');check(end>=0&&end+4<=LIMITS.header_bytes);const head=raw.subarray(0,end).toString('latin1');check(!/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/.test(head));
 const lines=head.split('\r\n'),status=/^HTTP\/1\.[01] (200|302|400|403|409|500|503) [\x20-\x7e]*$/.exec(lines.shift());check(status&&lines.length<=64);
 const headers={},cookies=[];
 for(const line of lines){const m=/^([A-Za-z0-9-]{1,64}):[ \t]*([^\r\n]*)$/.exec(line);check(m);const key=m[1].toLowerCase(),value=m[2];if(key==='set-cookie'){cookies.push(value);continue;}check(!Object.hasOwn(headers,key));headers[key]=value;}
 const body=raw.subarray(end+4);check(body.length<=LIMITS.response_bytes&&!Object.hasOwn(headers,'transfer-encoding')&&(!Object.hasOwn(headers,'content-length')||headers['content-length']===String(body.length)));
 const location=headers.location??null;check(location===null||Number(status[1])===302&&location===ORIGIN+BRIDGE);
 return {status:Number(status[1]),body,body_bytes:body.length,header_bytes:end+4,eof:true,location,cookies};
}
export async function sendOwnedPacket(packet,left,{connect=createConnection}={}){
 check(packet&&Buffer.isBuffer(packet.wire)&&packet.wire.length<=LIMITS.header_bytes+LIMITS.body_bytes+1024&&integer(left,LIMITS.transaction_ms)&&left>0);
 // Reconstruct exact supplied framing before the only connect call. Copy blocks
 // caller mutation while the socket is pending; no client library adds bytes.
 const wire=Buffer.from(packet.wire),split=wire.indexOf('\r\n\r\n'),first=wire.indexOf('\r\n');check(split>first&&first>0);
 const start=wire.subarray(0,first).toString('ascii'),meta=packet.meta;
 check(start===meta.method+' '+meta.target+' HTTP/1.1'&&split+4-first-2===meta.header_bytes&&wire.length-split-4===meta.body_bytes&&route(meta,meta.challenge)!==false);
 const lines=wire.subarray(first+2,split).toString('ascii').split('\r\n'),seen=new Set();
 for(const line of lines){const m=/^(Host|X-ADB-Challenge|X-ADB-Exchange|Connection|User-Agent|Content-Type|Content-Length|Cookie): ([\x20-\x7e]+)$/.exec(line);check(m&&!seen.has(m[1]));seen.add(m[1]);const expected={Host:HOST,'X-ADB-Challenge':meta.challenge,Connection:'close','User-Agent':'ADB-owned-HTTP-proof','Content-Type':meta.content_type,'Content-Length':meta.content_length};if(Object.hasOwn(expected,m[1]))check(m[2]===expected[m[1]]);if(m[1]==='X-ADB-Exchange')check(/^[1-9][0-9]?$/.test(m[2])&&Number(m[2])<=64);}
 check(['Host','X-ADB-Challenge','X-ADB-Exchange','Connection','User-Agent'].every(k=>seen.has(k))&&(meta.method==='POST'?seen.has('Content-Length')&&seen.has('Content-Type'):!seen.has('Content-Length')&&!seen.has('Content-Type')));
 return new Promise((resolve,reject)=>{
  let socket,settled=false,bytes=0,raw=Buffer.alloc(0);const finish=(error,value)=>{if(settled)return;settled=true;clearTimeout(timer);socket?.destroy();error?reject(Error('Owned HTTP exchange refused')):resolve(value);};
  const timer=setTimeout(()=>finish(true),left);
  try{socket=connect({host:'127.0.0.1',port:18777});socket.on('error',()=>finish(true));socket.on('connect',()=>socket.write(wire));socket.on('data',chunk=>{if(settled)return;bytes+=chunk.length;if(bytes>LIMITS.header_bytes+LIMITS.response_bytes){finish(true);return;}raw=Buffer.concat([raw,chunk]);const h=raw.indexOf('\r\n\r\n');if((h<0&&raw.length>LIMITS.header_bytes)||(h>=0&&(h+4>LIMITS.header_bytes||raw.length-h-4>LIMITS.response_bytes)))finish(true);});socket.on('end',()=>{try{finish(false,parseOwnedResponse(raw));}catch{finish(true);}});socket.on('close',()=>{if(!settled)finish(true);});}catch{finish(true);}
 });
}
