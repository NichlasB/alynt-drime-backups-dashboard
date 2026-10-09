<?php
/**
 * Fake site repository for admin action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-actions-sites-local-state-double.php';

/**
 * Fake site repository.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites {
	use Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites_Local_State;

	/**
	 * Current site row.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site = array(
		'id'                => 42,
		'enrollment_status' => 'active',
	);

	/**
	 * Gets a site.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function get( $site_id ) {
		if ( ! is_array( $this->site ) || (int) $this->site['id'] !== (int) $site_id ) {
			return null;
		}

		return $this->site;
	}
}
