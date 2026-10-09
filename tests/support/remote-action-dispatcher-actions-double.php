<?php
/**
 * Fake action repository for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-dispatcher-action-state-double.php';
require_once __DIR__ . '/remote-action-dispatcher-schedule-lookup-double.php';

/**
 * Test action repository with a fixed fresh preview.
 */
class Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Actions extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	use Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Action_State_Double;
	use Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Schedule_Lookups;

	/**
	 * Inserted requests.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $requests = array();

	/**
	 * Latest state update.
	 *
	 * @var array<string,mixed>
	 */
	public $latest_state = array();

}
