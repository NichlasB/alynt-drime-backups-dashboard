<?php
/**
 * Admin page detail helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides reusable detail-list, status-history, and manual-check rendering helpers.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers {
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
		echo '</dl>';
		$this->render_backup_sources_detail( $payload );
		echo '</div>';
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
	 * Determines whether a site row has active polling credentials.
	 *
	 * Sites-list queries intentionally expose only a redacted
	 * `has_polling_secret` flag, while detail queries may include the encrypted
	 * ciphertext. Treat either as sufficient evidence without exposing the
	 * ciphertext in list screens.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function site_has_polling_credentials( array $site ) {
		$has_secret = ! empty( $site['polling_secret_ciphertext'] ) || ! empty( $site['has_polling_secret'] );

		return ! empty( $site['polling_key_id'] ) && $has_secret;
	}

	/**
	 * Determines whether a site can be manually checked now.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function site_can_manual_check( array $site ) {
		if ( isset( $site['enrollment_status'] ) && 'revoked' === $site['enrollment_status'] ) {
			return false;
		}

		return $this->site_has_polling_credentials( $site );
	}

	/**
	 * Gets state-specific unavailable copy for manual checks.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function manual_check_unavailable_message( array $site ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '';

		if ( 'revoked' === $status ) {
			return __( 'Pairing revoked locally. Re-enroll this site before manual checks are available.', 'alynt-drime-backups-dashboard' );
		}

		if ( 'pending' === $status ) {
			return __( 'Waiting for client opt-in before manual checks are available.', 'alynt-drime-backups-dashboard' );
		}

		return __( 'Polling credentials are missing. Re-enroll this site to restore manual checks.', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Renders the Sites-list next-poll line with credential-aware copy.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function next_poll_html( array $site ) {
		if ( $this->site_can_manual_check( $site ) ) {
			return '<span class="adbd-row-meta">' . esc_html__( 'Next poll:', 'alynt-drime-backups-dashboard' ) . ' ' . $this->time_html( isset( $site['next_poll_at'] ) ? $site['next_poll_at'] : '' ) . '</span>';
		}

		$status = isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '';

		if ( 'revoked' === $status ) {
			$message = __( 'Unavailable until re-enrolled', 'alynt-drime-backups-dashboard' );
		} elseif ( 'pending' === $status ) {
			$message = __( 'Waiting for client opt-in', 'alynt-drime-backups-dashboard' );
		} else {
			$message = __( 'Credentials missing', 'alynt-drime-backups-dashboard' );
		}

		return '<span class="adbd-row-meta adbd-row-meta-muted">' . esc_html__( 'Next poll:', 'alynt-drime-backups-dashboard' ) . ' ' . esc_html( $message ) . '</span>';
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
		if ( ! $this->site_can_manual_check( $site ) ) {
			echo '<span class="description adbd-action-unavailable">' . esc_html( $this->manual_check_unavailable_message( $site ) ) . '</span>';
			return;
		}

		?>
		<form method="post" class="adbd-inline-form">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_check_status_now' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="check_status_now">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
			<button type="submit" class="button <?php echo $primary ? 'button-primary' : ''; ?>" data-busy-label="<?php esc_attr_e( 'Checking…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Check Now', 'alynt-drime-backups-dashboard' ); ?></button>
		</form>
		<?php
	}

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
			echo '<p><button type="button" class="button button-secondary" disabled aria-disabled="true">' . esc_html__( 'Request Backup Now', 'alynt-drime-backups-dashboard' ) . '</button> <span class="description">' . esc_html__( 'Dispatch is intentionally disabled until the signed request endpoint is implemented and the client site opts in to V2 actions.', 'alynt-drime-backups-dashboard' ) . '</span></p>';
		} else {
			echo '<p><span class="adbd-status-pill is-pending">' . esc_html__( 'Not available yet', 'alynt-drime-backups-dashboard' ) . '</span> ' . esc_html( $availability['message'] ) . '</p>';
		}

		$this->render_remote_action_history( $history );
		echo '</div></div>';
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
			return array(
				'available'   => true,
				'message'     => __( 'The latest client report says scan/upload-now capability is available, but this dashboard build has not enabled dispatch yet.', 'alynt-drime-backups-dashboard' ),
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

	/**
	 * Renders recent remote action history without raw payloads.
	 *
	 * @param array<int,array<string,mixed>> $history Remote action history.
	 * @return void
	 */
	private function render_remote_action_history( array $history ) {
		echo '<h4>' . esc_html__( 'Remote Action History', 'alynt-drime-backups-dashboard' ) . '</h4>';

		if ( empty( $history ) ) {
			echo '<p class="description">' . esc_html__( 'No V2 remote action requests are stored for this site yet.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		echo '<div class="adbd-table-wrap"><table class="widefat striped adbd-history-table"><caption>' . esc_html__( 'Recent V2 remote action requests for this site', 'alynt-drime-backups-dashboard' ) . '</caption><thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Requested', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Action', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'State', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Result', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $history as $row ) {
			echo '<tr><td>' . $this->time_html( isset( $row['requested_at'] ) ? $row['requested_at'] : '' ) . '</td><td>' . esc_html( $this->remote_action_label( isset( $row['action_type'] ) ? (string) $row['action_type'] : '' ) ) . '</td><td>' . esc_html( $this->remote_action_state_label( isset( $row['state'] ) ? (string) $row['state'] : '' ) ) . '</td><td>' . esc_html( isset( $row['result_summary'] ) && '' !== $row['result_summary'] ? (string) $row['result_summary'] : '-' ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- time_html() returns escaped markup.
		}

		echo '</tbody></table></div>';
	}

	/**
	 * Gets a safe operator label for a V2 action type.
	 *
	 * @param string $action_type Action type.
	 * @return string
	 */
	private function remote_action_label( $action_type ) {
		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW === sanitize_key( $action_type ) ) {
			return __( 'Request Backup Now', 'alynt-drime-backups-dashboard' );
		}

		return __( 'Unknown action', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a safe operator label for a V2 action state.
	 *
	 * @param string $state State.
	 * @return string
	 */
	private function remote_action_state_label( $state ) {
		$labels = array(
			'queued_for_dispatch' => __( 'Queued for dispatch', 'alynt-drime-backups-dashboard' ),
			'dispatch_failed'     => __( 'Dispatch failed', 'alynt-drime-backups-dashboard' ),
			'accepted'            => __( 'Accepted', 'alynt-drime-backups-dashboard' ),
			'rejected'            => __( 'Rejected', 'alynt-drime-backups-dashboard' ),
			'unsupported'         => __( 'Unsupported', 'alynt-drime-backups-dashboard' ),
			'rate_limited'        => __( 'Rate limited', 'alynt-drime-backups-dashboard' ),
			'busy'                => __( 'Busy', 'alynt-drime-backups-dashboard' ),
			'running'             => __( 'Running', 'alynt-drime-backups-dashboard' ),
			'succeeded'           => __( 'Succeeded', 'alynt-drime-backups-dashboard' ),
			'failed'              => __( 'Failed', 'alynt-drime-backups-dashboard' ),
			'timed_out'           => __( 'Timed out', 'alynt-drime-backups-dashboard' ),
			'stale'               => __( 'Stale', 'alynt-drime-backups-dashboard' ),
		);

		$state = sanitize_key( $state );

		return isset( $labels[ $state ] ) ? $labels[ $state ] : __( 'Unknown', 'alynt-drime-backups-dashboard' );
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
