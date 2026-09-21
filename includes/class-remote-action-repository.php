<?php
/**
 * Remote action repository.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores dashboard-owned remote action request records.
 *
 * @since 0.1.15
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {

	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Sanitizers;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Context;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Lookups;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Rollback_Preview_Lookups;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Summary;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_State;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Reconciliation;

	const DEFAULT_STATE                       = 'queued_for_dispatch';
	const RETENTION_DAYS                      = 90;
	const ACCEPTED_CONFIRMATION_STALE_SECONDS = 1800;
	const RUNNING_CONFIRMATION_STALE_SECONDS  = 3600;
	const SCHEDULE_PREVIEW_FRESH_SECONDS      = 900;

	/**
	 * Capability sanitizer.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities
	 */
	private $capabilities;

	/**
	 * Constructor.
	 *
	 * @since 0.1.15
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities|null $capabilities Capability sanitizer.
	 */
	public function __construct( $capabilities = null ) {
		$this->capabilities = $capabilities instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities ? $capabilities : new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
	}
}
