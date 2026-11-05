<?php
/** Discriminating isolated capture/dispatch assertions, not genuine authentication. */
use PHPUnit\Framework\Assert as A;
if(defined('ADB_POLICY_ISOLATED')) {
function trusted_case($case,$entry,$provider,$context,$adapter,$receiver,$store,$sites,$request) {
	if(in_array($case,array('view-capability','view-record','view-invalid','closed-factories','record-error'),true)) {
		if('view-capability'===$case)$GLOBALS['policy_capable']=false;
		if('view-record'===$case)$sites->record_override=array('id'=>8);
		if('record-error'===$case)boot_set($entry,'sites',new class {function get($id){throw new RuntimeException('owned-private-record-failure');}});
		if('closed-factories'===$case){foreach(array(Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context::class,Alynt_Drime_Backups_Dashboard_Source_Policy_Lazy_Provider::class,Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry::class,Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter::class,Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::class,Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context::class)as$class)trusted_error($class::create(),'source_policy_unavailable');}
		else { $result='record-error'===$case?$entry->submit($request):$entry->view('view-invalid'===$case?'007':7,'wpvivid');trusted_error($result,'view-capability'===$case?'source_policy_forbidden':('view-invalid'===$case?'source_policy_invalid':'source_policy_unavailable')); }
		A::assertSame('fresh',boot_get($context,'state'));A::assertFalse(boot_get($provider,'used'));A::assertSame(0,$GLOBALS['boot_parse_calls']);A::assertSame(0,$store->reads);A::assertSame(0,$receiver->closes);A::assertTrue($receiver->close());return;
	}
	$early=array('capability','nonce','nonce-domain','nonce-compound','site-compound','site-negative','site-overflow','site-padded','site-long','source-long','mode-long','revision-long','revision-upper','record');
	if(in_array($case,$early,true)) {
		if('capability'===$case)$GLOBALS['policy_capable']=false;
		if('nonce'===$case)$request['_wpnonce']='expired';if('nonce-domain'===$case)$request['_wpnonce']='other-action';if('nonce-compound'===$case)$request['_wpnonce']=array('valid');
		$changes=array('site-compound'=>array('dashboard_site_id',array()),'site-negative'=>array('dashboard_site_id','-7'),'site-overflow'=>array('dashboard_site_id','9999999999999999999'),'site-padded'=>array('dashboard_site_id','007'),'site-long'=>array('dashboard_site_id',str_repeat('7',1024)),'source-long'=>array('source_key',str_repeat('a',1024)),'mode-long'=>array('source_mode',str_repeat('a',1024)),'revision-long'=>array('source_policy_revision',str_repeat('a',1024)),'revision-upper'=>array('source_policy_revision',str_repeat('A',64)));
		if(isset($changes[$case]))$request[$changes[$case][0]]=$changes[$case][1];if('record'===$case)$sites->record_override=array('id'=>8);
		trusted_error($entry->submit($request),'capability'===$case?'source_policy_forbidden':('record'===$case?'source_policy_unavailable':'source_policy_invalid'));
		A::assertSame('fresh',boot_get($context,'state'));A::assertFalse(boot_get($provider,'used'));A::assertSame(0,$GLOBALS['boot_parse_calls']);A::assertSame(0,$store->reads);A::assertSame(0,$receiver->closes);
		if('capability'===$case)A::assertSame(array('capability'),$GLOBALS['policy_trace']);elseif(strpos($case,'nonce')===0)A::assertCount('nonce-compound'===$case?1:2,$GLOBALS['policy_trace']);
		if('record'!==$case)A::assertSame(array(),$sites->calls);A::assertTrue($receiver->close());return;
	}
	if('serialize'===$case) {
		foreach(array($context,$provider)as$object){$failed=false;try{serialize($object);}catch(RuntimeException $error){$failed=true;}A::assertTrue($failed);$class=get_class($object);$failed=false;try{unserialize('O:'.strlen($class).':"'.$class.'":0:{}');}catch(RuntimeException $error){$failed=true;}A::assertTrue($failed);$failed=false;try{$copy=clone $object;}catch(Error $error){$failed=true;}A::assertTrue($failed);}
		A::assertSame(array(),get_object_vars($context));A::assertSame(array(),get_object_vars($provider));A::assertTrue($receiver->close());return;
	}
	if('default-policy'===$case)boot_set($context,'policy',null);
	if('source'===$case){$policy=boot_get($context,'policy');$policy['pins']['source-policy-isolated-wpdb.php']=str_repeat('f',64);boot_set($context,'policy',$policy);}
	if('parser-source'===$case){$policy=boot_get($context,'policy');$policy['files']['parser_api_file']='source-policy-isolated-wordpress.php';boot_set($context,'policy',$policy);}
	if('missing-facts'===$case){$facts=$receiver->profile();unset($facts['database']);boot_set($receiver,'qualification',$facts);}
	if('foreign-class'===$case)$GLOBALS['wpdb']=new class {function __get($key){throw new RuntimeException('Unknown property called');}};
	$originalRegistry=$GLOBALS['wp_filter'];
	if('registry-shape'===$case)$GLOBALS['wp_filter']=new class implements ArrayAccess {
		public static $calls=0;
		public function offsetExists($key):bool{++self::$calls;throw new RuntimeException('No ArrayAccess during admission');}
		#[ReturnTypeWillChange]
		public function offsetGet($key){++self::$calls;throw new RuntimeException('No ArrayAccess read');}
		public function offsetSet($key,$value):void{++self::$calls;throw new RuntimeException('No ArrayAccess write');}
		public function offsetUnset($key):void{++self::$calls;throw new RuntimeException('No ArrayAccess unset');}
	};
	if('registry-shape'===$case){A::assertFalse($context->capture());A::assertSame(0,$GLOBALS['wp_filter']::$calls);$GLOBALS['wp_filter']=$originalRegistry;}
	if('parser-warning'===$case)$GLOBALS['boot_parser_warning']=true;
	if('positive-legacy-parser'===$case){$policy=boot_get($context,'policy');$policy['core']='6.0';boot_set($context,'policy',$policy);$GLOBALS['wp_version']='6.0';$GLOBALS['boot_parser_core']='6.0';$facts=$receiver->profile();$facts['core']='6.0';boot_set($receiver,'qualification',$facts);$store->row['autoload']='no';$request=trusted_request($store);}
	if('foreign-owner'===$case){$foreign=(new ReflectionClass(Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::class))->newInstanceWithoutConstructor();boot_invoke($foreign,'__construct');boot_set($context,'receiver',$foreign);}
	if('unknown-hook'===$case)policy_isolated_hook('update_option',static function(){A::fail('Unknown hook must not execute');});
	if(in_array($case,array('late-global','late-core','late-table','late-hook','reentry'),true))$store->after_read=static function()use($case,$provider,$receiver,$context,$store){
		if('late-global'===$case)$GLOBALS['wpdb']=(new ReflectionClass('wpdb'))->newInstanceWithoutConstructor();
		if('late-core'===$case)$GLOBALS['wp_version']='6.0';
		if('late-table'===$case)$GLOBALS['wpdb']->options='other_options';
		if('late-hook'===$case)policy_isolated_hook('update_option',static function(){A::fail('Unknown hook must not execute');});
		if('reentry'===$case){trusted_error($provider->view_source_mode(7,'wpvivid'),'source_policy_unavailable');A::assertSame(0,$receiver->closes);A::assertSame('captured',boot_get($context,'state'));A::assertSame(DB_PASSWORD,boot_get($context,'frame')['password']);}
	};
	if('stale'===$case)$request['source_policy_revision']=str_repeat('f',64);
	if('unchanged'===$case)$request['source_mode']='required';
	if('read-error'===$case)$store->read_fault=1064;
	if('uncertain'===$case)$store->throw_after_write=true;
	if('close-error'===$case)$store->close_ok=false;
	$result='positive-view'===$case?$entry->view(7,'wpvivid'):$entry->submit($request);
	if('positive-view'===$case){A::assertSame(array('mode','revision'),array_keys($result));A::assertSame('required',$result['mode']);A::assertSame($request['source_policy_revision'],$result['revision']);A::assertSame(0,$store->mutations);}
	elseif(in_array($case,array('positive-save','positive-legacy-parser','reentry'),true)){A::assertSame('saved',$result);A::assertSame(1,$store->mutations);}
	elseif('unchanged'===$case){A::assertSame('unchanged',$result);A::assertSame(0,$store->attempts);}
	else { $code='source_policy_unavailable';if('stale'===$case)$code='source_policy_conflict';if('read-error'===$case)$code='source_policy_storage_error';if(in_array($case,array('uncertain','close-error'),true))$code='source_policy_write_uncertain';trusted_error($result,$code); }
	if(in_array($case,array('default-policy','source','parser-source','foreign-class','foreign-owner','unknown-hook','registry-shape','missing-facts'),true))A::assertSame(0,$store->reads);
	if(in_array($case,array('source','parser-source','foreign-class','unknown-hook'),true))A::assertSame(0,$GLOBALS['boot_parse_calls'],'Refuse provenance before invoking even the parser.');
	A::assertNull(boot_get($context,'frame'));A::assertNull(boot_get($context,'receiver'));A::assertNull(boot_get($context,'ambient'));A::assertNull(boot_get($context,'policy'));A::assertNull(boot_get($context,'binding'));A::assertSame('closed',boot_get($context,'state'));
	A::assertNull(boot_get($provider,'owner'));A::assertNull(boot_get($provider,'context'));A::assertNull(boot_get($adapter,'operation_context'));
	$reads=$store->reads;$mutations=$store->mutations;trusted_error($provider->view_source_mode(7,'wpvivid'),'source_policy_unavailable');A::assertSame($reads,$store->reads);A::assertSame($mutations,$store->mutations);
	A::assertFalse($context->capture());A::assertFalse($context->valid($receiver));
	if('foreign-owner'===$case)A::assertTrue($receiver->close());else A::assertSame(1,$receiver->closes);
	if('registry-shape'!==$case)A::assertFalse(has_filter('query',array($receiver,'remove_placeholder_escape')));
}
}
