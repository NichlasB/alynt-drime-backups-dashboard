<?php
/** Fixed isolated owner reports, not installed/native authentication or receipts. */
function cleanup_fixture_rows() {
	static $rows=null;
	if(null!==$rows)return $rows;
	$built=array();
	foreach(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::cases() as $case){
		$process=proc_open(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode',__DIR__.'/source-policy-cleanup-owner-worker.php',$case),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		PHPUnit\Framework\Assert::assertIsResource($process); fclose($pipes[0]);
		$out=stream_get_contents($pipes[1]); $err=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
		PHPUnit\Framework\Assert::assertSame(0,proc_close($process),$err); PHPUnit\Framework\Assert::assertSame('',$err);
		$row=Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::parse($out); PHPUnit\Framework\Assert::assertIsArray($row,$out);
		$built[$case]=$row;
	}
	$rows=$built;return $rows;
}
function cleanup_fixture_observation($pid,$alive) {return array('pid'=>$pid,'alive'=>$alive,'scope'=>'all-except-observer','sessions'=>0,'transactions'=>0);}
function cleanup_fixture_wire($row) {return implode('|',array_values($row))."\n";}
function cleanup_fixture_counter(&$row,$field,$key,$value) {$keys=Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::counters();$items=explode(',',$row[$field]);$items[array_search($key,$keys,true)]=$value;$row[$field]=implode(',',$items);}
