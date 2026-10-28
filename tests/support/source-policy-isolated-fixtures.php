<?php
/** Only in-memory fixture state, never a native provider or authenticated grant. */
if ( defined( 'ADB_POLICY_ISOLATED' ) ) {
class Alynt_Drime_Backups_Dashboard_Test_Policy_Store {
	public $row, $reads=0, $attempts=0, $mutations=0, $ack=1, $read_ack=null, $read_fault=0, $write_fault=0;
	public $lose_ack=false, $throw_after_write=false, $before_write=null, $close_ok=true, $close_throw=false;
	public $after_read=null, $after_prepare=null;
	public function __construct( $map=array(), $autoload='off' ) { $this->row=$map===null?null:array('option_value'=>serialize($map),'autoload'=>$autoload); }
}
class Alynt_Drime_Backups_Dashboard_Test_Policy_Connection extends Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection {
	public function __construct( $store ) { $this->store=$store; $this->ready=true; $this->dbh=true; }
	protected function driver_error_number() { return $this->error_code; }
}
class Alynt_Drime_Backups_Dashboard_Test_Policy_Sites {
	public $calls=array(), $exists=true, $record_override=false;
	public function get( $id ) { $this->calls[]=$id; $GLOBALS['policy_trace'][]='record'; return $this->exists?(false!==$this->record_override?$this->record_override:array('id'=>$id)):null; }
}
class Alynt_Drime_Backups_Dashboard_Test_Policy_Collaborator {
	public $calls=array(), $result='saved', $view;
	public function view_source_mode($site,$source) { $this->calls[]=array($site,$source); return $this->view; }
	public function save_source_mode_if_current(...$args) { $this->calls[]=$args; return $this->result; }
}
class Alynt_Drime_Backups_Dashboard_Test_Policy_Decode_Tripwire {
	public static $magic_calls=0;
	public function __wakeup() { ++self::$magic_calls; PHPUnit\Framework\Assert::fail('No magic decode.'); }
	public function __toString() { ++self::$magic_calls; PHPUnit\Framework\Assert::fail('No object coercion.'); return ''; }
}
}
