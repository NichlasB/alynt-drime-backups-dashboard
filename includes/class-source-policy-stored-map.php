<?php
/**
 * Bounded primitive stored-map decoding DATA; never a trusted observation factory.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** No native read, option API fallback or magic-method-enabled decode. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map {
	/**
	 * Decodes a bounded raw row, then delegates primitive/reference checks to DATA.
	 *
	 * @param mixed  $row Fixed owner row, null for actual absence.
	 * @param string $owner Private storage binding.
	 * @param array  $states Qualified autoload representations.
	 * @return array|WP_Error Internal DATA only; never public form output.
	 */
	public static function decode( $row, $owner, array $states ) {
		$map = array();
		if ( null !== $row ) {
			if ( ! is_array( $row ) || array( 'option_value', 'autoload' ) !== array_keys( $row ) || ! is_string( $row['option_value'] ) || strlen( $row['option_value'] ) > 16384 || ! is_string( $row['autoload'] ) || ! in_array( $row['autoload'], $states, true ) ) {
				return new WP_Error( 'source_policy_invalid' );
			}
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Stored-byte warnings become fixed invalid DATA; previous handler always restored.
			set_error_handler(
				function () {
					throw new RuntimeException( 'Invalid stored policy encoding' );
				}
			);
			try {
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- Bounded bytes/depth, no classes; primitive/reference checks and exact re-encoding follow before use.
				$map = unserialize(
					$row['option_value'],
					array(
						'allowed_classes' => false,
						'max_depth'       => 3,
					)
				);
			} catch ( Throwable $error ) {
				return new WP_Error( 'source_policy_invalid' );
			} finally {
				restore_error_handler();
			}
		}
		$model = Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe( $owner, null !== $row, null === $row ? '' : $row['option_value'], null === $row ? '' : $row['autoload'], $map );
		return is_wp_error( $model ) ? $model : array(
			'model' => $model,
			'map'   => $map,
			'row'   => $row,
		);
	}
}
