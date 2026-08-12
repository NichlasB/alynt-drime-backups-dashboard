<?php
/**
 * Structured diagnostics event log settings.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages event-log settings, severity levels, and threshold checks.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Event_Log_Settings {
	/**
	 * Gets sanitized diagnostics settings.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string,mixed>
	 */
	public function settings() {
		if ( null !== $this->settings_cache ) {
			return $this->settings_cache;
		}

		$stored = function_exists( 'get_option' ) ? get_option( self::OPTION_SETTINGS, array() ) : array();

		$this->settings_cache = $this->sanitize_settings( is_array( $stored ) ? $stored : array() );

		return $this->settings_cache;
	}

	/**
	 * Gets supported severity levels.
	 *
	 * @since 0.1.0
	 *
	 * @return array<int,string>
	 */
	public function severity_levels() {
		return array_keys( $this->severity_order );
	}

	/**
	 * Saves diagnostics settings with autoload disabled.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string,mixed> $settings Raw settings.
	 * @return bool
	 */
	public function update_settings( array $settings ) {
		if ( ! function_exists( 'update_option' ) ) {
			return false;
		}

		$sanitized = $this->sanitize_settings( $settings );

		if ( $sanitized === $this->settings() ) {
			return true;
		}

		$updated = (bool) update_option( self::OPTION_SETTINGS, $sanitized, false );

		if ( $updated ) {
			$this->settings_cache = $sanitized;
		}

		return $updated;
	}

	/**
	 * Returns whether structured diagnostics logging is enabled.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$settings = $this->settings();

		return ! empty( $settings['enabled'] );
	}

	/**
	 * Sanitizes settings.
	 *
	 * @param array<string,mixed> $settings Raw settings.
	 * @return array<string,mixed>
	 */
	private function sanitize_settings( array $settings ) {
		$minimum_level = isset( $settings['minimum_level'] ) ? sanitize_key( $settings['minimum_level'] ) : 'warning';

		if ( ! isset( $this->severity_order[ $minimum_level ] ) ) {
			$minimum_level = 'warning';
		}

		return array(
			'enabled'        => ! empty( $settings['enabled'] ),
			'minimum_level'  => $minimum_level,
			'retention_days' => max( 1, min( 90, absint( isset( $settings['retention_days'] ) ? $settings['retention_days'] : 14 ) ) ),
			'max_events'     => max( 10, min( 1000, absint( isset( $settings['max_events'] ) ? $settings['max_events'] : 200 ) ) ),
		);
	}

	/**
	 * Normalizes severity.
	 *
	 * @param string $level Severity.
	 * @return string
	 */
	private function normalize_level( $level ) {
		$level = sanitize_key( $level );

		return isset( $this->severity_order[ $level ] ) ? $level : 'info';
	}

	/**
	 * Determines whether a level meets the configured threshold.
	 *
	 * @param string $level Level.
	 * @param string $minimum Minimum level.
	 * @return bool
	 */
	private function meets_threshold( $level, $minimum ) {
		return $this->severity_order[ $level ] >= $this->severity_order[ $this->normalize_level( $minimum ) ];
	}
}
