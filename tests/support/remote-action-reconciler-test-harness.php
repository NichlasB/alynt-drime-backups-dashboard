<?php
/**
 * Remote action reconciler test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-repository.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-reconciler.php';

/**
 * Fake action repository for reconciliation tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	/**
	 * Stored row.
	 *
	 * @var array<string,mixed>|null
	 */
	public $stored = null;

	/**
	 * Last lookup.
	 *
	 * @var array<string,mixed>
	 */
	public $lookup = array();

	/**
	 * Last client report.
	 *
	 * @var array<string,mixed>
	 */
	public $client_report = array();

	/**
	 * Stale update count.
	 *
	 * @var int
	 */
	public $stale = 0;

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * Finds by public ID and site.
	 *
	 * @param string $public_id Public ID.
	 * @param int    $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function find_by_public_id_for_site( $public_id, $site_id ) {
		$this->lookup = array(
			'public_id' => $public_id,
			'site_id'   => $site_id,
		);

		return $this->stored;
	}

	/**
	 * Stores client report.
	 *
	 * @param int                 $action_id Action ID.
	 * @param array<string,mixed> $client_action Client action.
	 * @param string|null         $now Now.
	 * @return bool
	 */
	public function mark_client_report( $action_id, array $client_action, $now = null ) {
		$this->client_report = array(
			'action_id'     => $action_id,
			'client_action' => $client_action,
			'now'           => $now,
		);

		return true;
	}

	/**
	 * Marks stale actions.
	 *
	 * @param int         $site_id Site ID.
	 * @param string|null $now Now.
	 * @return int
	 */
	public function mark_unconfirmed_actions_stale_for_site( $site_id, $now = null ) {
		unset( $site_id, $now );

		return $this->stale;
	}
}

/**
 * Shared remote action reconciler fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler_Test_Fixtures {
	/**
	 * Payload fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function payload() {
		return array(
			'remote_actions' => array(
				'protocol_version' => 2,
				'enabled'          => true,
				'last_action'      => array(
					'action_id'      => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
					'action_type'    => 'scan_upload_now',
					'state'          => 'succeeded',
					'result_code'    => 'action_succeeded',
					'result_summary' => 'Scan completed safely.',
					'counts'         => array(
						'found'            => 2,
						'queued'           => 0,
						'already_known'    => 1,
						'upload_attempted' => 1,
						'failed'           => 0,
					),
				),
			),
		);
	}
}
