<?php
/**
 * Poller test harness.
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
require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-capabilities.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-repository.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-reconciler.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-status-payload-validator-backup-sources.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-status-payload-validator-sanitizers.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-status-payload-validator.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-safe-transport.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-poller-scheduling.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-poller-locks.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-poller-status-check.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-poller.php';

/**
 * Fake site repository for poller tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Site.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site;

	/**
	 * Sites keyed by ID.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $sites = array();

	/**
	 * Due sites.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $due_sites = array();

	/**
	 * Success data.
	 *
	 * @var array<string,mixed>
	 */
	public $success = array();

	/**
	 * Success rows.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $successes = array();

	/**
	 * Failure data.
	 *
	 * @var array<string,mixed>
	 */
	public $failure = array();

	/**
	 * Failure rows.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $failures = array();

	/**
	 * Mark success result.
	 *
	 * @var bool
	 */
	public $mark_success_result = true;

	/**
	 * Mark failure result.
	 *
	 * @var bool
	 */
	public $mark_failure_result = true;

	/**
	 * Last due-for-poll query.
	 *
	 * @var array<string,mixed>
	 */
	public $due_query = array();

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed>|array<int,array<string,mixed>>|null $site Site.
	 */
	public function __construct( $site ) {
		if ( is_array( $site ) && isset( $site[0] ) && is_array( $site[0] ) ) {
			foreach ( $site as $row ) {
				$this->sites[ (int) $row['id'] ] = $row;
			}

			$this->due_sites = array_values( $this->sites );
			$this->site      = reset( $this->sites );
		} else {
			$this->site = $site;

			if ( is_array( $site ) && isset( $site['id'] ) ) {
				$this->sites[ (int) $site['id'] ] = $site;
			}
		}
	}

	/**
	 * Gets the fake site.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function get( $site_id ) {
		return isset( $this->sites[ (int) $site_id ] ) ? $this->sites[ (int) $site_id ] : null;
	}

	/**
	 * Gets due sites.
	 *
	 * @param int    $limit Limit.
	 * @param string $now Now.
	 * @return array<int,array<string,mixed>>
	 */
	public function due_for_poll( $limit = 5, $now = '' ) {
		$this->due_query = array(
			'limit' => $limit,
			'now'   => $now,
		);

		return array_slice( $this->due_sites, 0, (int) $limit );
	}

	/**
	 * Marks success.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $status Status.
	 * @param string $plugin_version Plugin version.
	 * @param string $next_poll_at Next poll.
	 * @return bool
	 */
	public function mark_poll_success( $site_id, $status, $plugin_version = '', $next_poll_at = '' ) {
		$this->success = array(
			'site_id'        => $site_id,
			'status'         => $status,
			'plugin_version' => $plugin_version,
			'next_poll_at'   => $next_poll_at,
		);
		$this->successes[] = $this->success;

		return $this->mark_success_result;
	}

	/**
	 * Marks failure.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $error_code Error code.
	 * @param string $summary Summary.
	 * @param string $next_poll_at Next poll.
	 * @param int    $consecutive_failures Consecutive failures.
	 * @return bool
	 */
	public function mark_poll_failure( $site_id, $error_code, $summary = '', $next_poll_at = '', $consecutive_failures = 1 ) {
		$this->failure = array(
			'site_id'              => $site_id,
			'error_code'           => $error_code,
			'summary'              => $summary,
			'next_poll_at'         => $next_poll_at,
			'consecutive_failures' => $consecutive_failures,
		);
		$this->failures[] = $this->failure;

		return $this->mark_failure_result;
	}
}

/**
 * Fake snapshot repository for poller tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository extends Alynt_Drime_Backups_Dashboard_Snapshot_Repository {
	/**
	 * Recorded snapshot.
	 *
	 * @var array<string,mixed>
	 */
	public $recorded = array();

	/**
	 * Record result.
	 *
	 * @var int|WP_Error
	 */
	public $record_result = 555;

	/**
	 * Records a fake snapshot.
	 *
	 * @param int    $site_id Site ID.
	 * @param array  $payload Payload.
	 * @param string $status_category Status.
	 * @return int
	 */
	public function record( $site_id, array $payload, $status_category ) {
		$this->recorded = array(
			'site_id' => $site_id,
			'payload' => $payload,
			'status'  => $status_category,
		);

		return $this->record_result;
	}
}

/**
 * Fake remote action reconciler for poller tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Poller_Remote_Action_Reconciler extends Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler {
	/**
	 * Calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $calls = array();

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * Reconciles payload.
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $payload Payload.
	 * @param string|null         $now Now.
	 * @return array<string,int>
	 */
	public function reconcile_site_payload( $site_id, array $payload, $now = null ) {
		unset( $now );

		$this->calls[] = array(
			'site_id' => $site_id,
			'payload' => $payload,
		);

		return array(
			'matched' => 0,
			'stale'   => 0,
		);
	}
}
