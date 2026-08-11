<?php
/**
 * Public HTTPS origin validation helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalizes and validates public HTTPS origins for v1 pairing and polling.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Origin_Validator {
	/**
	 * Normalizes a public HTTPS origin.
	 *
	 * @since 0.1.0
	 *
	 * @param string $origin Raw origin or URL.
	 * @return string Empty string when invalid.
	 */
	public function normalize_public_https_origin( $origin ) {
		$origin = trim( (string) $origin );
		$parts  = parse_url( $origin );

		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return '';
		}

		if ( 'https' !== strtolower( (string) $parts['scheme'] ) ) {
			return '';
		}

		if ( isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] ) ) {
			return '';
		}

		if ( isset( $parts['path'] ) && '' !== $parts['path'] && '/' !== $parts['path'] ) {
			return '';
		}

		if ( isset( $parts['port'] ) && 443 !== absint( $parts['port'] ) ) {
			return '';
		}

		$host = strtolower( rtrim( (string) $parts['host'], '.' ) );

		if ( ! $this->is_public_hostname( $host ) ) {
			return '';
		}

		return 'https://' . $host;
	}

	/**
	 * Builds the fixed uploader status endpoint for a canonical origin.
	 *
	 * @since 0.1.0
	 *
	 * @param string $origin Canonical origin.
	 * @return string
	 */
	public function status_endpoint_for_origin( $origin ) {
		$origin = $this->normalize_public_https_origin( $origin );

		return '' === $origin ? '' : $origin . '/wp-json/alynt-drime-backups-uploader/v1/status';
	}

	/**
	 * Validates that an origin resolves only to public IP addresses.
	 *
	 * @since 0.1.0
	 *
	 * @param string        $origin Canonical or raw origin.
	 * @param callable|null $resolver Optional resolver that returns IP strings or dns_get_record()-style rows.
	 * @return bool
	 */
	public function resolved_origin_is_public( $origin, $resolver = null ) {
		$origin = $this->normalize_public_https_origin( $origin );

		if ( '' === $origin ) {
			return false;
		}

		$parts = parse_url( $origin );
		$host  = is_array( $parts ) && ! empty( $parts['host'] ) ? strtolower( (string) $parts['host'] ) : '';

		if ( ! $this->is_public_hostname( $host ) ) {
			return false;
		}

		$records = is_callable( $resolver ) ? call_user_func( $resolver, $host ) : $this->resolve_host_ips( $host );
		$ips     = $this->extract_ips( $records );

		if ( empty( $ips ) ) {
			return false;
		}

		foreach ( $ips as $ip ) {
			if ( ! $this->is_public_ip( $ip ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks whether a host is an allowed public hostname for v1.
	 *
	 * @param string $host Hostname.
	 * @return bool
	 */
	private function is_public_hostname( $host ) {
		if ( '' === $host || 'localhost' === $host || false !== strpos( $host, '..' ) || preg_match( '/(^|\.)local$/', $host ) ) {
			return false;
		}

		if ( strlen( $host ) > 253 ) {
			return false;
		}

		foreach ( explode( '.', $host ) as $label ) {
			if ( '' === $label || strlen( $label ) > 63 ) {
				return false;
			}
		}

		if ( false !== filter_var( $host, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		return (bool) preg_match( '/^[a-z0-9][a-z0-9.-]*[a-z0-9]$/', $host );
	}

	/**
	 * Resolves A and AAAA records for a hostname.
	 *
	 * @param string $host Hostname.
	 * @return array<int,mixed>
	 */
	private function resolve_host_ips( $host ) {
		$records = array();

		if ( function_exists( 'dns_get_record' ) && defined( 'DNS_A' ) && defined( 'DNS_AAAA' ) ) {
			$resolved = dns_get_record( $host, DNS_A + DNS_AAAA );
			$records  = is_array( $resolved ) ? $resolved : array();
		}

		if ( empty( $records ) && function_exists( 'gethostbynamel' ) ) {
			$resolved = gethostbynamel( $host );
			$records  = is_array( $resolved ) ? $resolved : array();
		}

		return $records;
	}

	/**
	 * Extracts IP strings from resolver output.
	 *
	 * @param mixed $records Resolver output.
	 * @return array<int,string>
	 */
	private function extract_ips( $records ) {
		if ( ! is_array( $records ) ) {
			return array();
		}

		$ips = array();

		foreach ( $records as $record ) {
			if ( is_string( $record ) ) {
				$ips[] = $record;
				continue;
			}

			if ( is_array( $record ) ) {
				if ( ! empty( $record['ip'] ) ) {
					$ips[] = (string) $record['ip'];
				}

				if ( ! empty( $record['ipv6'] ) ) {
					$ips[] = (string) $record['ipv6'];
				}
			}
		}

		return array_values( array_unique( $ips ) );
	}

	/**
	 * Checks whether an IP address is public and routable.
	 *
	 * @param string $ip IP address.
	 * @return bool
	 */
	private function is_public_ip( $ip ) {
		return false !== filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
	}
}
