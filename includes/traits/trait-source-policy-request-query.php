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

/** The query class alone may initialize its private receiver/source fields. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Query {
	use Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Link;

	/** Construct only the root-owned query binding.
	 *
	 * @param object $root Private root.
	 * @param object $receiver Exact owned peer.
	 * @param string $file Fixed core declaration.
	 * @return object|null
	 */
	public static function from_request( $root, $receiver, $file ) {
		if ( ! $root instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry || ! $root->request_claim( 'query', $receiver, $file ) ) {
			return null;
		}
		$peer               = new self();
		$peer->request_root = $root;
		$peer->owned        = $receiver;
		$peer->ambient      = $GLOBALS['wpdb'] ?? null;
		$peer->core_file    = $file;
		return $root->request_retain( $peer ) ? $peer : null;
	}
}
