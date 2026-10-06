<?php
/**
 * Admin page action test doubles.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake enrollment manager for admin action tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager {
	/**
	 * Recorded calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $calls = array();

	/**
	 * Return value.
	 *
	 * @var array<string,mixed>|WP_Error
	 */
	public $result = array(
		'site_id'       => 123,
		'pairing_token' => 'adb1.test',
	);

	/**
	 * Records pending-site creation.
	 *
	 * @param array  $raw Raw pending site data.
	 * @param string $dashboard_origin Dashboard origin.
	 * @return array<string,mixed>|WP_Error
	 */
	public function create_pending_site( array $raw, $dashboard_origin ) {
		$this->calls[] = array(
			'raw'              => $raw,
			'dashboard_origin' => $dashboard_origin,
		);

		return $this->result;
	}
}

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

/**
 * Fake poller.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Poller {
	/**
	 * Calls.
	 *
	 * @var array<int,int>
	 */
	public $calls = array();

	/**
	 * Checks status.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>
	 */
	public function check_status_now( $site_id ) {
		$this->calls[] = (int) $site_id;

		return array(
			'category' => 'working',
		);
	}
}

/**
 * Fake event log.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Event_Log {
	/**
	 * Audit calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $audit_calls = array();

	/**
	 * Records audit.
	 *
	 * @param string              $action Action.
	 * @param string              $outcome Outcome.
	 * @param array<string,mixed> $context Context.
	 * @return void
	 */
	public function audit_action( $action, $outcome, array $context = array() ) {
		$this->audit_calls[] = array(
			'action'  => $action,
			'outcome' => $outcome,
			'context' => $context,
		);
	}
}

/**
 * Fake site repository.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites {
	/**
	 * Current site row.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site = array(
		'id'                => 42,
		'enrollment_status' => 'active',
	);

	/**
	 * Pause calls.
	 *
	 * @var array<int,int>
	 */
	public $pause_calls = array();

	/**
	 * Resume calls.
	 *
	 * @var array<int,int>
	 */
	public $resume_calls = array();

	/**
	 * Archive calls.
	 *
	 * @var array<int,int>
	 */
	public $archive_calls = array();

	/**
	 * Unarchive calls.
	 *
	 * @var array<int,int>
	 */
	public $unarchive_calls = array();

	/**
	 * Pause result.
	 *
	 * @var bool
	 */
	public $pause_result = true;

	/**
	 * Resume result.
	 *
	 * @var bool
	 */
	public $resume_result = true;

	/**
	 * Archive result.
	 *
	 * @var bool
	 */
	public $archive_result = true;

	/**
	 * Unarchive result.
	 *
	 * @var bool
	 */
	public $unarchive_result = true;

	/**
	 * Gets a site.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function get( $site_id ) {
		if ( ! is_array( $this->site ) || (int) $this->site['id'] !== (int) $site_id ) {
			return null;
		}

		return $this->site;
	}

	/**
	 * Records pause.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function pause_polling( $site_id ) {
		$this->pause_calls[] = (int) $site_id;

		return $this->pause_result;
	}

	/**
	 * Records resume.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function resume_polling( $site_id ) {
		$this->resume_calls[] = (int) $site_id;

		return $this->resume_result;
	}

	/**
	 * Records archive.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function archive_local( $site_id ) {
		$this->archive_calls[] = (int) $site_id;

		return $this->archive_result;
	}

	/**
	 * Records unarchive.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function unarchive_local( $site_id ) {
		$this->unarchive_calls[] = (int) $site_id;

		return $this->unarchive_result;
	}
}
