<?php
/**
 * Admin page site rendering.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders dashboard site sections and shared display helpers.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Sites {
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
		$submitted = is_wp_error( $result ) ? $this->submitted_pending_site_values() : array();
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
					<td><input type="text" id="alynt-dashboard-site-label" name="alynt_drime_backups_dashboard_pending_site[site_label]" class="regular-text" required="required" value="<?php echo esc_attr( isset( $submitted['site_label'] ) ? $submitted['site_label'] : '' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="alynt-dashboard-expected-origin"><?php esc_html_e( 'Expected client origin', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td>
						<input type="url" id="alynt-dashboard-expected-origin" name="alynt_drime_backups_dashboard_pending_site[expected_origin]" class="regular-text" required="required" placeholder="https://example.com" value="<?php echo esc_attr( isset( $submitted['expected_origin'] ) ? $submitted['expected_origin'] : '' ); ?>" />
						<p class="description"><?php esc_html_e( 'Public HTTPS origin only. The status endpoint path is fixed by the v1 protocol.', 'alynt-drime-backups-dashboard' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="alynt-dashboard-environment"><?php esc_html_e( 'Environment', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td>
						<select id="alynt-dashboard-environment" name="alynt_drime_backups_dashboard_pending_site[environment]">
							<option value="production" <?php selected( isset( $submitted['environment'] ) ? $submitted['environment'] : '', 'production' ); ?>><?php esc_html_e( 'Production', 'alynt-drime-backups-dashboard' ); ?></option>
							<option value="staging" <?php selected( isset( $submitted['environment'] ) ? $submitted['environment'] : '', 'staging' ); ?>><?php esc_html_e( 'Staging', 'alynt-drime-backups-dashboard' ); ?></option>
							<option value="development" <?php selected( isset( $submitted['environment'] ) ? $submitted['environment'] : '', 'development' ); ?>><?php esc_html_e( 'Development', 'alynt-drime-backups-dashboard' ); ?></option>
							<option value="other" <?php selected( isset( $submitted['environment'] ) ? $submitted['environment'] : '', 'other' ); ?>><?php esc_html_e( 'Other', 'alynt-drime-backups-dashboard' ); ?></option>
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
			echo '<p>' . esc_html__( 'No client sites currently need attention based on the latest local dashboard evidence.', 'alynt-drime-backups-dashboard' ) . '</p>';
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

		echo '<h3>' . esc_html__( 'Polling evidence', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped"><tbody>';
		$this->render_detail_row( __( 'Polling credential state', 'alynt-drime-backups-dashboard' ), $this->credential_state( $site ) );
		$this->render_detail_row( __( 'Last poll attempt', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( isset( $site['last_poll_attempt_at'] ) ? $site['last_poll_attempt_at'] : '' ) );
		$this->render_detail_row( __( 'Next scheduled poll', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( isset( $site['next_poll_at'] ) ? $site['next_poll_at'] : '' ) );
		$this->render_detail_row( __( 'Consecutive failures', 'alynt-drime-backups-dashboard' ), isset( $site['consecutive_failures'] ) ? (string) max( 0, (int) $site['consecutive_failures'] ) : '0' );
		$this->render_detail_row( __( 'Last safe error', 'alynt-drime-backups-dashboard' ), $this->safe_error_label( $site ) );
		$this->render_detail_row( __( 'Stored snapshots', 'alynt-drime-backups-dashboard' ), (string) $this->snapshots->count_for_site( $site_id ) );
		echo '</tbody></table>';

		$this->render_latest_snapshot_summary( $snapshot );

		if ( ! isset( $site['enrollment_status'] ) || 'revoked' !== $site['enrollment_status'] ) {
			?>
			<?php if ( ! empty( $site['polling_key_id'] ) && ! empty( $site['polling_secret_ciphertext'] ) ) : ?>
				<form method="post">
					<?php wp_nonce_field( 'alynt_drime_backups_dashboard_check_status_now' ); ?>
					<input type="hidden" name="alynt_drime_backups_dashboard_action" value="check_status_now">
					<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
					<p><button type="submit" class="button button-primary" onclick="this.setAttribute('aria-busy','true');this.disabled=true;this.textContent='<?php echo esc_js( __( 'Checking...', 'alynt-drime-backups-dashboard' ) ); ?>';this.form.submit();"><?php esc_html_e( 'Check Status Now', 'alynt-drime-backups-dashboard' ); ?></button></p>
				</form>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'alynt_drime_backups_dashboard_revoke_local' ); ?>
				<input type="hidden" name="alynt_drime_backups_dashboard_action" value="revoke_local">
				<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
				<p><button type="submit" class="button" onclick="return window.confirm('<?php echo esc_js( __( 'Revoke this local dashboard record? This removes the local pairing and polling credential state, but does not contact the client site or Drime.', 'alynt-drime-backups-dashboard' ) ); ?>');"><?php esc_html_e( 'Revoke Local Dashboard Record', 'alynt-drime-backups-dashboard' ); ?></button></p>
			</form>
			<?php
		}
	}

	/**
	 * Gets sanitized Add Site values from a failed POST for input preservation.
	 *
	 * @return array<string,string>
	 */
	private function submitted_pending_site_values() {
		if (
			! isset( $_POST['_wpnonce'] )
			|| ! function_exists( 'wp_verify_nonce' )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'alynt_drime_backups_dashboard_create_pending_site' )
		) {
			return array();
		}

		$raw = isset( $_POST['alynt_drime_backups_dashboard_pending_site'] ) ? wp_unslash( $_POST['alynt_drime_backups_dashboard_pending_site'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! is_array( $raw ) ) {
			return array();
		}

		return array(
			'site_label'      => isset( $raw['site_label'] ) ? sanitize_text_field( (string) $raw['site_label'] ) : '',
			'expected_origin' => isset( $raw['expected_origin'] ) ? esc_url_raw( (string) $raw['expected_origin'] ) : '',
			'environment'     => isset( $raw['environment'] ) ? sanitize_key( (string) $raw['environment'] ) : '',
		);
	}
}
