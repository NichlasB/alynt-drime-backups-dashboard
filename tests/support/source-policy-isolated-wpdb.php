<?php
/** Small core-query/reissue behavioral double. Contains no mysqli/network functions. */
if ( defined( 'ADB_POLICY_ISOLATED' ) ) {
if ( class_exists( 'wpdb', false ) ) throw new RuntimeException( 'Never replace a genuine wpdb.' );
class wpdb {
	public $ready = true, $options = 'wp_options', $last_result = array(), $last_error = '', $error_code = 0;
	public $store, $prepared = array(), $queries = array(), $physical = 0, $reconnects = 0, $closes = 0;
	public $dbh = null, $placeholder_calls = 0;
	private $marker = null;
	private $use_mysqli = false;
	public $num_queries=0, $charset, $collate, $is_mysql, $dbpassword='';
	protected $incompatible_modes=array('NO_ZERO_DATE');
	public function __construct(...$args) {
		if(empty($GLOBALS['policy_bootstrap_model'])) throw new RuntimeException('A parent/native constructor must never run.');
		$GLOBALS['boot_constructor_args']=$args;
		++$GLOBALS['boot_counts']['object']; $this->use_mysqli=empty($GLOBALS['boot_legacy']); $this->ready=false; $this->dbh=null; $this->store=$GLOBALS['boot_store']; boot_event('object'); $this->db_connect(false);
	}
	public function __get($name){return $this->$name;}
	public function parse_db_host($host){
		$GLOBALS['boot_parse_calls']=($GLOBALS['boot_parse_calls']??0)+1;
		if(!empty($GLOBALS['boot_parser_wrong']))return array('127.0.0.1',10005,null,false);
		$known=array('127.0.0.1:10004'=>array('127.0.0.1',10004,null,false),'[::1]:10004'=>array('::1',10004,null,true),'localhost:/tmp/owned.sock'=>array('localhost',null,'/tmp/owned.sock',false));
		$known['127.0.0.1:3306']=array('127.0.0.1',3306,null,false); // Fixed lab API answer, never parser implementation.
		return $known[$host]??false; // Fixed API answers, not a copied core parser.
	}
	public function db_connect($allow_bail=true){unset($allow_bail); ++$GLOBALS['boot_counts']['unexpected_connect']; return false;}
	public function init_charset(){boot_event('init'); $values=$this->determine_charset(DB_CHARSET,DB_COLLATE); $this->charset=$values['charset']; $this->collate=$values['collate'];}
	public function determine_charset($charset,$collate){$GLOBALS['boot_configured_collate']=$collate;$this->has_cap('collation');return array('charset'=>empty($GLOBALS['boot_helper_wrong'])?'utf8mb4':'utf8','collate'=>$collate?:'utf8mb4_unicode_520_ci');}
	public function has_cap($cap){unset($cap); return strlen($this->db_version())>0;}
	public function db_version(){return $this->db_server_info();}
	public function db_server_info(){return mysqli_get_server_info($this->dbh);}
	public function set_charset($dbh,$charset=null,$collate=null){boot_event('charset'); $dbh->charset=$charset??$this->charset; $dbh->collation=$collate??$this->collate;}
	public function set_sql_mode($modes=array()){boot_event('mode-helper'); $excluded=apply_filters('incompatible_sql_modes',$this->incompatible_modes); $this->dbh->mode=implode(',',array_diff($modes,$excluded));}
	public function prepare( $template, ...$args ) {
		$id='isolated:'.count($this->prepared); $this->prepared[$id]=array($template,$args);
		$wire=$this->add_placeholder_escape($this->_real_escape($id.'|'.serialize($args)));
		if($this->store&&$this->store->after_prepare) { $after=$this->store->after_prepare; $this->store->after_prepare=null; $after($this,$template); }
		return $wire;
	}
	public function placeholder_escape() { ++$this->placeholder_calls; if(null===$this->marker) $this->marker='{isolated-'.spl_object_hash($this).'}'; if(false===has_filter('query',array($this,'remove_placeholder_escape'))) add_filter('query',array($this,'remove_placeholder_escape'),0); return $this->marker; }
	public function add_placeholder_escape($query) { return str_replace('%',$this->placeholder_escape(),$query); }
	public function remove_placeholder_escape($query) { return str_replace($this->placeholder_escape(),'%',$query); }
	public function _real_escape($value) { return $this->add_placeholder_escape($value); }
	public function query( $sql ) {
		if ( ! $this->ready ) return false;
		$sql=apply_filters('query',$sql); list($sql,$wire)=explode('|',$sql,2);
		if ( ! isset( $this->prepared[$sql] ) ) throw new RuntimeException( 'Only fixed prepared queries are modeled.' );
		if($wire!==serialize($this->prepared[$sql][1])) throw new RuntimeException('Placeholder pipeline must restore exact parameter bytes.');
		$result = $this->physical_query( $this->prepared[$sql] );
		if ( 2006 === $this->error_code ) {
			if ( $this->check_connection() ) return $this->physical_query( $this->prepared[$sql] );
			return false;
		}
		return $result;
	}
	public function check_connection( $allow_bail = true ) { ++$this->reconnects; $this->store->lose_ack = false; return true; }
	public function close() { ++$this->closes; $this->ready = false; if ( $this->store->close_throw ) throw new RuntimeException( 'owned-close-private' ); if($this->store->close_ok) $this->dbh=null; return $this->store->close_ok; }
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
