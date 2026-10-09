<?php
/**
 * Admin Sites-list test exposure methods.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Harness methods exposing private Sites-list helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Exposure_Methods {
	/**
	 * Exposes visible Sites-list rows.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @return array<int,array<string,mixed>>
	 */
	public function visible_sites( array $sites ) {
		return $this->without_superseded_revoked_sites( $sites );
	}

	/**
	 * Exposes request-local site context.
	 *
	 * @param string $visibility Visibility.
	 * @return array<string,mixed>
	 */
	public function context_for( $visibility ) {
		return $this->site_status_context( $visibility );
	}

	/**
	 * Exposes default Attention count.
	 *
	 * @return int
	 */
	public function attention_count_for_test() {
		return $this->attention_count();
	}
}
