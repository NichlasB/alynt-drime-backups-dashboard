<?php
/**
 * Shared bootstrap for diagnostics tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-reads.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-local-state-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-runtime-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-site-repository.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-snapshot-repository-reads.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-snapshot-repository-retention.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-snapshot-repository.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-status-classifier-backup-sources.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-status-classifier-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-status-classifier.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-settings.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-reporting.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-event-log.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-enrollment-rest-responses.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-enrollment-rest-route-args.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-enrollment-rest-controller.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-poller-scheduling.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-poller-locks.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-poller-status-check.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-poller.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-diagnostics-scheduler.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-diagnostics-support-sections.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-diagnostics-support.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-diagnostics-attention-history-metrics.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-diagnostics-site-metrics.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-diagnostics-site-metric-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-diagnostics.php';
require_once __DIR__ . '/diagnostics-test-repositories.php';
require_once __DIR__ . '/diagnostics-test-harness.php';
