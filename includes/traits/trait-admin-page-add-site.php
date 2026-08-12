<?php
/**
 * Admin page Add Site shell.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the dashboard-generated pairing token flow.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Add_Site {
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

		$site_label_describedby      = 'alynt-dashboard-site-label-help';
		$expected_origin_describedby = 'alynt-dashboard-expected-origin-help';
		$site_label_invalid          = false;
		$expected_origin_invalid     = false;

		if ( is_wp_error( $result ) ) {
			$error_code = $result->get_error_code();

			if ( in_array( $error_code, array( 'site_label_required', 'site_label_too_long' ), true ) ) {
				$site_label_invalid      = true;
				$site_label_describedby .= ' adbd-action-notice';
			}

			if ( in_array( $error_code, array( 'expected_origin_invalid', 'expected_origin_too_long', 'pending_site_exists' ), true ) ) {
				$expected_origin_invalid      = true;
				$expected_origin_describedby .= ' adbd-action-notice';
			}
		}
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
					<td><input type="text" id="alynt-dashboard-site-label" name="alynt_drime_backups_dashboard_pending_site[site_label]" class="regular-text" required="required" aria-describedby="<?php echo esc_attr( $site_label_describedby ); ?>" <?php echo $site_label_invalid ? 'aria-invalid="true"' : ''; ?> value="<?php echo esc_attr( isset( $submitted['site_label'] ) ? $submitted['site_label'] : '' ); ?>"><p id="alynt-dashboard-site-label-help" class="description"><?php esc_html_e( 'Used in this dashboard only. It is never sent to the client site.', 'alynt-drime-backups-dashboard' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><label for="alynt-dashboard-expected-origin"><?php esc_html_e( 'Expected public HTTPS origin', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td>
						<input type="url" id="alynt-dashboard-expected-origin" name="alynt_drime_backups_dashboard_pending_site[expected_origin]" class="regular-text" required="required" aria-describedby="<?php echo esc_attr( $expected_origin_describedby ); ?>" <?php echo $expected_origin_invalid ? 'aria-invalid="true"' : ''; ?> placeholder="https://example.com" value="<?php echo esc_attr( isset( $submitted['expected_origin'] ) ? $submitted['expected_origin'] : '' ); ?>">
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
					<td><code class="adbd-endpoint-preview" aria-live="polite" aria-atomic="true" data-empty-label="<?php esc_attr_e( 'Enter an HTTPS origin to preview the fixed endpoint.', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Enter an HTTPS origin to preview the fixed endpoint.', 'alynt-drime-backups-dashboard' ); ?></code><p class="description"><?php esc_html_e( 'The path is fixed and cannot be edited. The dashboard polls this authenticated GET endpoint and nothing else.', 'alynt-drime-backups-dashboard' ); ?></p></td>
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
