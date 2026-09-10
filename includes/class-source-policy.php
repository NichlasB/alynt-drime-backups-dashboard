<?php
/**
 * Dashboard-owned backup source monitoring policy.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.19
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores per-site dashboard-only source monitoring overrides.
 *
 * These policies affect dashboard classification and display only. They do not
 * contact client sites, mutate backups, change WPvivid settings, or grant Drime
 * access.
 *
 * @since 0.1.19
 */
class Alynt_Drime_Backups_Dashboard_Source_Policy {
	const OPTION_SOURCE_POLICIES = 'alynt_drime_backups_dashboard_source_policies';
	const SOURCE_WPVIVID         = 'wpvivid';
	const MODE_REQUIRED          = 'required';
	const MODE_EXTERNAL_OPTIONAL = 'external_optional';

	/**
	 * In-memory policy override used by focused tests.
	 *
	 * @var array<string,array<string,string>>|null
	 */
	private $policies = null;

	/**
	 * Constructor.
	 *
	 * @param array<string,array<string,string>>|null $policies Optional policy map.
	 */
	public function __construct( $policies = null ) {
		if ( is_array( $policies ) ) {
			$this->policies = $this->sanitize_policies( $policies );
		}
	}

	/**
	 * Returns whether a source should be treated as external/optional.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param string              $source_key Source key.
	 * @return bool
	 */
	public function source_is_external_optional( array $site, $source_key ) {
		return self::MODE_EXTERNAL_OPTIONAL === $this->source_mode( $site, $source_key );
	}

	/**
	 * Gets the effective monitoring mode for a source.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param string              $source_key Source key.
	 * @return string
	 */
	public function source_mode( array $site, $source_key ) {
		$site_id    = $this->site_id_from_row( $site );
		$source_key = $this->sanitize_source_key( $source_key );

		if ( $site_id <= 0 || '' === $source_key ) {
			return self::MODE_REQUIRED;
		}

		$policies = $this->all_policies();
		$key      = (string) $site_id;

		if ( isset( $policies[ $key ][ $source_key ] ) && self::MODE_EXTERNAL_OPTIONAL === $policies[ $key ][ $source_key ] ) {
			return self::MODE_EXTERNAL_OPTIONAL;
		}

		return self::MODE_REQUIRED;
	}

	/**
	 * Updates one source monitoring mode.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $source_key Source key.
	 * @param string $mode Monitoring mode.
	 * @return bool
	 */
	public function set_source_mode( $site_id, $source_key, $mode ) {
		$site_id    = absint( $site_id );
		$source_key = $this->sanitize_source_key( $source_key );
		$mode       = $this->sanitize_mode( $mode );

		if ( $site_id <= 0 || '' === $source_key ) {
			return false;
		}

		$policies = $this->all_policies();
		$key      = (string) $site_id;
		$before   = $policies;

		if ( self::MODE_EXTERNAL_OPTIONAL === $mode ) {
			if ( ! isset( $policies[ $key ] ) || ! is_array( $policies[ $key ] ) ) {
				$policies[ $key ] = array();
			}

			$policies[ $key ][ $source_key ] = self::MODE_EXTERNAL_OPTIONAL;
		} else {
			if ( isset( $policies[ $key ][ $source_key ] ) ) {
				unset( $policies[ $key ][ $source_key ] );
			}

			if ( isset( $policies[ $key ] ) && empty( $policies[ $key ] ) ) {
				unset( $policies[ $key ] );
			}
		}

		$policies = $this->sanitize_policies( $policies );

		if ( $before === $policies ) {
			return true;
		}

		if ( ! function_exists( 'update_option' ) ) {
			$this->policies = $policies;
			return true;
		}

		return update_option( self::OPTION_SOURCE_POLICIES, $policies, false );
	}

	/**
	 * Gets all stored policies.
	 *
	 * @return array<string,array<string,string>>
	 */
	public function all_policies() {
		if ( null !== $this->policies ) {
			return $this->policies;
		}

		$policies = function_exists( 'get_option' ) ? get_option( self::OPTION_SOURCE_POLICIES, array() ) : array();

		return $this->sanitize_policies( is_array( $policies ) ? $policies : array() );
	}

	/**
	 * Gets a stable site ID from a row-like array.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return int
	 */
	private function site_id_from_row( array $site ) {
		if ( isset( $site['id'] ) ) {
			return absint( $site['id'] );
		}

		if ( isset( $site['dashboard_site_id'] ) ) {
			return absint( $site['dashboard_site_id'] );
		}

		return 0;
	}

	/**
	 * Sanitizes one source key.
	 *
	 * @param string $source_key Source key.
	 * @return string
	 */
	private function sanitize_source_key( $source_key ) {
		$source_key = sanitize_key( $source_key );

		return self::SOURCE_WPVIVID === $source_key ? $source_key : '';
	}

	/**
	 * Sanitizes one policy mode.
	 *
	 * @param string $mode Mode.
	 * @return string
	 */
	private function sanitize_mode( $mode ) {
		return self::MODE_EXTERNAL_OPTIONAL === sanitize_key( $mode ) ? self::MODE_EXTERNAL_OPTIONAL : self::MODE_REQUIRED;
	}

	/**
	 * Sanitizes the stored policy map.
	 *
	 * @param array<mixed,mixed> $policies Raw policies.
	 * @return array<string,array<string,string>>
	 */
	private function sanitize_policies( array $policies ) {
		$clean = array();

		foreach ( $policies as $site_id => $source_modes ) {
			$site_id = absint( $site_id );

			if ( $site_id <= 0 || ! is_array( $source_modes ) ) {
				continue;
			}

			foreach ( $source_modes as $source_key => $mode ) {
				$source_key = $this->sanitize_source_key( (string) $source_key );
				$mode       = $this->sanitize_mode( (string) $mode );

				if ( '' === $source_key || self::MODE_EXTERNAL_OPTIONAL !== $mode ) {
					continue;
				}

				if ( ! isset( $clean[ (string) $site_id ] ) ) {
					$clean[ (string) $site_id ] = array();
				}

				$clean[ (string) $site_id ][ $source_key ] = $mode;
			}
		}

		return $clean;
	}
}
