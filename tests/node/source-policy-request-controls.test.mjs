import {test} from 'node:test';
import assert from 'node:assert/strict';
import {join} from 'node:path';
import {readFileSync,writeFileSync} from 'node:fs';
import {requestImage} from '../support/source-policy-request-image.mjs';

test('ordinary request composition uses unchanged guard/peer algorithms and assertion-kills semantic controls',()=>requestImage(({temporary,run})=>{
  for(const scenario of ['save','view','root-controls','native-policy']){const result=run(scenario);assert.equal(result.error,undefined);assert.equal(result.status,0,result.stderr+result.stdout);assert.match(result.stdout,/^checks:[1-9][0-9]*\n$/);assert.equal(result.stderr,'');}
  const root='includes/traits/trait-source-policy-request-composition.php';
  const entry='includes/class-source-policy-guarded-entry.php';
  const peers='includes/traits/trait-source-policy-request-connection.php';
  const controls=[
    [root,'private function request_owner( $site ) {','private function request_owner( $site ) { return false;','save'],
    [entry,"! current_user_can( 'manage_options' )",'false','capability'],
    [entry,"! wp_verify_nonce( wp_unslash( $request['_wpnonce'] ), 'alynt_drime_backups_dashboard_update_source_policy' )",'false','nonce'],
    [root,'! $this->record_matches( $site )','false','record'],
    [root,"self::$request_running )",'false )','root-controls'],
    [root,"( $this->request_peers['query'] ?? null ) === $query",'true','root-controls'],
    [root,"( $this->request_peers['connection'] ?? null ) === $receiver",'true','root-controls'],
    [root,"( $this->request_peers['context'] ?? null ) === $context",'true','root-controls'],
    [peers,'$this->request_frame_attempted = true;','/* missing consumption */','root-controls'],
    [root,"( $closing ? 'closing' : 'running' ) === $this->request_phase &&",'','root-controls'],
    ['includes/traits/trait-source-policy-production-join.php','return null === $this->request_root || $this->receiver->bind_request_frame( $this->request_root, $this, $this->production_query, $this->frame, $this->binding );','return true;','save'],
    ['includes/class-source-policy-trusted-source.php',"is_string( $policy['driver'] ?? null ) && '' !== $policy['driver']",'true','native-policy']
  ];
  for(const [path,before,after,scenario]of controls){
    const file=join(temporary,path),source=readFileSync(file,'utf8'),mutant=source.replaceAll(before,after);assert.notEqual(mutant,source,before);writeFileSync(file,mutant);
    try{const result=run(scenario);assert.equal(result.error,undefined);assert.notEqual(result.status,0,before);assert.match(result.stderr,/PHPUnit\\Framework\\ExpectationFailedException/,result.stderr+result.stdout);}
    finally{writeFileSync(file,source);assert.equal(readFileSync(file,'utf8'),source);}
  }
}));

test('positive construction image never supplies private state or ready/facts/handle/policy fields',()=>{
  const worker=readFileSync(new URL('../support/source-policy-request-worker.php',import.meta.url),'utf8');
  const cases=readFileSync(new URL('../support/source-policy-request-cases.php',import.meta.url),'utf8');
  assert.doesNotMatch(worker+cases,/boot_set\(|boot_invoke\(|newInstanceWithoutConstructor|setValue\(|Closure::bind/);
  assert.doesNotMatch(cases.split("$result='view'")[0],/boot_get\(|new Reflection/,'Active-state controls use passive witnesses, not Reflection.');
  assert.match(worker,/new wpdb\(\)/);assert.match(cases,/\$class::request_submit\(\$request\)/);
});
