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

/** Construction occurs in the actual connection's scope, without parent IO. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Connection {
	use Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Link;

	/** One frame push attempt, consumed before the source/settings callback boundary.
	 *
	 * @var bool
	 */
	private $request_frame_attempted = false;

	/** Construct only the root-owned handle-free receiver.
	 *
	 * @param object $root Private admitted root.
	 * @return object|null Fixed peer only to that root.
	 */
	final public static function from_request( $root ) {
		if ( ! $root instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry || ! $root->request_claim( 'connection' ) ) {
			return null;
		}
		$peer               = new self();
		$peer->request_root = $root;
		return $root->request_retain( $peer ) ? $peer : null;
	}

	/** Context pushes its frame once; no descriptor or credential getter.
	 *
	 * @param object $root Private root.
	 * @param object $context Exact context.
	 * @param object $query Exact query peer.
	 * @param array  $frame Frozen private frame.
	 * @param array  $binding Source binding.
	 * @return bool
	 */
	final public function bind_request_frame( $root, $context, $query, array $frame, array $binding ) {
		if ( $this->request_root !== $root || ! $root instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry
			|| ! $root->request_bound( $this, $query, $context ) || null !== $this->query_context || null !== $this->bootstrap_frame || null !== $this->bootstrap_binding
			|| $this->request_frame_attempted || $this->bootstrap_attempted || $this->close_attempted || null !== $this->dbh || $this->ready ) {
			return false;
		}
		$this->request_frame_attempted = true;
		if ( ! $context->bootstrap_matches( $this, $query, $frame, $binding ) ) {
			return false;
		}
		$this->query_context     = $query;
		$this->bootstrap_frame   = $frame;
		$this->bootstrap_binding = $binding;
		return true;
	}
}
