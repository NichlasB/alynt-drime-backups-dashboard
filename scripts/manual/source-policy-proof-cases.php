<?php
/**
 * Positive writes, stale interleavings, malformed bytes and strict creation proof.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.PHP.DiscouragedPHPFunctions -- Fixed owned table and primitive fixture values, no application options.
if ( ! defined( 'ADB_POLICY_PROOF' ) ) {
	exit( 1 );
}

/**
 * Resets only one pre-existing owned row, clearing the private core option cache.
 *
 * @param array|null $map Primitive map; null represents absence.
 * @param string     $autoload Exact core fixture state.
 * @return void
 */
function adbp_seed( $map, $autoload = 'off' ) {
	global $wpdb;
	adbp_check( false !== $wpdb->query( 'DELETE FROM ' . $wpdb->options ), 'owned reset succeeded' );
	if ( null !== $map ) {
		adbp_check(
			1 === $wpdb->insert(
				$wpdb->options,
				array(
					'option_name'  => Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES,
					'option_value' => serialize( $map ),
					'autoload'     => $autoload,
				)
			),
			'owned primitive seed'
		);
	}
	adbp_check( adbp_invalidate(), 'private cache reset' );
}

/**
 * Asserts an exact error rather than truthiness or an unavailable-only outcome.
 *
 * @param mixed  $result Candidate result.
 * @param string $code Required error.
 * @return void
 */
function adbp_error( $result, $code ) {
	adbp_check( is_wp_error( $result ) && $code === $result->get_error_code(), 'exact ' . $code );
}

/**
 * Exercises real statements and core reads with exact non-target preservation.
 *
 * @return void
 */
function adbp_storage_cases() {
	global $wpdb, $groups, $queries;
	$name = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
	$base = array( 9 => array( 'wpvivid' => 'external_optional' ) );
	foreach ( array( 'off', 'no', 'yes', 'on', 'auto', 'auto-on', 'auto-off' ) as $autoload ) {
		adbp_seed( $base, $autoload );
		adbp_check( get_option( $name ) === $base, 'genuine core read before update' );
		$before = adbp_observe();
		$count  = $queries;
		adbp_check( array( 'status' => 'saved' ) === adbp_save( $before['model']->revision(), 7, 'external_optional' ), 'positive acknowledged update' );
		adbp_check( 2 === $queries - $count, 'one admission read and one mutation' );
		$after = adbp_observe();
		adbp_check(
			'off' === $after['row']['autoload'] && array(
				9 => $base[9],
				7 => $base[9],
			) === $after['map'],
			'exact winner plus untouched non-target and non-autoload'
		);
		adbp_check( get_option( $name ) === $after['map'], 'core cache sees acknowledged value after invalidation' );
		$count = $queries;
		adbp_error( adbp_save( $before['model']->revision(), 7, 'external_optional' ), 'source_policy_conflict' );
		adbp_check( 1 === $queries - $count, 'stale already-satisfied intent is read-only conflict' );
		adbp_error( adbp_save( $before['model']->revision(), 8, 'external_optional' ), 'source_policy_conflict' );
		adbp_check( array( 'status' => 'unchanged' ) === adbp_save( $after['model']->revision(), 7, 'external_optional' ), 'fresh exact no-op' );
	}
	$groups[] = 'core-autoload-cache-update-stale-noop';
	adbp_seed( null );
	adbp_check( false === get_option( $name ), 'negative core cache primed' );
	$absent = adbp_observe();
	adbp_check( array( 'status' => 'unchanged' ) === adbp_save( $absent['model']->revision(), 7, 'required' ), 'missing required remains absent' );
	adbp_check( null === adbp_observe()['row'], 'no-op creates no row' );
	adbp_check( array( 'status' => 'saved' ) === adbp_save( $absent['model']->revision(), 7, 'external_optional' ), 'strict positive creation' );
	adbp_check( array( 7 => array( 'wpvivid' => 'external_optional' ) ) === get_option( $name ), 'negative cache invalidated after insert' );
	adbp_error( adbp_save( $absent['model']->revision(), 8, 'external_optional' ), 'source_policy_conflict' );
	adbp_seed( array() );
	adbp_check( $absent['model']->revision() !== adbp_observe()['model']->revision(), 'present-empty differs from absent' );
	$groups[] = 'strict-create-and-absence';
	adbp_seed(
		array(
			'007' => array( 'WPVIVID' => 'external_optional' ),
			9     => $base[9],
		)
	);
	$aliases = adbp_observe();
	adbp_check( array( 'status' => 'saved' ) === adbp_save( $aliases['model']->revision(), 8, 'external_optional' ), 'native save accepts primitive historical aliases' );
	adbp_check(
		array(
			7 => $base[9],
			9 => $base[9],
			8 => $base[9],
		) === adbp_observe()['map'],
		'alias normalization preserves every effective non-target policy'
	);
	$groups[] = 'native-alias-map-preservation';
	foreach ( array( 'not serialized', 'O:8:"stdClass":0:{}', str_repeat( 'x', 16385 ) ) as $malformed ) {
		adbp_seed( array() );
		adbp_check( 1 === $wpdb->update( $wpdb->options, array( 'option_value' => $malformed ), array( 'option_name' => $name ) ), 'owned malformed fixture installed' );
		$count = $queries;
		adbp_error( adbp_save( str_repeat( 'a', 64 ), 7, 'external_optional' ), 'source_policy_invalid' );
		adbp_check( 1 === $queries - $count, 'malformed storage never written' );
	}
	$groups[] = 'malformed-bounded-storage';
}
