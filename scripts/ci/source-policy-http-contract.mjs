// Pure proposed HTTP input/result consistency. Never an endpoint or auth implementation.
import {HTTP_PROFILE,LIMITS,ORIGIN,HOST,BRIDGE,record,integer,hex,identityData} from './source-policy-http-profile.mjs';
export const HTTP_CASES=Object.freeze(['view','save','noop','stale','anonymous','incapable','nonce-missing','nonce-action','nonce-user','nonce-session','nonce-compound','site-compound','revision-bad']);
const results=Object.freeze(['view','saved','unchanged','source_policy_conflict','source_policy_forbidden','source_policy_forbidden',...Array(7).fill('source_policy_invalid')]);
export function route(value,challenge){
 const row=record(value,['host','method','target','challenge','header_bytes','body_bytes','content_length','transfer_encoding','content_type']);
 if(!row||!hex(challenge,32)||row.challenge!==challenge||row.host!==HOST||!integer(row.header_bytes,LIMITS.header_bytes)||row.header_bytes===0||!integer(row.body_bytes,LIMITS.body_bytes)||row.transfer_encoding!==null)return false;
 if(row.method==='GET'){
  if(row.body_bytes!==0||row.content_length!==null||row.content_type!==null)return false;
  if(row.target==='/wp-login.php')return 'login-form';
  if(row.target===BRIDGE)return 'view';
  return row.target===BRIDGE+'?control=wrong-action'?'nonce-control':false;
 }
 if(row.method!=='POST'||row.body_bytes===0||row.content_length!==String(row.body_bytes)||row.content_type!=='application/x-www-form-urlencoded')return false;
 return row.target==='/wp-login.php'?'login-submit':row.target===BRIDGE?'submit':false;
}
export function loginFields(value){
 const row=record(value,['log','pwd','wp-submit','redirect_to','testcookie']);
 return !!(row&&typeof row.log==='string'&&/^[A-Za-z0-9_-]{1,60}$/.test(row.log)&&typeof row.pwd==='string'&&row.pwd.length>0&&Buffer.byteLength(row.pwd)<=256&&!/[\x00-\x1f\x7f]/.test(row.pwd)&&typeof row['wp-submit']==='string'&&row['wp-submit'].length>0&&row['wp-submit'].length<=64&&row.redirect_to===ORIGIN+BRIDGE&&row.testcookie==='1');
}
export function ledgerMatches(value,phase){
 const row=record(value,['phase','sql','policy_reads','policy_writes','owner_allocations','unknown_changes','mail','outbound']);
 if(!row||!['setup','bootstrap','auth'].includes(phase)||row.phase!==phase)return false;
 const max=phase==='setup'?LIMITS.setup_sql:phase==='bootstrap'?LIMITS.bootstrap_sql:LIMITS.auth_sql;
 if(!Object.keys(row).filter(k=>k!=='phase').every(k=>integer(row[k],max))||row.policy_reads+row.policy_writes>row.sql||row.unknown_changes!==0||row.outbound!==0||row.owner_allocations!==0)return false;
 return phase==='setup'?row.mail<=1:row.mail===0&&row.policy_reads===0&&row.policy_writes===0;
}
export function responseMatches(value,kind){
 const row=record(value,['status','body_bytes','header_bytes','eof','location']);
 if(!row||!['login-form','login-submit','view','submit','nonce-control'].includes(kind)||!integer(row.body_bytes,LIMITS.response_bytes)||!integer(row.header_bytes,LIMITS.header_bytes)||row.header_bytes===0||row.eof!==true)return false;
 if(kind==='login-submit')return row.status===302&&row.location===ORIGIN+BRIDGE;
 return row.location===null&&[200,400,403,409].includes(row.status)&&(kind==='submit'||row.status===200);
}
export function caseMatches(value,expected,index){
 const keys=['event','model','run','head','image','index','status','result','checks','bootstrap','auth','records','connects','commands','policy_reads','policy_writes','before','after','sentinel','revision','mode','unknown_changes','production_ready'];
 const row=record(value,keys),want=record(expected,['run','head','image','root','before','after','sentinel','revision']);
 if(!row||!want||!identityData({run:want.run,head:want.head,image:want.image,root:want.root})||!integer(index,12)||row.event!=='http-case-complete'||row.model!==HTTP_PROFILE.model||row.index!==index||row.production_ready!==false)return false;
 for(const [key,n]of [['run',32],['head',40],['image',64],['before',64],['after',64],['sentinel',64]])if(!hex(want[key],n)||row[key]!==want[key])return false;
 const status=index<3?200:index===3?409:index<6?403:400;
 if(row.status!==status||row.result!==results[index]||!integer(row.checks,99999)||row.checks===0||!integer(row.bootstrap,256)||!integer(row.auth,256)||row.unknown_changes!==0)return false;
 const positive=index<4,n=positive?1:0;
 if(row.records!==n||row.connects!==n||row.policy_reads!==n||row.policy_writes!==(index===1?1:0)||!integer(row.commands,9999)||(positive?row.commands<=n:row.commands!==0))return false;
 if(index===1?row.before===row.after:row.before!==row.after)return false;
 return index===0?row.mode==='required'&&hex(want.revision,64)&&row.revision===want.revision:row.mode===null&&want.revision===null&&row.revision===null;
}
// Extra genuine views refresh private nonce/revision DATA, never extra case credit.
export function viewObservationMatches(value,expected){
 const keys=['event','model','run','head','image','index','status','result','checks','bootstrap','auth','records','connects','commands','policy_reads','policy_writes','before','after','sentinel','revision','mode','unknown_changes','production_ready'];
 const row=record(value,keys),want=record(expected,['run','head','image','root','before','after','sentinel','revision','mode']);
 if(!row||!want||!['required','external_optional'].includes(want.mode)||row.mode!==want.mode||row.event!=='http-view-complete'||row.index!==null)return false;
 // Only the already-validated auxiliary tag/mode are adapted to the shared view
 // invariant; physical identity/counts/revision/policy DATA are never repaired.
 const {mode,...base}=want;
 return caseMatches({...row,event:'http-case-complete',index:0,mode:'required'},base,0);
}
