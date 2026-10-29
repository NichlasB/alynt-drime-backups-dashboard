<?php
use PHPUnit\Framework\TestCase;

/** Synthetic import observations/refusals, never a DLL loader or runtime proof. */
final class SourcePolicyMinimumImportsTest extends TestCase {
	protected function setUp():void {require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-static.php';require_once __DIR__.'/support/source-policy-minimum-fixtures.php';}
	private function archive() {return array('files'=>array(array('path'=>'php.exe','bytes'=>minimum_test_pe()),array('path'=>'php7.dll','bytes'=>minimum_test_pe(true)),array('path'=>'ext/php_mysqli.dll','bytes'=>minimum_test_pe(true))));}
	public function test_actual_selected_parser_emits_only_fixed_bounded_names_and_hashes() {
		$archive=$this->archive();$pe=adbmin_selected_pe($archive);$this->assertCount(3,$pe);
		foreach($pe as $index=>$image){$this->assertSame($archive['files'][$index]['path'],$image['role']);$this->assertSame(array('kernel32.dll'),$image['imports']);$this->assertSame(hash('sha256',$archive['files'][$index]['bytes']),$image['sha256']);$this->assertFalse($image['load_authorized']);$this->assertSame(array('role','machine','format','imports','load_authorized','sha256'),array_keys($image));}
	}
	/** @dataProvider selected_refusals */
	public function test_selected_role_inventory_and_parser_guards_are_not_optional($change,$expected) {
		$archive=$this->archive();if('missing'===$change)array_pop($archive['files']);elseif('case'===$change)$archive['files'][0]['path']='PHP.exe';elseif('duplicate'===$change)$archive['files'][]=$archive['files'][0];elseif('object'===$change)$archive['files'][0]['bytes']=new stdClass;elseif('wrong-kind'===$change)$archive['files'][0]['bytes']=minimum_test_pe(true);else $archive['files'][0]['bytes'][0]='X';
		try{adbmin_selected_pe($archive);$this->fail('Selected guard bypassed');}catch(RuntimeException $error){$this->assertSame($expected,$error->getMessage());}
	}
	public static function selected_refusals(){return array(array('missing','selected-inventory-refused'),array('case','selected-role-missing'),array('duplicate','selected-inventory-refused'),array('object','selected-inventory-refused'),array('wrong-kind','selected-pe-structure-refused'),array('malformed','selected-pe-structure-refused'));}
	private function with_import($name) {$archive=$this->archive();$archive['files'][1]['bytes']=substr_replace($archive['files'][1]['bytes'],$name."\0",576,strlen($name)+1);return $archive;}
	public function test_observed_exact_imagehlp_is_external_unverified_not_load_authority() {
		$result=adbmin_selected_import_identity($this->with_import('IMAGEHLP.dll'));$this->assertSame(array('imagehlp.dll'=>'presence-and-transitive-closure-unverified','kernel32.dll'=>'presence-and-transitive-closure-unverified'),$result['external_direct_imports']);$this->assertSame(array(),$result['bundled_direct_imports']);$this->assertFalse($result['dependency_closure_qualified']);$this->assertFalse($result['runtime_loading']);
	}
	/** @dataProvider unknown_imports */
	public function test_unknown_lookalike_and_unreviewed_api_imports_remain_refused($name) {
		try{adbmin_selected_import_identity($this->with_import($name));$this->fail('Unknown import accepted');}catch(RuntimeException $error){$this->assertSame('unexpected-import-refused',$error->getMessage());}
	}
	public static function unknown_imports(){return array(array('imagehlp2.dll'),array('imagehlp.dll.evil.dll'),array('dbghelp.dll'),array('unknown.dll'),array('api-ms-win-core-unknown-l1-1-0.dll'),array('api-ms-win-crt-runtime-l1-2-0.dll'));}
	public function test_existing_crt_and_pinned_archive_direct_dependency_classification_remain_distinct() {
		$archive=$this->with_import('api-ms-win-crt-runtime-l1-1-0.dll');$archive['files'][0]['bytes']=substr_replace($archive['files'][0]['bytes'],"PHP7.dll\0",576,9);$result=adbmin_selected_import_identity($archive);
		$this->assertSame(array('php7.dll'=>hash('sha256',$archive['files'][1]['bytes'])),$result['bundled_direct_imports']);$this->assertSame(array('api-ms-win-crt-runtime-l1-1-0.dll'=>'presence-and-transitive-closure-unverified','kernel32.dll'=>'presence-and-transitive-closure-unverified'),$result['external_direct_imports']);$this->assertFalse($result['dependency_closure_qualified']);
	}
	public function test_complete_core_guard_still_precedes_selected_binary_parsing() {
		try{adbmin_static_identity(array('files'=>array()),array('files'=>array()));$this->fail('Core identity guard bypassed');}catch(RuntimeException $error){$this->assertSame('complete-core-tree-refused',$error->getMessage());}
	}
	/** @dataProvider semantic_controls */
	public function test_in_memory_policy_controls_are_assertion_killed_without_editing_source($mutation,$status) {
		$file=dirname(__DIR__).'/scripts/manual/source-policy-minimum-static.php';$digest=hash_file('sha256',$file);
		$find=array('known'=>"'imagehlp.dll', ",'unknown'=>"throw new RuntimeException( 'unexpected-import-refused' );",'core'=>"if ( ! adbmin_core_tree( \$core['files'] ) ) {\n\t\tthrow new RuntimeException( 'complete-core-tree-refused' );\n\t}");
		$code='$s=file_get_contents($argv[1]);$find='.var_export($find,true).';if("none"!==$argv[2]){$s=str_replace($find[$argv[2]],"",$s,$n);if(1!==$n)exit(2);}$s=str_replace("__DIR__",var_export(dirname($argv[1]),true),substr($s,5));eval($s);require $argv[3];';
		$code.='$files=array(array("path"=>"php.exe","bytes"=>minimum_test_pe()),array("path"=>"php7.dll","bytes"=>minimum_test_pe(true)),array("path"=>"ext/php_mysqli.dll","bytes"=>minimum_test_pe(true)));$ok=true;foreach(array("imagehlp.dll"=>true,"unknown.dll"=>false) as $name=>$expected){$p=$files;$p[1]["bytes"]=substr_replace($p[1]["bytes"],$name."\0",576,strlen($name)+1);try{$r=adbmin_selected_import_identity(array("files"=>$p));$actual=true;if($expected)$ok=$ok&&isset($r["external_direct_imports"][$name])&&!$r["dependency_closure_qualified"]&&!$r["runtime_loading"];}catch(RuntimeException $e){$actual=false;$ok=$ok&&"unexpected-import-refused"===$e->getMessage();}$ok=$ok&&$actual===$expected;}try{adbmin_static_identity(array("files"=>array()),array("files"=>array()));$ok=false;}catch(RuntimeException $e){$ok=$ok&&"complete-core-tree-refused"===$e->getMessage();}echo $ok?"contract-pass":"contract-killed";exit($ok?0:1);';
		$process=proc_open(array(PHP_BINARY,'-n','-r',$code,$file,$mutation,__DIR__.'/support/source-policy-minimum-fixtures.php'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame($status,proc_close($process),$err);$this->assertSame('',$err);$this->assertSame(0===$status?'contract-pass':'contract-killed',$out);$this->assertSame($digest,hash_file('sha256',$file));
	}
	public static function semantic_controls(){return array(array('none',0),array('known',1),array('unknown',1),array('core',1));}
	public function test_diagnostic_inclusion_is_inert_and_does_not_call_admission_or_extraction() {
		ob_start();require_once dirname(__DIR__).'/scripts/manual/source-policy-minimum-import-diagnostic.php';$this->assertSame('',ob_get_clean());$source=file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-minimum-import-diagnostic.php');
		foreach(array("adbmin_download( 'php' )","! adbmin_php_zip( \$bytes )","adbmin_archive( \$owner, 'php' )","adbmin_selected_pe( \$archive )",'$owner->cleanup();',"'confirmed' === \$report['cleanup']") as $guard)$this->assertStringContainsString($guard,$source);
		foreach(array('adbmin_static_identity(','adbmin_extract_static(','curl_exec(','proc_open(','include ','eval(') as $forbidden)$this->assertStringNotContainsString($forbidden,$source);
		$this->assertLessThan(strpos($source,"\$owner->write( 'php.zip'"),strpos($source,'! adbmin_php_zip( $bytes )'));
	}
}
