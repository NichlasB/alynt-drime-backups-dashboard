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
	 */
	public function __construct( $sites = null, $snapshots = null, $classifier = null, $enrollment_manager = null, $poller = null, $diagnostics = null, $remote_actions = null, $action_opt_in_manager = null ) {
		$this->sites                 = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->snapshots             = $snapshots instanceof Alynt_Drime_Backups_Dashboard_Snapshot_Repository ? $snapshots : new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$this->classifier            = $classifier instanceof Alynt_Drime_Backups_Dashboard_Status_Classifier ? $classifier : new Alynt_Drime_Backups_Dashboard_Status_Classifier();
		$this->enrollment_manager    = $enrollment_manager instanceof Alynt_Drime_Backups_Dashboard_Enrollment_Manager ? $enrollment_manager : new Alynt_Drime_Backups_Dashboard_Enrollment_Manager( $this->sites );
		$this->poller                = $poller instanceof Alynt_Drime_Backups_Dashboard_Poller ? $poller : new Alynt_Drime_Backups_Dashboard_Poller( $this->sites, $this->snapshots, $this->classifier );
		$this->diagnostics           = $diagnostics instanceof Alynt_Drime_Backups_Dashboard_Diagnostics ? $diagnostics : new Alynt_Drime_Backups_Dashboard_Diagnostics( $this->sites, $this->snapshots, $this->classifier );
		$this->event_log             = $this->diagnostics->event_log();
		$this->remote_actions        = $remote_actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository ? $remote_actions : new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$this->action_opt_in_manager = $action_opt_in_manager instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Manager ? $action_opt_in_manager : new Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Manager( $this->sites );
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

	/**
	 * Renders the current read-only scaffold page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'alynt-drime-backups-dashboard' ) );
		}

		$tab    = $this->current_tab();
		$result = $this->handle_post_action();

		?>
		<div class="wrap adbd-wrap">
			<h1><?php esc_html_e( 'Drime Backups Dashboard', 'alynt-drime-backups-dashboard' ); ?></h1>

			<div class="notice notice-info inline adbd-read-only-notice">
				<p>
					<?php
					esc_html_e(
						'Read-only dashboard. This page shows what paired client sites report about their own backup uploads. It cannot create, restore, delete, or clean up backups, and it cannot change client-site settings, credentials, or Drime data. Its actions are limited to polling the fixed status endpoint and managing this dashboard\'s local records.',
						'alynt-drime-backups-dashboard'
					);
					?>
				</p>
			</div>
			<hr class="wp-header-end">

			<?php $this->render_tabs( $tab ); ?>
			<?php $this->render_action_result( $result ); ?>

			<?php
			switch ( $tab ) {
				case 'add-site':
					$this->render_add_site_shell( $result );
					break;
				case 'site':
					$this->render_site_detail_shell( $result );
					break;
				case 'attention':
					$this->render_attention_shell();
					break;
				case 'diagnostics':
					$this->render_diagnostics_shell();
					break;
				case 'sites':
				default:
					$this->render_sites_shell();
					break;
			}
			?>
		</div>
		<?php
	}

	/**
	 * Gets the current tab.
	 *
	 * @return string
	 */
	private function current_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'sites'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return in_array( $tab, array( 'sites', 'add-site', 'site', 'attention', 'diagnostics' ), true ) ? $tab : 'sites';
	}

	/**
	 * Renders navigation tabs.
	 *
	 * @param string $active Active tab.
	 * @return void
	 */
	private function render_tabs( $active ) {
		$tabs = array(
			'sites'       => __( 'Sites', 'alynt-drime-backups-dashboard' ),
			'add-site'    => __( 'Add Site', 'alynt-drime-backups-dashboard' ),
			'attention'   => __( 'Attention', 'alynt-drime-backups-dashboard' ),
			'diagnostics' => __( 'Diagnostics', 'alynt-drime-backups-dashboard' ),
		);

		echo '<nav class="nav-tab-wrapper" aria-label="' . esc_attr__( 'Dashboard sections', 'alynt-drime-backups-dashboard' ) . '">';

		$attention_count = $this->attention_count();

		foreach ( $tabs as $tab => $label ) {
			$url     = add_query_arg(
				array(
					'page' => self::MENU_SLUG,
					'tab'  => $tab,
				),
				admin_url( 'tools.php' )
			);
			$class   = $tab === $active ? ' nav-tab-active' : '';
			$current = $tab === $active ? ' aria-current="page"' : '';

			$count_markup = '';

			if ( 'attention' === $tab ) {
				$count_markup = sprintf(
					'<span class="adbd-tab-count" aria-label="%1$s">%2$s</span>',
					esc_attr(
						sprintf(
							/* translators: %d: number of sites needing attention. */
							_n( '%d site needs attention', '%d sites need attention', $attention_count, 'alynt-drime-backups-dashboard' ),
							$attention_count
						)
					),
					esc_html( number_format_i18n( $attention_count ) )
				);
			}

			printf(
				'<a class="nav-tab%1$s" href="%2$s"%3$s>%4$s%5$s</a>',
				esc_attr( $class ),
				esc_url( $url ),
				$current, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static attribute set above.
				esc_html( $label ),
				$count_markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Constructed from escaped values above.
			);
		}

		echo '</nav>';
	}
}
