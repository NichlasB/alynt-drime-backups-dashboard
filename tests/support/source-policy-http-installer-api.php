<?php
// Explicit API doubles, NOT genuine WordPress/core/authentication/storage proof.
if (defined('ADBH_INSTALLER_ISOLATED_API') && true === ADBH_INSTALLER_ISOLATED_API && !defined('ABSPATH') && PHP_SAPI==='cli' && count($argv)===2 && !extension_loaded('mysqli') && !extension_loaded('pdo_mysql')) {
$GLOBALS['wp_filter'] = array();
$GLOBALS['checks'] = 0;
$GLOBALS['effects'] = array('transport'=>0,'files'=>0,'mail'=>0,'foreign'=>0,'sql'=>0);
class WP_Hook { public $callbacks = array(); }
if ('no-error' !== $argv[1]) {
	class WP_Error {
		private $code;
		public function __construct($code) { $this->code=$code; }
		public function get_error_code() { return $this->code; }
	}
}
function add_filter($hook,$callback,$priority=10,$accepted=1) {
	global $wp_filter;
	if (!isset($wp_filter[$hook])) $wp_filter[$hook]=new WP_Hook();
	$wp_filter[$hook]->callbacks[$priority]['fixture-'.count($wp_filter[$hook]->callbacks[$priority]??array())]=array('function'=>$callback,'accepted_args'=>$accepted);
	ksort($wp_filter[$hook]->callbacks);
	return true;
}
function apply_filters($hook,$value,...$args) {
	global $wp_filter;
	foreach ($wp_filter[$hook]->callbacks as $entries) foreach ($entries as $entry) {
		$value=call_user_func_array($entry['function'],array_slice(array_merge(array($value),$args),0,$entry['accepted_args']));
	}
	return $value;
}
function must($condition) {
	++$GLOBALS['checks'];
	if (!$condition) throw new AssertionError('installer fixture assertion');
}
function refusal($callback) {
	$caught=false;
	try { $callback(); } catch (RuntimeException $e) { $caught=true; must($e->getMessage()==='HTTP installer containment refused'); }
	must($caught);
}
function identity() { return array('run'=>str_repeat('1',32),'head'=>str_repeat('2',40),'image'=>str_repeat('3',64),'root'=>'/tmp/owned'); }
function post() { return array('post_date'=>'2026-11-07 23:59:59','post_name'=>'hello-world'); }
function probe_urls() { return array('http://127.0.0.1:18777/2026/11/07/hello-world/','http://127.0.0.1:18777/index.php/2026/11/07/hello-world/'); }
function facts() { return array('permalinks'=>'','files_equal'=>true,'installing'=>false,'source_matches'=>true); }
function fresh($purpose='setup') {
	$GLOBALS['wp_filter']=array();
	$c=new Adbh_Installer_Containment(identity(),$purpose);
	$c->register();
	return $c;
}
function arguments() {
	// Independent pinned-source fixture, NOT obtained from the implementation.
	$id=identity();$core=$id['root'].'/adb-http-'.$id['run'].'/core';
	return array('method'=>'GET','timeout'=>5,'redirection'=>5,'httpversion'=>'1.0',
		'user-agent'=>'WordPress/7.1.4; http://127.0.0.1:18777','reject_unsafe_urls'=>false,
		'blocking'=>true,'headers'=>array(),'cookies'=>array(),'body'=>null,'compress'=>false,
		'decompress'=>true,'sslverify'=>true,'sslcertificates'=>$core.'/wp-includes/certificates/ca-bundle.crt',
		'stream'=>false,'filename'=>null,'limit_response_size'=>null,'_redirection'=>5);
}
function hard_call() {
	++$GLOBALS['effects']['sql']; // Model core soft-option work BEFORE the hard gate.
	$value=apply_filters('flush_rewrite_rules_hard',true);
	if ($value) ++$GLOBALS['effects']['files'];
	must(false===$value);
}
function http_call($url,$args=null) {
	$value=apply_filters('pre_http_request',false,$args??arguments(),$url);
	if (false===$value) ++$GLOBALS['effects']['transport'];
	// Assert primary contract independently of any secondary local blocking.
	must(is_object($value)&&get_class($value)==='WP_Error'&&$value->get_error_code()==='adbh_http_blocked');
	return $value;
}
function flow($count=4) {
	$urls=probe_urls();
	hard_call(); http_call($urls[0]); hard_call(); http_call($urls[1]); hard_call();
	if ($count===4) hard_call();
}
function mail_call() {
	$atts=array('to'=>'PRIVATE_RECIPIENT','subject'=>'PRIVATE_SUBJECT','message'=>'PRIVATE_PASSWORD','headers'=>array(),'attachments'=>array(),'embeds'=>array());
	$value=apply_filters('pre_wp_mail',null,$atts);
	if (null===$value) ++$GLOBALS['effects']['mail'];
	must(false===$value);
}
function sidecar_line($row) {
	$keys=array('model','run','head','image','owner','stage','http','hard','mail','violation','completed','runtime_grant','production_ready');
	$values=array(); foreach($keys as $key) $values[]=is_bool($row[$key])?($row[$key]?'true':'false'):(string)$row[$key];
	echo 'sidecar:'.implode('|',$values)."\n";
}
}
