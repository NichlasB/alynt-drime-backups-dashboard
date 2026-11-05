<?php
/** Pure expectation DATA, never authority or server/version attestation. */
use PHPUnit\Framework\Assert as A;
if(defined('ADB_POLICY_ISOLATED')) {
function production_data($context,$receiver) {
	$class=Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Expectation::class;$frame=boot_get($receiver,'bootstrap_frame');$runtime=boot_get($context,'production_runtime');
	$expected=$class::select($frame,$runtime);A::assertSame(array('core'=>'6.0','server'=>'5.5.5-10.6.23-MariaDB','collation'=>'utf8mb4_unicode_ci','database'=>'isolated','table'=>'wp_options'),$expected);
	$projection=$frame;unset($projection['user'],$projection['password']);A::assertTrue($class::matches($projection,$runtime,$expected));A::assertFalse($class::matches($frame,$runtime,$expected));
	foreach(array_keys($runtime)as$key){foreach(array(null,true,1,'wrong',array())as$value){$bad=$runtime;$bad[$key]=$value;A::assertFalse($class::select($frame,$bad),$key);}$bad=$runtime;unset($bad[$key]);A::assertFalse($class::select($frame,$bad));}
	$bad=$runtime;$bad['extra']=true;A::assertFalse($class::select($frame,$bad));
	foreach(array('core'=>'7.1.3','host'=>'localhost','port'=>'10004','charset'=>'utf8mb4','collate'=>'utf8mb4_unicode_520_ci','transport'=>'unix_socket','socket'=>'/tmp/owned.sock','db_host'=>'127.0.0.1:10005','database'=>"bad\0",'table'=>'wp_options;','user'=>'bad user','password'=>"bad\0")as$key=>$value){$bad=$frame;$bad[$key]=$value;A::assertFalse($class::select($bad,$runtime),$key);}
	foreach(array_keys($frame)as$key){$bad=$frame;unset($bad[$key]);A::assertFalse($class::select($bad,$runtime),$key);}
	foreach(array_keys($projection)as$key){$bad=$projection;unset($bad[$key]);A::assertFalse($class::matches($bad,$runtime,$expected),$key);}
	foreach(array_keys($expected)as$key){$bad=$expected;$bad[$key]='other';A::assertFalse($class::matches($projection,$runtime,$bad),$key);}
	foreach(array(1,65535)as$port){$other=$frame;$other['port']=$port;$other['db_host']='127.0.0.1:'.$port;A::assertSame($expected,$class::select($other,$runtime));}
	foreach(array(0,65536)as$port){$bad=$frame;$bad['port']=$port;$bad['db_host']='127.0.0.1:'.$port;A::assertFalse($class::select($bad,$runtime));}
	A::assertSame('10.6.23-MariaDB',Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::server('6.0'),'NULL/default row remains strict.');
	A::assertSame(0,$GLOBALS['boot_counts']['allocate']);A::assertSame(0,$GLOBALS['boot_counts']['commands']);A::assertNull($receiver->profile());
}
}
