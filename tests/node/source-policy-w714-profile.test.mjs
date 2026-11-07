import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync,writeFileSync,mkdtempSync,rmSync,existsSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve,sep} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';
const root=fileURLToPath(new URL('../../',import.meta.url)),php=process.env.PHP_BINARY??'php';
const literal=v=>"'"+v.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'";
const names=['class-source-policy-transport-profile.php','class-source-policy-native-profile.php','traits/trait-source-policy-minimum-lab.php','class-source-policy-production-expectation.php'];
const originals=Object.fromEntries(names.map(name=>[name,readFileSync(join(root,'includes',name),'utf8').replaceAll('\r\n','\n')]));

// Namespace-local platform DATA models both hosts; no real platform observation, core or native APIs.
function model(changes={},platform='Windows'){
  const temporary=mkdtempSync(join(tmpdir(),'adb-w714-'));
  assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-w714-'));
  const copies=[];
  try{
    for(const [index,name]of names.entries()){
      const path=join(temporary,index+'.php'),original=originals[name],changed=changes[name]?changes[name](original):original;
      if(changes[name])assert.notEqual(changed,original,'A semantic control must alter its intended actual source');
      const code=changed.replace('<?php',`<?php\nnamespace AdbW714;\n${index===0?'const PHP_OS_FAMILY='+literal(platform)+';':''}`).replaceAll('__DIR__',literal(join(root,'includes')));
      writeFileSync(path,code);copies.push([path,original]);
    }
    const script=`define('ABSPATH','/virtual/');${copies.map(([path])=>'require '+literal(path)+';').join('')}
      class AdbW714Probe {use AdbW714\\Alynt_Drime_Backups_Dashboard_Source_Policy_Minimum_Lab;private $production_context=null;public function server(){return $this->bootstrap_expected_server('7.1.4');}}
      $method=new ReflectionMethod('AdbW714\\Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile','expected_server');if(PHP_VERSION_ID<80100)$method->setAccessible(true);
      $frame=array('core'=>'7.1.4','database'=>'isolated','table'=>'wp_options','host'=>'127.0.0.1','port'=>10004,'charset'=>'utf8mb4','collate'=>'','transport'=>'tcp_loopback','socket'=>null,'db_host'=>'127.0.0.1:10004');
      $windows=${platform==='Windows'?'true':'false'};if(!$windows){$frame['host']='localhost';$frame['port']=null;$frame['charset']='utf8';$frame['transport']='unix_socket';$frame['socket']='/tmp/owned.sock';$frame['db_host']='localhost:/tmp/owned.sock';}
      $runtime=array('platform'=>${literal(platform)},'arch'=>$windows?'AMD64':'x86_64','php'=>'8.5.12','client'=>'mysqlnd 8.5.12','mode'=>0,'word_size'=>8,'nts'=>true);
      $expected=array('core'=>'7.1.4','server'=>$windows?'10.6.23-MariaDB':'10.11.18-MariaDB-ubu2204','collation'=>'utf8mb4_unicode_520_ci','database'=>'isolated','table'=>'wp_options');
      $class='AdbW714\\Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Expectation';$transport='AdbW714\\Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile';
      $tuple=array($frame['host'],$frame['port'],$frame['socket'],false);$badTuple=$tuple;$badTuple[1]=$windows?'10004':'';
      $out=array($method->invoke(null,array('core'=>'7.1.4'),null,null),(new AdbW714Probe())->server(),(int)$class::matches($frame,$runtime,$expected),(int)$transport::matches_parsed($frame,$tuple),(int)$transport::matches_parsed($frame,$badTuple));
      foreach(array('nts'=>false,'arch'=>'wrong','mode'=>3)as$key=>$value){$bad=$runtime;$bad[$key]=$value;$out[]=(int)$class::matches($frame,$bad,$expected);}
      $out[]=(int)(false===$transport::server('7.1.4','Darwin'));
      if(class_exists('wpdb',false)||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))throw new RuntimeException('No core/native effects');echo implode('|',$out);`;
    const r=spawnSync(php,['-n','-r',script],{encoding:'utf8',timeout:5000,maxBuffer:4096});
    if(r.error||r.status!==0||r.stderr!==''||!/^(?:10\.6\.23-MariaDB|10\.11\.18-MariaDB-ubu2204)\|(?:10\.6\.23-MariaDB|10\.11\.18-MariaDB-ubu2204)(?:\|[01]){7}$/.test(r.stdout))throw Error('Isolated setup failure, not assertion kill: '+r.stderr);
    return r.stdout;
  }finally{
    for(const [path,original]of copies){writeFileSync(path,original);assert.equal(readFileSync(path,'utf8'),original);}
    rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);
  }
}
const windows='10.6.23-MariaDB|10.6.23-MariaDB|1|1|0|0|0|0|1';
test('actual standalone and default-facts consumers use trusted Windows platform; same-core Linux remains distinct',()=>{
  assert.equal(model(),windows);
  assert.equal(model({},'Linux'),'10.11.18-MariaDB-ubu2204|10.11.18-MariaDB-ubu2204|1|1|0|0|0|0|1');
});
test('omitted platform at either actual consumer and removed runtime/platform guards are assertion-killed controls',()=>{
  for(const name of ['class-source-policy-native-profile.php','traits/trait-source-policy-minimum-lab.php'])assert.throws(()=>assert.equal(model({[name]:s=>s.replace(', PHP_OS_FAMILY','')}),windows),{code:'ERR_ASSERTION'});
  assert.throws(()=>assert.equal(model({'class-source-policy-production-expectation.php':s=>s.replace("if ( ( $runtime[ $key ] ?? null ) !== $value )",'if ( false )')}),windows),{code:'ERR_ASSERTION'});
  assert.throws(()=>assert.equal(model({'class-source-policy-transport-profile.php':s=>s.replace("null !== $platform && ! in_array( $platform, array( 'Windows', 'Linux' ), true )",'false')}),windows),{code:'ERR_ASSERTION'});
});
test('fixed Windows prerequisite and request version/core/runtime guards agree without executing their bodies',()=>{
  const read=name=>readFileSync(join(root,name),'utf8');
  const bootstrap=read('scripts/manual/source-policy-bootstrap-owned-proof.php');
  for(const expected of ["adbp_profile( '7.1.4' )","'core'      => '7.1.4'","'AMD64' !== php_uname( 'm' )","false !== PHP_ZTS","8 !== PHP_INT_SIZE"])assert.ok(bootstrap.includes(expected));
  assert.ok(read('scripts/manual/source-policy-adapter-owned-proof.php').includes("adbp_initial_mode( '7.1.4', PHP_VERSION, $driver->report_mode )"));
  assert.ok(read('scripts/ci/source-policy-wl-request-bootstrap.php').includes("adbg_check( '7.1.4' === $wp_version"));
  for(const file of ['scripts/manual/source-policy-connected-cleanup-worker.php','scripts/manual/source-policy-adapter-owned-proof.php'])assert.ok(read(file).includes("require __DIR__ . '/source-policy-bootstrap-owned-proof.php'"));
});
