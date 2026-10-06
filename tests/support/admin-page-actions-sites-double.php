<?php
/**
 * Fake site repository for admin action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake site repository.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites {
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
	 * Archive calls.
	 *
	 * @var array<int,int>
	 */
	public $archive_calls = array();

	/**
	 * Unarchive calls.
	 *
	 * @var array<int,int>
	 */
	public $unarchive_calls = array();

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
	 * Archive result.
	 *
	 * @var bool
	 */
	public $archive_result = true;

	/**
	 * Unarchive result.
	 *
	 * @var bool
	 */
	public $unarchive_result = true;

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

	/**
	 * Records archive.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function archive_local( $site_id ) {
		$this->archive_calls[] = (int) $site_id;

		return $this->archive_result;
	}

	/**
	 * Records unarchive.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function unarchive_local( $site_id ) {
		$this->unarchive_calls[] = (int) $site_id;

		return $this->unarchive_result;
	}
}
