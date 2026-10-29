<?php
use PHPUnit\Framework\TestCase;

/** Non-authorizing policy DATA; no real operator trust or host inspection. */
final class SourcePolicyMinimumHostAdmissionTest extends TestCase {
	protected function setUp():void {require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-host-admission.php';require_once __DIR__.'/support/source-policy-minimum-host-fixtures.php';}
	public function test_positive_consistency_requires_every_stage_and_never_authorizes_execution() {
		$o=minimum_host_owner();$this->assertFalse($o->snapshot()['synthetic_complete']);$this->assertTrue(minimum_host_sequence($o,6));$this->assertFalse($o->snapshot()['synthetic_complete']);
		$b=minimum_host_bindings();$this->assertTrue($o->cleanup($b['worker'],$b['observer'],minimum_host_cleanup(),7));$s=$o->snapshot();$this->assertTrue($s['synthetic_complete']);$this->assertTrue($s['cleanup_consistent']);
		foreach(array('execution','load_authorized','human_approval_verified','platform_verified','signature_verified','offline_provenance_verified','native_qualified') as $key)$this->assertFalse($s[$key]);
		$this->assertSame(array('step','failed','reason','synthetic_complete','cleanup_consistent','execution','load_authorized','human_approval_verified','platform_verified','signature_verified','offline_provenance_verified','native_qualified'),array_keys($s));
	}
	/** @dataProvider profile_fields */
	public function test_every_observed_profile_dimension_is_bound($field) {
		$p=minimum_host_profile();$observed=$p;$observed[$field]=is_int($p[$field])?$p[$field]+1:(is_bool($p[$field])?!$p[$field]:$p[$field].'x');
		$this->expectException(InvalidArgumentException::class);new Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission(str_repeat('a',32),minimum_host_source(),$p,$observed,minimum_host_decision(),minimum_host_bindings(),minimum_host_modules());
	}
	public static function profile_fields(){require_once __DIR__.'/support/source-policy-minimum-host-fixtures.php';return array_map(function($v){return array($v);},array_keys(minimum_host_profile()));}
	/** @dataProvider decision_cases */
	public function test_explicit_decision_domains_and_bindings_cannot_be_replayed_or_relabelled($case) {
		$d=minimum_host_decision();$run=str_repeat('a',32);$source=minimum_host_source();$b=minimum_host_bindings();
		switch($case){case 'missing':unset($d['basis']);break;case 'extra':$d['approved']=true;break;case 'policy':$d['policy']='existing-host-operator-trust-v2';break;case 'scope':$d['scope']='native-write';break;case 'basis':$d['basis']='verified-signature';break;case 'signature':$d['signature_verified']=true;break;case 'offline':$d['offline_provenance_verified']=true;break;case 'run':$run=str_repeat('b',32);break;case 'profile':$d['profile_sha256']=str_repeat('0',64);break;case 'source':$source=str_repeat('0',64);break;case 'run-type':$run=array();break;case 'source-type':$source=new stdClass;break;case 'pid-type':$b['worker']['pid']='101';break;case 'pid-reuse':$b['observer']['pid']=101;break;case 'object-reuse':$b['observer']['object']=$b['worker']['object'];break;case 'binding-source':$b['observer']['source']=$b['worker']['source'];break;case 'image':$b['worker']['image']=str_repeat('0',64);break;case 'start':$b['worker']['start']='0123';break;case 'extra-binding':$b['worker']['handle']=new stdClass;break;}
		$this->expectException(InvalidArgumentException::class);$p=minimum_host_profile();new Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission($run,$source,$p,$p,$d,$b,minimum_host_modules());
	}
	public static function decision_cases(){return array_map(function($v){return array($v);},array('missing','extra','policy','scope','basis','signature','offline','run','profile','source','run-type','source-type','pid-type','pid-reuse','object-reuse','binding-source','image','start','extra-binding'));}
	/** @dataProvider invalid_profiles */
	public function test_matching_invalid_profiles_cannot_self_bless_a_host($field,$value) {
		$p=minimum_host_profile();$p[$field]=$value;$this->assertFalse(Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission::profile_digest($p));
		$this->expectException(InvalidArgumentException::class);new Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission(str_repeat('a',32),minimum_host_source(),$p,$p,minimum_host_decision($p),minimum_host_bindings(),minimum_host_modules());
	}
	public static function invalid_profiles(){return array(array('system','C:/Windows/system32'),array('system','C:/Users/System32'),array('tool_root','C:/Windows/Tools'),array('tool_root','C:/Tools/../PowerShell'),array('tool_root','C:/Tools/CON'),array('tool_root','C:/Tools/runtime.'),array('tool_root','C:/Tools//PowerShell'),array('tool_root','C:/Tools:evil/PowerShell'),array('tool_root','C:/'.str_repeat('a',241)),array('tool_root',new stdClass),array('architecture','x86'),array('crt_architecture','x86'),array('tool_architecture','x86'),array('crt_installed',1),array('ubr','0'),array('ubr',-1),array('ubr',1000000),array('build','10.0'),array('crt_version','15.0.1.0'),array('tool_version','8.0.0'),array('windows_identity',str_repeat('A',64)),array('crt_sha256',array()),array('tool_modules_sha256',str_repeat('a',65)),array('extra',true));}
	/** @dataProvider invalid_modules */
	public function test_untrusted_module_shapes_and_limits_are_validated_independently($case) {
		$p=minimum_host_profile();$rows=minimum_host_modules();switch($case){case 'duplicate':$rows[]=$rows[0];break;case 'partial':array_pop($rows);break;case 'extra':$rows[]=array('name'=>'extra.dll','path'=>$p['system'].'/extra.dll','sha256'=>str_repeat('a',64),'size'=>1,'role'=>'windows');break;case 'path':$rows[0]['path']='C:/User/kernel32.dll';break;case 'role':$rows[1]['role']='windows';break;case 'hash':$rows[1]['sha256']=str_repeat('0',64);break;case 'type':$rows[0]['size']='1024';break;case 'size':$rows[0]['size']=33554433;break;case 'sparse':unset($rows[1]);break;case 'large':$rows=array_fill(0,129,$rows[0]);break;case 'aggregate':for($i=0;$i<9;$i++)$rows[]=array('name'=>'x'.$i.'.dll','path'=>$p['system'].'/x'.$i.'.dll','sha256'=>str_repeat('a',64),'size'=>33554432,'role'=>'windows');break;case 'application':$rows[0]=array('name'=>'php7.dll','path'=>$p['system'].'/php7.dll','sha256'=>str_repeat('a',64),'size'=>1,'role'=>'windows');break;case 'alias':$rows[0]['path']='C:/Windows/System32/../System32/kernel32.dll';break;case 'missing-kernel':array_shift($rows);break;case 'crt':$rows[]=array('name'=>'vcruntime140.dll','path'=>$p['tool_root'].'/vcruntime140.dll','sha256'=>$p['crt_sha256'],'size'=>1,'role'=>'observer-tool');break;}
		$o=minimum_host_owner();$b=minimum_host_bindings();$this->assertTrue(minimum_host_sequence($o,1));$this->assertFalse($o->controller('modules',$b['worker'],$b['observer'],$rows,2));$this->assertTrue($o->snapshot()['failed']);
		$this->expectException(InvalidArgumentException::class);new Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission(str_repeat('a',32),minimum_host_source(),$p,$p,minimum_host_decision(),$b,$rows);
	}
	public static function invalid_modules(){return array_map(function($v){return array($v);},array('duplicate','partial','extra','path','role','hash','type','size','sparse','large','aggregate','application','alias','missing-kernel','crt'));}
	public function test_module_row_order_is_canonical_but_expected_set_is_not_learned_from_observation() {
		$o=minimum_host_owner();$b=minimum_host_bindings();$this->assertTrue(minimum_host_sequence($o,1));$this->assertTrue($o->controller('modules',$b['worker'],$b['observer'],array_reverse(minimum_host_modules()),2));
		$p=minimum_host_profile();$p['windows_identity']=str_repeat('6',64);$this->assertNotSame(Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission::profile_digest($p),Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission::profile_digest(minimum_host_profile()));
	}
	/** @dataProvider mirrored_module_cases */
	public function test_matching_module_digests_cannot_hide_invalid_structure_or_resource_claims($case) {
		$p=minimum_host_profile();$rows=minimum_host_modules();switch($case){case 'duplicate':$rows[]=$rows[0];break;case 'path':$rows[0]['path']='C:/User/kernel32.dll';break;case 'size':$rows[0]['size']=33554433;break;case 'type':$rows[0]['size']='1024';break;case 'sparse':unset($rows[1]);break;case 'missing-kernel':array_shift($rows);break;case 'application':$rows[]=array('name'=>'php7.dll','path'=>$p['system'].'/php7.dll','sha256'=>str_repeat('a',64),'size'=>1,'role'=>'windows');break;case 'count':case 'aggregate':for($i=0;$i<('count'===$case?126:9);$i++)$rows[]=array('name'=>'x'.$i.'.dll','path'=>$p['system'].'/x'.$i.'.dll','sha256'=>str_repeat('a',64),'size'=>'count'===$case?1:33554432,'role'=>'windows');break;}
		$p['tool_modules_sha256']=minimum_host_module_digest($rows);$this->expectException(InvalidArgumentException::class);new Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission(str_repeat('a',32),minimum_host_source(),$p,$p,minimum_host_decision($p),minimum_host_bindings(),$rows);
	}
	public static function mirrored_module_cases(){return array_map(function($v){return array($v);},array('duplicate','path','size','type','sparse','missing-kernel','application','count','aggregate'));}
	public function test_exact_module_count_and_byte_ceilings_are_valid_consistency_not_authority() {
		foreach(array('count','bytes') as $limit){$p=minimum_host_profile();$rows=minimum_host_modules();for($i=0;$i<('count'===$limit?125:5);$i++)$rows[]=array('name'=>'x'.$i.'.dll','path'=>$p['system'].'/x'.$i.'.dll','sha256'=>str_repeat('a',64),'size'=>1,'role'=>'windows');if('bytes'===$limit)foreach($rows as &$row)$row['size']=33554432;unset($row);
			$p['tool_modules_sha256']=minimum_host_module_digest($rows);$o=new Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission(str_repeat('a',32),minimum_host_source(),$p,$p,minimum_host_decision($p),minimum_host_bindings(),$rows);$b=minimum_host_bindings();$this->assertTrue($o->worker('held',$b['worker'],0));$this->assertTrue($o->controller('observer-started',$b['worker'],$b['observer'],array(),1));$this->assertTrue($o->controller('modules',$b['worker'],$b['observer'],$rows,2));$this->assertFalse($o->snapshot()['execution']);$this->assertFalse($o->snapshot()['synthetic_complete']);
		}
	}
}
