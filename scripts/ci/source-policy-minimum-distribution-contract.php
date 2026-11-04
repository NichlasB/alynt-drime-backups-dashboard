<?php
/** Fixed proof-entry/evidence DATA, never a native or production factory.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Validates the private inherited job frame before core/credentials/native IO.
 *
 * @param array $env Explicit environment DATA.
 * @return bool Exact separately approved route, not runtime qualification.
 */
function adbm_distribution_entry( array $env ) {
	$fixed = array(
		'GITHUB_ACTIONS'                  => 'true',
		'GITHUB_EVENT_NAME'               => 'workflow_dispatch',
		'GITHUB_REPOSITORY'               => 'NichlasB/alynt-drime-backups-dashboard',
		'GITHUB_REF'                      => 'refs/heads/master',
		'GITHUB_WORKFLOW_REF'             => 'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-minimum-distribution.yml@refs/heads/master',
		'GITHUB_ACTOR'                    => 'NichlasB',
		'RUNNER_OS'                       => 'Linux',
		'RUNNER_ENVIRONMENT'              => 'github-hosted',
		'GITHUB_RUN_ATTEMPT'              => '1',
		'MINIMUM_MODEL'                   => 'trusted-distribution-legacy-v1',
		'DISTRIBUTION_EXECUTION_APPROVED' => 'true',
	);
	foreach ( $fixed as $key => $value ) {
		if ( ( $env[ $key ] ?? null ) !== $value ) {
			return false;
		}
	}
	return is_string( $env['GITHUB_SHA'] ?? null ) && 1 === preg_match( '/\A[a-f0-9]{40}\z/', $env['GITHUB_SHA'] )
		&& ( $env['EXPECTED_SHA'] ?? null ) === $env['GITHUB_SHA'] && is_string( $env['GITHUB_RUN_ID'] ?? null )
		&& 1 === preg_match( '/\A[1-9][0-9]{0,19}\z/', $env['GITHUB_RUN_ID'] )
		&& ! isset( $env['MINIMUM_SCOPE'] ) && empty( $env['DOCKER_HOST'] ) && empty( $env['DOCKER_CONTEXT'] );
}

/** Exact source/run/candidate correlation; no observed version alias is accepted.
 *
 * @param array $evidence Fixed worker evidence, never a request grant.
 * @param array $env Private inherited job DATA, not credentials.
 * @return bool Recognized source-bound evidence only, never native authority.
 */
function adbm_distribution_evidence( array $evidence, array $env ) {
	$fixed = array(
		'model'         => 'trusted-distribution-legacy-v1',
		'platform'      => 'Linux',
		'arch'          => 'x86_64',
		'php'           => '7.4.33',
		'client'        => 'mysqlnd 7.4.33',
		'core_commit'   => 'cc101b64012b16d087780657a2b828ccd7794a63',
		'runtime_image' => 'sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4',
		'server_image'  => 'sha256:14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1',
		'initial_mode'  => 0,
	);
	if ( ! adbm_distribution_entry( $env ) || 12 !== count( $evidence ) || array_diff( array_keys( $evidence ), array_merge( array_keys( $fixed ), array( 'head', 'image', 'run' ) ) ) ) {
		return false;
	}
	foreach ( $fixed as $key => $value ) {
		if ( ( $evidence[ $key ] ?? null ) !== $value ) {
			return false;
		}
	}
	foreach ( array(
		'head'  => 40,
		'image' => 64,
		'run'   => 32,
	) as $key => $length ) {
		if ( ! is_string( $evidence[ $key ] ?? null ) || 1 !== preg_match( '/\A[a-f0-9]{' . $length . '}\z/', $evidence[ $key ] ) ) {
			return false;
		}
	}
	return $evidence['head'] === $env['GITHUB_SHA'] && ( $env['MINIMUM_IMAGE'] ?? null ) === $evidence['image']
		&& substr( hash( 'sha256', $env['GITHUB_RUN_ID'] . ':' . $env['GITHUB_RUN_ATTEMPT'] ), 0, 32 ) === $evidence['run'];
}

/** Keeps the old scoped lab distinct from unchanged default legacy admission.
 *
 * @param array $evidence Source-owned proof correlation, not a public grant.
 * @return string Fixed construction branch only.
 * @throws RuntimeException For unknown or mismatched evidence before allocation.
 */
function adbm_minimum_binding_mode( array $evidence ) {
	$distribution = defined( 'ADB_MINIMUM_DISTRIBUTION' ) && true === ADB_MINIMUM_DISTRIBUTION;
	if ( ! $distribution && 'trusted-lab-functional-v1' === ( $evidence['model'] ?? null ) ) {
		return 'lab'; // The existing private scope still validates every old evidence field.
	}
	if ( $distribution && adbm_distribution_evidence( $evidence, getenv() ) ) {
		return 'default'; // No scope, accepted server alias, helper override or ready/facts injection.
	}
	throw new RuntimeException( 'Unknown minimum qualification model' );
}
