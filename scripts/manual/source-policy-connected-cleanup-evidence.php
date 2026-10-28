<?php
/**
 * Versioned connected cleanup DATA only; no native provider or ADBC1 changes.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/source-policy-cleanup-evidence.php';

/** Separates diagnostic accounting from pending independent release evidence. */
final class Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence {
	const PROFILE = 'wp713-php8512-mysqlnd8512-mariadb10623-win64-connected-v2';
	const POLICY  = 'counter-observation-release-independent';

	/** Fixed connected cases; not the legacy seven-case qualification cohort.
	 *
	 * @return array
	 */
	public static function cases() {
		return array( 'missing-table', 'positive' );
	}

	/** Distinct31-field schema cannot parse as an ADBC1 packet.
	 *
	 * @return array
	 */
	public static function fields() {
		return array_merge( Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::fields(), array( 'counter_policy' ) );
	}

	/** Bounded scalar parsing; no implicit legacy upgrade or diagnostic receipt import.
	 *
	 * @param mixed $wire Complete newline-terminated ASCII frame.
	 * @return array|false
	 */
	public static function parse( $wire ) {
		if ( ! is_string( $wire ) || strlen( $wire ) > 16384 || 1 !== preg_match( '/\A[A-Za-z0-9_,|.-]+\n\z/', $wire ) ) {
			return false;
		}
		$fields = self::fields();
		$values = explode( '|', substr( $wire, 0, -1 ), count( $fields ) + 1 );
		if ( count( $values ) !== count( $fields ) || 'ADBC2' !== $values[0] ) {
			return false;
		}
		foreach ( $values as $value ) {
			if ( '' === $value || strlen( $value ) > 256 ) {
				return false;
			}
		}
		return array_combine( $fields, $values );
	}

	/** Inspects terminal DATA, never confirms physical release or native qualification.
	 *
	 * @param mixed  $row Parsed frame.
	 * @param string $run Expected private run correlation.
	 * @param string $case_name Fixed scheduled case.
	 * @param string $source Expected producer image correlation, not authentication.
	 * @param int    $pid Owned-child correlation, not a capability.
	 * @return array|false Pending evidence and unmodified counter strings only.
	 */
	public static function inspect( $row, $run, $case_name, $source, $pid ) {
		if ( ! is_array( $row ) || array_keys( $row ) !== self::fields() || ! in_array( $case_name, self::cases(), true ) || ! is_int( $pid ) || $pid < 1
			|| ! is_string( $run ) || 1 !== preg_match( '/\A[a-f0-9]{32}\z/', $run ) || ! is_string( $source ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $source ) ) {
			return false;
		}
		foreach ( $row as $value ) {
			if ( ! is_string( $value ) || strlen( $value ) > 256 ) {
				return false;
			}
		}
		$positive = 'positive' === $case_name;
		$expected = array(
			'version'            => 'ADBC2',
			'run'                => $run,
			'case'               => $case_name,
			'profile'            => self::PROFILE,
			'source'             => $source,
			'pid'                => (string) $pid,
			'allocations'        => '1',
			'outcome'            => $positive ? 'active' : 'unavailable',
			'active'             => $positive ? '1' : '0',
			'ack'                => '1',
			'close_calls'        => '1',
			'cached'             => '1',
			'identity'           => '1',
			'ambient'            => '0',
			'dbh_null'           => '1',
			'qualification_null' => '1',
			'frame_clean'        => '1',
			'callback_clean'     => '1',
			'results_clean'      => '1',
			'closed_probe'       => '1',
			'state'              => 'closed',
			'observer_calls'     => '0',
			'steps'              => '8',
			'queries'            => $positive ? '1' : '0',
			'roundtrip'          => $positive ? '1' : '0',
			'seal'               => $positive ? '1' : '0',
			'counter_policy'     => self::POLICY,
		);
		foreach ( $expected as $key => $value ) {
			if ( $row[ $key ] !== $value ) {
				return false;
			}
		}
		if ( implode( ',', array_fill( 0, 11, '0' ) ) !== $row['before'] || ! self::small( $row['elapsed'], 20000 ) ) {
			return false;
		}
		$boot  = self::vector( $row['bootstrap'] );
		$after = self::vector( $row['after'] );
		if ( false === $boot || false === $after || ! self::small( $boot['com_query'], 16 ) || (int) $boot['com_query'] < 7 ) {
			return false;
		}
		// Preserve all connection/effect/resource budgets; open/persistent accounting is separate DATA.
		$essential                         = array_fill_keys( array( 'connect_success', 'connect_failure', 'com_query', 'result_set_queries', 'explicit_free_result', 'implicit_free_result', 'unbuffered_sets', 'reconnect', 'connection_reused' ), '0' );
		$essential['connect_success']      = '1';
		$essential['com_query']            = $boot['com_query'];
		$essential['result_set_queries']   = '6';
		$essential['explicit_free_result'] = '6';
		foreach ( array( $boot, $after ) as $index => $map ) {
			if ( 1 === $index && $positive ) {
				$essential['com_query']            = (string) ( (int) $essential['com_query'] + 1 );
				$essential['result_set_queries']   = '7';
				$essential['explicit_free_result'] = '7';
			}
			foreach ( $essential as $key => $value ) {
				if ( $map[ $key ] !== $value ) {
					return false;
				}
			}
		}
		$nominal = '0' === $boot['pconnect_success'] && '0' === $after['pconnect_success']
			&& ( $positive ? '1' : '0' ) === $boot['active_connections'] && '0' === $after['active_connections'];
		return array(
			'release_evidence'        => 'pending-live-reconciliation',
			'counter_accounting'      => $nominal ? 'nominal' : 'divergent',
			'counter_review_required' => ! $nominal,
			'bootstrap'               => $row['bootstrap'],
			'after'                   => $row['after'],
			'native_qualification'    => 'not-established',
		);
	}

	/** Exact canonical unsigned-shaped bytes, never cast or repaired.
	 *
	 * @param string $value Bounded vector.
	 * @return array|false
	 */
	private static function vector( $value ) {
		if ( 1 !== preg_match( '/\A(?:0|[1-9][0-9]{0,19})(?:,(?:0|[1-9][0-9]{0,19})){10}\z/', $value ) ) {
			return false;
		}
		return array_combine( Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::counters(), explode( ',', $value ) );
	}

	/** Validates only genuinely small values before bounded arithmetic.
	 *
	 * @param string $value Canonical decimal.
	 * @param int    $maximum Fixed maximum.
	 * @return bool
	 */
	private static function small( $value, $maximum ) {
		return 1 === preg_match( '/\A(?:0|[1-9][0-9]{0,4})\z/', $value ) && (int) $value <= $maximum;
	}
}
