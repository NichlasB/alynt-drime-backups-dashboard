<?php
use PHPUnit\Framework\TestCase;

/** Synthetic Git object integrity, never a caller-selected admission root. */
final class SourcePolicyMinimumCoreModesTest extends TestCase {
	protected function setUp():void {require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-core-modes.php';}
	private function fixture():array {
		$blob=sha1("blob 1\0x");$child="100755 run.sh\0".hex2bin($blob);$child_sha=sha1('tree '.strlen($child)."\0".$child);$root="100644 a.txt\0".hex2bin($blob)."40000 dir\0".hex2bin($child_sha);
		return array('sha'=>sha1('tree '.strlen($root)."\0".$root),'truncated'=>false,'tree'=>array(array('path'=>'a.txt','mode'=>'100644','type'=>'blob','sha'=>$blob),array('path'=>'dir','mode'=>'040000','type'=>'tree','sha'=>$child_sha),array('path'=>'dir/run.sh','mode'=>'100755','type'=>'blob','sha'=>$blob)));
	}
	public function test_hashes_each_git_tree_and_preserves_authenticated_mode_data() {
		$input=$this->fixture();$facts=Alynt_Drime_Backups_Dashboard_Minimum_Core_Modes::inspect($input);$this->assertIsArray($facts);$this->assertSame('100644',$facts['files']['a.txt']['mode']);$this->assertSame('100755',$facts['files']['dir/run.sh']['mode']);$this->assertCount(2,$facts['directories']);
		$input['tree']=array_reverse($input['tree']);$this->assertSame($facts,Alynt_Drime_Backups_Dashboard_Minimum_Core_Modes::inspect($input));
		$this->assertFalse(Alynt_Drime_Backups_Dashboard_Minimum_Core_Modes::from_metadata($input),'A consistent synthetic root is not the pinned core root.');
		$this->assertTrue((new ReflectionClass(Alynt_Drime_Backups_Dashboard_Minimum_Core_Modes::class))->getConstructor()->isPrivate());
	}
	public function test_changed_missing_unknown_and_ambiguous_graphs_are_refused() {
		$good=$this->fixture();$cases=array();$bad=$good;$bad['truncated']=true;$cases[]=$bad;$bad=$good;$bad['sha']=str_repeat('0',40);$cases[]=$bad;
		foreach(array('mode'=>'100755','sha'=>str_repeat('0',40),'type'=>'commit','path'=>'../a') as $key=>$value){$bad=$good;$bad['tree'][0][$key]=$value;$cases[]=$bad;}
		$bad=$good;unset($bad['tree'][1]);$cases[]=$bad;$bad=$good;$bad['tree'][]=$bad['tree'][0];$cases[]=$bad;$bad=$good;$bad['tree'][2]['path']='Dir/run.sh';$cases[]=$bad;
		foreach(array('120000','160000',100644) as $mode){$bad=$good;$bad['tree'][0]['mode']=$mode;$cases[]=$bad;}
		$bad=$good;$bad['tree']=array_fill(0,8193,$good['tree'][0]);$cases[]=$bad;
		foreach($cases as $case)$this->assertFalse(Alynt_Drime_Backups_Dashboard_Minimum_Core_Modes::inspect($case));
	}
}
