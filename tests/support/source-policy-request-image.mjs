import assert from 'node:assert/strict';
import {cpSync,existsSync,mkdtempSync,readFileSync,rmSync,writeFileSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve,sep} from 'node:path';
import {fileURLToPath,pathToFileURL} from 'node:url';
import {spawnSync} from 'node:child_process';

// Only source/runtime expectation DATA are rebound to fixed API doubles in this owned image.
// Positive guards/algorithms/constructors are unchanged; negative-only witnesses/faults are explicit below.
// The shipped catalogs stay closed and byte-identical.
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
    writeFileSync(worker,readFileSync(worker,'utf8').replace("require ABSPATH.'vendor/autoload.php';",`require '${autoload}';`));
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
        // Negative/identity-control images only: passive witnesses, never private-state input.
        if(scenario==='root-controls'){
          alter('includes/traits/trait-source-policy-request-composition.php',source=>source.replace(/\$this->request_phase\s*=\s*'running';/,match=>match+" $GLOBALS['request_observed_root']=$this; $GLOBALS['request_observed_peers']=$this->request_peers;"));
          alter('includes/traits/trait-source-policy-production-join.php',source=>source.replace('return null === $this->request_root',"$GLOBALS['request_observed_frame']=$this->frame; $GLOBALS['request_observed_binding']=$this->binding; return null === $this->request_root"));
        }
        if(scenario==='terminal-fault')alter('includes/traits/trait-source-policy-request-composition.php',source=>source.replace("$root->request_peers['context']->finish();","$root->request_peers['context']->finish(); throw new RuntimeException('owned-private-cleanup');"));
        return spawnSync(php,['-n',worker,scenario],{encoding:'utf8',timeout:15000,maxBuffer:32768});
      }finally{for(const [file,source]of observed){writeFileSync(file,source);assert.equal(readFileSync(file,'utf8'),source);}}
    };
    return callback({temporary,run,root});
  }finally{
    for(let i=0;i<catalogs.length;i++)assert.equal(readFileSync(join(root,catalogs[i]),'utf8'),original[i]);
    owned();rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);
  }
}

if(process.argv[1]&&import.meta.url===pathToFileURL(resolve(process.argv[1])).href){
  requestImage(({run})=>{const result=run(process.argv[2],process.argv[3]);assert.equal(result.error,undefined);process.stdout.write(result.stdout);process.stderr.write(result.stderr);process.exitCode=result.status??1;},process.argv[2]!=='original-policy');
}
