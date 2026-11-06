<?php
/**
 * Unregistered result-only request composition. No application caller or wiring.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The inert wrapper is not authority; only post-record assembly issues peers. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Composition {
	/** Reentry barrier includes guard callbacks.
	 *
	 * @var bool
	 */
	private static $request_running = false;
	/** Inert, guarding, assembling, running, closing or closed.
	 *
	 * @var string
	 */
	private $request_phase = 'inert';
	/** Fixed unexported graph.
	 *
	 * @var array
	 */
	private $request_peers = array();
	/** Consumed one-class construction lease.
	 *
	 * @var string|null
	 */
	private $request_pending = null;
	/** Non-secret code-owned declaration catalog.
	 *
	 * @var array|null
	 */
	private $request_policy = null;

	/** Unregistered command, never a factory or global POST reader.
	 *
	 * @param mixed $request Raw command DATA.
	 * @return string|WP_Error
	 */
	public static function request_submit( $request ) {
		return self::request_run( $request, null );
	}

	/** Same existing guarded view policy; no arbitrary operation selector.
	 *
	 * @param mixed $site Raw site.
	 * @param mixed $source Fixed source.
	 * @return array|WP_Error
	 */
	public static function request_view( $site, $source ) {
		return self::request_run( $site, array( $source ) );
	}

	/** Execute one fixed result-only operation with independent terminal cleanup.
	 *
	 * @param mixed      $input Raw DATA.
	 * @param array|null $view Null only for command.
	 * @return array|string|WP_Error
	 */
	private static function request_run( $input, $view ) {
		if ( self::$request_running ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		self::$request_running = true;
		$root                  = new self();
		$root->request_phase   = 'guarding';
		try {
			$result = null === $view ? $root->submit( $input ) : $root->view( $input, $view[0] );
		} catch ( Throwable $error ) {
			$result = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		} finally {
			$root->request_phase   = 'closing'; // Revoke before cleanup, including partial construction.
			$root->request_pending = null;
			$release_error         = false;
			try {
				if ( isset( $root->request_peers['context'] ) ) {
					$root->request_peers['context']->finish();
				} elseif ( isset( $root->request_peers['connection'] ) ) {
					$root->request_peers['connection']->close();
				}
			} catch ( Throwable $error ) {
				$release_error = true;
			}
			foreach ( $root->request_peers as $peer ) {
				try {
					$release_error = ! $peer->release_request( $root ) || $release_error;
				} catch ( Throwable $error ) {
					$release_error = true;
				}
			}
			$root->request_peers   = array();
			$root->request_policy  = null;
			$root->owner           = null;
			$root->sites           = null;
			$root->request_phase   = 'closed';
			self::$request_running = false;
			if ( $release_error ) {
				$uncertain = 'saved' === $result || ( is_object( $result ) && 'WP_Error' === get_class( $result ) && 'source_policy_write_uncertain' === $result->get_error_code() );
				$result    = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( $uncertain ? 'source_policy_write_uncertain' : 'source_policy_unavailable' );
			}
		}
		return $result;
	}

	/** Existing fixture collaborators keep their original path; no caller injection.
	 *
	 * @param int $site Admitted identity.
	 * @return bool
	 */
	private function request_owner( $site ) {
		if ( null === $this->owner && 'guarding' !== $this->request_phase ) {
			return false;
		}
		if ( null === $this->sites && 'guarding' === $this->request_phase && class_exists( 'Alynt_Drime_Backups_Dashboard_Site_Repository', false ) ) {
			$this->sites = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		}
		if ( ! $this->sites || ! $this->record_matches( $site ) ) {
			return false;
		}
		if ( null !== $this->owner ) {
			return true;
		}
		require_once dirname( __DIR__ ) . '/class-source-policy-lazy-provider.php';
		$this->request_policy = Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Source::request_policy( $GLOBALS['wp_version'] ?? null );
		if ( false === $this->request_policy || ! class_exists( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection', false ) ) {
			return false; // Unsupported declarations refuse before config/peer allocation.
		}
		$this->request_phase = 'assembling';
		$connection          = Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::from_request( $this );
		$query               = Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context::from_request( $this, $connection, $this->request_policy['root'] . $this->request_policy['files']['core_file'] );
		$context             = Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context::from_request( $this, $connection, $query );
		$adapter             = Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter::from_request( $this, $connection );
		$provider            = Alynt_Drime_Backups_Dashboard_Source_Policy_Lazy_Provider::from_request( $this, $adapter, $context );
		if ( 5 !== count( $this->request_peers ) || null === $provider ) {
			return false;
		}
		$this->owner         = $provider;
		$this->request_phase = 'running';
		return true;
	}

	/** Class-owned one-time lease, never a caller selector with an exported root.
	 *
	 * @param string $role Fixed role literal.
	 * @param mixed  $first Exact peer/source dependency.
	 * @param mixed  $second Exact dependency.
	 * @return bool
	 */
	public function request_claim( $role, $first = null, $second = null ) {
		$roles = array( 'connection', 'query', 'context', 'adapter', 'provider' );
		if ( 'assembling' !== $this->request_phase || null !== $this->request_pending || ( $roles[ count( $this->request_peers ) ] ?? null ) !== $role ) {
			return false;
		}
		$peers = $this->request_peers;
		$want  = array(
			'connection' => array( null, null ),
			'query'      => array( $peers['connection'] ?? null, $this->request_policy['root'] . $this->request_policy['files']['core_file'] ),
			'context'    => array( $peers['connection'] ?? null, $peers['query'] ?? null ),
			'adapter'    => array( $peers['connection'] ?? null, null ),
			'provider'   => array( $peers['adapter'] ?? null, $peers['context'] ?? null ),
		);
		// phpcs:ignore WordPress.PHP.YodaConditions -- Exact peer dependency identity, never scalar normalization.
		if ( $want[ $role ] !== array( $first, $second ) ) {
			return false;
		}
		$this->request_pending = $role;
		return true;
	}

	/** Retain the exact class-owned peer for the consumed role lease.
	 *
	 * @param object $peer Newly class-owned peer.
	 * @return bool
	 */
	public function request_retain( $peer ) {
		$classes = array(
			'connection' => 'No_Reconnect_Connection',
			'query'      => 'Query_Context',
			'context'    => 'Trusted_Context',
			'adapter'    => 'Native_Adapter',
			'provider'   => 'Lazy_Provider',
		);
		$role    = $this->request_pending;
		if ( 'assembling' !== $this->request_phase || ! isset( $classes[ $role ] ) || ! is_object( $peer ) || isset( $this->request_peers[ $role ] )
			// phpcs:ignore WordPress.PHP.YodaConditions -- Exact code-owned class identity, no caller class selection.
			|| 'Alynt_Drime_Backups_Dashboard_Source_Policy_' . $classes[ $role ] !== get_class( $peer ) ) {
			return false;
		}
		$this->request_peers[ $role ] = $peer;
		$this->request_pending        = null;
		return true;
	}

	/** Compare peer identity only; do not expose it.
	 *
	 * @param object $peer Exact retained peer.
	 * @param bool   $closing Cleanup only.
	 * @return bool
	 */
	public function request_contains( $peer, $closing = false ) {
		return ( $closing ? 'closing' : 'running' ) === $this->request_phase && in_array( $peer, $this->request_peers, true );
	}

	/** Validate the exact private handoff tuple.
	 *
	 * @param object $receiver Exact receiver.
	 * @param object $query Exact query.
	 * @param object $context Exact context.
	 * @return bool
	 */
	public function request_bound( $receiver, $query, $context ) {
		return 'running' === $this->request_phase && ( $this->request_peers['connection'] ?? null ) === $receiver
			&& ( $this->request_peers['query'] ?? null ) === $query && ( $this->request_peers['context'] ?? null ) === $context;
	}

	/** Compare the context's own selection to our frozen catalog, never export it.
	 *
	 * @param object $context Exact retained context.
	 * @param mixed  $policy Context-selected non-secret declaration DATA.
	 * @return bool
	 */
	public function request_catalog_matches( $context, $policy ) {
		return 'running' === $this->request_phase && isset( $this->request_peers['context'] ) && $this->request_peers['context'] === $context
			// phpcs:ignore WordPress.PHP.YodaConditions -- Exact frozen catalog identity, never caller-supplied authority.
			&& is_array( $policy ) && $this->request_policy === $policy;
	}

	/** Reject serialization of the operation identity.
	 *
	 * @throws RuntimeException Always.
	 */
	public function __sleep() {
		throw new RuntimeException( 'Request root cannot be serialized' );
	}

	/** Reject crafted serialized identities as well as our own.
	 *
	 * @throws RuntimeException Always.
	 */
	public function __wakeup() {
		throw new RuntimeException( 'Request root cannot be restored' );
	}

	/** No duplicate operation identity. */
	private function __clone() {}
}
