<?php
/**
 * Profile and relevant-hook consistency DATA, not a public native grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/class-source-policy-error.php';

/** No method here discovers a site, opens a connection or qualifies a real profile. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile {
	/**
	 * Validates private provider facts; the production provider remains absent.
	 *
	 * @param mixed       $facts Provider DATA, never request input.
	 * @param string      $table Fixed connection table binding.
	 * @param object|null $query_context Closed private receiver context, not a caller grant.
	 * @return array|WP_Error
	 */
	public static function admit( $facts, $table, $query_context = null ) {
		global $wp_object_cache, $wp_filter;
		foreach ( array( 'all', 'incompatible_sql_modes' ) as $hook ) {
			if ( isset( $wp_filter[ $hook ] ) && ! empty( $wp_filter[ $hook ]->callbacks ) ) {
				return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
			}
		}
		$required = array( 'core', 'server', 'database', 'table', 'schema', 'autocommit', 'single_site', 'dropins', 'cache', 'bounded_session', 'hooks' );
		if ( ! is_array( $facts ) || count( $required ) !== count( $facts ) || array_diff( $required, array_keys( $facts ) ) ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		if ( ! is_string( $table ) || strlen( $table ) > 64 || ! preg_match( '/\A[A-Za-z0-9_]+options\z/', $table )
			|| $facts['table'] !== $table || ! is_string( $facts['database'] ) || ! preg_match( '/\A[A-Za-z0-9_-]{1,64}\z/', $facts['database'] )
			|| ! in_array( $facts['core'], array( '6.0', '7.1.3' ), true ) || '10.6.23-MariaDB' !== $facts['server']
			|| 'options4-unique-name-InnoDB' !== $facts['schema'] || true !== $facts['autocommit'] || true !== $facts['single_site']
			|| false !== $facts['dropins'] || 'core-process' !== $facts['cache'] || true !== $facts['bounded_session'] ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		$fingerprint = self::fingerprint();
		if ( Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $fingerprint ) || ! is_string( $facts['hooks'] ) || ! hash_equals( $fingerprint, $facts['hooks'] )
			|| ! is_object( $wp_object_cache ) || 'WP_Object_Cache' !== get_class( $wp_object_cache ) || ! function_exists( 'wp_using_ext_object_cache' ) || false !== wp_using_ext_object_cache()
			|| ( null === $query_context ? ( isset( $wp_filter['query'] ) && ! empty( $wp_filter['query']->callbacks ) ) : ( ! $query_context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context || ! $query_context->valid() ) ) ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		return array(
			'owner'    => hash( 'sha256', $facts['database'] . ':' . $table ),
			'autoload' => '6.0' === $facts['core'] ? 'no' : 'off',
			'states'   => '6.0' === $facts['core'] ? array( 'yes', 'no' ) : array( 'yes', 'no', 'on', 'off', 'auto', 'auto-on', 'auto-off' ),
			'hooks'    => $fingerprint,
		);
	}

	/**
	 * Inspects bounded hook metadata without invoking callbacks or autoloaders.
	 *
	 * @param bool $without_query Private warm-up comparison excludes the owned registry change.
	 * @return string|WP_Error Consistency DATA only; matching it is NOT qualification.
	 */
	public static function fingerprint( $without_query = false ) {
		global $wp_filter;
		$hooks = self::hook_names();
		if ( isset( $wp_filter ) && ! is_array( $wp_filter ) ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		$frames = '';
		foreach ( $hooks as $hook ) {
			if ( $without_query && 'query' === $hook ) {
				continue;
			}
			$frames .= strlen( $hook ) . ':' . $hook;
			if ( ! isset( $wp_filter[ $hook ] ) ) {
				continue;
			}
			$registry = $wp_filter[ $hook ];
			if ( ! is_object( $registry ) || 'WP_Hook' !== get_class( $registry ) || ! is_array( $registry->callbacks ) || count( $registry->callbacks ) > 32 ) {
				return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
			}
			$count = 0;
			foreach ( $registry->callbacks as $priority => $entries ) {
				if ( ! is_int( $priority ) || ! is_array( $entries ) ) {
					return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
				}
				foreach ( $entries as $entry ) {
					if ( ++$count > 64 || ! is_array( $entry ) || ! isset( $entry['function'], $entry['accepted_args'] ) || ! is_int( $entry['accepted_args'] ) || $entry['accepted_args'] < 0 || $entry['accepted_args'] > 4 ) {
						return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
					}
					$identity = self::callback_identity( $entry['function'] );
					if ( null === $identity ) {
						return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
					}
					$part    = $priority . ':' . $entry['accepted_args'] . ':' . $identity;
					$frames .= strlen( $part ) . ':' . $part;
				}
			}
		}
		return hash( 'sha256', $frames );
	}

	/** Relevant hook-name DATA, not callback qualification.
	 *
	 * @return array
	 */
	public static function hook_names() {
		$name = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
		return array( 'sanitize_key', 'pre_option', "pre_option_$name", "option_$name", "default_option_$name", "sanitize_option_$name", "pre_update_option_$name", 'pre_update_option', 'update_option', "update_option_$name", 'updated_option', 'add_option', "add_option_$name", 'added_option', 'alloptions', 'pre_cache_alloptions', 'pre_wp_load_alloptions', 'wp_autoload_values_to_autoload', 'all', 'incompatible_sql_modes', 'query' );
	}

	/**
	 * Identifies a callable's metadata; never calls is_callable/autoload/__toString.
	 *
	 * @param mixed $callback Metadata slot.
	 * @return string|null
	 */
	private static function callback_identity( $callback ) {
		if ( is_string( $callback ) && strlen( $callback ) <= 191 ) {
			return 'function:' . $callback;
		}
		if ( is_object( $callback ) ) {
			return 'object:' . get_class( $callback ) . ':' . spl_object_hash( $callback );
		}
		if ( is_array( $callback ) && array( 0, 1 ) === array_keys( $callback ) && is_string( $callback[1] ) && strlen( $callback[1] ) <= 191 ) {
			$target = is_object( $callback[0] ) ? get_class( $callback[0] ) . ':' . spl_object_hash( $callback[0] ) : ( is_string( $callback[0] ) ? $callback[0] : null );
			return null === $target || strlen( $target ) > 256 ? null : 'method:' . $target . ':' . $callback[1];
		}
		return null;
	}
}
