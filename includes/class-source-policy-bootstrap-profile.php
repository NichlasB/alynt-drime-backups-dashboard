<?php
/**
 * Private-frame consistency/source checks; no provider or native grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/class-source-policy-error.php';
require_once __DIR__ . '/class-source-policy-transport-profile.php';

/** Pure checks only. Private bindings are not distribution authentication. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile {
	/**
	 * Validates frozen, internally provisioned settings before allocation.
	 *
	 * @param mixed $frame Private setup DATA.
	 * @return bool
	 */
	public static function settings( $frame ) {
		$keys = array( 'core', 'database', 'table', 'host', 'port', 'user', 'password', 'charset', 'collate', 'transport', 'socket', 'db_host' );
		if ( ! is_array( $frame ) || count( $keys ) !== count( $frame ) || array_diff( $keys, array_keys( $frame ) ) ) {
			return false;
		}
		foreach ( array_diff( $keys, array( 'port', 'socket' ) ) as $key ) {
			if ( ! is_string( $frame[ $key ] ) || strlen( $frame[ $key ] ) > 1024 || false !== strpos( $frame[ $key ], "\0" ) ) {
				return false;
			}
		}
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::valid( $frame, PHP_OS_FAMILY )
			&& 1 === preg_match( '/\A[A-Za-z0-9_-]{1,64}\z/', $frame['database'] )
			&& strlen( $frame['table'] ) <= 64 && 1 === preg_match( '/\A[A-Za-z0-9_]+options\z/', $frame['table'] )
			&& 1 === preg_match( '/\A[A-Za-z0-9_.-]{1,64}\z/', $frame['user'] )
			&& in_array( $frame['charset'], array( 'utf8', 'utf8mb4' ), true ) && in_array( $frame['collate'], array( '', 'utf8mb4_unicode_520_ci' ), true )
			&& defined( 'DB_CHARSET' ) && DB_CHARSET === $frame['charset'] && defined( 'DB_COLLATE' ) && DB_COLLATE === $frame['collate']
			&& ! defined( 'WP_SETUP_CONFIG' ) && ( ! defined( 'WP_INSTALLING' ) || ! WP_INSTALLING )
			&& ( ! defined( 'WP_USE_EXT_MYSQL' ) || ! WP_USE_EXT_MYSQL ) && ( ! defined( 'MYSQL_CLIENT_FLAGS' ) || 0 === MYSQL_CLIENT_FLAGS )
			&& ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES );
	}

	/**
	 * Checks loaded declaration identities, never invokes an unknown collaborator.
	 *
	 * @param object $receiver Fixed private receiver.
	 * @param mixed  $binding Private expected source bindings, null by default.
	 * @return bool
	 */
	public static function source( $receiver, $binding ) {
		if ( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' !== get_class( $receiver ) || ( $GLOBALS['wpdb'] ?? null ) === $receiver
			|| ! is_array( $binding ) || ! isset( $binding['core_file'], $binding['observers'], $binding['observer_files'] ) || ! array_key_exists( 'driver_file', $binding )
			|| ! is_string( $binding['core_file'] ) || false === realpath( $binding['core_file'] )
			|| ! defined( 'MULTISITE' ) || false !== MULTISITE || ! defined( 'WP_CONTENT_DIR' ) || ! is_string( WP_CONTENT_DIR )
			|| ! isset( $binding['cache_file'], $binding['cache_api_file'] ) || ! is_object( $GLOBALS['wp_object_cache'] ?? null )
			|| 'WP_Object_Cache' !== get_class( $GLOBALS['wp_object_cache'] ) ) {
			return false;
		}
		foreach ( array( 'db.php', 'object-cache.php', 'advanced-cache.php' ) as $dropin ) {
			if ( file_exists( WP_CONTENT_DIR . '/' . $dropin ) ) {
				return false;
			}
		}
		if ( ! is_string( $binding['cache_file'] ) || false === realpath( $binding['cache_file'] )
			|| ( new ReflectionClass( 'WP_Object_Cache' ) )->getFileName() !== realpath( $binding['cache_file'] )
			|| ! is_string( $binding['cache_api_file'] ) || false === realpath( $binding['cache_api_file'] ) || ! function_exists( 'wp_using_ext_object_cache' )
			|| ( new ReflectionFunction( 'wp_using_ext_object_cache' ) )->getFileName() !== realpath( $binding['cache_api_file'] ) || false !== wp_using_ext_object_cache() ) {
			return false;
		}
		foreach ( array( '__construct', '__get', 'parse_db_host', 'init_charset', 'determine_charset', 'set_charset', 'set_sql_mode', 'has_cap', 'db_version', 'db_server_info', 'prepare', '_real_escape', 'placeholder_escape', 'add_placeholder_escape', 'remove_placeholder_escape', 'query', 'close' ) as $name ) {
			$method = new ReflectionMethod( 'wpdb', $name );
			if ( 'wpdb' !== $method->getDeclaringClass()->getName() || $method->getFileName() !== realpath( $binding['core_file'] ) ) {
				return false;
			}
			if ( '__construct' !== $name && ( new ReflectionMethod( $receiver, $name ) )->getDeclaringClass()->getName() !== ( in_array( $name, array( 'query', 'close' ), true ) ? get_class( $receiver ) : 'wpdb' ) ) {
				return false;
			}
		}
		foreach ( array( 'mysqli_init', 'mysqli_options', 'mysqli_real_connect', 'mysqli_get_client_info', 'mysqli_get_server_info', 'mysqli_errno', 'mysqli_query', 'mysqli_num_rows', 'mysqli_fetch_assoc', 'mysqli_free_result', 'mysqli_close' ) as $name ) {
			if ( ! function_exists( $name ) ) {
				return false; // Capability refusal only, never a backend-selection switch.
			}
			$function = new ReflectionFunction( $name );
			if ( null === $binding['driver_file'] ? ( ! $function->isInternal() || 'mysqli' !== $function->getExtensionName() )
				: ( ! is_string( $binding['driver_file'] ) || false === realpath( $binding['driver_file'] ) || $function->getFileName() !== realpath( $binding['driver_file'] ) ) ) {
				return false;
			}
		}
		$absint = function_exists( 'absint' ) ? new ReflectionFunction( 'absint' ) : null;
		return isset( $binding['parser_api_file'] ) && is_string( $binding['parser_api_file'] ) && null !== $absint
			&& false !== realpath( $binding['parser_api_file'] ) && $absint->getFileName() === realpath( $binding['parser_api_file'] )
			// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_get_client_info -- Capability observation only after source binding, never a connection or provider.
			&& false !== strpos( mysqli_get_client_info(), 'mysqlnd' ) && self::hooks( $binding ) && self::report_source( $binding['driver_file'] );
	}

	/** Validates the fixed shared-driver observer without executing unknown code.
	 *
	 * @param string|null $file Expected fixture file or native extension.
	 * @return bool
	 */
	public static function report_source( $file ) {
		if ( ! class_exists( 'mysqli_driver', false ) ) {
			return false;
		}
		$class = new ReflectionClass( 'mysqli_driver' );
		return null === $file ? ( $class->isInternal() && 'mysqli' === $class->getExtensionName() )
			: ( is_string( $file ) && false !== realpath( $file ) && $class->getFileName() === realpath( $file ) );
	}

	/** Reads shared reporting only from the already source-bound fixed observer.
	 *
	 * @param string|null $file Expected fixture file or native extension.
	 * @return int|false
	 */
	public static function report_mode( $file ) {
		try {
			return self::report_source( $file ) ? ( new mysqli_driver() )->report_mode : false;
		} catch ( Throwable $error ) {
			return false; // No raw driver diagnostic or alternate reporting posture.
		}
	}

	/**
	 * Requires exact privately qualified observer metadata, not a fresh fingerprint.
	 *
	 * @param array $binding Private expected source/observer graph.
	 * @return bool
	 */
	public static function hooks( array $binding ) {
		global $wp_filter;
		if ( ! is_array( $binding['observers'] ) || ! is_array( $binding['observer_files'] ) || Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::fingerprint() ) ) {
			return false;
		}
		foreach ( Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::hook_names() as $name ) {
			if ( 'query' === $name ) {
				continue; // Exact query receivers are independently bound by Query_Context.
			}
			$actual = isset( $wp_filter[ $name ] ) ? $wp_filter[ $name ]->callbacks : array();
			if ( in_array( $name, array( 'all', 'incompatible_sql_modes' ), true ) && $actual ) {
				return false;
			}
			if ( ( $binding['observers'][ $name ] ?? array() ) !== $actual ) {
				return false;
			}
			foreach ( $actual as $entries ) {
				foreach ( $entries as $entry ) {
					$callback = $entry['function'];
					if ( is_array( $callback ) && ! is_object( $callback[0] ) && ( ! is_string( $callback[0] ) || ! class_exists( $callback[0], false ) ) ) {
						return false; // Never autoload a callback while qualifying its metadata.
					}
					$definition = is_array( $callback ) ? new ReflectionMethod( $callback[0], $callback[1] ) : new ReflectionFunction( $callback );
					$file       = $definition->getFileName();
					if ( ! is_string( $file ) || ! isset( $binding['observer_files'][ $file ] ) || hash_file( 'sha256', $file ) !== $binding['observer_files'][ $file ] ) {
						return false;
					}
				}
			}
		}
		return true;
	}
}
