<?php
/**
 * Enrollment REST controller test bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/class-origin-validator.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-pairing-tokens.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-credential-vault.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-reads.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-runtime-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-site-repository.php';
require_once dirname( __DIR__, 2 ) . '/includes/diagnostics/class-event-log-redactor.php';
require_once dirname( __DIR__, 2 ) . '/includes/diagnostics/traits/trait-event-log-storage.php';
require_once dirname( __DIR__, 2 ) . '/includes/diagnostics/traits/trait-event-log-settings.php';
require_once dirname( __DIR__, 2 ) . '/includes/diagnostics/traits/trait-event-log-reporting.php';
require_once dirname( __DIR__, 2 ) . '/includes/diagnostics/class-event-log.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-enrollment-rest-responses.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-enrollment-rest-route-args.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-enrollment-rest-controller.php';
require_once __DIR__ . '/enrollment-rest-controller-test-harness.php';
