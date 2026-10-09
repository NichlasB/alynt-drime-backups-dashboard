<?php
/**
 * Enrollment manager site repository test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake repository for enrollment manager tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Last inserted data.
	 *
	 * @var array<string,mixed>
	 */
	public $last_insert = array();

	/**
	 * Create result override.
	 *
	 * @var int|WP_Error
	 */
	public $create_result = 123;

	/**
	 * Existing active pending site.
	 *
	 * @var array<string,mixed>|null
	 */
	public $active_pending = null;

	/**
	 * Creates a pending fake row.
	 *
	 * @param array $data Site data.
	 * @return int|WP_Error
	 */
	public function create_pending( array $data ) {
		$this->last_insert = $data;

		return $this->create_result;
	}

	/**
	 * Gets an active pending fake row.
	 *
	 * @param string $expected_origin Expected origin.
	 * @param string $now Current UTC datetime.
	 * @return array<string,mixed>|null
	 */
	public function get_active_pending_by_expected_origin( $expected_origin, $now = '' ) {
		return $this->active_pending;
	}
}
