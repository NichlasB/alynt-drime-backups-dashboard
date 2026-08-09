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
	 * Constructor.
	 */
	public function __construct() {
		$this->admin_page = new Alynt_Drime_Backups_Dashboard_Admin_Page();
		$this->poller     = new Alynt_Drime_Backups_Dashboard_Poller();

		$this->hooks();
	}

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'admin_menu', array( $this->admin_page, 'register_menu' ) );
		add_action( 'alynt_drime_backups_dashboard_poll_sites', array( $this->poller, 'poll_sites' ) );
	}
}
