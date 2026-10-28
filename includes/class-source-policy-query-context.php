<?php
/**
 * Closed, private query registration/provenance lifecycle; no native provider.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/class-source-policy-error.php';

/** Receivers/source bindings are provisioned only by isolated fixtures here. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context {
	/** Fixed owned receiver.
	 *
	 * @var object|null
	 */
	private $owned = null;
	/** Fixed ambient receiver, never adopted from callbacks.
	 *
	 * @var object|null
	 */
	private $ambient = null;
	/** Qualified core source binding; no provider exists here.
	 *
	 * @var string|null
	 */
	private $core_file = null;
	/** Exact private owned class binding.
	 *
	 * @var string
	 */
	private $owned_class = 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection';
	/** Exact private owned class source binding.
	 *
	 * @var string
	 */
	private $owned_file = __DIR__ . '/class-source-policy-no-reconnect-connection.php';
	/** Single-use state.
	 *
	 * @var string
	 */
	private $state = 'fresh';
	/** Original query metadata, retaining exact object identities.
	 *
	 * @var array|null
	 */
	private $initial = null;
	/** Sealed query metadata.
	 *
	 * @var array|null
	 */
	private $sealed = null;
	/** Other relevant hook fingerprint before warm-up.
	 *
	 * @var string|null
	 */
	private $other_hooks = null;
	/** Already-present ambient callback.
	 *
	 * @var bool
	 */
	private $ambient_present = false;
	/** Registration ownership captured before warm-up.
	 *
	 * @var bool
	 */
	private $leased = false;
	/** Cleanup has been attempted once.
	 *
	 * @var bool
	 */
	private $finished = false;
	/** Confirmed callback cleanup.
	 *
	 * @var bool
	 */
	private $clean = false;

	/** Opens/registers nothing. */
	private function __construct() {}

	/** Production provisioning remains closed.
	 *
	 * @return WP_Error
	 */
	public static function create() {
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
	}

	/** Establishes only the verified owned callback before sealing.
	 *
	 * @return bool
	 */
	public function begin() {
		if ( 'fresh' !== $this->state ) {
			return false;
		}
		$this->state = 'opening';
		try {
			if ( ! $this->provenance() ) {
				return false;
			}
			$this->initial         = $this->metadata();
			$this->ambient_present = null !== $this->ambient && false !== has_filter( 'query', array( $this->ambient, 'remove_placeholder_escape' ) );
			$this->other_hooks     = Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::fingerprint( true );
			if ( ! $this->allowed( $this->initial, false ) || ! is_string( $this->other_hooks ) ) {
				return false;
			}
			$this->leased = true;
			$this->owned->placeholder_escape();
			$this->sealed = $this->metadata();
			if ( ! $this->allowed( $this->sealed, true ) || Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::fingerprint( true ) !== $this->other_hooks ) {
				return false;
			}
			$this->state = 'active';
			return true;
		} catch ( Throwable $error ) {
			$this->state = 'rejected';
			return false; // Fixed refusal; owner still performs independent terminal cleanup.
		}
	}

	/** Never refreshes a changed registry or source binding.
	 *
	 * @return bool
	 */
	public function valid() {
		return 'active' === $this->state && $this->provenance() && $this->sealed === $this->metadata()
			&& Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::fingerprint( true ) === $this->other_hooks;
	}

	/**
	 * Compares identity only; exports no receiver/handle.
	 *
	 * @param object $receiver Private connection.
	 * @return bool
	 */
	public function owns( $receiver ) {
		return $this->owned === $receiver;
	}

	/** Removes only the exact leased entry, independently of native close.
	 *
	 * @return bool
	 */
	public function finish() {
		if ( $this->finished ) {
			return $this->clean;
		}
		$this->finished = true;
		$this->state    = 'closing';
		try {
			$entries = $this->metadata();
			if ( null === $entries || null === $this->initial ) {
				return false;
			}
			foreach ( $entries as $entry ) {
				if ( $this->leased && 0 === $entry[0] && 1 === $entry[2] && array( $this->owned, 'remove_placeholder_escape' ) === $entry[1] ) {
					remove_filter( 'query', array( $this->owned, 'remove_placeholder_escape' ), 0 );
					break;
				}
			}
			$after       = $this->metadata();
			$this->clean = $this->initial === $after;
			foreach ( $after ?? array() as $entry ) {
				if ( array( $this->owned, 'remove_placeholder_escape' ) === $entry[1] ) {
					$this->clean = false;
				}
			}
			return $this->clean;
		} catch ( Throwable $error ) {
			return false; // Unconfirmed cleanup; do not repair foreign entries or replay removal.
		} finally {
			$this->state = $this->clean ? 'closed' : 'cleanup_failed';
		}
	}

	/** Checks metadata only, including the virtual placeholder dependency.
	 *
	 * @return bool
	 */
	private function provenance() {
		if ( ! is_object( $this->owned ) || get_class( $this->owned ) !== $this->owned_class || ! $this->owned instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection
			|| ! is_string( $this->core_file ) || ! is_string( $this->owned_file ) || $this->owned === $this->ambient
			|| false === realpath( $this->core_file ) || false === realpath( $this->owned_file )
			|| ( $GLOBALS['wpdb'] ?? null ) !== $this->ambient ) {
			return false;
		}
		if ( ( new ReflectionClass( $this->owned ) )->getFileName() !== realpath( $this->owned_file ) ) {
			return false;
		}
		$receivers = array( $this->owned );
		if ( null !== $this->ambient ) {
			if ( ! is_object( $this->ambient ) || 'wpdb' !== get_class( $this->ambient ) ) {
				return false;
			}
			$receivers[] = $this->ambient;
		}
		foreach ( $receivers as $receiver ) {
			foreach ( array( 'prepare', '_real_escape', 'add_placeholder_escape', 'placeholder_escape', 'remove_placeholder_escape' ) as $method ) {
				$definition = new ReflectionMethod( $receiver, $method );
				if ( 'wpdb' !== $definition->getDeclaringClass()->getName() || $definition->getFileName() !== realpath( $this->core_file ) ) {
					return false;
				}
			}
		}
		foreach ( array( 'query', 'close' ) as $method ) {
			$definition = new ReflectionMethod( 'wpdb', $method );
			if ( 'wpdb' !== $definition->getDeclaringClass()->getName() || $definition->getFileName() !== realpath( $this->core_file ) ) {
				return false;
			}
		}
		foreach ( array( 'query', 'execute', 'prepare_checked', 'admit_profile', 'db_connect', 'check_connection', 'print_error', 'close' ) as $method ) {
			$definition = new ReflectionMethod( $this->owned, $method );
			if ( ! $definition->isFinal() || 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' !== $definition->getDeclaringClass()->getName() || $definition->getFileName() !== realpath( __DIR__ . '/class-source-policy-no-reconnect-connection.php' ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Accepts at most the fixed two receivers at exact core priority/argument count.
	 *
	 * @param array|null $entries Bounded actual metadata.
	 * @param bool       $owned_required Whether warm-up has completed.
	 * @return bool
	 */
	private function allowed( $entries, $owned_required ) {
		if ( ! is_array( $entries ) || count( $entries ) > 2 ) {
			return false;
		}
		$owned   = 0;
		$ambient = 0;
		foreach ( $entries as $entry ) {
			if ( 0 !== $entry[0] || 1 !== $entry[2] ) {
				return false;
			}
			if ( array( $this->owned, 'remove_placeholder_escape' ) === $entry[1] ) {
				++$owned;
			} elseif ( null !== $this->ambient && array( $this->ambient, 'remove_placeholder_escape' ) === $entry[1] ) {
				++$ambient;
			} else {
				return false;
			}
		}
		return (int) $owned_required === $owned && (int) $this->ambient_present === $ambient;
	}

	/** Bounds metadata without invoking callback/magic/autoload.
	 *
	 * @return array|null
	 */
	private function metadata() {
		global $wp_filter;
		if ( isset( $wp_filter ) && ! is_array( $wp_filter ) ) {
			return null;
		}
		if ( ! isset( $wp_filter['query'] ) ) {
			return array();
		}
		$registry = $wp_filter['query'];
		if ( ! is_object( $registry ) || 'WP_Hook' !== get_class( $registry ) || ! is_array( $registry->callbacks ) || count( $registry->callbacks ) > 32 ) {
			return null;
		}
		$metadata = array();
		foreach ( $registry->callbacks as $priority => $entries ) {
			if ( ! is_int( $priority ) || ! is_array( $entries ) || count( $entries ) > 64 ) {
				return null;
			}
			foreach ( $entries as $entry ) {
				if ( count( $metadata ) >= 64 || ! is_array( $entry ) || 2 !== count( $entry ) || array( 'function', 'accepted_args' ) !== array_keys( $entry ) || ! is_int( $entry['accepted_args'] ) ) {
					return null;
				}
				$callback = $entry['function'];
				if ( is_array( $callback ) && ( 2 !== count( $callback ) || array( 0, 1 ) !== array_keys( $callback ) || ! is_object( $callback[0] ) || ! is_string( $callback[1] ) ) ) {
					return null;
				}
				$metadata[] = array( $priority, $entry['function'], $entry['accepted_args'] );
			}
		}
		return $metadata;
	}
}
