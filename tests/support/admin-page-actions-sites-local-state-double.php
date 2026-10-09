<?php
/**
 * Fake site repository local-state methods for admin action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-actions-sites-archive-state-double.php';

/**
 * Fake site repository local-state behavior.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites_Local_State {
	use Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites_Archive_State;

	/**
	 * Pause calls.
	 *
	 * @var array<int,int>
	 */
	public $pause_calls = array();

	/**
	 * Resume calls.
	 *
	 * @var array<int,int>
	 */
	public $resume_calls = array();

	/**
	 * Pause result.
	 *
	 * @var bool
	 */
	public $pause_result = true;

	/**
	 * Resume result.
	 *
	 * @var bool
	 */
	public $resume_result = true;

	/**
	 * Records pause.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function pause_polling( $site_id ) {
		$this->pause_calls[] = (int) $site_id;

		return $this->pause_result;
	}

	/**
	 * Records resume.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function resume_polling( $site_id ) {
		$this->resume_calls[] = (int) $site_id;

		return $this->resume_result;
	}
}
