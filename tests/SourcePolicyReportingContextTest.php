<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-isolated-test-case.php';

/** RAM-only active-owner models, not a genuine shared mysqli driver proof.
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SourcePolicyReportingContextTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Policy_Isolated_Test_Case;
	/** @dataProvider stages */
	public function test_changed_reporting_context_cannot_grant_or_replay($stage,$reads,$writes,$code) {
		list($owner,$connection,$store)=$this->owner();$revision=$this->revision($store);
		$change=static function(){$GLOBALS['policy_report_mode']=0;};
		if('entry'===$stage)$change();
		if('read-prepare'===$stage)$store->after_prepare=$change;
		if('read-return'===$stage)$store->after_read=$change;
		if('write-prepare'===$stage)$store->after_read=static function()use($store,$change){$store->after_prepare=$change;};
		if(in_array($stage,array('write-return','write-zero','write-error'),true))$store->before_write=$change;
		if('write-zero'===$stage)$store->ack=0;
		if('write-error'===$stage)$store->write_fault=1064;
		$result=$owner->save_source_mode_if_current($revision,7,'wpvivid','external_optional');
		$this->error_code($result,$code);$this->assertSame($reads,$store->reads);$this->assertSame($writes,$store->attempts);$this->assertSame(0,$connection->reconnects);$this->assertSame(1,$connection->closes);
		$this->assertSame('write-return'===$stage||'write-zero'===$stage?1:0,$store->mutations);
		$this->assertFalse(has_filter('query',array($connection,'remove_placeholder_escape')));$this->assertNull($connection->profile());
		$this->assertSame(0,$GLOBALS['policy_report_mode'],'Never toggle/restore shared reporting.');
		$this->error_code($owner->save_source_mode_if_current($revision,7,'wpvivid','external_optional'),'source_policy_unavailable');$this->assertSame($writes,$store->attempts);
	}
	public static function stages(){return array(array('entry',0,0,'source_policy_unavailable'),array('read-prepare',0,0,'source_policy_unavailable'),array('read-return',1,0,'source_policy_unavailable'),array('write-prepare',1,0,'source_policy_unavailable'),array('write-return',1,1,'source_policy_write_uncertain'),array('write-zero',1,1,'source_policy_write_uncertain'),array('write-error',1,1,'source_policy_write_uncertain'));}
	public function test_unchanged_reporting_allows_real_ram_save_before_terminal_revocation() {
		list($owner,$connection,$store)=$this->owner();
		$this->assertSame('saved',$owner->save_source_mode_if_current($this->revision($store),7,'wpvivid','external_optional'));
		$this->assertSame(1,$store->mutations);$this->assertSame(1,$connection->closes);$this->assertSame(3,$GLOBALS['policy_report_mode']);
	}
}
