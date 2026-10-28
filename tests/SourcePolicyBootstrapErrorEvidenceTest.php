<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-bootstrap-error-evidence.php';

/** No installed files, WordPress libraries or manual entry points are executed. */
final class SourcePolicyBootstrapErrorEvidenceTest extends TestCase {
	/** Source wiring only; does not execute or qualify the genuine audit. */
	public function test_manual_audit_uses_the_evidence_checker() {
		$source=file_get_contents(dirname(__DIR__).'/scripts/manual/source-policy-bootstrap-error-audit.php');
		$this->assertStringContainsString("require_once __DIR__ . '/source-policy-bootstrap-error-evidence.php';",$source);
		$this->assertStringContainsString('if ( ! Alynt_Drime_Backups_Dashboard_Bootstrap_Error_Evidence::result_matches( $group, $result ) ) {',$source);
	}
	/** @dataProvider result_cases */
	public function test_audit_rejects_vacuous_or_contaminated_error_results($group,$kind,$expected) {
		$result=new WP_Error('source_policy_unavailable');
		if('empty'===$kind)$result=new WP_Error();
		if('wrong-code'===$kind)$result=new WP_Error('source_policy_invalid');
		if('message'===$kind)$result=new WP_Error('source_policy_unavailable','unexpected-message');
		if('data'===$kind)$result=new WP_Error('source_policy_unavailable','',array('raw'=>'owned-private'));
		if('extra-code'===$kind)$result->errors['unexpected']=array('');
		if('malformed-map'===$kind)$result->errors='not-a-map';
		if('subclass'===$kind)$result=new class extends WP_Error { public function get_error_code(){throw new RuntimeException('No virtual getter');} };
		if('true'===$kind)$result=true;
		if('false'===$kind)$result=false;
		if('array'===$kind)$result=array('status'=>'saved');
		$this->assertSame($expected,Alynt_Drime_Backups_Dashboard_Bootstrap_Error_Evidence::result_matches($group,$result));
	}
	public static function result_cases() {
		$cases=array();
		foreach(array('core-construction','factory','admission','private-bootstrap') as $group){
			$cases[]=array($group,'valid',true);
			foreach(array('empty','wrong-code','message','data','extra-code','malformed-map','subclass','true','array') as $kind)$cases[]=array($group,$kind,false);
		}
		return array_merge($cases,array(array('core-classification','true',true),array('core-classification','false',false),array('core-classification','valid',false),array('public-query','false',true),array('public-query','true',false),array('public-query','valid',false),array('unknown','valid',false)));
	}
}
