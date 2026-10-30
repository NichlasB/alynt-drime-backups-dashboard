<?php
/**
 * Socket-free expected Linux minimum-lab DATA, never runtime admission.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** No IO, runtime receiver, scope mint, provider or native qualification. */
final class Alynt_Drime_Backups_Dashboard_Linux_Minimum_Tuple {
	/** Static DATA only; no instance can confer authority. */
	private function __construct() {}

	/**
	 * Returns one expected candidate row, not observed artifact/build identity.
	 *
	 * @return array
	 */
	public static function template() {
		return array(
			'format'        => 'ADBLMT1',
			'platform'      => 'Linux',
			'arch'          => 'x86_64',
			'php'           => '7.4.33',
			'client'        => 'mysqlnd 7.4.33',
			'core'          => '6.0',
			'commit'        => 'cc101b64012b16d087780657a2b828ccd7794a63',
			'tree'          => 'a3bd7a4074a4c95c8af3b969346726273d68e167',
			'runtime_image' => 'sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4',
			'server_image'  => 'sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5',
			'server'        => '10.11.18-MariaDB-ubu2204',
			'transport'     => 'tcp_loopback',
			'host'          => '127.0.0.1',
			'port'          => 3306,
			'socket'        => null,
			'db_host'       => '127.0.0.1:3306',
			'initial_mode'  => 0,
		);
	}

	/**
	 * Checks exact expected DATA without coercion or invoking supplied objects.
	 *
	 * @param mixed $row Supplied expected DATA.
	 * @return bool Consistency only.
	 */
	public static function consistent( $row ) {
		$fixed = self::template();
		if ( ! self::shape( $row, array_keys( $fixed ) ) ) {
			return false;
		}
		foreach ( $fixed as $key => $value ) {
			if ( ( $row[ $key ] ?? null ) !== $value ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Hashes canonical fixed ordering only after strict expected-row validation.
	 *
	 * @param mixed $row Supplied candidate descriptor.
	 * @return string|false Correlation digest, never an artifact attestation.
	 */
	public static function digest( $row ) {
		if ( ! self::consistent( $row ) ) {
			return false;
		}
		$bytes = self::canonical();
		return is_string( $bytes ) ? hash( 'sha256', $bytes ) : false;
	}

	/**
	 * Encodes only this fixed ASCII row; not a general JSON encoder or input API.
	 *
	 * PHP 7.4 -n need not load JSON. Preserve the existing canonical JSON bytes
	 * without loading extensions, coercing supplied DATA or changing the domain.
	 *
	 * @return string|false Fixed canonical bytes; refuse unsafe future source edits.
	 */
	private static function canonical() {
		$parts = array();
		foreach ( self::template() as $key => $value ) {
			if ( ! is_string( $key ) || 1 !== preg_match( '/\A[a-z_]{1,32}\z/', $key ) ) {
				return false;
			}
			if ( is_string( $value ) && 1 === preg_match( '/\A[A-Za-z0-9_.: -]{1,128}\z/', $value ) ) {
				$encoded = '"' . $value . '"';
			} elseif ( is_int( $value ) ) {
				$encoded = (string) $value;
			} elseif ( null === $value ) {
				$encoded = 'null';
			} else {
				return false; // No fallback encoder, object conversion or escaping guess.
			}
			$parts[] = '"' . $key . '":' . $encoded;
		}
		return '{' . implode( ',', $parts ) . '}';
	}

	/**
	 * Compares supplied transcript DATA to independent parent expectations.
	 *
	 * @param mixed $tuple Expected tuple DATA, not observations.
	 * @param mixed $wire Bounded decoded transcript DATA.
	 * @param mixed $expected Parent's independent expected transcript DATA.
	 * @return bool Correlation only; matching claims do not establish provenance.
	 */
	public static function correlates( $tuple, $wire, $expected ) {
		$hash = self::digest( $tuple );
		if ( false === $hash || ! self::transcript( $wire, $hash ) || ! self::transcript( $expected, $hash ) ) {
			return false;
		}
		foreach ( array( 'event', 'head', 'source_image', 'run', 'pid', 'tuple_hash' ) as $key ) {
			if ( $wire[ $key ] !== $expected[ $key ] ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Validates finite decoded DATA before accessing values or comparing identity.
	 *
	 * @param mixed $row Untrusted decoded DATA.
	 * @param array $keys Fixed source-owned key set.
	 * @return bool
	 */
	private static function shape( $row, array $keys ) {
		if ( ! is_array( $row ) || count( $keys ) !== count( $row ) ) {
			return false;
		}
		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $row ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Checks bounded scalars only; performs no observation or ownership grant.
	 *
	 * @param mixed  $row Supplied transcript DATA.
	 * @param string $hash Canonical expected tuple digest.
	 * @return bool
	 */
	private static function transcript( $row, $hash ) {
		if ( ! self::shape( $row, array( 'event', 'head', 'source_image', 'run', 'pid', 'tuple_hash' ) ) ) {
			return false;
		}
		foreach ( array(
			'head'         => 40,
			'source_image' => 64,
			'run'          => 32,
		) as $key => $length ) {
			if ( ! is_string( $row[ $key ] ) || strlen( $row[ $key ] ) !== $length || 1 !== preg_match( '/\A[a-f0-9]+\z/', $row[ $key ] ) ) {
				return false;
			}
		}
		return 'tuple-data' === $row['event'] && $hash === $row['tuple_hash']
			&& is_int( $row['pid'] ) && $row['pid'] > 0 && $row['pid'] <= 2147483647;
	}
}
