import {test} from 'node:test';
import assert from 'node:assert/strict';
import {cpSync,existsSync,mkdirSync,mkdtempSync,readFileSync,rmSync,writeFileSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {dirname,join,resolve,sep} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';

// Only an owned disposable source copy is mutated. No working tree/native/site IO.
test('trusted capture positives and refusals assertion-kill semantic guard bypasses',()=>{
  const root=fileURLToPath(new URL('../../',import.meta.url));
  const temporary=mkdtempSync(join(tmpdir(),'adb-trusted-controls-'));
  assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-trusted-controls-'));
  try{
    for(const part of ['includes','tests/support']){
      mkdirSync(dirname(join(temporary,part)),{recursive:true});
      cpSync(join(root,part),join(temporary,part),{recursive:true});
    }
    const worker=join(temporary,'tests/support/source-policy-trusted-worker.php');
    const autoload=join(root,'vendor/autoload.php').replaceAll('\\','/').replaceAll("'","\\'");
    writeFileSync(worker,readFileSync(worker,'utf8').replace("require ABSPATH.'vendor/autoload.php';",`require '${autoload}';`));
    const run=scenario=>{
      const result=spawnSync(process.env.PHP_BINARY??'php',['-n',worker,scenario],{encoding:'utf8',timeout:10000,maxBuffer:16384});
      assert.equal(result.error,undefined);return result;
    };
    const positive=run('positive-save');assert.equal(positive.status,0,positive.stderr+positive.stdout);
    assert.match(positive.stdout,/^checks:[1-9][0-9]*\n$/);assert.equal(positive.stderr,'');
    const entry='includes/class-source-policy-guarded-entry.php';
    const context='includes/class-source-policy-trusted-context.php';
    const controls=[
      [context,'public function capture() {','public function capture() { return false;','positive-save'],
      [entry,"! current_user_can( 'manage_options' )",'false','capability'],
      [entry,"! wp_verify_nonce( wp_unslash( $request['_wpnonce'] ), 'alynt_drime_backups_dashboard_update_source_policy' )",'false','nonce-domain'],
      [entry,"! $this->valid_site_id( $request['dashboard_site_id'] )",'false','site-negative'],
      [entry,'! $this->record_matches( $site_id )','false','record'],
      [context,"hash_file( 'sha256', $path ) !== $digest",'false','source'],
      ['includes/class-source-policy-bootstrap-profile.php',"$absint->getFileName() === realpath( $binding['parser_api_file'] )",'true','parser-source'],
      ['includes/class-source-policy-native-adapter.php','null !== $this->operation_context && ! $this->operation_context->valid( $this->connection )','false','late-core'],
      [context,"( isset( $GLOBALS['wp_filter'] ) && ! is_array( $GLOBALS['wp_filter'] ) )",'false','registry-shape'],
      ['includes/class-source-policy-lazy-provider.php','$this->used || ! is_int( $site )','false || ! is_int( $site )','reentry'],
      [context,'restore_error_handler();','','positive-save']
    ];
    for(const [path,before,after,scenario]of controls){
      const file=join(temporary,path),source=readFileSync(file,'utf8'),mutant=source.replaceAll(before,after);
      assert.notEqual(mutant,source,before);writeFileSync(file,mutant);
      try{
        const result=run(scenario);assert.notEqual(result.status,0,before+' must fail');
        assert.match(result.stderr,/PHPUnit\\Framework\\ExpectationFailedException/,result.stderr+result.stdout);
      }finally{writeFileSync(file,source);assert.equal(readFileSync(file,'utf8'),source);}
    }
  }finally{
    assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-trusted-controls-'));
    rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);
  }
});
