<?php
/**
 * Admin backup source evidence test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-source-policy.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-timestamp-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-evidence-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-compact-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-operator-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-policy-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-evidence.php';
require_once __DIR__ . '/admin-page-backup-source-evidence-fixtures.php';
require_once __DIR__ . '/admin-page-backup-source-evidence-harness-class.php';
