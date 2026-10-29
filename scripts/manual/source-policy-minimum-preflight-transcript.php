<?php
/**
 * Versioned synthetic preflight producer/validator. No IO, clock or launch grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Separate channels model evidence ownership; caller claims cannot authenticate it. */
final class Alynt_Drime_Backups_Dashboard_Minimum_Preflight_Transcript {
	/** Fixed comparison reference from the separately authenticated publisher ZIP. */
	private const EXECUTABLE_SHA256 = '4b06073dd8230421ba701a677dd8b1aa23ed55706663d3fdf996702b557f83b2';
	/** Synthetic run correlation.
	 *
	 * @var string
	 */
	private $run;
	/** Synthetic image correlation.
	 *
	 * @var string
	 */
	private $image;
	/** Supplied PID/start claims, not process handles.
	 *
	 * @var array
	 */
	private $bindings;
	/** Expected source digest claim.
	 *
	 * @var string
	 */
	private $source;
	/** Expected base-module digest claim.
	 *
	 * @var string
	 */
	private $base;
	/** Expected module-set claims.
	 *
	 * @var array
	 */
	private $modules;
	/** Next event, never reset after refusal.
	 *
	 * @var int
	 */
	private $step = 0;
	/** Last accepted virtual millisecond.
	 *
	 * @var int
	 */
	private $last = 0;
	/** All protocol input bytes, including controller/ack claims.
	 *
	 * @var int
	 */
	private $bytes = 0;
	/** Sticky failure.
	 *
	 * @var bool
	 */
	private $failed = false;
	/** First safe reason only.
	 *
	 * @var string
	 */
	private $reason = '';
	/** Discarded stderr count, capped at overflow sentinel4097.
	 *
	 * @var int
	 */
	private $stderr = 0;

	/**
	 * Creates only synthetic bindings. No evidence is discovered/authenticated.
	 *
	 * @param mixed $run Run32hex claim.
	 * @param mixed $image Image64hex claim.
	 * @param array $bindings Exact base/mysqli PID/start claims.
	 * @param mixed $source Expected worker/producer source digest.
	 * @param mixed $base Expected compiled base-module digest.
	 * @param array $modules Exact base/mysqli module digest/count claims.
	 * @throws InvalidArgumentException Invalid primitive bindings.
	 */
	public function __construct( $run, $image, array $bindings, $source, $base, array $modules ) {
		$valid = self::header( $run, $image, 'base', 1, 1 ) && self::digest( $source ) && self::digest( $base )
			&& 2 === count( $bindings ) && 2 === count( $modules ) && array_keys( $bindings ) === array( 'base', 'mysqli' ) && array_keys( $modules ) === array( 'base', 'mysqli' );
		foreach ( array( 'base', 'mysqli' ) as $stage ) {
			$b     = $bindings[ $stage ] ?? null;
			$m     = $modules[ $stage ] ?? null;
			$valid = $valid && is_array( $b ) && 2 === count( $b ) && array_keys( $b ) === array( 'pid', 'start' ) && self::header( $run, $image, $stage, $b['pid'], 1 ) && self::start_tick( $b['start'] )
				&& is_array( $m ) && 2 === count( $m ) && array_keys( $m ) === array( 'sha256', 'count' ) && self::digest( $m['sha256'] ) && is_int( $m['count'] ) && $m['count'] >= ( 'base' === $stage ? 4 : 5 ) && $m['count'] <= 128;
		}
		if ( ! $valid || $bindings['base']['pid'] === $bindings['mysqli']['pid'] ) {
			throw new InvalidArgumentException( 'Synthetic preflight bindings refused' );
		}
		$this->run      = $run;
		$this->image    = $image;
		$this->bindings = $bindings;
		$this->source   = $source;
		$this->base     = $base;
		$this->modules  = $modules;
	}

	/** Primitive digest syntax only.
	 *
	 * @param mixed $value DATA.
	 * @return bool
	 */
	private static function digest( $value ) {
		return is_string( $value ) && 64 === strlen( $value ) && 1 === preg_match( '/\A[a-f0-9]{64}\z/D', $value );
	}

	/** Canonical start claim, not a timestamp observation.
	 *
	 * @param mixed $value DATA.
	 * @return bool
	 */
	private static function start_tick( $value ) {
		return is_string( $value ) && strlen( $value ) <= 19 && 1 === preg_match( '/\A[1-9][0-9]{0,18}\z/D', $value );
	}

	/**
	 * Bounded primitive envelope fields, no casting or discovery.
	 *
	 * @param mixed $run Run claim.
	 * @param mixed $image Image claim.
	 * @param mixed $stage Fixed stage.
	 * @param mixed $pid PID claim.
	 * @param mixed $seq Canonical event number.
	 * @return bool
	 */
	private static function header( $run, $image, $stage, $pid, $seq ) {
		return is_string( $run ) && 32 === strlen( $run ) && 1 === preg_match( '/\A[a-f0-9]{32}\z/D', $run ) && self::digest( $image ) && in_array( $stage, array( 'base', 'mysqli' ), true )
			&& is_int( $pid ) && $pid > 0 && $pid <= 2147483647 && is_int( $seq ) && $seq >= 1 && $seq <= 64;
	}

	/**
	 * Exact expected wire fields, not measurement of a runtime.
	 *
	 * @param mixed $stage Fixed stage.
	 * @param mixed $source Source digest claim.
	 * @param mixed $base Base-module digest claim.
	 * @return array|false
	 */
	public static function profile_fields( $stage, $source, $base ) {
		if ( ! in_array( $stage, array( 'base', 'mysqli' ), true ) || ! self::digest( $source ) || ! self::digest( $base ) ) {
			return false;
		}
		$mysqli = 'mysqli' === $stage;
		return array( 'Windows', 'cli', '7.4.33', '8', 'nts', 'none', 'none', 'none', 'none', $source, $base, $mysqli ? 'mysqlnd 7.4.33' : 'absent', $mysqli ? 'internal' : 'absent', $mysqli ? '0' : 'absent', $mysqli ? '0' : 'absent' );
	}

	/**
	 * Produces one bounded consistency frame. Does not write it or grant authority.
	 *
	 * @param mixed $run Run claim.
	 * @param mixed $image Image claim.
	 * @param mixed $stage Fixed stage.
	 * @param mixed $pid PID claim.
	 * @param mixed $seq Event number.
	 * @param mixed $kind Fixed kind.
	 * @param array $fields Exact primitive payload.
	 * @return string|false
	 */
	public static function frame( $run, $image, $stage, $pid, $seq, $kind, array $fields ) {
		if ( ! self::header( $run, $image, $stage, $pid, $seq ) || ! is_string( $kind ) || ! self::fields( $stage, $kind, $fields ) ) {
			return false;
		}
		$line = 'ADBMP1|' . $run . '|' . $image . '|' . $stage . '|' . $pid . '|' . $seq . '|' . $kind . '|' . implode( '|', $fields ) . "\n";
		return strlen( $line ) <= 512 ? $line : false;
	}

	/**
	 * Checks finite kind payloads before any concatenation.
	 *
	 * @param string $stage Fixed stage.
	 * @param string $kind Fixed kind.
	 * @param array  $fields Untrusted payload.
	 * @return bool
	 */
	private static function fields( $stage, $kind, array $fields ) {
		switch ( $kind ) {
			case 'start':
				return 2 === count( $fields ) && array_keys( $fields ) === array( 0, 1 ) && self::start_tick( $fields[0] ) && self::EXECUTABLE_SHA256 === $fields[1];
			case 'profile':
				return 15 === count( $fields ) && array_keys( $fields ) === range( 0, 14 ) && false !== self::profile_fields( $stage, $fields[9], $fields[10] ) && self::profile_fields( $stage, $fields[9], $fields[10] ) === $fields;
			case 'held':
				return array( 'await-observer' ) === $fields;
			case 'modules':
				return 2 === count( $fields ) && array_keys( $fields ) === array( 0, 1 ) && self::digest( $fields[0] ) && is_string( $fields[1] ) && 1 === preg_match( '/\A[1-9][0-9]{0,2}\z/D', $fields[1] ) && (int) $fields[1] <= 128;
			case 'released':
				return array( 'acknowledged' ) === $fields;
			case 'exit':
				return array( '0', 'drained' ) === $fields;
			case 'cleanup':
				return array( 'process-absent', 'unchanged', 'base' === $stage ? 'retained-owned' : 'artifacts-absent' ) === $fields;
		}
		return false;
	}

	/**
	 * Exact sole stdin response DATA, never a command channel.
	 *
	 * @param mixed $run Run claim.
	 * @param mixed $image Image claim.
	 * @param mixed $stage Fixed stage.
	 * @param mixed $pid PID claim.
	 * @return string|false
	 */
	public static function ack_frame( $run, $image, $stage, $pid ) {
		return self::header( $run, $image, $stage, $pid, 5 ) ? 'ADBMP1-ACK|' . $run . '|' . $image . '|' . $stage . '|' . $pid . "|5\n" : false;
	}

	/** First refusal is sticky; raw input is never retained.
	 *
	 * @param string $reason Safe code.
	 * @return false
	 */
	private function reject( $reason ) {
		if ( ! $this->failed ) {
			$this->reason = $reason;
		}
		$this->failed = true;
		return false;
	}

	/** Virtual caller-clock consistency only.
	 *
	 * @param mixed $now Milliseconds.
	 * @return bool
	 */
	private function time( $now ) {
		if ( $this->failed || ! is_int( $now ) || $now < $this->last || $now >= 25000 ) {
			return $this->reject( 'clock-or-state' );
		}
		$this->last = $now;
		return true;
	}

	/**
	 * Fixed next-channel/spec DATA, not an observer or process state.
	 *
	 * @return array
	 */
	private function next() {
		$stage = $this->step < 8 ? 'base' : 'mysqli';
		$pid   = $this->bindings[ $stage ]['pid'];
		$spec  = array(
			array( 'worker', 'start', array( $this->bindings[ $stage ]['start'], self::EXECUTABLE_SHA256 ) ),
			array( 'worker', 'profile', self::profile_fields( $stage, $this->source, $this->base ) ),
			array( 'worker', 'held', array( 'await-observer' ) ),
			array( 'controller', 'modules', array( $this->modules[ $stage ]['sha256'], (string) $this->modules[ $stage ]['count'] ) ),
			array( 'stdin', 'ack', array() ),
			array( 'worker', 'released', array( 'acknowledged' ) ),
			array( 'controller', 'exit', array( '0', 'drained' ) ),
			array( 'controller', 'cleanup', array( 'process-absent', 'unchanged', 'base' === $stage ? 'retained-owned' : 'artifacts-absent' ) ),
		);
		$row   = $spec[ $this->step % 8 ];
		return array( $row[0], 'ack' === $row[1] ? self::ack_frame( $this->run, $this->image, $stage, $pid ) : self::frame( $this->run, $this->image, $stage, $pid, $this->step % 8 + 1, $row[1], $row[2] ) );
	}

	/** Finite wire consumption with source-channel separation.
	 *
	 * @param string $channel Private method-selected source.
	 * @param mixed  $line Supplied complete frame.
	 * @param mixed  $now Virtual elapsed milliseconds.
	 * @return bool Consistency, never execution permission.
	 */
	private function consume( $channel, $line, $now ) {
		if ( ! $this->time( $now ) || $this->step >= 16 || ! is_string( $line ) || strlen( $line ) > 512 || $this->bytes + strlen( $line ) > 16384 ) {
			return $this->reject( 'frame-budget-or-state' );
		}
		$expected = $this->next();
		if ( $channel !== $expected[0] || $line !== $expected[1] ) {
			return $this->reject( 'frame-or-channel' );
		}
		$this->bytes += strlen( $line );
		++$this->step;
		return true;
	}

	/** Worker claims cannot satisfy controller evidence.
	 *
	 * @param mixed $line Frame.
	 * @param mixed $now Virtual ms.
	 * @return bool
	 */
	public function worker( $line, $now ) {
		return $this->consume( 'worker', $line, $now );
	}

	/** Controller claims are DATA, not authenticated observations.
	 *
	 * @param mixed $line Frame.
	 * @param mixed $now Virtual ms.
	 * @return bool
	 */
	public function controller( $line, $now ) {
		return $this->consume( 'controller', $line, $now );
	}

	/** No arbitrary input command is accepted.
	 *
	 * @param mixed $line Exact ACK.
	 * @param mixed $now Virtual ms.
	 * @return bool
	 */
	public function stdin( $line, $now ) {
		return $this->consume( 'stdin', $line, $now );
	}

	/** Discards raw errors; any nonempty stderr refuses.
	 *
	 * @param mixed $bytes Claim.
	 * @param mixed $now Virtual ms.
	 * @return bool
	 */
	public function stderr( $bytes, $now ) {
		if ( ! $this->time( $now ) || ! is_string( $bytes ) ) {
			return $this->reject( 'stderr-type' );
		}
		if ( '' !== $bytes ) {
			$this->stderr = min( 4097, strlen( $bytes ) );
			return $this->reject( 'stderr' );
		}
		return true;
	}

	/** EOF alone cannot complete a transcript.
	 *
	 * @param mixed $now Virtual ms.
	 * @return bool
	 */
	public function eof( $now ) {
		return $this->time( $now ) && ( 16 === $this->step || $this->reject( 'early-eof' ) );
	}

	/** Non-secret state only. All outcomes remain non-authorizing. @return array */
	public function snapshot() {
		return array(
			'phase'               => 16 === $this->step ? 'complete' : ( $this->step < 8 ? 'base' : 'mysqli' ),
			'events'              => $this->step,
			'failed'              => $this->failed,
			'reason'              => $this->reason,
			'stderr_bytes_capped' => $this->stderr,
			'synthetic_complete'  => ! $this->failed && 16 === $this->step,
			'execution'           => false,
			'load_authorized'     => false,
			'native_qualified'    => false,
		);
	}
}
