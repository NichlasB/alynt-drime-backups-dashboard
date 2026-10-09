<?php
/**
 * Poller test doubles.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/poller-test-reconciler-double.php';

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
