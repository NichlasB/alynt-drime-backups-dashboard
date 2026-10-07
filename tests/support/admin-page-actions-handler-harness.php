<?php
/**
 * Admin page action handler test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Minimal harness exposing the private trait action handler for tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Actions;

	/**
	 * Enrollment manager.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager
	 */
	public $enrollment_manager;

	/**
	 * Remote action dispatcher.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Dispatcher
	 */
	public $remote_action_dispatcher;

	/**
	 * Poller.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Poller
	 */
	public $poller;

	/**
	 * Event log.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Event_Log
	 */
	public $event_log;

	/**
	 * Site repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites
	 */
	public $sites;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager $enrollment_manager Enrollment manager.
	 */
	public function __construct( $enrollment_manager ) {
		$this->enrollment_manager       = $enrollment_manager;
		$this->remote_action_dispatcher = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Dispatcher();
		$this->poller                   = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Poller();
		$this->event_log                = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Event_Log();
		$this->sites                    = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites();
	}

	/**
	 * Exposes the private action handler.
	 *
	 * @return array<string,mixed>|WP_Error|null
	 */
	public function handle_for_test() {
		return $this->handle_post_action();
	}
}
