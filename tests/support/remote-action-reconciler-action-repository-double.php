<?php
/**
 * Fake action repository for remote action reconciler tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-reconciler-client-report-methods.php';
require_once __DIR__ . '/remote-action-reconciler-stale-methods.php';

/**
 * Fake action repository for reconciliation tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	use Alynt_Drime_Backups_Dashboard_Test_Reconciler_Client_Report_Methods;
	use Alynt_Drime_Backups_Dashboard_Test_Reconciler_Stale_Methods;

	/**
	 * Stored row.
	 *
	 * @var array<string,mixed>|null
	 */
	public $stored = null;

	/**
	 * Last lookup.
	 *
	 * @var array<string,mixed>
	 */
	public $lookup = array();

	/**
	 * Last client report.
	 *
	 * @var array<string,mixed>
	 */
	public $client_report = array();

	/**
	 * Constructor.
	 */
	public function __construct() {}
}
