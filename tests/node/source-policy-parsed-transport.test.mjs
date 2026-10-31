import {test} from 'node:test';
import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';
import {readFileSync} from 'node:fs';
const read=p=>readFileSync(new URL('../../'+p,import.meta.url),'utf8');
const profile=read('includes/class-source-policy-transport-profile.php').replace(/^<\?php\s*/, '');
const trait=read('includes/traits/trait-source-policy-native-bootstrap.php');
const frame=`array('core'=>'6.0','transport'=>'tcp_loopback','host'=>'127.0.0.1','port'=>3306,'socket'=>null,'db_host'=>'127.0.0.1:3306')`;
function run(source,checks){
  return spawnSync(process.env.PHP_BINARY??'php',['-n','-r',`define('ABSPATH','isolated/');eval(base64_decode('${Buffer.from(source).toString('base64')}'));function verify($ok){if(!$ok)throw new RuntimeException('tuple-assertion-control');}${checks}echo 'verified';`],{encoding:'utf8',timeout:5000,maxBuffer:8192});
}
function accepted(result){assert.equal(result.error,undefined);assert.equal(result.status,0,result.stderr+result.stdout);assert.equal(result.stderr,'');assert.equal(result.stdout,'verified');}
function killed(source,checks){const r=run(source,checks);assert.equal(r.error,undefined);assert.notEqual(r.status,0);assert.match(r.stderr+r.stdout,/RuntimeException: tuple-assertion-control/);}
const checks=`$f=${frame};$name='Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile';
verify($name::matches_parsed($f,array('127.0.0.1','3306',null,false)));
verify(!$name::matches_parsed($f,array('127.0.0.1',3306,null,false)));
verify(!$name::matches_parsed($f,array('127.0.0.1','3306',null,0)));
verify(!$name::matches_parsed($f,array('localhost','3306',null,false)));
$bad=$f;$bad['db_host'].='tail';verify(!$name::matches_parsed($bad,array('127.0.0.1','3306',null,false)));
$bad=$f;$bad['port']=65536;$bad['db_host']='127.0.0.1:65536';verify(!$name::matches_parsed($bad,array('127.0.0.1','65536',null,false)));
$f['core']='7.1.3';verify($name::matches_parsed($f,array('127.0.0.1',3306,null,false)));
verify(!$name::matches_parsed($f,array('127.0.0.1','3306',null,false)));`;

test('actual pure matcher and four semantic guard controls discriminate source API types, bounds and endpoint identity',()=>{
  accepted(run(profile,checks));
  const mutants=[
    profile.replace("'6.0' === $frame['core'] ? (string) $frame['port'] : $frame['port']","$frame['port']"),
    profile.replace(') === $parsed;',') == $parsed;'),
    profile.replace('if ( ! self::valid( $frame, PHP_OS_FAMILY ) )','if ( false )'),
    profile.replace("return array( $frame['host'], $port, $frame['socket'], '::1' === $frame['host'] ) === $parsed;",'return true;'),
  ];
  for(const mutant of mutants){assert.notEqual(mutant,profile);killed(mutant,checks);}
});

test('actual transport guard excerpt rejects before effects and calls the parser once; bypass and delayed-guard controls are killed',()=>{
  const start=trait.indexOf('$frame               = $this->bootstrap_frame;');
  const end=trait.indexOf('parent::__construct(',start);assert.ok(start>=0&&end>start);
  const guard=trait.slice(start,end),predicate="! Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed( $frame, $this->parse_db_host( $frame['db_host'] ) )";
  assert.ok(guard.includes(predicate));
  assert.ok(trait.indexOf('$this->bootstrap_check( false )')<start);
  assert.ok(end<trait.indexOf('$this->query_context->begin()')&&end<trait.indexOf('mysqli_init()'));
  const fixture=body=>`class FixedTupleGuardDouble {public $bootstrap_frame,$bootstrap_binding=array('driver_file'=>'fixed'),$report_source,$report_mode,$parsed,$calls=0,$effects=0;
function observed_report_mode(){return 0;}function parse_db_host($host){++$this->calls;return $this->parsed;}
function invoke(){${body}++$this->effects;}}
$o=new FixedTupleGuardDouble();$o->bootstrap_frame=${frame};$o->parsed=array('127.0.0.1','3306',null,false);$o->invoke();verify($o->calls===1&&$o->effects===1);
$o=new FixedTupleGuardDouble();$o->bootstrap_frame=${frame};$o->parsed=array('127.0.0.1','3306tail',null,false);try{$o->invoke();}catch(RuntimeException $e){}verify($o->calls===1&&$o->effects===0);`;
  accepted(run(profile,fixture(guard)));
  const bypass=guard.replace(predicate,'false');assert.notEqual(bypass,guard);killed(profile,fixture(bypass));
  killed(profile,fixture('++$this->effects;'+guard));
});
