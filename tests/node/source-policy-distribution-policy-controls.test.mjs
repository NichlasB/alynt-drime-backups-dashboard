import {test} from 'node:test';
import assert from 'node:assert/strict';
import {cpSync,existsSync,mkdirSync,mkdtempSync,readFileSync,rmSync,writeFileSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {dirname,join,resolve,sep} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';

// Mutations occur only in an owned disposable SOURCE copy; never candidate/native IO.
test('actual bound owner controls reject semantic bypasses and an always-unavailable policy',()=>{
  const root=fileURLToPath(new URL('../../',import.meta.url));
  const temporary=mkdtempSync(join(tmpdir(),'adb-distribution-policy-'));
  assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-distribution-policy-'));
  try{
    for(const part of ['includes','tests/support','scripts/ci']){
      mkdirSync(dirname(join(temporary,part)),{recursive:true});
      cpSync(join(root,part),join(temporary,part),{recursive:true});
    }
    const worker=join(temporary,'tests/support/source-policy-distribution-worker.php');
    const autoload=join(root,'vendor/autoload.php').replaceAll('\\','/').replaceAll("'","\\'");
    writeFileSync(worker,readFileSync(worker,'utf8').replace("require ABSPATH.'vendor/autoload.php';",`require '${autoload}';`));
    const run=scenario=>{
      const result=spawnSync(process.env.PHP_BINARY??'php',['-n','-d','disable_functions=json_encode,json_decode',worker,scenario],{encoding:'utf8',timeout:10000,maxBuffer:16384});
      assert.equal(result.error,undefined);return result;
    };
    const positive=run('positive');assert.equal(positive.status,0,positive.stderr+positive.stdout);
    assert.match(positive.stdout,/^checks:[1-9][0-9]*\n$/);assert.equal(positive.stderr,'');
    const scope='includes/class-source-policy-minimum-lab-scope.php',session='includes/class-source-policy-bootstrap-evidence.php';
    const controls=[
      [scope,'$this->receiver = $receiver;','return;','positive'],
      [scope,'$this->context !== $context','false','positive'],
      [scope,"'5.5.5-10.6.23-MariaDB'","'10.6.23-MariaDB'",'unprefixed'],
      [scope,"'utf8' !== $frame['charset'] || '' !== $frame['collate']",'false','configured-charset'],
      [session,"( $row['collation'] ?? null ) === $collation",'true','session-collation'],
      [session,"'collation' => 'utf8mb4_unicode_520_ci',","'collation' => 'utf8mb4_unicode_ci',",'schema']
    ];
    for(const [path,before,after,scenario]of controls){
      const file=join(temporary,path),source=readFileSync(file,'utf8');
      const mutant=source.replace(before,after);assert.notEqual(mutant,source,before);
      writeFileSync(file,mutant);
      try{
        const result=run(scenario);assert.notEqual(result.status,0,before+' must be assertion-killed');
        assert.match(result.stderr,/PHPUnit\\Framework\\ExpectationFailedException/,result.stderr+result.stdout);
      }finally{writeFileSync(file,source);}
    }
  }finally{
    assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-distribution-policy-'));
    rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);
  }
});
