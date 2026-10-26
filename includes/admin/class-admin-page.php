<?php
/**
 * Admin page shell.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the dashboard admin surface.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Admin_Page {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Actions;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Display_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Shell;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Timestamp_Helpers;

	const MENU_SLUG = 'alynt-drime-backups-dashboard';

	/**
	 * Registered WordPress admin page hook.
	 *
	 * @var string
	 */
	private $page_hook = '';

	/**
	 * Cached sites, snapshots, and status counts for the current request.
	 *
	 * @var array<string,mixed>|null
	 */
	private $site_status_context = null;

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
	 * Status classifier.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Status_Classifier
	 */
	private $classifier;

	/**
	 * Enrollment manager.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Enrollment_Manager
	 */
	private $enrollment_manager;

	/**
	 * Poller.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Poller
	 */
	private $poller;

	/**
	 * Diagnostics provider.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Diagnostics
	 */
	private $diagnostics;

	/**
	 * Structured event log.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Event_Log
	 */
	private $event_log;

	/**
	 * Remote action repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
	 */
	private $remote_actions;

	/**
	 * Remote action opt-in manager.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Manager
	 */
	private $action_opt_in_manager;

	/**
	 * Remote action dispatcher.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher
	 */
	private $remote_action_dispatcher;

	/**
	 * Dashboard-owned source policy.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Source_Policy
	 */
	private $source_policy;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null              $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Snapshot_Repository|null          $snapshots Snapshot repository.
	 * @param Alynt_Drime_Backups_Dashboard_Status_Classifier|null            $classifier Status classifier.
	 * @param Alynt_Drime_Backups_Dashboard_Enrollment_Manager|null           $enrollment_manager Enrollment manager.
	 * @param Alynt_Drime_Backups_Dashboard_Poller|null                       $poller Poller.
	 * @param Alynt_Drime_Backups_Dashboard_Diagnostics|null                  $diagnostics Diagnostics.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Repository|null     $remote_actions Remote action repository.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Manager|null $action_opt_in_manager Action opt-in manager.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher|null     $remote_action_dispatcher Remote action dispatcher.
	 * @param Alynt_Drime_Backups_Dashboard_Source_Policy|null                $source_policy Source policy store.
	 */
	public function __construct( $sites = null, $snapshots = null, $classifier = null, $enrollment_manager = null, $poller = null, $diagnostics = null, $remote_actions = null, $action_opt_in_manager = null, $remote_action_dispatcher = null, $source_policy = null ) {
		$this->sites                    = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->snapshots                = $snapshots instanceof Alynt_Drime_Backups_Dashboard_Snapshot_Repository ? $snapshots : new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$this->source_policy            = $source_policy instanceof Alynt_Drime_Backups_Dashboard_Source_Policy ? $source_policy : new Alynt_Drime_Backups_Dashboard_Source_Policy();
		$this->classifier               = $classifier instanceof Alynt_Drime_Backups_Dashboard_Status_Classifier ? $classifier : new Alynt_Drime_Backups_Dashboard_Status_Classifier( $this->source_policy );
		$this->enrollment_manager       = $enrollment_manager instanceof Alynt_Drime_Backups_Dashboard_Enrollment_Manager ? $enrollment_manager : new Alynt_Drime_Backups_Dashboard_Enrollment_Manager( $this->sites );
		$this->poller                   = $poller instanceof Alynt_Drime_Backups_Dashboard_Poller ? $poller : new Alynt_Drime_Backups_Dashboard_Poller( $this->sites, $this->snapshots, $this->classifier );
		$this->diagnostics              = $diagnostics instanceof Alynt_Drime_Backups_Dashboard_Diagnostics ? $diagnostics : new Alynt_Drime_Backups_Dashboard_Diagnostics( $this->sites, $this->snapshots, $this->classifier );
		$this->event_log                = $this->diagnostics->event_log();
		$this->remote_actions           = $remote_actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository ? $remote_actions : new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$this->action_opt_in_manager    = $action_opt_in_manager instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Manager ? $action_opt_in_manager : new Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Manager( $this->sites );
		$this->remote_action_dispatcher = $remote_action_dispatcher instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher ? $remote_action_dispatcher : new Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher( $this->sites, $this->snapshots, $this->remote_actions );
	}

	/**
	 * Registers the admin menu entry.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_menu() {
		$this->page_hook = add_management_page(
			__( 'Drime Backups Dashboard', 'alynt-drime-backups-dashboard' ),
			__( 'Drime Backups Dashboard', 'alynt-drime-backups-dashboard' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render' )
		);

		if ( '' !== $this->page_hook ) {
			add_action( 'load-' . $this->page_hook, array( $this, 'send_no_cache_headers' ) );
		}
	}

	/**
	 * Sends no-cache headers for the dashboard admin surface.
	 *
	 * @since 0.1.35
	 *
	 * @return void
	 */
	public function send_no_cache_headers() {
		if ( function_exists( 'nocache_headers' ) ) {
			nocache_headers();
		}
	}

	/**
	 * Enqueues the dashboard assets only on this plugin screen.
	 *
	 * @since 0.1.0
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( '' === $this->page_hook || $hook_suffix !== $this->page_hook ) {
			return;
		}

		wp_enqueue_style(
			'alynt-drime-backups-dashboard-admin',
			ALYNT_DRIME_BACKUPS_DASHBOARD_URL . 'assets/dist/admin/index.css',
			array(),
			ALYNT_DRIME_BACKUPS_DASHBOARD_VERSION
		);
		wp_enqueue_script(
			'alynt-drime-backups-dashboard-admin',
			ALYNT_DRIME_BACKUPS_DASHBOARD_URL . 'assets/dist/admin/index.js',
			array(),
			ALYNT_DRIME_BACKUPS_DASHBOARD_VERSION,
			true
		);
	}
}
