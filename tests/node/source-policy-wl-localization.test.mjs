import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync,writeFileSync,mkdirSync,mkdtempSync,rmSync,existsSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join,resolve,sep} from 'node:path';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';
const root=fileURLToPath(new URL('../../',import.meta.url)),php=process.env.PHP_BINARY??'php';
const source=readFileSync(join(root,'scripts/ci/source-policy-wl-request-bootstrap.php'),'utf8').replaceAll('\r\n','\n');
const start=source.indexOf("\tforeach ( array( 'pomo/translations.php', 'l10n.php' )"),end=source.indexOf("\tadbg_check( ( $w ? '7.1.3'",start);
const literal=value=>"'"+value.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'";
const fragment=()=>{assert.ok(start>0&&end>start);return source.slice(start,end);};

// Synthetic declarations exercise the actual loader/order/origin guard, not genuine WordPress authentication.
function run(code=fragment(),profile='l',override='none'){
  const temporary=mkdtempSync(join(tmpdir(),'adb-wl-localization-')),core=join(temporary,'wp-includes'),copy=join(temporary,'fragment.php');
  assert.ok(resolve(temporary).startsWith(resolve(tmpdir())+sep+'adb-wl-localization-'));
  try{
    mkdirSync(join(core,'pomo'),{recursive:true});
    writeFileSync(join(core,'pomo','plural-forms.php'),"<?php $GLOBALS['order'][]='plural';class Plural_Forms {}\n");
    writeFileSync(join(core,'pomo','entry.php'),"<?php $GLOBALS['order'][]='entry';class Translation_Entry {}\n");
    writeFileSync(join(core,'pomo','translations.php'),"<?php require_once __DIR__.'/plural-forms.php';require_once __DIR__.'/entry.php';$GLOBALS['order'][]='translations';if(!class_exists('NOOP_Translations',false)){class NOOP_Translations {}}\n");
    writeFileSync(join(core,'l10n.php'),"<?php $GLOBALS['order'][]='l10n';if(!function_exists('__')){function __($value){return $value;}}\n");
    writeFileSync(copy,'<?php\n'+code);
    const overrides=override==='function'?"function __($value){return $value;}":override==='class'?'class NOOP_Translations {}':'';
    const script=`define('ABSPATH',${literal(temporary.replaceAll('\\','/')+'/')});define('WPINC','wp-includes');$w=${profile==='w'?'true':'false'};$GLOBALS['order']=array();function adbg_check($condition,$label){if(!$condition)throw new RuntimeException('guard');}${overrides}$refused=false;try{require ${literal(copy)};}catch(RuntimeException $error){$refused=true;}if(class_exists('wpdb',false)||function_exists('mysqli_init'))throw new LogicException('effects');echo ($refused?'refused':'accepted').'|'.implode(',',$GLOBALS['order']);`;
    const r=spawnSync(php,['-n','-r',script],{encoding:'utf8',timeout:5000,maxBuffer:4096});
    if(r.error||r.status!==0||r.stderr!=='')throw Error('Loader setup/parser failure, not semantic assertion kill: '+r.stderr);
    return r.stdout;
  }finally{
    writeFileSync(copy,'<?php\n'+fragment());assert.equal(readFileSync(copy,'utf8'),'<?php\n'+fragment());rmSync(temporary,{recursive:true,force:false});assert.equal(existsSync(temporary),false);
  }
}

test('actual selected W/L loader selects finite localization order and rejects shadow function/class origins',()=>{
  assert.equal(run(),'accepted|plural,entry,translations,l10n');assert.equal(run(fragment(),'w'),'accepted|plural,entry,translations,l10n');
  for(const override of ['function','class'])assert.equal(run(fragment(),'l',override),'refused|plural,entry,translations,l10n');
});

test('localization load in both selected profiles and both genuine-origin checks are assertion-killed controls',()=>{
  const code=fragment(),none='/* omitted localization load */';
  assert.throws(()=>assert.equal(run(none),'accepted|plural,entry,translations,l10n'),{code:'ERR_ASSERTION'});
  const omittedW='if ( ! $w ) {'+code+'}';assert.throws(()=>assert.equal(run(omittedW,'w'),'accepted|plural,entry,translations,l10n'),{code:'ERR_ASSERTION'});
  for(const [needle,override]of [
    ["( new ReflectionFunction( '__' ) )->getFileName() === realpath( ABSPATH . WPINC . '/l10n.php' )",'function'],
    ["( new ReflectionClass( 'NOOP_Translations' ) )->getFileName() === realpath( ABSPATH . WPINC . '/pomo/translations.php' )",'class']
  ]){const changed=code.replace(needle,'true');assert.notEqual(changed,code);assert.throws(()=>assert.equal(run(changed,'l',override),'refused|plural,entry,translations,l10n'),{code:'ERR_ASSERTION'});}
});
