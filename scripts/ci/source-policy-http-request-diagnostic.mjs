// Private failure DATA only. Never a receipt substitute or runtime authority.
import {record,integer,LIMITS} from './source-policy-http-profile.mjs';
export const REQUEST_DIAGNOSTIC_PREFIX='ADBH_REQUEST_FAILURE ';
const stages=['exchange','route','early-hooks','core-bootstrap','core-ready','login','bridge','shutdown','receipt','unavailable'];
function packet(value){
 const row=record(value,['event','exchange','stage','exception','origin','line','assertion']);
 return row&&row.event==='owned-request-refused'&&integer(row.exchange,64)&&stages.includes(row.stage)
  &&['RuntimeException','Error','TypeError','ValueError','ParseError','other','unavailable'].includes(row.exception)
  &&['core','proof','other'].includes(row.origin)&&integer(row.line,100000)
  &&(row.assertion===null||typeof row.assertion==='string'&&/^[a-f0-9]{64}$/.test(row.assertion))?row:null;
}
export class RequestDiagnosticStream {
 #line='';#dropping=false;#first=null;
 push(bytes){
  if(this.#first)return;
  if(!Buffer.isBuffer(bytes)||bytes.length>LIMITS.diagnostic_bytes)return;
  for(const byte of bytes){
   if(byte===10){
    if(!this.#dropping&&this.#line.startsWith(REQUEST_DIAGNOSTIC_PREFIX)){
     try{const row=packet(JSON.parse(this.#line.slice(REQUEST_DIAGNOSTIC_PREFIX.length)));if(row){this.#first=row;this.#line='';return;}}catch{/* Unknown/private lines are discarded, never exported. */}
    }
    this.#line='';this.#dropping=false;
   }else if(!this.#dropping){if(this.#line.length<1023)this.#line+=String.fromCharCode(byte);else{this.#line='';this.#dropping=true;}}
  }
 }
 evidence(exchange){return this.#first?.exchange===exchange?{...this.#first}:null;}
}
const scalar=(value,key)=>{try{return Object.getOwnPropertyDescriptor(value,key)?.value;}catch{return undefined;}};
export function requestFailureSummary(exchange,response,error){
 const status=scalar(response,'status'),body=scalar(response,'body_bytes'),headers=scalar(response,'header_bytes'),eof=scalar(response,'eof');
 return {event:'owned-http-receipt-failure',exchange:integer(exchange,64)?exchange:0,
  receipt:scalar(error,'code')==='ENOENT'?'absent':error instanceof SyntaxError?'invalid-json':'unavailable',
  status:[200,302,400,403,409,500,503].includes(status)?status:null,
  body_bytes:integer(body,LIMITS.response_bytes)?body:null,header_bytes:integer(headers,LIMITS.header_bytes)?headers:null,eof:typeof eof==='boolean'?eof:null};
}
export function readRequiredReceipt(read,exchange,response,onFailure){
 try{return read();}catch(error){
  try{onFailure(requestFailureSummary(exchange,response,error));}catch{/* Diagnostic failure must not replace the original receipt refusal. */}
  throw error;
 }
}
