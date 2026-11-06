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
require_once __DIR__ . '/traits/trait-source-policy-trusted-context-source.php';
require_once __DIR__ . '/traits/trait-source-policy-trusted-context-settings.php';
require_once __DIR__ . '/traits/trait-source-policy-production-join.php';
require_once __DIR__ . '/traits/trait-source-policy-request-context.php';

/** Private bindings are composed only by isolated fixtures in this foundation. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context {
	use Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Context;
	use Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context_Source;
	use Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context_Settings;
	use Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Join;

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
			if ( 'production_active' === $this->state ) {
				return $this->production_valid( $receiver );
			}
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
		$this->clear_production();
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
