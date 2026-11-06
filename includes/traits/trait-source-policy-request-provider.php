<?php
/**
 * Class-owned handle-free request peer constructors; never public factories.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/trait-source-policy-request-link.php';

/** Private lazy pair, consumed by the existing provider state machine. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Provider {
	use Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Link;

	/** Construct only the root-owned lazy pair.
	 *
	 * @param object $root Private root.
	 * @param object $owner Exact adapter.
	 * @param object $context Exact context.
	 * @return object|null
	 */
	public static function from_request( $root, $owner, $context ) {
		if ( ! $root instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry || ! $root->request_claim( 'provider', $owner, $context ) ) {
			return null;
		}
		$peer               = new self();
		$peer->request_root = $root;
		$peer->owner        = $owner;
		$peer->context      = $context;
		$peer->bootstrap    = true;
		return $root->request_retain( $peer ) ? $peer : null;
	}
}
