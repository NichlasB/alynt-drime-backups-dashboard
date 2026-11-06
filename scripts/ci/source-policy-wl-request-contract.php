<?php
/**
 * Fixed W/L private request admission DATA, never an application grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// Shared fixed case/diagnostic DATA only; never the M admission route.
require_once __DIR__ . '/source-policy-minimum-request-contract.php';

/** Exact operator/job envelope before any core, credentials or native work.
 *
 * @param array $env Inherited private DATA.
 * @param array $args Fixed command arguments.
 * @return bool
 */
function adbwl_entry( array $env, array $args ) {
	$profile = $env['WL_REQUEST_PROFILE'] ?? null;
	if ( ! in_array( $profile, array( 'w', 'l' ), true ) || 'true' !== ( $env['WL_REQUEST_APPROVED'] ?? null )
		|| ! in_array( $env['WL_REQUEST_MODE'] ?? null, array( 'audit', 'proof' ), true )
		|| 3 !== count( $args ) || '--owned-wl-request-child' !== $args[1] || ! is_string( $args[2] )
		|| ! preg_match( '/\A(?:[0-9]|1[0-9]|2[0-2])\z/', $args[2] ) ) {
		return false;
	}
	foreach ( array(
		'WL_REQUEST_HEAD'  => 40,
		'WL_REQUEST_RUN'   => 32,
		'WL_REQUEST_IMAGE' => 64,
	) as $key => $size ) {
		if ( ! is_string( $env[ $key ] ?? null ) || 1 !== preg_match( '/\A[a-f0-9]{' . $size . '}\z/', $env[ $key ] ) ) {
			return false;
		}
	}
	if ( ( 'audit' === $env['WL_REQUEST_MODE'] && '0' !== $args[2] ) || ( $env['WL_REQUEST_PREFIX'] ?? null ) !== 'adbm_' . substr( $env['WL_REQUEST_RUN'], 0, 12 ) . '_' ) {
		return false;
	}
	if ( 'proof' === $env['WL_REQUEST_MODE'] && ( ! is_string( $env['WL_REQUEST_MODULES'] ?? null ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $env['WL_REQUEST_MODULES'] ) ) ) {
		return false;
	}
	if ( 'w' === $profile ) {
		return 'plugin-tester-local-only' === ( $env['WL_REQUEST_TARGET'] ?? null );
	}
	return 'true' === ( $env['QUALIFICATION_GENUINE_REQUEST_APPROVED'] ?? null ) && 'true' === ( $env['GITHUB_ACTIONS'] ?? null )
		&& 'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-linux-qualification.yml@refs/heads/master' === ( $env['GITHUB_WORKFLOW_REF'] ?? null )
		&& 'workflow_dispatch' === ( $env['GITHUB_EVENT_NAME'] ?? null ) && 'NichlasB/alynt-drime-backups-dashboard' === ( $env['GITHUB_REPOSITORY'] ?? null )
		&& 'refs/heads/master' === ( $env['GITHUB_REF'] ?? null ) && 'NichlasB' === ( $env['GITHUB_ACTOR'] ?? null )
		&& '1' === ( $env['GITHUB_RUN_ATTEMPT'] ?? null ) && is_string( $env['GITHUB_RUN_ID'] ?? null ) && 1 === preg_match( '/\A[1-9][0-9]*\z/', $env['GITHUB_RUN_ID'] )
		&& $env['WL_REQUEST_HEAD'] === ( $env['EXPECTED_SHA'] ?? null ) && $env['WL_REQUEST_HEAD'] === ( $env['GITHUB_SHA'] ?? null ) // phpcs:ignore WordPress.PHP.YodaConditions -- Exact source/job identity, not normalization.
		&& substr( hash( 'sha256', $env['GITHUB_RUN_ID'] . ':1' ), 0, 32 ) === $env['WL_REQUEST_RUN'];
}

/** Fixed non-secret, correlated terminal; no raw query/exception material.
 *
 * @param array  $rows Frozen source rows.
 * @param string $modules Module inventory digest.
 * @return string
 */
function adbwl_audit_wire( $rows, $modules ) {
	return json_encode(
		array(
			'event'              => 'audit',
			'profile'            => getenv( 'WL_REQUEST_PROFILE' ),
			'run'                => getenv( 'WL_REQUEST_RUN' ),
			'image'              => hash( 'sha256', json_encode( $rows ) ),
			'modules'            => $modules,
			'checks'             => $GLOBALS['adbg_checks'],
			'native_connections' => 0,
			'sql_queries'        => 0,
		)
	) . "\n";
}

/** Only emitted after the case and owned ambient release succeed.
 *
 * @param int $index Case index.
 * @param int $records Record commands.
 * @param int $connects Native connections.
 * @param int $commands Native commands.
 * @return string
 */
function adbwl_case_wire( $index, $records, $connects, $commands ) {
	return json_encode(
		array(
			'event'    => 'request-closed',
			'profile'  => getenv( 'WL_REQUEST_PROFILE' ),
			'run'      => getenv( 'WL_REQUEST_RUN' ),
			'image'    => getenv( 'WL_REQUEST_IMAGE' ),
			'pid'      => getmypid(),
			'index'    => $index,
			'checks'   => $GLOBALS['adbg_checks'],
			'setup'    => $GLOBALS['adbg_ledger']['setup'],
			'auth'     => $GLOBALS['adbg_ledger']['auth'],
			'records'  => $records,
			'connects' => $connects,
			'commands' => $commands,
			'closed'   => true,
		)
	) . "\n";
}

/** Map only the shared fixed failure contract into a distinct W/L diagnostic.
 *
 * @param string $wire Source-owned failure.
 * @return string
 */
function adbwl_failure_wire( $wire ) {
	if ( ! adbg_failure_valid( $wire ) ) {
		return '';
	}
	return adbwl_include_prefix( $wire ) . str_replace( 'ADBMGRF1|', 'ADBWLRF1|' . getenv( 'WL_REQUEST_PROFILE' ) . '|', $wire );
}

/** Optional bounded include cause; legacy failure stays intact on malformed DATA.
 *
 * @param string $wire Already validated base failure.
 * @return string
 */
function adbwl_include_prefix( $wire ) {
	if ( ! adbg_failure_valid( $wire ) ) {
		return '';
	}
	$fact = $GLOBALS['adbwl_include_failure'] ?? null;
	if ( 'l' !== getenv( 'WL_REQUEST_PROFILE' ) || 'audit' !== getenv( 'WL_REQUEST_MODE' ) || ! is_array( $fact )
		|| array_keys( $fact ) !== array( 'ordinal', 'origin', 'reason', 'path_digest' ) || ! is_int( $fact['ordinal'] ) || $fact['ordinal'] < 0 || $fact['ordinal'] > 511
		|| ! in_array( $fact['origin'], array( 'repo', 'core', 'foreign', 'unresolved' ), true ) || ! in_array( $fact['reason'], array( 'scope', 'size', 'stat' ), true )
		|| ! is_string( $fact['path_digest'] ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $fact['path_digest'] )
		|| ( 'scope' !== $fact['reason'] && ! in_array( $fact['origin'], array( 'repo', 'core' ), true ) ) ) {
		return '';
	}
	$parts = explode( '|', rtrim( $wire, "\n" ) );
	if ( 'child' !== $parts[1] || '0' !== $parts[2] || 'audit' !== $parts[3]
		|| ( hash( 'sha256', 'finite actual include closure' ) !== $parts[5] && ! ( 'stat' === $fact['reason'] && str_repeat( '0', 64 ) === $parts[5] ) ) ) {
		return '';
	}
	return implode( '|', array( 'ADBWLIC1', 'l', '0', 'audit', $fact['ordinal'], $fact['origin'], $fact['reason'], $fact['path_digest'] ) ) . "\n";
}
