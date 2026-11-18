import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {spawnSync} from 'node:child_process';
const root=new URL('../../',import.meta.url);
const read=p=>readFileSync(new URL(p,root),'utf8');
const setup=read('scripts/ci/source-policy-http-owned-setup.php');
const observer=read('scripts/ci/source-policy-http-owned-observer.php');
function body(source=setup,name='events'){const m=source.match(new RegExp('function adbh_prepare_core_'+name+'\\(\\) \\{[\\s\\S]+?\\n\\}'));assert.ok(m,'actual setup preparation exists');return m[0];}
const api=String.raw`
function need($ok,$label){if(!$ok)throw new RuntimeException($label);}
function adbh_check($ok,$label){need($ok,$label);}
function wp_installing(){return $GLOBALS['installing'];}
function wp_next_scheduled($hook){return isset($GLOBALS['events'][$hook])?$GLOBALS['events'][$hook]->timestamp:false;}
function wp_schedule_event($timestamp,$schedule,$hook,$args=array(),$error=false){
 need($error===true && $args===array(),'genuine-api-error-contract');
 if($GLOBALS['fault']==='schedule-false')return false;
 if($GLOBALS['fault']==='schedule-error')return (object)array('error'=>'synthetic');
 $intervals=array('hourly'=>3600,'daily'=>86400,'weekly'=>604800);
 adbh_owned_observe("UPDATE adbh_abcdef123456_options SET option_value = 'synthetic-events' WHERE option_name = 'cron'");
 ++$GLOBALS['executed'];$GLOBALS['events'][$hook]=(object)array('timestamp'=>$timestamp,'schedule'=>$schedule,'args'=>$args,'interval'=>$intervals[$schedule]);return true;
}
function wp_get_scheduled_event($hook){
 $event=$GLOBALS['events'][$hook]??false;
 if($GLOBALS['fault']==='absent')return false;
 if($event){$event=clone $event;foreach(array('timestamp','schedule','args','interval') as $key){if($GLOBALS['fault']===$key)$event->$key=null;}}
 return $event;
}
function proof(){
 $expected=array('recovery_mode_clean_expired_keys'=>array('daily',86400,0),'wp_site_health_scheduled_check'=>array('weekly',604800,86400),'wp_privacy_delete_old_export_files'=>array('hourly',3600,0),'wp_privacy_personal_data_cleanup_requests'=>array('daily',86400,0));
 $before=time();adbh_prepare_core_events();$after=time();need(array_keys($expected)===array_keys($GLOBALS['events']),'all-four-real-preparations');need(4===$GLOBALS['executed'],'only-four-writes');
 foreach($expected as $hook=>$spec){$e=$GLOBALS['events'][$hook];need($e->schedule===$spec[0]&&$e->interval===$spec[1]&&$e->args===array()&&$e->timestamp>=$before+$spec[2]&&$e->timestamp<=$after+$spec[2],'correct-core-event');}
 need(4===$GLOBALS['adbh_ledger']['setup'] && 0===$GLOBALS['adbh_ledger']['bootstrap'],'setup-accounting');
}
`;
const widgetApi=String.raw`
class WP_Widget_Factory {public $widgets=array();}
$GLOBALS['wp_widget_factory']=new WP_Widget_Factory();$GLOBALS['options']=array();
$GLOBALS['classes']=array('WP_Widget_Pages','WP_Widget_Calendar','WP_Widget_Archives','WP_Widget_Media_Audio','WP_Widget_Media_Image','WP_Widget_Media_Gallery','WP_Widget_Media_Video','WP_Widget_Meta','WP_Widget_Search','WP_Widget_Text','WP_Widget_Categories','WP_Widget_Recent_Posts','WP_Widget_Recent_Comments','WP_Widget_RSS','WP_Widget_Tag_Cloud','WP_Nav_Menu_Widget','WP_Widget_Custom_HTML','WP_Widget_Block');
foreach($GLOBALS['classes'] as $index=>$class){eval('class '.$class.' {public $option_name="widget_'.$index.'";}');if(in_array($index,array(9,10,13),true))$GLOBALS['options']['widget_'.$index]=array();}
function wp_widgets_init(){
 foreach($GLOBALS['classes'] as $class){$w=new $class();$GLOBALS['wp_widget_factory']->widgets[$class]=$w;if(!isset($GLOBALS['options'][$w->option_name])){adbh_owned_observe("INSERT INTO adbh_abcdef123456_options (option_name, option_value) VALUES ('synthetic-widget', 'synthetic-setting')");++$GLOBALS['executed'];$GLOBALS['options'][$w->option_name]=array('_multiwidget'=>1);}}
 if($GLOBALS['fault']==='registry')unset($GLOBALS['wp_widget_factory']->widgets['WP_Widget_Block']);
}
function get_option($name){return $GLOBALS['fault']==='setting'?false:($GLOBALS['options'][$name]??false);}
function widgetProof(){adbh_prepare_core_widgets();need(array_keys($GLOBALS['wp_widget_factory']->widgets)===$GLOBALS['classes'],'all-eighteen-registered');need(15===$GLOBALS['executed'],'only-missing-settings-written');need(18===count($GLOBALS['options']),'all-settings-ready');foreach(array(9,10,13) as $i)need(array()===$GLOBALS['options']['widget_'.$i],'existing-settings-preserved');}
`;
const patternApi=String.raw`
$GLOBALS['adbh_private']['content_path']='/owned/content';$GLOBALS['cached']=false;$GLOBALS['expiry']=false;
function wp_is_development_mode($mode){need($mode==='theme','fixed-mode');return $GLOBALS['fault']==='development';}
class WP_Theme {
 function exists(){return true;}function get_stylesheet(){return $GLOBALS['fault']==='foreign-theme'?'other':'twentytwentyfive';}function parent(){return false;}function get_theme_root(){return '/owned/content/themes';}function get($name){need($name==='Version','fixed-version');return '1.4';}
 function get_block_patterns(){
  $patterns=array('owned/pattern'=>array('title'=>'Synthetic only'));
  if($GLOBALS['fault']!=='missing-cache'){adbh_owned_observe("INSERT INTO adbh_abcdef123456_options (option_name, option_value) VALUES ('synthetic-pattern-cache', 'synthetic-setting')");++$GLOBALS['executed'];$GLOBALS['cached']=array('version'=>'1.4','patterns'=>$patterns);$GLOBALS['expiry']=time()+1800;}
  return $GLOBALS['fault']==='empty'?array():$patterns;
 }
}
function wp_get_theme(){return new WP_Theme();}
function get_site_transient($key){need($key==='wp_theme_files_patterns-'.md5('/owned/content/themes/twentytwentyfive'),'fixed-cache-key');$v=$GLOBALS['cached'];if(is_array($v)&&$GLOBALS['fault']==='version')$v['version']='wrong';return $v;}
function get_option($key){need($key==='_site_transient_timeout_wp_theme_files_patterns-'.md5('/owned/content/themes/twentytwentyfive'),'fixed-expiry-key');return $GLOBALS['fault']==='expired'?time()-1:$GLOBALS['expiry'];}
function patternProof(){adbh_prepare_core_patterns();need(1===$GLOBALS['executed']&&is_array($GLOBALS['cached']),'actual-cache-preparation');}
`;
function run({source=setup,fault='',purpose='setup',installing=false,stage='seed',cron=true,existing=false,name='events'}={}){
 const header=`define('ADB_HTTP_WORKER',${JSON.stringify(purpose)});define('DISABLE_WP_CRON',${cron?'true':'false'});$GLOBALS['adbh_stage']=${JSON.stringify(stage)};$GLOBALS['installing']=${installing?'true':'false'};$GLOBALS['fault']=${JSON.stringify(fault)};$GLOBALS['events']=array();$GLOBALS['executed']=0;$GLOBALS['adbh_private']=array('prefix'=>'adbh_abcdef123456_');$GLOBALS['adbh_ledger']=array('setup'=>0,'bootstrap'=>0,'auth'=>0);`;
 const init=existing?"$GLOBALS['events']['recovery_mode_clean_expired_keys']=(object)array('timestamp'=>123);":'';
 const code=header+observer.replace(/^<\?php/,'')+api+({widgets:widgetApi,patterns:patternApi}[name]??'')+body(source,name)+init+({widgets:'widgetProof();',patterns:'patternProof();'}[name]??'proof();')+`echo 'ok';`;
 return spawnSync(process.env.PHP_BINARY??'php',['-n','-r',code],{encoding:'utf8',timeout:5000,maxBuffer:8192});
}
function passed(r){assert.equal(r.error,undefined);assert.equal(r.status,0,r.stderr+r.stdout);assert.equal(r.stderr,'');assert.equal(r.stdout,'ok');}
function failed(r,label){assert.equal(r.error,undefined);assert.notEqual(r.status,0);assert.match(r.stderr+r.stdout,new RegExp('RuntimeException: '+label));assert.doesNotMatch(r.stderr+r.stdout,/Parse error|syntax error|TypeError/);}
test('actual setup prepares the four core events through checked APIs and owned setup SQL admission',()=>passed(run()));
test('preparation rejects request context, installation mode, wrong stage, enabled cron and preexisting events',()=>{
 for(const options of [{purpose:'request'},{installing:true},{stage:'core-bootstrap'},{cron:false}])failed(run(options),'core-events-context');
 failed(run({existing:true}),'core-event-absence');
});
test('failed scheduling and missing or malformed event readback fail closed',()=>{
 for(const fault of ['schedule-false','schedule-error','absent','timestamp','schedule','args','interval'])failed(run({fault}),fault.startsWith('schedule-')?'core-event-schedule':'core-event-readback');
});
test('source call remains after install-mode exit and before setup finalization/baseline sealing',()=>{
 const call=setup.indexOf('\n\tadbh_prepare_core_events();');assert.ok(call>setup.indexOf('\n\twp_installing( false );'));assert.ok(call<setup.indexOf('\n\tregister_shutdown_function('));
 const controller=read('scripts/ci/source-policy-http-owned-controller.mjs');assert.ok(controller.indexOf('const setup=JSON.parse(raw)')<controller.indexOf('db.seal(s,'));
});
test('actual finite core-widget preparation preserves existing settings and uses only setup admission',()=>{
 passed(run({name:'widgets'}));for(const options of [{purpose:'request'},{installing:true},{stage:'core-bootstrap'},{cron:false}])failed(run({...options,name:'widgets'}),'core-widgets-context');
 failed(run({name:'widgets',fault:'registry'}),'core-widgets-registry');failed(run({name:'widgets',fault:'setting'}),'core-widget-readback');
 const call=setup.indexOf('\n\tadbh_prepare_core_widgets();');assert.ok(call>setup.indexOf('\n\tadbh_prepare_core_events();'));assert.ok(call<setup.indexOf('\n\tregister_shutdown_function('));
 for(const [from,to,options,oracle]of [
  ['\n\twp_widgets_init();','\n\t/* omitted */',{},passed],
  ["is_array( get_option( $widget->option_name ) )",'true',{fault:'setting'},r=>failed(r,'core-widget-readback')],
  ["array_keys( $wp_widget_factory->widgets ) === $classes",'true',{fault:'registry'},r=>failed(r,'core-widgets-registry')],
 ]){assert.ok(setup.includes(from));const r=run({...options,name:'widgets',source:setup.replace(from,to)});assert.equal(r.error,undefined);assert.doesNotMatch(r.stderr+r.stdout,/Parse error|syntax error|TypeError/);assert.throws(()=>oracle(r),{code:'ERR_ASSERTION'});}
});
test('genuine theme-cache preparation requires fixed theme/mode and durable matching readback before seal',()=>{
 passed(run({name:'patterns'}));failed(run({name:'patterns',purpose:'request'}),'core-patterns-context');failed(run({name:'patterns',fault:'development'}),'core-patterns-mode');failed(run({name:'patterns',fault:'foreign-theme'}),'core-patterns-theme');
 for(const fault of ['missing-cache','version','empty','expired'])failed(run({name:'patterns',fault}),'core-patterns-readback');
 const call=setup.indexOf('\n\tadbh_prepare_core_patterns();');assert.ok(call>setup.indexOf('\n\tadbh_prepare_core_widgets();'));assert.ok(call<setup.indexOf('\n\tregister_shutdown_function('));
 for(const [from,to,options,oracle]of [
  ['$expires >= $now + 600','true',{fault:'expired'},r=>failed(r,'core-patterns-readback')],
  ["$cached['version'] === $theme->get( 'Version' )",'true',{fault:'version'},r=>failed(r,'core-patterns-readback')],
  ["'twentytwentyfive' === $theme->get_stylesheet()",'true',{fault:'foreign-theme'},r=>failed(r,'core-patterns-theme')],
 ]){assert.ok(setup.includes(from));const r=run({...options,name:'patterns',source:setup.replace(from,to)});assert.equal(r.error,undefined);assert.doesNotMatch(r.stderr+r.stdout,/Parse error|syntax error|TypeError/);assert.throws(()=>oracle(r),{code:'ERR_ASSERTION'});}
});
test('guard removal, missing preparation and altered event controls are assertion-killed',()=>{
 const controls=[
  ["'recovery_mode_clean_expired_keys'", "'wrong_recovery_event'", {}, passed],
  ["'hourly', 3600, 0", "'daily', 86400, 0", {}, passed],
  ["true === wp_schedule_event( $timestamp, $spec[0], $hook, array(), true )", 'true', {}, passed],
  ["false === wp_next_scheduled( $hook )", 'true', {existing:true},r=>failed(r,'core-event-absence')],
  ["$event->interval === $spec[1]", 'true', {fault:'interval'},r=>failed(r,'core-event-readback')],
  ["'setup' === ADB_HTTP_WORKER", 'true', {purpose:'request'},r=>failed(r,'core-events-context')],
 ];
 for(const [from,to,options,oracle]of controls){assert.ok(setup.includes(from),from);const source=setup.replace(from,to);assert.notEqual(source,setup);const r=run({...options,source});assert.equal(r.error,undefined);assert.doesNotMatch(r.stderr+r.stdout,/Parse error|syntax error|TypeError/);assert.throws(()=>oracle(r),{code:'ERR_ASSERTION'});}
});
