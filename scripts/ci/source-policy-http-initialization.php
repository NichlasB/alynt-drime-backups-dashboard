<?php
/**
 * Dormant socket-free initializer query admission; no SQL executor or WP hooks.
 *
 * Supplied tickets/preflight facts are DATA, not a native execution grant.
 * The finite candidate grammar must still qualify against genuine core later.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Single-use, per-query remaining-credit model. Never returns a DB handle. */
final class ADBH_Initialization_Query_Guard {

	/**
	 * Private copied scalar ticket.
	 *
	 * @var array|null
	 */
	private $ticket;
	/**
	 * Derived owned options table.
	 *
	 * @var string
	 */
	private $table;
	/**
	 * Last supplied virtual timestamp.
	 *
	 * @var int
	 */
	private $last;
	/**
	 * Query credits consumed by this child.
	 *
	 * @var int
	 */
	private $used = 0;
	/**
	 * Failure/completion is terminal.
	 *
	 * @var bool
	 */
	private $closed = false;

	/**
	 * Require the versioned child ticket and explicit fresh CLI DATA preconditions.
	 *
	 * @param array $ticket Bound private credit DATA.
	 * @param array $facts Isolated preflight DATA; not runtime observation.
	 * @param int   $at Virtual elapsed milliseconds under the original job clock.
	 */
	public function __construct( $ticket, $facts, $at ) {
		$this->check( null === $this->ticket && ! $this->closed, 'initialization-reentry' );
		$keys = array( 'model', 'run', 'head', 'image', 'owner', 'manifest', 'anchors', 'role', 'setup_sql_used', 'sql_remaining', 'setup_started', 'setup_deadline' );
		$this->check( is_array( $ticket ) && count( $ticket ) === count( $keys ) && ! array_diff( $keys, array_keys( $ticket ) ), 'initialization-ticket' );
		$this->check( 'owned-http-l714-initialized-v2' === $ticket['model'] && 'post-install-bootstrap' === $ticket['role'], 'initialization-role' );
		foreach ( array(
			'run'      => 32,
			'head'     => 40,
			'image'    => 64,
			'owner'    => 64,
			'manifest' => 64,
			'anchors'  => 64,
		) as $key => $length ) {
			$this->check( is_string( $ticket[ $key ] ) && 1 === preg_match( '/\A[a-f0-9]{' . $length . '}\z/', $ticket[ $key ] ), 'initialization-binding' );
		}
		$this->check( is_int( $ticket['setup_sql_used'] ) && $ticket['setup_sql_used'] > 0 && $ticket['setup_sql_used'] < 1024 && is_int( $ticket['sql_remaining'] ) && 1024 - $ticket['setup_sql_used'] === $ticket['sql_remaining'], 'initialization-credit' );
		$this->check( is_int( $ticket['setup_started'] ) && $ticket['setup_started'] >= 0 && $ticket['setup_started'] <= 510000 && is_int( $ticket['setup_deadline'] ) && 60000 === $ticket['setup_deadline'] - $ticket['setup_started'] && is_int( $at ) && $at >= $ticket['setup_started'] && $at < $ticket['setup_deadline'], 'initialization-time' );
		$this->check(
			array(
				'sapi'                     => 'cli',
				'before_bootstrap'         => true,
				'installing_override'      => false,
				'short_init_override'      => false,
				'protected_cache_absent'   => true,
				'protected_non_autoloaded' => true,
				'owner_absent'             => true,
				'ddl_sealed'               => true,
			) === $facts,
			'initialization-preflight'
		);
		$this->ticket = array();
		foreach ( $keys as $key ) {
			$this->ticket[ $key ] = $ticket[ $key ];
		}
		$this->table = 'adbh_' . substr( $ticket['run'], 0, 12 ) . '_options';
		$this->last  = $at;
	}

	/**
	 * Poison on any refusal, before a caller could execute returned same-byte SQL.
	 *
	 * @param bool   $ok Required condition.
	 * @param string $label Fixed non-secret reason.
	 * @return void
	 * @throws RuntimeException Fixed refusal, with no caller-controlled text.
	 */
	private function check( $ok, $label ) {
		if ( ! $ok ) {
			$this->closed = true;
			throw new RuntimeException( $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Private fixed labels only; no HTML sink or caller-controlled message.
		}
	}

	/**
	 * Exact key grammar, not prefix-based SQL authorization or a write executor.
	 *
	 * @param mixed $key Decoded option name.
	 * @return bool
	 */
	private function core_key( $key ) {
		return is_string( $key ) && 1 === preg_match( '/\A[a-zA-Z0-9_:-]{1,191}\z/', $key ) && false === stripos( $key, 'alynt_drime' ) && false === stripos( $key, 'adbh_' ) && 'owned_non_target' !== strtolower( $key );
	}

	/**
	 * Require literal key spelling; tokenizer escape projection is not SQL decoding.
	 *
	 * @param string $sql Bounded candidate bytes.
	 * @param string $key Projected key.
	 * @param int    $index Literal position fixed by the admitted shape.
	 * @return bool
	 */
	private function literal_key( $sql, $key, $index ) {
		preg_match_all( "/'(?:[^'\\\\]|\\\\.)*'/s", $sql, $literals );
		return isset( $literals[0][ $index ] ) && "'" . $key . "'" === $literals[0][ $index ];
	}

	/**
	 * Finite options-only candidate: no DDL, users/sessions, joins or broad writes.
	 * Protected rows must be non-autoloaded; that fact needs future native proof.
	 *
	 * @param mixed $sql Candidate core bytes, never executed here.
	 * @return bool
	 */
	private function allows( $sql ) {
		$tokens = adbh_owned_tokens( $sql );
		if ( null === $tokens ) {
			return false;
		}
		list( $shape, $values ) = $tokens;
		$table                  = preg_quote( $this->table, '/' );
		// Pinned wpdb validates option field charset/length before a core write.
		// Column metadata only: no option rows, other tables, DDL or extra clauses.
		if ( 'SHOW FULL COLUMNS FROM ' . $this->table === $shape ) {
			return true;
		}
		if ( in_array( $shape, array( 'SELECT @@SESSION.sql_mode', 'SELECT VERSION()', 'SELECT FOUND_ROWS()' ), true ) ) {
			return true;
		}
		if ( preg_match( '/\ASELECT option_name, option_value FROM ' . $table . ' WHERE autoload IN \(\s*\?,\s*\?,\s*\?,\s*\?\s*\)\z/', $shape ) ) {
			return array( 'yes', 'on', 'auto-on', 'auto' ) === $values;
		}
		if ( preg_match( '/\ASELECT option_value FROM ' . $table . ' WHERE option_name = \?(?: LIMIT 1)?\z/', $shape ) ) {
			return 1 === count( $values ) && $this->core_key( $values[0] ) && $this->literal_key( $sql, $values[0], 0 );
		}
		$key = null;
		if ( preg_match( '/\AINSERT INTO ' . $table . ' \(option_name, option_value, autoload\) VALUES \(\?, \?, \?\)(?: ON DUPLICATE KEY UPDATE option_name = VALUES\(option_name\), option_value = VALUES\(option_value\), autoload = VALUES\(autoload\))?\z/', $shape ) && 3 === count( $values ) ) {
			$key = $values[0];
		} elseif ( preg_match( '/\AUPDATE ' . $table . ' SET option_value = \?(?:, autoload = \?)? WHERE option_name = \?\z/', $shape ) && in_array( count( $values ), array( 2, 3 ), true ) ) {
			$key = end( $values );
		} elseif ( 'DELETE FROM ' . $this->table . ' WHERE option_name = ?' === $shape && 1 === count( $values ) ) {
			$key = $values[0];
		}
		// These are protected fixture identity/configuration anchors, not setup keys.
		$index = 0 === strpos( $shape, 'UPDATE ' ) ? count( $values ) - 1 : 0;
		return $this->core_key( $key ) && $this->literal_key( $sql, $key, $index ) && ! in_array( strtolower( $key ), array( 'siteurl', 'home', 'active_plugins', 'template', 'stylesheet', 'current_theme', 'db_version', 'initial_db_version' ), true ) && ! preg_match( '/(?:user_roles|capabilities|session_tokens)\z/i', $key );
	}

	/**
	 * Charge BEFORE returning executable bytes; clock/credit cannot be reset.
	 *
	 * @param mixed $sql Candidate bytes.
	 * @param int   $at Original virtual clock.
	 * @return string
	 */
	public function filter( $sql, $at ) {
		$this->check( ! $this->closed, 'initialization-closed' );
		$this->check( is_int( $at ) && $at >= $this->last && $at < $this->ticket['setup_deadline'], 'initialization-time' );
		$this->check( $this->used < $this->ticket['sql_remaining'], 'initialization-credit' );
		$this->check( $this->allows( $sql ), 'initialization-sql' );
		++$this->used;
		$this->last = $at;
		return $sql;
	}

	/**
	 * Close this model, not genuine shutdown/authentication/release attestation.
	 *
	 * @return int Consumed initializer SQL credits.
	 */
	public function finish() {
		$this->check( ! $this->closed && $this->used > 0, 'initialization-finish' );
		$this->closed = true;
		return $this->used;
	}
}

// Definitions only; inclusion performs no query, bootstrap or hook registration.
require_once __DIR__ . '/source-policy-http-owned-observer.php';
