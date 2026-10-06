<?php
/**
 * Enrollment REST controller repository test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake repository for enrollment REST tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Pending site.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site;

	/**
	 * Stored enrollment data.
	 *
	 * @var array<string,mixed>
	 */
	public $stored = array();

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed>|null $site Site.
	 */
	public function __construct( $site = null ) {
		$this->site = $site;
	}

	/**
	 * Gets a pending site by public ID.
	 *
	 * @param string $public_id Public ID.
	 * @return array<string,mixed>|null
	 */
	public function get_pending_by_public_id( $public_id ) {
		if ( ! $this->site || $public_id !== $this->site['public_id'] || 'pending' !== $this->site['enrollment_status'] ) {
			return null;
		}

		return $this->site;
	}

	/**
	 * Stores enrollment state.
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $data Data.
	 * @return bool
	 */
	public function complete_enrollment_pending_first_poll( $site_id, array $data ) {
		$this->stored = array_merge(
			array(
				'site_id' => $site_id,
			),
			$data
		);

		return true;
	}
}
