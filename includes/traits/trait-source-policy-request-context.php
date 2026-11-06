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

/** No ready/facts/runtime/source policy is supplied to the context. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Context {
	use Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Link;

	/** Construct only the root-owned context without settings or readiness.
	 *
	 * @param object $root Private root.
	 * @param object $receiver Owned peer.
	 * @param object $query Bound peer.
	 * @return object|null
	 */
	public static function from_request( $root, $receiver, $query ) {
		if ( ! $root instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry || ! $root->request_claim( 'context', $receiver, $query ) ) {
			return null;
		}
		$peer                   = new self();
		$peer->request_root     = $root;
		$peer->receiver         = $receiver;
		$peer->production_query = $query;
		return $root->request_retain( $peer ) ? $peer : null;
	}
}
