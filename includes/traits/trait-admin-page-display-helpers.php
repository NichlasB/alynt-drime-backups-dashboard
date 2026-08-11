<?php
/**
 * Admin page display helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides shared table, status, and formatting helpers for admin page sections.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Display_Helpers {
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
	 * Renders an item in a semantic detail list.
	 *
	 * @param string $label Label.
	 * @param string $value Value or safe markup.
	 * @param bool   $value_is_html Whether the value is already escaped markup.
	 * @return void
	 */
	private function render_detail_item( $label, $value, $value_is_html = false ) {
		echo '<div><dt>' . esc_html( $label ) . '</dt><dd>';
		echo $value_is_html ? $value : esc_html( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup callers pass output from escaping helpers only.
		echo '</dd></div>';
	}

	/**
	 * Renders a latest snapshot summary without raw payload output.
	 *
	 * @param array<string,mixed>|null $snapshot Snapshot row.
	 * @return void
	 */
	private function render_latest_snapshot_summary( $snapshot ) {
		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Latest Redacted Snapshot', 'alynt-drime-backups-dashboard' ) . '</h3>';

		if ( empty( $snapshot ) || ! is_array( $snapshot ) ) {
			echo '<div class="adbd-panel-body"><p>' . esc_html__( 'No redacted status snapshot has been stored for this site yet. This is expected while client opt-in or the first valid report is pending.', 'alynt-drime-backups-dashboard' ) . '</p></div></div>';
			return;
		}

		$payload = $this->decoded_snapshot_payload( $snapshot );

		echo '<dl class="adbd-detail-list adbd-evidence-list">';
		$this->render_detail_item( __( 'Observed', 'alynt-drime-backups-dashboard' ), $this->time_html( isset( $snapshot['observed_at'] ) ? $snapshot['observed_at'] : '' ), true );
		$this->render_detail_item( __( 'Schema version', 'alynt-drime-backups-dashboard' ), isset( $snapshot['schema_version'] ) ? (string) (int) $snapshot['schema_version'] : '-' );
		$this->render_detail_item( __( 'Queue', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'queue_count' ) );
		$this->render_detail_item( __( 'Uploaded', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'uploaded_count' ) );
		$this->render_detail_item( __( 'Failed', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'failed_count' ) );
		$this->render_detail_item( __( 'Warnings', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'warning_count' ) );
		$this->render_detail_item( __( 'Active upload', 'alynt-drime-backups-dashboard' ), ! empty( $payload['active_upload'] ) ? __( 'Yes', 'alynt-drime-backups-dashboard' ) : __( 'No', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_item( __( 'Cron status', 'alynt-drime-backups-dashboard' ), isset( $payload['cron_status'] ) && '' !== $payload['cron_status'] ? (string) $payload['cron_status'] : '-' );
		echo '</dl></div>';
	}

	/**
	 * Decodes a snapshot payload for safe summary fields.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return array<string,mixed>
	 */
	private function decoded_snapshot_payload( array $snapshot ) {
		if ( isset( $snapshot['decoded_payload'] ) && is_array( $snapshot['decoded_payload'] ) ) {
			return $snapshot['decoded_payload'];
		}

		if ( empty( $snapshot['payload_json'] ) ) {
			return array();
		}

		$decoded = json_decode( (string) $snapshot['payload_json'], true );

		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Gets a non-negative payload count.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $key Field key.
	 * @return string
	 */
	private function payload_count( array $payload, $key ) {
		return isset( $payload[ $key ] ) ? (string) max( 0, (int) $payload[ $key ] ) : '0';
	}

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
			} else {
				echo '<span class="description">' . esc_html__( 'No validated snapshot is stored yet.', 'alynt-drime-backups-dashboard' ) . '</span>';
			}

			echo '</td>';
			echo '<td data-label="' . esc_attr__( 'Freshness', 'alynt-drime-backups-dashboard' ) . '">' . $this->time_html( isset( $site['last_seen_at'] ) ? $site['last_seen_at'] : '' ) . '<span class="adbd-row-meta">' . esc_html__( 'Next poll:', 'alynt-drime-backups-dashboard' ) . ' ' . $this->time_html( isset( $site['next_poll_at'] ) ? $site['next_poll_at'] : '' ) . '</span></td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- time_html() returns escaped markup.
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

	/**
	 * Renders a manual read-only status-check form when credentials exist.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $site_id Site ID.
	 * @param bool                $primary Whether to use primary styling.
	 * @return void
	 */
	private function render_check_status_form( array $site, $site_id, $primary ) {
		if (
			empty( $site['polling_key_id'] )
			|| empty( $site['polling_secret_ciphertext'] )
			|| ( isset( $site['enrollment_status'] ) && 'revoked' === $site['enrollment_status'] )
		) {
			echo '<span class="description adbd-action-unavailable">' . esc_html__( 'Manual check unavailable until active pairing credentials exist.', 'alynt-drime-backups-dashboard' ) . '</span>';
			return;
		}

		?>
		<form method="post" class="adbd-inline-form">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_check_status_now' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="check_status_now">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
			<button type="submit" class="button <?php echo $primary ? 'button-primary' : ''; ?>" data-busy-label="<?php esc_attr_e( 'Checking…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Check Status Now', 'alynt-drime-backups-dashboard' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Renders a bounded recent snapshot history table.
	 *
	 * @param array<int,array<string,mixed>> $history Snapshot history.
	 * @return void
	 */
	private function render_recent_history( array $history ) {
		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Recent Status History', 'alynt-drime-backups-dashboard' ) . '</h3>';

		if ( empty( $history ) ) {
			echo '<div class="adbd-panel-body"><p>' . esc_html__( 'No status history is available yet.', 'alynt-drime-backups-dashboard' ) . '</p></div></div>';
			return;
		}

		echo '<div class="adbd-table-wrap"><table class="widefat striped adbd-history-table"><caption>' . esc_html__( 'The ten most recent retained redacted snapshots for this site', 'alynt-drime-backups-dashboard' ) . '</caption><thead><tr><th scope="col">' . esc_html__( 'Observed', 'alynt-drime-backups-dashboard' ) . '</th><th scope="col">' . esc_html__( 'Status', 'alynt-drime-backups-dashboard' ) . '</th><th scope="col">' . esc_html__( 'Evidence', 'alynt-drime-backups-dashboard' ) . '</th></tr></thead><tbody>';

		foreach ( $history as $row ) {
			$category = isset( $row['overall_status'] ) ? sanitize_key( $row['overall_status'] ) : '';
			$status   = array(
				'category' => $category,
				'label'    => $this->classifier->label( $category ),
			);
			$queue    = number_format_i18n( isset( $row['queue_count'] ) ? max( 0, (int) $row['queue_count'] ) : 0 );
			$uploaded = number_format_i18n( isset( $row['uploaded_count'] ) ? max( 0, (int) $row['uploaded_count'] ) : 0 );
			$failed   = number_format_i18n( isset( $row['failed_count'] ) ? max( 0, (int) $row['failed_count'] ) : 0 );
			$warnings = number_format_i18n( isset( $row['warning_count'] ) ? max( 0, (int) $row['warning_count'] ) : 0 );
			$cron     = isset( $row['cron_status'] ) && '' !== $row['cron_status'] ? $row['cron_status'] : '-';

			echo '<tr><td>' . $this->time_html( isset( $row['observed_at'] ) ? $row['observed_at'] : '' ) . '</td><td>' . $this->status_badge( $status ) . '</td><td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper markup is escaped.
			printf(
				/* translators: 1: queue count, 2: uploaded count, 3: failed count, 4: warning count, 5: cron status. */
				esc_html__( 'Queue %1$s; Uploaded %2$s; Failed %3$s; Warnings %4$s; Cron %5$s', 'alynt-drime-backups-dashboard' ),
				esc_html( $queue ),
				esc_html( $uploaded ),
				esc_html( $failed ),
				esc_html( $warnings ),
				esc_html( $cron )
			);
			echo '</td></tr>';
		}

		echo '</tbody></table></div></div>';
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
}
