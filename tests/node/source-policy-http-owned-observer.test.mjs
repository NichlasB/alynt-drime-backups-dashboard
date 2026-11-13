import test from 'node:test';
import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
test('actual socket-free SQL observer preserves allowed bytes and rejects protected/foreign effects',()=>{
 const path=fileURLToPath(new URL('../../scripts/ci/source-policy-http-owned-observer.php',import.meta.url));
 const php=`function adbh_check($ok,$label){if(!$ok)throw new RuntimeException($label);}require ${JSON.stringify(path)};define('ADB_HTTP_WORKER','request');$GLOBALS['adbh_private']=array('prefix'=>'adbh_abcdef123456_');$GLOBALS['adbh_route']='view';$GLOBALS['adbh_ledger']=array('setup'=>0,'bootstrap'=>0,'auth'=>0);$good="SELECT option_value FROM adbh_abcdef123456_options WHERE option_name='blogname' LIMIT 1";if(adbh_owned_observe($good)!==$good)exit(2);$bad=array("SELECT option_value FROM adbh_abcdef123456_options WHERE option_name='alynt_drime_backups_dashboard_source_policies'",'UPDATE adbh_abcdef123456_options SET option_value=1','SELECT * FROM foreign_options','SELECT * FROM adbh_abcdef123456_options; DELETE FROM adbh_abcdef123456_options');$n=0;foreach($bad as $sql){try{adbh_owned_observe($sql);exit(3);}catch(RuntimeException $e){$n++;}}if($n!==4)exit(4);echo 'protected4/same-bytes1';`;
 const r=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',php],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr);assert.equal(r.stderr,'');assert.equal(r.stdout,'protected4/same-bytes1');
});
