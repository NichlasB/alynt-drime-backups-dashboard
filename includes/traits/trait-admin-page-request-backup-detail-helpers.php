<?php
/**
 * Admin page helper split.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Provides request-backup and remote-action opt-in rendering helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_Detail_Helpers {


	/**
	 * Renders the V2.1 Request Backup Now eligibility and history panel.
	 *
	 * This intentionally renders an inert control until dashboard dispatch and
	 * client-side action handling are implemented and explicitly enabled.
	 *
	 * @param array<string,mixed>            $site Site row.
	 * @param array<string,mixed>|null       $snapshot Latest snapshot row.
	 * @param array<int,array<string,mixed>> $history Remote action history.
	 * @return void
	 */
	private function render_request_backup_now_panel( array $site, $snapshot, array $history ) {
		$payload      = is_array( $snapshot ) ? $this->decoded_snapshot_payload( $snapshot ) : array();
		$availability = $this->request_backup_now_availability( $site, $payload );

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Request Backup Now', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'V2.1 is designed as a signed request for the client uploader to scan for ready backup packages and upload eligible items using its own local settings. The dashboard still does not receive Drime credentials and does not create, restore, delete, or clean up backups.', 'alynt-drime-backups-dashboard' ) . '</p>';

		if ( $availability['available'] ) {
			echo '<p><span class="adbd-status-pill is-working">' . esc_html__( 'Capability reported', 'alynt-drime-backups-dashboard' ) . '</span> ' . esc_html( $availability['message'] ) . '</p>';
			$this->render_request_backup_now_form( $site );
		} else {
			echo '<p><span class="adbd-status-pill is-pending">' . esc_html__( 'Not available yet', 'alynt-drime-backups-dashboard' ) . '</span> ' . esc_html( $availability['message'] ) . '</p>';
			$this->render_action_opt_in_form( $site );
		}

		$this->render_remote_action_history( $history );
		echo '</div></div>';
	}

	/**
	 * Renders the signed V2.1 Request Backup Now form.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return void
	 */
	private function render_request_backup_now_form( array $site ) {
		$description_id = 'adbd-request-backup-now-description';
		?>
		<form method="post" class="adbd-inline-form">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_request_backup_now' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="request_backup_now">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( isset( $site['id'] ) ? (string) (int) $site['id'] : '0' ); ?>">
			<button type="submit" class="button button-secondary" data-busy-label="<?php esc_attr_e( 'Requesting…', 'alynt-drime-backups-dashboard' ); ?>" aria-describedby="<?php echo esc_attr( $description_id ); ?>"><?php esc_html_e( 'Request Backup Now', 'alynt-drime-backups-dashboard' ); ?></button>
			<span id="<?php echo esc_attr( $description_id ); ?>" class="description"><?php esc_html_e( 'Sends one signed scan/upload-now intent. The client site may accept, reject, rate-limit, or report busy; no backup creation, restore, cleanup, settings, or credential action is requested.', 'alynt-drime-backups-dashboard' ); ?></span>
		</form>
		<?php
	}

	/**
	 * Renders a display-once V2 action opt-in token result.
	 *
	 * @param array<string,mixed>|WP_Error|null $result Action result.
	 * @param int                               $site_id Current site ID.
	 * @return void
	 */
	private function render_action_opt_in_token_panel( $result, $site_id ) {
		if (
			! is_array( $result )
			|| ! isset( $result['action'], $result['action_opt_in_token'], $result['site_id'] )
			|| 'generate_action_opt_in_token' !== $result['action']
			|| (int) $result['site_id'] !== (int) $site_id
		) {
			return;
		}

		echo '<div class="adbd-panel adbd-token-panel"><h3>' . esc_html__( 'V2 Action Opt-In Token — Shown Once', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'Copy this token now and paste it into the paired client site. It lets that client explicitly opt in to the V2.1 scan/upload-now action using the public action key in the token. The dashboard stores the matching private key encrypted, but it does not store this token.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '<label for="adbd-action-opt-in-token"><strong>' . esc_html__( 'Action opt-in token', 'alynt-drime-backups-dashboard' ) . '</strong></label>';
		echo '<div class="adbd-copy-row"><input id="adbd-action-opt-in-token" class="large-text code" type="text" readonly="readonly" value="' . esc_attr( (string) $result['action_opt_in_token'] ) . '" aria-label="' . esc_attr__( 'V2 action opt-in token', 'alynt-drime-backups-dashboard' ) . '" aria-describedby="adbd-action-opt-in-token-help">';
		echo '<button type="button" class="button button-primary adbd-copy-button" hidden data-copy-target="adbd-action-opt-in-token" data-busy-label="' . esc_attr__( 'Copying…', 'alynt-drime-backups-dashboard' ) . '" data-success-message="' . esc_attr__( 'Action opt-in token copied to the clipboard.', 'alynt-drime-backups-dashboard' ) . '" data-error-message="' . esc_attr__( 'The token could not be copied automatically. Select the field and copy it manually.', 'alynt-drime-backups-dashboard' ) . '">' . esc_html__( 'Copy Token', 'alynt-drime-backups-dashboard' ) . '</button></div>';
		echo '<p id="adbd-action-opt-in-token-help" class="description">' . esc_html__( 'This token is not a Drime token and does not grant restore, delete, cleanup, schedule, settings, or credential actions.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '<p class="adbd-copy-status" role="status" aria-live="polite"></p>';
		echo '<dl class="adbd-detail-list">';
		$this->render_detail_item( __( 'Expires', 'alynt-drime-backups-dashboard' ), isset( $result['action_token_expires_at'] ) ? (string) $result['action_token_expires_at'] : '-' );
		$this->render_detail_item( __( 'Expected client origin', 'alynt-drime-backups-dashboard' ), isset( $result['expected_origin'] ) ? (string) $result['expected_origin'] : '-' );
		$this->render_detail_item( __( 'Allowed action', 'alynt-drime-backups-dashboard' ), __( 'Scan for ready backup packages and upload eligible items', 'alynt-drime-backups-dashboard' ) );
		echo '</dl></div></div>';
	}

	/**
	 * Renders the V2 action opt-in token generation form.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return void
	 */
	private function render_action_opt_in_form( array $site ) {
		if ( ! $this->site_can_manual_check( $site ) ) {
			return;
		}

		$description_id = 'adbd-action-opt-in-generation-description';
		?>
		<form method="post" class="adbd-inline-form">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_generate_action_opt_in_token' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="generate_action_opt_in_token">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( isset( $site['id'] ) ? (string) (int) $site['id'] : '0' ); ?>">
			<button type="submit" class="button" data-busy-label="<?php esc_attr_e( 'Generating…', 'alynt-drime-backups-dashboard' ); ?>" aria-describedby="<?php echo esc_attr( $description_id ); ?>"><?php esc_html_e( 'Generate V2 Opt-In Token', 'alynt-drime-backups-dashboard' ); ?></button>
			<span id="<?php echo esc_attr( $description_id ); ?>" class="description"><?php esc_html_e( 'Creates a short-lived adb2a token for client-side V2 action opt-in. It does not request a backup.', 'alynt-drime-backups-dashboard' ); ?></span>
		</form>
		<?php
	}

	/**
	 * Renders a compact V2.1 row hint for the Sites table.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param array<string,mixed> $payload Latest decoded snapshot payload.
	 * @return string
	 */
	private function request_backup_now_row_hint( array $site, array $payload ) {
		$availability = $this->request_backup_now_availability( $site, $payload );
		$label        = $availability['available']
			? __( 'Request Backup: capability reported', 'alynt-drime-backups-dashboard' )
			: $availability['short_label'];
		$last_action  = $this->remote_action_last_action( $payload );

		if ( ! empty( $last_action['state'] ) ) {
			$label = sprintf(
				/* translators: 1: capability label, 2: latest client action state. */
				__( '%1$s · latest client action: %2$s', 'alynt-drime-backups-dashboard' ),
				$label,
				$this->remote_action_state_label( (string) $last_action['state'] )
			);
		}

		return '<span class="description adbd-row-meta">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Gets V2.1 Request Backup Now availability from redacted client evidence.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param array<string,mixed> $payload Latest decoded snapshot payload.
	 * @return array{available:bool,message:string,short_label:string}
	 */
	private function request_backup_now_availability( array $site, array $payload ) {
		if ( ! $this->site_can_manual_check( $site ) ) {
			return array(
				'available'   => false,
				'message'     => __( 'This site must be actively enrolled with polling credentials before V2 actions can be considered.', 'alynt-drime-backups-dashboard' ),
				'short_label' => __( 'Request Backup: enrollment needed', 'alynt-drime-backups-dashboard' ),
			);
		}

		$remote_actions = isset( $payload['remote_actions'] ) && is_array( $payload['remote_actions'] ) ? $payload['remote_actions'] : array();
		$capabilities   = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();

		if ( $capabilities->supports_scan_upload_now( $remote_actions ) ) {
			if ( array_key_exists( 'action_key_id', $site ) && ( empty( $site['action_key_id'] ) || empty( $site['action_private_key_ciphertext'] ) ) ) {
				return array(
					'available'   => false,
					'message'     => __( 'The latest client report says scan/upload-now capability is available, but this dashboard record no longer has the encrypted signing key. Regenerate the V2 opt-in token and run Check Now.', 'alynt-drime-backups-dashboard' ),
					'short_label' => __( 'Request Backup: opt-in needed', 'alynt-drime-backups-dashboard' ),
				);
			}

			return array(
				'available'   => true,
				'message'     => __( 'The latest client report says scan/upload-now capability is available. The request is signed by this dashboard and the client remains the execution owner.', 'alynt-drime-backups-dashboard' ),
				'short_label' => __( 'Request Backup: capability reported', 'alynt-drime-backups-dashboard' ),
			);
		}

		if ( 2 === absint( isset( $remote_actions['protocol_version'] ) ? $remote_actions['protocol_version'] : 0 ) ) {
			return array(
				'available'   => false,
				'message'     => __( 'The latest client report understands V2.1 remote actions, but the client has not opted in with a valid action key or Sodium verification is unavailable.', 'alynt-drime-backups-dashboard' ),
				'short_label' => __( 'Request Backup: opt-in needed', 'alynt-drime-backups-dashboard' ),
			);
		}

		return array(
			'available'   => false,
			'message'     => __( 'The latest client report does not advertise V2.1 scan/upload-now capability. Upgrade and opt in on the client site before this action can be enabled.', 'alynt-drime-backups-dashboard' ),
			'short_label' => __( 'Request Backup: not available yet', 'alynt-drime-backups-dashboard' ),
		);
	}
}
