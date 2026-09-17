<?php
/**
 * Admin page shell rendering.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the top-level dashboard shell and tab navigation.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Shell {
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
