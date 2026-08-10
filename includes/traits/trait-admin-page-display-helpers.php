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
	 * Renders a latest snapshot summary without raw payload output.
	 *
	 * @param array<string,mixed>|null $snapshot Snapshot row.
	 * @return void
	 */
	private function render_latest_snapshot_summary( $snapshot ) {
		echo '<h3>' . esc_html__( 'Latest redacted snapshot summary', 'alynt-drime-backups-dashboard' ) . '</h3>';

		if ( empty( $snapshot ) || ! is_array( $snapshot ) ) {
			echo '<p>' . esc_html__( 'No redacted status snapshot has been stored for this site yet.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		$payload = $this->decoded_snapshot_payload( $snapshot );

		echo '<table class="widefat striped"><tbody>';
		$this->render_detail_row( __( 'Observed', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( isset( $snapshot['observed_at'] ) ? $snapshot['observed_at'] : '' ) );
		$this->render_detail_row( __( 'Schema version', 'alynt-drime-backups-dashboard' ), isset( $snapshot['schema_version'] ) ? (string) (int) $snapshot['schema_version'] : '-' );
		$this->render_detail_row( __( 'Queue count', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'queue_count' ) );
		$this->render_detail_row( __( 'Uploaded count', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'uploaded_count' ) );
		$this->render_detail_row( __( 'Failed count', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'failed_count' ) );
		$this->render_detail_row( __( 'Warnings', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'warning_count' ) );
		$this->render_detail_row( __( 'Cron status', 'alynt-drime-backups-dashboard' ), isset( $payload['cron_status'] ) ? (string) $payload['cron_status'] : '-' );
		echo '</tbody></table>';
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
		echo '<th scope="col">' . esc_html__( 'Next poll', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Failures', 'alynt-drime-backups-dashboard' ) . '</th>';
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
			echo '<td>' . $this->status_cell( $status ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<td>' . esc_html( $this->date_or_dash( isset( $site['last_seen_at'] ) ? $site['last_seen_at'] : '' ) ) . '</td>';
			echo '<td>' . esc_html( $this->date_or_dash( isset( $site['next_poll_at'] ) ? $site['next_poll_at'] : '' ) ) . '</td>';
			echo '<td>' . esc_html( isset( $site['consecutive_failures'] ) ? (string) max( 0, (int) $site['consecutive_failures'] ) : '0' ) . '</td>';
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
}
