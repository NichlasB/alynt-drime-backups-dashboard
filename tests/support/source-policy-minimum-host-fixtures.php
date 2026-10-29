<?php
/** Fictional supplied DATA only; never installed metadata or human consent. */
function minimum_host_modules() {
	return array(
		array('name'=>'kernel32.dll','path'=>'C:/Windows/System32/kernel32.dll','sha256'=>str_repeat('e',64),'size'=>1024,'role'=>'windows'),
		array('name'=>'pwsh.exe','path'=>'C:/OwnedTools/PowerShell/pwsh.exe','sha256'=>str_repeat('f',64),'size'=>1024,'role'=>'observer-tool'),
		array('name'=>'system.management.automation.dll','path'=>'C:/OwnedTools/PowerShell/system.management.automation.dll','sha256'=>str_repeat('d',64),'size'=>1024,'role'=>'observer-tool'),
	);
}
function minimum_host_profile() {
	return array('windows_root'=>'C:/Windows','system'=>'C:/Windows/System32','build'=>'10.0.12345','ubr'=>0,'architecture'=>'x64',
		'windows_identity'=>str_repeat('1',64),'windows_files_sha256'=>str_repeat('2',64),'crt_version'=>'14.50.12345.0','crt_sha256'=>str_repeat('3',64),'crt_identity'=>str_repeat('4',64),'crt_installed'=>true,'crt_architecture'=>'x64',
		'tool_root'=>'C:/OwnedTools/PowerShell','tool_version'=>'7.6.5','tool_sha256'=>str_repeat('f',64),'tool_identity'=>str_repeat('5',64),'tool_modules_sha256'=>minimum_host_module_digest(minimum_host_modules()),'tool_architecture'=>'x64');
}
function minimum_host_module_digest($rows) { $map=array();foreach($rows as $row)$map[$row['name']]=implode('|',$row);ksort($map);return hash('sha256',implode("\n",$map)); }
function minimum_host_bindings() {
	return array('worker'=>array('pid'=>101,'start'=>'123456789','object'=>str_repeat('c',32),'image'=>str_repeat('f',64),'source'=>str_repeat('a',64)),
		'observer'=>array('pid'=>102,'start'=>'123456790','object'=>str_repeat('d',32),'image'=>str_repeat('f',64),'source'=>str_repeat('b',64)));
}
function minimum_host_source() { return hash('sha256',str_repeat('a',64)."\n".str_repeat('b',64)); }
function minimum_host_decision($profile=null) {
	$profile=$profile??minimum_host_profile();
	return array('policy'=>'existing-host-operator-trust-v1','scope'=>'minimum-preflight-observer','run'=>str_repeat('a',32),
		'profile_sha256'=>Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission::profile_digest($profile),'source_sha256'=>minimum_host_source(),
		'basis'=>'operator-trust-not-signature','signature_verified'=>false,'offline_provenance_verified'=>false);
}
function minimum_host_owner() {
	return new Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission(str_repeat('a',32),minimum_host_source(),minimum_host_profile(),minimum_host_profile(),minimum_host_decision(),minimum_host_bindings(),minimum_host_modules());
}
function minimum_host_exit() { return array('exit'=>0,'drained'=>true,'absent'=>true); }
function minimum_host_cleanup() { return array('worker_absent'=>true,'observer_absent'=>true,'pipes_drained'=>true,'fixtures_absent'=>true,'unchanged'=>true); }
function minimum_host_sequence($owner,$until=7) {
	$b=minimum_host_bindings();$ok=$owner->worker('held',$b['worker'],0);
	$events=array(array('observer-started',array()),array('modules',minimum_host_modules()),array('observer-exit',minimum_host_exit()),array('release',array()));
	foreach($events as $i=>$e){if($i+1>$until)return $ok;$ok=$owner->controller($e[0],$b['worker'],$b['observer'],$e[1],$i+1)&&$ok;}
	if($until<5)return $ok;$ok=$owner->worker('released',$b['worker'],5)&&$ok;
	if($until<6)return $ok;$ok=$owner->controller('worker-exit',$b['worker'],$b['observer'],minimum_host_exit(),6)&&$ok;
	if($until<7)return $ok;return $owner->cleanup($b['worker'],$b['observer'],minimum_host_cleanup(),7)&&$ok;
}
