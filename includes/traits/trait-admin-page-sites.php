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
		$context   = $this->site_status_context();
		$sites     = $context['sites'];
		$snapshots = $context['snapshots'];

		echo '<section aria-labelledby="adbd-sites-heading">';
		echo '<h2 id="adbd-sites-heading">' . esc_html__( 'Sites', 'alynt-drime-backups-dashboard' ) . '</h2>';

		if ( empty( $sites ) ) {
			$this->render_empty_state();
			echo '</section>';
			return;
		}

		echo '<p class="adbd-screen-intro">';
		printf(
			esc_html(
				/* translators: %d: number of dashboard sites. */
				_n( '%d paired client site. Status reflects its most recent redacted snapshot, not a live connection.', '%d paired client sites. Status reflects each site\'s most recent redacted snapshot, not a live connection.', count( $sites ), 'alynt-drime-backups-dashboard' )
			),
			esc_html( number_format_i18n( count( $sites ) ) )
		);
		echo '</p>';

		$this->render_status_summary( $context['counts'], count( $sites ), $context['attention_count'] );
		$this->render_sites_table( $sites, $snapshots, $context['statuses'] );
		echo '<p class="description adbd-table-note">' . esc_html__( 'Check Status Now re-polls the site\'s fixed authenticated read-only status endpoint. It does not start, stop, or alter a backup.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</section>';
	}

	/**
	 * Renders the Add Site shell.
	 *
	 * @param array<string,mixed>|WP_Error|null $result Action result.
	 * @return void
	 */
	private function render_add_site_shell( $result = null ) {
		$submitted = is_wp_error( $result ) ? $this->submitted_pending_site_values() : array();
		$sites_url = add_query_arg(
			array(
				'page' => self::MENU_SLUG,
				'tab'  => 'sites',
			),
			admin_url( 'tools.php' )
		);
		?>
		<section class="adbd-narrow-screen" aria-labelledby="adbd-add-site-heading">
		<h2 id="adbd-add-site-heading"><?php esc_html_e( 'Add Site', 'alynt-drime-backups-dashboard' ); ?></h2>
		<?php if ( is_array( $result ) && isset( $result['pairing_token'] ) ) : ?>
			<p class="adbd-screen-intro"><?php esc_html_e( 'The local pending record was created. It stays Pending until an administrator on the client site explicitly opts in.', 'alynt-drime-backups-dashboard' ); ?></p>
			<div class="adbd-panel adbd-token-panel">
				<h3><?php esc_html_e( 'Pairing Token — Shown Once', 'alynt-drime-backups-dashboard' ); ?></h3>
				<div class="adbd-panel-body">
					<p><?php esc_html_e( 'Copy this token now. Before enrollment, the dashboard stores only a verifier derived from it and cannot show it again. If it is lost, generate a new token; the old one then stops working.', 'alynt-drime-backups-dashboard' ); ?></p>
					<label for="adbd-pairing-token"><strong><?php esc_html_e( 'Pairing token', 'alynt-drime-backups-dashboard' ); ?></strong></label>
					<div class="adbd-copy-row">
						<input id="adbd-pairing-token" class="large-text code" type="text" readonly="readonly" value="<?php echo esc_attr( (string) $result['pairing_token'] ); ?>" aria-describedby="adbd-pairing-token-help">
						<button type="button" class="button button-primary adbd-copy-button" hidden data-copy-target="adbd-pairing-token" data-success-message="<?php esc_attr_e( 'Pairing token copied to the clipboard.', 'alynt-drime-backups-dashboard' ); ?>" data-error-message="<?php esc_attr_e( 'The token could not be copied automatically. Select the field and copy it manually.', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Copy Token', 'alynt-drime-backups-dashboard' ); ?></button>
					</div>
					<p id="adbd-pairing-token-help" class="description"><?php esc_html_e( 'This token is single use and is not a Drime API token.', 'alynt-drime-backups-dashboard' ); ?></p>
					<p class="adbd-copy-status" role="status" aria-live="polite"></p>
					<dl class="adbd-detail-list">
						<div><dt><?php esc_html_e( 'Expires', 'alynt-drime-backups-dashboard' ); ?></dt><dd><?php echo esc_html( (string) $result['pairing_expires_at'] ); ?></dd></div>
						<div><dt><?php esc_html_e( 'Expected client origin', 'alynt-drime-backups-dashboard' ); ?></dt><dd><?php echo esc_html( (string) $result['expected_origin'] ); ?></dd></div>
						<div><dt><?php esc_html_e( 'Endpoint that will be polled', 'alynt-drime-backups-dashboard' ); ?></dt><dd><code><?php echo esc_html( (string) $result['status_endpoint_preview'] ); ?></code></dd></div>
					</dl>
				</div>
			</div>
			<div class="adbd-panel">
				<h3><?php esc_html_e( 'Next Steps on the Client Site', 'alynt-drime-backups-dashboard' ); ?></h3>
				<div class="adbd-panel-body">
					<ol class="adbd-steps">
						<li><?php esc_html_e( 'Send the token to an administrator of the client site through a channel you already trust.', 'alynt-drime-backups-dashboard' ); ?></li>
						<li><?php esc_html_e( 'They paste it into the Alynt Drime Backups Uploader pairing field.', 'alynt-drime-backups-dashboard' ); ?></li>
						<li><?php esc_html_e( 'They review the dashboard origin and explicitly confirm the opt-in.', 'alynt-drime-backups-dashboard' ); ?></li>
						<li><?php esc_html_e( 'This dashboard starts polling only the fixed authenticated read-only status endpoint.', 'alynt-drime-backups-dashboard' ); ?></li>
					</ol>
				</div>
			</div>
			<p class="adbd-actions"><a class="button button-primary" href="<?php echo esc_url( $sites_url ); ?>"><?php esc_html_e( 'Done, Back to Sites', 'alynt-drime-backups-dashboard' ); ?></a><a class="button" href="
			<?php
			echo esc_url(
				add_query_arg(
					array(
						'page' => self::MENU_SLUG,
						'tab'  => 'add-site',
					),
					admin_url( 'tools.php' )
				)
			);
			?>
																			"><?php esc_html_e( 'Add Another Site', 'alynt-drime-backups-dashboard' ); ?></a></p>
		<?php else : ?>
			<p class="adbd-screen-intro"><?php esc_html_e( 'Describe the client site, then generate a short-lived pairing token. Generating it does not contact the client site or Drime. Nothing is collected until a client-site administrator confirms the opt-in.', 'alynt-drime-backups-dashboard' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_create_pending_site' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="create_pending_site">
			<div class="adbd-panel adbd-form-panel"><table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="alynt-dashboard-site-label"><?php esc_html_e( 'Site label', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td><input type="text" id="alynt-dashboard-site-label" name="alynt_drime_backups_dashboard_pending_site[site_label]" class="regular-text" required="required" aria-describedby="alynt-dashboard-site-label-help" value="<?php echo esc_attr( isset( $submitted['site_label'] ) ? $submitted['site_label'] : '' ); ?>"><p id="alynt-dashboard-site-label-help" class="description"><?php esc_html_e( 'Used in this dashboard only. It is never sent to the client site.', 'alynt-drime-backups-dashboard' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><label for="alynt-dashboard-expected-origin"><?php esc_html_e( 'Expected public HTTPS origin', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td>
						<input type="url" id="alynt-dashboard-expected-origin" name="alynt_drime_backups_dashboard_pending_site[expected_origin]" class="regular-text" required="required" aria-describedby="alynt-dashboard-expected-origin-help" placeholder="https://example.com" value="<?php echo esc_attr( isset( $submitted['expected_origin'] ) ? $submitted['expected_origin'] : '' ); ?>">
						<p id="alynt-dashboard-expected-origin-help" class="description"><?php esc_html_e( 'Scheme and host only, with no path. HTTPS is required, and polling is refused if enrollment reports a different origin.', 'alynt-drime-backups-dashboard' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="alynt-dashboard-environment"><?php esc_html_e( 'Environment', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td>
						<select id="alynt-dashboard-environment" name="alynt_drime_backups_dashboard_pending_site[environment]" aria-describedby="alynt-dashboard-environment-help">
							<option value="production" <?php selected( isset( $submitted['environment'] ) ? $submitted['environment'] : '', 'production' ); ?>><?php esc_html_e( 'Production', 'alynt-drime-backups-dashboard' ); ?></option>
							<option value="staging" <?php selected( isset( $submitted['environment'] ) ? $submitted['environment'] : '', 'staging' ); ?>><?php esc_html_e( 'Staging', 'alynt-drime-backups-dashboard' ); ?></option>
							<option value="development" <?php selected( isset( $submitted['environment'] ) ? $submitted['environment'] : '', 'development' ); ?>><?php esc_html_e( 'Development', 'alynt-drime-backups-dashboard' ); ?></option>
							<option value="other" <?php selected( isset( $submitted['environment'] ) ? $submitted['environment'] : '', 'other' ); ?>><?php esc_html_e( 'Other', 'alynt-drime-backups-dashboard' ); ?></option>
						</select>
						<p id="alynt-dashboard-environment-help" class="description"><?php esc_html_e( 'Labels the site in this dashboard. It does not change polling behavior.', 'alynt-drime-backups-dashboard' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Status endpoint', 'alynt-drime-backups-dashboard' ); ?></th>
					<td><code class="adbd-endpoint-preview" data-empty-label="<?php esc_attr_e( 'Enter an HTTPS origin to preview the fixed endpoint.', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Enter an HTTPS origin to preview the fixed endpoint.', 'alynt-drime-backups-dashboard' ); ?></code><p class="description"><?php esc_html_e( 'The path is fixed and cannot be edited. The dashboard polls this authenticated GET endpoint and nothing else.', 'alynt-drime-backups-dashboard' ); ?></p></td>
				</tr>
			</table>
			</div>
			<div class="notice notice-info inline adbd-token-explainer"><p><strong><?php esc_html_e( 'The pairing token is not a Drime API token.', 'alynt-drime-backups-dashboard' ); ?></strong> <?php esc_html_e( 'It permits one client site to opt in to publishing its own redacted upload status. It grants no access to Drime, storage, or backup files.', 'alynt-drime-backups-dashboard' ); ?></p></div>
			<p class="adbd-actions"><button type="submit" class="button button-primary" data-busy-label="<?php esc_attr_e( 'Generating…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Generate Pairing Token', 'alynt-drime-backups-dashboard' ); ?></button><a class="button-link" href="<?php echo esc_url( $sites_url ); ?>"><?php esc_html_e( 'Cancel', 'alynt-drime-backups-dashboard' ); ?></a><span class="description"><?php esc_html_e( 'The token is shown once and expires after 15 minutes.', 'alynt-drime-backups-dashboard' ); ?></span></p>
		</form>
		<?php endif; ?>
		</section>
		<?php
	}

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

	/**
	 * Renders one site detail shell.
	 *
	 * @return void
	 */
	private function render_site_detail_shell() {
		$site_id   = isset( $_GET['site_id'] ) ? absint( wp_unslash( $_GET['site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$site      = $site_id > 0 ? $this->sites->get( $site_id ) : null;
		$snapshot  = $site ? $this->snapshots->latest_for_site( $site_id ) : null;
		$sites_url = add_query_arg(
			array(
				'page' => self::MENU_SLUG,
				'tab'  => 'sites',
			),
			admin_url( 'tools.php' )
		);

		echo '<section aria-labelledby="adbd-site-detail-heading">';
		echo '<p class="adbd-back-link"><a href="' . esc_url( $sites_url ) . '">&larr; ' . esc_html__( 'Back to Sites', 'alynt-drime-backups-dashboard' ) . '</a></p>';

		if ( ! $site ) {
			echo '<h2 id="adbd-site-detail-heading">' . esc_html__( 'Site Detail', 'alynt-drime-backups-dashboard' ) . '</h2>';
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'No dashboard site record was found. Return to Sites and choose an existing record.', 'alynt-drime-backups-dashboard' ) . '</p></div></section>';
			return;
		}

		$status         = $this->classifier->classify( $site, $snapshot );
		$endpoint       = rtrim( isset( $site['expected_origin'] ) ? (string) $site['expected_origin'] : '', '/' ) . '/wp-json/alynt-drime-backups-uploader/v1/status';
		$history        = $this->snapshots->recent_for_site( $site_id, 10 );
		$confirm_revoke = isset( $_GET['confirm_revoke'] ) && '1' === sanitize_key( wp_unslash( $_GET['confirm_revoke'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only presentation state.
		$detail_url     = add_query_arg(
			array(
				'page'    => self::MENU_SLUG,
				'tab'     => 'site',
				'site_id' => $site_id,
			),
			admin_url( 'tools.php' )
		);

		echo '<div class="adbd-detail-title"><h2 id="adbd-site-detail-heading">' . esc_html( $this->site_name( $site ) ) . '</h2>' . $this->status_badge( $status ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge is escaped by status_badge().
		echo '<p class="adbd-site-meta"><span>' . esc_html( isset( $site['expected_origin'] ) ? $site['expected_origin'] : '' ) . '</span><span>' . esc_html( $this->environment_label( isset( $site['environment'] ) ? $site['environment'] : '' ) ) . '</span><span>' . esc_html( isset( $site['plugin_version'] ) && '' !== $site['plugin_version'] ? sprintf( /* translators: %s: uploader plugin version. */ __( 'Uploader %s', 'alynt-drime-backups-dashboard' ), $site['plugin_version'] ) : __( 'Uploader version unknown', 'alynt-drime-backups-dashboard' ) ) . '</span></p>';
		echo '<div class="notice notice-' . esc_attr( $this->status_notice_tone( $status['category'] ) ) . ' inline adbd-status-summary"><p><strong>' . esc_html( $status['message'] ) . '</strong></p><p>' . esc_html( $this->status_guidance( $status['category'] ) ) . '</p></div>';
		echo '<div class="adbd-actions">';
		$this->render_check_status_form( $site, $site_id, true );
		echo '<span class="description">' . esc_html__( 'A manual check reads the same fixed endpoint used by scheduled polling and cannot change a backup.', 'alynt-drime-backups-dashboard' ) . '</span></div>';

		echo '<div class="adbd-panel-grid">';
		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Enrollment and Identity', 'alynt-drime-backups-dashboard' ) . '</h3><dl class="adbd-detail-list">';
		$this->render_detail_item( __( 'Enrollment state', 'alynt-drime-backups-dashboard' ), $this->enrollment_label( isset( $site['enrollment_status'] ) ? $site['enrollment_status'] : '' ) );
		$this->render_detail_item( __( 'Expected origin', 'alynt-drime-backups-dashboard' ), isset( $site['expected_origin'] ) ? $site['expected_origin'] : '' );
		$this->render_detail_item( __( 'Fixed status endpoint', 'alynt-drime-backups-dashboard' ), '<code>' . esc_html( $endpoint ) . '</code>', true );
		$this->render_detail_item( __( 'Status schema', 'alynt-drime-backups-dashboard' ), isset( $site['payload_schema_version'] ) && '' !== $site['payload_schema_version'] ? (string) (int) $site['payload_schema_version'] : '-' );
		echo '</dl></div>';
		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Polling and Credential State', 'alynt-drime-backups-dashboard' ) . '</h3><dl class="adbd-detail-list">';
		$this->render_detail_item( __( 'Credential state', 'alynt-drime-backups-dashboard' ), $this->credential_state( $site ) );
		$this->render_detail_item( __( 'Last report received', 'alynt-drime-backups-dashboard' ), $this->time_html( isset( $site['last_seen_at'] ) ? $site['last_seen_at'] : '' ), true );
		$this->render_detail_item( __( 'Last poll attempt', 'alynt-drime-backups-dashboard' ), $this->time_html( isset( $site['last_poll_attempt_at'] ) ? $site['last_poll_attempt_at'] : '' ), true );
		$this->render_detail_item( __( 'Next scheduled poll', 'alynt-drime-backups-dashboard' ), $this->time_html( isset( $site['next_poll_at'] ) ? $site['next_poll_at'] : '' ), true );
		$this->render_detail_item( __( 'Consecutive failures', 'alynt-drime-backups-dashboard' ), isset( $site['consecutive_failures'] ) ? (string) max( 0, (int) $site['consecutive_failures'] ) : '0' );
		$this->render_detail_item( __( 'Last safe error', 'alynt-drime-backups-dashboard' ), $this->safe_error_label( $site ) );
		echo '</dl></div></div>';

		$this->render_latest_snapshot_summary( $snapshot );
		$this->render_recent_history( $history );

		echo '<div class="adbd-panel adbd-privacy-panel"><h3>' . esc_html__( 'Credential and Privacy Boundary', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body"><p>' . esc_html__( 'Before enrollment, only a verifier for the display-once pairing token is stored. After enrollment, encrypted per-site polling credential material is stored, but its plaintext is never displayed.', 'alynt-drime-backups-dashboard' ) . '</p><p>' . esc_html__( 'This screen never shows pairing tokens, polling secrets, authorization headers, raw response bodies, filesystem paths, SQL, cookies, nonces, salts, or Drime credentials.', 'alynt-drime-backups-dashboard' ) . '</p></div></div>';

		if ( ! isset( $site['enrollment_status'] ) || 'revoked' !== $site['enrollment_status'] ) {
			echo '<div class="adbd-panel adbd-danger-panel"><h3>' . esc_html__( 'Revoke Local Dashboard Record', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body"><p>' . esc_html__( 'Revocation marks this local record revoked, clears its pairing and polling credential fields and next-poll state, and stops future polling. Existing snapshots remain under the normal 30-day retention cleanup. It does not contact the client site or Drime.', 'alynt-drime-backups-dashboard' ) . '</p>';

			if ( $confirm_revoke ) {
				echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Confirm local revocation.', 'alynt-drime-backups-dashboard' ) . '</strong> ' . esc_html__( 'This local credential state cannot be recovered; pairing again requires a new token and client opt-in.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
				?>
				<form method="post" class="adbd-actions">
					<?php wp_nonce_field( 'alynt_drime_backups_dashboard_revoke_local' ); ?>
					<input type="hidden" name="alynt_drime_backups_dashboard_action" value="revoke_local">
					<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
					<button type="submit" class="button adbd-button-danger" data-busy-label="<?php esc_attr_e( 'Revoking…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Confirm Local Revocation', 'alynt-drime-backups-dashboard' ); ?></button>
					<a class="button" href="<?php echo esc_url( $detail_url ); ?>"><?php esc_html_e( 'Cancel', 'alynt-drime-backups-dashboard' ); ?></a>
				</form>
				<?php
			} else {
				$confirm_url = add_query_arg( 'confirm_revoke', '1', $detail_url );
				echo '<p><a class="button adbd-button-danger" href="' . esc_url( $confirm_url ) . '">' . esc_html__( 'Review Local Revocation', 'alynt-drime-backups-dashboard' ) . '</a></p>';
			}

			echo '</div></div>';
		}

		echo '</section>';
	}

	/**
	 * Gets the request-local site, snapshot, and classification context.
	 *
	 * @return array<string,mixed>
	 */
	private function site_status_context() {
		if ( is_array( $this->site_status_context ) ) {
			return $this->site_status_context;
		}

		$sites     = $this->sites->all();
		$snapshots = $this->snapshots->latest_by_site_ids( wp_list_pluck( $sites, 'id' ) );
		$statuses  = array();
		$counts    = array(
			'working'         => 0,
			'pending'         => 0,
			'paused'          => 0,
			'incompatible'    => 0,
			'not_reporting'   => 0,
			'needs_attention' => 0,
			'not_configured'  => 0,
		);

		foreach ( $sites as $site ) {
			$site_id              = (int) $site['id'];
			$snapshot             = isset( $snapshots[ $site_id ] ) ? $snapshots[ $site_id ] : null;
			$status               = $this->classifier->classify( $site, $snapshot );
			$statuses[ $site_id ] = $status;

			if ( isset( $counts[ $status['category'] ] ) ) {
				++$counts[ $status['category'] ];
			}
		}

		$attention_count = $counts['incompatible'] + $counts['not_reporting'] + $counts['needs_attention'] + $counts['not_configured'];

		$this->site_status_context = array(
			'sites'           => $sites,
			'snapshots'       => $snapshots,
			'statuses'        => $statuses,
			'counts'          => $counts,
			'attention_count' => $attention_count,
		);

		return $this->site_status_context;
	}

	/**
	 * Counts sites represented by the Attention tab.
	 *
	 * @return int
	 */
	private function attention_count() {
		$context = $this->site_status_context();

		return isset( $context['attention_count'] ) ? (int) $context['attention_count'] : 0;
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
