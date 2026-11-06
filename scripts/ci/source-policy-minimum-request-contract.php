<?php
/**
 * Fixed genuine-request proof DATA/admission, never an application grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// Pure definitions only; this dependency never starts an operation.
require_once __DIR__ . '/source-policy-minimum-distribution-contract.php';

/**
 * Admit only the exact separately approved request-proof job.
 *
 * @param array $env Exact inherited job DATA.
 * @return bool
 */
function adbg_entry( array $env ) {
	return adbm_distribution_entry( $env ) && 'genuine-session-request-v1' === ( $env['MINIMUM_OPERATION'] ?? null )
		&& 'true' === ( $env['GENUINE_REQUEST_APPROVED'] ?? null )
		&& ( ! array_key_exists( 'PRODUCTION_JOIN_APPROVED', $env ) || 'false' === $env['PRODUCTION_JOIN_APPROVED'] );
}

/**
 * Private identity DATA must be safe before any library load or transcript.
 *
 * @param array $env Inherited environment.
 * @return bool
 */
function adbg_identity( array $env ) {
	foreach ( array( 'MINIMUM_IMAGE', 'MINIMUM_SERVER_CID' ) as $key ) {
		if ( ! isset( $env[ $key ] ) || ! is_string( $env[ $key ] ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $env[ $key ] ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Accept only source-owned diagnostic enums/digests, never an exception message.
 *
 * @param mixed $wire Private terminal.
 * @return bool
 */
function adbg_failure_valid( $wire ) {
	return is_string( $wire ) && strlen( $wire ) <= 256 && 1 === preg_match( '/\AADBMGRF1\|(parent|child)\|(?:[0-9]|1[0-9]|2[0-2])\|(audit|ambient|setup|auth|entry|verify|cleanup)\|(RuntimeException|Error|TypeError|ValueError|ParseError|ArgumentCountError|ReflectionException|other)\|[a-f0-9]{64}\n\z/', $wire );
}

/**
 * Capture the first bounded cause before cleanup; private values are never read.
 *
 * @param string    $role Fixed source-owned role.
 * @param int       $index Fixed case index.
 * @param string    $stage Fixed stage.
 * @param Throwable $error Caught failure, class only.
 * @return string Private fixed-shape failure.
 */
function adbg_failure( $role, $index, $stage, Throwable $error ) {
	$kind = get_class( $error );
	if ( ! in_array( $kind, array( 'RuntimeException', 'Error', 'TypeError', 'ValueError', 'ParseError', 'ArgumentCountError', 'ReflectionException' ), true ) ) {
		$kind = 'other';
	}
	$wire = 'ADBMGRF1|' . $role . '|' . $index . '|' . $stage . '|' . $kind . '|' . ( $GLOBALS['adbg_assertion'] ?? str_repeat( '0', 64 ) ) . "\n";
	return adbg_failure_valid( $wire ) ? $wire : 'ADBMGRF1|parent|0|cleanup|other|' . str_repeat( '0', 64 ) . "\n";
}

/**
 * Fresh process for each source-owned request case.
 *
 * @return array
 */
function adbg_cases() {
	return array( 'view', 'save', 'noop', 'stale', 'cookie-missing', 'cookie-malformed', 'cookie-expired', 'cookie-tampered', 'cookie-user', 'cookie-destroyed', 'incapable', 'nonce-action', 'nonce-user', 'nonce-session', 'nonce-empty', 'nonce-compound', 'nonce-expired', 'site-compound', 'site-padded', 'revision-bad', 'mode-compound', 'record-missing', 'record-wrong' );
}

/**
 * Non-secret terminal projection; counts never stand in for validation.
 *
 * @param string $scenario Fixed case.
 * @return string
 */
function adbg_expected( $scenario ) {
	if ( ! in_array( $scenario, adbg_cases(), true ) ) {
		return 'invalid-case';
	}
	if ( 0 === strpos( $scenario, 'cookie-' ) || 'incapable' === $scenario ) {
		return 'source_policy_forbidden';
	}
	if ( 0 === strpos( $scenario, 'record-' ) ) {
		return 'source_policy_unavailable';
	}
	if ( in_array( $scenario, array( 'view', 'save', 'noop', 'stale' ), true ) ) {
		return array(
			'view'  => 'view',
			'save'  => 'saved',
			'noop'  => 'unchanged',
			'stale' => 'source_policy_conflict',
		)[ $scenario ];
	}
	return 'source_policy_invalid';
}
