import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {spawnSync} from 'node:child_process';
const root=new URL('../../',import.meta.url);
const read=file=>readFileSync(new URL(file,root),'utf8');
const worker=read('scripts/ci/source-policy-http-owned-worker.php');
const router=read('scripts/ci/source-policy-http-owned-router.php');
const observer=read('scripts/ci/source-policy-http-owned-observer.php');
const literal=s=>"'"+s.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'";
function call(source,verb){
 const matches=source.match(new RegExp(verb+"_filter\\( 'query', 'adbh_owned_observe', [^;]+?\\)",'g'));
 assert.equal(matches?.length,1,`one actual ${verb} call`);return matches[0];
}
// Isolated hook/prepare-order model only: no core loading, wpdb, SQL or IO.
// Pinned class-wpdb.php:2427-2459 registers restoration at 0, potentially AFTER
// our early observer registration. Same-priority hooks retain insertion order.
const hooks=String.raw`
function need($ok,$label){if(!$ok)throw new RuntimeException($label);}
function adbh_check($ok,$label){need($ok,$label);}
function add_filter($tag,$callback,$priority=10,$accepted=1){$GLOBALS['hooks'][$priority][$callback]=$callback;return true;}
function remove_filter($tag,$callback,$priority=10){$found=isset($GLOBALS['hooks'][$priority][$callback]);unset($GLOBALS['hooks'][$priority][$callback]);return $found;}
function has_filter($tag,$callback){foreach($GLOBALS['hooks'] as $priority=>$entries){if(isset($entries[$callback]))return $priority;}return false;}
function apply_filters($tag,$sql){ksort($GLOBALS['hooks'],SORT_NUMERIC);foreach($GLOBALS['hooks'] as $entries){foreach($entries as $callback)$sql=$callback($sql);}return $sql;}
function restore_percent($sql){return str_replace($GLOBALS['marker'],'%',$sql);}
function reset_ledger(){$GLOBALS['adbh_ledger']=array('setup'=>0,'bootstrap'=>0,'auth'=>0);}
function execute_simulated($sql){$out=apply_filters('query',$sql);++$GLOBALS['executed'];return $out;}
function refused($sql,$label){$before=$GLOBALS['executed'];try{execute_simulated($sql);need(false,'denial-required');}catch(RuntimeException $e){need($label===$e->getMessage(),'refusal-label');}need($before===$GLOBALS['executed'],'pre-execution-denial');}
function expiry($site=false){
 $p=$GLOBALS['adbh_private']['prefix'];$name=$site?'_site_transient_':'_transient_';$timeout=$name.'timeout_';$offset=$site?17:12;
 $quote=static function($s){return "'".str_replace('\\','\\\\',$s)."'";};
 return "DELETE a, b FROM {$p}options a, {$p}options b WHERE a.option_name LIKE ".$quote(addcslashes($name,'_%\\').'%')." AND a.option_name NOT LIKE ".$quote(addcslashes($timeout,'_%\\').'%')." AND b.option_name = CONCAT( ".$quote($timeout).", SUBSTRING( a.option_name, {$offset} ) ) AND b.option_value < 1794610800";
}
$GLOBALS['hooks']=array();$GLOBALS['executed']=0;$GLOBALS['adbh_private']=array('prefix'=>'adbh_abcdef123456_');reset_ledger();
`;
function run(body,{w=worker,r=router,o=observer,purpose='setup'}={}){
 const load=`eval(base64_decode('${Buffer.from(o.replace(/^<\?php/,'')).toString('base64')}'));`;
 const php=hooks+load+`define('ADB_HTTP_WORKER',${literal(purpose)});`+call(w,'add')+`;add_filter('query','restore_percent',0,1);`+body.replaceAll('/*REMOVE*/',call(r,'remove')).replaceAll('/*REATTACH*/',call(r,'add'));
 return spawnSync(process.env.PHP_BINARY??'php',['-n','-r',php],{encoding:'utf8',timeout:5000,maxBuffer:8192});
}
function passed(r){assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.equal(r.stdout,'ok');}
const setup=String.raw`
foreach(array(str_repeat('a',64),str_repeat('b',64)) as $seed){
 $GLOBALS['marker']='{'.$seed.'}';
 foreach(array(false,true) as $site){$plain=expiry($site);$prepared=str_replace('%',$GLOBALS['marker'],$plain);need($prepared!==$plain,'prepared-fixture');need($plain===execute_simulated($prepared),'normalized-availability-and-bytes');}
 $plain="SELECT option_value FROM adbh_abcdef123456_options WHERE option_name='literal % and \\' quote'";
 need($plain===execute_simulated(str_replace('%',$GLOBALS['marker'],$plain)),'literal-byte-preservation');
}
need(6===$GLOBALS['adbh_ledger']['setup'],'positive-accounting');
need(/*REMOVE*/,'exact-removal');need(false===has_filter('query','adbh_owned_observe'),'observer-absent');need(0===has_filter('query','restore_percent'),'core-restoration-retained');
/*REATTACH*/;need(1===has_filter('query','adbh_owned_observe'),'reattach-order');
$plain=expiry();need($plain===execute_simulated(str_replace('%',$GLOBALS['marker'],$plain)),'reattach-availability');
reset_ledger();
foreach(array(str_replace('adbh_abcdef123456_options b','foreign_options b',$plain),str_replace('%','{unknown-marker}',$plain),$plain.'; DELETE FROM foreign_options',$plain.' /* extra */') as $bad)refused($bad,'sql-shape');
need(0===$GLOBALS['adbh_ledger']['setup'],'shape-denied-before-accounting');
need(remove_filter('query','restore_percent',0),'remove-model-restorer');refused(str_replace('%',$GLOBALS['marker'],$plain),'sql-shape');
add_filter('query','restore_percent',0,1);$GLOBALS['adbh_ledger']['setup']=1023;
need($plain===execute_simulated(str_replace('%',$GLOBALS['marker'],$plain)),'budget-last-allowed');refused($plain,'sql-budget');echo 'ok';`;
const request=String.raw`
$GLOBALS['marker']='{'.str_repeat('c',64).'}';$GLOBALS['adbh_route']='view';
$plain="SELECT option_value FROM adbh_abcdef123456_options WHERE option_name='blogname%'";
need($plain===execute_simulated(str_replace('%',$GLOBALS['marker'],$plain)),'request-positive');
/*REMOVE*/;/*REATTACH*/;
foreach(array("SELECT option_value FROM adbh_abcdef123456_options WHERE option_name='alynt_drime_backups_dashboard_source_policies'",'SELECT * FROM foreign_options','UPDATE adbh_abcdef123456_options SET option_value=1',expiry()) as $bad)refused($bad,false!==strpos($bad,'source_policies')?'pre-entry-policy-read':'sql-shape');
need(!isset($GLOBALS['adbh_sql_refusal']),'no-request-capture');$GLOBALS['adbh_ledger']['bootstrap']=255;
need($plain===execute_simulated($plain),'request-budget-last');refused($plain,'sql-budget');echo 'ok';`;
test('actual early registration and router handoff observe restored placeholders without changing bytes or budgets',()=>passed(run(setup)));
test('request-mode protected/foreign/write denials remain before the simulated executor after reattachment',()=>passed(run(request,{purpose:'request'})));
test('early, equal-priority and inconsistent handoff controls fail assertions rather than syntax',()=>{
 const changes=[
  {w:worker.replace("'adbh_owned_observe', 1, 1","'adbh_owned_observe', PHP_INT_MIN, 1")},
  {w:worker.replace("'adbh_owned_observe', 1, 1","'adbh_owned_observe', 0, 1")},
  {r:router.replace("'adbh_owned_observe', 1 )","'adbh_owned_observe', PHP_INT_MIN )")},
  {r:router.replace("'adbh_owned_observe', 1, 1","'adbh_owned_observe', PHP_INT_MIN, 1")},
  {o:observer.replace("adbh_check( null !== $class, 'sql-shape' );","adbh_check( true, 'sql-shape' );")},
  {o:observer.replace("adbh_check( $GLOBALS['adbh_ledger'][ $phase ] <= ( $setup ? 1024 : 256 ), 'sql-budget' );","adbh_check( true, 'sql-budget' );")},
  {o:observer.replace('return $sql;','return $sql . ";";')},
 ];
 for(const change of changes){const key=Object.keys(change)[0],original={w:worker,r:router,o:observer}[key];assert.notEqual(change[key],original,'mutation applied');const r=run(setup,change);assert.notEqual(r.status,0);assert.match(r.stderr+r.stdout,/RuntimeException:/);assert.doesNotMatch(r.stderr+r.stdout,/Parse error|syntax error|TypeError/);}
});
test('worker initial registration, exact removal and reattachment use the same post-core priority',()=>{
 assert.equal(call(worker,'add'),"add_filter( 'query', 'adbh_owned_observe', 1, 1 )");
 assert.equal(call(router,'remove'),"remove_filter( 'query', 'adbh_owned_observe', 1 )");
 assert.equal(call(router,'add'),"add_filter( 'query', 'adbh_owned_observe', 1, 1 )");
});
