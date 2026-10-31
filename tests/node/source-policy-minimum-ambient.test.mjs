import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {spawnSync} from 'node:child_process';
const root=new URL('../../',import.meta.url),read=p=>readFileSync(new URL(p,root),'utf8');
const worker=read('scripts/ci/source-policy-minimum-qualification.php');
function between(source,start,end){const a=source.indexOf(start),b=source.indexOf(end,a);assert.ok(a>=0&&b>a,start+' to '+end);return source.slice(a,b);}
const setup=between(worker,"$ambient           = ( new ReflectionClass( 'wpdb' ) )",'\n\tadbb_check( 0 === ( new mysqli_driver() )');
const release=between(worker,'\tif ( $ambient instanceof wpdb ) {',"\n\t$frame['password'] = '';");
// Execute only source-owned composition/cleanup excerpts against in-memory stand-ins.
// No WordPress source is loaded, no native mysqli classes/calls, SQL or networking.
function model(mode='good',source=setup) {
  const code=`namespace AmbientModel {
  use \\ReflectionClass;
  class mysqli {}
  class wpdb {
    public static $constructs=0,$connects=0,$closes=0,$args=false,$mode='${mode}';
    public $dbuser,$dbpassword,$dbname,$dbhost,$ready=false,$dbh=null;
    private $initialized=false;
    public function __construct($user,$password,$database,$host){
      ++self::$constructs;self::$args=array($user,$password,$database,$host)===array('owned-user','inert-test','owned-db','127.0.0.1:3306');
      if(self::$mode==='throw-before')throw new \\Error('private failure');
      $this->initialized=true;$this->dbuser=$user;$this->dbpassword=$password;$this->dbname=$database;$this->dbhost=$host;
      $this->db_connect();if(self::$mode==='throw-after')throw new \\Error('private failure');
    }
    public function db_connect($bail=true){
      ++self::$connects;if(!$this->initialized||self::$connects>1)throw new \\Error('invalid construction or repeat');
      if(self::$mode==='not-ready')return false;
      $this->ready=true;$this->dbh=self::$mode==='no-handle'?null:new mysqli();return true;
    }
    public function close(){++self::$closes;$this->dbh=null;$this->ready=false;return true;}
    public function flush(){}
  }
  function adbb_bind($object,$field,$value){$object->$field=$value;}
  function adbb_check($ok,$label){if(!$ok)throw new \\RuntimeException('Refused');}
  function remove_filter($name,$callback,$priority){}
  }
  namespace {class_alias('AmbientModel\\\\wpdb','wpdb');}
  namespace AmbientModel {
  use \\ReflectionClass;
  $ambient=null;$wpdb=null;$failed=false;$frame=array('user'=>'owned-user','password'=>'inert-test','database'=>'owned-db','db_host'=>'127.0.0.1:3306');
  $outcome='accepted';try{${source}}catch(\\Throwable $error){$outcome='refused';$failed=true;}
  $retained=$ambient instanceof wpdb&&$wpdb===$ambient;
  ${release}
  echo $outcome.'|'.wpdb::$constructs.'|'.wpdb::$connects.'|'.wpdb::$closes.'|'.(wpdb::$args?'1':'0').'|'.($retained?'1':'0').'|'.($ambient instanceof wpdb&&$ambient->dbh===null?'1':'0').'|'.($failed?'1':'0');
  }`;
  const child=spawnSync(process.env.PHP_BINARY??'php',['-n','-r',code],{encoding:'utf8',timeout:5000,maxBuffer:2048});
  assert.equal(child.status,0,child.stderr+child.stdout);assert.equal(child.stderr,'');return child.stdout;
}
test('actual ambient composition invokes constructor once with fixed inputs and no reconnect',()=>{
  assert.equal(model(),'accepted|1|1|1|1|1|1|0');
});
test('constructor failures stay refused and partially initialized ownership remains available to actual cleanup',()=>{
  assert.equal(model('throw-before'),'refused|1|0|0|1|1|1|1');
  assert.equal(model('throw-after'),'refused|1|1|1|1|1|1|1');
  assert.equal(model('not-ready'),'refused|1|1|0|1|1|1|1');
  assert.equal(model('no-handle'),'refused|1|1|0|1|1|1|1');
});
test('in-memory semantic controls kill omitted constructor, repeat connection and missing readiness guard',()=>{
  const omitted=setup.replace(/\$ambient->__construct\([^;]+;/,'');assert.notEqual(omitted,setup);
  assert.throws(()=>assert.equal(model('good',omitted),'accepted|1|1|1|1|1|1|0'),assert.AssertionError);
  const repeat=setup+'$ambient->db_connect(false);';
  assert.throws(()=>assert.equal(model('good',repeat),'accepted|1|1|1|1|1|1|0'),assert.AssertionError);
  const unchecked=setup.replace(/adbb_check\( true === \$ambient->ready[^;]+;/,'');assert.notEqual(unchecked,setup);
  assert.throws(()=>assert.equal(model('not-ready',unchecked),'refused|1|1|0|1|1|1|1'),assert.AssertionError);
});
test('entry, audit, single-connection and report-mode guards remain source-backed',()=>{
  for(const guard of ["if ( 'audit' === $argv[2] )","'frozen parent source and owned credential'"]){assert.ok(worker.indexOf(guard)>=0);assert.ok(worker.indexOf(guard)<worker.indexOf(setup));}
  assert.match(setup,/\$wpdb\s*=\s*\$ambient;[\s\S]*\$ambient->__construct\( \$frame\['user'\], \$frame\['password'\], \$frame\['database'\], \$frame\['db_host'\] \)/);
  assert.doesNotMatch(setup,/adbb_bind\(|->db_connect\(|setValue|setAccessible|->(?:ready|dbh|use_mysqli)\s*=(?!=)/);
  assert.match(worker,/0 === \( new mysqli_driver\(\) \)->report_mode && '1' === mysqli_get_client_stats\(\)\['connect_success'\]/);
  assert.match(worker,/20000000000/);
  assert.match(read('.github/workflows/source-policy-minimum-qualification.yml'),/workflow_dispatch:/);
  assert.doesNotMatch(read('.github/workflows/ci.yml'),/minimum-controller|minimum-qualification/);
});
