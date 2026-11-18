<?php
/**
 * Dormant initializer worker composition for isolated API tests only.
 * No CLI entry, core loader, SQL executor, native adapter or runtime authority.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Fixed callback composition; supplied observations remain model DATA. */
final class ADBH_Initialized_Worker_Preparation {

	/** Copied scalar ticket.
	 *
	 * @var array
	 */
	private $ticket;
	/** Private query admission, never a DB handle.
	 *
	 * @var ADBH_Initialization_Query_Guard|null
	 */
	private $guard;
	/** Isolated callbacks; no native implementation is provided.
	 *
	 * @var array
	 */
	private $api;
	/** One-use worker state.
	 *
	 * @var string
	 */
	private $state = 'fresh';
	/** Own registration attempts only, for exact callback cleanup.
	 *
	 * @var array
	 */
	private $registered = array();
	/** Last original virtual timestamp.
	 *
	 * @var int
	 */
	private $last;

	/**
	 * Bind the existing query guard before any modeled bootstrap or hook effect.
	 *
	 * @param array $ticket Private initializer credit DATA.
	 * @param array $identity Supplied owned identity DATA, not attestation.
	 * @param array $facts Fresh preflight DATA.
	 * @param array $api Explicit isolated closures.
	 */
	public function __construct( $ticket, $identity, $facts, $api ) {
		$this->check( null === $this->guard && 'fresh' === $this->state, 'initializer-reentry' );
		$this->check( adbh_installer_shape( $api, array( 'kind', 'clock', 'bootstrap', 'shutdown', 'observe' ) ) && 'isolated-initializer-worker-v2' === $api['kind'], 'initializer-api' );
		foreach ( array( 'clock', 'bootstrap', 'shutdown', 'observe' ) as $method ) {
			$this->check( $api[ $method ] instanceof Closure, 'initializer-api' );
		}
		$this->api = array();
		foreach ( $api as $key => $value ) {
			$this->api[ $key ] = $value;
		}
		$this->check( adbh_installer_identity( $identity ), 'initializer-identity' );
		$at          = $this->clock();
		$this->guard = new ADBH_Initialization_Query_Guard( $ticket, $facts, $at );
		// Identity grammar is ASCII with no quotes/backslashes. Match JS's exact
		// canonical identity record without requiring JSON in PHP7.4 -n tests.
		$canonical = '{"run":"' . $identity['run'] . '","head":"' . $identity['head'] . '","image":"' . $identity['image'] . '","root":"' . $identity['root'] . '"}';
		$this->check( $ticket['run'] === $identity['run'] && $ticket['head'] === $identity['head'] && $ticket['image'] === $identity['image'] && hash( 'sha256', $canonical ) === $ticket['owner'], 'initializer-binding' );
		$this->ticket = array();
		foreach ( $ticket as $key => $value ) {
			$this->ticket[ $key ] = $value;
		}
		$this->last = $at;
	}

	/**
	 * Terminal fixed-label refusal; never retain callback errors or arguments.
	 *
	 * @param bool   $ok Condition.
	 * @param string $label Private source-owned label.
	 * @throws RuntimeException On refusal.
	 */
	private function check( $ok, $label ) {
		if ( ! $ok ) {
			$this->state = 'failed';
			throw new RuntimeException( $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed private labels, not HTML or caller text.
		}
	}

	/** Fixed priorities: query follows core's priority-zero placeholder restoration.
	 *
	 * @return array
	 */
	private function hooks() {
		return array(
			'query'                    => array( 'query', 1, 1 ),
			'pre_http_request'         => array( 'deny', 10, 3 ),
			'flush_rewrite_rules_hard' => array( 'deny', 10, 1 ),
			'block_local_requests'     => array( 'deny', 10, 1 ),
			'pre_wp_mail'              => array( 'deny', 10, 2 ),
		);
	}

	/** Original virtual setup clock, including intervals without a query.
	 *
	 * @return int
	 */
	private function now() {
		$at = $this->clock();
		$this->check( is_int( $at ) && $at >= $this->last && $at < $this->ticket['setup_deadline'], 'initializer-time' );
		$this->last = $at;
		return $at;
	}

	/** Fixed safe boundary even when the first isolated clock callback fails.
	 *
	 * @return mixed Original value, subsequently range/type checked.
	 */
	private function clock() {
		try {
			return ( $this->api['clock'] )();
		} catch ( Throwable $error ) {
			$this->check( false, 'initializer-clock' );
		}
	}

	/**
	 * The actual per-query guard runs before the isolated simulated executor.
	 *
	 * @param mixed $sql Supplied query bytes.
	 * @return string
	 */
	public function query( $sql ) {
		$this->check( 'running' === $this->state, 'initializer-order' );
		return $this->guard->filter( $sql, $this->now() );
	}

	/** No installer exception: any attempted transport/mail/hard-file effect fails.
	 *
	 * @throws RuntimeException Always, before the modeled effect.
	 */
	public function deny() {
		$this->check( false, 'initializer-effect' );
	}

	/** Require our callbacks at the fixed priorities, not a caller acknowledgment. */
	private function ready() {
		$this->check( 'running' === $this->state, 'initializer-order' );
		foreach ( $this->hooks() as $hook => $spec ) {
			$this->check( has_filter( $hook, array( $this, $spec[0] ) ) === $spec[1], 'initializer-hook' );
		}
	}

	/**
	 * One modeled normal bootstrap and shutdown, then observed facts and cleanup.
	 * These closures are not genuine WordPress/shutdown or native cancellation proof.
	 *
	 * @return array Count/binding completion DATA only.
	 * @throws RuntimeException On unavailable evidence or unknown callback cleanup.
	 */
	public function run() {
		$this->check( 'fresh' === $this->state, 'initializer-replay' );
		$this->check( function_exists( 'add_filter' ) && function_exists( 'remove_filter' ) && function_exists( 'has_filter' ), 'initializer-hooks-unavailable' );
		$this->state = 'running';
		$completion  = null;
		$failed      = false;
		try {
			foreach ( $this->hooks() as $hook => $spec ) {
				$this->registered[ $hook ] = $spec;
				$this->check( true === add_filter( $hook, array( $this, $spec[0] ), $spec[1], $spec[2] ), 'initializer-register' );
			}
			$this->ready();
			$this->now();
			$this->check( true === ( $this->api['bootstrap'] )(), 'initializer-bootstrap' );
			$this->ready();
			$this->check( true === ( $this->api['shutdown'] )(), 'initializer-shutdown' );
			$this->ready();
			$observed = ( $this->api['observe'] )();
			$expected = array(
				'full_bootstrap'    => true,
				'installing'        => false,
				'short_init'        => false,
				'protected_reads'   => 0,
				'protected_writes'  => 0,
				'owner_allocations' => 0,
				'session_writes'    => 0,
				'mail'              => 0,
				'outbound'          => 0,
				'file_writes'       => 0,
				'source_unchanged'  => true,
				'anchors_unchanged' => true,
			);
			$this->check( $observed === $expected, 'initializer-observation' );
			$this->ready();
			$this->now();
			$completion = array_merge( $this->ticket, array( 'sql' => $this->guard->finish() ), $observed );
		} catch ( Throwable $error ) {
			$this->state = 'failed';
			$failed      = true;
		} finally {
			$clean = true;
			foreach ( $this->registered as $hook => $spec ) {
				try {
					$callback = array( $this, $spec[0] );
					if ( false !== has_filter( $hook, $callback ) ) {
						remove_filter( $hook, $callback, $spec[1] );
					}
					$clean = false === has_filter( $hook, $callback ) && $clean;
				} catch ( Throwable $error ) {
					$clean = false;
				}
			}
			$this->check( $clean, $failed ? 'Initializer worker preparation refused; cleanup unconfirmed' : 'Initializer worker cleanup unconfirmed' );
		}
		$this->check( ! $failed && 'running' === $this->state, 'Initializer worker preparation refused' );
		$this->now();
		$this->state = 'closed';
		return array(
			'event'            => 'initializer-worker-prepared-v2',
			'completion'       => $completion,
			'callbacks_closed' => true,
			'runtime_grant'    => false,
			'production_ready' => false,
		);
	}
}

const ADBH_INITIALIZED_WORKER_EXECUTION_ENABLED = false;

require_once __DIR__ . '/source-policy-http-initialization.php';
require_once __DIR__ . '/source-policy-http-installer-data.php';
