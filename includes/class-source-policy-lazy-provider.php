<?php
/**
 * Dormant lazy capture/dispatch boundary. No native constructor or public mint.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/class-source-policy-trusted-context.php';

/** The fixed owner/context pair remains private and isolated-fixture composed. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Lazy_Provider {
	/** Exact fixed adapter, never a request callback.
	 *
	 * @var object|null
	 */
	private $owner = null;
	/** Operation-local context.
	 *
	 * @var object|null
	 */
	private $context = null;
	/** Consume before capture, callbacks or dispatch.
	 *
	 * @var bool
	 */
	private $used = false;

	/** Constructing a wrapper opens/reads nothing. */
	private function __construct() {}

	/** All actual application provisioning remains disabled.
	 *
	 * @return WP_Error
	 */
	public static function create() {
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
	}

	/** Guarded view interface; never export the owner/context.
	 *
	 * @param mixed $site Site identity.
	 * @param mixed $source Fixed source.
	 * @return array|WP_Error
	 */
	public function view_source_mode( $site, $source ) {
		return $this->run( null, $site, $source, 'required' );
	}

	/** Guarded conditional interface; no implicit legacy/native fallback.
	 *
	 * @param mixed $revision Expected consistency digest.
	 * @param mixed $site Site identity.
	 * @param mixed $source Fixed source.
	 * @param mixed $mode Desired mode.
	 * @return string|WP_Error
	 */
	public function save_source_mode_if_current( $revision, $site, $source, $mode ) {
		if ( ! is_string( $revision ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $revision ) ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_invalid' );
		}
		return $this->run( $revision, $site, $source, $mode );
	}

	/** One operation, independent terminal revocation, no reentry/replay.
	 *
	 * @param string|null $revision Null only for view.
	 * @param mixed       $site Guarded integer identity.
	 * @param mixed       $source Fixed source.
	 * @param mixed       $mode Fixed mode.
	 * @return array|string|WP_Error
	 */
	private function run( $revision, $site, $source, $mode ) {
		if ( $this->used || ! is_int( $site ) || $site <= 0 || 'wpvivid' !== $source || ! in_array( $mode, array( 'required', 'external_optional' ), true ) ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		$this->used = true;
		$result     = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		try {
			if ( $this->owner instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter
				&& $this->context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context && $this->context->capture() ) {
				$result = null === $revision ? $this->owner->view_source_mode( $site, $source, $this->context )
					: $this->owner->save_source_mode_if_current( $revision, $site, $source, $mode, $this->context );
			}
		} catch ( Throwable $error ) {
			$result = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		} finally {
			$context       = $this->context;
			$this->context = null;
			$this->owner   = null;
			if ( ! $context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context || ! $context->finish() ) {
				$uncertain = 'saved' === $result || ( is_object( $result ) && 'WP_Error' === get_class( $result ) && 'source_policy_write_uncertain' === $result->get_error_code() );
				$result    = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( $uncertain ? 'source_policy_write_uncertain' : 'source_policy_unavailable' );
			}
		}
		return $result;
	}

	/** No private owner/context export.
	 *
	 * @throws RuntimeException Always.
	 */
	public function __sleep() {
		throw new RuntimeException( 'Policy provider cannot be serialized' );
	}

	/** No restoration/replay of a private owner/context pair.
	 *
	 * @throws RuntimeException Always.
	 */
	public function __wakeup() {
		throw new RuntimeException( 'Policy provider cannot be restored' );
	}

	/** No replay through cloning. */
	private function __clone() {}
}
