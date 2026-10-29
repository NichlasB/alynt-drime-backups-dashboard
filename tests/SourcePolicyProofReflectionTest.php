<?php
use PHPUnit\Framework\TestCase;

/** Actual harness helper functions in a driver-free, JSON-free disposable process. */
final class SourcePolicyProofReflectionTest extends TestCase {
	public function test_real_helper_preserves_private_visibility_constructor_and_closed_owner() {
		$root=dirname(__DIR__);$code='require $argv[1];class wpdb {public $ready=true,$dbh=null;public function __construct(){throw new RuntimeException("Parent constructor must not run");}}class WP_Error {public $errors=array();}define("ABSPATH",$argv[2]."/");require $argv[2]."/includes/class-source-policy-no-reconnect-connection.php";';
		$code.='$class=new ReflectionClass("Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection");$owner=adbb_construct($class);';
		$code.='class ProofInputs {private $input="initial";public function value(){return $this->input;}}$inputs=new ProofInputs;adbb_bind($inputs,"input","owned\0input");$fixed=false;try{adbb_construct(new ReflectionClass("wpdb"));}catch(RuntimeException $error){$fixed="Fixed proof receiver required"===$error->getMessage();}echo serialize(array($owner->ready,$owner->dbh,$owner->db_connect(),$owner->check_connection(),$inputs->value(),$class->getConstructor()->isProtected(),function_exists("json_encode"),$fixed,array_keys($owner::create()->errors),$owner->profile()));';
		$process=proc_open(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode','-r',$code,$root.'/scripts/manual/source-policy-proof-reflection.php',$root),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),$err);$this->assertSame('',$err);
		$this->assertSame(array(false,null,false,false,"owned\0input",true,false,true,array('source_policy_unavailable'),null),unserialize($out,array('allowed_classes'=>false)));
	}
	public function test_compatibility_does_not_enable_public_factory_or_reconnect() {
		$source=file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-proof-reflection.php');
		$this->assertSame(2,substr_count($source,'PHP_VERSION_ID < 80100'));$this->assertSame(2,substr_count($source,'->setAccessible( true )'));
		foreach(array('mysqli_',"'ready'","'dbh'",'bootstrap(',"'active'",'json_encode','getenv(') as $forbidden)$this->assertStringNotContainsString($forbidden,$source);
		foreach(array('bootstrap-owned-proof','bootstrap-native-cases','adapter-proof-cases','ambient-proof-operation','cleanup-native-cases','connected-cleanup-operation','failed-connect-diagnosis') as $name){
			$worker=file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-'.$name.'.php');
			$this->assertStringNotContainsString('->getConstructor()->invoke(',$worker,$name);$this->assertStringContainsString('adbb_construct(',$worker,$name);
		}
	}
}
