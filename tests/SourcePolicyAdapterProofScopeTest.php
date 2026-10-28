<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-adapter-proof-scope.php';
final class SourcePolicyAdapterProofScopeTest extends TestCase {
 /** @dataProvider statements */
 public function test_exact_owned_peer_catalog_rejects_cross_target_extra_sql_and_coercion($sql,$table,$expected){$this->assertSame($expected,adba_sql_scope($sql,$table));}
 public static function statements(){
  $t='adbb_0123456789ab_options';$insert="INSERT INTO $t (option_name,option_value,autoload) VALUES ('alynt_drime_backups_dashboard_source_policies',X'613a303a7b7d','off')";
  $rows=array();foreach(array('START TRANSACTION','ROLLBACK','SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1',"DELETE FROM $t","SELECT option_value,autoload FROM $t","SELECT option_id FROM $t FOR UPDATE",$insert) as $sql)$rows[]=array($sql,$t,true);
  foreach(array('DELETE FROM wp_options',$insert.';',str_replace('off','yes',$insert),str_replace("X'613a303a7b7d'",'NULL',$insert),"SELECT * FROM $t","DELETE FROM $t; DELETE FROM wp_options","DELETE FROM $t\n",new stdClass(),str_repeat('x',33001)) as $sql)$rows[]=array($sql,$t,false);
  $rows[]=array(str_replace('613a303a7b7d','123',$insert),$t,false);
  foreach(array('wp_options',"$t;DROP",strtoupper($t),new stdClass()) as $table)$rows[]=array('ROLLBACK',$table,false);return $rows;
 }
 public function test_unapproved_native_entry_has_no_output_or_core_load(){
  $p=proc_open(array(PHP_BINARY,'-n',dirname(__DIR__).'/scripts/manual/source-policy-adapter-owned-proof.php','unapproved'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);$this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame(1,proc_close($p));$this->assertSame('',$out);$this->assertSame('',$err);
 }
}
