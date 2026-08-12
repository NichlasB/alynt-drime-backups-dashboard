<?php
/**
 * Admin page Attention shell.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the prioritized attention queue for paired sites.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Attention {
	/**
	 * Renders the Attention shell.
	 *
	 * @return void
	 */
	private function render_attention_shell() {
		$context   = $this->site_status_context();
		$sites     = $context['sites'];
		$snapshots = $context['snapshots'];
		$attention = array();

		foreach ( $sites as $site ) {
			$site_id = (int) $site['id'];
			$status  = isset( $context['statuses'][ $site_id ] ) ? $context['statuses'][ $site_id ] : array();

			if ( in_array( $status['category'], array( 'incompatible', 'not_reporting', 'needs_attention', 'not_configured' ), true ) ) {
				$site['_dashboard_status'] = $status;
				$attention[]               = $site;
			}
		}

		$priority = array(
			'needs_attention' => 1,
			'incompatible'    => 2,
			'not_reporting'   => 3,
			'not_configured'  => 4,
		);

		usort(
			$attention,
			static function ( $left, $right ) use ( $priority ) {
				$left_status  = isset( $left['_dashboard_status']['category'] ) ? $left['_dashboard_status']['category'] : '';
				$right_status = isset( $right['_dashboard_status']['category'] ) ? $right['_dashboard_status']['category'] : '';
				$comparison   = ( isset( $priority[ $left_status ] ) ? $priority[ $left_status ] : 99 ) <=> ( isset( $priority[ $right_status ] ) ? $priority[ $right_status ] : 99 );

				return 0 !== $comparison ? $comparison : strcasecmp( isset( $left['site_label'] ) ? $left['site_label'] : '', isset( $right['site_label'] ) ? $right['site_label'] : '' );
			}
		);

		echo '<section aria-labelledby="adbd-attention-heading">';
		echo '<h2 id="adbd-attention-heading">' . esc_html__( 'Attention', 'alynt-drime-backups-dashboard' ) . '</h2>';

		if ( empty( $attention ) ) {
			echo '<div class="adbd-empty-state"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><h3>' . esc_html__( 'No sites currently need attention', 'alynt-drime-backups-dashboard' ) . '</h3><p>' . esc_html__( 'No latest local snapshot is classified as needs attention, not reporting, incompatible, or not configured.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
			echo '</section>';
			return;
		}

		printf(
			'<p class="adbd-screen-intro">%s</p>',
			esc_html(
				sprintf(
					/* translators: %d: number of sites needing attention. */
					_n( '%d site needs someone to review its latest evidence.', '%d sites need someone to review their latest evidence. The list is ordered by status priority.', count( $attention ), 'alynt-drime-backups-dashboard' ),
					count( $attention )
				)
			)
		);
		echo '<ol class="adbd-attention-list">';

		foreach ( $attention as $site ) {
			$site_id  = (int) $site['id'];
			$status   = $site['_dashboard_status'];
			$snapshot = isset( $snapshots[ $site_id ] ) ? $snapshots[ $site_id ] : null;
			$url      = add_query_arg(
				array(
					'page'    => self::MENU_SLUG,
					'tab'     => 'site',
					'site_id' => $site_id,
				),
				admin_url( 'tools.php' )
			);

			echo '<li><div class="adbd-attention-heading"><a href="' . esc_url( $url ) . '">' . esc_html( $this->site_name( $site ) ) . '</a>' . $this->status_badge( $status ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge is escaped by status_badge().
			echo '<p class="adbd-origin">' . esc_html( isset( $site['expected_origin'] ) ? $site['expected_origin'] : '' ) . '</p>';
			echo '<p><strong>' . esc_html( isset( $status['message'] ) ? $status['message'] : '' ) . '</strong></p>';
			echo '<p class="description">' . esc_html( $this->status_guidance( $status['category'] ) ) . '</p>';
			echo '<p class="adbd-attention-freshness">' . esc_html__( 'Latest evidence:', 'alynt-drime-backups-dashboard' ) . ' ';
			echo $this->time_html( $snapshot && isset( $snapshot['observed_at'] ) ? $snapshot['observed_at'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- time_html() returns escaped markup.
			echo '</p><div class="adbd-actions"><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'View Site', 'alynt-drime-backups-dashboard' ) . '</a>';
			$this->render_check_status_form( $site, $site_id, false );
			echo '</div></li>';
		}

		echo '</ol></section>';
	}
}
