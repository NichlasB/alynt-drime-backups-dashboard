<?php
/** Small core-query/reissue behavioral double. Contains no mysqli/network functions. */
if ( defined( 'ADB_POLICY_ISOLATED' ) ) {
if ( class_exists( 'wpdb', false ) ) throw new RuntimeException( 'Never replace a genuine wpdb.' );
class wpdb {
	public $ready = true, $options = 'wp_options', $last_result = array(), $last_error = '', $error_code = 0;
	public $store, $prepared = array(), $queries = array(), $physical = 0, $reconnects = 0, $closes = 0;
	public function __construct() { throw new RuntimeException( 'A parent/native constructor must never run.' ); }
	public function prepare( $template, ...$args ) { $id = 'isolated:' . count( $this->prepared ); $this->prepared[$id] = array( $template, $args ); return $id; }
	public function query( $sql ) {
		if ( ! $this->ready ) return false;
		if ( ! isset( $this->prepared[$sql] ) ) throw new RuntimeException( 'Only fixed prepared queries are modeled.' );
		$result = $this->physical_query( $this->prepared[$sql] );
		if ( 2006 === $this->error_code ) {
			if ( $this->check_connection() ) return $this->physical_query( $this->prepared[$sql] );
			return false;
		}
		return $result;
	}
	public function check_connection( $allow_bail = true ) { ++$this->reconnects; $this->store->lose_ack = false; return true; }
	public function close() { ++$this->closes; $this->ready = false; if ( $this->store->close_throw ) throw new RuntimeException( 'owned-close-private' ); return $this->store->close_ok; }
	private function physical_query( $prepared ) {
		list( $sql, $args ) = $prepared; ++$this->physical; $this->queries[] = $prepared;
		$this->last_result = array(); $this->last_error = ''; $this->error_code = 0;
		if ( 0 === strpos( $sql, 'SELECT ' ) ) {
			++$this->store->reads;
			if ( $this->store->read_fault ) return $this->failure( $this->store->read_fault );
			if ( $this->store->row !== null ) {
				$row = $this->store->row;
				if ( is_string( $row['option_value'] ) && strlen( $row['option_value'] ) > 16384 ) $row['option_value'] = null;
				$this->last_result = array( (object) $row );
			}
			if($this->store->after_read) { $callback=$this->store->after_read; $this->store->after_read=null; $callback(); }
			return $this->store->read_ack !== null ? $this->store->read_ack : count( $this->last_result );
		}
		++$this->store->attempts;
		if ( $this->store->before_write ) { $callback=$this->store->before_write; $this->store->before_write=null; $callback(); }
		if ( $this->store->write_fault ) return $this->failure( $this->store->write_fault );
		if ( 0 === strpos( $sql, 'INSERT INTO ' ) ) {
			if ( $this->store->row !== null ) return $this->failure( 1062 );
			if ( count($args)!==3 || strpos($sql,'DUPLICATE')!==false || strpos($sql,'IGNORE')!==false ) throw new RuntimeException('Strict insert required.');
			$this->store->row=array('option_value'=>$args[1],'autoload'=>$args[2]);
		} elseif ( 0 === strpos( $sql, 'UPDATE ' ) ) {
			if ( count($args)!==8 ) throw new RuntimeException('Exact predicate parameters required.');
			$row=$this->store->row;
			if ( $row===null ) return 0;
			if ( strpos($sql,'BINARY option_value=BINARY %s')!==false && $row['option_value']!==$args[4] ) return 0;
			if ( strpos($sql,'OCTET_LENGTH(option_value)=%d')!==false && strlen($row['option_value'])!==$args[5] ) return 0;
			if ( strpos($sql,'BINARY autoload=BINARY %s')!==false && $row['autoload']!==$args[6] ) return 0;
			if ( strpos($sql,'OCTET_LENGTH(autoload)=%d')!==false && strlen($row['autoload'])!==$args[7] ) return 0;
			$this->store->row=array('option_value'=>$args[0],'autoload'=>$args[1]);
		} else throw new RuntimeException('Unexpected statement in isolated model.');
		++$this->store->mutations;
		if ( $this->store->throw_after_write ) throw new RuntimeException('owned-private-after-submit');
		if ( $this->store->lose_ack ) return $this->failure(2006);
		return $this->store->ack;
	}
	private function failure( $code ) { $this->error_code=$code; $this->last_error='owned-private-driver-material'; return false; }
}
}
