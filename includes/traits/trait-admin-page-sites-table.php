<?php
/**
 * Admin page Sites table rendering.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Sites list table, empty state, and status summary metrics.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_Table {
	/**
	 * Renders a sites table.
	 *
	 * @param array<int,array<string,mixed>>  $sites Sites.
	 * @param array<int,array<string,mixed>>  $snapshots Snapshots keyed by site ID.
	 * @param array<int,array<string,string>> $statuses Statuses keyed by site ID.
	 * @return void
	 */
	private function render_sites_table( array $sites, array $snapshots, array $statuses = array() ) {
		echo '<div class="adbd-table-wrap"><table class="wp-list-table widefat striped adbd-sites-table">';
		echo '<caption>' . esc_html__( 'Paired client sites and their latest reported upload health', 'alynt-drime-backups-dashboard' ) . '</caption>';
		echo '<thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Site', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Status', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Backup Evidence', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Freshness', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Actions', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $sites as $site ) {
			$site_id  = (int) $site['id'];
			$snapshot = isset( $snapshots[ $site_id ] ) ? $snapshots[ $site_id ] : null;
			$status   = isset( $statuses[ $site_id ] ) ? $statuses[ $site_id ] : ( isset( $site['_dashboard_status'] ) ? $site['_dashboard_status'] : $this->classifier->classify( $site, $snapshot ) );
			$payload  = $snapshot ? $this->decoded_snapshot_payload( $snapshot ) : array();
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
				'<td data-label="%1$s"><a class="row-title" href="%2$s">%3$s</a><span class="adbd-origin">%4$s</span><span class="adbd-row-meta">%5$s</span></td>',
				esc_attr__( 'Site', 'alynt-drime-backups-dashboard' ),
				esc_url( $url ),
				esc_html( $this->site_name( $site ) ),
				esc_html( isset( $site['expected_origin'] ) ? $site['expected_origin'] : '' ),
				esc_html(
					sprintf(
						/* translators: 1: environment label, 2: enrollment label. */
						__( '%1$s · %2$s', 'alynt-drime-backups-dashboard' ),
						$this->environment_label( isset( $site['environment'] ) ? $site['environment'] : '' ),
						$this->enrollment_label( isset( $site['enrollment_status'] ) ? $site['enrollment_status'] : '' )
					)
				)
			);
			echo '<td data-label="' . esc_attr__( 'Status', 'alynt-drime-backups-dashboard' ) . '">' . $this->status_cell( $status ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- status_cell() returns escaped markup.
			echo '<td data-label="' . esc_attr__( 'Backup Evidence', 'alynt-drime-backups-dashboard' ) . '">';

			if ( $snapshot ) {
				echo '<div class="adbd-evidence"><span>' . esc_html__( 'Queue', 'alynt-drime-backups-dashboard' ) . ' <strong>' . esc_html( $this->payload_count( $payload, 'queue_count' ) ) . '</strong></span><span>' . esc_html__( 'Uploaded', 'alynt-drime-backups-dashboard' ) . ' <strong>' . esc_html( $this->payload_count( $payload, 'uploaded_count' ) ) . '</strong></span><span>' . esc_html__( 'Failed', 'alynt-drime-backups-dashboard' ) . ' <strong>' . esc_html( $this->payload_count( $payload, 'failed_count' ) ) . '</strong></span><span>' . esc_html__( 'Warnings', 'alynt-drime-backups-dashboard' ) . ' <strong>' . esc_html( $this->payload_count( $payload, 'warning_count' ) ) . '</strong></span></div>';
				echo '<span class="adbd-row-meta">' . esc_html__( 'Cron:', 'alynt-drime-backups-dashboard' ) . ' ' . esc_html( isset( $payload['cron_status'] ) && '' !== $payload['cron_status'] ? $payload['cron_status'] : __( 'Not reported', 'alynt-drime-backups-dashboard' ) ) . '</span>';
				echo $this->backup_sources_compact_html( $payload ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper returns escaped source-summary markup.
			} else {
				echo '<span class="description">' . esc_html__( 'No validated snapshot is stored yet.', 'alynt-drime-backups-dashboard' ) . '</span>';
			}

			echo '</td>';
			echo '<td data-label="' . esc_attr__( 'Freshness', 'alynt-drime-backups-dashboard' ) . '">' . $this->time_html( isset( $site['last_seen_at'] ) ? $site['last_seen_at'] : '' ) . $this->next_poll_html( $site ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- time_html() and next_poll_html() return escaped markup.
			echo '<td data-label="' . esc_attr__( 'Actions', 'alynt-drime-backups-dashboard' ) . '"><div class="adbd-row-actions"><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'View', 'alynt-drime-backups-dashboard' ) . '</a>';
			$this->render_check_status_form( $site, $site_id, false );
			echo '</div></td>';
			echo '</tr>';
		}

		echo '</tbody></table></div>';
	}

	/**
	 * Renders the empty state.
	 *
	 * @return void
	 */
	private function render_empty_state() {
		$url = add_query_arg(
			array(
				'page' => self::MENU_SLUG,
				'tab'  => 'add-site',
			),
			admin_url( 'tools.php' )
		);

		echo '<div class="adbd-empty-state"><span class="dashicons dashicons-admin-site-alt3" aria-hidden="true"></span><h3>' . esc_html__( 'No Client Sites Are Paired Yet', 'alynt-drime-backups-dashboard' ) . '</h3><p>' . esc_html__( 'Pairing starts here and finishes on the client site. Until a client-site administrator confirms the opt-in, no status is collected.', 'alynt-drime-backups-dashboard' ) . '</p><ol class="adbd-steps"><li>' . esc_html__( 'Add the site here and generate a display-once pairing token.', 'alynt-drime-backups-dashboard' ) . '</li><li>' . esc_html__( 'The client-site administrator pastes the token into the uploader and opts in.', 'alynt-drime-backups-dashboard' ) . '</li><li>' . esc_html__( 'This dashboard begins polling the fixed read-only endpoint on its scheduled interval.', 'alynt-drime-backups-dashboard' ) . '</li></ol><a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Add Site', 'alynt-drime-backups-dashboard' ) . '</a></div>';
	}

	/**
	 * Renders the compact Sites status summary.
	 *
	 * @param array<string,int> $counts Status counts.
	 * @param int               $total Total sites.
	 * @param int               $attention Attention count.
	 * @return void
	 */
	private function render_status_summary( array $counts, $total, $attention ) {
		$metrics = array(
			array( __( 'Total Sites', 'alynt-drime-backups-dashboard' ), $total, 'neutral' ),
			array( __( 'Working', 'alynt-drime-backups-dashboard' ), isset( $counts['working'] ) ? $counts['working'] : 0, 'success' ),
			array( __( 'Attention', 'alynt-drime-backups-dashboard' ), $attention, 'danger' ),
			array( __( 'Pending', 'alynt-drime-backups-dashboard' ), isset( $counts['pending'] ) ? $counts['pending'] : 0, 'neutral' ),
			array( __( 'Paused', 'alynt-drime-backups-dashboard' ), isset( $counts['paused'] ) ? $counts['paused'] : 0, 'neutral' ),
		);

		echo '<div class="adbd-metrics" aria-label="' . esc_attr__( 'Site status summary', 'alynt-drime-backups-dashboard' ) . '">';

		foreach ( $metrics as $metric ) {
			echo '<div class="adbd-metric is-' . esc_attr( $metric[2] ) . '"><strong>' . esc_html( number_format_i18n( (int) $metric[1] ) ) . '</strong><span>' . esc_html( $metric[0] ) . '</span></div>';
		}

		echo '</div>';
	}
}
