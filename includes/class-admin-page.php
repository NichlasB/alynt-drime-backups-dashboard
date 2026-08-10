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
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null     $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Snapshot_Repository|null $snapshots Snapshot repository.
	 * @param Alynt_Drime_Backups_Dashboard_Status_Classifier|null   $classifier Status classifier.
	 * @param Alynt_Drime_Backups_Dashboard_Enrollment_Manager|null  $enrollment_manager Enrollment manager.
	 * @param Alynt_Drime_Backups_Dashboard_Poller|null              $poller Poller.
	 * @param Alynt_Drime_Backups_Dashboard_Diagnostics|null         $diagnostics Diagnostics.
	 */
	public function __construct( $sites = null, $snapshots = null, $classifier = null, $enrollment_manager = null, $poller = null, $diagnostics = null ) {
		$this->sites              = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->snapshots          = $snapshots instanceof Alynt_Drime_Backups_Dashboard_Snapshot_Repository ? $snapshots : new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$this->classifier         = $classifier instanceof Alynt_Drime_Backups_Dashboard_Status_Classifier ? $classifier : new Alynt_Drime_Backups_Dashboard_Status_Classifier();
		$this->enrollment_manager = $enrollment_manager instanceof Alynt_Drime_Backups_Dashboard_Enrollment_Manager ? $enrollment_manager : new Alynt_Drime_Backups_Dashboard_Enrollment_Manager( $this->sites );
		$this->poller             = $poller instanceof Alynt_Drime_Backups_Dashboard_Poller ? $poller : new Alynt_Drime_Backups_Dashboard_Poller( $this->sites, $this->snapshots, $this->classifier );
		$this->diagnostics        = $diagnostics instanceof Alynt_Drime_Backups_Dashboard_Diagnostics ? $diagnostics : new Alynt_Drime_Backups_Dashboard_Diagnostics( $this->sites, $this->snapshots, $this->classifier );
		$this->event_log          = $this->diagnostics->event_log();
	}

	/**
	 * Registers the admin menu entry.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_management_page(
			__( 'Drime Backups Dashboard', 'alynt-drime-backups-dashboard' ),
			__( 'Drime Backups Dashboard', 'alynt-drime-backups-dashboard' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render' )
		);
	}

	/**
	 * Renders the current read-only scaffold page.
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
		<div class="wrap">
			<h1><?php esc_html_e( 'Drime Backups Dashboard', 'alynt-drime-backups-dashboard' ); ?></h1>

			<div class="notice notice-info inline">
				<p>
					<?php
					esc_html_e(
						'This dashboard remains read-only relative to client sites and Drime. It can create local enrollment records, ingest opt-in status payloads, and poll the fixed status endpoint, but it does not run remote backup, restore, cleanup, settings, credential, or Drime actions.',
						'alynt-drime-backups-dashboard'
					);
					?>
				</p>
			</div>

			<?php $this->render_tabs( $tab ); ?>
			<?php $this->render_action_result( $result ); ?>

			<?php
			switch ( $tab ) {
				case 'add-site':
					$this->render_add_site_shell( $result );
					break;
				case 'site':
					$this->render_site_detail_shell();
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

		foreach ( $tabs as $tab => $label ) {
			$url   = add_query_arg(
				array(
					'page' => self::MENU_SLUG,
					'tab'  => $tab,
				),
				admin_url( 'tools.php' )
			);
			$class = $tab === $active ? ' nav-tab-active' : '';

			printf(
				'<a class="nav-tab%1$s" href="%2$s">%3$s</a>',
				esc_attr( $class ),
				esc_url( $url ),
				esc_html( $label )
			);
		}

		echo '</nav>';
	}
}
