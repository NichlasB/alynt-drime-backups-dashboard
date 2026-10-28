<?php
/** Trait declarations only in parent; APIs/driver fixtures load solely in child tests. */
trait Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case {
	protected function setUp(): void {
		parent::setUp();
		define( 'ADB_POLICY_ISOLATED', true );
		require __DIR__.'/source-policy-isolated-wordpress.php';
		require __DIR__.'/source-policy-isolated-wpdb.php';
		foreach (array('query-context','no-reconnect-connection','native-profile','stored-map','native-adapter','guarded-entry') as $file) require_once dirname(__DIR__,2).'/includes/class-source-policy-'.$file.'.php';
		require __DIR__.'/source-policy-isolated-fixtures.php';
		$GLOBALS['policy_capable']=true; $GLOBALS['policy_trace']=array(); $GLOBALS['wp_filter']=array();
		$GLOBALS['policy_report_mode']=3;
		$GLOBALS['policy_cache_fail']=false; $GLOBALS['policy_cache']=array();
		$GLOBALS['wp_object_cache']=new WP_Object_Cache(); $GLOBALS['policy_ext_cache']=false;
		unset($GLOBALS['wpdb']); $GLOBALS['policy_after_add']=null; $GLOBALS['policy_remove_throw']=false; $GLOBALS['policy_remove_retain']=false;
	}
	private function inject($object,$property,$value) {
		$class=in_array($property,array('qualification','query_context','native_state','report_mode','report_source'),true)?Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::class:$object;
		$slot=new ReflectionProperty($class,$property); if(PHP_VERSION_ID<80100) $slot->setAccessible(true); $slot->setValue($object,$value);
	}
	private function owner($store=null,$core='7.1.3',$connection=null) {
		$store=$store?:new Alynt_Drime_Backups_Dashboard_Test_Policy_Store(array(),'6.0'===$core?'no':'off');
		$connection=$connection?:new Alynt_Drime_Backups_Dashboard_Test_Policy_Connection($store);
		if(null!==$connection->dbh) $this->inject($connection,'native_state','active'); // RAM handle, not native provenance.
		$this->inject($connection,'report_mode',3);
		$context=$this->context_for($connection); $context->begin(); $this->inject($connection,'query_context',$context);
		$facts=array('core'=>$core,'server'=>'10.6.23-MariaDB','database'=>'isolated','table'=>'wp_options','schema'=>'options4-unique-name-InnoDB','autocommit'=>true,'single_site'=>true,'dropins'=>false,'cache'=>'core-process','bounded_session'=>true,'hooks'=>Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::fingerprint());
		$this->inject($connection,'qualification',$facts);
		$owner=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter::class))->newInstanceWithoutConstructor();
		$this->inject($owner,'connection',$connection);
		return array($owner,$connection,$store,$facts,$context);
	}
	private function context_for($connection) {
		$context=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context::class))->newInstanceWithoutConstructor();
		foreach(array('owned'=>$connection,'ambient'=>$GLOBALS['wpdb']??null,'core_file'=>__DIR__.'/source-policy-isolated-wpdb.php','owned_class'=>get_class($connection),'owned_file'=>(new ReflectionClass($connection))->getFileName()) as $field=>$value) $this->inject($context,$field,$value);
		return $context;
	}
	private function revision($store,$core='7.1.3') {
		$owner=hash('sha256','isolated:wp_options');
		$observation=Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map::decode($store->row,$owner,'6.0'===$core?array('yes','no'):array('yes','no','on','off','auto','auto-on','auto-off'));
		return $observation['model']->revision();
	}
	private function error_code($result,$code) { $this->assertInstanceOf(WP_Error::class,$result); $this->assertSame($code,$result->get_error_code()); $this->assertStringNotContainsString('owned-private',serialize($result)); }
	private function entry($owner=null) {
		$owner=$owner?:new Alynt_Drime_Backups_Dashboard_Test_Policy_Collaborator();
		$sites=new Alynt_Drime_Backups_Dashboard_Test_Policy_Sites();
		$entry=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry::class))->newInstanceWithoutConstructor();
		$this->inject($entry,'owner',$owner); $this->inject($entry,'sites',$sites);
		return array($entry,$owner,$sites);
	}
	private function request() { return array('_wpnonce'=>'valid','dashboard_site_id'=>'7','source_key'=>'wpvivid','source_mode'=>'external_optional','source_policy_revision'=>str_repeat('a',64)); }
}
