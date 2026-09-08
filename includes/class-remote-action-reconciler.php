<?php
/**
 * Remote action reconciliation.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reconciles dashboard action requests with redacted client-side status evidence.
 *
 * @since 0.1.16
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler {
	/**
	 * Remote action repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
	 */
	private $actions;

	/**
	 * Capability sanitizer.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities
	 */
	private $capabilities;

	/**
	 * Constructor.
	 *
	 * @since 0.1.16
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Repository|null   $actions Remote action repository.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities|null $capabilities Capability sanitizer.
	 */
	public function __construct( $actions = null, $capabilities = null ) {
		$this->actions      = $actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository ? $actions : new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$this->capabilities = $capabilities instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities ? $capabilities : new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
	}

	/**
	 * Reconciles one successful poll payload against dashboard-owned action rows.
	 *
	 * @since 0.1.16
	 *
	 * @param int                 $site_id Dashboard site ID.
	 * @param array<string,mixed> $payload Validated status payload.
	 * @param string|null         $now Current UTC MySQL timestamp.
	 * @return array<string,int|string>|WP_Error
	 */
	public function reconcile_site_payload( $site_id, array $payload, $now = null ) {
		$site_id = absint( $site_id );

		if ( 0 === $site_id ) {
			return array(
				'matched' => 0,
				'stale'   => 0,
				'reason'  => 'site_missing',
			);
		}

		$matched        = 0;
		$remote_actions = isset( $payload['remote_actions'] ) && is_array( $payload['remote_actions'] ) ? $payload['remote_actions'] : array();
		$last_action    = $this->last_action_from_remote_actions( $remote_actions );

		if ( ! empty( $last_action ) ) {
			$stored = $this->actions->find_by_public_id_for_site( (string) $last_action['action_id'], $site_id );

			if ( is_array( $stored ) && $this->action_types_match( $stored, $last_action ) && $this->client_report_can_update( $stored, $last_action ) ) {
				$matched = $this->actions->mark_client_report( isset( $stored['id'] ) ? (int) $stored['id'] : 0, $last_action, $now ) ? 1 : 0;
			}
		}

		$stale = $this->actions->mark_unconfirmed_actions_stale_for_site( $site_id, $now );

		if ( is_wp_error( $stale ) ) {
			return $stale;
		}

		return array(
			'matched' => $matched,
			'stale'   => (int) $stale,
		);
	}

	/**
	 * Gets a sanitized last-action report from remote action capability evidence.
	 *
	 * @param array<string,mixed> $remote_actions Remote action evidence.
	 * @return array<string,mixed>
	 */
	private function last_action_from_remote_actions( array $remote_actions ) {
		$sanitized = $this->capabilities->sanitize( $remote_actions );

		if ( is_wp_error( $sanitized ) || empty( $sanitized['last_action'] ) || ! is_array( $sanitized['last_action'] ) ) {
			return array();
		}

		return $sanitized['last_action'];
	}

	/**
	 * Verifies a client report describes the same action type as the dashboard row.
	 *
	 * @param array<string,mixed> $stored Stored dashboard row.
	 * @param array<string,mixed> $last_action Client last-action summary.
	 * @return bool
	 */
	private function action_types_match( array $stored, array $last_action ) {
		$stored_type = isset( $stored['action_type'] ) ? $this->capabilities->sanitize_action_type( (string) $stored['action_type'] ) : '';
		$client_type = isset( $last_action['action_type'] ) ? $this->capabilities->sanitize_action_type( (string) $last_action['action_type'] ) : '';

		return '' !== $stored_type && hash_equals( $stored_type, $client_type );
	}

	/**
	 * Verifies the client supplied an observable action state, not only the sanitizer fallback.
	 *
	 * @param array<string,mixed> $last_action Client last-action summary.
	 * @return bool
	 */
	private function client_state_is_observable( array $last_action ) {
		$state = isset( $last_action['state'] ) ? $this->capabilities->sanitize_state( (string) $last_action['state'] ) : '';

		return '' !== $state && Alynt_Drime_Backups_Dashboard_Remote_Action_Repository::DEFAULT_STATE !== $state;
	}

	/**
	 * Determines whether a client report may update a stored dashboard row.
	 *
	 * @param array<string,mixed> $stored Stored dashboard row.
	 * @param array<string,mixed> $last_action Client last-action summary.
	 * @return bool
	 */
	private function client_report_can_update( array $stored, array $last_action ) {
		if ( ! $this->client_state_is_observable( $last_action ) ) {
			return false;
		}

		$stored_state = isset( $stored['state'] ) ? $this->capabilities->sanitize_state( (string) $stored['state'] ) : '';
		$client_state = isset( $last_action['state'] ) ? $this->capabilities->sanitize_state( (string) $last_action['state'] ) : '';

		if ( $this->state_is_terminal( $stored_state ) && in_array( $client_state, array( 'accepted', 'running' ), true ) ) {
			return false;
		}

		$stored_time = isset( $stored['client_updated_at'] ) ? strtotime( (string) $stored['client_updated_at'] ) : false;
		$client_time = $this->client_action_timestamp( $last_action );

		return false === $stored_time || false === $client_time || $client_time >= $stored_time;
	}

	/**
	 * Gets whether an action state is terminal for dashboard history purposes.
	 *
	 * @param string $state State.
	 * @return bool
	 */
	private function state_is_terminal( $state ) {
		return in_array( $state, array( 'succeeded', 'failed', 'rejected', 'unsupported', 'rate_limited', 'busy', 'timed_out', 'stale', 'dispatch_failed' ), true );
	}

	/**
	 * Gets the best client-side timestamp from a last-action summary.
	 *
	 * @param array<string,mixed> $last_action Client last-action summary.
	 * @return int|false
	 */
	private function client_action_timestamp( array $last_action ) {
		foreach ( array( 'updated_at', 'completed_at', 'requested_at' ) as $key ) {
			if ( empty( $last_action[ $key ] ) ) {
				continue;
			}

			$timestamp = strtotime( (string) $last_action[ $key ] );

			if ( false !== $timestamp ) {
				return $timestamp;
			}
		}

		return false;
	}
}
