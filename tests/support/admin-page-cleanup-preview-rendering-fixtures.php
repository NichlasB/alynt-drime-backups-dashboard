<?php
/**
 * Cleanup-preview fixtures for admin remote-action rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-cleanup-preview-evidence-fixtures.php';
require_once __DIR__ . '/admin-page-cleanup-preview-history-fixtures.php';
require_once __DIR__ . '/admin-page-cleanup-preview-last-action-fixtures.php';
require_once __DIR__ . '/admin-page-cleanup-preview-snapshot-fixtures.php';

/**
 * Provides reusable cleanup-preview snapshots and history rows.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Rendering_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Evidence_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_History_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Last_Action_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Snapshot_Fixtures;

}
