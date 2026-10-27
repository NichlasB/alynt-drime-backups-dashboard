<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyNativeAdapterCallbackSafetyTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	public function test_even_a_matching_query_callback_manifest_cannot_transform_sql() {
		$invoked=false; policy_isolated_hook('query',function($sql)use(&$invoked){$invoked=true;return $sql;});
		list($owner,$connection,$store)=$this->owner();
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_unavailable');
		$this->assertFalse($invoked); $this->assertSame(0,$connection->physical);
	}
	/** @dataProvider changed_read_profiles */
	public function test_a_changed_profile_during_read_cannot_export_an_editable_revision($kind) {
		list($owner,$connection,$store)=$this->owner();
		$store->after_read=function()use($kind){if('cache'===$kind)$GLOBALS['wp_object_cache']=new stdClass();else policy_isolated_hook('update_option',function(){});};
		$this->error_code($owner->view_source_mode(7,'wpvivid'),'source_policy_unavailable');
		$this->assertSame(1,$store->reads); $this->assertSame(0,$store->attempts); $this->assertSame(1,$connection->closes);
	}
	public static function changed_read_profiles(){return array(array('cache'),array('hook'));}
	public function test_unknown_callback_added_by_sanitizer_is_not_executed() {
		$name=Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES; $invoked=false;
		policy_isolated_hook('sanitize_option_'.$name,function($value)use($name,&$invoked){policy_isolated_hook('pre_update_option_'.$name,function($next)use(&$invoked){$invoked=true;return $next;});return $value;});
		list($owner,$connection,$store)=$this->owner();
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_unavailable');
		$this->assertFalse($invoked); $this->assertSame(0,$store->attempts);
	}
	public function test_unknown_generic_pre_hook_added_by_specific_hook_is_not_executed() {
		$name=Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES; $invoked=false;
		policy_isolated_hook('pre_update_option_'.$name,function($value)use(&$invoked){policy_isolated_hook('pre_update_option',function($next)use(&$invoked){$invoked=true;return $next;});return $value;});
		list($owner,$connection,$store)=$this->owner();
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_unavailable');
		$this->assertFalse($invoked); $this->assertSame(0,$store->attempts);
	}
	public function test_unknown_generic_success_hook_added_after_commit_is_not_executed() {
		$name=Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES; $invoked=false;
		policy_isolated_hook('update_option_'.$name,function()use(&$invoked){policy_isolated_hook('updated_option',function()use(&$invoked){$invoked=true;});});
		list($owner,$connection,$store)=$this->owner();
		$this->error_code($owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'),'source_policy_write_uncertain');
		$this->assertFalse($invoked); $this->assertSame(1,$store->mutations); $this->assertSame(1,$connection->closes);
	}
}
