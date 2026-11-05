import {test} from 'node:test';
import assert from 'node:assert/strict';
import {cpSync,existsSync,mkdirSync,mkdtempSync,readFileSync,rmSync,writeFileSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {dirname,join,resolve,sep} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';

test('production join assertion-kills legacy and non-legacy semantic controls in an owned source copy',()=>{
  const root=fileURLToPath(new URL('../../',import.meta.url));
  const temporary=mkdtempSync(join(tmpdir(),'adb-production-controls-'));
  const owned=()=>assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-production-controls-'));
  owned();
  try{
    for(const part of ['includes','tests/support']){mkdirSync(dirname(join(temporary,part)),{recursive:true});cpSync(join(root,part),join(temporary,part),{recursive:true});}
    const worker=join(temporary,'tests/support/source-policy-production-worker.php');
    const autoload=join(root,'vendor/autoload.php').replaceAll('\\','/').replaceAll("'","\\'");
    writeFileSync(worker,readFileSync(worker,'utf8').replace("require ABSPATH.'vendor/autoload.php';",`require '${autoload}';`));
    const run=scenario=>{const result=spawnSync(process.env.PHP_BINARY??'php',['-n',worker,scenario],{encoding:'utf8',timeout:10000,maxBuffer:16384});assert.equal(result.error,undefined);return result;};
    const positive=run('save');assert.equal(positive.status,0,positive.stderr+positive.stdout);assert.equal(positive.stderr,'');assert.match(positive.stdout,/^checks:[1-9][0-9]*\n$/);
    for(const scenario of ['w:save','w:data','l:data']){const result=run(scenario);assert.equal(result.status,0,result.stderr+result.stdout);assert.equal(result.stderr,'');}
    const joinPolicy='includes/traits/trait-source-policy-production-join.php';
    const boundary='includes/traits/trait-source-policy-production-boundary.php';
    const controls=[
      [joinPolicy,'public function capture_bootstrap() {','public function capture_bootstrap() { return false;','save'],
      [boundary,'&& $this->query_context === $query','&& true','active-query'],
      [joinPolicy,'$this->frame = $this->production_projection;','/* retain transient secrets */','active-frame'],
      [joinPolicy,'$this->production_query      = null;','/* retain query */','save'],
      [joinPolicy,"&& $this->production_runtime['client'] === mysqli_get_client_info()",'&& true','active-client'],
      [joinPolicy,"&& $this->production_projection === $this->settings( false )",'&& true','active-table'],
      ['includes/class-source-policy-no-reconnect-connection.php','$this->bootstrap_frame    = null;','/* retain descriptor */','frame'],
      ['includes/traits/trait-source-policy-adapter-operation.php','return $this->connection->bootstrap_production( $context );','return true;','save'],
      ['includes/traits/trait-source-policy-native-bootstrap.php','! $this->production_context->activate( $this, $this->query_context )','false','save'],
      ['includes/class-source-policy-production-expectation.php',"'5.5.5-10.6.23-MariaDB'","'10.6.23-MariaDB'",'data'],
      ['includes/class-source-policy-production-expectation.php',"'mode'     => 0","'mode'     => 3",'data'],
      [joinPolicy,'public function capture_bootstrap() {','public function capture_bootstrap() { return false;','w:save'],
      ['includes/class-source-policy-production-expectation.php',"$fixed['word_size'] = 8;","$fixed['word_size'] = 4;",'w:data'],
      ['includes/class-source-policy-production-expectation.php',"$fixed['nts']       = true;","$fixed['nts']       = false;",'w:data'],
      ['includes/class-source-policy-production-expectation.php',"if ( ( $runtime[ $key ] ?? null ) !== $value )",'if ( false )','w:data'],
      ['includes/class-source-policy-production-expectation.php',"! Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::valid( $frame, $fixed['platform'] )",'false','w:data'],
      ['includes/class-source-policy-production-expectation.php',"'7.1.3' === $core ? 'AMD64' : 'x86_64'","'x86_64'",'w:data'],
      ['includes/class-source-policy-production-expectation.php',"'6.0' === $core ? 'utf8mb4_unicode_ci' : 'utf8mb4_unicode_520_ci'","'utf8mb4_unicode_ci'",'l:data'],
      [joinPolicy,'Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Expectation::matches( $this->production_projection, $this->production_runtime, $this->production_expected )','true','w:active-runtime'],
      [boundary,'&& $this->query_context === $query','&& true','w:active-query'],
      [joinPolicy,"$this->production_runtime['word_size'] = PHP_INT_SIZE;",'/* missing immutable fact */','w:runtime-observation'],
      [joinPolicy,"$this->production_runtime['nts']       = 0 === PHP_ZTS;",'/* missing immutable fact */','w:runtime-observation']
    ];
    for(const [path,before,after,scenario]of controls){
      const file=join(temporary,path),source=readFileSync(file,'utf8'),mutant=source.replaceAll(before,after);assert.notEqual(mutant,source,before);writeFileSync(file,mutant);
      try{const result=run(scenario);assert.notEqual(result.status,0,before+' must fail');assert.match(result.stderr,/PHPUnit\\Framework\\ExpectationFailedException/,result.stderr+result.stdout);}
      finally{writeFileSync(file,source);assert.equal(readFileSync(file,'utf8'),source);}
    }
  }finally{owned();rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);}
});
