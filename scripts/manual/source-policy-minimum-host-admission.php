<?php
/**
 * Supplied host-trust and observer lifecycle DATA, never a physical admission.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Self-contained pure consistency model; no includes, IO or actual trust grant. */
final class Alynt_Drime_Backups_Dashboard_Minimum_Host_Admission {
	/** Supplied identity claims.
	 *
	 * @var array
	 */
	private $bindings;
	/** Supplied expected profile claims.
	 *
	 * @var array
	 */
	private $profile;
	/** Supplied expected module digest.
	 *
	 * @var string
	 */
	private $modules;
	/** Next ordered event.
	 *
	 * @var int
	 */
	private $step = 0;
	/** Last virtual millisecond.
	 *
	 * @var int
	 */
	private $last = 0;
	/** Virtual observer start.
	 *
	 * @var int
	 */
	private $observer_start = 0;
	/** Sticky refusal.
	 *
	 * @var bool
	 */
	private $failed = false;
	/** First safe reason, never raw supplied input.
	 *
	 * @var string
	 */
	private $reason = '';
	/** Complete cleanup DATA, even after refusal; never actual absence.
	 *
	 * @var bool
	 */
	private $clean = false;

	/**
	 * Binds primitive claims only. Matching supplied facts cannot prove authority.
	 *
	 * @param mixed $run Synthetic run32hex.
	 * @param mixed $source Combined fixed-source digest claim.
	 * @param array $expected Expected profile DATA.
	 * @param array $observed Observed profile DATA.
	 * @param array $decision Explicit operator-trust decision DATA, not real consent.
	 * @param array $bindings Worker/observer identity DATA, not Process objects.
	 * @param array $modules Expected existing-tool module DATA.
	 * @throws InvalidArgumentException Invalid supplied contract.
	 */
	public function __construct( $run, $source, array $expected, array $observed, array $decision, array $bindings, array $modules ) {
		$profile = self::profile_digest( $expected );
		if ( ! self::hex( $run, 32 ) || ! self::hex( $source, 64 ) || false === $profile || $expected !== $observed
			|| array(
				'policy'                      => 'existing-host-operator-trust-v1',
				'scope'                       => 'minimum-preflight-observer',
				'run'                         => $run,
				'profile_sha256'              => $profile,
				'source_sha256'               => $source,
				'basis'                       => 'operator-trust-not-signature',
				'signature_verified'          => false,
				'offline_provenance_verified' => false,
			) !== $decision || 2 !== count( $bindings ) || array_keys( $bindings ) !== array( 'worker', 'observer' )
			|| ! self::binding( $bindings['worker'], $expected['tool_sha256'] ) || ! self::binding( $bindings['observer'], $expected['tool_sha256'] )
			|| $bindings['worker']['pid'] === $bindings['observer']['pid'] || $bindings['worker']['object'] === $bindings['observer']['object']
			|| $bindings['worker']['source'] === $bindings['observer']['source']
			|| hash( 'sha256', $bindings['worker']['source'] . "\n" . $bindings['observer']['source'] ) !== $source ) {
			throw new InvalidArgumentException( 'Synthetic host admission contract refused' );
		}
		$digest = self::module_digest( $modules, $expected );
		if ( false === $digest || $digest !== $expected['tool_modules_sha256'] ) {
			throw new InvalidArgumentException( 'Synthetic module contract refused' );
		}
		$this->bindings = $bindings;
		$this->profile  = $expected;
		$this->modules  = $digest;
	}

	/** Primitive lowercase hex syntax, never authentication.
	 *
	 * @param mixed $v DATA.
	 * @param int   $length Length.
	 * @return bool
	 */
	private static function hex( $v, $length ) {
		return is_string( $v ) && strlen( $v ) === $length && 1 === preg_match( '/\A[a-f0-9]+\z/D', $v );
	}

	/** Bounded portable absolute path DATA, not final-path resolution.
	 *
	 * @param mixed $v DATA.
	 * @return bool
	 */
	private static function path( $v ) {
		if ( ! is_string( $v ) || strlen( $v ) > 240 || ! preg_match( '/\A[A-Z]:\/[A-Za-z0-9_.\/-]+\z/D', $v ) ) {
			return false;
		}
		foreach ( explode( '/', substr( $v, 3 ) ) as $part ) {
			if ( '' === $part || strlen( $part ) > 80 || '.' === substr( $part, -1 ) || preg_match( '/\A(?:CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(?:\.|\z)/iD', $part ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Finite typed profile digest. No host/table/registry is discovered or trusted.
	 *
	 * @param array $p Supplied primitive profile.
	 * @return string|false Correlation only.
	 */
	public static function profile_digest( array $p ) {
		if ( 18 !== count( $p ) || array_keys( $p ) !== array( 'windows_root', 'system', 'build', 'ubr', 'architecture', 'windows_identity', 'windows_files_sha256', 'crt_version', 'crt_sha256', 'crt_identity', 'crt_installed', 'crt_architecture', 'tool_root', 'tool_version', 'tool_sha256', 'tool_identity', 'tool_modules_sha256', 'tool_architecture' )
			|| ! is_string( $p['windows_root'] ) || ! preg_match( '/\A[A-Z]:\/Windows\z/D', $p['windows_root'] ) || $p['system'] !== $p['windows_root'] . '/System32'
			|| ! is_string( $p['build'] ) || ! preg_match( '/\A[0-9]{1,5}\.[0-9]{1,5}\.[0-9]{1,6}\z/D', $p['build'] )
			|| ! is_int( $p['ubr'] ) || $p['ubr'] < 0 || $p['ubr'] > 999999 || 'x64' !== $p['architecture'] || 'x64' !== $p['crt_architecture'] || 'x64' !== $p['tool_architecture'] || true !== $p['crt_installed']
			|| ! is_string( $p['crt_version'] ) || ! preg_match( '/\A14\.[0-9]{1,5}\.[0-9]{1,6}\.[0-9]{1,5}\z/D', $p['crt_version'] )
			|| ! is_string( $p['tool_version'] ) || ! preg_match( '/\A7\.[0-9]{1,3}\.[0-9]{1,3}\z/D', $p['tool_version'] ) || ! self::path( $p['tool_root'] )
			|| 0 === strpos( strtolower( $p['tool_root'] . '/' ), strtolower( $p['windows_root'] . '/' ) ) ) {
			return false;
		}
		foreach ( array( 'windows_identity', 'windows_files_sha256', 'crt_sha256', 'crt_identity', 'tool_sha256', 'tool_identity', 'tool_modules_sha256' ) as $key ) {
			if ( ! self::hex( $p[ $key ], 64 ) ) {
				return false;
			}
		}
		return hash( 'sha256', implode( "\n", $p ) );
	}

	/** Exact object/PID/start/source claims; no live object or handle.
	 *
	 * @param mixed  $b DATA.
	 * @param string $image Expected digest.
	 * @return bool
	 */
	private static function binding( $b, $image ) {
		return is_array( $b ) && 5 === count( $b ) && array_keys( $b ) === array( 'pid', 'start', 'object', 'image', 'source' ) && is_int( $b['pid'] ) && $b['pid'] > 0 && $b['pid'] <= 2147483647
			&& is_string( $b['start'] ) && strlen( $b['start'] ) <= 20 && 1 === preg_match( '/\A[1-9][0-9]*\z/D', $b['start'] )
			&& self::hex( $b['object'], 32 ) && $image === $b['image'] && self::hex( $b['source'], 64 );
	}

	/** Bounded complete tool-module DATA, never observed modules.
	 *
	 * @param array $rows DATA.
	 * @param array $p Valid profile DATA.
	 * @return string|false
	 */
	private static function module_digest( array $rows, array $p ) {
		if ( count( $rows ) < 2 || count( $rows ) > 128 || array_keys( $rows ) !== range( 0, count( $rows ) - 1 ) ) {
			return false;
		}
		$map   = array();
		$total = 0;
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) || 5 !== count( $row ) || array_keys( $row ) !== array( 'name', 'path', 'sha256', 'size', 'role' ) || ! is_string( $row['name'] )
				|| ! preg_match( '/\A[a-z0-9][a-z0-9._-]{0,74}\.(?:dll|exe)\z/D', $row['name'] ) || isset( $map[ $row['name'] ] )
				|| in_array( $row['name'], array( 'php.exe', 'php7.dll', 'php_mysqli.dll' ), true ) || ! self::hex( $row['sha256'], 64 )
				|| ! is_int( $row['size'] ) || $row['size'] < 1 || $row['size'] > 33554432 || ! in_array( $row['role'], array( 'windows', 'observer-tool' ), true ) ) {
				return false;
			}
			$root = 'windows' === $row['role'] ? $p['system'] : $p['tool_root'];
			if ( $root . '/' . $row['name'] !== $row['path'] || ( 'pwsh.exe' === $row['name'] && ( 'observer-tool' !== $row['role'] || $p['tool_sha256'] !== $row['sha256'] ) )
				|| ( 'kernel32.dll' === $row['name'] && 'windows' !== $row['role'] )
				|| ( 'vcruntime140.dll' === $row['name'] && ( 'windows' !== $row['role'] || $p['crt_sha256'] !== $row['sha256'] ) ) ) {
				return false;
			}
			$total += $row['size'];
			if ( $total > 268435456 ) {
				return false;
			}
			$map[ $row['name'] ] = implode( '|', $row );
		}
		if ( ! isset( $map['pwsh.exe'], $map['kernel32.dll'] ) ) {
			return false;
		}
		ksort( $map );
		return hash( 'sha256', implode( "\n", $map ) );
	}

	/** Sticky fixed reason only.
	 *
	 * @param string $reason Safe reason.
	 * @return false
	 */
	private function reject( $reason ) {
		if ( ! $this->failed ) {
			$this->reason = $reason;
		}
		$this->failed = true;
		return false;
	}

	/** Virtual monotonic deadline, not an OS timeout.
	 *
	 * @param mixed $now DATA.
	 * @param bool  $work Work or cleanup.
	 * @return bool
	 */
	private function time( $now, $work ) {
		if ( ! is_int( $now ) || $now < $this->last || $now >= 25000 || ( $work && $now >= 20000 ) ) {
			return $this->reject( 'deadline' );
		}
		$this->last = $now;
		return true;
	}

	/** Worker channel for held/released claims only.
	 *
	 * @param mixed $kind Kind.
	 * @param array $worker Identity DATA.
	 * @param mixed $now Virtual ms.
	 * @return bool
	 */
	public function worker( $kind, array $worker, $now ) {
		if ( $this->failed || ! $this->time( $now, true ) ) {
			return false;
		}
		if ( $worker !== $this->bindings['worker'] || ! ( ( 0 === $this->step && 'held' === $kind ) || ( 5 === $this->step && 'released' === $kind ) ) ) {
			return $this->reject( 'worker-order-or-identity' );
		}
		++$this->step;
		return true;
	}

	/** Controller channel cannot authenticate its caller.
	 *
	 * @param mixed $kind Kind.
	 * @param array $worker Worker DATA.
	 * @param array $observer Observer DATA.
	 * @param array $data Payload DATA.
	 * @param mixed $now Virtual ms.
	 * @return bool
	 */
	public function controller( $kind, array $worker, array $observer, array $data, $now ) {
		if ( $this->failed || ! $this->time( $now, $this->step < 6 ) ) {
			return false;
		}
		if ( $worker !== $this->bindings['worker'] || $observer !== $this->bindings['observer'] ) {
			return $this->reject( 'controller-identity' );
		}
		if ( ( 2 === $this->step || 3 === $this->step ) && $now - $this->observer_start >= 2000 ) {
			return $this->reject( 'observer-deadline' );
		}
		$exit  = array(
			'exit'    => 0,
			'drained' => true,
			'absent'  => true,
		);
		$valid = ( 1 === $this->step && 'observer-started' === $kind && array() === $data )
			|| ( 2 === $this->step && 'modules' === $kind && self::module_digest( $data, $this->profile ) === $this->modules )
			|| ( 3 === $this->step && 'observer-exit' === $kind && $exit === $data )
			|| ( 4 === $this->step && 'release' === $kind && array() === $data )
			|| ( 6 === $this->step && 'worker-exit' === $kind && $exit === $data );
		if ( ! $valid ) {
			return $this->reject( 'controller-order-or-data' );
		}
		if ( 1 === $this->step ) {
			$this->observer_start = $now;
		}
		++$this->step;
		return true;
	}

	/** Cleanup after success/refusal never reopens authority.
	 *
	 * @param array $worker Worker DATA.
	 * @param array $observer Observer DATA.
	 * @param array $data Cleanup DATA.
	 * @param mixed $now Virtual ms.
	 * @return bool
	 */
	public function cleanup( array $worker, array $observer, array $data, $now ) {
		if ( ! $this->time( $now, false ) ) {
			return false;
		}
		if ( $this->clean || ( ! $this->failed && 7 !== $this->step ) || $worker !== $this->bindings['worker'] || $observer !== $this->bindings['observer']
			|| array(
				'worker_absent'   => true,
				'observer_absent' => true,
				'pipes_drained'   => true,
				'fixtures_absent' => true,
				'unchanged'       => true,
			) !== $data ) {
			return $this->reject( 'cleanup-data' );
		}
		$this->clean = true;
		if ( ! $this->failed ) {
			++$this->step;
		}
		return true;
	}

	/** Safe supplied-DATA summary only, no paths, handles or actual approval. @return array */
	public function snapshot() {
		return array(
			'step'                        => $this->step,
			'failed'                      => $this->failed,
			'reason'                      => $this->reason,
			'synthetic_complete'          => ! $this->failed && 8 === $this->step,
			'cleanup_consistent'          => $this->clean,
			'execution'                   => false,
			'load_authorized'             => false,
			'human_approval_verified'     => false,
			'platform_verified'           => false,
			'signature_verified'          => false,
			'offline_provenance_verified' => false,
			'native_qualified'            => false,
		);
	}
}
