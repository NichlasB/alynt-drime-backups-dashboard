<?php
use PHPUnit\Framework\TestCase;

/** Complete synthetic Git trees, never installed core or a downloaded archive. */
final class SourcePolicyMinimumTreeTest extends TestCase {
	protected function setUp():void {require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-tree.php';}
	public function test_complete_tree_oid_uses_git_blob_framing_and_directory_sorting() {
		$this->assertSame('4b825dc642cb6eb9a060e54bf8d69288fbee4904',adbmin_tree_oid(array()));
		$entries=array(array('path'=>'hello.txt','mode'=>'100644','bytes'=>"hello\n"));
		$expected=sha1("tree 37\0"."100644 hello.txt\0".hex2bin('ce013625030ba8dba906f756967f9e9ca394464a'));
		$this->assertSame($expected,adbmin_tree_oid($entries));$this->assertFalse(adbmin_core_tree($entries));
		$nested=array(array('path'=>'a/x','mode'=>'100644','bytes'=>'x'),array('path'=>'a.c','mode'=>'100755','bytes'=>'y'));
		$child="100644 x\0".hex2bin(sha1("blob 1\0x"));$tree="100755 a.c\0".hex2bin(sha1("blob 1\0y"))."40000 a\0".hex2bin(sha1('tree '.strlen($child)."\0".$child));
		$this->assertSame(sha1('tree '.strlen($tree)."\0".$tree),adbmin_tree_oid($nested),'Git compares the directory name with a trailing slash, after a.c.');
		$this->assertSame(adbmin_tree_oid($nested),adbmin_tree_oid(array_reverse($nested)));
		$changed=$nested;$changed[0]['bytes']='z';$this->assertNotSame(adbmin_tree_oid($nested),adbmin_tree_oid($changed));
		$changed=$nested;$changed[1]['mode']='100644';$this->assertNotSame(adbmin_tree_oid($nested),adbmin_tree_oid($changed));
	}
	public function test_unknown_modes_collisions_traversal_and_nonprimitive_shapes_are_refused() {
		foreach(array('120000','160000','40000',100644) as $mode)$this->assertFalse(adbmin_tree_oid(array(array('path'=>'x','mode'=>$mode,'bytes'=>'x'))));
		foreach(array(array('x','x'),array('x','X'),array('x','x/y'),array('A/x','a/y'),array('../x','y'),array('x',str_repeat('a/',18).'x')) as $paths){$rows=array();foreach($paths as $path)$rows[]=array('path'=>$path,'mode'=>'100644','bytes'=>'x');$this->assertFalse(adbmin_tree_oid($rows));}
		$this->assertFalse(adbmin_tree_oid(array(array('path'=>'x','mode'=>'100644','bytes'=>new stdClass()))));
		$this->assertFalse(adbmin_tree_oid(array(array('path'=>'x','mode'=>'100644','bytes'=>'x','extra'=>true))));
	}
}
