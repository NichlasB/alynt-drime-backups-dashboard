<?php
/**
 * Fixed private configuration capture; no constructor or public provider.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Used only by the closed trusted-context implementation. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context_Settings {
	/** Trusted constants/stock ambient only; no file bootstrap, filter or public DTO.
	 *
	 * @param bool $secrets Include transient bootstrap credentials only.
	 * @return array|null
	 * @throws RuntimeException Caught by capture/valid on parser diagnostics.
	 */
	private function settings( $secrets = true ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Do not emit parser/configuration diagnostics or suppress-and-continue.
		set_error_handler(
			static function () {
				throw new RuntimeException( 'Policy settings unavailable' );
			}
		);
		try {
			return $this->read_settings( $secrets );
		} finally {
			restore_error_handler();
		}
	}

	/** Read only after fixed source binding, returning solely to private capture.
	 *
	 * @param bool $secrets Include transient bootstrap credentials only.
	 * @return array|null
	 */
	private function read_settings( $secrets = true ) {
		$names = array( 'DB_NAME', 'DB_HOST', 'DB_CHARSET', 'DB_COLLATE' );
		foreach ( $secrets ? array_merge( $names, array( 'DB_USER', 'DB_PASSWORD' ) ) : $names as $name ) {
			if ( ! defined( $name ) || ! is_string( constant( $name ) ) || strlen( constant( $name ) ) > 1024 ) {
				return null;
			}
		}
		$parsed = $this->ambient->parse_db_host( DB_HOST ); // Only after exact stock source/declaration binding.
		if ( ! is_array( $parsed ) || 4 !== count( $parsed ) || array( 0, 1, 2, 3 ) !== array_keys( $parsed ) ) {
			return null;
		}
		$public = get_object_vars( $this->ambient );
		if ( ! isset( $public['options'] ) || ! is_string( $public['options'] ) || strlen( $public['options'] ) > 64 ) {
			return null;
		}
		$port = $parsed[1];
		if ( '6.0' === $this->policy['core'] && is_string( $port ) && 1 === preg_match( '/\A[0-9]{1,5}\z/', $port ) ) {
			$port = (int) $port; // Exact archived parser type, still checked against the full raw tuple below.
		}
		$frame = array(
			'core'      => $this->policy['core'],
			'database'  => DB_NAME,
			'table'     => $this->ambient->options,
			'host'      => $parsed[0],
			'port'      => $port,
			'charset'   => DB_CHARSET,
			'collate'   => DB_COLLATE,
			'transport' => null === $parsed[2] ? 'tcp_loopback' : 'unix_socket',
			'socket'    => $parsed[2],
			'db_host'   => DB_HOST,
		);
		if ( $secrets ) {
			$frame['user']     = DB_USER;
			$frame['password'] = DB_PASSWORD;
		}
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::matches_parsed( $frame, $parsed ) ? $frame : null;
	}
}
