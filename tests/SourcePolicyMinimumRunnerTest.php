<?php
use PHPUnit\Framework\TestCase;

/** Ordered synthetic runner consistency is never native/provisioning authority. */
final class SourcePolicyMinimumRunnerTest extends TestCase {
	protected function setUp():void {require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-runner.php';require_once __DIR__.'/support/source-policy-minimum-fixtures.php';}
	public function test_complete_ordered_transcript_closes_without_execution() {
		$run=str_repeat('a',32);$image=str_repeat('b',64);$owner=new Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript($run,$image);
		foreach(minimum_test_lines($run,$image) as $index=>$line)$this->assertTrue($owner->feed($line,$index));
		$this->assertSame(array('phase'=>'closed','failed'=>false,'synthetic_complete'=>true,'execution'=>false,'native_qualified'=>false),$owner->snapshot());
		$this->assertFalse($owner->feed(minimum_test_lines($run,$image)[4],6));$this->assertTrue($owner->snapshot()['failed']);
	}
	public function test_bad_order_correlation_framing_types_and_time_are_sticky() {
		$run=str_repeat('a',32);$image=str_repeat('b',64);$lines=minimum_test_lines($run,$image);
		foreach(array($lines[1],str_replace($run,str_repeat('c',32),$lines[0]),str_replace($image,str_repeat('c',64),$lines[0]),str_replace('|2','|3',$lines[0]),str_replace("\n","\r\n",$lines[0]),$lines[0].'extra',str_repeat('x',513),null) as $bad){$owner=new Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript($run,$image);$this->assertFalse($owner->feed($bad,0));$this->assertFalse($owner->feed($lines[0],1));$this->assertFalse($owner->snapshot()['synthetic_complete']);}
		foreach(array(-1,600001,'0',0.0,true) as $bad){$owner=new Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript($run,$image);$this->assertFalse($owner->feed($lines[0],$bad));}
		$owner=new Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript($run,$image);$this->assertTrue($owner->feed($lines[0],5));$this->assertFalse($owner->feed($lines[1],4));
	}
	public function test_profile_is_exact_not_cast_or_switched() {
		$facts=array('os'=>'Windows','php'=>'7.4.33','mysqlnd'=>'7.4.33','int_size'=>8,'zts'=>false,'mode'=>0,'core'=>'6.0','mysqli_internal'=>true,'autoload'=>'no');
		$this->assertTrue(Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript::runtime_data($facts));
		foreach(array('os'=>'Linux','php'=>'8.5.12','mysqlnd'=>'mysqlnd 7.4.33','int_size'=>'8','zts'=>true,'mode'=>3,'core'=>'7.1.3','mysqli_internal'=>false,'autoload'=>'off') as $key=>$value){$bad=$facts;$bad[$key]=$value;$this->assertFalse(Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript::runtime_data($bad));}
		$bad=$facts;$bad['ready']=true;$this->assertFalse(Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript::runtime_data($bad));unset($facts['mode']);$this->assertFalse(Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript::runtime_data($facts));
	}
	public function test_every_stage_refuses_bad_claims_and_bad_constructor_correlation() {
		$run=str_repeat('a',32);$image=str_repeat('b',64);$lines=minimum_test_lines($run,$image);
		foreach($lines as $stage=>$line){$owner=new Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript($run,$image);for($index=0;$index<$stage;++$index)$this->assertTrue($owner->feed($lines[$index],$index));$this->assertFalse($owner->feed(str_replace('|','!',$line),$stage));$this->assertFalse($owner->feed($line,$stage));}
		foreach(array(array('', $image),array($run,str_repeat('B',64)),array(null,$image),array($run,array())) as $bad){try{new Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript(...$bad);$this->fail('Invalid correlation accepted');}catch(InvalidArgumentException $error){$this->assertSame('Minimum synthetic correlation refused',$error->getMessage());}}
	}
}
