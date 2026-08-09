<?php
/**
 * Plugin orchestrator.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Plugin {
	/**
	 * Admin page.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Admin_Page
	 */
	private $admin_page;

	/**
	 * Poller.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Poller
	 */
	private $poller;

	/**
	 * Enrollment REST controller.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller
	 */
	private $enrollment_rest_controller;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$sites        = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$snapshots    = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$classifier   = new Alynt_Drime_Backups_Dashboard_Status_Classifier();
		$this->poller = new Alynt_Drime_Backups_Dashboard_Poller( $sites, $snapshots, $classifier );

		$this->admin_page                 = new Alynt_Drime_Backups_Dashboard_Admin_Page(
			$sites,
			$snapshots,
			$classifier,
			null,
			$this->poller
		);
		$this->enrollment_rest_controller = new Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller();

		$this->hooks();
	}

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'admin_menu', array( $this->admin_page, 'register_menu' ) );
		add_action( 'rest_api_init', array( $this->enrollment_rest_controller, 'register_routes' ) );
		add_action( 'alynt_drime_backups_dashboard_poll_sites', array( $this->poller, 'poll_sites' ) );
	}
}
