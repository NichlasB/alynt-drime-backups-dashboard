<?php
/**
 * Strict read-only authority evidence consumer; never grants execution.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Validates a separate model contract without changing legacy action semantics. */
class Alynt_Drime_Backups_Dashboard_Cadence_Authority_Evidence {
	const STATUS_KEYS  = array( 'contract_version', 'mode', 'site_uuid', 'key_id', 'authority_revision', 'current_cadence', 'next_due_at', 'authority_fingerprint', 'model_enabled', 'pending', 'observed_at', 'expires_at', 'dispatch_supported', 'execution_supported' );
	const PREVIEW_KEYS = array( 'contract_version', 'mode', 'kind', 'site_uuid', 'key_id', 'source_change_action_id', 'preview_action_id', 'authority_revision', 'authority_fingerprint', 'current_cadence', 'rollback_cadence', 'source_created_at', 'source_expires_at', 'preview_created_at', 'preview_expires_at', 'intent_expires_at', 'safety_checks_passed', 'eligible', 'dispatch_supported', 'execution_supported', 'preview_fingerprint' );

	/**
	 * UTC seconds clock.
	 *
	 * @var callable
	 */
	private $clock;

	/**
	 * No hooks, remote requests or database writes.
	 *
	 * @param callable|null $clock UTC clock.
	 */
	public function __construct( $clock = null ) {
		$this->clock = null === $clock ? 'time' : $clock;
	}

	/**
	 * Omit invalid/unsupported optional evidence, retaining legacy status behavior.
	 *
	 * @param mixed  $value Untrusted model status.
	 * @param string $expected_uuid Persisted enrollment UUID.
	 * @param string $expected_key Persisted action-key ID, not self-reported payload ID.
	 * @return array
	 */
	public function status( $value, $expected_uuid, $expected_key ) {
		$now = call_user_func( $this->clock );
		if ( ! $this->exact_keys( $value, self::STATUS_KEYS ) || ! $this->common( $value, $expected_uuid, $expected_key )
			|| ! $this->integer( $now ) || ! $this->integer( $value['next_due_at'], true ) || ! is_bool( $value['model_enabled'] )
			|| ! in_array( $value['pending'], array( 'none', 'claimed', 'started', 'recovery_required' ), true )
			|| ! $this->integer( $value['observed_at'], true ) || ! $this->integer( $value['expires_at'], true ) ) {
			return array();
		}
		if ( $value['observed_at'] > $now || $value['expires_at'] <= $now || $value['expires_at'] <= $value['observed_at'] || $value['expires_at'] > $value['observed_at'] + 300 ) {
			return array();
		}
		return array_intersect_key( $value, array_flip( self::STATUS_KEYS ) );
	}

	/**
	 * Validate a prototype inspection against fresh independently supplied context.
	 *
	 * @param mixed  $value Untrusted inspection.
	 * @param array  $authority Current model status, never inferred from inspection.
	 * @param string $expected_uuid Persisted UUID.
	 * @param string $expected_key Persisted action-key ID.
	 * @param string $source Expected native change ID.
	 * @param string $preview Expected inspection ID.
	 * @param int    $intent_expires_at Expected candidate intent expiry, not authorization.
	 * @return array
	 */
	public function preview( $value, array $authority, $expected_uuid, $expected_key, $source, $preview, $intent_expires_at ) {
		$now       = call_user_func( $this->clock );
		$authority = $this->status( $authority, $expected_uuid, $expected_key );
		if ( empty( $authority ) || ! $authority['model_enabled'] || 'none' !== $authority['pending'] || ! $this->exact_keys( $value, self::PREVIEW_KEYS )
			|| ! $this->common( $value, $expected_uuid, $expected_key ) || 'model_change_preview' !== $value['kind'] || true !== $value['safety_checks_passed'] || false !== $value['eligible']
			|| ! $this->uuid( $source ) || ! $this->uuid( $preview ) || $source === $preview || $source !== $value['source_change_action_id'] || $preview !== $value['preview_action_id']
			|| ! $this->cadence( $value['rollback_cadence'] ) || $value['rollback_cadence'] === $value['current_cadence'] || ! $this->hash( $value['preview_fingerprint'] ) ) {
			return array();
		}
		foreach ( array( 'authority_revision', 'authority_fingerprint', 'current_cadence' ) as $field ) {
			if ( $value[ $field ] !== $authority[ $field ] ) {
				return array();
			}
		}
		foreach ( array( 'source_created_at', 'source_expires_at', 'preview_created_at', 'preview_expires_at', 'intent_expires_at' ) as $field ) {
			if ( ! $this->integer( $value[ $field ], true ) ) {
				return array();
			}
		}
		if ( ! $this->integer( $intent_expires_at, true ) || $intent_expires_at !== $value['intent_expires_at'] || $intent_expires_at <= $now || $intent_expires_at > $now + 300
			|| $value['source_created_at'] > $value['preview_created_at'] || $value['preview_created_at'] > $now || $value['preview_expires_at'] <= $now
			|| $value['source_created_at'] + 3600 !== $value['source_expires_at'] || $intent_expires_at > $value['preview_created_at'] + 300
			|| min( $value['preview_created_at'] + 900, $value['source_expires_at'], $intent_expires_at ) !== $value['preview_expires_at'] ) {
			return array();
		}
		$canonical = array();
		foreach ( self::PREVIEW_KEYS as $field ) {
			if ( 'preview_fingerprint' !== $field ) {
				$canonical[ $field ] = $value[ $field ];
			}
		}
		if ( ! hash_equals( hash( 'sha256', wp_json_encode( $canonical ) ), $value['preview_fingerprint'] ) ) {
			return array();
		}
		return array_merge( $canonical, array( 'preview_fingerprint' => $value['preview_fingerprint'] ) );
	}

	/**
	 * Require only the supported version, bound identity and literal-false flags.
	 *
	 * @param array  $value Exact-shaped evidence.
	 * @param string $expected_uuid Persisted UUID.
	 * @param string $expected_key Persisted key ID.
	 * @return bool
	 */
	private function common( array $value, $expected_uuid, $expected_key ) {
		return 1 === $value['contract_version'] && 'client_owned_v1' === $value['mode']
			&& $this->uuid( $expected_uuid ) && $expected_uuid === $value['site_uuid']
			&& is_string( $expected_key ) && 1 === preg_match( '/^[A-Za-z0-9_.-]{1,128}$/D', $expected_key ) && $expected_key === $value['key_id']
			&& $this->integer( $value['authority_revision'] ) && $this->cadence( $value['current_cadence'] ) && $this->hash( $value['authority_fingerprint'] )
			&& false === $value['dispatch_supported'] && false === $value['execution_supported'];
	}

	/**
	 * Reject unknown and nested fields, rather than coercing future contracts.
	 *
	 * @param mixed $value Evidence.
	 * @param array $keys Allowed fields.
	 * @return bool
	 */
	private function exact_keys( $value, array $keys ) {
		return is_array( $value ) && count( $value ) === count( $keys ) && ! array_diff( array_keys( $value ), $keys );
	}

	/**
	 * Bound arithmetic without numeric-string coercion.
	 *
	 * @param mixed $value Counter/time.
	 * @param bool  $positive Require positive timestamp.
	 * @return bool
	 */
	private function integer( $value, $positive = false ) {
		return is_int( $value ) && $value >= ( $positive ? 1 : 0 ) && $value < PHP_INT_MAX - 3600;
	}

	/**
	 * Validate UUID.
	 *
	 * @param mixed $value UUID.
	 * @return bool
	 */
	private function uuid( $value ) {
		return is_string( $value ) && 1 === preg_match( '/^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/D', $value );
	}

	/**
	 * Validate fingerprint.
	 *
	 * @param mixed $value Fingerprint.
	 * @return bool
	 */
	private function hash( $value ) {
		return is_string( $value ) && 1 === preg_match( '/^[a-f0-9]{64}$/D', $value );
	}

	/**
	 * Validate the bounded cadence domain.
	 *
	 * @param mixed $value Cadence.
	 * @return bool
	 */
	private function cadence( $value ) {
		return in_array( $value, array( 'every_15_minutes', 'every_30_minutes', 'hourly' ), true );
	}
}
