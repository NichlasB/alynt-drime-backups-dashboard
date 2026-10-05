<?php
/**
 * Test support for diagnostics tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared fixture builders for diagnostics tests.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures {
	/**
	 * Creates a site row.
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function site( $site_id, array $overrides = array() ) {
		return array_merge(
			array(
				'id'                         => $site_id,
				'public_id'                  => '00000000-0000-4000-8000-00000000000' . $site_id,
				'site_label'                 => 'Client ' . $site_id,
				'expected_origin'            => 'https://client' . $site_id . '.example.com',
				'enrollment_status'          => 'active',
				'overall_status'             => 'working',
				'polling_key_id'             => 'pk_example_0000000000000000',
				'polling_secret_ciphertext'  => 'adbv1.ciphertext',
				'next_poll_at'               => '2099-01-01 00:00:00',
				'last_poll_attempt_at'       => '',
				'last_seen_at'               => '',
				'consecutive_failures'       => 0,
				'last_error_code'            => '',
				'last_error_summary'         => '',
				'paused_at'                  => '',
			),
			$overrides
		);
	}

	/**
	 * Creates a healthy snapshot.
	 *
	 * @param array<string,mixed> $overrides Payload overrides.
	 * @return array<string,mixed>
	 */
	private function snapshot( array $overrides = array() ) {
		return array(
			'schema_version'   => 1,
			'observed_at'      => gmdate( 'Y-m-d H:i:s' ),
			'decoded_payload'  => array_merge(
				array(
					'schema_version'           => 1,
					'server_outbox_configured' => true,
					'failed_count'             => 0,
					'warning_count'            => 0,
					'warnings'                 => array(),
					'cron_status'              => 'ok',
				),
				$overrides
			),
		);
	}

	/**
	 * Creates a retained snapshot summary row.
	 *
	 * @param string $status Snapshot status.
	 * @param string $observed_at Observed time.
	 * @return array<string,mixed>
	 */
	private function snapshot_row( $status, $observed_at ) {
		return array(
			'overall_status' => $status,
			'observed_at'    => $observed_at,
		);
	}
}

/**
 * Fake site repository for diagnostics tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Sites.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $sites;

	/**
	 * Constructor.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 */
	public function __construct( array $sites ) {
		$this->sites = $sites;
	}

	/**
	 * Lists sites.
	 *
	 * @param array $args Query args.
	 * @return array<int,array<string,mixed>>
	 */
	public function all( $args = array() ) {
		unset( $args );

		return $this->sites;
	}
}

/**
 * Fake snapshot repository for diagnostics tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository extends Alynt_Drime_Backups_Dashboard_Snapshot_Repository {
	/**
	 * Snapshots keyed by site ID.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $snapshots;

	/**
	 * Recent snapshot histories keyed by site ID.
	 *
	 * @var array<int,array<int,array<string,mixed>>>
	 */
	private $histories;

	/**
	 * Constructor.
	 *
	 * @param array<int,array<string,mixed>>            $snapshots Snapshots.
	 * @param array<int,array<int,array<string,mixed>>> $histories Recent snapshot histories.
	 */
	public function __construct( array $snapshots, array $histories = array() ) {
		$this->snapshots = $snapshots;
		$this->histories = $histories;
	}

	/**
	 * Gets latest snapshots keyed by site ID.
	 *
	 * @param array<int> $site_ids Site IDs.
	 * @return array<int,array<string,mixed>>
	 */
	public function latest_by_site_ids( array $site_ids ) {
		$matched = array();

		foreach ( $site_ids as $site_id ) {
			if ( isset( $this->snapshots[ (int) $site_id ] ) ) {
				$matched[ (int) $site_id ] = $this->snapshots[ (int) $site_id ];
			}
		}

		return $matched;
	}

	/**
	 * Gets recent snapshots for one site.
	 *
	 * @param int $site_id Site ID.
	 * @param int $limit Maximum snapshots.
	 * @return array<int,array<string,mixed>>
	 */
	public function recent_for_site( $site_id, $limit = 10 ) {
		$site_id = (int) $site_id;

		if ( empty( $this->histories[ $site_id ] ) ) {
			return array();
		}

		return array_slice( $this->histories[ $site_id ], 0, max( 1, min( 50, (int) $limit ) ) );
	}
}

/**
 * Harness exposing support-summary action aggregates.
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Support;

	/**
	 * Gets support-safe action aggregate output.
	 *
	 * @param array<string,mixed> $remote_actions Remote-action aggregate.
	 * @return array<string,mixed>
	 */
	public function support_action_summary( array $remote_actions ) {
		$support = $this->support_summary_from_diagnostics(
			array(
				'poll_schedule_state'   => 'scheduled',
				'cleanup_state'         => 'scheduled',
				'poll_interval_seconds' => 900,
				'poll_batch_size'       => 20,
				'retention_days'        => 30,
				'global_lock_active'    => false,
			),
			array(),
			array(),
			array(
				'settings' => array(),
				'summary'  => array(),
				'audit'    => array(
					'summary' => array(),
				),
			),
			1789843200,
			$remote_actions
		);

		return $support['actions'];
	}
}
