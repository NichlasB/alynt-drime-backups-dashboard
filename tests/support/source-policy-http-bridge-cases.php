<?php
/** Isolated API-image bridge cases, never stock auth/HTTP or native proof. */
use PHPUnit\Framework\Assert as A;
if(defined('ADB_POLICY_ISOLATED')) {
function wp_create_nonce($action){$GLOBALS['http_nonce_actions'][]=$action;return 'adb_http_wrong_action'===$action?'wrong-action':'valid';}
function http_bridge_case($case,$request,$store){
	$GLOBALS['http_nonce_actions']=array();$challenge=str_repeat('9',32);
	$meta=array('host'=>'127.0.0.1:18777','method'=>'POST','target'=>'/_adb-owned-source-policy','challenge'=>$challenge,'header_bytes'=>120,'body_bytes'=>10,'content_length'=>'10','transfer_encoding'=>null,'content_type'=>'application/x-www-form-urlencoded');
	if(in_array($case,array('view','wrong-action','view-denied'),true)){$meta['method']='GET';$meta['body_bytes']=0;$meta['content_length']=null;$meta['content_type']=null;}
	if('wrong-action'===$case)$meta['target'].='?control=wrong-action';
	if(in_array($case,array('denied','view-denied'),true))$GLOBALS['policy_capable']=false;
	if('private-challenge'===$case)$meta['challenge']=str_repeat('8',32);
	if('unknown-route'===$case)$meta['target']='/wp-config.php';
	if('framing'===$case)$meta['content_length']='010';
	if('compound-nonce'===$case)$request['_wpnonce']=array('valid');
	if('nonce'===$case)$request['_wpnonce']='wrong-action';
	if('compound-site'===$case)$request['dashboard_site_id']=array(7);
	if('revision'===$case)$request['source_policy_revision']='bad';
	if('stale'===$case)$request['source_policy_revision']=str_repeat('f',64);
	if('noop'===$case)$request['source_mode']='required';
	if('unavailable'===$case)$GLOBALS['request_record']=array('id'=>8);
	$original=$request;$result=adbh_bridge($meta,$request,$challenge);
	A::assertSame($original,$request,'No raw field coercion or in-place rewrite.');
	A::assertSame(array('status','result','mode','revision','nonce'),array_keys($result));
	if(in_array($case,array('view','wrong-action'),true)){
		A::assertSame(200,$result['status']);A::assertSame('view',$result['result']);A::assertSame('required',$result['mode']);A::assertSame($request['source_policy_revision'],$result['revision']);
		A::assertSame('wrong-action'===$case?'wrong-action':'valid',$result['nonce']);A::assertSame(array('wrong-action'===$case?'adb_http_wrong_action':'alynt_drime_backups_dashboard_update_source_policy'),$GLOBALS['http_nonce_actions']);
		A::assertSame(1,$store->reads);A::assertSame(0,$store->mutations);
	}elseif(in_array($case,array('save','noop'),true)){
		A::assertSame(200,$result['status']);A::assertSame('save'===$case?'saved':'unchanged',$result['result']);A::assertSame('save'===$case?1:0,$store->mutations);A::assertSame(1,$store->reads);
	}else{
		$code=in_array($case,array('denied','view-denied'),true)?'forbidden':('stale'===$case?'conflict':('unavailable'===$case?'unavailable':'invalid'));
		A::assertSame('source_policy_'.$code,$result['result']);A::assertSame(array('forbidden'=>403,'conflict'=>409,'unavailable'=>503,'invalid'=>400)[$code],$result['status']);A::assertNull($result['nonce']);A::assertNull($result['revision']);
		if('stale'!==$case){A::assertSame(0,$store->reads);A::assertSame(0,$GLOBALS['boot_counts']['allocate']);}
		A::assertSame(0,$store->mutations);
	}
	if(!in_array($case,array('view','wrong-action'),true))A::assertSame(array(),$GLOBALS['http_nonce_actions'],'Never mint a nonce on a denial or submit.');
	if(in_array($case,array('private-challenge','unknown-route','framing'),true))A::assertSame(array(),$GLOBALS['policy_trace'],'Framing rejects before any auth API.');
	if(in_array($case,array('denied','view-denied'),true))A::assertSame(array('capability'),$GLOBALS['policy_trace']);
	if(in_array($case,array('nonce','compound-nonce','compound-site','revision'),true))A::assertSame('capability',$GLOBALS['policy_trace'][0]);
	A::assertStringNotContainsString('owned-private',serialize($result));
	foreach($result as$value)A::assertFalse(is_object($value),'No write-capable handle/root escapes.');
}
}
