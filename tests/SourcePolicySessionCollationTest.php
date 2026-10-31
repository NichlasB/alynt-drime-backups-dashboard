<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/includes/class-source-policy-bootstrap-evidence.php';

/** Pure default/unknown-owner DATA checks; no core, native IO or schema mutation. */
final class SourcePolicySessionCollationTest extends TestCase {
	private static function row() {return array(array('db'=>'isolated','ac'=>'1','tx'=>'0','statement_limit'=>'3.000000','row_limit'=>'1','metadata_limit'=>'1','client_charset'=>'utf8mb4','connection_charset'=>'utf8mb4','result_charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_520_ci','mode'=>'NO_ENGINE_SUBSTITUTION'));}
	private static function frame() {return array('core'=>'6.0','database'=>'isolated','table'=>'wp_options');}
	private static function valid($rows,$charset='utf8mb4',$collation='utf8mb4_unicode_520_ci',$receiver=null) {return Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Evidence::session($rows,self::frame(),$charset,$collation,array('NO_ENGINE_SUBSTITUTION'),$receiver);}
	public function test_unscoped_default_remains_strict_even_when_frame_claims_lab_fields() {
		$this->assertTrue(self::valid(self::row()));
		foreach(array('utf8mb4_unicode_ci','utf8mb4_general_ci','',null,new stdClass()) as $collation){$row=self::row();$row[0]['collation']=$collation;$this->assertFalse(self::valid($row,'utf8mb4',$collation));}
		$this->assertFalse(self::valid(self::row(),'utf8'));
	}
	public function test_unknown_owner_is_not_a_callable_profile_provider() {
		$owner=new class {public $calls=0;function minimum_lab_profile(){++$this->calls;throw new RuntimeException('Must not run unknown provider');}function __get($key){++$this->calls;throw new RuntimeException('No unknown property');}};
		foreach(array($owner,new stdClass(),false,'utf8mb4_unicode_ci',array('collation'=>'utf8mb4_unicode_ci')) as $bad)$this->assertFalse(self::valid(self::row(),'utf8mb4','utf8mb4_unicode_520_ci',$bad));
		$this->assertSame(0,$owner->calls);
	}
	public function test_each_session_field_still_requires_exact_qualified_values() {
		foreach(array_keys(self::row()[0]) as $key){$rows=self::row();$rows[0][$key]='wrong';$this->assertFalse(self::valid($rows),$key);}
		foreach(array(array(),array(null),array('not a row'),array(self::row()[0],self::row()[0])) as $rows)$this->assertFalse(self::valid($rows));
	}
	public function test_schema_collation_does_not_follow_connection_collation() {
		$columns=array();foreach(array(array('option_id','bigint(20) unsigned','NO','PRI','auto_increment'),array('option_name','varchar(191)','NO','UNI',''),array('option_value','longtext','NO','',''),array('autoload','varchar(20)','NO','MUL','')) as $values)$columns[]=array_combine(array('name','type','nullable','key_kind','extra'),$values);
		$indexes=array();foreach(array(array('PRIMARY','0','option_id'),array('autoload','1','autoload'),array('option_name','0','option_name')) as $values)$indexes[]=array('name'=>$values[0],'non_unique'=>$values[1],'sequence'=>'1','column_name'=>$values[2],'prefix_length'=>null);
		$table=array(array('engine'=>'InnoDB','collation'=>'utf8mb4_unicode_520_ci'));
		$this->assertTrue(Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Evidence::schema($table,$columns,$indexes));
		$table[0]['collation']='utf8mb4_unicode_ci';$this->assertFalse(Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Evidence::schema($table,$columns,$indexes));
	}
}
