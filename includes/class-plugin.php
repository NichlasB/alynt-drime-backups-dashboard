<?php
/**
 * Plugin orchestrator.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Plugin {
	/**
	 * Admin page.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Admin_Page
	 */
	private $admin_page;

	/**
	 * Poller.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Poller
	 */
	private $poller;

	/**
	 * Snapshot repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Snapshot_Repository
	 */
	private $snapshots;

	/**
	 * Remote action repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
	 */
	private $remote_actions;

	/**
	 * Shared opt-in diagnostics event log.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Event_Log
	 */
	private $event_log;

	/**
	 * Enrollment REST controller.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller
	 */
	private $enrollment_rest_controller;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$installed = Alynt_Drime_Backups_Dashboard_Storage::maybe_upgrade();
		if ( is_wp_error( $installed ) ) {
			add_action( 'admin_notices', array( $this, 'storage_error_notice' ) );
			return;
		}

		$sites                = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->snapshots      = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$source_policy        = new Alynt_Drime_Backups_Dashboard_Source_Policy();
		$classifier           = new Alynt_Drime_Backups_Dashboard_Status_Classifier( $source_policy );
		$event_log            = new Alynt_Drime_Backups_Dashboard_Event_Log();
		$this->event_log      = $event_log;
		$this->remote_actions = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$reconciler           = new Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler( $this->remote_actions );
		$this->poller         = new Alynt_Drime_Backups_Dashboard_Poller( $sites, $this->snapshots, $classifier, null, null, null, null, $event_log, $reconciler );
		$diagnostics          = new Alynt_Drime_Backups_Dashboard_Diagnostics( $sites, $this->snapshots, $classifier, $event_log, $this->remote_actions );
		$dispatcher           = new Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher( $sites, $this->snapshots, $this->remote_actions );

		$this->admin_page                 = new Alynt_Drime_Backups_Dashboard_Admin_Page(
			$sites,
			$this->snapshots,
			$classifier,
			null,
			$this->poller,
			$diagnostics,
			$this->remote_actions,
			null,
			$dispatcher,
			$source_policy
		);
		$this->enrollment_rest_controller = new Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller( null, null, null, $event_log );

		$this->hooks();
	}

	/**
	 * Reports a failed storage update without enabling normal dashboard hooks.
	 *
	 * @return void
	 */
	public function storage_error_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// Persistent configuration failure: keep visible until storage is repaired.
		echo '<div class="notice notice-error" role="alert"><p>' . esc_html__( 'The dashboard could not finish its storage update. Please retry activation or contact support. Dashboard operations are paused until the update succeeds.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
	}

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		// phpcs:ignore WordPress.WP.CronInterval.ChangeDetected -- The interval value is registered in cron_schedules().
		add_filter( 'cron_schedules', array( 'Alynt_Drime_Backups_Dashboard_Poller', 'cron_schedules' ) );
		add_action( 'admin_menu', array( $this->admin_page, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this->admin_page, 'enqueue_assets' ) );
		add_action( 'rest_api_init', array( $this->enrollment_rest_controller, 'register_routes' ) );
		add_action( Alynt_Drime_Backups_Dashboard_Poller::CRON_HOOK, array( $this->poller, 'poll_sites' ) );
		add_action( Alynt_Drime_Backups_Dashboard_Poller::CLEANUP_HOOK, array( $this, 'cleanup_retention' ) );
	}

	/**
	 * Runs bounded dashboard database retention cleanup.
	 *
	 * @return void
	 */
	public function cleanup_retention() {
		// Attempt both original batches before reporting, even if logging is unavailable.
		$snapshots = $this->snapshots->cleanup_retention();
		$actions   = $this->remote_actions->cleanup_retention();
		foreach ( array(
			'snapshot_cleanup_failed'      => $snapshots,
			'remote_action_cleanup_failed' => $actions,
		) as $code => $result ) {
			if ( ! is_wp_error( $result ) ) {
				continue;
			}
			// Never forward error messages/data: repository details may contain private SQL.
			$message = 'snapshot_cleanup_failed' === $code
				? __( 'The dashboard could not clean up old status snapshots.', 'alynt-drime-backups-dashboard' )
				: __( 'The dashboard could not clean up old remote action records.', 'alynt-drime-backups-dashboard' );
			$this->event_log->log( 'error', 'storage', $code, $message );
		}
	}
}
