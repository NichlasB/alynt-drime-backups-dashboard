import test from 'node:test';
import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {readFileSync} from 'node:fs';
import {fileURLToPath} from 'node:url';
import {createHash} from 'node:crypto';
import {setupFailureSummary} from '../../scripts/ci/source-policy-http-owned-controller.mjs';
const observer=fileURLToPath(new URL('../../scripts/ci/source-policy-http-owned-observer.php',import.meta.url));
const diagnostic=fileURLToPath(new URL('../../scripts/ci/source-policy-http-owned-diagnostic.php',import.meta.url));
const source=readFileSync(observer,'utf8');
function run(body,mutation=null){
 const load=mutation===null?`require ${JSON.stringify(observer)};`:`eval(base64_decode('${Buffer.from(mutation.replace(/^<\?php/,'')).toString('base64')}'));`;
 const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',load+`require ${JSON.stringify(diagnostic)};function need($ok,$label){if(!$ok)throw new RuntimeException($label);}`+body],{encoding:'utf8',timeout:5000,maxBuffer:8192});
 return r;
}
function good(body){const r=run(body);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.equal(r.stdout,'ok');}
const branchProof=String.raw`
$p='adbh_abcdef123456_';$expiry="DELETE a, b FROM {$p}options a, {$p}options b WHERE a.option_name LIKE 'bad' AND a.option_name NOT LIKE 'bad' AND b.option_name = CONCAT( 'bad', SUBSTRING( a.option_name, 12 ) ) AND b.option_value < 1794610800";
$cases=array(
 array('SELECT 1','bad',true,'prefix'),array(null,$p,true,'tokens-input'),
 array("SELECT '".chr(92),$p,true,'tokens-escape'),array("SELECT 'open",$p,true,'tokens-literal'),
 array(str_repeat("'x',",2049),$p,true,'tokens-literal'),array('SELECT 1; DELETE FROM foreign',$p,true,'tokens-skeleton'),
 array("SHOW TABLES LIKE 'foreign'",$p,true,'show-tables'),array('SHOW TABLES',$p,false,'show-tables'),
 array("DELETE a, b FROM {$p}options a, foreign b WHERE 1=1",$p,true,'expiry-shape'),array($expiry,$p,true,'expiry-values'),
 array('TRUNCATE TABLE '.$p.'options',$p,true,'verb'),array('SELECT 1',$p,true,'tables'),
 array('SELECT * FROM foreign_options',$p,true,'table-scope'),array('UPDATE '.$p.'options SET option_value=1',$p,false,'write-scope')
);
foreach($cases as $case){$reason=null;need(null===adbh_owned_sql_class($case[0],$case[1],$case[2],false,$reason),'still-refused');need($reason===$case[3],'fixed-branch');}
need('bootstrap-read'===adbh_owned_sql_class('SELECT * FROM '.$p.'options',$p,false,false),'available');echo 'ok';`;
test('all twelve fixed branch labels describe actual refusals without changing positive availability',()=>good(branchProof));
const originProof=String.raw`
$frames=array(array('file'=>'/owned/proof/private.php','line'=>1,'args'=>array('secret-sql-password')),
 array('file'=>'/owned/core/wp-includes/class-wp-hook.php','line'=>1),array('file'=>'/owned/core/wp-includes/plugin.php','line'=>2),
 array('file'=>'/owned/core/wp-includes/class-wpdb.php','line'=>3),array('file'=>'/owned/core/wp-admin/includes/upgrade.php','line'=>3027));
need(array('caller'=>'upgrade','line'=>3027)===adbh_owned_sql_origin($frames,'/owned'),'core-origin');
need(array('caller'=>'wpdb','line'=>3)===adbh_owned_sql_origin(array_slice($frames,0,4),'/owned'),'wpdb-fallback');
need(array('caller'=>'other-core','line'=>7)===adbh_owned_sql_origin(array(array('file'=>'/owned/core/wp-includes/unknown.php','line'=>7)),'/owned'),'unknown-not-guessed');
foreach(array(null,new stdClass(),array_fill(0,17,$frames[4]),array(array('file'=>'/owned/core/../private.php','line'=>99)),array(array('file'=>'/owned/coreevil/wp-includes/option.php','line'=>99)),array(array('file'=>new stdClass(),'line'=>99)),array(array('file'=>str_repeat('x',4097),'line'=>99))) as $bad){need(array('caller'=>'unavailable','line'=>0)===adbh_owned_sql_origin($bad,'/owned'),'unavailable');}
foreach(array(-1,100001,'123',true,null,new stdClass()) as $line){need(array('caller'=>'option','line'=>0)===adbh_owned_sql_origin(array(array('file'=>'/owned/core/wp-includes/option.php','line'=>$line)),'/owned'),'bounded-line');}
need(false===strpos(serialize(adbh_owned_sql_origin($frames,'/owned')),'secret'),'no-arguments');echo 'ok';`;
test('fixed core mapping skips hooks/DB wrappers and refuses malformed, foreign or over-budget trace DATA',()=>good(originProof));
const captureProof=String.raw`
function adbh_check($ok,$label){if(!$ok){$GLOBALS['adbh_failure']=hash('sha256',$label);throw new RuntimeException('secret-sql-password');}}
define('ADB_HTTP_WORKER','setup');$GLOBALS['adbh_stage']='install';$GLOBALS['adbh_private']=array('prefix'=>'adbh_abcdef123456_');$GLOBALS['adbh_ledger']=array('setup'=>0,'bootstrap'=>0,'auth'=>0);
try{adbh_owned_observe('TRUNCATE TABLE foreign');need(false,'throw-required');}catch(RuntimeException $e){$packet=adbh_setup_diagnostic($e,'/owned');}
need(isset($packet['sql'])&&array('branch'=>'verb','caller'=>'unavailable','line'=>0)===$packet['sql'],'capture-required');
$first=$packet['sql'];$GLOBALS['adbh_private']['prefix']='bad';try{adbh_owned_observe('SELECT 1');}catch(RuntimeException $e){}
need($first===$GLOBALS['adbh_sql_refusal'],'first-only');need(0===$GLOBALS['adbh_ledger']['setup'],'refused-before-accounting');
need(false===strpos(serialize($packet),'secret-sql-password'),'private-output');echo 'ok';`;
test('actual refusal capture preserves first fact, throws and exports only attached bounded DATA',()=>good(captureProof));
test('request refusals and non-SQL failures cannot attach stale SQL facts',()=>{
 good(String.raw`function adbh_check($ok,$label){if(!$ok)throw new RuntimeException($label);}define('ADB_HTTP_WORKER','request');$GLOBALS['adbh_private']=array('prefix'=>'adbh_abcdef123456_');try{adbh_owned_observe('TRUNCATE TABLE foreign');}catch(RuntimeException $e){}need(!isset($GLOBALS['adbh_sql_refusal']),'request-no-capture');echo 'ok';`);
 good(String.raw`$GLOBALS['adbh_sql_refusal']=array('branch'=>'verb','caller'=>'upgrade','line'=>3027);$GLOBALS['adbh_failure']=hash('sha256','another-assertion');need(!isset(adbh_setup_diagnostic(new RuntimeException('secret'),'/owned')['sql']),'assertion-binding');$GLOBALS['adbh_failure']=hash('sha256','sql-shape');need(!isset(adbh_setup_diagnostic(new Error('secret'),'/owned')['sql']),'exception-binding');echo 'ok';`);
});
test('PHP exporter rejects malformed SQL DATA rather than serializing private values',()=>good(String.raw`
$GLOBALS['adbh_failure']=hash('sha256','sql-shape');$e=new RuntimeException('secret');$valid=array('branch'=>'verb','caller'=>'upgrade','line'=>3027);
foreach(array(array('branch'=>'secret'),array('caller'=>'/private/secret.php'),array('line'=>'3027'),array('line'=>100001),array('caller'=>'unavailable'),array('raw'=>'private-sql')) as $delta){$GLOBALS['adbh_sql_refusal']=array_replace($valid,$delta);need(!isset(adbh_setup_diagnostic($e,'/owned')['sql']),'export-denial');}
$GLOBALS['adbh_sql_refusal']=$valid;need($valid===adbh_setup_diagnostic($e,'/owned')['sql'],'export-positive');
foreach(array('prefix','tokens-input','tokens-escape','tokens-literal','tokens-skeleton','show-tables','expiry-shape','expiry-values','verb','tables','table-scope','write-scope') as $branch){$GLOBALS['adbh_sql_refusal']=array_replace($valid,array('branch'=>$branch));need($GLOBALS['adbh_sql_refusal']===adbh_setup_diagnostic($e,'/owned')['sql'],'branch-positive');}
foreach(array('unavailable','wpdb','other-core','upgrade','schema','option','user','meta','functions','load','post','taxonomy','pluggable','capabilities','formatting') as $caller){$GLOBALS['adbh_sql_refusal']=array_replace($valid,array('caller'=>$caller,'line'=>0));need($GLOBALS['adbh_sql_refusal']===adbh_setup_diagnostic($e,'/owned')['sql'],'caller-positive');}echo 'ok';`));
const wire={event:'owned-setup-refused',stage:'install',exception:'RuntimeException',origin:'proof',line:22,assertion:'a2d19317633ec1cec3dce5975aa0e3604cc4afba6aedcc747c06f07ca63effac',sql:{branch:'verb',caller:'upgrade',line:3027}};
test('parent accepts optional exact SQL DATA only under the matching legacy SQL assertion',()=>{
 const read=r=>setupFailureSummary(1,null,'',JSON.stringify(r)).detail;
 assert.deepEqual(read(wire),wire);
 for(const branch of ['prefix','tokens-input','tokens-escape','tokens-literal','tokens-skeleton','show-tables','expiry-shape','expiry-values','verb','tables','table-scope','write-scope']){const r={...wire,sql:{...wire.sql,branch}};assert.deepEqual(read(r),r);}
 for(const caller of ['unavailable','wpdb','other-core','upgrade','schema','option','user','meta','functions','load','post','taxonomy','pluggable','capabilities','formatting']){const r={...wire,sql:{...wire.sql,caller,line:0}};assert.deepEqual(read(r),r);}
 assert.ok(Buffer.byteLength(JSON.stringify(wire))<1024);
 const {sql,...legacy}=wire;assert.deepEqual(read(legacy),legacy);
 for(const delta of [{branch:'private-sql'},{caller:'/private/file.php'},{line:-1},{line:100001},{line:'3027'},{caller:'unavailable'},{raw:'secret'}])assert.equal(read({...wire,sql:{...sql,...delta}}),null);
 for(const delta of [{assertion:'a'.repeat(64)},{exception:'Error'},{raw:'secret'},{sql:null}])assert.equal(read({...wire,...delta}),null);
});
test('capture/retention guards are assertion-killed controls, not parse failures',()=>{
 for(const [before,after,label] of [
  ['null === $class && $diagnose && ! isset( $GLOBALS[\'adbh_sql_refusal\'] )','false','capture-required'],
  ['&& ! isset( $GLOBALS[\'adbh_sql_refusal\'] )','','first-only'],
 ]){
  assert.equal(source.split(before).length,2);const r=run(captureProof,source.replace(before,after));assert.notEqual(r.status,0);assert.match(r.stdout+r.stderr,new RegExp('RuntimeException: '+label));assert.doesNotMatch(r.stdout+r.stderr,/Parse error|syntax error/);
 }
 assert.match(source,/debug_backtrace\( DEBUG_BACKTRACE_IGNORE_ARGS, 16 \)/);
});
test('origin availability, trace ceiling and root binding have semantic controls',()=>{
 for(const [before,after,label] of [
  ["$files[ $file ] ?? 'other-core'","'unavailable'",'core-origin'],
  ['count( $frames ) > 16','false','unavailable'],
  [" || 0 !== strpos( $frame['file'], $root . '/core/' )",'','core-origin'],
 ]){
  assert.equal(source.split(before).length,2);const r=run(originProof,source.replace(before,after));assert.notEqual(r.status,0);assert.match(r.stdout+r.stderr,new RegExp('RuntimeException: '+label));assert.doesNotMatch(r.stdout+r.stderr,/Parse error|syntax error/);
 }
});
test('classifier guard source remains baseline-equivalent apart from fixed diagnostic assignments',()=>{
 const parts=['adbh_owned_tokens','adbh_owned_sql_class'].map(n=>source.replace(/\r\n/g,'\n').match(new RegExp('function '+n+'[^]*?\\n}'))[0].replace(/, &\$reason = null/g,'').replace(/\n[ \t]*\$reason\s*= '[a-z-]+';/g,'').replace('adbh_owned_tokens( $sql, $reason )','adbh_owned_tokens( $sql )').replace(/[ \t]+/g,' '));
 assert.equal(createHash('sha256').update(parts.join('\n')).digest('hex'),'74293a2710d49ba090f3b2be378e7473e981b9a09780d184e3f38f76125f498b');
});
