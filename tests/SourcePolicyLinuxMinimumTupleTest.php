<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-linux-minimum-fixtures.php';

/** Pure expected DATA only: positive consistency is never runtime admission. */
final class SourcePolicyLinuxMinimumTupleTest extends TestCase {
	private const HELPER='Alynt_Drime_Backups_Dashboard_Linux_Minimum_Tuple';
	protected function setUp():void {
		$file=dirname(__DIR__).'/scripts/manual/source-policy-linux-minimum-tuple.php';
		$this->assertFileExists($file,'The approved positive DATA contract must be implemented.');require_once $file;
	}
	public function test_positive_independent_tuple_and_canonical_digest() {
		$c=self::HELPER;$row=linux_minimum_tuple_fixture();$expected=hash('sha256',json_encode($row,JSON_UNESCAPED_SLASHES));
		$this->assertSame($row,$c::template());$this->assertTrue($c::consistent($row));$this->assertSame($expected,$c::digest($row));
		$this->assertTrue($c::consistent(array_reverse($row,true)));$this->assertSame($expected,$c::digest(array_reverse($row,true)));
		$row['core']='7.1.4';$this->assertSame(linux_minimum_tuple_fixture(),$c::template());
		$methods=get_class_methods($c);sort($methods);$this->assertSame(array('consistent','correlates','digest','template'),$methods);
		$pins=require dirname(__DIR__).'/scripts/manual/source-policy-supported-core-pins.php';$this->assertSame($pins['6.0']['commit'],$row['commit']);$this->assertCount(11,$pins['6.0']['pins']);
	}
	public function test_fixed_canonical_bytes_match_json_reference_and_golden_digest() {
		$c=self::HELPER;$reference=json_encode(linux_minimum_tuple_fixture(),JSON_UNESCAPED_SLASHES);
		$method=new ReflectionMethod($c,'canonical');if(PHP_VERSION_ID<80100)$method->setAccessible(true);
		$this->assertSame($reference,$method->invoke(null));$this->assertSame(hash('sha256',$reference),linux_minimum_correlation_fixture()['tuple_hash']);
		$this->assertSame(linux_minimum_correlation_fixture()['tuple_hash'],$c::digest(linux_minimum_tuple_fixture()));
	}
	/** @dataProvider fixed_fields */
	public function test_each_fixed_field_is_exact_typed_and_required($key) {
		$c=self::HELPER;$row=linux_minimum_tuple_fixture();
		foreach(array('different',true,false,null,0,1,3307,3,'3306','0',array(),new stdClass(),str_repeat('x',4097)) as $value){
			if($value===$row[$key])continue;$bad=$row;$bad[$key]=$value;$this->assertFalse($c::consistent($bad),$key);$this->assertFalse($c::digest($bad),$key);
		}
		$bad=$row;unset($bad[$key]);$this->assertFalse($c::consistent($bad));$bad['unknown']=null;$this->assertFalse($c::consistent($bad));
		if(is_string($row[$key]))foreach(array($row[$key].'suffix',$row[$key]."\0",strtoupper($row[$key]).' ') as $value){$bad=$row;$bad[$key]=$value;$this->assertFalse($c::consistent($bad));}
	}
	public static function fixed_fields(){return array_map(function($key){return array($key);},array_keys(linux_minimum_tuple_fixture()));}
	public function test_wrong_shapes_and_hostile_values_have_no_callback_or_conversion() {
		$c=self::HELPER;foreach(array(null,false,'ADBLMT1',new stdClass(),array(),array_fill(0,1000,null)) as $row){$this->assertFalse($c::consistent($row));$this->assertFalse($c::digest($row));}
		$hostile=new class {public function __toString(){throw new RuntimeException('Must not convert');}public function __invoke(){throw new RuntimeException('Must not call');}};
		$row=linux_minimum_tuple_fixture();$row['php']=$hostile;$this->assertFalse($c::consistent($row));
		$wire=linux_minimum_correlation_fixture();$wire['head']=$hostile;$this->assertFalse($c::correlates(linux_minimum_tuple_fixture(),$wire,$wire));
		$row=linux_minimum_tuple_fixture();$row['ready']=true;$this->assertFalse($c::consistent($row));
	}
	public function test_positive_parent_correlation_and_reordering_are_not_observations() {
		$c=self::HELPER;$row=linux_minimum_tuple_fixture();$wire=linux_minimum_correlation_fixture();
		$this->assertTrue($c::correlates($row,$wire,$wire));$this->assertTrue($c::correlates(array_reverse($row,true),array_reverse($wire,true),$wire));
		foreach(array(1,2147483647) as $pid){$wire['pid']=$pid;$this->assertTrue($c::correlates($row,$wire,$wire));}
		$wire['head']=str_repeat('f',40);$this->assertTrue($c::correlates($row,$wire,$wire),'Matching supplied identities prove correlation only.');
		$row['server']='10.6.23-MariaDB';$this->assertFalse($c::correlates($row,$wire,$wire));
	}
	/** @dataProvider transcript_changes */
	public function test_transcript_fields_are_bounded_and_parent_bound($key,$value,$valid_shape) {
		$c=self::HELPER;$row=linux_minimum_tuple_fixture();$wire=linux_minimum_correlation_fixture();$bad=$wire;$bad[$key]=$value;
		$this->assertFalse($c::correlates($row,$bad,$wire));$this->assertFalse($c::correlates($row,$wire,$bad));
		$this->assertSame($valid_shape,$c::correlates($row,$bad,$bad));
	}
	public static function transcript_changes() {
		$cases=array(array('event','closed',false),array('tuple_hash',str_repeat('a',64),false));
		foreach(array('head'=>40,'source_image'=>64,'run'=>32) as $key=>$size){
			$cases[]=array($key,str_repeat('d',$size),true);
			foreach(array(str_repeat('A',$size),str_repeat('a',$size-1),str_repeat('a',$size+1),str_repeat('a',$size-1)."\n",str_repeat('a',$size-1).'g',str_repeat('a',4097),null,true,123,array(),new stdClass()) as $value)$cases[]=array($key,$value,false);
		}
		foreach(array(0,-1,2147483648,'123',true,123.0,null,array(),new stdClass()) as $value)$cases[]=array('pid',$value,false);
		$cases[]=array('pid',124,true);return $cases;
	}
	public function test_complete_transcript_shapes_are_required_on_both_sides() {
		$c=self::HELPER;$row=linux_minimum_tuple_fixture();$wire=linux_minimum_correlation_fixture();
		foreach(array_keys($wire) as $key){$bad=$wire;unset($bad[$key]);$this->assertFalse($c::correlates($row,$bad,$wire));$this->assertFalse($c::correlates($row,$wire,$bad));$bad['other']=null;$this->assertFalse($c::correlates($row,$bad,$bad));}
		$bad=$wire;$bad['native_qualified']=true;$this->assertFalse($c::correlates($row,$bad,$bad));
		foreach(array(null,false,'tuple-data',new stdClass(),array_fill(0,1000,null)) as $bad){$this->assertFalse($c::correlates($row,$bad,$wire));$this->assertFalse($c::correlates($row,$wire,$bad));}
	}
}
