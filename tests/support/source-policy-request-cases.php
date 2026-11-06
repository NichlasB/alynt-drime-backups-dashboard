<?php
/** Fixed guard/assembly scenarios; Reflection is terminal observation ONLY. */
use PHPUnit\Framework\Assert as A;
if(defined('ADB_POLICY_ISOLATED')) {
function request_case($case,$request,$store) {
	$class=Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry::class;
	if('capability'===$case)$GLOBALS['policy_capable']=false;
	if('nonce'===$case)$request['_wpnonce']='wrong';
	if('site'===$case)$request['dashboard_site_id']='007';
	if('revision'===$case)$request['source_policy_revision']='wrong';
	if('compound-nonce'===$case)$request['_wpnonce']=array('valid');
	if('compound-site'===$case)$request['dashboard_site_id']=array(7);
	if('compound-mode'===$case)$request['source_mode']=new stdClass();
	if('source-key'===$case)$request['source_key']='drime';
	if('request-object'===$case)$request=new stdClass();
	if('record'===$case)$GLOBALS['request_record']=array('id'=>8);
	if('record-throw'===$case)$GLOBALS['request_record_throw']=true;
	if('stale'===$case)$request['source_policy_revision']=str_repeat('f',64);
	if('noop'===$case)$request['source_mode']='required';
	if('uncertain'===$case)$store->throw_after_write=true;
	if('close-error'===$case)$GLOBALS['boot_after']['indexes']=static function(){$GLOBALS['boot_fault']='close-false';};
	if('native-policy'===$case)A::assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Source::request_policy('6.0'));
	if('original-policy'===$case)foreach(array('6.0','7.1.3','7.1.4','unknown',null)as$core)A::assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Source::request_policy($core));
	if('root-controls'===$case)$GLOBALS['boot_after']['indexes']=static function(){
		$receiver=$GLOBALS['boot_connection'];$root=$GLOBALS['request_observed_root'];$peers=$GLOBALS['request_observed_peers'];
		A::assertCount(5,$peers);A::assertTrue($root->request_bound($receiver,$peers['query'],$peers['context']));
		A::assertFalse($root->request_claim('connection'));A::assertFalse($root->request_retain(new stdClass()));
		A::assertFalse($root->request_bound($receiver,new stdClass(),$peers['context']));A::assertFalse($root->request_bound(new stdClass(),$peers['query'],$peers['context']));A::assertFalse($root->request_bound($receiver,$peers['query'],new stdClass()));
		A::assertNull(Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::from_request($root));A::assertNull(Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context::from_request($root,$receiver,'wrong'));
		A::assertFalse($receiver->bind_request_frame($root,$peers['context'],$peers['query'],$GLOBALS['request_observed_frame'],$GLOBALS['request_observed_binding']),'Duplicate push refuses.');
		A::assertFalse($receiver->release_request($root),'Cannot detach before cleanup.');
		foreach(array('clone','serialize')as$operation){$refused=false;try{if('clone'===$operation)$copy=clone $root;else serialize($root);}catch(Throwable $error){$refused=true;}A::assertTrue($refused);}
		$trace=$GLOBALS['policy_trace'];$records=$GLOBALS['request_record_reads'];trusted_error(Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry::request_view(7,'wpvivid'),'source_policy_unavailable');A::assertSame($trace,$GLOBALS['policy_trace']);A::assertSame($records,$GLOBALS['request_record_reads']);
	};
	$result='view'===$case?$class::request_view(7,'wpvivid'):$class::request_submit($request);
	if(in_array($case,array('save','root-controls'),true)){A::assertSame('saved',$result);A::assertSame(1,$store->mutations);A::assertSame(array(9=>array('wpvivid'=>'external_optional'),7=>array('wpvivid'=>'external_optional')),unserialize($store->row['option_value']));A::assertSame('no',$store->row['autoload']);}
	elseif('view'===$case){A::assertSame(array('mode'=>'required','revision'=>$request['source_policy_revision']),$result);A::assertSame(0,$store->mutations);}
	elseif('noop'===$case){A::assertSame('unchanged',$result);A::assertSame(0,$store->attempts);}
	else{$codes=array('capability'=>'forbidden','nonce'=>'invalid','site'=>'invalid','revision'=>'invalid','compound-nonce'=>'invalid','compound-site'=>'invalid','compound-mode'=>'invalid','source-key'=>'invalid','request-object'=>'invalid','stale'=>'conflict','uncertain'=>'write_uncertain','close-error'=>'write_uncertain','terminal-fault'=>'write_uncertain');trusted_error($result,'source_policy_'.($codes[$case]??'unavailable'));}
	$early=array('capability','nonce','site','revision','compound-nonce','compound-site','compound-mode','source-key','request-object');
	if(in_array($case,$early,true)){A::assertSame(0,$GLOBALS['request_record_reads']);A::assertSame(0,$GLOBALS['boot_counts']['allocate']);A::assertSame(0,$store->reads);}
	if(in_array($case,array('record','record-throw','native-policy','original-policy','source'),true)){A::assertSame(0,$GLOBALS['boot_counts']['allocate']);A::assertSame(0,$store->reads);}
	if(isset($GLOBALS['boot_connection'])){
		$receiver=$GLOBALS['boot_connection'];A::assertFalse($receiver->ready);A::assertNull(boot_get($receiver,'request_root'));A::assertNull(boot_get($receiver,'bootstrap_frame'));A::assertTrue(boot_get($receiver,'request_frame_attempted'));A::assertSame('',boot_get($receiver,'dbpassword'));A::assertSame(1,$GLOBALS['boot_counts']['close']);A::assertSame(7,$GLOBALS['boot_counts']['commands']);
	}
	if(isset($GLOBALS['request_observed_root'])){$root=$GLOBALS['request_observed_root'];A::assertSame('closed',boot_get($root,'request_phase'));A::assertSame(array(),boot_get($root,'request_peers'));A::assertNull(boot_get($root,'request_policy'));A::assertFalse($root->request_claim('connection'));trusted_error($root->view(7,'wpvivid'),'source_policy_unavailable');}
	if(in_array($case,array_merge($early,array('record','record-throw')),true))A::assertFalse(class_exists(Alynt_Drime_Backups_Dashboard_Source_Policy_Lazy_Provider::class,false),'Guard denial must not load the provider.');
	require_once ABSPATH.'includes/class-source-policy-lazy-provider.php'; // Definition-only refusal checks after guard-order observations.
	foreach(array('No_Reconnect_Connection','Query_Context','Trusted_Context','Native_Adapter','Lazy_Provider')as$name){$peer='Alynt_Drime_Backups_Dashboard_Source_Policy_'.$name;$args=array(null);if(in_array($name,array('Query_Context','Trusted_Context','Lazy_Provider'),true))$args=array(null,null,null);elseif('Native_Adapter'===$name)$args=array(null,null);A::assertNull(call_user_func_array(array($peer,'from_request'),$args));$args[0]=new stdClass();A::assertNull(call_user_func_array(array($peer,'from_request'),$args));trusted_error($peer::create(),'source_policy_unavailable');}
	$refused=false;try{unserialize('O:'.strlen($class).':"'.$class.'":0:{}');}catch(Throwable $error){$refused=true;}A::assertTrue($refused,'Crafted root restoration refuses.');
	$running=new ReflectionProperty($class,'request_running');if(PHP_VERSION_ID<80100)$running->setAccessible(true);A::assertFalse($running->getValue(),'Terminal static guard released.');
}
}
