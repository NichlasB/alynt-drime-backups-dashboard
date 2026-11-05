<?php
/**
 * Fixed private declaration consistency; no constructor or public provider.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Used only by the closed trusted-context implementation. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context_Source {
	/** No raw filesystem diagnostic or replacement of the caller's error handler.
	 *
	 * @param array $policy Fixed policy.
	 * @return bool
	 * @throws RuntimeException Caught by capture/valid on IO warnings.
	 */
	private function checked_declarations( array $policy ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Scoped warning-to-refusal conversion, never suppression or raw filesystem output.
		set_error_handler(
			static function () {
				throw new RuntimeException( 'Policy source unavailable' );
			}
		);
		try {
			return $this->declarations( $policy );
		} finally {
			restore_error_handler();
		}
	}

	/**
	 * Fixed source and empty non-query observer policy, not a live fingerprint grant.
	 *
	 * @param array $policy Privately selected fixed expectations.
	 * @return bool
	 */
	private function declarations( array $policy ) {
		$ambient = $GLOBALS['wpdb'] ?? null;
		if ( ! $this->owned( $this->receiver ) || ! is_object( $ambient ) || 'wpdb' !== get_class( $ambient )
			|| $ambient === $this->receiver || ( isset( $GLOBALS['wp_filter'] ) && ! is_array( $GLOBALS['wp_filter'] ) )
			|| count( $policy['pins'] ) > 12 || ! $policy['pins'] ) {
			return false;
		}
		foreach ( $policy['pins'] as $file => $digest ) {
			$path = $policy['root'] . $file;
			if ( ! is_string( $digest ) || 64 !== strlen( $digest ) || ! is_file( $path ) || filesize( $path ) > 524288 || hash_file( 'sha256', $path ) !== $digest ) {
				return false;
			}
		}
		$binding = array(
			'driver_file'    => $policy['driver'],
			'observers'      => array(),
			'observer_files' => array(),
		);
		foreach ( $policy['files'] as $key => $file ) {
			$binding[ $key ] = realpath( $policy['root'] . $file );
		}
		if ( ( new ReflectionClass( $ambient ) )->getFileName() !== $binding['core_file']
			|| ! class_exists( 'WP_Hook', false ) || ( new ReflectionClass( 'WP_Hook' ) )->getFileName() !== $binding['hook_file'] ) {
			return false;
		}
		foreach ( Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::hook_names() as $hook ) {
			if ( 'query' !== $hook && isset( $GLOBALS['wp_filter'][ $hook ] ) ) {
				$registry = $GLOBALS['wp_filter'][ $hook ];
				if ( ! is_object( $registry ) || 'WP_Hook' !== get_class( $registry ) || array() !== $registry->callbacks ) {
					return false;
				}
			}
		}
		if ( null !== $this->binding && $this->binding !== $binding ) {
			return false;
		}
		$this->binding = $binding;
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::source( $this->receiver, $binding );
	}

	/** Only exact owned declaration; never invoke an unknown cleanup collaborator.
	 *
	 * @param mixed $receiver Private receiver.
	 * @return bool
	 */
	private function owned( $receiver ) {
		return is_object( $receiver ) && 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' === get_class( $receiver )
			&& ( $GLOBALS['wpdb'] ?? null ) !== $receiver
			&& ( new ReflectionClass( $receiver ) )->getFileName() === realpath( dirname( __DIR__ ) . '/class-source-policy-no-reconnect-connection.php' );
	}
}
