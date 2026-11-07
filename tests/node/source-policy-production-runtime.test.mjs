import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {fileURLToPath} from 'node:url';
import {spawnSync} from 'node:child_process';
const root=fileURLToPath(new URL('../../',import.meta.url)),php=process.env.PHP_BINARY??'php';
const source=readFileSync(new URL('../../includes/traits/trait-source-policy-production-join.php',import.meta.url),'utf8');
function observation(change=expression=>expression){
  const match=/\$this->production_runtime\['nts'\]\s*=\s*([^;]+);/.exec(source);assert.ok(match,'Actual immutable runtime observation must remain present');
  const expression=change(match[1]).replaceAll('PHP_ZTS','$zts');
  const script=`foreach(array(false,true,0,1,null,'0','',array()) as $zts){$observed=${expression};echo $observed===true?'1':($observed===false?'0':'X');}if(extension_loaded('mysqli')||class_exists('wpdb',false))throw new RuntimeException('effects');`;
  const result=spawnSync(php,['-n','-r',script],{cwd:root,encoding:'utf8',timeout:5000,maxBuffer:4096});
  if(result.error||result.status!==0||result.stderr!=='')throw Error('Isolated runtime expression setup failure, not semantic assertion kill');
  return result.stdout;
}
test('actual PHP8.5 NTS observation accepts only boolean false, not integer or loosely false DATA',()=>{
  assert.equal(observation(),'10000000');
  for(const change of [()=> '0 === PHP_ZTS',()=> '! PHP_ZTS',()=> 'true'])assert.throws(()=>assert.equal(observation(change),'10000000'),{code:'ERR_ASSERTION'});
});
