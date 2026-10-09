<?php
/**
 * Fake remote-action and poller collaborators for admin action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-actions-poller-double.php';

/**
 * Fake remote action dispatcher.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Dispatcher {
	/**
	 * Calls.
	 *
	 * @var array<int,array<string,int>>
	 */
	public $calls = array();

	/**
	 * Result.
	 *
	 * @var array<string,mixed>|WP_Error
	 */
	public $result = array(
		'action'       => 'request_backup_now',
		'remote_state' => 'accepted',
	);

	/**
	 * Records request.
	 *
	 * @param int $site_id Site ID.
	 * @param int $requested_by User ID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function request_scan_upload_now( $site_id, $requested_by = 0 ) {
		$this->calls[] = array(
			'site_id'      => (int) $site_id,
			'requested_by' => (int) $requested_by,
		);

		return $this->result;
	}

	/**
	 * Records cleanup preview request.
	 *
	 * @param int $site_id Site ID.
	 * @param int $requested_by User ID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function request_cleanup_preview( $site_id, $requested_by = 0 ) {
		$this->calls[] = array(
			'site_id'      => (int) $site_id,
			'requested_by' => (int) $requested_by,
		);

		return array(
			'action'       => 'cleanup_preview',
			'remote_state' => 'accepted',
		);
	}
}
