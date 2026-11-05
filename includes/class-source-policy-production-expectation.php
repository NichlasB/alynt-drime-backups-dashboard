<?php
/**
 * One exact legacy compatibility row, DATA only, not a context or native mint.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/class-source-policy-bootstrap-profile.php';

/** Trusted host/service consistency, never binary or distribution attestation. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Expectation {
	/**
	 * Classifies only complete already-private bootstrap settings.
	 *
	 * @param mixed $frame Fixed private settings, not accepted by a public provider.
	 * @param mixed $runtime Source-owned immutable observations, not request DATA.
	 * @return array|false Non-secret expectation only.
	 */
	public static function select( $frame, $runtime ) {
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::settings( $frame ) ? self::row( $frame, $runtime ) : false;
	}

	/**
	 * Active identity check without reconstructing a fake password-bearing frame.
	 *
	 * @param array $projection Frozen non-secret settings.
	 * @param mixed $runtime Frozen runtime DATA.
	 * @param mixed $expected Previously selected row.
	 * @return bool
	 */
	public static function matches( array $projection, $runtime, $expected ) {
		$keys = array( 'core', 'database', 'table', 'host', 'port', 'charset', 'collate', 'transport', 'socket', 'db_host' );
		if ( count( $keys ) !== count( $projection ) || array_diff( $keys, array_keys( $projection ) ) ) {
			return false;
		}
		$actual = self::row( $projection, $runtime );
		return false !== $actual && $actual === $expected;
	}

	/**
	 * No origin mixing, normalization, observation-selected fallback or IO.
	 *
	 * @param array $frame Private complete or non-secret identity.
	 * @param mixed $runtime Fixed runtime facts.
	 * @return array|false
	 */
	private static function row( array $frame, $runtime ) {
		$fixed = array(
			'platform' => 'Linux',
			'arch'     => 'x86_64',
			'php'      => '7.4.33',
			'client'   => 'mysqlnd 7.4.33',
			'mode'     => 0,
		);
		if ( ! is_array( $runtime ) || count( $fixed ) !== count( $runtime ) || array_diff( array_keys( $runtime ), array_keys( $fixed ) ) ) {
			return false;
		}
		foreach ( $fixed as $key => $value ) {
			if ( ( $runtime[ $key ] ?? null ) !== $value ) {
				return false;
			}
		}
		if ( ! Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::valid( $frame, 'Linux' )
			|| '6.0' !== ( $frame['core'] ?? null ) || 'tcp_loopback' !== $frame['transport'] || '127.0.0.1' !== $frame['host']
			|| 'utf8' !== ( $frame['charset'] ?? null ) || '' !== ( $frame['collate'] ?? null )
			|| ! is_string( $frame['database'] ?? null ) || 1 !== preg_match( '/\A[A-Za-z0-9_-]{1,64}\z/', $frame['database'] )
			|| ! is_string( $frame['table'] ?? null ) || strlen( $frame['table'] ) > 64 || 1 !== preg_match( '/\A[A-Za-z0-9_]+options\z/', $frame['table'] ) ) {
			return false;
		}
		return array(
			'core'      => '6.0',
			'server'    => '5.5.5-10.6.23-MariaDB',
			'collation' => 'utf8mb4_unicode_ci',
			'database'  => $frame['database'],
			'table'     => $frame['table'],
		);
	}
}
