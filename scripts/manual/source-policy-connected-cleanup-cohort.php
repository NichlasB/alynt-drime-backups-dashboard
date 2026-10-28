<?php
/**
 * ADBC2 connected release DATA state machine; no backend, native or producer wiring.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/source-policy-connected-cleanup-evidence.php';

/** Fixed refusal/positive cohort, independent observers and no qualification grants. */
final class Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Cohort {
	/** Run correlation.
	 *
	 * @var string|null
	 */
	private $run;
	/** Expected producer image correlation, not authentication.
	 *
	 * @var string|null
	 */
	private $source;
	/** Original virtual start.
	 *
	 * @var int|null
	 */
	private $started;
	/** Last monotonic virtual time.
	 *
	 * @var int|null
	 */
	private $last;
	/** Finite phase.
	 *
	 * @var string
	 */
	private $phase = 'launch';
	/** Completed case count.
	 *
	 * @var int
	 */
	private $index = 0;
	/** Current child correlation.
	 *
	 * @var int
	 */
	private $pid = 0;
	/** Consumed child correlations.
	 *
	 * @var array
	 */
	private $seen = array();
	/** Incomplete bounded frame only.
	 *
	 * @var string
	 */
	private $buffer = '';
	/** Current stdout bytes.
	 *
	 * @var int
	 */
	private $out = 0;
	/** Whole-cohort stdout bytes.
	 *
	 * @var int
	 */
	private $total_out = 0;
	/** Clipped stderr byte count, never text.
	 *
	 * @var int
	 */
	private $err = 0;
	/** Current pending scalar summary, not a grant.
	 *
	 * @var array|null
	 */
	private $pending;
	/** Completed case accounting classifications.
	 *
	 * @var array
	 */
	private $accounting = array();

	/** Binds the private context without selecting a runtime backend.
	 *
	 * @param mixed $run Run correlation.
	 * @param mixed $source Producer source correlation.
	 * @param mixed $started Original virtual milliseconds.
	 */
	public function __construct( $run, $source, $started ) {
		if ( ! is_string( $run ) || 1 !== preg_match( '/\A[a-f0-9]{32}\z/', $run ) || ! is_string( $source ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $source ) || ! is_int( $started ) || $started < 0 ) {
			$this->reject();
			return;
		}
		$this->run     = $run;
		$this->source  = $source;
		$this->started = $started;
		$this->last    = $started;
	}

	/** Schedules only the next connected case with a fresh child correlation.
	 *
	 * @param mixed $pid Owned child correlation, not a capability.
	 * @param mixed $now Virtual monotonic milliseconds.
	 * @return array|false
	 */
	public function launch( $pid, $now ) {
		if ( ! $this->tick( $now ) || 'launch' !== $this->phase || ! is_int( $pid ) || $pid < 1 || in_array( $pid, $this->seen, true ) ) {
			return $this->reject();
		}
		$this->pid    = $pid;
		$this->seen[] = $pid;
		$this->out    = 0;
		$this->phase  = 'output';
		return array(
			'case'    => $this->case_name(),
			'run'     => $this->run,
			'source'  => $this->source,
			'profile' => Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::PROFILE,
		);
	}

	/** Consumes bounded virtual chunks; no stderr or failed payload retention.
	 *
	 * @param mixed $stream out or err.
	 * @param mixed $chunk Supplied bytes.
	 * @param mixed $now Virtual monotonic milliseconds.
	 * @return bool
	 */
	public function feed( $stream, $chunk, $now ) {
		if ( ! $this->tick( $now ) || 'output' !== $this->phase || ! is_string( $chunk ) || ! in_array( $stream, array( 'out', 'err' ), true ) ) {
			return $this->reject();
		}
		if ( 'err' === $stream ) {
			$this->err += min( strlen( $chunk ), 4097 );
			return '' === $chunk ? true : $this->reject();
		}
		$length = strlen( $chunk );
		if ( $length > 16384 - $this->out || $length > 131072 - $this->total_out ) {
			return $this->reject();
		}
		$this->out       += $length;
		$this->total_out += $length;
		$this->buffer    .= $chunk;
		if ( false !== strpos( $this->buffer, "\n" ) ) {
			$row           = Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::parse( $this->buffer );
			$this->pending = Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::inspect( $row, $this->run, $this->case_name(), $this->source, $this->pid );
			if ( false === $this->pending ) {
				return $this->reject();
			}
			$this->buffer = '';
			$this->phase  = 'held';
		}
		return true;
	}

	/** Requires independent all-server quiescence while the exact child is alive.
	 *
	 * @param mixed $observation Strict observer DATA from a future guarded adapter.
	 * @param mixed $now Virtual monotonic milliseconds.
	 * @return string|false New private token, never ADBC-ACK or an owner grant.
	 */
	public function live( $observation, $now ) {
		if ( ! $this->tick( $now ) || 'held' !== $this->phase || ! $this->clear( $observation, true ) ) {
			return $this->reject();
		}
		$this->phase = 'exit';
		return 'ADBC2-ACK|' . $this->run . '|' . $this->source . '|' . $this->case_name() . '|' . $this->pid . "\n";
	}

	/** Completes only after clean exit and distinct post-exit reconciliation.
	 *
	 * @param mixed $code Actual child exit code supplied as DATA.
	 * @param mixed $observation Post-exit observer DATA.
	 * @param mixed $now Virtual monotonic milliseconds.
	 * @return bool
	 */
	public function finish( $code, $observation, $now ) {
		if ( ! $this->tick( $now ) || 'exit' !== $this->phase || 0 !== $code || ! $this->clear( $observation, false ) ) {
			return $this->reject();
		}
		$this->accounting[ $this->case_name() ] = $this->pending['counter_accounting'];
		$this->pending                          = null;
		++$this->index;
		$this->phase = 2 === $this->index ? 'done' : 'launch';
		return true;
	}

	/** No completion state exports qualification or retained input payloads.
	 *
	 * @return array Scalar diagnostics only.
	 */
	public function status() {
		return array(
			'phase'                   => $this->phase,
			'completed'               => $this->index,
			'release_evidence'        => 'done' === $this->phase ? 'complete' : ( 'unconfirmed' === $this->phase ? 'unconfirmed' : 'pending' ),
			'counter_accounting'      => $this->accounting,
			'counter_review_required' => 'unconfirmed' === $this->phase || in_array( 'divergent', $this->accounting, true ) || ( is_array( $this->pending ) && $this->pending['counter_review_required'] ),
			'native_qualification'    => 'not-established',
			'stdout_bytes'            => $this->total_out,
			'stderr_bytes'            => $this->err,
		);
	}

	/** Fixed next connected case.
	 *
	 * @return string
	 */
	private function case_name() {
		return Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::cases()[ $this->index ];
	}
	/** Permanently rejects, clearing incomplete and pending payloads.
	 *
	 * @return false
	 */
	private function reject() {
		$this->phase   = 'unconfirmed';
		$this->buffer  = '';
		$this->pending = null;
		return false;
	}
	/** Checks the original cohort deadline, never resetting per case.
	 *
	 * @param mixed $now Virtual monotonic milliseconds.
	 * @return bool
	 */
	private function tick( $now ) {
		if ( 'unconfirmed' === $this->phase || ! is_int( $now ) || $now < $this->last || $now - $this->started >= 25000 ) {
			return false;
		}
		$this->last = $now;
		return true;
	}
	/** All sessions except observer, including NULL and foreign databases.
	 *
	 * @param mixed $row Strict supplied observer DATA.
	 * @param bool  $alive Required liveness.
	 * @return bool
	 */
	private function clear( $row, $alive ) {
		return is_array( $row ) && array_keys( $row ) === array( 'pid', 'alive', 'scope', 'sessions', 'transactions' )
			&& $row['pid'] === $this->pid && $row['alive'] === $alive && 'all-except-observer' === $row['scope'] && 0 === $row['sessions'] && 0 === $row['transactions'];
	}
}
