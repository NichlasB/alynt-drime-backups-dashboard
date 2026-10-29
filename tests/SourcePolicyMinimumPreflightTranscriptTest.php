<?php
use PHPUnit\Framework\TestCase;

/** No frame, including a valid synthetic terminal, is a process/load permit. */
final class SourcePolicyMinimumPreflightTranscriptTest extends TestCase {
	protected function setUp():void {require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-launch-data.php';require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-preflight-transcript.php';require_once __DIR__.'/support/source-policy-minimum-preflight-fixtures.php';}
	private function feed($owner,$event,$now) {return $owner->{$event[0]}($event[1],$now);}
	public function test_launch_summary_and_module_data_compose_without_physical_authority() {
		$f=minimum_launch_fixture();$r=Alynt_Drime_Backups_Dashboard_Minimum_Launch_Data::inspect($f);$o=new Alynt_Drime_Backups_Dashboard_Minimum_Preflight_Transcript($f['run'],$r['manifest_sha256'],minimum_preflight_bindings(),$r['source_sha256'],str_repeat('f',64),minimum_preflight_expected($f));
		foreach(minimum_preflight_lines() as $i=>$event){$event[1]=str_replace(array(str_repeat('d',64),str_repeat('e',64)),array($r['manifest_sha256'],$r['source_sha256']),$event[1]);$this->assertTrue($this->feed($o,$event,$i));}$this->assertTrue($o->snapshot()['synthetic_complete']);$this->assertFalse($o->snapshot()['execution']);$this->assertFalse($r['filesystem_verified']);$this->assertFalse($r['platform_verified']);
	}
	public function test_two_ordered_stages_close_synthetically_with_separate_channels() {
		$o=minimum_preflight_owner();$this->assertTrue($o->stderr('',0));foreach(minimum_preflight_lines() as $i=>$event){$this->assertTrue($this->feed($o,$event,$i));if(7===$i)$this->assertFalse($o->snapshot()['synthetic_complete']);}
		$r=$o->snapshot();$this->assertSame('complete',$r['phase']);$this->assertTrue($r['synthetic_complete']);foreach(array('execution','load_authorized','native_qualified') as $key)$this->assertFalse($r[$key]);$this->assertTrue($o->eof(17));$this->assertFalse($this->feed($o,minimum_preflight_lines()[0],18));$this->assertFalse($o->snapshot()['synthetic_complete']);
	}
	public function test_every_event_rejects_removed_reordered_or_wrong_source_guard() {
		$events=minimum_preflight_lines();foreach($events as $at=>$event){foreach(array('correlation','channel','replay','extra','partial','crlf','pid','time','type') as $case){$o=minimum_preflight_owner();for($i=0;$i<$at;$i++)$this->assertTrue($this->feed($o,$events[$i],$i));$bad=$event;$time=$at;
			if('correlation'===$case)$bad[1]=str_replace(str_repeat('d',64),str_repeat('b',64),$bad[1]);elseif('channel'===$case)$bad[0]='worker'===$bad[0]?'controller':'worker';elseif('replay'===$case)$bad=$events[max(0,$at-1)];elseif('extra'===$case)$bad[1].='extra';elseif('partial'===$case)$bad[1]=substr($bad[1],0,-1);elseif('crlf'===$case)$bad[1]=str_replace("\n","\r\n",$bad[1]);elseif('pid'===$case)$bad[1]=str_replace('|101|','|103|',str_replace('|102|','|104|',$bad[1]));elseif('time'===$case)$time=25000;else $time=(string)$time;
			if('replay'===$case&&0===$at)$bad=$events[1];$this->assertFalse($this->feed($o,$bad,$time),$at.':'.$case);$this->assertFalse($this->feed($o,$event,$at+1));$this->assertTrue($o->snapshot()['failed']);$this->assertFalse($o->snapshot()['synthetic_complete']);}}
	}
	public function test_profile_fact_and_exit_cleanup_mutations_are_refused() {
		$events=minimum_preflight_lines();foreach(array(1,7,9,15) as $at){$o=minimum_preflight_owner();for($i=0;$i<$at;$i++)$this->assertTrue($this->feed($o,$events[$i],$i));$bad=$events[$at];$bad[1]=str_replace(array('7.4.33','retained-owned','artifacts-absent'),array('8.5.12','unconfirmed','unconfirmed'),$bad[1]);$this->assertFalse($this->feed($o,$bad,$at));}
		$o=minimum_preflight_owner();$this->assertFalse($o->eof(0));$this->assertTrue($o->snapshot()['failed']);$o=minimum_preflight_owner();$this->assertFalse($o->stderr('private error must not be retained',0));$this->assertStringNotContainsString('private error',var_export($o->snapshot(),true));
		$o=minimum_preflight_owner();$this->assertTrue($this->feed($o,$events[0],5));$this->assertFalse($this->feed($o,$events[1],4));
	}
	public function test_producer_bounds_and_canonical_fields_reject_unsafe_values() {
		$c='Alynt_Drime_Backups_Dashboard_Minimum_Preflight_Transcript';$r=str_repeat('a',32);$h=str_repeat('b',64);
		foreach(array(array($r,$h,'base',101,1,'start',array('01',$h)),array($r,$h,'base',101,1,'start',array('1','bad')),array($r,$h,'other',101,1,'held',array('await-observer')),array($r,$h,'base','101',1,'held',array('await-observer')),array($r,$h,'base',101,'1','held',array('await-observer')),array($r,$h,'base',101,1,'held',array("await-observer\n")),array($r,$h,'base',101,1,'modules',array($h,'129')),array($r,$h,'base',101,1,'exit',array('1','drained'))) as $bad)$this->assertFalse($c::frame(...$bad));
		$this->assertFalse($c::ack_frame($r,$h,'base',0));$this->assertFalse($c::profile_fields('other',$h,$h));
		$b=minimum_preflight_bindings();$b['mysqli']['pid']=$b['base']['pid'];try{new $c($r,$h,$b,$h,$h,minimum_preflight_expected());$this->fail('Ambiguous child identity accepted');}catch(InvalidArgumentException $e){$this->assertSame('Synthetic preflight bindings refused',$e->getMessage());}
	}
	public function test_clock_types_binding_refusals_and_stderr_overflow_preserve_first_safe_reason() {
		$events=minimum_preflight_lines();foreach(array(-1,25000,'0',0.0,true,array()) as $bad){$o=minimum_preflight_owner();$this->assertFalse($o->worker($events[0][1],$bad));$reason=$o->snapshot()['reason'];$this->assertFalse($o->worker($events[0][1],0));$this->assertSame($reason,$o->snapshot()['reason']);}
		$o=minimum_preflight_owner();$this->assertFalse($o->worker(str_repeat('x',513),0));$this->assertSame(0,$o->snapshot()['events']);$o=minimum_preflight_owner();$this->assertFalse($o->stderr(str_repeat('s',5000),0));$this->assertSame(4097,$o->snapshot()['stderr_bytes_capped']);
		foreach(array('pid','start','extra','module','count','run') as $case){$b=minimum_preflight_bindings();$m=minimum_preflight_expected();$run=str_repeat('a',32);if('pid'===$case)$b['base']['pid']='101';elseif('start'===$case)$b['base']['start']='0123';elseif('extra'===$case)$b['base']['extra']=true;elseif('module'===$case)$m['base']['sha256']='bad';elseif('count'===$case)$m['base']['count']=129;else $run=new stdClass;
			try{new Alynt_Drime_Backups_Dashboard_Minimum_Preflight_Transcript($run,str_repeat('d',64),$b,str_repeat('e',64),str_repeat('f',64),$m);$this->fail('Malformed binding accepted');}catch(InvalidArgumentException $e){$this->assertSame('Synthetic preflight bindings refused',$e->getMessage());}}
	}
}
