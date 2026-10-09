<?php
/**
 * Poller site-repository test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/poller-test-site-repository-due-methods.php';

/**
 * Fake site repository for poller tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	use Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository_Due_Methods;
	use Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository_Write_Methods;

	/**
	 * Site.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site;

	/**
	 * Sites keyed by ID.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $sites = array();

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed>|array<int,array<string,mixed>>|null $site Site.
	 */
	public function __construct( $site ) {
		if ( is_array( $site ) && isset( $site[0] ) && is_array( $site[0] ) ) {
			foreach ( $site as $row ) {
				$this->sites[ (int) $row['id'] ] = $row;
			}

			$this->due_sites = array_values( $this->sites );
			$this->site      = reset( $this->sites );
		} else {
			$this->site = $site;

			if ( is_array( $site ) && isset( $site['id'] ) ) {
				$this->sites[ (int) $site['id'] ] = $site;
			}
		}
	}

	/**
	 * Gets the fake site.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function get( $site_id ) {
		return isset( $this->sites[ (int) $site_id ] ) ? $this->sites[ (int) $site_id ] : null;
	}
}
