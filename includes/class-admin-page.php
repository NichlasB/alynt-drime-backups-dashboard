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

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Drime Backups Dashboard', 'alynt-drime-backups-dashboard' ); ?></h1>

			<div class="notice notice-info inline">
				<p>
					<?php
					esc_html_e(
						'Dashboard scaffold is installed locally. Pairing, polling, and client status ingestion remain disabled until the read-only protocol is implemented and approved.',
						'alynt-drime-backups-dashboard'
					);
					?>
				</p>
			</div>

			<h2><?php esc_html_e( 'v1 Boundary', 'alynt-drime-backups-dashboard' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Dashboard-generated one-time pairing token.', 'alynt-drime-backups-dashboard' ); ?></li>
				<li><?php esc_html_e( 'Client-site opt-in before any status endpoint is enabled.', 'alynt-drime-backups-dashboard' ); ?></li>
				<li><?php esc_html_e( 'Dashboard polling is read-only and fixed to the approved client status route.', 'alynt-drime-backups-dashboard' ); ?></li>
				<li><?php esc_html_e( 'No remote backup, restore, delete, cleanup, settings, or credential mutation actions.', 'alynt-drime-backups-dashboard' ); ?></li>
			</ul>

			<h2><?php esc_html_e( 'Eventual Host', 'alynt-drime-backups-dashboard' ); ?></h2>
			<p>
				<?php
				esc_html_e(
					'Planned host profile: control-sitesmanage live-only. No live site changes have been made by this scaffold.',
					'alynt-drime-backups-dashboard'
				);
				?>
			</p>
		</div>
		<?php
	}
}
