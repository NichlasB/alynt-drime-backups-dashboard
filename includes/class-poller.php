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
	 * Runs the scheduled poll.
	 *
	 * Scheduled batching remains a later slice.
	 *
	 * @return void
	 */
	public function poll_sites() {
		do_action( 'alynt_drime_backups_dashboard_poll_sites_noop' );
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

		$auth = $this->polling_auth_scheme( $site );

		if ( is_wp_error( $auth ) ) {
			$this->sites->mark_poll_failure( $site_id, $auth->get_error_code(), $auth->get_error_message() );
			return $auth;
		}

		$raw_payload = $this->transport->fetch_status_payload( $site, $auth, $this->http_client );

		if ( is_wp_error( $raw_payload ) ) {
			$this->sites->mark_poll_failure( $site_id, $raw_payload->get_error_code(), $raw_payload->get_error_message() );
			return $raw_payload;
		}

		$payload = $this->validator->validate( $raw_payload, isset( $site['site_uuid'] ) ? (string) $site['site_uuid'] : '' );

		if ( is_wp_error( $payload ) ) {
			$this->sites->mark_poll_failure( $site_id, $payload->get_error_code(), $payload->get_error_message() );
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

		$this->sites->mark_poll_success( $site_id, $status['category'], isset( $payload['plugin_version'] ) ? (string) $payload['plugin_version'] : '' );

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
}
