<?php
/**
 * Admin schedule-management history fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-pending-schedule-history-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-apply-history-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-rollback-history-fixtures.php';

/**
 * Shared schedule-management history fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Management_History_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Pending_Schedule_History_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Schedule_Apply_History_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Schedule_Rollback_History_Fixtures;

}
