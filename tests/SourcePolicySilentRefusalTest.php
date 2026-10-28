<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicySilentRefusalTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	public function test_realistic_error_model_exposes_both_core_events() {
		$events=array(); $observer=static function($hook)use(&$events){$events[]=$hook;};
		policy_isolated_hook('all',$observer,1);
		$error=new WP_Error('source_policy_unavailable');
		$this->assertSame(array('wp_error_added'),$events);
		$this->assertTrue(is_wp_error($error));
		$this->assertSame(array('wp_error_added','is_wp_error_instance'),$events);
		$this->assertSame('diagnostic',__('diagnostic','policy-proof'));
		$this->assertSame(array('wp_error_added','is_wp_error_instance','gettext','gettext_policy-proof'),$events);
	}
	/** @dataProvider boundaries */
	public function test_owned_refusals_do_not_dispatch_error_observers($operation,$hook,$behavior) {
		$events=0; $entered=false; $observer=static function()use(&$events,&$entered,$behavior){
			++$events;
			if('throw'===$behavior)throw new RuntimeException('Observer must never execute');
			if('reenter'===$behavior&&!$entered){$entered=true;Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::create();}
		};
		policy_isolated_hook($hook,$observer,1); $registry=$GLOBALS['wp_filter'][$hook]->callbacks;
		$connection=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::class))->newInstanceWithoutConstructor();
		$constructor=new ReflectionMethod($connection,'__construct');if(PHP_VERSION_ID<80100)$constructor->setAccessible(true);$constructor->invoke($connection);
		if('factory'===$operation)$result=$connection::create();
		elseif('admission'===$operation)$result=$connection->admit_profile();
		elseif('query'===$operation)$result=$connection->query('SELECT never-dispatched');
		elseif('bootstrap'===$operation){$method=new ReflectionMethod($connection,'bootstrap');if(PHP_VERSION_ID<80100)$method->setAccessible(true);$result=$method->invoke($connection);}
		elseif('decode'===$operation)$result=Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map::decode(array(),'fixture',array('off'));
		else $result=Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe(array(),false,'','',array());
		$this->assertSame(0,$events,'Owned error construction/classification must not invoke an observer.');
		if('query'===$operation)$this->assertFalse($result);else {
			$this->assertInstanceOf(WP_Error::class,$result);
			$this->assertSame(in_array($operation,array('decode','data'),true)?'source_policy_invalid':'source_policy_unavailable',$result->get_error_code());
			$this->assertNull($result->get_error_data());
		}
		$this->assertNull($connection->dbh); $this->assertFalse($connection->ready);
		$this->assertSame(0,$connection->physical); $this->assertSame($registry,$GLOBALS['wp_filter'][$hook]->callbacks);
	}
	public static function closed_operations(){return array(array('factory'),array('admission'),array('query'),array('bootstrap'),array('decode'),array('data'));}
	public static function boundaries(){
		$cases=array();foreach(self::closed_operations() as $case)foreach(array('all','wp_error_added','is_wp_error_instance','gettext') as $hook)foreach(array('observe','throw','reenter') as $behavior)$cases[]=array($case[0],$hook,$behavior);return $cases;
	}
	public function test_fixed_codes_message_data_and_external_predicate_are_preserved() {
		$events=array();policy_isolated_hook('all',static function($hook)use(&$events){$events[]=$hook;},1);
		foreach(array('unavailable','invalid','forbidden','conflict','storage_error','write_uncertain','save_failed') as $reason){
			$error=Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal('source_policy_'.$reason);
			$this->assertSame('WP_Error',get_class($error));$this->assertSame('source_policy_'.$reason,$error->get_error_code());$this->assertSame('',$error->get_error_message());$this->assertNull($error->get_error_data());
			$this->assertTrue(Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error($error));
		}
		$diagnostic=Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe(array(),false,'','',array());
		$this->assertSame('The monitoring policy could not be saved safely.',$diagnostic->get_error_message());
		$this->assertSame(array(),$events);
		$this->assertTrue(is_wp_error($diagnostic));$this->assertSame(array('is_wp_error_instance'),$events,'External callers retain genuine observer behavior.');
	}
	public function test_type_classification_and_bad_code_do_not_probe_objects() {
		$probe=new class { public function __toString(){throw new RuntimeException('No coercion');} public function __invoke(){throw new RuntimeException('No invocation');} };
		$error=new class extends WP_Error { public function get_error_code(){throw new RuntimeException('No virtual getter during classification');} };
		foreach(array(null,false,0,1,'WP_Error',array(),new stdClass(),$probe) as $value)$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error($value));
		$this->assertTrue(Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error($error));
		foreach(array(null,false,1,'unknown',array(),$probe) as $value)$this->assertSame('source_policy_unavailable',Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal($value)->get_error_code());
	}
	public function test_other_factories_entry_guards_and_code_only_projection_stay_silent() {
		list($entry,$owner,$sites)=$this->entry();$owner->result=new WP_Error('source_policy_conflict','owned-private-message',array('raw'=>'owned-private'));
		$called=0;policy_isolated_hook('all',static function()use(&$called){++$called;throw new RuntimeException('No observer');});
		foreach(array('Native_Adapter','Guarded_Entry','Query_Context') as $suffix){
			$class='Alynt_Drime_Backups_Dashboard_Source_Policy_'.$suffix;$this->error_code($class::create(),'source_policy_unavailable');
		}
		$this->error_code(Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::admit(null,'wp_options'),'source_policy_unavailable');
		$GLOBALS['policy_capable']=false;$this->error_code($entry->view(7,'wpvivid'),'source_policy_forbidden');$this->assertSame(array(),$sites->calls);
		$GLOBALS['policy_capable']=true;$this->error_code($entry->submit(array()),'source_policy_invalid');$this->assertSame(array(),$owner->calls);
		$projected=$entry->submit($this->request());$this->error_code($projected,'source_policy_conflict');$this->assertSame('',$projected->get_error_message());$this->assertNull($projected->get_error_data());
		$this->assertSame(0,$called);
	}
	/** @dataProvider owner_faults */
	public function test_owner_positive_and_error_results_with_error_hook_tripwires($fault,$expected) {
		list($owner,$connection,$store)=$this->owner();$revision=$this->revision($store);
		if('read'===$fault)$store->read_fault=2006;
		if('write'===$fault)$store->write_fault=1064;
		if('ack'===$fault)$store->lose_ack=true;
		if('cache'===$fault){$GLOBALS['policy_cache_fail']=true;$GLOBALS['policy_cache']=array('options'=>array('alloptions'=>array('owned'=>'old')));}
		$called=0;foreach(array('wp_error_added','is_wp_error_instance','gettext') as $hook)policy_isolated_hook($hook,static function()use(&$called){++$called;throw new RuntimeException('No error observer');});
		$result=$owner->save_source_mode_if_current($revision,7,'wpvivid','external_optional');
		if('saved'===$expected){$this->assertSame('saved',$result);$this->assertSame(1,$store->mutations);}else $this->error_code($result,$expected);
		$this->assertSame(0,$called);$this->assertSame(1,$connection->closes);$this->assertSame(0,$connection->reconnects);
	}
	public static function owner_faults(){return array(array('none','saved'),array('read','source_policy_storage_error'),array('write','source_policy_storage_error'),array('ack','source_policy_write_uncertain'),array('cache','source_policy_write_uncertain'));}
	public function test_missing_core_error_class_never_invokes_an_autoloader() {
		$path=var_export(dirname(__DIR__).'/includes/class-source-policy-error.php',true);
		$code='define("ABSPATH","isolated");require '.$path.';$calls=0;spl_autoload_register(function()use(&$calls){++$calls;throw new RuntimeException("No autoload");});try{Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal();exit(2);}catch(RuntimeException $e){if("Policy error class unavailable"!==$e->getMessage())exit(3);}if(Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error(new stdClass())||0!==$calls)exit(4);echo "refused:no-autoload";';
		$process=proc_open(array(PHP_BINARY,'-n','-r',$code),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$output=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process));$this->assertSame('',$error);$this->assertSame('refused:no-autoload',$output);
	}
	public function test_foreign_error_subclass_getter_is_not_invoked_during_projection() {
		list($entry,$owner)=$this->entry();$foreign=new class extends WP_Error { public $called=0;public function get_error_code(){++$this->called;do_action('wp_error_added');return 'source_policy_conflict';} };$owner->result=$foreign;
		$events=0;policy_isolated_hook('all',static function()use(&$events){++$events;});$result=$entry->submit($this->request());
		$this->assertSame(0,$events);$this->assertSame(0,$foreign->called);$this->error_code($result,'source_policy_save_failed');$this->assertNull($result->get_error_data());
	}
}
