<?php
/** Finite isolated IO cases; no WordPress, native extension, socket or database. */
function policy_transport_scenarios() {
	return array('transport-tcp','transport-off','transport-ipv6','transport-socket','transport-old-frame','transport-parser','transport-parser-source','transport-mixed','transport-trailing','transport-report-invalid','transport-report-string','transport-report-late','transport-helper','transport-helper-session','transport-negotiation','transport-server','transport-socket-strict','transport-socket-report-string','transport-socket-generic','transport-socket-suffix','transport-socket-trailing','transport-socket-patch','transport-socket-type','transport-socket-core');
}
if(defined('ADB_POLICY_ISOLATED')) {
function transport_configure($case,$connection) {
	$frame=boot_get($connection,'bootstrap_frame');$binding=boot_get($connection,'bootstrap_binding');
	$frame['transport']='tcp_loopback';$frame['socket']=null;$frame['db_host']='127.0.0.1:10004';
	$binding['parser_api_file']=__DIR__.'/wordpress-shims-sanitization.php';
	$GLOBALS['boot_report_mode']='transport-off'===$case?0:3;
	$GLOBALS['boot_parse_calls']=0;$GLOBALS['boot_report_reads']=0;
	$GLOBALS['boot_expected_endpoint']=array('127.0.0.1','owned','owned-private-password','isolated',10004,null,0);
	if('transport-ipv6'===$case){$frame['host']='::1';$frame['db_host']='[::1]:10004';$GLOBALS['boot_expected_endpoint'][0]='[::1]';}
	if(0===strpos($case,'transport-socket')){
		$frame['core']='7.1.4';$frame['transport']='unix_socket';$frame['host']='localhost';$frame['port']=null;$frame['socket']='/tmp/owned.sock';$frame['db_host']='localhost:/tmp/owned.sock';
		$GLOBALS['boot_report_mode']='transport-socket-strict'===$case?3:0;$GLOBALS['boot_server']='10.11.18-MariaDB-ubu2204';
		$GLOBALS['boot_expected_endpoint']=array('localhost','owned','owned-private-password','isolated',null,'/tmp/owned.sock',0);
		$bad_servers=array('transport-socket-generic'=>'10.11.18-MariaDB','transport-socket-suffix'=>'10.11.18-MariaDB-ubu2404','transport-socket-trailing'=>"10.11.18-MariaDB-ubu2204\0",'transport-socket-patch'=>'10.11.19-MariaDB-ubu2204','transport-socket-type'=>new stdClass());
		if(array_key_exists($case,$bad_servers))$GLOBALS['boot_server']=$bad_servers[$case];
		if('transport-socket-report-string'===$case)$GLOBALS['boot_report_mode']='0';
		if('transport-socket-core'===$case)$frame['core']='7.1.3';
	}
	if('transport-old-frame'===$case)foreach(array('transport','socket','db_host') as $key)unset($frame[$key]);
	if('transport-parser'===$case)$GLOBALS['boot_parser_wrong']=true;
	if('transport-parser-source'===$case)$binding['parser_api_file']=__FILE__;
	if('transport-mixed'===$case)$frame['socket']='/tmp/owned.sock';
	if('transport-trailing'===$case)$frame['db_host'].=':suffix';
	if('transport-report-invalid'===$case)$GLOBALS['boot_report_mode']=4;
	if('transport-report-string'===$case)$GLOBALS['boot_report_mode']='3';
	if('transport-report-late'===$case)$GLOBALS['boot_after']['identity']=static function(){$GLOBALS['boot_report_mode']=0;};
	if('transport-helper'===$case)$GLOBALS['boot_helper_wrong']=true;
	if('transport-helper-session'===$case){$GLOBALS['boot_helper_wrong']=true;$GLOBALS['boot_rewrite']['session']=static function($rows){foreach(array('client_charset','connection_charset','result_charset') as $key)$rows[0][$key]='utf8';return $rows;};}
	if('transport-negotiation'===$case)$GLOBALS['boot_rewrite']['session']=static function($rows){$rows[0]['client_charset']='utf8';return $rows;};
	if('transport-server'===$case)$GLOBALS['boot_server']='10.6.23-MariaDB-extra';
	boot_set($connection,'bootstrap_frame',$frame);boot_set($connection,'bootstrap_binding',$binding);
}
function transport_verify($case,$connection,$result) {
	$success=in_array($case,array('transport-tcp','transport-off','transport-ipv6'),true)||('transport-socket'===$case&&'Linux'===PHP_OS_FAMILY);
	PHPUnit\Framework\Assert::assertSame($success,true===$result,'Positive transcripts must reach the actual dormant initializer.');
	PHPUnit\Framework\Assert::assertSame($success,$connection->ready);
	$early=in_array($case,array('transport-old-frame','transport-parser','transport-parser-source','transport-mixed','transport-trailing','transport-report-invalid','transport-report-string','transport-socket-strict','transport-socket-report-string','transport-socket-core'),true)||(0===strpos($case,'transport-socket')&&'Linux'!==PHP_OS_FAMILY);
	if($early){PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_counts']['object']);PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_counts']['allocate']);}
	if(in_array($case,array('transport-old-frame','transport-parser-source','transport-mixed','transport-trailing'),true))PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_parse_calls']);
	if('transport-parser-source'===$case)PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_report_reads'],'Unknown helper source must not execute the shared observer.');
	if('transport-parser'===$case)PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_parse_calls']);
	if('transport-report-late'===$case)PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_counts']['commands'],'No command after changed shared context.');
	if('transport-socket-core'===$case)PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_parse_calls']);
	$wrong_server=in_array($case,array('transport-server','transport-socket-generic','transport-socket-suffix','transport-socket-trailing','transport-socket-patch','transport-socket-type'),true);
	if($wrong_server){
		PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_counts']['commands'],'Wrong build refuses before identity/SET/schema commands.');
		PHPUnit\Framework\Assert::assertSame($early?0:1,$GLOBALS['boot_counts']['allocate']);PHPUnit\Framework\Assert::assertSame($early?0:1,$GLOBALS['boot_counts']['connect']);
	}
	if($success){
		PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_counts']['allocate']);PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_counts']['connect']);PHPUnit\Framework\Assert::assertSame(7,$GLOBALS['boot_counts']['commands']);
		PHPUnit\Framework\Assert::assertSame(1,$GLOBALS['boot_parse_calls']);PHPUnit\Framework\Assert::assertSame(DB_CHARSET,'utf8');PHPUnit\Framework\Assert::assertSame('',$GLOBALS['boot_configured_collate']);
		PHPUnit\Framework\Assert::assertSame('utf8mb4',$connection->charset);PHPUnit\Framework\Assert::assertSame('utf8mb4_unicode_520_ci',$connection->collate);
		PHPUnit\Framework\Assert::assertSame($GLOBALS['boot_expected_endpoint'],$GLOBALS['boot_actual_endpoint']);
		PHPUnit\Framework\Assert::assertSame('transport-socket'===$case?'localhost:/tmp/owned.sock':('transport-ipv6'===$case?'[::1]:10004':'127.0.0.1:10004'),$GLOBALS['boot_constructor_args'][3]);
		PHPUnit\Framework\Assert::assertIsArray($connection->admit_profile());PHPUnit\Framework\Assert::assertInstanceOf(WP_Error::class,boot_invoke($connection,'bootstrap'));
		if('transport-socket'===$case){
			$facts=boot_get($connection,'qualification');PHPUnit\Framework\Assert::assertSame('10.11.18-MariaDB-ubu2204',$facts['server']);
			$facts['server']='10.11.18-MariaDB';boot_set($connection,'qualification',$facts);
			$refusal=$connection->admit_profile();PHPUnit\Framework\Assert::assertInstanceOf(WP_Error::class,$refusal);PHPUnit\Framework\Assert::assertSame('source_policy_unavailable',$refusal->get_error_code());
			PHPUnit\Framework\Assert::assertFalse($connection->query('SELECT never-dispatched'));
			PHPUnit\Framework\Assert::assertSame(7,$GLOBALS['boot_counts']['commands'],'Altered facts cannot dispatch after a positive initializer.');
		}
	}else{PHPUnit\Framework\Assert::assertInstanceOf(WP_Error::class,$result);PHPUnit\Framework\Assert::assertSame('source_policy_unavailable',$result->get_error_code());}
	PHPUnit\Framework\Assert::assertSame(0,$GLOBALS['boot_counts']['unexpected_connect']);
	PHPUnit\Framework\Assert::assertSame(!$early,$connection->close(),'Preflight never began a callback lease; do not invent a cleanup receipt.');PHPUnit\Framework\Assert::assertSame(!$early,$connection->close());
	PHPUnit\Framework\Assert::assertSame($early?0:1,$GLOBALS['boot_counts']['close']);
	PHPUnit\Framework\Assert::assertSame($GLOBALS['boot_counts']['results'],$GLOBALS['boot_counts']['freed']);
	foreach(array('bootstrap_frame','bootstrap_binding','qualification','report_mode','report_source') as $key)PHPUnit\Framework\Assert::assertNull(boot_get($connection,$key));
	PHPUnit\Framework\Assert::assertFalse(has_filter('query',array($connection,'remove_placeholder_escape')));
	PHPUnit\Framework\Assert::assertSame('',$connection->dbpassword);
}
}
