<?php
/** Synthetic claims only; no installed paths, source evidence or runtime grant. */
function minimum_launch_fixture() {
	$run=str_repeat('a',32);$root='C:/Temp/alynt-minimum-'.$run;
	return array('run'=>$run,'root'=>$root,'platform'=>array('root'=>'C:/Windows','system'=>'C:/Windows/System32','build'=>'10.0.12345','provenance'=>'reviewed-platform-profile'),
		'artifacts'=>array('php_sha256'=>'14ae3250d4447c8ccfc4c45a70d90adfbcd61e728d85f0be56a7ddf8f9c8aace','core_tree'=>'a3bd7a4074a4c95c8af3b969346726273d68e167','source_pins'=>12),
		'files'=>array('php.exe'=>'4b06073dd8230421ba701a677dd8b1aa23ed55706663d3fdf996702b557f83b2','php7.dll'=>'51a5c862da428391c4864d862ca4fffbc608362cd66ada757b84d991a65bd035','ext/php_mysqli.dll'=>'1c69a4336a2a42fef76c0d840543b8cd0a1aae4eb6f9111dfb5f2e6ed6a46891','worker.php'=>str_repeat('b',64),'producer.php'=>str_repeat('c',64)),
		'cwd_entries'=>array(),'environment'=>array('SystemRoot'=>'C:/Windows','WINDIR'=>'C:/Windows','PATH'=>'C:/Windows/System32','TEMP'=>$root.'/tmp','TMP'=>$root.'/tmp'),
		'dependencies'=>array('advapi32.dll'=>'platform-profile','api-ms-win-crt-convert-l1-1-0.dll'=>'platform-profile','api-ms-win-crt-environment-l1-1-0.dll'=>'platform-profile','api-ms-win-crt-filesystem-l1-1-0.dll'=>'platform-profile','api-ms-win-crt-heap-l1-1-0.dll'=>'platform-profile','api-ms-win-crt-locale-l1-1-0.dll'=>'platform-profile','api-ms-win-crt-math-l1-1-0.dll'=>'platform-profile','api-ms-win-crt-runtime-l1-1-0.dll'=>'platform-profile','api-ms-win-crt-stdio-l1-1-0.dll'=>'platform-profile','api-ms-win-crt-string-l1-1-0.dll'=>'platform-profile','api-ms-win-crt-time-l1-1-0.dll'=>'platform-profile','api-ms-win-crt-utility-l1-1-0.dll'=>'platform-profile','bcrypt.dll'=>'platform-profile','crypt32.dll'=>'platform-profile','dnsapi.dll'=>'platform-profile','imagehlp.dll'=>'platform-profile','iphlpapi.dll'=>'platform-profile','kernel32.dll'=>'platform-profile','ole32.dll'=>'platform-profile','shell32.dll'=>'platform-profile','vcruntime140.dll'=>'installed-microsoft-runtime','ws2_32.dll'=>'platform-profile'));
}
function minimum_modules_fixture($stage,$layout=null) {
	$layout=$layout??minimum_launch_fixture();$rows=array();
	foreach(array('php.exe','php7.dll','ext/php_mysqli.dll') as $role){if('base'===$stage&&'ext/php_mysqli.dll'===$role)continue;$rows[]=array('name'=>basename($role),'path'=>$layout['root'].'/runtime/'.$role,'sha256'=>$layout['files'][$role],'size'=>1024,'origin'=>'publisher');}
	foreach(array('kernel32.dll'=>'platform-profile','vcruntime140.dll'=>'installed-microsoft-runtime') as $name=>$origin)$rows[]=array('name'=>$name,'path'=>$layout['platform']['system'].'/'.$name,'sha256'=>hash('sha256','synthetic-'.$name),'size'=>2048,'origin'=>$origin);
	return $rows;
}
function minimum_preflight_bindings() {
	return array('base'=>array('pid'=>101,'start'=>'123456789'),'mysqli'=>array('pid'=>102,'start'=>'123456790'));
}
function minimum_preflight_expected($layout=null) {
	$layout=$layout??minimum_launch_fixture();$result=array();foreach(array('base','mysqli') as $stage){$rows=minimum_modules_fixture($stage,$layout);$data=Alynt_Drime_Backups_Dashboard_Minimum_Launch_Data::module_data($stage,$layout,$rows,$rows);$result[$stage]=array('sha256'=>$data['sha256'],'count'=>$data['count']);}return $result;
}
function minimum_preflight_lines() {
	$run=str_repeat('a',32);$image=str_repeat('d',64);$source=str_repeat('e',64);$base=str_repeat('f',64);$bindings=minimum_preflight_bindings();$modules=minimum_preflight_expected();$lines=array();
	foreach(array('base','mysqli') as $stage){$pid=$bindings[$stage]['pid'];$events=array(
		array('worker','start',array($bindings[$stage]['start'],'4b06073dd8230421ba701a677dd8b1aa23ed55706663d3fdf996702b557f83b2')),
		array('worker','profile',Alynt_Drime_Backups_Dashboard_Minimum_Preflight_Transcript::profile_fields($stage,$source,$base)),
		array('worker','held',array('await-observer')),array('controller','modules',array($modules[$stage]['sha256'],(string)$modules[$stage]['count'])),
		array('stdin','ack',array()),array('worker','released',array('acknowledged')),array('controller','exit',array('0','drained')),
		array('controller','cleanup',array('process-absent','unchanged','base'===$stage?'retained-owned':'artifacts-absent')));
		foreach($events as $index=>$event){list($channel,$kind,$fields)=$event;$line='ack'===$kind?Alynt_Drime_Backups_Dashboard_Minimum_Preflight_Transcript::ack_frame($run,$image,$stage,$pid):Alynt_Drime_Backups_Dashboard_Minimum_Preflight_Transcript::frame($run,$image,$stage,$pid,$index+1,$kind,$fields);$lines[]=array($channel,$line);}
	}return $lines;
}
function minimum_preflight_owner() {
	return new Alynt_Drime_Backups_Dashboard_Minimum_Preflight_Transcript(str_repeat('a',32),str_repeat('d',64),minimum_preflight_bindings(),str_repeat('e',64),str_repeat('f',64),minimum_preflight_expected());
}
