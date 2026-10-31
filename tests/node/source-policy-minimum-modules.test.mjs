import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {spawnSync} from 'node:child_process';
const root=new URL('../../',import.meta.url),read=p=>readFileSync(new URL(p,root),'utf8');
// Independent frozen observation: audit-only run 41230593868 at c0a29ee.
const observed='Core;PDO;Phar;Reflection;SPL;SimpleXML;ctype;curl;date;dom;fileinfo;filter;ftp;hash;iconv;json;libxml;mbstring;mysqli;mysqlnd;openssl;pcre;pdo_sqlite;posix;readline;session;sqlite3;standard;tokenizer;xml;xmlreader;xmlwriter;zlib'.split(';');
const phpArray=names=>'array('+names.map(n=>"'"+n+"'").join(',')+')';
function precedes(source,a,b){assert.ok(source.indexOf(a)>=0,a);assert.ok(source.indexOf(b)>=0,b);assert.ok(source.indexOf(a)<source.indexOf(b),a+' before '+b);}
function guard(names,settings='array()') {
  const source=read('scripts/ci/source-policy-minimum-worker-source.php');
  const match=/adbb_check\( ([^\n]+), 'fixed (?:dynamic|selected-image) module profile' \);/.exec(source);assert.ok(match);
  const file=new URL('scripts/ci/source-policy-minimum-worker-source.php',root).pathname.replace(/^\/([A-Za-z]:\/)/,'$1');
  const code=`namespace {define('ABSPATH','/synthetic-not-wordpress/');function adbb_check(){throw new \\RuntimeException('No native IO permitted');}require '${file}';}namespace SyntheticModuleGuard {function extension_loaded($name){return in_array($name,$GLOBALS['names'],true);}function ini_get($name){return $GLOBALS['settings'][$name]??'';}$names=${phpArray(names)};$GLOBALS['names']=$names;$GLOBALS['settings']=${settings};echo (${match[1]})?'accepted':'refused';}`;
  const child=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',code],{encoding:'utf8',timeout:5000,maxBuffer:8192});
  assert.equal(child.status,0,child.stderr+child.stdout);assert.equal(child.stderr,'');return child.stdout;
}
function php(code) {
  const file=new URL('scripts/ci/source-policy-minimum-worker-source.php',root).pathname.replace(/^\/([A-Za-z]:\/)/,'$1');
  const child=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',`define('ABSPATH','/synthetic-not-wordpress/');function adbb_check(){throw new RuntimeException('No runtime or assertion IO permitted');}require '${file}';${code}`],{encoding:'utf8',timeout:5000,maxBuffer:8192});
  assert.equal(child.status,0,child.stderr+child.stdout);assert.equal(child.stderr,'');return child.stdout;
}
test('only the complete observed 33-module selected-image profile is admitted, including mbstring',()=>{
  assert.equal(observed.length,33);assert.ok(observed.includes('mbstring'));assert.ok(!observed.includes('pdo_mysql'));
  assert.equal(php(`echo adbm_fixed_module_names(${phpArray(observed)})?'accepted':'refused';`),'accepted');
});
test('actual source-owned module guard accepts the observed profile but refuses PDO-MySQL and injected entry files',()=>{
  assert.equal(guard(observed),'accepted');
  assert.equal(guard([...observed,'pdo_mysql'].sort()),'refused');
  assert.equal(guard(observed,"array('auto_prepend_file'=>'/synthetic/injected')"),'refused');
  assert.equal(guard(observed,"array('auto_append_file'=>'/synthetic/injected')"),'refused');
});
test('each missing, extra, replaced, reordered or duplicated module is refused without native IO',()=>{
  const bad=[];
  for(let i=0;i<observed.length;i++){
    bad.push(observed.filter((_,j)=>j!==i));const replaced=[...observed];replaced[i]='unapproved_module';bad.push(replaced.sort());
  }
  bad.push([...observed,'pdo_mysql'].sort(),[...observed,'unapproved_module'].sort(),[...observed,'mbstring'].sort(),[...observed].reverse(),observed.map(n=>n==='Core'?'core':n));
  const code=bad.map(names=>`echo adbm_fixed_module_names(${phpArray(names)})?'T':'F';`).join('');
  assert.equal(php(code),'F'.repeat(bad.length));
  assert.equal(php("foreach(array(null,false,33,'mbstring',new stdClass(),array())as$v){echo adbm_fixed_module_names($v)?'T':'F';}"),'FFFFFF');
  assert.equal(php(`$v=${phpArray(observed)};unset($v[0]);$v[99]='Core';echo adbm_fixed_module_names($v)?'T':'F';`),'F');
});
test('source-backed binding preserves all other identity, INI, report-mode, inventory and reflection guards',()=>{
  const source=read('scripts/ci/source-policy-minimum-worker-source.php'),worker=read('scripts/ci/source-policy-minimum-qualification.php'),controller=read('scripts/ci/source-policy-minimum-controller.mjs');
  assert.match(source,/adbb_check\( adbm_fixed_module_names\( \$names \) && ! extension_loaded\( 'pdo_mysql' \) && '' === ini_get\( 'auto_prepend_file' \) && '' === ini_get\( 'auto_append_file' \)/);
  for(const token of ['51810786a5d7ab1e43e7d43077493d7dfced717cc81fc08270cde58b32731d29','aa91cdc28133fac497aade6a658e83c219fcbbbfb809069969dd32c749a8657e','count( $names ) <= 64','false === php_ini_loaded_file()','! php_ini_scanned_files()',"$function->isInternal() && 'mysqli' === $function->getExtensionName()"])
    assert.ok(source.includes(token),token);
  precedes(source,'adbb_check( $php_root,','adbb_check( adbm_fixed_module_names(');
  precedes(source,'adbb_check( $mysqli_root,','adbb_check( adbm_fixed_module_names(');
  assert.match(source,/adbb_check\( \$function->isInternal\(\) && 'mysqli' === \$function->getExtensionName\(\), 'internal native declarations' \)/);
  assert.match(worker,/adbb_check\( 0 === \( new mysqli_driver\(\) \)->report_mode, 'initial report OFF' \)/);
  assert.match(worker,/adbm_modules\(\) === \$modules, 'module inventory unchanged'/);
  assert.match(controller,/requireFact\(out.split\('\|'\)\[5\]===modules,'audit\/proof inventory mismatch'\)/);
  assert.match(controller,/08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4/);
});
