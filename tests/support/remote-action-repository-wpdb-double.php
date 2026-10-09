<?php
/**
 * Remote action repository wpdb test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-repository-wpdb-write-methods.php';

/**
 * Fake wpdb for remote action repository tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB {
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB_Query_Methods;
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB_Write_Methods;

	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * Insert ID.
	 *
	 * @var int
	 */
	public $insert_id = 321;
}
