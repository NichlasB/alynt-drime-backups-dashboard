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
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null     $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Snapshot_Repository|null $snapshots Snapshot repository.
	 * @param Alynt_Drime_Backups_Dashboard_Status_Classifier|null   $classifier Status classifier.
	 * @param Alynt_Drime_Backups_Dashboard_Enrollment_Manager|null  $enrollment_manager Enrollment manager.
	 */
	public function __construct( $sites = null, $snapshots = null, $classifier = null, $enrollment_manager = null ) {
		$this->sites              = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->snapshots          = $snapshots instanceof Alynt_Drime_Backups_Dashboard_Snapshot_Repository ? $snapshots : new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$this->classifier         = $classifier instanceof Alynt_Drime_Backups_Dashboard_Status_Classifier ? $classifier : new Alynt_Drime_Backups_Dashboard_Status_Classifier();
		$this->enrollment_manager = $enrollment_manager instanceof Alynt_Drime_Backups_Dashboard_Enrollment_Manager ? $enrollment_manager : new Alynt_Drime_Backups_Dashboard_Enrollment_Manager( $this->sites );
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
						'Dashboard Phase 2 shell is local-only. Pairing, polling, and client status ingestion remain disabled until the read-only protocol is implemented and approved.',
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
	 * Handles approved local dashboard POST actions.
	 *
	 * @return array<string,mixed>|WP_Error|null
	 */
	private function handle_post_action() {
		if ( empty( $_POST['alynt_drime_backups_dashboard_action'] ) ) {
			return null;
		}

		$action = sanitize_key( wp_unslash( $_POST['alynt_drime_backups_dashboard_action'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( 'create_pending_site' === $action ) {
			check_admin_referer( 'alynt_drime_backups_dashboard_create_pending_site' );

			$pending_site = isset( $_POST['alynt_drime_backups_dashboard_pending_site'] ) ? wp_unslash( $_POST['alynt_drime_backups_dashboard_pending_site'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$raw          = is_array( $pending_site ) ? $pending_site : array();

			return $this->enrollment_manager->create_pending_site( $raw, home_url( '/', 'https' ) );
		}

		if ( 'revoke_local' === $action ) {
			check_admin_referer( 'alynt_drime_backups_dashboard_revoke_local' );

			$site_id = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			return array(
				'action'  => 'revoke_local',
				'success' => $site_id > 0 && $this->sites->revoke_local( $site_id ),
			);
		}

		return new WP_Error( 'dashboard_action_unknown', __( 'The requested dashboard action is not supported.', 'alynt-drime-backups-dashboard' ) );
	}

	/**
	 * Renders an action result notice.
	 *
	 * @param array<string,mixed>|WP_Error|null $result Result.
	 * @return void
	 */
	private function render_action_result( $result ) {
		if ( null === $result ) {
			return;
		}

		if ( is_wp_error( $result ) ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
			return;
		}

		if ( isset( $result['pairing_token'] ) ) {
			echo '<div class="notice notice-success inline"><p>' . esc_html__( 'Pending dashboard site created. Copy the pairing token now; it is not stored and cannot be shown again.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
			return;
		}

		if ( isset( $result['action'] ) && 'revoke_local' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Dashboard record revoked locally. No client site or Drime action was attempted.', 'alynt-drime-backups-dashboard' )
				: __( 'The dashboard record could not be revoked locally.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			echo '<div class="notice ' . esc_attr( $class ) . ' inline"><p>' . esc_html( $message ) . '</p></div>';
		}
	}

	/**
	 * Gets the current tab.
	 *
	 * @return string
	 */
	private function current_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'sites'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return in_array( $tab, array( 'sites', 'add-site', 'site', 'attention' ), true ) ? $tab : 'sites';
	}

	/**
	 * Renders navigation tabs.
	 *
	 * @param string $active Active tab.
	 * @return void
	 */
	private function render_tabs( $active ) {
		$tabs = array(
			'sites'     => __( 'Sites', 'alynt-drime-backups-dashboard' ),
			'add-site'  => __( 'Add Site', 'alynt-drime-backups-dashboard' ),
			'attention' => __( 'Attention', 'alynt-drime-backups-dashboard' ),
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

	/**
	 * Renders the Sites shell.
	 *
	 * @return void
	 */
	private function render_sites_shell() {
		$sites     = $this->sites->all();
		$snapshots = $this->snapshots->latest_by_site_ids( wp_list_pluck( $sites, 'id' ) );

		echo '<h2>' . esc_html__( 'Sites', 'alynt-drime-backups-dashboard' ) . '</h2>';

		if ( empty( $sites ) ) {
			$this->render_empty_state();
			$this->render_fixture_status_guide();
			return;
		}

		$this->render_sites_table( $sites, $snapshots );
	}

	/**
	 * Renders the Add Site shell.
	 *
	 * @param array<string,mixed>|WP_Error|null $result Action result.
	 * @return void
	 */
	private function render_add_site_shell( $result = null ) {
		?>
		<h2><?php esc_html_e( 'Add Site', 'alynt-drime-backups-dashboard' ); ?></h2>
		<p><?php esc_html_e( 'Create a local pending enrollment and display one dashboard-generated token for the client-site administrator to paste into the uploader. This does not contact the client site or Drime.', 'alynt-drime-backups-dashboard' ); ?></p>
		<?php if ( is_array( $result ) && isset( $result['pairing_token'] ) ) : ?>
			<h3><?php esc_html_e( 'Display-once pairing token', 'alynt-drime-backups-dashboard' ); ?></h3>
			<p><?php esc_html_e( 'Copy this token now. The dashboard stores only a verifier and safe metadata.', 'alynt-drime-backups-dashboard' ); ?></p>
			<textarea class="large-text code" rows="5" readonly="readonly" aria-label="<?php esc_attr_e( 'Display-once pairing token', 'alynt-drime-backups-dashboard' ); ?>"><?php echo esc_textarea( (string) $result['pairing_token'] ); ?></textarea>
			<table class="widefat striped">
				<tbody>
					<?php $this->render_detail_row( __( 'Client origin', 'alynt-drime-backups-dashboard' ), (string) $result['expected_origin'] ); ?>
					<?php $this->render_detail_row( __( 'Status endpoint preview', 'alynt-drime-backups-dashboard' ), (string) $result['status_endpoint_preview'] ); ?>
					<?php $this->render_detail_row( __( 'Expires', 'alynt-drime-backups-dashboard' ), (string) $result['pairing_expires_at'] ); ?>
				</tbody>
			</table>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_create_pending_site' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="create_pending_site">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="alynt-dashboard-site-label"><?php esc_html_e( 'Site label', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td><input type="text" id="alynt-dashboard-site-label" name="alynt_drime_backups_dashboard_pending_site[site_label]" class="regular-text" required="required" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="alynt-dashboard-expected-origin"><?php esc_html_e( 'Expected client origin', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td>
						<input type="url" id="alynt-dashboard-expected-origin" name="alynt_drime_backups_dashboard_pending_site[expected_origin]" class="regular-text" required="required" placeholder="https://example.com" />
						<p class="description"><?php esc_html_e( 'Public HTTPS origin only. The status endpoint path is fixed by the v1 protocol.', 'alynt-drime-backups-dashboard' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="alynt-dashboard-environment"><?php esc_html_e( 'Environment', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td>
						<select id="alynt-dashboard-environment" name="alynt_drime_backups_dashboard_pending_site[environment]">
							<option value="production"><?php esc_html_e( 'Production', 'alynt-drime-backups-dashboard' ); ?></option>
							<option value="staging"><?php esc_html_e( 'Staging', 'alynt-drime-backups-dashboard' ); ?></option>
							<option value="development"><?php esc_html_e( 'Development', 'alynt-drime-backups-dashboard' ); ?></option>
							<option value="other"><?php esc_html_e( 'Other', 'alynt-drime-backups-dashboard' ); ?></option>
						</select>
					</td>
				</tr>
			</table>
			<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Generate Pairing Token', 'alynt-drime-backups-dashboard' ); ?></button></p>
		</form>
		<?php
	}

	/**
	 * Renders the Attention shell.
	 *
	 * @return void
	 */
	private function render_attention_shell() {
		$sites     = $this->sites->all();
		$snapshots = $this->snapshots->latest_by_site_ids( wp_list_pluck( $sites, 'id' ) );
		$attention = array();

		foreach ( $sites as $site ) {
			$snapshot = isset( $snapshots[ (int) $site['id'] ] ) ? $snapshots[ (int) $site['id'] ] : null;
			$status   = $this->classifier->classify( $site, $snapshot );

			if ( in_array( $status['category'], array( 'incompatible', 'not_reporting', 'needs_attention', 'not_configured' ), true ) ) {
				$site['_dashboard_status'] = $status;
				$attention[]               = $site;
			}
		}

		echo '<h2>' . esc_html__( 'Attention', 'alynt-drime-backups-dashboard' ) . '</h2>';

		if ( empty( $attention ) ) {
			echo '<p>' . esc_html__( 'No client sites currently need attention. This view will populate after enrollment and polling are implemented.', 'alynt-drime-backups-dashboard' ) . '</p>';
			$this->render_fixture_status_guide();
			return;
		}

		$this->render_sites_table( $attention, $snapshots );
	}

	/**
	 * Renders one site detail shell.
	 *
	 * @return void
	 */
	private function render_site_detail_shell() {
		$site_id  = isset( $_GET['site_id'] ) ? absint( wp_unslash( $_GET['site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$site     = $site_id > 0 ? $this->sites->get( $site_id ) : null;
		$snapshot = $site ? $this->snapshots->latest_for_site( $site_id ) : null;

		echo '<h2>' . esc_html__( 'Site Detail', 'alynt-drime-backups-dashboard' ) . '</h2>';

		if ( ! $site ) {
			echo '<p>' . esc_html__( 'No site record was found for this shell view.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		$status = $this->classifier->classify( $site, $snapshot );

		echo '<table class="widefat striped"><tbody>';
		$this->render_detail_row( __( 'Site', 'alynt-drime-backups-dashboard' ), $this->site_name( $site ) );
		$this->render_detail_row( __( 'Expected origin', 'alynt-drime-backups-dashboard' ), isset( $site['expected_origin'] ) ? $site['expected_origin'] : '' );
		$this->render_detail_row( __( 'Environment', 'alynt-drime-backups-dashboard' ), isset( $site['environment'] ) ? $site['environment'] : '' );
		$this->render_detail_row( __( 'Enrollment', 'alynt-drime-backups-dashboard' ), isset( $site['enrollment_status'] ) ? $site['enrollment_status'] : '' );
		$this->render_detail_row( __( 'Status', 'alynt-drime-backups-dashboard' ), $status['label'] );
		$this->render_detail_row( __( 'Message', 'alynt-drime-backups-dashboard' ), $status['message'] );
		$this->render_detail_row( __( 'Last seen', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( isset( $site['last_seen_at'] ) ? $site['last_seen_at'] : '' ) );
		echo '</tbody></table>';

		if ( ! isset( $site['enrollment_status'] ) || 'revoked' !== $site['enrollment_status'] ) {
			?>
			<form method="post">
				<?php wp_nonce_field( 'alynt_drime_backups_dashboard_revoke_local' ); ?>
				<input type="hidden" name="alynt_drime_backups_dashboard_action" value="revoke_local">
				<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
				<p><button type="submit" class="button"><?php esc_html_e( 'Revoke Local Dashboard Record', 'alynt-drime-backups-dashboard' ); ?></button></p>
			</form>
			<?php
		}
	}

	/**
	 * Renders a detail table row.
	 *
	 * @param string $label Row label.
	 * @param string $value Row value.
	 * @return void
	 */
	private function render_detail_row( $label, $value ) {
		printf(
			'<tr><th scope="row">%1$s</th><td>%2$s</td></tr>',
			esc_html( $label ),
			esc_html( $value )
		);
	}

	/**
	 * Renders a sites table.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @param array<int,array<string,mixed>> $snapshots Snapshots keyed by site ID.
	 * @return void
	 */
	private function render_sites_table( array $sites, array $snapshots ) {
		echo '<table class="widefat striped">';
		echo '<thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Site', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Environment', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Enrollment', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Status', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Last seen', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Message', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $sites as $site ) {
			$site_id  = (int) $site['id'];
			$snapshot = isset( $snapshots[ $site_id ] ) ? $snapshots[ $site_id ] : null;
			$status   = isset( $site['_dashboard_status'] ) ? $site['_dashboard_status'] : $this->classifier->classify( $site, $snapshot );
			$url      = add_query_arg(
				array(
					'page'    => self::MENU_SLUG,
					'tab'     => 'site',
					'site_id' => $site_id,
				),
				admin_url( 'tools.php' )
			);

			echo '<tr>';
			printf(
				'<td><a href="%1$s">%2$s</a><br><span class="description">%3$s</span></td>',
				esc_url( $url ),
				esc_html( $this->site_name( $site ) ),
				esc_html( isset( $site['expected_origin'] ) ? $site['expected_origin'] : '' )
			);
			echo '<td>' . esc_html( isset( $site['environment'] ) ? $site['environment'] : '' ) . '</td>';
			echo '<td>' . esc_html( isset( $site['enrollment_status'] ) ? $site['enrollment_status'] : '' ) . '</td>';
			echo '<td>' . esc_html( $status['label'] ) . '</td>';
			echo '<td>' . esc_html( $this->date_or_dash( isset( $site['last_seen_at'] ) ? $site['last_seen_at'] : '' ) ) . '</td>';
			echo '<td>' . esc_html( $status['message'] ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Renders the empty state.
	 *
	 * @return void
	 */
	private function render_empty_state() {
		echo '<p>' . esc_html__( 'No client sites are enrolled yet. The dashboard remains read-only and local until the pairing protocol is approved.', 'alynt-drime-backups-dashboard' ) . '</p>';
	}

	/**
	 * Renders status category fixture guidance.
	 *
	 * @return void
	 */
	private function render_fixture_status_guide() {
		$categories = array(
			'pending',
			'paused',
			'incompatible',
			'not_reporting',
			'needs_attention',
			'not_configured',
			'working',
		);

		echo '<h3>' . esc_html__( 'Status categories', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<ul>';

		foreach ( $categories as $category ) {
			echo '<li>' . esc_html( $this->classifier->label( $category ) ) . '</li>';
		}

		echo '</ul>';
	}

	/**
	 * Gets a display name for a site.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function site_name( array $site ) {
		if ( ! empty( $site['site_label'] ) ) {
			return (string) $site['site_label'];
		}

		if ( ! empty( $site['expected_origin'] ) ) {
			return (string) $site['expected_origin'];
		}

		return __( 'Unnamed site', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Formats a date-ish value or returns a dash.
	 *
	 * @param string $value Date value.
	 * @return string
	 */
	private function date_or_dash( $value ) {
		if ( '' === (string) $value ) {
			return '-';
		}

		return (string) $value;
	}
}
