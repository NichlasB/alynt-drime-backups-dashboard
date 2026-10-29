<?php
/**
 * Fixed minimum runner consistency DATA; no process launcher or authority token.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Sticky, ordered synthetic transcript. Never exposed to production bootstrap. */
final class Alynt_Drime_Backups_Dashboard_Minimum_Runner_Transcript {
	/**
	 * Correlation only.
	 *
	 * @var string
	 */
	private $prefix;
	/**
	 * Next bounded event.
	 *
	 * @var int
	 */
	private $step = 0;
	/**
	 * Monotonic elapsed milliseconds, not a real clock.
	 *
	 * @var int
	 */
	private $last = 0;
	/**
	 * First failure is permanent.
	 *
	 * @var bool
	 */
	private $failed = false;

	/**
	 * Creates synthetic correlation state only.
	 *
	 * @param mixed $run Synthetic controller run ID.
	 * @param mixed $image Synthetic image digest, not trusted source admission.
	 * @throws InvalidArgumentException Invalid correlation DATA.
	 */
	public function __construct( $run, $image ) {
		if ( ! is_string( $run ) || ! preg_match( '/\A[a-f0-9]{32}\z/D', $run ) || ! is_string( $image ) || ! preg_match( '/\A[a-f0-9]{64}\z/D', $image ) ) {
			throw new InvalidArgumentException( 'Minimum synthetic correlation refused' );
		}
		$this->prefix = 'ADBM0|' . $run . '|' . $image . '|';
	}

	/**
	 * Exact supplied-profile consistency, never observation/authentication.
	 *
	 * @param array $facts Supplied observations.
	 * @return bool DATA only, never a runtime permit.
	 */
	public static function runtime_data( array $facts ) {
		return array(
			'os'              => 'Windows',
			'php'             => '7.4.33',
			'mysqlnd'         => '7.4.33',
			'int_size'        => 8,
			'zts'             => false,
			'mode'            => 0,
			'core'            => '6.0',
			'mysqli_internal' => true,
			'autoload'        => 'no',
		) === $facts;
	}

	/**
	 * Validates a claimed event, not an artifact, subprocess or external observer.
	 *
	 * @param mixed $line Bounded exact LF frame, never logged or retained.
	 * @param mixed $now Virtual elapsed milliseconds.
	 * @return bool Synthetic consistency only.
	 */
	public function feed( $line, $now ) {
		$spec   = adbmin_catalog();
		$events = array( 'inventory|2', 'pe|3|x64|PE32+', 'source|' . $spec['core_commit'] . '|' . $spec['core_tree'], 'profile|7.4.33|7.4.33|6.0|0|no', 'closed|no-extraction|no-execution' );
		if ( $this->failed || ! is_int( $now ) || $now < $this->last || $now > 600000 || $this->step >= count( $events ) || ! is_string( $line ) || strlen( $line ) > 512
			|| $this->prefix . $events[ $this->step ] . "\n" !== $line ) {
			$this->failed = true;
			return false;
		}
		$this->last = $now;
		++$this->step;
		return true;
	}

	/** Safe non-secret state, never an execution/qualification permit. @return array */
	public function snapshot() {
		$phases = array( 'new', 'inventory', 'pe', 'source', 'profile', 'closed' );
		return array(
			'phase'              => $phases[ $this->step ],
			'failed'             => $this->failed,
			'synthetic_complete' => ! $this->failed && 5 === $this->step,
			'execution'          => false,
			'native_qualified'   => false,
		);
	}
}

// Fixed sibling declarations only; no runtime/source artifact is loaded.
require_once __DIR__ . '/source-policy-minimum-artifacts.php';
