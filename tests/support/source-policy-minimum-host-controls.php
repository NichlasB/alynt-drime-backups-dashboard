<?php
/** In-memory semantic changes only, never disk/source/native mutation. */
if(!isset($argv)||4!==count($argv)||'--synthetic-host-control'!==$argv[1])return;
$root=$argv[2];$mutation=$argv[3];
$edits=array(
	'profile'=>array('$expected !== $observed','false'),
	'decision'=>array(') !== $decision',') !== $decision && false'),
	'image'=>array('$image === $b[\'image\']','true'),
	'source'=>array('hash( \'sha256\', $bindings[\'worker\'][\'source\'] . "\n" . $bindings[\'observer\'][\'source\'] ) !== $source','false'),
	'module-path'=>array('$root . \'/\' . $row[\'name\'] !== $row[\'path\']','false'),
	'module-comparison'=>array('self::module_digest( $data, $this->profile ) === $this->modules','false !== self::module_digest( $data, $this->profile )'),
	'identity'=>array('$observer !== $this->bindings[\'observer\'] ) {','false ) {'),
	'clock'=>array('$now >= 25000','false'),
	'observer-clock'=>array('$now - $this->observer_start >= 2000','false'),
	'release'=>array("4 === \$this->step && 'release'", "2 <= \$this->step && 'release'"),
	'cleanup'=>array('! $this->failed && 8 === $this->step','! $this->failed && 7 <= $this->step'),
	'sticky'=>array('! $this->failed && 8 === $this->step','8 === $this->step'),
	'absence'=>array(') !== $data ) {',') !== $data && false ) {'),
);
if('none'!==$mutation&&!isset($edits[$mutation]))exit(2);
$file=$root.'/scripts/manual/source-policy-minimum-host-admission.php';
if('none'===$mutation)require_once $file;else{$source=file_get_contents($file);$source=str_replace($edits[$mutation][0],$edits[$mutation][1],$source,$count);if(1!==$count)exit(2);eval(substr($source,5));}
require_once $root.'/tests/support/source-policy-minimum-host-fixtures.php';
$class='Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission';$b=minimum_host_bindings();
function minimum_host_contract_rejects($p,$observed,$decision,$bindings,$rows,$source=null){
	try {new Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission(str_repeat('a',32),$source??minimum_host_source(),$p,$observed,$decision,$bindings,$rows);return false;}catch(InvalidArgumentException $e){return true;}
}
$o=minimum_host_owner();$pass=minimum_host_sequence($o,6)&&!$o->snapshot()['synthetic_complete'];$pass=$o->cleanup($b['worker'],$b['observer'],minimum_host_cleanup(),7)&&$pass;
$s=$o->snapshot();$pass=$pass&&$s['synthetic_complete']&&!$s['execution']&&!$s['human_approval_verified']&&!$s['signature_verified']&&!$s['load_authorized'];
$pass=!$o->worker('held',$b['worker'],8)&&$pass;$pass=!$o->snapshot()['synthetic_complete']&&$pass;
$p=minimum_host_profile();$observed=$p;$observed['ubr']=1;$pass=minimum_host_contract_rejects($p,$observed,minimum_host_decision(),$b,minimum_host_modules())&&$pass;
$d=minimum_host_decision();$d['signature_verified']=true;$pass=minimum_host_contract_rejects($p,$p,$d,$b,minimum_host_modules())&&$pass;
$bad=$b;$bad['worker']['image']=str_repeat('0',64);$pass=minimum_host_contract_rejects($p,$p,minimum_host_decision(),$bad,minimum_host_modules())&&$pass;
$d=minimum_host_decision();$d['source_sha256']=str_repeat('0',64);$pass=minimum_host_contract_rejects($p,$p,$d,$b,minimum_host_modules(),str_repeat('0',64))&&$pass;
$rows=minimum_host_modules();$rows[0]['path']='C:/User/kernel32.dll';$parts=array();foreach($rows as $row)$parts[]=implode('|',$row);$p['tool_modules_sha256']=hash('sha256',implode("\n",$parts));$pass=minimum_host_contract_rejects($p,$p,minimum_host_decision($p),$b,$rows)&&$pass;
$o=minimum_host_owner();$pass=minimum_host_sequence($o,1)&&$pass;$rows=minimum_host_modules();$rows[0]['sha256']=str_repeat('0',64);$pass=!$o->controller('modules',$b['worker'],$b['observer'],$rows,2)&&$pass;
$o=minimum_host_owner();$pass=minimum_host_sequence($o,1)&&$pass;$bad=$b['observer'];$bad['object']=str_repeat('0',32);$pass=!$o->controller('modules',$b['worker'],$bad,minimum_host_modules(),2)&&$pass;
$o=minimum_host_owner();$pass=minimum_host_sequence($o,6)&&$pass;$pass=!$o->cleanup($b['worker'],$b['observer'],minimum_host_cleanup(),25000)&&$pass;
$o=minimum_host_owner();$pass=minimum_host_sequence($o,1)&&$pass;$pass=!$o->controller('modules',$b['worker'],$b['observer'],minimum_host_modules(),2001)&&$pass;
$o=minimum_host_owner();$pass=minimum_host_sequence($o,1)&&$pass;$pass=!$o->controller('release',$b['worker'],$b['observer'],array(),2)&&$pass;
$o=minimum_host_owner();$pass=minimum_host_sequence($o,6)&&$pass;$bad=minimum_host_cleanup();$bad['observer_absent']=false;$pass=!$o->cleanup($b['worker'],$b['observer'],$bad,7)&&$pass;
$pass=$pass&&!extension_loaded('mysqli')&&!extension_loaded('pdo_mysql')&&!function_exists('json_encode');echo $pass?'contract-pass':'contract-killed';exit($pass?0:1);
