<?php
/**
 * Dormant virtual-IO cohort. No process, clock, SQL, native or production IO.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/source-policy-cleanup-evidence.php';

/** DATA state transitions only, not a child launcher or native authenticator. */
final class Alynt_Drime_Backups_Dashboard_Cleanup_Cohort {
	/** Private run correlation.
	 *
	 * @var string|null
	 */
	private $run;
	/** Source correlation, never provenance.
	 *
	 * @var string|null
	 */
	private $source;
	/** Original virtual start.
	 *
	 * @var int|null
	 */
	private $started;
	/** Last supplied virtual time.
	 *
	 * @var int|null
	 */
	private $last;
	/** Finite orchestration state.
	 *
	 * @var string
	 */
	private $phase = 'launch';
	/** Number of completed fixed cases.
	 *
	 * @var int
	 */
	private $index = 0;
	/** Current child correlation.
	 *
	 * @var int
	 */
	private $pid = 0;
	/** Previously consumed child correlations.
	 *
	 * @var array
	 */
	private $seen = array();
	/** Bounded incomplete stdout only.
	 *
	 * @var string
	 */
	private $buffer = '';
	/** Current stdout count.
	 *
	 * @var int
	 */
	private $out = 0;
	/** Current clipped stderr count; no error text retained.
	 *
	 * @var int
	 */
	private $err = 0;
	/** Whole-cohort stdout count.
	 *
	 * @var int
	 */
	private $total_out = 0;
	/** Whole-cohort clipped stderr count.
	 *
	 * @var int
	 */
	private $total_err = 0;

	/**
	 * Accepts only explicit local correlation and a supplied monotonic clock.
	 *
	 * @param mixed $run Run correlation.
	 * @param mixed $source Source correlation, not provenance.
	 * @param mixed $started Virtual monotonic milliseconds.
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

	/**
	 * Schedules one fixed case; exports no command, handle or credential.
	 *
	 * @param mixed $pid Correlated owned-child identifier from the future adapter.
	 * @param mixed $now Supplied monotonic milliseconds.
	 * @return array|false
	 */
	public function launch( $pid, $now ) {
		if ( ! $this->tick( $now ) || 'launch' !== $this->phase || ! is_int( $pid ) || $pid < 1 || in_array( $pid, $this->seen, true ) ) {
			return $this->reject();
		}
		$this->seen[] = $pid;
		$this->pid    = $pid;
		$this->out    = 0;
		$this->err    = 0;
		$this->phase  = 'output';
		return array(
			'case'    => $this->case_name(),
			'run'     => $this->run,
			'source'  => $this->source,
			'profile' => Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::PROFILE,
		);
	}

	/**
	 * Accepts bounded virtual pipe chunks; never retains stderr or failed bytes.
	 *
	 * @param mixed $stream out or err.
	 * @param mixed $chunk Bytes supplied by the future bounded reader.
	 * @param mixed $now Supplied monotonic milliseconds.
	 * @return bool
	 */
	public function feed( $stream, $chunk, $now ) {
		if ( ! $this->tick( $now ) || 'output' !== $this->phase || ! is_string( $chunk ) || ! in_array( $stream, array( 'out', 'err' ), true ) ) {
			return $this->reject();
		}
		$length = strlen( $chunk );
		if ( 'err' === $stream ) {
			$this->err       += min( $length, 4097 );
			$this->total_err += min( $length, 4097 );
			return '' === $chunk ? true : $this->reject(); // A nonempty error channel cannot complete a case; no raw error text is retained.
		}
		if ( $length > 16384 - $this->out || $length > 131072 - $this->total_out ) {
			return $this->reject();
		}
		$this->out       += $length;
		$this->total_out += $length;
		$this->buffer    .= $chunk;
		if ( false !== strpos( $this->buffer, "\n" ) ) {
			$row = Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::parse( $this->buffer );
			if ( ! Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches( $row, $this->run, $this->case_name(), $this->source, $this->pid ) ) {
				return $this->reject();
			}
			$this->buffer = '';
			$this->phase  = 'held';
		}
		return true;
	}

	/**
	 * Acknowledges only an all-session observation while the child is alive.
	 *
	 * @param mixed $observation Observer DATA, not SQL or a collaborator object.
	 * @param mixed $now Supplied monotonic milliseconds.
	 * @return string|false Fixed private acknowledgement, not a grant.
	 */
	public function live( $observation, $now ) {
		if ( ! $this->tick( $now ) || 'held' !== $this->phase || ! $this->clear( $observation, true ) ) {
			return $this->reject();
		}
		$this->phase = 'exit';
		return 'ADBC-ACK|' . $this->run . '|' . $this->case_name() . '|' . $this->pid . "\n";
	}

	/**
	 * Consumes one clean exit and post-exit observation before the next case.
	 *
	 * @param mixed $exit_code Exact child exit code.
	 * @param mixed $observation Independent post-exit DATA.
	 * @param mixed $now Supplied monotonic milliseconds.
	 * @return bool
	 */
	public function finish( $exit_code, $observation, $now ) {
		if ( ! $this->tick( $now ) || 'exit' !== $this->phase || 0 !== $exit_code || ! $this->clear( $observation, false ) ) {
			return $this->reject();
		}
		++$this->index;
		$this->phase = 7 === $this->index ? 'done' : 'launch';
		return true;
	}

	/**
	 * Reports only finite progress, never input bytes, handles or admission facts.
	 *
	 * @return array
	 */
	public function status() {
		return array(
			'phase'        => $this->phase,
			'completed'    => $this->index,
			'stdout_bytes' => $this->total_out,
			'stderr_bytes' => $this->total_err,
		);
	}

	/** Fixed next case.
	 *
	 * @return string
	 */
	private function case_name() {
		return Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::cases()[ $this->index ];
	}

	/** Permanent fail-closed transition; failed payload is discarded.
	 *
	 * @return false
	 */
	private function reject() {
		$this->phase  = 'unconfirmed';
		$this->buffer = '';
		return false;
	}

	/**
	 * Checks the original cohort deadline, never resetting it per case.
	 *
	 * @param mixed $now Supplied monotonic milliseconds.
	 * @return bool
	 */
	private function tick( $now ) {
		if ( 'unconfirmed' === $this->phase || ! is_int( $now ) || $now < $this->last || $now - $this->started >= 25000 ) {
			return false;
		}
		$this->last = $now;
		return true;
	}

	/**
	 * Requires all-server visibility, including NULL/other database sessions.
	 *
	 * @param mixed $row Bounded observer DATA.
	 * @param bool  $alive Required process liveness.
	 * @return bool
	 */
	private function clear( $row, $alive ) {
		return is_array( $row ) && array_keys( $row ) === array( 'pid', 'alive', 'scope', 'sessions', 'transactions' )
			&& $row['pid'] === $this->pid && $row['alive'] === $alive && 'all-except-observer' === $row['scope'] && 0 === $row['sessions'] && 0 === $row['transactions'];
	}
}
