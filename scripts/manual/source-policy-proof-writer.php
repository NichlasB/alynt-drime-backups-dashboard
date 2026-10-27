<?php
/**
 * Fixed fixture-only native candidate. No runtime loader, constructor or form wiring.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.PHP.DiscouragedPHPFunctions -- Explicit primitive fixture serialization and fixed owned-table native proof, never request data.
if ( PHP_SAPI !== 'cli' || ! defined( 'ADB_POLICY_PROOF' ) ) {
	exit( 1 );
}

/**
 * Obtains bounded raw bytes, not cached/filtered application option data.
 *
 * @return array|WP_Error Internal owned proof observation only.
 */
function adbp_observe() {
	global $wpdb;
	$name = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
	$row  = $wpdb->get_row( $wpdb->prepare( "SELECT IF(OCTET_LENGTH(option_value)<=16384,option_value,NULL) AS option_value,autoload FROM $wpdb->options WHERE option_name=%s AND BINARY option_name=BINARY %s LIMIT 1", $name, $name ), ARRAY_A );
	if ( '' !== $wpdb->last_error ) {
		return new WP_Error( 'source_policy_storage_error' );
	}
	$map = array();
	if ( null !== $row ) {
		if ( ! is_string( $row['option_value'] ) || ! in_array( $row['autoload'], array( 'yes', 'no', 'on', 'off', 'auto', 'auto-on', 'auto-off' ), true ) ) {
			return new WP_Error( 'source_policy_invalid' );
		}
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Bounded untrusted stored-byte warning becomes invalid DATA; handler always restored, no debug output.
		set_error_handler(
			function () {
				throw new RuntimeException( 'Invalid owned serialization' );
			}
		);
		try {
			$map = unserialize(
				$row['option_value'],
				array(
					'allowed_classes' => false,
					'max_depth'       => 8,
				)
			);
		} catch ( Throwable $error ) {
			return new WP_Error( 'source_policy_invalid' );
		} finally {
			restore_error_handler();
		}
	}
	$model = Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe( $wpdb->options, null !== $row, null === $row ? '' : $row['option_value'], null === $row ? '' : $row['autoload'], $map );
	return is_wp_error( $model ) ? $model : array(
		'model' => $model,
		'map'   => $map,
		'row'   => $row,
	);
}

/**
 * Invalidates only three relevant keys in the private genuine core cache.
 *
 * @return bool Confirmed absence in this process, NOT shared-cache acceptance.
 */
function adbp_invalidate() {
	foreach ( array( 'alloptions', 'notoptions', Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES ) as $key ) {
		wp_cache_delete( $key, 'options' );
		$found = null;
		wp_cache_get( $key, 'options', false, $found );
		if ( false !== $found ) {
			return false;
		}
	}
	return true;
}

/**
 * Executes one owned admission/mutation with no retry or production fallback.
 *
 * @param string $revision Expected DATA revision.
 * @param int    $site Site identity.
 * @param string $mode Requested mode.
 * @return array|WP_Error Fixed proof result, not a public native receipt API.
 */
function adbp_save( $revision, $site, $mode ) {
	global $wpdb;
	static $busy = false;
	$name        = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
	if ( $busy ) {
		return new WP_Error( 'source_policy_unavailable' );
	}
	foreach ( array( 'sanitize_key', 'pre_option', "pre_option_$name", "option_$name", "default_option_$name" ) as $hook ) {
		if ( false !== has_filter( $hook ) ) {
			return new WP_Error( 'source_policy_unavailable' );
		}
	}
	$busy      = true;
	$committed = false;
	$attempted = false;
	try {
		$before = adbp_observe();
		if ( is_wp_error( $before ) ) {
			return $before;
		}
		$plan = $before['model']->plan( $revision, $site, 'wpvivid', $mode );
		if ( is_wp_error( $plan ) ) {
			return $plan;
		}
		$value = sanitize_option( $name, $plan['policies'] );
		if ( 'insert' !== $plan['operation'] ) {
			$value = apply_filters( "pre_update_option_$name", $value, $before['map'], $name );
			$value = apply_filters( 'pre_update_option', $value, $name, $before['map'] );
		}
		// Value transformers are unavailable, never permission to discard non-target policies.
		if ( $value !== $plan['policies'] ) {
			return new WP_Error( 'source_policy_unavailable' );
		}
		if ( 'noop' === $plan['operation'] ) {
			return array( 'status' => 'unchanged' );
		}
		$bytes    = maybe_serialize( $value );
		$autoload = wp_determine_option_autoload_value( $name, $value, $bytes, false );
		if ( 'off' !== $autoload || strlen( $bytes ) > 16384 ) {
			return new WP_Error( 'source_policy_unavailable' );
		}
		if ( 'insert' === $plan['operation'] ) {
			do_action( 'add_option', $name, $value );
			$sql = $wpdb->prepare( "INSERT INTO $wpdb->options (option_name,option_value,autoload) VALUES (%s,%s,%s)", $name, $bytes, $autoload );
		} else {
			do_action( 'update_option', $name, $before['map'], $value );
			$row = $before['row'];
			$sql = $wpdb->prepare( adbp_update_template( $wpdb->options ), $bytes, $autoload, $name, $name, $row['option_value'], strlen( $row['option_value'] ), $row['autoload'], strlen( $row['autoload'] ) );
		}
		$attempted = true;
		$affected  = $wpdb->query( $sql );
		if ( false === $affected ) {
			// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_errno -- Read the genuine error code of the fixed owned statement; never infer duplicate/definite failure from message text.
			$errno = mysqli_errno( $wpdb->dbh );
			if ( 'insert' === $plan['operation'] && 1062 === $errno ) {
				$competing = adbp_observe(); // One bounded follow-up; no retry/upsert.
				return new WP_Error( ! is_wp_error( $competing ) && null !== $competing['row'] ? 'source_policy_conflict' : 'source_policy_write_uncertain' );
			}
			return new WP_Error( in_array( $errno, array( 1064, 1205, 1213 ), true ) ? 'source_policy_storage_error' : 'source_policy_write_uncertain' );
		}
		if ( 0 === $affected ) {
			return new WP_Error( 'source_policy_conflict' );
		}
		$committed = true;
		if ( 1 !== $affected || ! adbp_invalidate() ) {
			return new WP_Error( 'source_policy_write_uncertain' );
		}
		if ( 'insert' === $plan['operation'] ) {
			do_action( "add_option_$name", $name, $value );
			do_action( 'added_option', $name, $value );
		} else {
			do_action( "update_option_$name", $before['map'], $value, $name );
			do_action( 'updated_option', $name, $before['map'], $value );
		}
		return array( 'status' => 'saved' );
	} catch ( Throwable $error ) {
		return new WP_Error( $committed || $attempted ? 'source_policy_write_uncertain' : 'source_policy_unavailable' );
	} finally {
		$busy = false;
	}
}
