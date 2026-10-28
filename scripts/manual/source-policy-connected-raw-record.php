<?php
/**
 * Private raw diagnostic DATA only; never owner admission or ADBC1 acceptance.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ADCR_PRIVATE' ) && ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Strict stages and independent live observations; no native/process operations. */
final class Alynt_Drime_Backups_Dashboard_Raw_Connected_Record {
	const PROFILE = 'php8512-mysqlnd8512-mariadb10623-win64-raw';
	/** Private run correlation.
	 *
	 * @var string|null
	 */
	private $run;
	/** Frozen source correlation.
	 *
	 * @var string|null
	 */
	private $source;
	/** Owned child correlation.
	 *
	 * @var int|null
	 */
	private $pid;
	/** Last supplied monotonic milliseconds.
	 *
	 * @var int
	 */
	private $last = 0;
	/** Finite diagnostic phase.
	 *
	 * @var string
	 */
	private $phase = 'connected';
	/** Observed connected counter snapshot.
	 *
	 * @var array|null
	 */
	private $connected;

	/** Fixed native names, original eleven followed by four supporting observations.
	 *
	 * @return array
	 */
	public static function counters() {
		return explode( ',', 'connect_success,connect_failure,com_query,result_set_queries,explicit_free_result,implicit_free_result,unbuffered_sets,reconnect,connection_reused,pconnect_success,active_connections,active_persistent_connections,explicit_close,implicit_close,disconnect_close' );
	}

	/** Constructor binds scalar correlations, never producer authentication.
	 *
	 * @param mixed $run Private run.
	 * @param mixed $source Frozen source correlation.
	 * @param mixed $pid Owned child correlation.
	 */
	public function __construct( $run, $source, $pid ) {
		if ( ! is_string( $run ) || 1 !== preg_match( '/\A[a-f0-9]{32}\z/', $run ) || ! is_string( $source ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $source ) || ! is_int( $pid ) || $pid < 1 ) {
			$this->phase = 'unconfirmed';
		}
		$this->run    = $run;
		$this->source = $source;
		$this->pid    = $pid;
	}

	/** Canonical native strings, never casts unsigned counters.
	 *
	 * @param mixed $vector Fixed counter vector.
	 * @return array|false
	 */
	public static function vector( $vector ) {
		if ( ! is_string( $vector ) || 1 !== preg_match( '/\A(?:0|[1-9][0-9]{0,19})(?:,(?:0|[1-9][0-9]{0,19})){14}\z/', $vector ) ) {
			return false;
		}
		return array_combine( self::counters(), explode( ',', $vector ) );
	}

	/** Validates one finite frame. Counter anomalies cannot grant owner qualification.
	 *
	 * @param mixed $wire Complete ASCII line.
	 * @param mixed $stage Fixed expected stage.
	 * @param mixed $run Run correlation.
	 * @param mixed $source Source correlation.
	 * @param mixed $pid Owned child correlation.
	 * @return array|false
	 */
	public static function frame( $wire, $stage, $run, $source, $pid ) {
		if ( ! is_string( $run ) || 1 !== preg_match( '/\A[a-f0-9]{32}\z/', $run ) || ! is_string( $source ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $source ) || ! is_int( $pid ) || $pid < 1
			|| ! in_array( $stage, array( 'connected', 'closed' ), true ) || ! is_string( $wire ) || strlen( $wire ) > 16384 || 1 !== preg_match( '/\A[A-Za-z0-9_,|.-]+\n\z/', $wire ) ) {
			return false;
		}
		$parts = explode( '|', substr( $wire, 0, -1 ) );
		if ( count( $parts ) !== ( 'connected' === $stage ? 12 : 11 ) || array_slice( $parts, 0, 6 ) !== array( 'ADCR1', $run, $source, (string) $pid, self::PROFILE, $stage ) ) {
			return false;
		}
		if ( 'connected' === $stage ) {
			$zero = implode( ',', array_fill( 0, 15, '0' ) );
			if ( $zero !== $parts[6] || $zero !== $parts[7] || array_slice( $parts, 9 ) !== array( '1', '1', '0' ) ) {
				return false;
			}
		} elseif ( array_slice( $parts, 7 ) !== array( '1', '1', '1', 'none' ) ) {
			return false; // Identity, one PHP close invocation, true acknowledgement, no close exception.
		}
		$map = self::vector( $parts[ 'connected' === $stage ? 8 : 6 ] );
		if ( false === $map || '1' !== $map['connect_success'] || '0' !== $map['connect_failure'] ) {
			return false;
		}
		foreach ( array( 'com_query', 'result_set_queries', 'explicit_free_result', 'implicit_free_result', 'unbuffered_sets', 'reconnect', 'connection_reused' ) as $key ) {
			if ( '0' !== $map[ $key ] ) {
				return false;
			}
		}
		return $map; // Persistence/open/close counts remain unnormalized diagnostic bytes.
	}

	/** Feeds one stage once, with one original virtual25s deadline.
	 *
	 * @param mixed $wire Fixed frame.
	 * @param mixed $now Monotonic milliseconds since launch.
	 * @return bool
	 */
	public function feed( $wire, $now ) {
		if ( ! $this->tick( $now ) || ! in_array( $this->phase, array( 'connected', 'closed' ), true ) ) {
			return $this->reject();
		}
		$map = self::frame( $wire, $this->phase, $this->run, $this->source, $this->pid );
		if ( false === $map || ( 'closed' === $this->phase && $map['pconnect_success'] !== $this->connected['pconnect_success'] ) ) {
			return $this->reject();
		}
		$this->connected = 'connected' === $this->phase ? $map : $this->connected;
		$this->phase    .= '_hold';
		return true;
	}

	/** Requires actual all-server observation supplied by the separately audited adapter.
	 *
	 * @param mixed $pid Actual owned child.
	 * @param mixed $alive Actual liveness.
	 * @param mixed $sessions All sessions except observer, including DB=NULL/foreign.
	 * @param mixed $transactions All transactions.
	 * @param mixed $now Original monotonic milliseconds.
	 * @return string|false Fixed phase token, never ADBC1/owner authority.
	 */
	public function observe( $pid, $alive, $sessions, $transactions, $now ) {
		$connected = 'connected_hold' === $this->phase;
		if ( ! $this->tick( $now ) || ! in_array( $this->phase, array( 'connected_hold', 'closed_hold' ), true ) || $pid !== $this->pid || true !== $alive || ( $connected ? 1 : 0 ) !== $sessions || 0 !== $transactions ) {
			return $this->reject();
		}
		$this->phase = $connected ? 'closed' : 'exit';
		return self::token( $connected ? 'CLOSE' : 'EXIT', $this->run, $this->source, $this->pid );
	}

	/** Private phase-specific control string.
	 *
	 * @param string $phase CLOSE or EXIT.
	 * @param string $run Run correlation.
	 * @param string $source Source correlation.
	 * @param int    $pid Owned child.
	 * @return string
	 */
	public static function token( $phase, $run, $source, $pid ) {
		return 'ADCR-' . $phase . '|' . $run . '|' . $source . '|' . $pid . "\n";
	}

	/** Clean exit and independent post-exit quiescence are mandatory.
	 *
	 * @param mixed $code Actual exit code.
	 * @param mixed $pid Owned child correlation.
	 * @param mixed $alive Actual liveness.
	 * @param mixed $sessions All-server sessions except observer.
	 * @param mixed $transactions All transactions.
	 * @param mixed $now Original monotonic milliseconds.
	 * @return bool
	 */
	public function finish( $code, $pid, $alive, $sessions, $transactions, $now ) {
		if ( ! $this->tick( $now ) || 'exit' !== $this->phase || 0 !== $code || $pid !== $this->pid || false !== $alive || 0 !== $sessions || 0 !== $transactions ) {
			return $this->reject();
		}
		$this->phase = 'done';
		return true;
	}

	/** Reports the finite diagnostic phase.
	 *
	 * @return string No grant or producer authentication.
	 */
	public function phase() {
		return $this->phase;
	}
	/** Permanently refuses progress.
	 *
	 * @return false
	 */
	private function reject() {
		$this->phase = 'unconfirmed';
		return false;
	}
	/** Checks the original deadline and monotonic ordering.
	 *
	 * @param mixed $now Original monotonic milliseconds.
	 * @return bool
	 */
	private function tick( $now ) {
		if ( 'unconfirmed' === $this->phase || ! is_int( $now ) || $now < $this->last || $now >= 25000 ) {
			return false;
		}
		$this->last = $now;
		return true;
	}
}
