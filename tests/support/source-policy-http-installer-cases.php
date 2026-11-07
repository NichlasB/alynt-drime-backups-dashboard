<?php
// Isolated callback/IO model scenarios only. No actual core, SQL or network.
if (defined('ADBH_INSTALLER_ISOLATED_API') && true === ADBH_INSTALLER_ISOLATED_API && !defined('ABSPATH') && PHP_SAPI==='cli' && count($argv)===2 && !extension_loaded('mysqli') && !extension_loaded('pdo_mysql')) {
$case=$argv[1];
$c=fresh(in_array($case,array('request','late','primary-late'),true)?'request':'setup');
if ('positive'===$case) { $id=identity();must(adbh_installer_args($id['root'].'/adb-http-'.$id['run'].'/core')===arguments());must(adbh_installer_urls(post())===probe_urls()); }
if ('positive'===$case || 'post-mismatch'===$case || 'fact'===substr($case,0,4) || 'short'===$case || 'mail'===$case) {
	$c->begin_install(); flow('short'===$case?3:4);
	if ('positive'===$case || 'mail'===$case) mail_call();
	if ('post-mismatch'===$case) { $p=post();$p['post_date']='2026-11-08 00:00:00';refusal(function()use($c,$p){$c->seal($p,facts());}); }
	elseif ('short'===$case) refusal(function()use($c){$c->seal(post(),facts());});
	elseif ('mail'===$case) { mail_call();refusal(function()use($c){$c->seal(post(),facts());}); }
	elseif ('fact'===substr($case,0,4)) { $f=facts();$key=substr($case,5);$f[$key]=is_bool($f[$key])?!$f[$key]:'/%postname%/';refusal(function()use($c,$f){$c->seal(post(),$f);}); }
	else {
		$row=$c->seal(post(),facts()); must($row['completed']&&$row['http']===2&&$row['hard']===4&&$row['mail']===1);
		must($GLOBALS['effects']===array('transport'=>0,'files'=>0,'mail'=>0,'foreign'=>0,'sql'=>4));
		must(strpos(serialize($row),'PRIVATE_')===false&&strpos(serialize($row),'hello-world')===false);
		sidecar_line($row);
	}
} elseif ('request'===$case || 'late'===$case || 'primary-late'===$case) {
	$row=$c->seal(null,facts());must($row['completed']&&$row['http']===0&&$row['hard']===0);sidecar_line($row);
	if ('late'===$case || 'primary-late'===$case) { http_call(adbh_installer_urls(post())[0]);must(!$c->summary()['completed']&&$c->summary()['violation']); }
} elseif ('unknown'===$case || 'order'===$case || 'preemption'===$case || 'no-error'===$case) {
	$c->begin_install();
	if ('no-error'===$case) refusal(function()use($c){$c->http(false,arguments(),adbh_installer_urls(post())[0]);});
	elseif ('preemption'===$case) { hard_call();$v=$c->http(array('response'=>'PRIVATE'),arguments(),adbh_installer_urls(post())[0]);must(get_class($v)==='WP_Error'); }
	else { if ('unknown'===$case) hard_call();http_call('unknown'===$case?'http://127.0.0.1:18777/private':adbh_installer_urls(post())[0]); }
	must($c->summary()['violation']);refusal(function()use($c){$c->seal(post(),facts());});
} elseif ('data'===$case) {
	foreach(array(null,array(),new stdClass(),array_merge(identity(),array('extra'=>true)),array_merge(identity(),array('root'=>'/tmp/../foreign')))as$id) must(!adbh_installer_identity($id));
	foreach(array('1900-02-29 00:00:00','2026-11-07 24:00:00','2026-11-07 00:60:00','2026-11-07 00:00:60','2026-02-30 00:00:00')as$date) must(false===adbh_installer_urls(array('post_date'=>$date,'post_name'=>'hello-world')));
	must(is_array(adbh_installer_urls(array('post_date'=>'2000-02-29 00:00:00','post_name'=>'hello-world'))));
	refusal(function(){new Adbh_Installer_Containment(identity(),'install');});
	refusal(function(){new Adbh_Installer_Containment(array_merge(identity(),array('root'=>'/tmp/../foreign')),'setup');});
	$GLOBALS['wp_filter']=array();$ordered=new Adbh_Installer_Containment(array_reverse(identity(),true),'request');$ordered->register();must($ordered->seal(null,facts())['owner']===hash('sha256',implode(':',identity())));
	$GLOBALS['wp_filter']=array();$raw=identity();$run=$raw['run'];$raw['run']=&$run;$copied=new Adbh_Installer_Containment($raw,'request');$copied->register();$run=str_repeat('f',32);must($copied->seal(null,facts())['run']===identity()['run']);
} elseif ('arg'===substr($case,0,3) || 'url'===substr($case,0,3)) {
	$c->begin_install();hard_call();$a=arguments();$u=adbh_installer_urls(post())[0];$which=substr($case,4);
	if ('arg'===substr($case,0,3)) {
		if ('object'===$which) $a=new stdClass();
		elseif ('extra'===$which) $a['extra']='PRIVATE';
		elseif ('missing'===$which) unset($a['timeout']);
		else $a[$which]=is_bool($a[$which])?!$a[$which]:(is_int($a[$which])?(string)$a[$which]:'PRIVATE');
	} else {
		$urls=array('query'=>$u.'?x=PRIVATE','encoded'=>str_replace('hello','%68ello',$u),'host'=>str_replace('127.0.0.1','localhost',$u),'port'=>str_replace('18777','18778',$u),'order'=>'http://127.0.0.1:18777/index.php/2026/11/07/hello-world/','long'=>str_repeat('x',257));
		$u='object'===$which?new class{public function __toString(){throw new AssertionError('coerced');}}:$urls[$which];
	}
	http_call($u,$a);must($c->summary()['violation']);refusal(function()use($c){$c->seal(post(),facts());});
} elseif ('foreign'===substr($case,0,7) || 'registry'===$case) {
	if ('registry'===$case) $GLOBALS['wp_filter']['pre_http_request']->callbacks[10]='PRIVATE';
	else add_filter('pre_http_request',function($value){++$GLOBALS['effects']['foreign'];return false;},'foreign-late'===$case?20:10,3);
	if ('registry'===$case) refusal(function()use($c){$c->http(false,arguments(),adbh_installer_urls(post())[0]);});
	else refusal(function(){http_call(adbh_installer_urls(post())[0]);});
	must($GLOBALS['effects']['foreign']===0);
} elseif ('repeat'===$case || 'purpose'===$case || 'registration'===$case) {
	if ('purpose'===$case) { $c=fresh('request');refusal(function()use($c){$c->begin_install();}); }
	elseif ('registration'===$case) refusal(function()use($c){$c->register();});
	else { $c->begin_install();refusal(function()use($c){$c->begin_install();}); }
} elseif ('local'===$case) { must(true===apply_filters('block_local_requests',false));must($c->summary()['violation']); }
else throw new AssertionError('unknown fixture scenario');
echo 'checks:'.$GLOBALS['checks']."\n";
}
