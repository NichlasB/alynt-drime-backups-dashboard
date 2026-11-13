import test from 'node:test';
import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
import {readFileSync} from 'node:fs';
const observerPath=fileURLToPath(new URL('../../scripts/ci/source-policy-http-owned-observer.php',import.meta.url));
function runObserver(php,source=null){
 const load=source===null?`require ${JSON.stringify(observerPath)};`:`eval(base64_decode('${Buffer.from(source.replace(/^<\?php/,'')).toString('base64')}'));`;
 return spawnSync(process.env.PHP_BINARY??'php',['-n','-r',load+php],{encoding:'utf8',timeout:5000,maxBuffer:4096});
}
// Fixed synthetic bytes from pinned core option.php:1645-1671, not core loading,
// wpdb/authentication or a SQL execution proof. Escape LIKE and SQL separately.
const expiryProof=String.raw`
$p='adbh_abcdef123456_';
function expiry_sql($p,$site=false){
 $name=$site?'_site_transient_':'_transient_';
 $timeout=$name.'timeout_';$offset=$site?17:12;
 $quote=static function($s){return "'".str_replace('\\','\\\\',$s)."'";};
 $like=static function($s){return addcslashes($s,'_%\\').'%';};
 return "DELETE a, b FROM {$p}options a, {$p}options b\nWHERE a.option_name LIKE ".$quote($like($name))."\nAND a.option_name NOT LIKE ".$quote($like($timeout))."\nAND b.option_name = CONCAT( ".$quote($timeout).", SUBSTRING( a.option_name, {$offset} ) )\nAND b.option_value < 1794610800";
}
foreach(array(false,true) as $site){
 $q=expiry_sql($p,$site);
 if('setup-write'!==adbh_owned_sql_class($q,$p,true,false))throw new RuntimeException('fixed-expiry-positive');
 foreach(array(array(false,false),array(false,true)) as $flags){if(null!==adbh_owned_sql_class($q,$p,$flags[0],$flags[1]))throw new RuntimeException('setup-only');}
}
$q=expiry_sql($p);$other='adbh_123456abcdef_';
$bad=array(
 str_replace($p.'options b','foreign_options b',$q),
 str_replace($p.'options b',$other.'options b',$q),
 str_replace($p.'options',$p.'usermeta',$q),
 str_replace('DELETE a, b','DELETE a',$q),
 str_replace('SUBSTRING( a.option_name, 12 )','SUBSTRING( a.option_name, 17 )',$q),
 str_replace('_transient_timeout_','_site_transient_timeout_',$q),
 str_replace('AND b.option_value < 1794610800','',$q),
 str_replace('AND b.option_value < 1794610800','AND b.option_value > 1794610800',$q),
 str_replace('1794610800','-1',$q),str_replace('1794610800','12345678901',$q),
 str_replace('1794610800','1794610800 OR 1=1',$q),
 str_replace('b.option_name = CONCAT','b.option_name != CONCAT',$q),
 str_replace('NOT LIKE','LIKE',$q),str_replace('a.option_name','a.option_value',$q),
 $q.'; DELETE FROM foreign_options',$q.' /* unknown */',
 str_replace('a, b FROM','a, b, c FROM',$q),
 str_replace('FROM '.$p.'options a,','FROM foreign_options a,',$q),
 str_replace('LIKE ','LIKE BINARY ',$q),str_replace("\\\\_transient\\\\_%","%",$q)
);
foreach($bad as $sql){if(null!==adbh_owned_sql_class($sql,$p,true,false))throw new RuntimeException('expiry-denial');}
echo 'expiry-positive2/denials20/request-denials4';`;
test('pinned single-site transient expiry shapes are setup-only and both aliases remain owned',()=>{
 const r=runObserver(expiryProof);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.equal(r.stdout,'expiry-positive2/denials20/request-denials4');
});
test('expiry admission preserves actual observer bytes and the existing setup SQL budget',()=>{
 const php=expiryProof.replace("echo 'expiry-positive2/denials20/request-denials4';",'')+String.raw`
 function adbh_check($ok,$label){if(!$ok)throw new RuntimeException($label);}
 define('ADB_HTTP_WORKER','setup');$GLOBALS['adbh_private']=array('prefix'=>$p);
 $GLOBALS['adbh_ledger']=array('setup'=>1023,'bootstrap'=>0,'auth'=>0);$q=expiry_sql($p);
 if($q!==adbh_owned_observe($q)||1024!==$GLOBALS['adbh_ledger']['setup'])exit(2);
 try{adbh_owned_observe($q);exit(3);}catch(RuntimeException $e){if('sql-budget'!==$e->getMessage())exit(4);}
 echo 'same-bytes/budget-refused';`;
 const r=runObserver(php);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.equal(r.stdout,'same-bytes/budget-refused');
});
test('six source-backed expiry controls are assertion-killed, not syntax failures',()=>{
 const source=readFileSync(observerPath,'utf8');
 const controls=[
  ['$setup && 0 === strpos( $shape, \'DELETE a, b FROM \' )','0 === strpos( $shape, \'DELETE a, b FROM \' )','setup-only'],
  ["$options . ' b WHERE", "'[A-Za-z0-9_]+' . ' b WHERE",'expiry-denial'],
  ["array( addcslashes( $name, '_%\\\\' ) . '%', addcslashes( $timeout, '_%\\\\' ) . '%', $timeout ) === $values",'true','expiry-denial'],
  ['AND b\\.option_value < [0-9]{1,10}','AND b\\.option_value [<>] [0-9]{1,10}','expiry-denial'],
  ["? '_transient_' : '_site_transient_'","? '_transient_' : '_transient_'",'fixed-expiry-positive'],
  ["=== $values ? 'setup-write' : null","=== $values ? null : null",'fixed-expiry-positive'],
 ];
 for(const [before,after,label] of controls){
  assert.equal(source.split(before).length,2,`unique mutation: ${before}`);
  const r=runObserver(expiryProof,source.replace(before,after));assert.notEqual(r.status,0);
  assert.match(r.stdout+r.stderr,new RegExp(`RuntimeException: ${label}`));
  assert.doesNotMatch(r.stdout+r.stderr,/Parse error|syntax error/);
 }
});
test('actual socket-free SQL observer preserves allowed bytes and rejects protected/foreign effects',()=>{
 const path=fileURLToPath(new URL('../../scripts/ci/source-policy-http-owned-observer.php',import.meta.url));
 const php=`function adbh_check($ok,$label){if(!$ok)throw new RuntimeException($label);}require ${JSON.stringify(path)};define('ADB_HTTP_WORKER','request');$GLOBALS['adbh_private']=array('prefix'=>'adbh_abcdef123456_');$GLOBALS['adbh_route']='view';$GLOBALS['adbh_ledger']=array('setup'=>0,'bootstrap'=>0,'auth'=>0);$good="SELECT option_value FROM adbh_abcdef123456_options WHERE option_name='blogname' LIMIT 1";if(adbh_owned_observe($good)!==$good)exit(2);$bad=array("SELECT option_value FROM adbh_abcdef123456_options WHERE option_name='alynt_drime_backups_dashboard_source_policies'",'UPDATE adbh_abcdef123456_options SET option_value=1','SELECT * FROM foreign_options','SELECT * FROM adbh_abcdef123456_options; DELETE FROM adbh_abcdef123456_options');$n=0;foreach($bad as $sql){try{adbh_owned_observe($sql);exit(3);}catch(RuntimeException $e){$n++;}}if($n!==4)exit(4);echo 'protected4/same-bytes1';`;
 const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',php],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr);assert.equal(r.stderr,'');assert.equal(r.stdout,'protected4/same-bytes1');
});
test('actual PHP diagnostic exports bounded identity without exception text or absolute paths',()=>{
 const path=fileURLToPath(new URL('../../scripts/ci/source-policy-http-owned-diagnostic.php',import.meta.url));
 // PHP7.4 -n does not guarantee the JSON extension; this fixture tests the pure
 // DATA helper using a fixed scalar wire, not the PHP8.5 native JSON transport.
 const php=`require ${JSON.stringify(path)};$GLOBALS['adbh_stage']='core-bootstrap';$GLOBALS['adbh_failure']=str_repeat('a',64);$GLOBALS['adbh_diagnostic_location']=adbh_diagnostic_location('/owned/core/wp-includes/load.php',123,'/owned');echo implode('|',array_values(adbh_setup_diagnostic(new RuntimeException('secret-sql-password'),'/owned')));`;
 const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',php],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.equal(r.stdout,'owned-setup-refused|core-bootstrap|RuntimeException|core|123|'+'a'.repeat(64));assert.equal(r.stdout.includes('/owned'),false);assert.equal(r.stdout.includes('secret-sql-password'),false);
});
