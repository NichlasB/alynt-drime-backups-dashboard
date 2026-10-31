<?php
/**
 * Finite private endpoint consistency, never connection authority.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** No IO, default endpoint, provider or caller-selected executor. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile {
	/**
	 * Checks a complete frozen tuple before any core parser or driver allocation.
	 *
	 * @param array  $frame Private internally composed settings.
	 * @param string $platform Observed platform; production callers use PHP_OS_FAMILY.
	 * @return bool Consistency only, not a native grant.
	 */
	public static function valid( array $frame, $platform ) {
		foreach ( array( 'transport', 'db_host', 'host', 'port', 'socket', 'core' ) as $key ) {
			if ( ! array_key_exists( $key, $frame ) ) {
				return false;
			}
		}
		if ( 'tcp_loopback' === $frame['transport'] ) {
			$host = '::1' === $frame['host'] ? '[::1]' : $frame['host'];
			return in_array( $frame['core'], array( '6.0', '7.1.3' ), true )
				&& in_array( $frame['host'], array( '127.0.0.1', '::1' ), true ) && null === $frame['socket']
				&& is_int( $frame['port'] ) && $frame['port'] > 0 && $frame['port'] <= 65535
				&& $host . ':' . $frame['port'] === $frame['db_host'];
		}
		return 'unix_socket' === $frame['transport'] && 'Linux' === $platform && '7.1.4' === $frame['core']
			&& 'localhost' === $frame['host'] && null === $frame['port'] && is_string( $frame['socket'] )
			&& strlen( $frame['socket'] ) <= 96 && 1 === preg_match( '#\A/(?:[A-Za-z0-9_-][A-Za-z0-9_.-]*/)*[A-Za-z0-9_-][A-Za-z0-9_.-]*\z#', $frame['socket'] )
			&& 'localhost:' . $frame['socket'] === $frame['db_host'];
	}

	/**
	 * Matches fixed core parser DATA without coercing any returned component.
	 *
	 * @param array $frame Validated private endpoint; never caller connection authority.
	 * @param mixed $parsed Result of the already source-verified core parser.
	 * @return bool Exact tuple match, including version-specific port type.
	 */
	public static function matches_parsed( array $frame, $parsed ) {
		if ( ! self::valid( $frame, PHP_OS_FAMILY ) ) {
			return false;
		}
		// Pinned 6.0 preserves regex strings; pinned 7.1.3 returns absint ports.
		// Render only our validated integer. Never cast or loosely compare parser DATA.
		$port = '6.0' === $frame['core'] ? (string) $frame['port'] : $frame['port'];
		return array( $frame['host'], $port, $frame['socket'], '::1' === $frame['host'] ) === $parsed;
	}

	/**
	 * Explicit candidate server row; never a version-prefix or installed-binary grant.
	 *
	 * @param mixed $core Exact internally bound core row.
	 * @return string|false Expected connected server identity.
	 */
	public static function server( $core ) {
		if ( in_array( $core, array( '6.0', '7.1.3' ), true ) ) {
			return '10.6.23-MariaDB';
		}
		return '7.1.4' === $core ? '10.11.18-MariaDB-ubu2204' : false;
	}
}
