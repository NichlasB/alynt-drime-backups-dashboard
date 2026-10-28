<?php
/**
 * Dormant proof-local DATA contract. No runtime caller or native grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Exact bytes only; matching a report does not authenticate its producer. */
final class Alynt_Drime_Backups_Dashboard_Cleanup_Evidence {
	const PROFILE = 'wp713-php8512-mysqlnd8512-mariadb10623-win64';
	const WRAP    = '18446744073709551615';

	/** Fixed case order, never caller-selected commands.
	 *
	 * @return array
	 */
	public static function cases() {
		return array( 'default', 'wrong-source', 'all', 'incompatible_sql_modes', 'missing-database', 'missing-table', 'positive' );
	}

	/** Fixed scalar wire field order.
	 *
	 * @return array
	 */
	public static function fields() {
		return explode( ',', 'version,run,case,profile,source,pid,before,bootstrap,after,allocations,outcome,active,ack,close_calls,cached,identity,ambient,dbh_null,qualification_null,frame_clean,callback_clean,results_clean,closed_probe,state,observer_calls,steps,queries,roundtrip,seal,elapsed' );
	}

	/** Native-counter order; no unsigned-to-integer conversion.
	 *
	 * @return array
	 */
	public static function counters() {
		return explode( ',', 'connect_success,connect_failure,com_query,result_set_queries,explicit_free_result,implicit_free_result,unbuffered_sets,reconnect,connection_reused,pconnect_success,active_connections' );
	}

	/**
	 * Parses one bounded ASCII record without JSON, unserialize or coercion.
	 *
	 * @param mixed $wire Complete newline-terminated record.
	 * @return array|false Scalar DATA only.
	 */
	public static function parse( $wire ) {
		if ( ! is_string( $wire ) || strlen( $wire ) > 16384 || 1 !== preg_match( '/\A[A-Za-z0-9_,|.-]+\n\z/', $wire ) ) {
			return false;
		}
		$fields = self::fields();
		$values = explode( '|', substr( $wire, 0, -1 ), count( $fields ) + 1 );
		if ( count( $values ) !== count( $fields ) ) {
			return false;
		}
		foreach ( $values as $value ) {
			if ( '' === $value || strlen( $value ) > 256 ) {
				return false;
			}
		}
		return array_combine( $fields, $values );
	}

	/**
	 * Checks a proof-local case, never production admission or source provenance.
	 *
	 * @param mixed  $row Parsed DATA.
	 * @param string $run Expected private run correlation.
	 * @param string $case_name Fixed scheduled case.
	 * @param string $source Expected source correlation, not authentication.
	 * @param int    $pid Owned child correlation, not a process capability.
	 * @return bool
	 */
	public static function matches( $row, $run, $case_name, $source, $pid ) {
		if ( ! is_array( $row ) || array_keys( $row ) !== self::fields() || ! in_array( $case_name, self::cases(), true ) || ! is_int( $pid ) || $pid < 1
			|| ! is_string( $run ) || 1 !== preg_match( '/\A[a-f0-9]{32}\z/', $run ) || ! is_string( $source ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $source ) ) {
			return false;
		}
		foreach ( $row as $value ) {
			if ( ! is_string( $value ) || strlen( $value ) > 256 ) {
				return false;
			}
		}
		$preflight = in_array( $case_name, array_slice( self::cases(), 0, 4 ), true );
		$positive  = 'positive' === $case_name;
		$connected = in_array( $case_name, array( 'missing-table', 'positive' ), true );
		$expected  = array(
			'version'            => 'ADBC1',
			'run'                => $run,
			'case'               => $case_name,
			'source'             => $source,
			'pid'                => (string) $pid,
			'profile'            => self::PROFILE,
			'allocations'        => $preflight ? '0' : '1',
			'outcome'            => $positive ? 'active' : 'unavailable',
			'active'             => $positive ? '1' : '0',
			'ack'                => $preflight ? '0' : '1',
			'close_calls'        => $preflight ? '0' : '1',
			'cached'             => '1',
			'identity'           => '1',
			'ambient'            => '0',
			'dbh_null'           => '1',
			'qualification_null' => '1',
			'frame_clean'        => '1',
			'callback_clean'     => '1',
			'results_clean'      => '1',
			'closed_probe'       => $preflight ? '0' : '1',
			'state'              => $preflight ? 'preflight' : 'closed',
			'observer_calls'     => '0',
			'steps'              => $connected ? '8' : '0',
			'queries'            => $positive ? '1' : '0',
			'roundtrip'          => $positive ? '1' : '0',
			'seal'               => $positive ? '1' : '0',
		);
		foreach ( $expected as $key => $value ) {
			if ( $row[ $key ] !== $value ) {
				return false;
			}
		}
		$zero = implode( ',', array_fill( 0, count( self::counters() ), '0' ) );
		if ( $row['before'] !== $zero || ! self::small( $row['elapsed'], 20000 ) ) {
			return false;
		}
		$bootstrap = self::counter_map( $row['bootstrap'] );
		$after     = self::counter_map( $row['after'] );
		if ( false === $bootstrap || false === $after ) {
			return false;
		}
		$want = array_fill_keys( self::counters(), '0' );
		if ( $connected ) {
			if ( ! self::small( $bootstrap['com_query'], 16 ) || (int) $bootstrap['com_query'] < 7 ) {
				return false;
			}
			$want['connect_success']      = '1';
			$want['com_query']            = $bootstrap['com_query'];
			$want['result_set_queries']   = '6';
			$want['explicit_free_result'] = '6';
			$want['active_connections']   = $positive ? '1' : '0';
		} elseif ( ! $preflight ) {
			$want['connect_failure']    = '1';
			$want['active_connections'] = self::WRAP;
		}
		if ( $bootstrap !== $want ) {
			return false;
		}
		if ( $positive ) {
			$want['com_query']            = (string) ( (int) $want['com_query'] + 1 ); // Separate fixed post-bootstrap round-trip command.
			$want['result_set_queries']   = '7';
			$want['explicit_free_result'] = '7';
			$want['active_connections']   = '0';
		}
		return $after === $want;
	}

	/**
	 * Validates a small decimal before bounded arithmetic.
	 *
	 * @param mixed $value Exact decimal bytes.
	 * @param int   $max Fixed small maximum.
	 * @return bool
	 */
	private static function small( $value, $max ) {
		return is_string( $value ) && 1 === preg_match( '/\A(?:0|[1-9][0-9]{0,4})\z/', $value ) && (int) $value <= $max;
	}

	/**
	 * Keeps each counter an exact canonical decimal string.
	 *
	 * @param string $value Bounded counter vector.
	 * @return array|false
	 */
	private static function counter_map( $value ) {
		$items = explode( ',', $value, count( self::counters() ) + 1 );
		if ( count( $items ) !== count( self::counters() ) ) {
			return false;
		}
		foreach ( $items as $item ) {
			if ( 1 !== preg_match( '/\A(?:0|[1-9][0-9]{0,19})\z/', $item ) ) {
				return false;
			}
		}
		return array_combine( self::counters(), $items );
	}
}
