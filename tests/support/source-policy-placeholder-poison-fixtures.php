<?php
/** Isolated metadata tripwires, never eligible native providers. */
if(defined('ADB_POLICY_ISOLATED')) {
class PolicyPoisonPlaceholder extends Alynt_Drime_Backups_Dashboard_Test_Policy_Connection {
	public function placeholder_escape(){++$GLOBALS['policy_poison_calls'];throw new RuntimeException('owned-private-poison');}
}
class PolicyPoisonAdd extends Alynt_Drime_Backups_Dashboard_Test_Policy_Connection {
	public function add_placeholder_escape($query){++$GLOBALS['policy_poison_calls'];throw new RuntimeException('owned-private-poison');}
}
class PolicyPoisonRemove extends Alynt_Drime_Backups_Dashboard_Test_Policy_Connection {
	public function remove_placeholder_escape($query){++$GLOBALS['policy_poison_calls'];throw new RuntimeException('owned-private-poison');}
}
class PolicyPoisonPrepare extends Alynt_Drime_Backups_Dashboard_Test_Policy_Connection {
	public function prepare($template,...$args){++$GLOBALS['policy_poison_calls'];throw new RuntimeException('owned-private-poison');}
}
class PolicyPoisonEscape extends Alynt_Drime_Backups_Dashboard_Test_Policy_Connection {
	public function _real_escape($value){++$GLOBALS['policy_poison_calls'];throw new RuntimeException('owned-private-poison');}
}
class PolicyReceiverTripwire {
	public function __call($name,$args){++$GLOBALS['policy_poison_calls'];throw new RuntimeException('owned-private-magic');}
	public function __get($name){++$GLOBALS['policy_poison_calls'];throw new RuntimeException('owned-private-magic');}
	public function __invoke(){++$GLOBALS['policy_poison_calls'];throw new RuntimeException('owned-private-magic');}
}
}
