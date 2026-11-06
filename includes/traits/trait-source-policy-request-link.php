<?php
/**
 * Class-owned handle-free request peer constructors; never public factories.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Unexported root linkage shared by the five fixed peers. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Request_Link {
	/** Private operation identity, not request DATA.
	 *
	 * @var object|null
	 */
	private $request_root = null;

	/** Release only the root's exact retained peer during terminal cleanup.
	 *
	 * @param object $root Private root.
	 * @return bool
	 */
	final public function release_request( $root ) {
		if ( $this->request_root !== $root || ! $root instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry || ! $root->request_contains( $this, true ) ) {
			return false;
		}
		$this->request_root = null;
		return true;
	}
}
