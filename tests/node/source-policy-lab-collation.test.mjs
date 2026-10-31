import {test} from 'node:test';
import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {readFileSync} from 'node:fs';
const read=p=>readFileSync(new URL('../../'+p,import.meta.url),'utf8');
const source=read('includes/class-source-policy-bootstrap-evidence.php').replace(/^<\?php\s*/,'');
const checks=`function verify($v){if(!$v)throw new RuntimeException('collation-assertion-control');}
$row=array(array('db'=>'isolated','ac'=>'1','tx'=>'0','statement_limit'=>'3','row_limit'=>'1','metadata_limit'=>'1','client_charset'=>'utf8mb4','connection_charset'=>'utf8mb4','result_charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_520_ci','mode'=>'NO_ENGINE_SUBSTITUTION'));
$frame=array('core'=>'6.0','database'=>'isolated','table'=>'wp_options');$modes=array('NO_ENGINE_SUBSTITUTION');$name='Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Evidence';
verify($name::session($row,$frame,'utf8mb4','utf8mb4_unicode_520_ci',$modes));
$mismatch=$row;$mismatch[0]['collation']='utf8mb4_unicode_ci';verify(!$name::session($mismatch,$frame,'utf8mb4','utf8mb4_unicode_520_ci',$modes));
class UnknownCollationProfileDouble {public $calls=0;function minimum_lab_profile(){++$this->calls;return array('core'=>'6.0','database'=>'isolated','table'=>'wp_options','collation'=>'utf8mb4_unicode_ci');}}
$unknown=new UnknownCollationProfileDouble();verify(!$name::session($mismatch,$frame,'utf8mb4','utf8mb4_unicode_ci',$modes,$unknown));verify($unknown->calls===0);
$columns=array();foreach(array(array('option_id','bigint(20) unsigned','NO','PRI','auto_increment'),array('option_name','varchar(191)','NO','UNI',''),array('option_value','longtext','NO','',''),array('autoload','varchar(20)','NO','MUL','')) as $v)$columns[]=array_combine(array('name','type','nullable','key_kind','extra'),$v);
$indexes=array();foreach(array(array('PRIMARY','0','option_id'),array('autoload','1','autoload'),array('option_name','0','option_name')) as $v)$indexes[]=array('name'=>$v[0],'non_unique'=>$v[1],'sequence'=>'1','column_name'=>$v[2],'prefix_length'=>null);
verify(!$name::schema(array(array('engine'=>'InnoDB','collation'=>'utf8mb4_unicode_ci')),$columns,$indexes));echo 'verified';`;
function run(code){return spawnSync(process.env.PHP_BINARY??'php',['-n','-r',`define('ABSPATH','isolated/');eval(base64_decode('${Buffer.from(code).toString('base64')}'));${checks}`],{encoding:'utf8',timeout:5000,maxBuffer:8192});}
test('in-memory actual validator controls kill default widening, missing receiver binding, session mismatch and changed schema requirements',()=>{
  const baseline=run(source);assert.equal(baseline.status,0,baseline.stderr+baseline.stdout);assert.equal(baseline.stderr,'');assert.equal(baseline.stdout,'verified');
  const unknownGate=/if \( ! is_object\( \$receiver \)[\s\S]+?\n\t\t\}/;
  assert.match(source,unknownGate);
  const mutants=[source.replace("return 'utf8mb4_unicode_520_ci';","return 'utf8mb4_unicode_ci';"),
    source.replace(unknownGate,'if ( false ) { return false; }'),
    source.replace("( $row['collation'] ?? null ) === $collation",'true'),
    source.replace("'collation' => 'utf8mb4_unicode_520_ci',","'collation' => 'utf8mb4_unicode_ci',")];
  for(const mutant of mutants){assert.notEqual(mutant,source);const r=run(mutant);assert.equal(r.error,undefined);assert.notEqual(r.status,0);assert.match(r.stdout+r.stderr,/RuntimeException: collation-assertion-control/);}
});
test('source-backed scope and runtime call choose the collation without a caller string, altered pins, SQL or production factory wiring',()=>{
  const scope=read('includes/class-source-policy-minimum-lab-scope.php'),bootstrap=read('includes/traits/trait-source-policy-native-bootstrap.php'),lab=read('includes/traits/trait-source-policy-minimum-lab.php');
  assert.match(scope,/'collation'\s*=> '' === \$this->frame\['collate'\] \? 'utf8mb4_unicode_ci' : 'utf8mb4_unicode_520_ci'/);
  assert.match(bootstrap,/null === \$this->minimum_lab_scope \? null : \$this/);
  assert.match(lab,/\$expected\['collation'\] !== \$this->collate/);assert.match(lab,/'utf8mb4' !== \$this->charset/);
  assert.match(source,/get_class\( \$receiver \)/);assert.match(source,/getFileName\(\)/);assert.match(source,/\$receiver->minimum_lab_profile\(\)/);
  assert.doesNotMatch(source,/mysqli_|->execute\(|->query\(|update_option|setValue\(/);
});
