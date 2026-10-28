<?php
/** Synthetic failure setup only, never native sessions or authenticated settings. */
if(defined('ADB_POLICY_ISOLATED')) {
function boot_configure($case,$connection,$context){
	$frame=boot_get($connection,'bootstrap_frame'); $binding=boot_get($connection,'bootstrap_binding');
	$bad=array('host'=>'example.invalid','port'=>'10004','table'=>'wp_options;DROP','database'=>'wrong name','user'=>str_repeat('a',65),'password'=>array(),'core'=>'unknown','charset'=>'latin1','collate'=>'unknown');
	foreach($bad as $key=>$value)if('bad-'.$key===$case)$frame[$key]=$value;
	if('positive-current'===$case){$frame['core']='7.1.3'; $GLOBALS['boot_store']->row['autoload']='off';}
	if('default'===$case)$frame=null;
	if('driver-selection'===$case)$GLOBALS['boot_legacy']=true;
	if('bad-core-source'===$case)$binding['core_file']=__FILE__;
	if('bad-driver-source'===$case)$binding['driver_file']=__FILE__;
	if('bad-cache-source'===$case)$binding['cache_file']=__FILE__;
	if('external-cache'===$case)$GLOBALS['policy_ext_cache']=true;
	if(in_array($case,array('positive-ambient','allocate-alias'),true)){
		$ambient=(new ReflectionClass('wpdb'))->newInstanceWithoutConstructor(); $ambient->dbh=new mysqli(); $ambient->dbh->connected=true; $GLOBALS['wpdb']=$ambient; $ambient->placeholder_escape(); boot_set($context,'ambient',$ambient);
	}
	$GLOBALS['boot_observer_calls']=0; $GLOBALS['boot_autoload_calls']=0;
	if(in_array($case,array('positive-observer','all','incompatible_sql_modes','unknown-observer','observer-source','observer-autoload'),true)){
		$hook=in_array($case,array('all','incompatible_sql_modes'),true)?$case:'sanitize_option_'.Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
		$callback=static function($value){++$GLOBALS['boot_observer_calls'];return $value;};
		if('observer-autoload'===$case){$callback=array('UnloadedBootstrapTripwire','run'); spl_autoload_register(static function($name){unset($name);++$GLOBALS['boot_autoload_calls'];throw new RuntimeException('No callback autoload');});}
		policy_isolated_hook($hook,$callback,1);
		if('unknown-observer'!==$case)$binding['observers'][$hook]=$GLOBALS['wp_filter'][$hook]->callbacks;
		$binding['observer_files'][__FILE__]='observer-source'===$case?'wrong':hash_file('sha256',__FILE__);
	}
	$GLOBALS['boot_fault']=$case;
	if('allocate-alias'===$case)$GLOBALS['boot_alias_handle']=$GLOBALS['wpdb']->dbh;
	if(in_array($case,array('late-hook','late-global','late-handle','late-budget','late-clock'),true)){
		$GLOBALS['boot_after']['identity']=static function()use($case,$connection){
			if('late-hook'===$case)policy_isolated_hook('all',static function($value){++$GLOBALS['boot_observer_calls'];return $value;});
			if('late-global'===$case)$GLOBALS['wpdb']=(new ReflectionClass('wpdb'))->newInstanceWithoutConstructor();
			if('late-handle'===$case){$GLOBALS['boot_foreign_handle']=new mysqli();$connection->dbh=$GLOBALS['boot_foreign_handle'];}
			if('late-budget'===$case)boot_set($connection,'bootstrap_credits',17);
			if('late-clock'===$case)boot_set($connection,'bootstrap_started',hrtime(true)-21000000000);
		};
	}
	if('during-prepare'===$case)$GLOBALS['boot_store']->after_prepare=static function(){policy_isolated_hook('all',static function($value){++$GLOBALS['boot_observer_calls'];return $value;});};
	$GLOBALS['boot_reentered']=false; $GLOBALS['boot_nested_refused']=false;
	if('reentry'===$case)$GLOBALS['boot_after']['identity']=static function()use($connection){$GLOBALS['boot_reentered']=is_wp_error(boot_invoke($connection,'bootstrap'));};
	if('object-reentry'===$case)$GLOBALS['boot_after']['object']=static function()use($connection){$GLOBALS['boot_reentered']=is_wp_error(boot_invoke($connection,'bootstrap'));};
	if('out-of-order'===$case)$GLOBALS['boot_after']['limits']=static function()use($connection){try{boot_invoke($connection,'bootstrap_probe','indexes');}catch(RuntimeException $error){$GLOBALS['boot_nested_refused']=true;}};
	if(0===strpos($case,'readback-')){$field=substr($case,9);$GLOBALS['boot_rewrite']['session']=static function($rows)use($field){$rows[0][$field]='wrong';return $rows;};}
	if(0===strpos($case,'column-')){$field=substr($case,7);$GLOBALS['boot_rewrite']['columns']=static function($rows)use($field){$rows[0][$field]='wrong';return $rows;};}
	if(0===strpos($case,'index-')){$field=substr($case,6);$GLOBALS['boot_rewrite']['indexes']=static function($rows)use($field){$rows[0][$field]='wrong';return $rows;};}
	boot_set($connection,'bootstrap_frame',$frame); boot_set($connection,'bootstrap_binding',$binding);
}
function boot_verify($case,$connection,$result){
	$success=in_array($case,array('positive','positive-current','positive-observer','positive-ambient','reentry','object-reentry','out-of-order','close-false','close-throw','close-warning','remove-false','remove-throw','close-and-remove'),true);
	PHPUnit\Framework\Assert::assertSame($success,true===$result,'Expected full transcript eligibility, not a default-refusal-only test.');
	PHPUnit\Framework\Assert::assertSame($success,$connection->ready);
	PHPUnit\Framework\Assert::assertNull(boot_get($connection,'bootstrap_frame')); PHPUnit\Framework\Assert::assertNull(boot_get($connection,'bootstrap_binding'));
	PHPUnit\Framework\Assert::assertSame('',$connection->dbpassword);
	PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_counts']['unexpected_connect']);
	PHPUnit\Framework\Assert::assertLessThanOrEqual(1,$GLOBALS['boot_counts']['allocate']); PHPUnit\Framework\Assert::assertLessThanOrEqual(1,$GLOBALS['boot_counts']['connect']);
	PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_autoload_calls']);
	if(!$success){PHPUnit\Framework\Assert::assertInstanceOf(WP_Error::class,$result);PHPUnit\Framework\Assert::assertSame('source_policy_unavailable',$result->get_error_code());PHPUnit\Framework\Assert::assertStringNotContainsString('owned-private',serialize($result));}
	if(in_array($case,array('default','bad-host','bad-port','bad-table','bad-database','bad-user','bad-password','bad-core','bad-charset','bad-collate','bad-core-source','bad-driver-source','bad-cache-source','external-cache','multisite','setup-mode','legacy','driver-selection','dropin','all','incompatible_sql_modes','unknown-observer','observer-source','observer-autoload','bad-client'),true))PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_counts']['allocate']);
	if(in_array($case,array('allocate-false','allocate-throw','allocate-alias','option-false','option-throw','option-warning'),true))PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_counts']['connect']);
	if(in_array($case,array('connect-false','connect-throw','connect-warning','connect-error','bad-server'),true))PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_counts']['commands']);
	if('during-prepare'===$case)PHPUnit\Framework\Assert::assertSame(4,$GLOBALS['boot_counts']['commands']);
	if(in_array($case,array('reentry','object-reentry'),true))PHPUnit\Framework\Assert::assertTrue($GLOBALS['boot_reentered']);
	if('out-of-order'===$case)PHPUnit\Framework\Assert::assertTrue($GLOBALS['boot_nested_refused']);
	if($success){
		PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_counts']['object']); PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_counts']['allocate']); PHPUnit\Framework\Assert::assertSame(3,$GLOBALS['boot_counts']['options']); PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_counts']['connect']); PHPUnit\Framework\Assert::assertSame(7,$GLOBALS['boot_counts']['commands']);
		foreach(array('init','charset','mode-helper') as $step)PHPUnit\Framework\Assert::assertSame(1,count(array_filter($GLOBALS['boot_events'],static function($event)use($step){return $event===$step;})));
		PHPUnit\Framework\Assert::assertIsArray($connection->admit_profile());
		if('positive-observer'===$case){$owner=(new ReflectionClass('Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter'))->newInstanceWithoutConstructor();boot_set($owner,'connection',$connection);$observed=Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map::decode($GLOBALS['boot_store']->row,hash('sha256','isolated:wp_options'),array('yes','no'));PHPUnit\Framework\Assert::assertSame('saved',$owner->save_source_mode_if_current($observed['model']->revision(),7,'wpvivid','external_optional'));PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_observer_calls']);}
		PHPUnit\Framework\Assert::assertTrue(is_wp_error(boot_invoke($connection,'bootstrap')));
	}
	if('positive-observer'!==$case)PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_observer_calls']);
	if(in_array($case,array('remove-false','remove-throw','close-and-remove'),true)){$GLOBALS['policy_remove_retain']='remove-throw'!==$case;$GLOBALS['policy_remove_throw']='remove-throw'===$case;}
	if('close-and-remove'===$case)$GLOBALS['boot_fault']='close-false';
	$clean=!in_array($case,array('default','driver-selection','bad-host','bad-port','bad-table','bad-database','bad-user','bad-password','bad-core','bad-charset','bad-collate','bad-core-source','bad-driver-source','bad-cache-source','external-cache','multisite','setup-mode','legacy','dropin','all','incompatible_sql_modes','unknown-observer','observer-source','observer-autoload','bad-client','allocate-false','allocate-throw','allocate-alias','late-handle','close-false','close-throw','close-warning','remove-false','remove-throw','close-and-remove'),true);
	// Preflight failures may be clean never-opened; do not force a fake unknown result.
	$closed=$connection->close(); if($success||$GLOBALS['boot_counts']['allocate']>0)PHPUnit\Framework\Assert::assertSame($clean,$closed,'Truthful once-only native/callback cleanup.');
	PHPUnit\Framework\Assert::assertSame($closed,$connection->close());
	PHPUnit\Framework\Assert::assertLessThanOrEqual(1,$GLOBALS['boot_counts']['close']);
	if(isset($GLOBALS['boot_handle'])&&!in_array($case,array('allocate-alias'),true))PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_counts']['close'],'Every owned partial/active allocation gets one release attempt.');
	if(!in_array($case,array('remove-false','remove-throw','close-and-remove'),true))PHPUnit\Framework\Assert::assertFalse(has_filter('query',array($connection,'remove_placeholder_escape')));
	else PHPUnit\Framework\Assert::assertSame(0,has_filter('query',array($connection,'remove_placeholder_escape')));
	PHPUnit\Framework\Assert::assertSame($GLOBALS['boot_counts']['results'],$GLOBALS['boot_counts']['freed']);
	if(isset($GLOBALS['boot_foreign_handle']))PHPUnit\Framework\Assert::assertFalse($GLOBALS['boot_foreign_handle']->closed);
	if(isset($GLOBALS['wpdb'])&&in_array($case,array('positive-ambient','allocate-alias'),true)){PHPUnit\Framework\Assert::assertFalse($GLOBALS['wpdb']->dbh->closed);PHPUnit\Framework\Assert::assertSame(0,has_filter('query',array($GLOBALS['wpdb'],'remove_placeholder_escape')));}
}
}
