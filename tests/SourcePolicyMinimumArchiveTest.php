<?php
use PHPUnit\Framework\TestCase;

/** Actual libzip and disposable filesystem checks; never downloaded/native code. */
final class SourcePolicyMinimumArchiveTest extends TestCase {
	private $owned;
	protected function setUp():void {require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-owned.php';require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-archive.php';$this->assertTrue(extension_loaded('zip'),'Existing dev libzip is required; no skip/install.');$this->owned=new Alynt_Drime_Backups_Dashboard_Minimum_Owned_Files();}
	protected function tearDown():void {if($this->owned){$root=$this->owned->root();$this->owned->cleanup();$this->assertDirectoryDoesNotExist($root);}}
	private function archive(array $rows):ZipArchive {
		$this->owned->write('synthetic.zip','');$zip=new ZipArchive();$this->assertTrue($zip->open($this->owned->path('synthetic.zip'),ZipArchive::OVERWRITE));
		foreach($rows as $row){list($name,$bytes,$mode)=$row;$this->assertTrue($zip->addFromString($name,$bytes));$this->assertTrue($zip->setExternalAttributesName($name,3,($mode<<16)|('/'===substr($name,-1)?0x10:0)));}
		$this->assertTrue($zip->close());$this->assertTrue($zip->open($this->owned->path('synthetic.zip'),ZipArchive::RDONLY|ZipArchive::CHECKCONS));return $zip;
	}
	public function test_real_libzip_positive_bytes_crc_modes_and_core_wrapper() {
		$prefix='WordPress-'.adbmin_catalog()['core_commit'].'/';$zip=$this->archive(array(array($prefix,'',0040755),array($prefix.'a.txt',"owned\0bytes",0100644),array($prefix.'bin/tool','x',0100755)));
		try{$result=adbmin_zip_contents($zip,'core',filesize($this->owned->path('synthetic.zip')),strlen($this->owned->root())+8);$this->assertSame(strlen("owned\0bytes")+1,$result['inventory']['expanded']);$this->assertSame(array('a.txt','bin/tool'),array_column($result['files'],'path'));$this->assertSame(array('100644','100755'),array_column($result['files'],'mode'));$this->assertSame("owned\0bytes",$result['files'][0]['bytes']);}finally{$zip->close();}
	}
	/** @dataProvider hostile_entries */
	public function test_actual_archive_metadata_refuses_unsafe_entries_before_expansion($rows) {
		$zip=$this->archive($rows);try{try{adbmin_zip_contents($zip,'php',filesize($this->owned->path('synthetic.zip')),strlen($this->owned->root())+8);$this->fail('Unsafe archive accepted');}catch(RuntimeException $error){$this->assertContains($error->getMessage(),array('zip-link-type-refused','zip-inventory-refused'));}}finally{$zip->close();}
	}
	public static function hostile_entries(){return array(array(array(array('../escape','x',0100644))),array(array(array('a','x',0120777))),array(array(array('Dir/a','x',0100644),array('dir/b','x',0100644))),array(array(array('a','x',0100644),array('a/b','x',0100644))),array(array(array('a','x',0104644))),array(array(array('CON .txt','x',0100644))));}
	public function test_fixed_php_digest_precedes_zip_decoding() {$this->owned->write('php.zip','not a ZIP');try{adbmin_archive($this->owned,'php');$this->fail('Unpinned ZIP admitted');}catch(RuntimeException $error){$this->assertSame('publisher-digest-refused',$error->getMessage());}}
	public function test_exclusive_write_traversal_and_changed_marker_refusal() {
		$this->owned->write('nested/a','unchanged');foreach(array('nested/a','../escape','C:/escape') as $path){try{$this->owned->write($path,'bad');$this->fail('Unsafe write accepted');}catch(RuntimeException $error){$this->assertContains($error->getMessage(),array('owned-overwrite-refused','owned-path-refused'));}}
		$this->assertSame('unchanged',$this->owned->read('nested/a',20));
		$path=$this->owned->path('.owner');$original=file_get_contents($path);file_put_contents($path,'changed-owned-test-marker');try{try{$this->owned->cleanup();$this->fail('Changed marker accepted');}catch(RuntimeException $error){$this->assertSame('owned-marker-changed',$error->getMessage());$this->assertFileExists($this->owned->path('nested/a'));}}finally{file_put_contents($path,$original);}
	}
	public function test_unknown_owned_entry_stops_cleanup_without_partial_deletion() {
		$this->owned->write('a','keep');$unexpected=$this->owned->root().DIRECTORY_SEPARATOR.'unexpected';file_put_contents($unexpected,'owned-test-only');try{try{$this->owned->cleanup();$this->fail('Untracked deletion permitted');}catch(RuntimeException $error){$this->assertSame('owned-cleanup-inventory-refused',$error->getMessage());$this->assertSame('keep',$this->owned->read('a',10));}}finally{unlink($unexpected);}
	}
	public function test_manual_extraction_is_exclusive_byte_readback_not_php_execution() {
		require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-static.php';
		$payload='<?php throw new RuntimeException("must never execute");';$runtime=array('files'=>array(array('path'=>'payload.php','mode'=>'100644','bytes'=>$payload)));$core=array('files'=>array(array('path'=>'empty.txt','mode'=>'100644','bytes'=>'')));
		$result=adbmin_extract_static($this->owned,$runtime,$core);$this->assertSame(2,$result['files']);$this->assertSame(strlen($payload),$result['bytes']);$this->assertFalse($result['runtime_loading']);$this->assertSame($payload,$this->owned->read('runtime/payload.php',1024));
		try{adbmin_extract_static($this->owned,$runtime,$core);$this->fail('Extraction overwrote a payload');}catch(RuntimeException $error){$this->assertSame('owned-overwrite-refused',$error->getMessage());}
	}
	public function test_libzip_consistency_refuses_disagreeing_local_name_before_expansion() {
		$zip=$this->archive(array(array('a.txt','owned',0100644)));$zip->close();$path=$this->owned->path('synthetic.zip');$bytes=file_get_contents($path);$this->assertSame("PK\x03\x04",substr($bytes,0,4));$bytes[30]='b';file_put_contents($path,$bytes);
		$zip=new ZipArchive();$this->assertNotSame(true,$zip->open($path,ZipArchive::RDONLY|ZipArchive::CHECKCONS));
	}
	public function test_network_entry_is_not_reached_by_inclusion_or_unknown_kind() {
		require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-acquire.php';try{adbmin_download('https://invalid.example/');$this->fail('Caller URL accepted');}catch(RuntimeException $error){$this->assertSame('download-preflight-refused',$error->getMessage());}
		$source=file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-minimum-acquire.php');$this->assertStringContainsString("CURLOPT_FOLLOWLOCATION => false",preg_replace('/ +/',' ',$source));$this->assertStringContainsString("'https' : CURLPROTO_HTTPS",$source);$this->assertStringNotContainsString('CURLOPT_SSL_VERIFYPEER => false',$source);
	}
	public function test_link_guard_precedes_parent_creation_in_actual_owner_source() {
		$source=file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-minimum-owned.php');$begin=strpos($source,'public function path(');$method=substr($source,$begin,strpos($source,'public function write(',$begin)-$begin);$guard=strpos($method,'if ( is_link( $path ) )');$create=strpos($method,'! mkdir( $path, 0700 )');$this->assertNotFalse($guard);$this->assertLessThan($create,$guard,'Dangling links must be refused before any mkdir attempt; source-level ordering, not a Windows reparse proof.');
	}
}
