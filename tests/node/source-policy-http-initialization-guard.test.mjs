import test from 'node:test';
import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {readFileSync} from 'node:fs';
import {fileURLToPath} from 'node:url';
import {InitializedFixtureData,INITIALIZED_HTTP_PROFILE} from '../../scripts/ci/source-policy-http-initialization.mjs';
import {manifestSummary} from '../../scripts/ci/source-policy-http-profile.mjs';
import {identity,storage,manifest} from './source-policy-http-fixtures.mjs';

const path=fileURLToPath(new URL('../../scripts/ci/source-policy-http-initialization.php',import.meta.url));
const observer=fileURLToPath(new URL('../../scripts/ci/source-policy-http-owned-observer.php',import.meta.url));
const literal=value=>typeof value==='string'?"'"+value.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'":typeof value==='boolean'?(value?'true':'false'):String(value);
const array=value=>'array('+Object.entries(value).map(([k,v])=>literal(k)+'=>'+literal(v)).join(',')+')';
function ticket(){
 const rows=manifest(),model=new InitializedFixtureData({identity:identity(),profile:{...INITIALIZED_HTTP_PROFILE},storage:storage(),manifest:rows,expectation:manifestSummary(rows),anchors:'8'.repeat(64)},0);
 model.installed({...model.binding(),role:'installer',sql:1022,exited:true,sessions:0,transactions:0,ddl_sealed:true,fixtures_verified:true},1);return model.begin(2);
}
const facts={sapi:'cli',before_bootstrap:true,installing_override:false,short_init_override:false,protected_cache_absent:true,protected_non_autoloaded:true,owner_absent:true,ddl_sealed:true};
const prelude=`$ticket=${array(ticket())};$facts=${array(facts)};$p='adbh_aaaaaaaaaaaa_';function expect($ok,$label){if(!$ok)throw new RuntimeException('assert-'.$label);}function guard($ticket,$facts){return new ADBH_Initialization_Query_Guard($ticket,$facts,2);}function denied($fn,$reason){try{$fn();throw new RuntimeException('assert-denial-'.$reason);}catch(RuntimeException $e){if($e->getMessage()!==$reason)throw $e;}}`;
function run(php,source=null){
 // RAM-only mutation controls; PHP7.4 -n need not have JSON. No core/DB loading.
 const load=source===null?`require ${literal(path)};`:`require ${literal(observer)};eval(base64_decode('${Buffer.from(source.replace(/^<\?php/,'' ).replace("__DIR__ . '/source-policy-http-owned-observer.php'",literal(observer))).toString('base64')}'));`;
 return spawnSync(process.env.PHP_BINARY??'php',['-n','-r',load+prelude+php],{encoding:'utf8',timeout:5000,maxBuffer:8192});
}
function passes(php){const r=run(php);assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');return r.stdout;}
const positive=String.raw`
$good=array(
 "SELECT @@SESSION.sql_mode","SELECT VERSION()","SELECT FOUND_ROWS()",
 "SELECT option_name, option_value FROM {$p}options WHERE autoload IN ( 'yes', 'on', 'auto-on', 'auto' )",
 "SELECT option_value FROM {$p}options WHERE option_name = 'blogname' LIMIT 1",
 "INSERT INTO {$p}options (option_name, option_value, autoload) VALUES ('cron', 'synthetic', 'on') ON DUPLICATE KEY UPDATE option_name = VALUES(option_name), option_value = VALUES(option_value), autoload = VALUES(autoload)",
 "UPDATE {$p}options SET option_value = 'synthetic', autoload = 'off' WHERE option_name = '_transient_timeout_core'",
 "DELETE FROM {$p}options WHERE option_name = '_transient_core'"
);
foreach($good as $q){$g=guard($ticket,$facts);expect($q===$g->filter($q,3),'positive-bytes');expect(1===$g->finish(),'positive-credit');expect(array()===get_object_vars($g),'no-public-handles');}
`;
const protectedProbe=String.raw`
$g=guard($ticket,$facts);$executed=0;
$q="SELECT option_value FROM {$p}options WHERE option_name = 'alynt_drime_backups_dashboard_source_policies' LIMIT 1";
denied(function()use($g,$q,&$executed){$g->filter($q,3);++$executed;},'initialization-sql');expect(0===$executed,'protected-pre-execute');
`;
const creditProbe=String.raw`
$g=guard($ticket,$facts);$executed=0;$q="SELECT VERSION()";
for($i=0;$i<2;$i++){expect($q===$g->filter($q,3+$i),'credit-positive');++$executed;}
denied(function()use($g,$q,&$executed){$g->filter($q,5);++$executed;},'initialization-credit');expect(2===$executed,'credit-pre-execute');
`;
test('actual PHP guard uses the JS v2 ticket and admits real model positives, preserving bytes',()=>{
 assert.equal(passes(positive+"echo 'positive8';"),'positive8');
});
test('shared remaining SQL credit is charged before a simulated executor',()=>{
 assert.equal(passes(creditProbe+"echo 'credit2/blocked1';"),'credit2/blocked1');
});
test('protected reads, writes, DDL, foreign and compound SQL never reach an executor',()=>{
 const php=positive+String.raw`
 $bad=array(
 "SELECT option_value FROM {$p}options WHERE option_name = 'alynt_drime_backups_dashboard_source_policies' LIMIT 1",
 "SELECT option_value FROM {$p}options WHERE option_name = 'ALYNT_DRIME_BACKUPS_DASHBOARD_SOURCE_POLICIES'",
 "SELECT option_value FROM {$p}options WHERE option_name = 'owned_non_target'",
 "SELECT * FROM {$p}options", "SELECT option_value FROM {$p}options WHERE option_id = 1",
 "SELECT option_name, option_value FROM {$p}options WHERE autoload IN ('no','off','auto-off','auto')",
 "SELECT option_value FROM foreign_options WHERE option_name = 'blogname'",
 "SELECT option_value FROM {$p}options, foreign_options WHERE option_name = 'blogname'",
 "SELECT option_value FROM {$p}options WHERE option_name = 'blogname' OR 1=1",
 "SELECT option_value FROM {$p}options WHERE option_name = CONCAT('alynt_', 'drime_backups_dashboard_source_policies')",
 "SELECT option_value FROM {$p}options WHERE option_name = 0x616c796e74",
 "SELECT option_value FROM {$p}options WHERE option_name = 'blogname'; SELECT VERSION()",
 "SELECT option_value FROM {$p}options WHERE option_name = 'blogname' /* comment */",
 "SELECT SLEEP(1) FROM {$p}options", "SELECT LOAD_FILE('/secret') FROM {$p}options",
 "CREATE TABLE {$p}options (id int)","ALTER TABLE {$p}options ADD c int","DROP TABLE {$p}options",
 "INSERT INTO {$p}users (ID) VALUES (1)", "UPDATE {$p}usermeta SET meta_value = 'session' WHERE user_id = 1",
 "UPDATE {$p}options SET option_value = 'synthetic'",
 "UPDATE {$p}options SET option_value = 'synthetic' WHERE option_name = 'alynt_drime_backups_dashboard_source_policies'",
 "DELETE FROM {$p}options WHERE option_name = 'owned_non_target'",
 "UPDATE {$p}options SET option_value = 'synthetic' WHERE option_name = '{$p}user_roles'",
 "UPDATE {$p}options SET option_value = 'synthetic' WHERE option_name = 'siteurl'",
 "INSERT INTO {$p}options (option_name, option_value, autoload) VALUES ('home', 'synthetic', 'on')",
 "SELECT option_value FROM {$p}options WHERE option_name = 'blogname'".chr(0),
 "SELECT option_value FROM {$p}options WHERE option_name = 'owned_non_target\\0'",
 "UPDATE {$p}options SET option_value = 'synthetic' WHERE option_name = 'HOME'",
 "UPDATE {$p}options SET option_value = 'synthetic' WHERE option_name = 'SITEURL'",
 "INSERT INTO {$p}options (option_name, option_value, autoload) VALUES ('Active_Plugins', 'synthetic', 'on')",
 "UPDATE {$p}options SET option_value = 'synthetic' WHERE option_name = 'home\\0'"
 );
 foreach($bad as $q){$g=guard($ticket,$facts);$executed=0;denied(function()use($g,$q,&$executed){$g->filter($q,3);++$executed;},'initialization-sql');expect(0===$executed,'denied-pre-execute');denied(function()use($g){$g->filter('SELECT VERSION()',4);},'initialization-closed');}
 echo count($bad);
 `;
 assert.equal(passes(php),'32');
});
test('time rollback, expiry, replay, malformed roles, preflight and copied credits fail closed',()=>{
 const php=String.raw`
 foreach(array(1,60000,60001,3.5,'3') as $at){$g=guard($ticket,$facts);denied(function()use($g,$at){$g->filter('SELECT VERSION()',$at);},'initialization-time');}
 foreach(array('sapi','before_bootstrap','installing_override','short_init_override','protected_cache_absent','protected_non_autoloaded','owner_absent','ddl_sealed') as $key){$f=$facts;$f[$key]=is_bool($f[$key])?!$f[$key]:'cli-server';denied(function()use($ticket,$f){guard($ticket,$f);},'initialization-preflight');}
 foreach(array('model'=>'owned-http-l714-v1','role'=>'installer','setup_sql_used'=>0,'sql_remaining'=>1024,'head'=>'invalid') as $key=>$value){$t=$ticket;$t[$key]=$value;try{guard($t,$facts);throw new RuntimeException('assert-ticket');}catch(RuntimeException $e){expect(0===strpos($e->getMessage(),'initialization-'),'ticket-refused');}}
 $g=guard($ticket,$facts);denied(function()use($g){$g->finish();},'initialization-finish');
 $g=guard($ticket,$facts);$g->filter('SELECT VERSION()',3);expect(1===$g->finish(),'finish-positive');denied(function()use($g){$g->filter('SELECT VERSION()',4);},'initialization-closed');
 $g=guard($ticket,$facts);denied(function()use($g,$ticket,$facts){$g->__construct($ticket,$facts,2);},'initialization-reentry');
 $remaining=2;$t=$ticket;$t['sql_remaining']=&$remaining;$g=guard($t,$facts);$remaining=1024;
 $g->filter('SELECT VERSION()',3);$g->filter('SELECT VERSION()',4);denied(function()use($g){$g->filter('SELECT VERSION()',5);},'initialization-credit');
 echo 'time/preflight/terminal/copied-credit';`;
 assert.equal(passes(php),'time/preflight/terminal/copied-credit');
});
test('seven source-backed semantic controls are assertion-killed, not syntax failures',()=>{
 const source=readFileSync(path,'utf8');
 const controls=[
  ["$this->used < $this->ticket['sql_remaining']",'true',creditProbe,'assert-denial-initialization-credit'],
  ["$at >= $this->last && $at < $this->ticket['setup_deadline']",'true',"$g=guard($ticket,$facts);denied(function()use($g){$g->filter('SELECT VERSION()',60000);},'initialization-time');",'assert-denial-initialization-time'],
  ["false === stripos( $key, 'alynt_drime' )",'true',protectedProbe,'assert-denial-initialization-sql'],
  ['$this->allows( $sql )','false',positive,'initialization-sql'],
  ["'owned_non_target' !== strtolower( $key )",'true',"$g=guard($ticket,$facts);denied(function()use($g,$p){$g->filter(\"SELECT option_value FROM {$p}options WHERE option_name = 'owned_non_target'\",3);},'initialization-sql');",'assert-denial-initialization-sql'],
  ['in_array( strtolower( $key ),','in_array( $key,',"$g=guard($ticket,$facts);denied(function()use($g,$p){$g->filter(\"UPDATE {$p}options SET option_value = 'synthetic' WHERE option_name = 'HOME'\",3);},'initialization-sql');",'assert-denial-initialization-sql'],
  ['isset( $literals[0][ $index ] ) && "\'" . $key . "\'" === $literals[0][ $index ]','true',String.raw`$g=guard($ticket,$facts);denied(function()use($g,$p){$g->filter("SELECT option_value FROM {$p}options WHERE option_name = 'owned_non_target\\0'",3);},'initialization-sql');`,'assert-denial-initialization-sql'],
 ];
 for(const [before,after,probe,label] of controls){assert.equal(source.split(before).length,2,before);const r=run(probe,source.replace(before,after));assert.notEqual(r.status,0);assert.match(r.stdout+r.stderr,new RegExp('RuntimeException: '+label));assert.doesNotMatch(r.stdout+r.stderr,/Parse error|syntax error/);}
});
