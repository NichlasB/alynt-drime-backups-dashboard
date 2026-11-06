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

/** Exact owned connection, never an ambient/caller handle. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Adapter {
	use Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Link;

	/** Construct only the root-owned adapter binding.
	 *
	 * @param object $root Private root.
	 * @param object $connection Exact peer.
	 * @return object|null
	 */
	public static function from_request( $root, $connection ) {
		if ( ! $root instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry || ! $root->request_claim( 'adapter', $connection ) ) {
			return null;
		}
		$peer               = new self();
		$peer->request_root = $root;
		$peer->connection   = $connection;
		return $root->request_retain( $peer ) ? $peer : null;
	}
}
