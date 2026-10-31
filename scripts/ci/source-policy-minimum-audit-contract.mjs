// Pure diagnostic DATA contracts. No engine, source, SQL or native execution.
export const AUDIT_WORKFLOW='NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-minimum-audit.yml@refs/heads/master';
const stage='(source-audit|ambient|escape|positive|noop-stale|update|race|duplicate|transform-reentry|lock|post-change|errno)';
const phase='(runtime|core-pins|core-load|owned-load|cache|profile|image|native)';
export function bootstrapEvidence(raw) {
  if(typeof raw!=='string'||Buffer.byteLength(raw)>256)return null;
  const m=/^ADBMBD1\|([01u]{4})\|(true|false|error|other|threw)\|([01u])\|([0-8]|u)\|([0-9]|1[0-6]|u)\|(never_opened|allocating|allocated|connecting|connected_unqualified|active|closed|closure_unknown|u)\|(fresh|opening|rejected|active|closing|closed|cleanup_failed|u)\|([01u])\|([01u])\|([01u])\|([01u])\|([01u])\n(?![\s\S])/.exec(raw);
  if(!m)return null;
  const [,bits,result,attempted,position,credits,native,context,close_attempted,closed,ready,dbh,handle]=m;
  return {before:{settings:bits[0],source:bits[1],context:bits[2],scope:bits[3]},result,
    after_internal_cleanup:{attempted,position,credits,native,context,close_attempted,closed,ready,dbh_present:dbh,bootstrap_handle_present:handle}};
}
export function failureEvidence(raw) {
  if(typeof raw!=='string'||Buffer.byteLength(raw)>4608)return null;
  let bootstrap=null,cause=null;
  if(raw.startsWith('ADBMBC1|')||raw.startsWith('ADBMBC2|')){
    const end=raw.indexOf('\n');if(end<0)return null;
    cause=bootstrapCauseEvidence(raw.slice(0,end+1));if(!cause)return null;
    raw=raw.slice(end+1);
  }
  if(raw.startsWith('ADBMBD1|')){
    const end=raw.indexOf('\n');if(end<0)return null;
    bootstrap=bootstrapEvidence(raw.slice(0,end+1));if(!bootstrap)return null;
    raw=raw.slice(end+1);
  }
  const attach=row=>row?{...row,...(bootstrap?{bootstrap}:{}),...(cause?{bootstrap_cause:cause}:{})}:row;
  const legacy=new RegExp('^ADBMF1\\|'+stage+'\\|'+phase+'\\|([0-9]{1,5})\\|([0-9])\\n(?![\\s\\S])').exec(raw);
  if(legacy)return attach({stage:legacy[1],phase:legacy[2],checks:Number(legacy[3]),groups:Number(legacy[4])});
  const match=new RegExp('^ADBMF2\\|'+stage+'\\|'+phase+'\\|([0-9]{1,5})\\|([0-9])\\|([a-f0-9]{64})\\|(RuntimeException|Error|TypeError|ValueError|ParseError|ArgumentCountError|ReflectionException|other)\\|([0-9]{1,5})\\|(none|worker|core|other)\\|([0-9]{1,6})\\|([01u]{7})\\|(-|!|[A-Za-z0-9_ ;]{1,4159})\\n(?![\\s\\S])').exec(raw);
  if(!match)return null;
  const [,s,p,checks,groups,assertion,exception,severity,source,line,facts,inventory]=match;
  const diag=source==='none'?Number(severity)===0&&Number(line)===0:
    [1,2,4,8,16,32,64,128,256,512,1024,2048,4096,8192,16384].includes(Number(severity))&&Number(line)>0;
  const names=['-','!'].includes(inventory)?null:inventory.split(';');
  if(!diag||(!names&&facts!=='uuuuuuu')||(names&&(names.length>64||names.some(n=>! /^[A-Za-z0-9_ ]{1,64}$/.test(n))||JSON.stringify([...new Set(names)].sort())!==JSON.stringify(names))))return null;
  return attach({stage:s,phase:p,checks:Number(checks),groups:Number(groups),assertion,exception,
    diagnostic:source==='none'?null:{severity:Number(severity),source,line:Number(line)},
    inventory:names?{names,php_root:facts[0],mysqli_root:facts[1],no_loaded_ini:facts[2],no_scanned_ini:facts[3],no_prepend:facts[4],no_append:facts[5],report_off:facts[6]}:inventory==='!'?{status:'invalid'}:null});
}
export function bootstrapCauseEvidence(raw) {
  if(typeof raw!=='string'||Buffer.byteLength(raw)>128)return null;
  const fields='(RuntimeException|Error|TypeError|ArgumentCountError|ValueError|other)\\|(bootstrap|other)\\|(0|[1-9][0-9]{0,5})\\|(never_opened|allocating|allocated|connecting|connected_unqualified|active|closed|closure_unknown)';
  const v2=new RegExp('^ADBMBC2\\|'+fields+'\\|(unobserved|unavailable|invalid|exact|legacy_greeting|different)\\n(?![\\s\\S])').exec(raw);
  const m=v2??new RegExp('^ADBMBC1\\|'+fields+'\\n(?![\\s\\S])').exec(raw);
  return m?{kind:m[1],source:m[2],line:Number(m[3]),state_before_cleanup:m[4],...(v2?{server_observation:m[5]}:{})}:null;
}
export function executionPhases(scope,argument,workflow) {
  if(scope===undefined&&argument===undefined&&workflow!==AUDIT_WORKFLOW)return ['audit','proof'];
  return scope==='audit-only-v1'&&argument==='--audit-only'&&workflow===AUDIT_WORKFLOW?['audit']:null;
}
export function releaseIssues(role,row) {
  return [row.oom?role+'-oom':null,row.running?role+'-still-running':null,row.exit!==0?role+'-nonzero-exit':null].filter(Boolean);
}
