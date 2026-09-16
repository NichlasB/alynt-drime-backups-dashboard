<?php
/**
 * Remote action capability sanitizer.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates optional V2 remote-action capability summaries.
 *
 * @since 0.1.15
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Public;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Action_Data;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedules;

	const PROTOCOL_VERSION        = 2;
	const ACTION_SCAN_UPLOAD_NOW  = 'scan_upload_now';
	const ACTION_SCHEDULE_PREVIEW = 'schedule_preview';
	const ACTION_SCHEDULE_APPLY   = 'schedule_apply';
	const SCHEDULE_SCAN_UPLOAD    = 'alynt_scan_upload';

	const MAX_ALLOWED_ACTIONS       = 5;
	const MAX_RESULT_SUMMARY_LENGTH = 160;
	const MAX_SCHEDULES             = 5;
	const MAX_SCHEDULE_CADENCES     = 10;
	const MAX_SCHEDULE_LABEL_LENGTH = 80;

	/**
	 * Allowed action states.
	 *
	 * @var array<int,string>
	 */
	private $allowed_states = array(
		'queued_for_dispatch',
		'dispatch_failed',
		'accepted',
		'rejected',
		'unsupported',
		'rate_limited',
		'busy',
		'running',
		'succeeded',
		'failed',
		'timed_out',
		'stale',
	);

	/**
	 * Fields that must never enter remote-action summaries.
	 *
	 * @var array<int,string>
	 */
	private $forbidden_fields = array(
		'api_token',
		'authorization',
		'backup_id',
		'backup_name',
		'backup_path',
		'backup_set_id',
		'body',
		'checksum_path',
		'command',
		'cookie',
		'drime_id',
		'file',
		'filename',
		'local_path',
		'manifest_path',
		'nonce',
		'package_id',
		'package_name',
		'password',
		'path',
		'private_key',
		'raw_response',
		'remote_catalog_path',
		'remote_index_path',
		'secret',
		'signature',
		'signed_url',
		'sql',
		'token',
	);
}
