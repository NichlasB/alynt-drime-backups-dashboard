<?php
/**
 * Shared fixtures for admin remote-action rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-cleanup-preview-rendering-fixtures.php';
require_once __DIR__ . '/admin-page-remote-action-context-fixtures.php';
require_once __DIR__ . '/admin-page-remote-action-history-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-management-history-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-management-rendering-fixtures.php';

/**
 * Provides reusable V2 remote-action rows and snapshots.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Rendering_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Rendering_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Context_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_History_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_History_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Schedule_Management_Rendering_Fixtures;

}
