<?php
/** Finite synthetic W/L candidates; no measured runtime, login, SQL or native grant. */
use PHPUnit\Framework\Assert as A;
if(defined('ADB_POLICY_ISOLATED')) {
function production_nonlegacy_data($context,$receiver) {
	$class=Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Expectation::class;
	$frame=boot_get($receiver,'bootstrap_frame');$runtime=boot_get($context,'production_runtime');
	$expected=array('core'=>$frame['core'],'server'=>'7.1.3'===$frame['core']?'10.6.23-MariaDB':'10.11.18-MariaDB-ubu2204','collation'=>'utf8mb4_unicode_520_ci','database'=>'isolated','table'=>'wp_options');
	$projection=$frame;unset($projection['user'],$projection['password']);
	// Pure active identity DATA can model either row on either host. The full settings guard stays host-bound.
	A::assertTrue($class::matches($projection,$runtime,$expected));
	if('7.1.4'!==$frame['core']||'Linux'===PHP_OS_FAMILY)A::assertSame($expected,$class::select($frame,$runtime));
	else A::assertFalse($class::select($frame,$runtime),'A Windows process must not select the Linux socket frame.');
	A::assertFalse($class::matches($frame,$runtime,$expected));
	foreach(array_keys($runtime)as$key){
		foreach(array(null,0,1,'8','0','true',false,array(),new stdClass())as$value){if($value===$runtime[$key])continue;$bad=$runtime;$bad[$key]=$value;A::assertFalse($class::matches($projection,$bad,$expected),$key);}
		$bad=$runtime;unset($bad[$key]);A::assertFalse($class::matches($projection,$bad,$expected),$key);
	}
	$bad=$runtime;$bad['extra']=true;A::assertFalse($class::matches($projection,$bad,$expected));
	foreach(array('platform'=>'7.1.3'===$frame['core']?'Linux':'Windows','arch'=>'7.1.3'===$frame['core']?'x86_64':'AMD64','php'=>'7.4.33','client'=>'mysqlnd 7.4.33','mode'=>3,'word_size'=>4,'nts'=>false)as$key=>$value){$bad=$runtime;$bad[$key]=$value;A::assertFalse($class::matches($projection,$bad,$expected),$key);}
	foreach(array('core'=>'6.0','charset'=>'7.1.3'===$frame['core']?'utf8':'utf8mb4','collate'=>'utf8mb4_unicode_520_ci','host'=>'::1','transport'=>'other','socket'=>'/tmp/other.sock','port'=>'10004','db_host'=>'other','database'=>"bad\0",'table'=>'wp_options;')as$key=>$value){$bad=$projection;$bad[$key]=$value;A::assertFalse($class::matches($bad,$runtime,$expected),$key);}
	foreach(array_keys($projection)as$key){$bad=$projection;unset($bad[$key]);A::assertFalse($class::matches($bad,$runtime,$expected),$key);}
	$bad=$projection;$bad['extra']=true;A::assertFalse($class::matches($bad,$runtime,$expected));
	foreach(array_keys($expected)as$key){$bad=$expected;$bad[$key]='wrong';A::assertFalse($class::matches($projection,$runtime,$bad),$key);}
	foreach(array('5.5.5-'.$expected['server'],'5.5.5-5.5.5-'.$expected['server'],'10.6.23-MariaDB','10.11.18-MariaDB-ubu2204')as$server){if($server===$expected['server'])continue;$bad=$expected;$bad['server']=$server;A::assertFalse($class::matches($projection,$runtime,$bad));}
	if('7.1.3'===$frame['core']){
		foreach(array(1,65535)as$port){$other=$projection;$other['port']=$port;$other['db_host']='127.0.0.1:'.$port;A::assertTrue($class::matches($other,$runtime,$expected));}
		foreach(array(0,65536,null,true,'1')as$port){$bad=$projection;$bad['port']=$port;$bad['db_host']='127.0.0.1:'.(string)$port;A::assertFalse($class::matches($bad,$runtime,$expected));}
	}else{
		foreach(array('relative.sock','/tmp/../owned.sock','/tmp//owned.sock',str_repeat('/a',49))as$socket){$bad=$projection;$bad['socket']=$socket;$bad['db_host']='localhost:'.$socket;A::assertFalse($class::matches($bad,$runtime,$expected));}
	}
	// Cross-source tuples cannot inherit another row, even when its complete runtime is valid.
	$other=$projection;$other['core']='7.1.3'===$frame['core']?'7.1.4':'7.1.3';$wrong=$expected;$wrong['core']=$other['core'];A::assertFalse($class::matches($other,$runtime,$wrong));
	A::assertSame('10.6.23-MariaDB',Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::server('6.0'));
	A::assertSame(0,$GLOBALS['boot_counts']['allocate']);A::assertSame(0,$GLOBALS['boot_counts']['commands']);A::assertSame(0,$GLOBALS['boot_store']->reads);A::assertNull($receiver->profile());
	trusted_error(Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context::create(),'source_policy_unavailable');
}
}
