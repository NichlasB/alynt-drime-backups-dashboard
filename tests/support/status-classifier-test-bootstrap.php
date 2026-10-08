<?php
/**
 * Shared bootstrap for status classifier tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/class-source-policy.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-status-classifier-backup-sources.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-status-classifier-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-status-classifier.php';
require_once __DIR__ . '/status-classifier-backup-source-fixtures.php';
require_once __DIR__ . '/status-classifier-test-fixtures.php';
