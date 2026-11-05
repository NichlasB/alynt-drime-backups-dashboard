<?php
/** Actual private initializer drives readiness; Reflection is only private fixture composition/drift. */
use PHPUnit\Framework\Assert as A;
if(defined('ADB_POLICY_ISOLATED')) {
function production_case($case,$entry,$provider,$context,$adapter,$receiver,$store,$sites,$query,$request) {
	if('capability'===$case)$GLOBALS['policy_capable']=false;
	if('nonce'===$case)$request['_wpnonce']='other-action';
	if('record'===$case)$sites->record_override=array('id'=>8);
	if('stale'===$case)$request['source_policy_revision']=str_repeat('f',64);
	if('unchanged'===$case)$request['source_mode']='required';
	if('source'===$case){$policy=boot_get($context,'policy');$policy['pins']['source-policy-isolated-wpdb.php']=str_repeat('f',64);boot_set($context,'policy',$policy);}
	if('runtime-default'===$case)boot_set($context,'production_runtime',null);
	if(0===strpos($case,'runtime-')&&'runtime-default'!==$case){$runtime=boot_get($context,'production_runtime');$runtime[substr($case,8)]='wrong';boot_set($context,'production_runtime',$runtime);}
	if('client'===$case)$GLOBALS['boot_client']='mysqlnd unknown';
	if('report'===$case)$GLOBALS['boot_report_mode']=3;
	if('mixed-origin'===$case)boot_set($receiver,'minimum_lab_scope',new stdClass());
	if('query-origin'===$case)boot_set($context,'production_query',clone $query);
	if('frame'===$case){$frame=boot_get($receiver,'bootstrap_frame');$frame['password']='other';boot_set($receiver,'bootstrap_frame',$frame);}
	if('binding'===$case){$binding=boot_get($receiver,'bootstrap_binding');$binding['cache_file']='other';boot_set($receiver,'bootstrap_binding',$binding);}
	if('helper'===$case)$GLOBALS['boot_helper_wrong']=true;
	if('schema'===$case)$GLOBALS['boot_rewrite']['table']=static function($rows){$rows[0]['collation']='utf8mb4_unicode_ci';return$rows;};
	$servers=array('unprefixed'=>'10.6.23-MariaDB','double-prefix'=>'5.5.5-5.5.5-10.6.23-MariaDB','newer'=>'5.5.5-10.11.18-MariaDB-ubu2204');if(isset($servers[$case]))$GLOBALS['boot_server']=$servers[$case];
	if(0===strpos($case,'session-')){$key=substr($case,8);$GLOBALS['boot_rewrite']['session']=static function($rows)use($key){$rows[0]['collation']='utf8mb4_unicode_ci';$rows[0][$key]='wrong';return$rows;};}
	if(0===strpos($case,'fault-'))$GLOBALS['boot_fault']=substr($case,6).'-throw';
	if(in_array($case,array('late-query','late-global','late-frame','late-client','late-mode','late-hook'),true))$GLOBALS['boot_after']['identity']=static function()use($case,$receiver,$context,$query){
		if('late-query'===$case)boot_set($receiver,'query_context',clone $query);
		if('late-global'===$case)$GLOBALS['wpdb']=(new ReflectionClass('wpdb'))->newInstanceWithoutConstructor();
		if('late-frame'===$case){$frame=boot_get($receiver,'bootstrap_frame');$frame['table']='other_options';boot_set($receiver,'bootstrap_frame',$frame);}
		if('late-client'===$case)$GLOBALS['boot_client']='other';if('late-mode'===$case)$GLOBALS['boot_report_mode']=3;
		if('late-hook'===$case)policy_isolated_hook('update_option',static function(){A::fail('Unknown hook executed');});
	};
	if('preconnect'===$case||0===strpos($case,'active-')){
		A::assertTrue($context->capture_bootstrap());A::assertFalse($context->capture_bootstrap());A::assertFalse($context->valid($receiver));A::assertFalse($context->activate($receiver,$query));A::assertNull($receiver->profile());A::assertFalse($receiver->ready);A::assertSame(0,$GLOBALS['boot_counts']['allocate']);
		if('preconnect'===$case){A::assertFalse($receiver->query('SELECT never-dispatched'));A::assertTrue($context->bootstrap_matches($receiver,$query,boot_get($receiver,'bootstrap_frame'),boot_get($receiver,'bootstrap_binding')));A::assertFalse($context->bootstrap_matches($receiver,clone $query,boot_get($receiver,'bootstrap_frame'),boot_get($receiver,'bootstrap_binding')));A::assertFalse($context->finish());return;}
		A::assertTrue($adapter->initialize_context($context));production_active($context,$receiver,$query);
		if('active-frame'===$case)A::assertArrayNotHasKey('password',boot_get($context,'frame'));
		else {if('active-client'===$case)$GLOBALS['boot_client']='mysqlnd wrong';if('active-table'===$case)$GLOBALS['wpdb']->options='other_options';if('active-query'===$case)boot_set($receiver,'query_context',clone $query);if('active-close'===$case)A::assertTrue($receiver->close());A::assertFalse($context->valid($receiver));trusted_error($adapter->view_source_mode(7,'wpvivid',$context),'source_policy_unavailable');A::assertSame(0,$store->reads);}
		A::assertTrue($context->finish());A::assertFalse($context->valid($receiver));return;
	}
	if('reentry'===$case)$store->after_read=static function()use($provider,$context,$receiver){trusted_error($provider->view_source_mode(7,'wpvivid'),'source_policy_unavailable');production_active($context,$receiver,boot_get($receiver,'query_context'));};
	if('uncertain'===$case)$store->throw_after_write=true;
	if('close-error'===$case)$GLOBALS['boot_after']['indexes']=static function(){$GLOBALS['boot_fault']='close-false';};
	$result='view'===$case?$entry->view(7,'wpvivid'):$entry->submit($request);
	if(in_array($case,array('save','reentry'),true)){A::assertSame('saved',$result);A::assertSame(1,$store->mutations);A::assertSame(array(9=>array('wpvivid'=>'external_optional'),7=>array('wpvivid'=>'external_optional')),unserialize($store->row['option_value']));}
	elseif('view'===$case){A::assertSame('required',$result['mode']);A::assertSame($request['source_policy_revision'],$result['revision']);A::assertSame(0,$store->mutations);}
	elseif('unchanged'===$case){A::assertSame('unchanged',$result);A::assertSame(0,$store->attempts);}
	else {$codes=array('capability'=>'source_policy_forbidden','nonce'=>'source_policy_invalid','stale'=>'source_policy_conflict','uncertain'=>'source_policy_write_uncertain','close-error'=>'source_policy_write_uncertain');trusted_error($result,$codes[$case]??'source_policy_unavailable');}
	if(in_array($case,array('capability','nonce','record'),true)){A::assertSame('fresh',boot_get($context,'state'));A::assertSame(0,$GLOBALS['boot_parse_calls']);A::assertSame(0,$GLOBALS['boot_counts']['allocate']);A::assertSame(0,$store->reads);A::assertFalse($context->finish());return;}
	foreach(array('frame','receiver','ambient','binding','policy','production_query','production_runtime','production_expected','production_projection')as$key)A::assertNull(boot_get($context,$key),$key);
	A::assertNull(boot_get($receiver,'production_context'));A::assertNull(boot_get($receiver,'qualification'));A::assertFalse($receiver->ready);A::assertNull(boot_get($provider,'owner'));A::assertNull(boot_get($provider,'context'));A::assertNull(boot_get($adapter,'operation_context'));
	$counts=$GLOBALS['boot_counts'];trusted_error($provider->view_source_mode(7,'wpvivid'),'source_policy_unavailable');A::assertSame($counts,$GLOBALS['boot_counts']);A::assertFalse($context->capture_bootstrap());A::assertFalse($receiver->query('SELECT never-dispatched'));
	if(in_array($case,array('save','view','unchanged','stale','reentry','uncertain','close-error'),true)){A::assertSame(8,boot_get($receiver,'bootstrap_position'));A::assertSame(12,boot_get($receiver,'bootstrap_credits'));A::assertSame(7,$GLOBALS['boot_counts']['commands']);A::assertSame(1,$GLOBALS['boot_counts']['connect']);}
	if(in_array($case,array('source','runtime-default','client','report','mixed-origin','query-origin','frame','binding'),true)||0===strpos($case,'runtime-')){A::assertSame(0,$GLOBALS['boot_counts']['allocate']);A::assertSame(0,$store->reads);}
	A::assertSame(isset($GLOBALS['boot_handle'])?1:0,$GLOBALS['boot_counts']['close']);A::assertSame('',boot_get($receiver,'dbpassword'));
}
function production_active($context,$receiver,$query) {
	A::assertTrue($receiver->ready);A::assertTrue($context->valid($receiver));A::assertTrue($receiver->production_completed($context,$query));A::assertSame('production_active',boot_get($context,'state'));
	A::assertSame('5.5.5-10.6.23-MariaDB',$receiver->profile()['server']);A::assertSame('utf8mb4_unicode_ci',$receiver->collate);A::assertSame(8,boot_get($receiver,'bootstrap_position'));A::assertSame(12,boot_get($receiver,'bootstrap_credits'));
	A::assertArrayNotHasKey('password',boot_get($context,'frame'));A::assertArrayNotHasKey('user',boot_get($context,'frame'));A::assertSame('',boot_get($receiver,'dbpassword'));A::assertNull(boot_get($receiver,'bootstrap_frame'));A::assertNull(boot_get($receiver,'bootstrap_binding'));
	A::assertFalse($context->activate($receiver,$query));trusted_error($receiver->bootstrap_production($context),'source_policy_unavailable');A::assertNull(boot_get($receiver,'minimum_lab_scope'));
}
}
