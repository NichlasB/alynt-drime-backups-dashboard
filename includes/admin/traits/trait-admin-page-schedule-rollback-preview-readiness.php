<?php
/**
 * Admin page schedule rollback-preview readiness helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.58
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides guarded schedule rollback-preview readiness helpers.
 *
 * @since 0.1.58
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Rollback_Preview_Readiness {

	/**
	 * Gets the operator-facing schedule rollback-preview readiness label.
	 *
	 * @param array<string,mixed>            $site Site row.
	 * @param array<string,mixed>            $schedule Schedule summary.
	 * @param array<string,mixed>            $capabilities Sanitized capabilities.
	 * @param array<int,array<string,mixed>> $remote_action_history Recent remote action rows.
	 * @return string
	 */
	private function schedule_rollback_preview_readiness_label( array $site, array $schedule, array $capabilities, array $remote_action_history = array() ) {
		$readiness = $this->schedule_rollback_preview_readiness( $site, $schedule, $capabilities, $remote_action_history );

		return $readiness['message'];
	}

	/**
	 * Gets escaped operator-facing schedule rollback-preview readiness markup.
	 *
	 * @param array<string,mixed>            $site Site row.
	 * @param array<string,mixed>            $schedule Schedule summary.
	 * @param array<string,mixed>            $capabilities Sanitized capabilities.
	 * @param array<int,array<string,mixed>> $remote_action_history Recent remote action rows.
	 * @return string
	 */
	private function schedule_rollback_preview_readiness_markup( array $site, array $schedule, array $capabilities, array $remote_action_history = array() ) {
		$readiness = $this->schedule_rollback_preview_readiness( $site, $schedule, $capabilities, $remote_action_history );
		$state     = sanitize_key( $readiness['state'] );

		return sprintf(
			'<span class="adbd-status-pill is-%1$s">%2$s</span> %3$s',
			esc_attr( $state ),
			esc_html( $readiness['label'] ),
			esc_html( $readiness['message'] )
		);
	}

	/**
	 * Gets structured operator-facing schedule rollback-preview readiness.
	 *
	 * @param array<string,mixed>            $site Site row.
	 * @param array<string,mixed>            $schedule Schedule summary.
	 * @param array<string,mixed>            $capabilities Sanitized capabilities.
	 * @param array<int,array<string,mixed>> $remote_action_history Recent remote action rows.
	 * @return array{state:string,label:string,message:string}
	 */
	private function schedule_rollback_preview_readiness( array $site, array $schedule, array $capabilities, array $remote_action_history = array() ) {
		$site_id             = isset( $site['id'] ) ? absint( $site['id'] ) : 0;
		$schedule_id         = isset( $schedule['schedule_id'] ) ? sanitize_key( (string) $schedule['schedule_id'] ) : '';
		$capabilities_helper = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();

		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::SCHEDULE_SCAN_UPLOAD !== $schedule_id ) {
			return $this->schedule_rollback_preview_readiness_result(
				'blocked',
				__( 'Unavailable', 'alynt-drime-backups-dashboard' ),
				__( 'Unavailable for this schedule class.', 'alynt-drime-backups-dashboard' )
			);
		}

		if ( ! $capabilities_helper->supports_schedule_rollback_preview_action( $capabilities, $schedule_id ) ) {
			return $this->schedule_rollback_preview_readiness_result(
				'hidden',
				__( 'Hidden', 'alynt-drime-backups-dashboard' ),
				__( 'Hidden until the latest client report advertises rollback-preview support.', 'alynt-drime-backups-dashboard' )
			);
		}

		if (
			0 === $site_id
			|| ! property_exists( $this, 'remote_actions' )
			|| ! $this->remote_actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
		) {
			return $this->schedule_rollback_preview_readiness_result(
				'waiting',
				__( 'Waiting', 'alynt-drime-backups-dashboard' ),
				__( 'Supported by the client, but waiting for dashboard action history.', 'alynt-drime-backups-dashboard' )
			);
		}

		$apply_public_id = $this->latest_apply_public_id_for_rollback_preview( $site_id, $schedule_id, $remote_action_history );

		if ( '' === $apply_public_id ) {
			return $this->schedule_rollback_preview_readiness_result(
				'waiting',
				__( 'Waiting', 'alynt-drime-backups-dashboard' ),
				__( 'Supported by the client; waiting for a successful Schedule Apply with rollback metadata.', 'alynt-drime-backups-dashboard' )
			);
		}

		$rollback_preview = $this->remote_actions->successful_schedule_apply_for_rollback_preview( $site_id, $apply_public_id, $capabilities );

		if ( is_wp_error( $rollback_preview ) ) {
			$code = $rollback_preview->get_error_code();

			if ( 'schedule_rollback_preview_metadata_expired' === $code ) {
				return $this->schedule_rollback_preview_readiness_result(
					'blocked',
					__( 'Expired', 'alynt-drime-backups-dashboard' ),
					__( 'Supported by the client, but the latest rollback metadata has expired.', 'alynt-drime-backups-dashboard' )
				);
			}

			if ( in_array( $code, array( 'schedule_rollback_preview_metadata_missing', 'schedule_rollback_preview_metadata_invalid' ), true ) ) {
				return $this->schedule_rollback_preview_readiness_result(
					'blocked',
					__( 'Blocked', 'alynt-drime-backups-dashboard' ),
					__( 'Supported by the client, but rollback metadata is incomplete. Run a new preview/apply sequence before pilot proof.', 'alynt-drime-backups-dashboard' )
				);
			}

			if ( 'schedule_rollback_preview_apply_not_ready' === $code ) {
				return $this->schedule_rollback_preview_readiness_result(
					'waiting',
					__( 'Waiting', 'alynt-drime-backups-dashboard' ),
					__( 'Supported by the client; waiting for the selected Schedule Apply to succeed.', 'alynt-drime-backups-dashboard' )
				);
			}

			if ( 'schedule_rollback_preview_unavailable' === $code ) {
				return $this->schedule_rollback_preview_readiness_result(
					'hidden',
					__( 'Hidden', 'alynt-drime-backups-dashboard' ),
					__( 'Hidden until the latest client report advertises rollback-preview support.', 'alynt-drime-backups-dashboard' )
				);
			}

			return $this->schedule_rollback_preview_readiness_result(
				'waiting',
				__( 'Waiting', 'alynt-drime-backups-dashboard' ),
				__( 'Supported by the client; waiting for rollback-preview-ready apply evidence.', 'alynt-drime-backups-dashboard' )
			);
		}

		return $this->schedule_rollback_preview_readiness_result(
			'ready',
			__( 'Ready', 'alynt-drime-backups-dashboard' ),
			__( 'Ready for non-mutating rollback preview from the latest successful Schedule Apply.', 'alynt-drime-backups-dashboard' )
		);
	}

	/**
	 * Builds a typed rollback-preview readiness result.
	 *
	 * @param string $state State key.
	 * @param string $label Short label.
	 * @param string $message Operator message.
	 * @return array{state:string,label:string,message:string}
	 */
	private function schedule_rollback_preview_readiness_result( $state, $label, $message ) {
		$allowed_states = array(
			'ready'   => true,
			'waiting' => true,
			'hidden'  => true,
			'blocked' => true,
		);

		$state = sanitize_key( $state );

		return array(
			'state'   => isset( $allowed_states[ $state ] ) ? $state : 'waiting',
			'label'   => (string) $label,
			'message' => (string) $message,
		);
	}

	/**
	 * Gets the latest successful apply public ID that may support rollback preview.
	 *
	 * @param int                                 $site_id Site ID.
	 * @param string                              $schedule_id Schedule ID.
	 * @param array<int,array<string,mixed>>|null $remote_action_history Recent remote action rows, or null to fetch.
	 * @return string
	 */
	private function latest_apply_public_id_for_rollback_preview( $site_id, $schedule_id, $remote_action_history = null ) {
		$site_id     = absint( $site_id );
		$schedule_id = sanitize_key( (string) $schedule_id );

		if (
			0 === $site_id
			|| '' === $schedule_id
			|| ! property_exists( $this, 'remote_actions' )
			|| ! $this->remote_actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
		) {
			return '';
		}

		$history = is_array( $remote_action_history ) ? $remote_action_history : $this->remote_actions->recent_for_site( $site_id, 10 );

		foreach ( $history as $row ) {
			if (
				! is_array( $row )
				|| Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY !== ( isset( $row['action_type'] ) ? sanitize_key( (string) $row['action_type'] ) : '' )
				|| 'succeeded' !== ( isset( $row['state'] ) ? sanitize_key( (string) $row['state'] ) : '' )
				|| empty( $row['public_id'] )
			) {
				continue;
			}

			$context  = ! empty( $row['redacted_context_json'] ) ? json_decode( (string) $row['redacted_context_json'], true ) : array();
			$apply    = is_array( $context ) && isset( $context['schedule_apply'] ) && is_array( $context['schedule_apply'] ) ? $context['schedule_apply'] : array();
			$metadata = isset( $apply['rollback_metadata'] ) && is_array( $apply['rollback_metadata'] ) ? $apply['rollback_metadata'] : array();

			if ( ! empty( $apply ) && ! empty( $apply['schedule_id'] ) && sanitize_key( (string) $apply['schedule_id'] ) !== $schedule_id ) {
				continue;
			}

			if ( ! empty( $metadata ) && empty( $metadata['captured'] ) ) {
				continue;
			}

			return (string) $row['public_id'];
		}

		return '';
	}
}
