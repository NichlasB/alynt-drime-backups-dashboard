<?php
use PHPUnit\Framework\TestCase;

/** Virtual clocks/channels and identity claims cannot prove physical processes. */
final class SourcePolicyMinimumObserverAdmissionTest extends TestCase {
	protected function setUp():void {require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-host-admission.php';require_once __DIR__.'/support/source-policy-minimum-host-fixtures.php';}
	public function test_every_missing_reordered_or_replayed_stage_refuses_and_cannot_reopen() {
		$b=minimum_host_bindings();$events=array(
			array('worker','held',array(),0),array('controller','observer-started',array(),1),array('controller','modules',minimum_host_modules(),2),array('controller','observer-exit',minimum_host_exit(),3),array('controller','release',array(),4),array('worker','released',array(),5),array('controller','worker-exit',minimum_host_exit(),6),array('cleanup','cleanup',minimum_host_cleanup(),7));
		foreach($events as $index=>$e){$o=minimum_host_owner();foreach(array_slice($events,0,$index) as $prior)$this->assertTrue($this->event($o,$prior,$b));$this->assertFalse($o->snapshot()['synthetic_complete']);
			if($index<7)$this->assertFalse($this->event($o,$events[$index+1],$b));else $this->assertFalse($o->controller('release',$b['worker'],$b['observer'],array(),7));
			if(7===$index)$this->assertTrue($this->event($o,$e,$b));else $this->assertFalse($this->event($o,$e,$b));$this->assertTrue($o->snapshot()['failed']);$this->assertFalse($o->snapshot()['synthetic_complete']);$this->assertFalse($o->snapshot()['execution']);
		}
		$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o));$this->assertFalse($o->worker('held',$b['worker'],8));$this->assertFalse($o->snapshot()['synthetic_complete']);
	}
	private function event($o,$e,$b) {if('worker'===$e[0])return $o->worker($e[1],$b['worker'],$e[3]);if('cleanup'===$e[0])return $o->cleanup($b['worker'],$b['observer'],$e[2],$e[3]);return $o->controller($e[1],$b['worker'],$b['observer'],$e[2],$e[3]);}
	public function test_wrong_identity_is_rejected_before_module_payload_traversal() {
		foreach(array('worker','observer') as $role)foreach(array('pid'=>999,'start'=>'999','object'=>str_repeat('0',32),'image'=>str_repeat('0',64),'source'=>str_repeat('0',64)) as $key=>$v){$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o,1));$b=minimum_host_bindings();$b[$role][$key]=$v;$this->assertFalse($o->controller('modules',$b['worker'],$b['observer'],array(new stdClass),2));$this->assertSame('controller-identity',$o->snapshot()['reason']);}
		$o=minimum_host_owner();$b=minimum_host_bindings();$this->assertFalse($o->worker('held',$b['observer'],0));$this->assertTrue($o->snapshot()['failed']);
	}
	/** @dataProvider times */
	public function test_virtual_deadlines_and_types_are_strict($now) {
		$o=minimum_host_owner();$b=minimum_host_bindings();$this->assertFalse($o->worker('held',$b['worker'],$now));$this->assertSame('deadline',$o->snapshot()['reason']);$this->assertFalse($o->worker('held',$b['worker'],0));
	}
	public static function times(){return array(array(-1),array(20000),array(25000),array('0'),array(0.0),array(false),array(null),array(new stdClass));}
	public function test_observer_must_exit_and_drain_within_its_own_budget_before_release() {
		$b=minimum_host_bindings();foreach(array(2001,2002) as $now){$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o,1));$this->assertFalse($o->controller('modules',$b['worker'],$b['observer'],minimum_host_modules(),$now));$this->assertSame('observer-deadline',$o->snapshot()['reason']);}
		$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o,1));$this->assertTrue($o->controller('modules',$b['worker'],$b['observer'],minimum_host_modules(),2000));$this->assertFalse($o->controller('observer-exit',$b['worker'],$b['observer'],minimum_host_exit(),2001));
		$o=minimum_host_owner();$this->assertTrue($o->worker('held',$b['worker'],19999));$this->assertFalse($o->controller('observer-started',$b['worker'],$b['observer'],array(),20000));
		$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o,2));$this->assertFalse($o->controller('observer-exit',$b['worker'],$b['observer'],minimum_host_exit(),1));
	}
	public function test_cancel_parent_exit_disposal_or_eof_cannot_substitute_for_owned_exit_and_drain() {
		$b=minimum_host_bindings();foreach(array('observer-exit'=>2,'worker-exit'=>5) as $kind=>$until)foreach(array('exit'=>1,'drained'=>false,'absent'=>false,'extra'=>true) as $key=>$value){$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o,$until));$facts=minimum_host_exit();$facts[$key]=$value;$this->assertFalse($o->controller($kind,$b['worker'],$b['observer'],$facts,$until+1));$this->assertFalse($o->snapshot()['synthetic_complete']);}
		foreach(array('cancelled','disposed','eof','stderr','parent-exited') as $kind){$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o,1));$this->assertFalse($o->controller($kind,$b['worker'],$b['observer'],array(),2));}
	}
	public function test_cleanup_is_independent_complete_and_bounded_even_after_failure() {
		$b=minimum_host_bindings();foreach(array_keys(minimum_host_cleanup()) as $key){$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o,6));$bad=minimum_host_cleanup();$bad[$key]=false;$this->assertFalse($o->cleanup($b['worker'],$b['observer'],$bad,7));$this->assertFalse($o->snapshot()['synthetic_complete']);$this->assertFalse($o->snapshot()['cleanup_consistent']);}
		$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o,6));$this->assertTrue($o->cleanup($b['worker'],$b['observer'],minimum_host_cleanup(),24999));$this->assertTrue($o->snapshot()['synthetic_complete']);
		$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o,6));$this->assertFalse($o->cleanup($b['worker'],$b['observer'],minimum_host_cleanup(),25000));$this->assertFalse($o->snapshot()['cleanup_consistent']);
		$o=minimum_host_owner();$this->assertTrue(minimum_host_sequence($o,1));$this->assertFalse($o->controller('modules',$b['worker'],$b['observer'],minimum_host_modules(),2001));$reason=$o->snapshot()['reason'];$this->assertTrue($o->cleanup($b['worker'],$b['observer'],minimum_host_cleanup(),2002));$this->assertTrue($o->snapshot()['cleanup_consistent']);$this->assertFalse($o->snapshot()['synthetic_complete']);$this->assertSame($reason,$o->snapshot()['reason']);$this->assertFalse($o->controller('release',$b['worker'],$b['observer'],array(),2003));
	}
}
