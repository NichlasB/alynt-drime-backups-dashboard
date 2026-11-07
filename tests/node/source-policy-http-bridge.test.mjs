import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync,writeFileSync,mkdirSync} from 'node:fs';
import {join} from 'node:path';
import {spawnSync} from 'node:child_process';
import {requestImage} from '../support/source-policy-request-image.mjs';
import {route} from '../../scripts/ci/source-policy-http-contract.mjs';
import * as f from './source-policy-http-fixtures.mjs';
const root=new URL('../../',import.meta.url),source=readFileSync(new URL('scripts/ci/source-policy-http-bridge.php',root),'utf8'),php=process.env.PHP_BINARY??'php';
const run=code=>{const r=spawnSync(php,['-n','-r',code],{encoding:'utf8',timeout:5000,maxBuffer:32768});assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');return r.stdout;};
const literal=s=>"'"+s.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'";
test('PHP bridge definitions do not load core/classes/native; PHP and JS metadata admission agree',()=>{
 const path=new URL('scripts/ci/source-policy-http-bridge.php',root).pathname.replace(/^\/([A-Za-z]:\/)/,'$1');
 assert.equal(run(`require ${literal(decodeURIComponent(path))};if(defined('ABSPATH')||class_exists('wpdb',false)||class_exists('Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry',false)||function_exists('mysqli_init'))throw new RuntimeException('effects');echo 'definitions-only';`),'definitions-only');
 const metas=['login-form','login-submit','view','submit','nonce-control'].map(f.meta);
 metas.push(...[{host:'localhost:18777'},{method:'DELETE'},{target:'/wp-admin/'},{challenge:f.run},{header_bytes:32769},{body_bytes:16385},{content_length:'010'},{content_type:'application/json'},{transfer_encoding:'chunked'},{extra:1}].map(change=>({...f.meta(),...change})));
 const json=Buffer.from(JSON.stringify(metas)).toString('base64');
 assert.deepEqual(JSON.parse(run(`require ${literal(decodeURIComponent(path))};$out=array();foreach(json_decode(base64_decode('${json}'),true)as$m)$out[]=adbh_route($m,'${f.challenge}');echo json_encode($out);`)),metas.map(meta=>route(meta,f.challenge)));
});
test('error objects cannot masquerade as saved/view success or export diagnostic data',()=>{
 const path=new URL('scripts/ci/source-policy-http-bridge.php',root).pathname.replace(/^\/([A-Za-z]:\/)/,'$1'),meta=Buffer.from(JSON.stringify(f.meta())).toString('base64');
 assert.equal(run(`require ${literal(decodeURIComponent(path))};class WP_Error{public function get_error_code(){return $GLOBALS['code'];}}class Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry{public static function request_submit($r){return new WP_Error();}}foreach(array('saved','view','unchanged','owned-private-secret',array('saved'))as$c){$GLOBALS['code']=$c;$r=adbh_bridge(json_decode(base64_decode('${meta}'),true),array(),'${f.challenge}');if($r!==adbh_result('source_policy_unavailable'))throw new RuntimeException('error accepted');}echo 'closed';`),'closed');
});
function image(callback){return requestImage(({temporary,run:execute})=>{
 mkdirSync(join(temporary,'scripts/ci'),{recursive:true});const file=join(temporary,'scripts/ci/source-policy-http-bridge.php');writeFileSync(file,source);
 const worker=join(temporary,'tests/support/source-policy-request-worker.php'),original=readFileSync(worker,'utf8');
 const target='request_case($argv[1],$request,$store);';assert.ok(original.includes(target));
 writeFileSync(worker,original.replace(target,"require ABSPATH.'scripts/ci/source-policy-http-bridge.php';require __DIR__.'/source-policy-http-bridge-cases.php';http_bridge_case($argv[1],$request,$store);"));
 return callback({execute,file,temporary});
});}
const passed=r=>{assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.match(r.stdout,/^checks:[1-9][0-9]*\n$/);};
test('actual fixed bridge and unchanged entry guard real algorithms in an explicitly rebound API image',()=>image(({execute})=>{
 for(const scenario of ['view','save','noop','stale','denied','view-denied','nonce','compound-nonce','compound-site','revision','private-challenge','unknown-route','framing','wrong-action','unavailable'])passed(execute(scenario));
}));
test('removing actual entry capability or nonce guards fails the bridge denial oracle',()=>image(({execute,temporary})=>{
 const file=join(temporary,'includes/class-source-policy-guarded-entry.php'),original=readFileSync(file,'utf8');
 const nonce=original.split('\n').find(line=>line.includes('wp_verify_nonce(')).trim();
 for(const[from,to,scenario]of [["if ( ! current_user_can( 'manage_options' ) ) {",'if ( false ) {','view-denied'],[nonce,'if ( false ) {','nonce']]){
  const changed=original.replace(from,to);assert.notEqual(changed,original);writeFileSync(file,changed);try{const r=execute(scenario);assert.equal(r.error,undefined);assert.notEqual(r.status,0);assert.match(r.stderr,/PHPUnit\\Framework\\ExpectationFailedException/);}finally{writeFileSync(file,original);assert.equal(readFileSync(file,'utf8'),original);}
 }
}));
test('bridge challenge/raw-forward/availability/nonce-domain guards have assertion-killed source controls',()=>image(({execute,file})=>{
 const controls=[
  ["$meta['challenge'] !== $challenge",'false','private-challenge'],
  ["$class::request_submit( $request )","$class::request_submit( array_merge( $request, array( '_wpnonce' => 'valid' ) ) )",'nonce'],
  ["$class::request_submit( $request )","$class::request_submit( array_merge( $request, array( 'source_policy_revision' => str_repeat( 'f', 64 ) ) ) )",'save'],
  ["$class::request_submit( $request )","'source_policy_unavailable'",'save'],
  ["'adb_http_wrong_action' :","'alynt_drime_backups_dashboard_update_source_policy' :",'wrong-action'],
  ["return adbh_result( 'source_policy_invalid' );","/* bypass ingress */",'unknown-route']
 ];
 for(const[from,to,scenario]of controls){const changed=source.replace(from,to);assert.notEqual(changed,source);writeFileSync(file,changed);try{const r=execute(scenario);assert.equal(r.error,undefined);assert.notEqual(r.status,0);assert.match(r.stderr,/PHPUnit\\Framework\\ExpectationFailedException/);}finally{writeFileSync(file,source);assert.equal(readFileSync(file,'utf8'),source);}}
 assert.equal(readFileSync(new URL('scripts/ci/source-policy-http-bridge.php',root),'utf8'),source);
}));
