import assert from 'node:assert/strict';
import {cpSync,existsSync,mkdtempSync,readFileSync,rmSync,writeFileSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve,sep} from 'node:path';
import {fileURLToPath,pathToFileURL} from 'node:url';
import {spawnSync} from 'node:child_process';

// Only source/runtime expectation DATA are rebound to fixed API doubles in this owned image.
// Positive guards/algorithms/constructors are unchanged; negative-only witnesses/faults are explicit below.
// Shipped catalog bytes are unchanged by every owned image; native DATA is not execution proof.
export function requestImage(callback,rebind=true) {
  const root=fileURLToPath(new URL('../../',import.meta.url));
  const temporary=mkdtempSync(join(tmpdir(),'adb-request-image-'));
  const owned=()=>assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-request-image-'));
  const catalogs=['includes/class-source-policy-trusted-source.php','includes/class-source-policy-production-expectation.php'];
  const original=catalogs.map(file=>readFileSync(join(root,file),'utf8'));
  owned();
  try{
    for(const part of ['includes','tests/support'])cpSync(join(root,part),join(temporary,part),{recursive:true});
    const worker=join(temporary,'tests/support/source-policy-request-worker.php');
    const autoload=join(root,'vendor/autoload.php').replaceAll('\\','/').replaceAll("'","\\'");
    for(const name of ['source-policy-request-worker.php','source-policy-request-admission-worker.php']){
      const file=join(temporary,'tests/support',name);
      writeFileSync(file,readFileSync(file,'utf8').replace("require ABSPATH.'vendor/autoload.php';",`require '${autoload}';`));
    }
    if(rebind)writeFileSync(join(temporary,catalogs[0]),original[0].replace('public static function policy( $core ) {','public static function policy( $core ) { return request_fixture_policy($core);'));
    let expectation=original[1];
    for(const [before,after]of [["'platform' => 'Linux'","'platform' => PHP_OS_FAMILY"],["'arch'     => 'x86_64'","'arch'     => php_uname('m')"],["'php'      => '7.4.33'","'php'      => PHP_VERSION"]]){
      assert.ok(expectation.includes(before));expectation=expectation.replace(before,after);
    }
    if(rebind)writeFileSync(join(temporary,catalogs[1]),expectation);
    const run=(scenario,php=process.env.PHP_BINARY??'php')=>{
      const observed=[];
      const alter=(path,transform)=>{const file=join(temporary,path),source=readFileSync(file,'utf8'),next=transform(source);assert.notEqual(next,source);observed.push([file,source]);writeFileSync(file,next);};
      try{
        if(scenario.startsWith('admission-')){
          assert.equal(rebind,false,'Pure native catalog cases must not use the API declaration catalog.');
          if(scenario!=='admission-shipped'){
            const version=scenario==='admission-php'?'7.4.32':['admission-w','admission-l','admission-unknown-native'].includes(scenario)?'8.5.12':'7.4.33';
            alter(catalogs[0],source=>source.replace('PHP_VERSION !==',`'${version}' !==`)); // Pure process-version DATA only, never native initialization.
          }
          if(scenario==='admission-malformed')alter(catalogs[0],source=>source.replace('public static function policy( $core ) {',"public static function policy( $core ) { return array('core'=>$core,'php'=>'7.4.33');"));
          if(scenario==='admission-core')alter(catalogs[0],source=>source.replace("'core'   => $core,","'core'   => 'unbound',"));
          if(scenario==='admission-driver')alter(catalogs[0],source=>source.replace("'driver' => null,","'driver' => false,"));
          if(scenario==='admission-unknown-native')alter(catalogs[0],source=>source.replace("array( '6.0', '7.1.3', '7.1.4' )","array( '6.0', '7.1.3', '7.1.4', '7.1.5' )"));
          return spawnSync(php,['-n',join(temporary,'tests/support/source-policy-request-admission-worker.php'),scenario],{encoding:'utf8',timeout:15000,maxBuffer:32768});
        }
        // Negative/identity-control images only: passive witnesses, never private-state input.
        if(['root-controls','catalog-extra','catalog-order','catalog-drift'].includes(scenario)){
          alter('includes/traits/trait-source-policy-request-composition.php',source=>source.replace(/\$this->request_phase\s*=\s*'running';/,match=>match+" $GLOBALS['request_observed_root']=$this; $GLOBALS['request_observed_peers']=$this->request_peers; $GLOBALS['request_observed_policy']=$this->request_policy;"));
          alter('includes/traits/trait-source-policy-production-join.php',source=>source.replace('return null === $this->request_root',"$GLOBALS['request_observed_frame']=$this->frame; $GLOBALS['request_observed_binding']=$this->binding; return null === $this->request_root"));
        }
        if(scenario==='root-controls')alter('includes/traits/trait-source-policy-request-composition.php',source=>source.replace("$root->request_phase   = 'closing';", "$root->request_phase   = 'closing'; \\PHPUnit\\Framework\\Assert::assertFalse($root->request_catalog_matches($GLOBALS['request_observed_peers']['context'],$GLOBALS['request_observed_policy']),'Closing root cannot admit catalog.');"));
        if(['catalog-extra','catalog-order','catalog-drift'].includes(scenario))alter('includes/traits/trait-source-policy-trusted-context-source.php',source=>source.replace('private function checked_declarations( array $policy ) {',"private function checked_declarations( array $policy ) { $GLOBALS['request_declaration_checks']=($GLOBALS['request_declaration_checks']??0)+1;"));
        // Negative-only corruption after genuine API capture; never part of save/view positives.
        if(scenario==='catalog-drift')alter('includes/traits/trait-source-policy-production-join.php',source=>source.replace("$this->state                 = 'bootstrap';","$this->state                 = 'bootstrap'; $this->policy['unexpected']='drift';"));
        if(scenario==='terminal-fault')alter('includes/traits/trait-source-policy-request-composition.php',source=>source.replace("$root->request_peers['context']->finish();","$root->request_peers['context']->finish(); throw new RuntimeException('owned-private-cleanup');"));
        return spawnSync(php,['-n',worker,scenario],{encoding:'utf8',timeout:15000,maxBuffer:32768});
      }finally{for(const [file,source]of observed.reverse()){writeFileSync(file,source);assert.equal(readFileSync(file,'utf8'),source);}}
    };
    return callback({temporary,run,root});
  }finally{
    for(let i=0;i<catalogs.length;i++)assert.equal(readFileSync(join(root,catalogs[i]),'utf8'),original[i]);
    owned();rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);
  }
}

if(process.argv[1]&&import.meta.url===pathToFileURL(resolve(process.argv[1])).href){
  requestImage(({run})=>{const result=run(process.argv[2],process.argv[3]);assert.equal(result.error,undefined);process.stdout.write(result.stdout);process.stderr.write(result.stderr);process.exitCode=result.status??1;},process.argv[2]!=='original-policy'&&!process.argv[2].startsWith('admission-'));
}
