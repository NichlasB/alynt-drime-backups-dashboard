<?php
/**
 * Admin page Sites list shell.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the dashboard Sites list screen and request-local status context.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Context;

	/**
	 * Renders the Sites shell.
	 *
	 * @return void
	 */
	private function render_sites_shell() {
		$show_archived  = $this->show_archived_records();
		$context        = $this->site_status_context( $show_archived ? 'archived' : 'visible' );
		$sites          = $context['sites'];
		$snapshots      = $context['snapshots'];
		$archived_count = count(
			$this->sites->all(
				array(
					'archived' => 'only',
					'limit'    => 500,
				)
			)
		);

		echo '<section aria-labelledby="adbd-sites-heading">';
		echo '<h2 id="adbd-sites-heading">' . esc_html( $show_archived ? __( 'Archived Local Records', 'alynt-drime-backups-dashboard' ) : __( 'Sites', 'alynt-drime-backups-dashboard' ) ) . '</h2>';

		$this->render_archived_records_toggle( $show_archived, $archived_count );

		if ( empty( $sites ) ) {
			if ( $show_archived ) {
				echo '<div class="adbd-empty-state"><span class="dashicons dashicons-archive" aria-hidden="true"></span><h3>' . esc_html__( 'No Archived Local Records', 'alynt-drime-backups-dashboard' ) . '</h3><p>' . esc_html__( 'Archived dashboard records remain retained locally for audit/history. None are archived right now.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
			} else {
				$this->render_empty_state();
			}
			echo '</section>';
			return;
		}

		echo '<p class="adbd-screen-intro">';
		if ( $show_archived ) {
			printf(
				esc_html(
					/* translators: %d: number of archived dashboard records. */
					_n( '%d archived local dashboard record. Archived records are retained for audit/history and are not polled.', '%d archived local dashboard records. Archived records are retained for audit/history and are not polled.', count( $sites ), 'alynt-drime-backups-dashboard' )
				),
				esc_html( number_format_i18n( count( $sites ) ) )
			);
		} else {
			printf(
				esc_html(
					/* translators: %d: number of dashboard sites. */
					_n( '%d paired client site. Status reflects its most recent redacted snapshot, not a live connection.', '%d paired client sites. Status reflects each site\'s most recent redacted snapshot, not a live connection.', count( $sites ), 'alynt-drime-backups-dashboard' )
				),
				esc_html( number_format_i18n( count( $sites ) ) )
			);
		}
		echo '</p>';

		$this->render_status_summary( $context['counts'], count( $sites ), $context['attention_count'] );
		$this->render_sites_table( $sites, $snapshots, $context['statuses'] );
		echo '<p class="description adbd-table-note">' . esc_html__( 'Check Now re-polls the site\'s fixed authenticated read-only status endpoint. It does not start, stop, or alter a backup.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</section>';
	}

	/**
	 * Renders the archived-records toggle.
	 *
	 * @param bool $show_archived Whether archived records are currently shown.
	 * @param int  $archived_count Archived record count.
	 * @return void
	 */
	private function render_archived_records_toggle( $show_archived, $archived_count ) {
		$url = add_query_arg(
			array(
				'page' => self::MENU_SLUG,
				'tab'  => 'sites',
			),
			admin_url( 'tools.php' )
		);

		if ( ! $show_archived ) {
			$url = add_query_arg( 'archived', '1', $url );
		}

		echo '<p class="adbd-view-toggle">';
		if ( $show_archived ) {
			echo '<a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Back to Active Sites', 'alynt-drime-backups-dashboard' ) . '</a>';
		} else {
			printf(
				'<a class="button" href="%1$s">%2$s</a>',
				esc_url( $url ),
				esc_html(
					sprintf(
						/* translators: %d: archived local dashboard record count. */
						_n( 'View Archived Record (%d)', 'View Archived Records (%d)', $archived_count, 'alynt-drime-backups-dashboard' ),
						(int) $archived_count
					)
				)
			);
		}
		echo '<span class="description">' . esc_html__( 'Archive is dashboard-local visibility only; it does not contact client sites, change backups, or alter Drime.', 'alynt-drime-backups-dashboard' ) . '</span></p>';
	}
}
