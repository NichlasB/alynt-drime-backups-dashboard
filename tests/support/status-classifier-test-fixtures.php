<?php
/**
 * Status classifier test fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/status-classifier-classification-fixtures.php';
require_once __DIR__ . '/status-classifier-payload-fixtures.php';
require_once __DIR__ . '/status-classifier-site-fixtures.php';

/**
 * Shared fixture builders for status classifier tests.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Classifier_Test_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Backup_Source_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Classification_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Payload_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Site_Fixtures;

}
