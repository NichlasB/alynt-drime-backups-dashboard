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
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null          $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Snapshot_Repository|null      $snapshots Snapshot repository.
	 * @param Alynt_Drime_Backups_Dashboard_Status_Classifier|null        $classifier Classifier.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault|null         $vault Credential vault.
	 * @param Alynt_Drime_Backups_Dashboard_Safe_Transport|null           $transport Transport.
	 * @param Alynt_Drime_Backups_Dashboard_Status_Payload_Validator|null $validator Payload validator.
	 * @param callable|null                                               $http_client HTTP client.
	 */
	public function __construct( $sites = null, $snapshots = null, $classifier = null, $vault = null, $transport = null, $validator = null, $http_client = null ) {
		$this->sites       = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->snapshots   = $snapshots instanceof Alynt_Drime_Backups_Dashboard_Snapshot_Repository ? $snapshots : new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$this->classifier  = $classifier instanceof Alynt_Drime_Backups_Dashboard_Status_Classifier ? $classifier : new Alynt_Drime_Backups_Dashboard_Status_Classifier();
		$this->vault       = $vault instanceof Alynt_Drime_Backups_Dashboard_Credential_Vault ? $vault : new Alynt_Drime_Backups_Dashboard_Credential_Vault();
		$this->transport   = $transport instanceof Alynt_Drime_Backups_Dashboard_Safe_Transport ? $transport : new Alynt_Drime_Backups_Dashboard_Safe_Transport();
		$this->validator   = $validator instanceof Alynt_Drime_Backups_Dashboard_Status_Payload_Validator ? $validator : new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
		$this->http_client = is_callable( $http_client ) ? $http_client : null;
	}

	/**
	 * Registers the custom polling recurrence.
	 *
	 * @param array<string,array<string,mixed>> $schedules Cron schedules.
	 * @return array<string,array<string,mixed>>
	 */
	public static function cron_schedules( $schedules ) {
		if ( ! isset( $schedules[ self::CRON_RECURRENCE ] ) ) {
			$schedules[ self::CRON_RECURRENCE ] = array(
				'interval' => self::POLL_INTERVAL_SECONDS,
				'display'  => __( 'Every 15 minutes', 'alynt-drime-backups-dashboard' ),
			);
		}

		return $schedules;
	}

	/**
	 * Schedules dashboard maintenance events.
	 *
	 * @return void
	 */
	public static function schedule_events() {
		if ( function_exists( 'add_filter' ) ) {
			// phpcs:ignore WordPress.WP.CronInterval.ChangeDetected -- The interval value is registered in cron_schedules().
			add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
		}

		if ( function_exists( 'wp_next_scheduled' ) && function_exists( 'wp_schedule_event' ) && ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 60, self::CRON_RECURRENCE, self::CRON_HOOK );
		}

		if ( function_exists( 'wp_next_scheduled' ) && function_exists( 'wp_schedule_event' ) && ! wp_next_scheduled( self::CLEANUP_HOOK ) ) {
			wp_schedule_event( time() + 300, 'daily', self::CLEANUP_HOOK );
		}
	}

	/**
	 * Unschedules dashboard maintenance events.
	 *
	 * @return void
	 */
	public static function unschedule_events() {
		if ( function_exists( 'wp_clear_scheduled_hook' ) ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
			wp_clear_scheduled_hook( self::CLEANUP_HOOK );
		}

		if ( function_exists( 'delete_transient' ) ) {
			delete_transient( self::GLOBAL_LOCK_KEY );
		}
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

	/**
	 * Performs the status check for a loaded site row.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return array<string,mixed>|WP_Error
	 */
	private function check_site_status( array $site ) {
		$site_id = isset( $site['id'] ) ? (int) $site['id'] : 0;
		$auth    = $this->polling_auth_scheme( $site );

		if ( is_wp_error( $auth ) ) {
			$this->mark_poll_failure( $site, $auth->get_error_code(), $auth->get_error_message() );
			return $auth;
		}

		$raw_payload = $this->transport->fetch_status_payload( $site, $auth, $this->http_client );

		if ( is_wp_error( $raw_payload ) ) {
			$this->mark_poll_failure( $site, $raw_payload->get_error_code(), $raw_payload->get_error_message() );
			return $raw_payload;
		}

		$payload = $this->validator->validate( $raw_payload, isset( $site['site_uuid'] ) ? (string) $site['site_uuid'] : '' );

		if ( is_wp_error( $payload ) ) {
			$this->mark_poll_failure( $site, $payload->get_error_code(), $payload->get_error_message() );
			return $payload;
		}

		$status   = $this->classifier->classify(
			array_merge(
				$site,
				array(
					'overall_status' => 'working',
					'last_seen_at'   => gmdate( 'Y-m-d H:i:s' ),
				)
			),
			array(
				'decoded_payload' => $payload,
				'observed_at'     => gmdate( 'Y-m-d H:i:s' ),
				'schema_version'  => 1,
			)
		);
		$snapshot = $this->snapshots->record( $site_id, $payload, $status['category'] );

		$this->sites->mark_poll_success(
			$site_id,
			$status['category'],
			isset( $payload['plugin_version'] ) ? (string) $payload['plugin_version'] : '',
			$this->next_poll_after_success( $site )
		);

		return array(
			'category'    => $status['category'],
			'label'       => $status['label'],
			'message'     => $status['message'],
			'snapshot_id' => $snapshot,
		);
	}

	/**
	 * Builds the polling authorization header.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string|WP_Error
	 */
	private function polling_auth_scheme( array $site ) {
		if ( empty( $site['public_id'] ) || empty( $site['polling_key_id'] ) || empty( $site['polling_secret_ciphertext'] ) ) {
			return new WP_Error( 'auth_missing', __( 'The dashboard site does not have a polling credential yet.', 'alynt-drime-backups-dashboard' ) );
		}

		$secret = $this->vault->decrypt( (string) $site['polling_secret_ciphertext'], 'site:' . (string) $site['public_id'] );

		if ( is_wp_error( $secret ) ) {
			return $secret;
		}

		return 'Bearer adb-poll-v1.' . (string) $site['polling_key_id'] . '.' . $secret;
	}

	/**
	 * Marks a poll failure with retry metadata.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param string              $error_code Error code.
	 * @param string              $summary Safe summary.
	 * @return void
	 */
	private function mark_poll_failure( array $site, $error_code, $summary ) {
		$failures = isset( $site['consecutive_failures'] ) ? max( 0, (int) $site['consecutive_failures'] ) + 1 : 1;

		$this->sites->mark_poll_failure(
			isset( $site['id'] ) ? (int) $site['id'] : 0,
			$error_code,
			$summary,
			$this->next_poll_after_failure( $site, $failures ),
			$failures
		);
	}

	/**
	 * Calculates the next success poll time.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function next_poll_after_success( array $site ) {
		return gmdate( 'Y-m-d H:i:s', time() + self::POLL_INTERVAL_SECONDS + $this->poll_jitter( $site, self::POLL_JITTER_SECONDS ) );
	}

	/**
	 * Calculates the next retry time after a failure.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $failures Consecutive failures.
	 * @return string
	 */
	private function next_poll_after_failure( array $site, $failures ) {
		$exponent = max( 0, min( 10, (int) $failures - 1 ) );
		$delay    = min( self::FAILURE_BACKOFF_MAX, self::POLL_INTERVAL_SECONDS * ( 2 ** $exponent ) );

		return gmdate( 'Y-m-d H:i:s', time() + $delay + $this->poll_jitter( $site, self::POLL_JITTER_SECONDS ) );
	}

	/**
	 * Returns deterministic per-site jitter.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $spread Maximum spread in seconds.
	 * @return int
	 */
	private function poll_jitter( array $site, $spread ) {
		$key  = ! empty( $site['public_id'] ) ? (string) $site['public_id'] : (string) ( isset( $site['id'] ) ? $site['id'] : 'site' );
		$hash = (int) sprintf( '%u', crc32( $key ) );

		return $spread > 0 ? $hash % ( $spread + 1 ) : 0;
	}

	/**
	 * Acquires the global scheduler lock.
	 *
	 * @return bool
	 */
	private function acquire_global_lock() {
		if ( ! function_exists( 'get_transient' ) || ! function_exists( 'set_transient' ) ) {
			return true;
		}

		if ( false !== get_transient( self::GLOBAL_LOCK_KEY ) ) {
			return false;
		}

		return (bool) set_transient( self::GLOBAL_LOCK_KEY, time(), self::LOCK_TTL_SECONDS );
	}

	/**
	 * Releases the global scheduler lock.
	 *
	 * @return void
	 */
	private function release_global_lock() {
		if ( function_exists( 'delete_transient' ) ) {
			delete_transient( self::GLOBAL_LOCK_KEY );
		}
	}

	/**
	 * Acquires a per-site poll lock.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	private function acquire_site_lock( $site_id ) {
		if ( ! function_exists( 'get_transient' ) || ! function_exists( 'set_transient' ) ) {
			return true;
		}

		$key = self::SITE_LOCK_KEY_PREFIX . absint( $site_id );

		if ( false !== get_transient( $key ) ) {
			return false;
		}

		return (bool) set_transient( $key, time(), self::LOCK_TTL_SECONDS );
	}

	/**
	 * Releases a per-site poll lock.
	 *
	 * @param int $site_id Site ID.
	 * @return void
	 */
	private function release_site_lock( $site_id ) {
		if ( function_exists( 'delete_transient' ) ) {
			delete_transient( self::SITE_LOCK_KEY_PREFIX . absint( $site_id ) );
		}
	}
}
