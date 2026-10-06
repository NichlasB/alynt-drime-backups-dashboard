<?php
/**
 * Status payload validator test bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-status-payload-validator-backup-sources.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-status-payload-validator-restore-readiness.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-status-payload-validator-sanitizers.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-status-payload-validator.php';
require_once __DIR__ . '/status-payload-validator-test-fixtures.php';
