<?php
/**
 * Shared bootstrap for admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-polling-state-rendering-wordpress-shims.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-local-actions.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-archive-actions.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-capabilities.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-basic-detail-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-attention-recovery-history-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-status-history-detail-helpers.php';
require_once __DIR__ . '/admin-page-polling-state-rendering-counting-doubles.php';
require_once __DIR__ . '/admin-page-polling-state-rendering-local-record-methods.php';
require_once __DIR__ . '/admin-page-polling-state-rendering-remote-action-methods.php';
require_once __DIR__ . '/admin-page-polling-state-rendering-test-harness.php';
require_once __DIR__ . '/admin-page-rendering-remote-action-double.php';
require_once __DIR__ . '/admin-page-remote-action-rendering-fixtures.php';
