<?php
/**
 * Dashboard polling coordinator.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates read-only client status polling.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Poller {
	use Alynt_Drime_Backups_Dashboard_Poller_Scheduling;
	use Alynt_Drime_Backups_Dashboard_Poller_Locks;
	use Alynt_Drime_Backups_Dashboard_Poller_Status_Check;

	const CRON_HOOK               = 'alynt_drime_backups_dashboard_poll_sites';
	const CLEANUP_HOOK            = 'alynt_drime_backups_dashboard_cleanup_snapshots';
	const CRON_RECURRENCE         = 'alynt_drime_backups_dashboard_15_minutes';
	const POLL_INTERVAL_SECONDS   = 900;
	const POLL_JITTER_SECONDS     = 300;
	const DEFAULT_BATCH_SIZE      = 5;
	const GLOBAL_LOCK_KEY         = 'alynt_drime_backups_dashboard_poll_sites_lock';
	const SITE_LOCK_KEY_PREFIX    = 'alynt_drime_backups_dashboard_poll_site_lock_';
	const LOCK_TTL_SECONDS        = 600;
	const FAILURE_BACKOFF_MAX     = 21600;
	const SNAPSHOT_RETENTION_DAYS = 30;
	const SNAPSHOT_CLEANUP_BATCH  = 500;

	/**
	 * Site repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Site_Repository
	 */
	private $sites;

	/**
	 * Snapshot repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Snapshot_Repository
	 */
	private $snapshots;

	/**
	 * Classifier.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Status_Classifier
	 */
	private $classifier;

	/**
	 * Credential vault.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Credential_Vault
	 */
	private $vault;

	/**
	 * Safe transport.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Safe_Transport
	 */
	private $transport;

	/**
	 * Payload validator.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Status_Payload_Validator
	 */
	private $validator;

	/**
	 * Optional HTTP client for tests.
	 *
	 * @var callable|null
	 */
	private $http_client;

	/**
	 * Structured event log.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Event_Log
	 */
	private $event_log;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null          $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Snapshot_Repository|null      $snapshots Snapshot repository.
	 * @param Alynt_Drime_Backups_Dashboard_Status_Classifier|null        $classifier Classifier.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault|null         $vault Credential vault.
	 * @param Alynt_Drime_Backups_Dashboard_Safe_Transport|null           $transport Transport.
	 * @param Alynt_Drime_Backups_Dashboard_Status_Payload_Validator|null $validator Payload validator.
	 * @param callable|null                                               $http_client HTTP client.
	 * @param Alynt_Drime_Backups_Dashboard_Event_Log|null                $event_log Event log.
	 */
	public function __construct( $sites = null, $snapshots = null, $classifier = null, $vault = null, $transport = null, $validator = null, $http_client = null, $event_log = null ) {
		$this->sites       = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->snapshots   = $snapshots instanceof Alynt_Drime_Backups_Dashboard_Snapshot_Repository ? $snapshots : new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$this->classifier  = $classifier instanceof Alynt_Drime_Backups_Dashboard_Status_Classifier ? $classifier : new Alynt_Drime_Backups_Dashboard_Status_Classifier();
		$this->vault       = $vault instanceof Alynt_Drime_Backups_Dashboard_Credential_Vault ? $vault : new Alynt_Drime_Backups_Dashboard_Credential_Vault();
		$this->transport   = $transport instanceof Alynt_Drime_Backups_Dashboard_Safe_Transport ? $transport : new Alynt_Drime_Backups_Dashboard_Safe_Transport();
		$this->validator   = $validator instanceof Alynt_Drime_Backups_Dashboard_Status_Payload_Validator ? $validator : new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
		$this->http_client = is_callable( $http_client ) ? $http_client : null;
		$this->event_log   = $event_log instanceof Alynt_Drime_Backups_Dashboard_Event_Log ? $event_log : new Alynt_Drime_Backups_Dashboard_Event_Log();
	}

	/**
	 * Runs the scheduled read-only poll batch.
	 *
	 * @param int $limit Maximum sites to poll.
	 * @return array<string,int|string>
	 */
	public function poll_sites( $limit = self::DEFAULT_BATCH_SIZE ) {
		$result = array(
			'processed' => 0,
			'success'   => 0,
			'failure'   => 0,
			'skipped'   => 0,
		);

		if ( ! $this->acquire_global_lock() ) {
			$result['skipped'] = 1;
			$result['reason']  = 'locked';
			$this->event_log->log( 'warning', 'cron', 'poll_batch_locked', __( 'The scheduled poll batch was skipped because another batch is running.', 'alynt-drime-backups-dashboard' ) );

			return $result;
		}

		try {
			$due_sites = $this->sites->due_for_poll( $limit, gmdate( 'Y-m-d H:i:s' ) );

			foreach ( $due_sites as $site ) {
				if ( empty( $site['id'] ) ) {
					++$result['skipped'];
					continue;
				}

				$poll_result = $this->check_status_now( (int) $site['id'] );

				++$result['processed'];

				if ( is_wp_error( $poll_result ) ) {
					if ( 'poll_locked' === $poll_result->get_error_code() ) {
						++$result['skipped'];
					} else {
						++$result['failure'];
					}
				} else {
					++$result['success'];
				}
			}
		} finally {
			$this->release_global_lock();
		}

		return $result;
	}

	/**
	 * Performs one manual read-only status check.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function check_status_now( $site_id ) {
		$site = $this->sites->get( $site_id );

		if ( ! $site ) {
			return new WP_Error( 'site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( ! $this->acquire_site_lock( (int) $site_id ) ) {
			return new WP_Error( 'poll_locked', __( 'A status check is already running for this site.', 'alynt-drime-backups-dashboard' ) );
		}

		try {
			return $this->check_site_status( $site );
		} finally {
			$this->release_site_lock( (int) $site_id );
		}
	}
}
