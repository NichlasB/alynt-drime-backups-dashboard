<?php
/**
 * Dormant operation-local capture; no public mint or native provisioning.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/class-source-policy-trusted-source.php';

/** Private bindings are composed only by isolated fixtures in this foundation. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context {
	/** Exact owned receiver, never an ambient handle.
	 *
	 * @var object|null
	 */
	private $receiver = null;
	/** Captured stock ambient identity.
	 *
	 * @var object|null
	 */
	private $ambient = null;
	/** Fixed expected declaration policy, never learned from current hashes.
	 *
	 * @var array|null
	 */
	private $policy = null;
	/** Private transient settings, including synthetic credentials in API tests.
	 *
	 * @var array|null
	 */
	private $frame = null;
	/** Fixed declaration binding.
	 *
	 * @var array|null
	 */
	private $binding = null;
	/** Single operation lifecycle.
	 *
	 * @var string
	 */
	private $state = 'fresh';

	/** No protected reads, callbacks, settings or allocation on construction. */
	private function __construct() {}

	/** No production context or fixture policy can be minted publicly.
	 *
	 * @return WP_Error
	 */
	public static function create() {
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
	}

	/**
	 * Capture only after the guarded entry admits the request. Exports no settings.
	 *
	 * @return bool Consistency only, not native/bootstrap authority.
	 */
	public function capture() {
		if ( 'fresh' !== $this->state ) {
			return false;
		}
		$this->state = 'capturing';
		try {
			$core   = $GLOBALS['wp_version'] ?? null;
			$policy = $this->policy ?? Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Source::policy( $core );
			if ( ! is_array( $policy ) || $core !== $policy['core'] || PHP_VERSION !== $policy['php'] || ! $this->checked_declarations( $policy ) ) {
				return false;
			}
			$this->policy  = $policy;
			$this->ambient = $GLOBALS['wpdb']; // Exact declaration identity was checked before reading configuration.
			$this->frame   = $this->settings();
			$this->state   = 'captured';
			return $this->valid( $this->receiver );
		} catch ( Throwable $error ) {
			return false; // Provider owns terminal cleanup; raw diagnostics never escape.
		}
	}

	/**
	 * Requires the same receiver/configuration/declarations; never reseals changes.
	 *
	 * @param object $receiver Exact adapter receiver.
	 * @return bool
	 */
	public function valid( $receiver ) {
		try {
			if ( 'captured' !== $this->state || null === $this->frame || ! is_array( $this->policy ) || ! isset( $this->policy['core'] ) || $this->receiver !== $receiver
				|| ( $GLOBALS['wpdb'] ?? null ) !== $this->ambient || ( $GLOBALS['wp_version'] ?? null ) !== $this->policy['core'] || ! $this->checked_declarations( $this->policy )
				|| $this->frame !== $this->settings() || ! Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::settings( $this->frame )
				|| ! Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::source( $receiver, $this->binding ) ) {
				return false;
			}
			$facts = $receiver->profile();
			return is_array( $facts ) && isset( $facts['core'], $facts['database'], $facts['table'] )
				&& $this->frame['core'] === $facts['core'] && $this->frame['database'] === $facts['database']
				&& $this->frame['table'] === $facts['table'] && $receiver->options === $this->frame['table'];
		} catch ( Throwable $error ) {
			return false;
		}
	}

	/** Revokes before fallible close; clears all retained settings/references.
	 *
	 * @return bool Confirmed owned cleanup only.
	 */
	public function finish() {
		$receiver       = $this->receiver;
		$this->state    = 'closed';
		$this->receiver = null;
		$this->ambient  = null;
		$this->frame    = null;
		$this->binding  = null;
		$this->policy   = null;
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Cleanup warnings are unknown release, never raw diagnostics or success.
		set_error_handler(
			static function () {
				throw new RuntimeException( 'Policy cleanup unavailable' );
			}
		);
		try {
			return $this->owned( $receiver ) && true === $receiver->close();
		} catch ( Throwable $error ) {
			return false;
		} finally {
			restore_error_handler();
		}
	}

	/** No raw filesystem diagnostic or replacement of the caller's error handler.
	 *
	 * @param array $policy Fixed policy.
	 * @return bool
	 * @throws RuntimeException Caught by capture/valid on IO warnings.
	 */
	private function checked_declarations( array $policy ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Scoped warning-to-refusal conversion, never suppression or raw filesystem output.
		set_error_handler(
			static function () {
				throw new RuntimeException( 'Policy source unavailable' );
			}
		);
		try {
			return $this->declarations( $policy );
		} finally {
			restore_error_handler();
		}
	}

	/**
	 * Fixed source and empty non-query observer policy, not a live fingerprint grant.
	 *
	 * @param array $policy Privately selected fixed expectations.
	 * @return bool
	 */
	private function declarations( array $policy ) {
		$ambient = $GLOBALS['wpdb'] ?? null;
		if ( ! $this->owned( $this->receiver ) || ! is_object( $ambient ) || 'wpdb' !== get_class( $ambient )
			|| $ambient === $this->receiver || ( isset( $GLOBALS['wp_filter'] ) && ! is_array( $GLOBALS['wp_filter'] ) )
			|| count( $policy['pins'] ) > 12 || ! $policy['pins'] ) {
			return false;
		}
		foreach ( $policy['pins'] as $file => $digest ) {
			$path = $policy['root'] . $file;
			if ( ! is_string( $digest ) || 64 !== strlen( $digest ) || ! is_file( $path ) || filesize( $path ) > 524288 || hash_file( 'sha256', $path ) !== $digest ) {
				return false;
			}
		}
		$binding = array(
			'driver_file'    => $policy['driver'],
			'observers'      => array(),
			'observer_files' => array(),
		);
		foreach ( $policy['files'] as $key => $file ) {
			$binding[ $key ] = realpath( $policy['root'] . $file );
		}
		if ( ( new ReflectionClass( $ambient ) )->getFileName() !== $binding['core_file']
			|| ! class_exists( 'WP_Hook', false ) || ( new ReflectionClass( 'WP_Hook' ) )->getFileName() !== $binding['hook_file'] ) {
			return false;
		}
		foreach ( Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::hook_names() as $hook ) {
			if ( 'query' !== $hook && isset( $GLOBALS['wp_filter'][ $hook ] ) ) {
				$registry = $GLOBALS['wp_filter'][ $hook ];
				if ( ! is_object( $registry ) || 'WP_Hook' !== get_class( $registry ) || array() !== $registry->callbacks ) {
					return false;
				}
			}
		}
		if ( null !== $this->binding && $this->binding !== $binding ) {
			return false;
		}
		$this->binding = $binding;
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::source( $this->receiver, $binding );
	}

	/** Only exact owned declaration; never invoke an unknown cleanup collaborator.
	 *
	 * @param mixed $receiver Private receiver.
	 * @return bool
	 */
	private function owned( $receiver ) {
		return is_object( $receiver ) && 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' === get_class( $receiver )
			&& ( $GLOBALS['wpdb'] ?? null ) !== $receiver
			&& ( new ReflectionClass( $receiver ) )->getFileName() === realpath( __DIR__ . '/class-source-policy-no-reconnect-connection.php' );
	}

	/** Trusted constants/stock ambient only; no file bootstrap, filter or public DTO.
	 *
	 * @return array|null
	 * @throws RuntimeException Caught by capture/valid on parser diagnostics.
	 */
	private function settings() {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Do not emit parser/configuration diagnostics or suppress-and-continue.
		set_error_handler(
			static function () {
				throw new RuntimeException( 'Policy settings unavailable' );
			}
		);
		try {
			return $this->read_settings();
		} finally {
			restore_error_handler();
		}
	}

	/** Read only after fixed source binding, returning solely to private capture.
	 *
	 * @return array|null
	 */
	private function read_settings() {
		foreach ( array( 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST', 'DB_CHARSET', 'DB_COLLATE' ) as $name ) {
			if ( ! defined( $name ) || ! is_string( constant( $name ) ) || strlen( constant( $name ) ) > 1024 ) {
				return null;
			}
		}
		$parsed = $this->ambient->parse_db_host( DB_HOST ); // Only after exact stock source/declaration binding.
		if ( ! is_array( $parsed ) || 4 !== count( $parsed ) || array( 0, 1, 2, 3 ) !== array_keys( $parsed ) ) {
			return null;
		}
		$public = get_object_vars( $this->ambient );
		if ( ! isset( $public['options'] ) || ! is_string( $public['options'] ) || strlen( $public['options'] ) > 64 ) {
			return null;
		}
		$port = $parsed[1];
		if ( '6.0' === $this->policy['core'] && is_string( $port ) && 1 === preg_match( '/\A[0-9]{1,5}\z/', $port ) ) {
			$port = (int) $port; // Exact archived parser type, still checked against the full raw tuple below.
		}
		$frame = array(
			'core'      => $this->policy['core'],
			'database'  => DB_NAME,
			'table'     => $this->ambient->options,
			'host'      => $parsed[0],
			'port'      => $port,
			'user'      => DB_USER,
			'password'  => DB_PASSWORD,
			'charset'   => DB_CHARSET,
			'collate'   => DB_COLLATE,
			'transport' => null === $parsed[2] ? 'tcp_loopback' : 'unix_socket',
			'socket'    => $parsed[2],
			'db_host'   => DB_HOST,
		);
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed( $frame, $parsed ) ? $frame : null;
	}

	/** No context/credential export through PHP serialization.
	 *
	 * @throws RuntimeException Always.
	 */
	public function __sleep() {
		throw new RuntimeException( 'Policy context cannot be serialized' );
	}

	/** Reject crafted serialized instances as well as our own serialization.
	 *
	 * @throws RuntimeException Always.
	 */
	public function __wakeup() {
		throw new RuntimeException( 'Policy context cannot be restored' );
	}

	/** No duplicate operation-local identity. */
	private function __clone() {}
}
