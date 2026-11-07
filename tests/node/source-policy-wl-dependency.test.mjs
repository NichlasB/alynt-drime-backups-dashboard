import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync,writeFileSync,mkdtempSync,rmSync,existsSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve,sep} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';
const root=fileURLToPath(new URL('../../',import.meta.url)),php=process.env.PHP_BINARY??'php';
const original=readFileSync(join(root,'scripts/ci/source-policy-wl-request-bootstrap.php'),'utf8').replaceAll('\r\n','\n');
const literal=value=>"'"+value.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'";
const dependency="$row['auth']['class-wp-metadata-lazyloader.php'] = array( 6833, 'c1df481157f06db91ab7f81fe68981db206beaa38721e6cab49a064110219467' );";
function model(change=source=>source,scenarios=['positive','w','size','missing','hash','extra','overlap'],control=false){
  const temporary=mkdtempSync(join(tmpdir(),'adb-wl-pins-')),bootstrap=join(temporary,'bootstrap.php'),fragment=join(temporary,'fragment.php');
  assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-wl-pins-'));
  try{
    let source=change(original);if(control)assert.notEqual(source,original);
    const start=source.indexOf('\tadbg_check( 12 === count( $row[\'pins\'] )'),end=source.indexOf("\tforeach ( array_merge( $row['includes']");assert.ok(start>0&&end>start);
    writeFileSync(fragment,'<?php\nnamespace AdbWlPinModel;\n'+source.slice(start,end));
    for(const [relative,absolute]of [['/source-policy-minimum-request-bootstrap.php','scripts/ci/source-policy-minimum-request-bootstrap.php'],['/source-policy-wl-request-source.php','scripts/ci/source-policy-wl-request-source.php'],['/../manual/source-policy-supported-core-pins.php','scripts/manual/source-policy-supported-core-pins.php'],['/source-policy-wl-request-pins.php','scripts/ci/source-policy-wl-request-pins.php']])source=source.replace('__DIR__ . '+literal(relative),literal(join(root,absolute)));
    writeFileSync(bootstrap,source);
    for(const scenario of scenarios){const r=spawnSync(php,['-n',join(root,'tests/support/source-policy-wl-pin-model.php'),scenario,bootstrap,fragment],{encoding:'utf8',timeout:5000,maxBuffer:4096});assert.equal(r.error,undefined);if(control){assert.notEqual(r.status,0,scenario);assert.equal(r.stderr,'PHPUnit\\Framework\\ExpectationFailedException\n');}else{assert.equal(r.status,0,scenario+': '+r.stderr);assert.equal(r.stderr,'');assert.match(r.stdout,/^checks:[1-9][0-9]*\n$/);}}
  }finally{writeFileSync(bootstrap,original);assert.equal(readFileSync(bootstrap,'utf8'),original);rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);}
}
test('actual L-only dependency catalog and pin guards accept a finite positive and refuse changed metadata before core load',()=>model());
test('actual dependency pin/profile/count/hash guards are non-vacuous assertion-killed controls',()=>{
  for(const [change,scenario]of [
    [s=>s.replace(dependency,'/* omitted dependency */'),'positive'],
    [s=>s.replace("if ( 'l' === getenv( 'WL_REQUEST_PROFILE' ) ) {\n\t\t"+dependency,'if ( true ) {\n\t\t'+dependency),'w'],
    [s=>s.replace("array( 6833, 'c1df", "array( 6834, 'c1df"),'positive'],
    [s=>s.replace('c1df481157f06db91ab7f81fe68981db206beaa38721e6cab49a064110219467','f'.repeat(64)),'positive'],
    [s=>s.replace("$pin[0] === filesize( ABSPATH . WPINC . '/' . $name )",'true'),'size'],
    [s=>s.replace("hash_equals( $pin[1], hash_file( 'sha256', ABSPATH . WPINC . '/' . $name ) )",'true'),'hash'],
    [s=>s.replace("( $w ? 9 : 10 ) === count( $row['auth'] )",'true'),'extra'],
    [s=>s.replace("! array_intersect_key( $row['pins'], $row['auth'] )",'true'),'overlap']
  ])model(change,[scenario],true);
});
test('actual post-audit source map is finite L-only DATA; missing assignment or W widening cannot pass',()=>{
  const start=original.indexOf('\tif ( ! $w ) {',original.indexOf('definition audit zero native effects')),end=original.indexOf('\treturn array( $rows, $modules );',start);assert.ok(start>0&&end>start);
  const block=original.slice(start,end);
  const run=(code,profile)=>{const script=`define('ADB_WL_REQUEST_PROOF',true);define('ADB_M_REQUEST_PROOF',true);require ${literal(join(root,'scripts/ci/source-policy-wl-request-bootstrap.php'))};putenv('WL_REQUEST_PROFILE=${profile}');$row=adbwl_catalog();$w='${profile}'==='w';${code}echo implode(',', $GLOBALS['adbwl_auth_sources']??array());if(defined('ABSPATH')||class_exists('wpdb',false)||function_exists('mysqli_init'))throw new RuntimeException('effects');`;const r=spawnSync(php,['-n','-r',script],{encoding:'utf8',timeout:5000,maxBuffer:4096});if(r.error||r.status!==0||r.stderr!=='')throw Error('Map setup failure, not a semantic kill');return r.stdout;};
  const expected=run(block,'l').split(',');assert.equal(expected.length,22);assert.ok(expected.includes('class-wp-metadata-lazyloader.php'));assert.equal(run(block,'w'),'');
  const absent=block.replace("$GLOBALS['adbwl_auth_sources'] = array_merge( array_keys( $row['pins'] ), array_keys( $row['auth'] ) );",'/* omitted map */');assert.notEqual(absent,block);assert.throws(()=>assert.deepEqual(run(absent,'l').split(','),expected),{code:'ERR_ASSERTION'});
  const wide=block.replace('if ( ! $w )','if ( true )');assert.notEqual(wide,block);assert.throws(()=>assert.equal(run(wide,'w'),''),{code:'ERR_ASSERTION'});
});
