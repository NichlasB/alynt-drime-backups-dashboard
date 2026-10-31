<?php
/**
 * Report-OFF positive, conflict and failure cases; disposable clone only.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Approved once-only owned proof and native counters.
if ( ! defined( 'ABSPATH' ) || ! function_exists( 'adb0_owner' ) ) {
	exit( 1 );
}

/** One ordered native body; no automatic retry or synthetic success path.
 *
 * @return void
 */
function adb0_cases() {
	global $groups, $stage, $scenario, $last_commands, $nested, $events, $request;
	$name  = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
	$hooks = array( "sanitize_option_$name", "pre_update_option_$name", 'pre_update_option', 'update_option', "update_option_$name", 'updated_option', 'add_option', "add_option_$name", 'added_option' );
	foreach ( $hooks as $hook ) {
		add_filter( $hook, 'adba_hook', 10, 3 );
	}
	$base     = array( 9 => array( 'wpvivid' => 'external_optional' ) );
	$scenario = 'normal';
	$stage    = 'positive';
	adba_seed( $base );
	$view = adb0_run( 'view' );
	adbb_check( is_array( $view ) && array_keys( $view ) === array( 'mode', 'revision' ) && 'required' === $view['mode'], 'real view positive control' );
	$old = $view['revision'];
	foreach ( array( 'alloptions', 'notoptions', $name ) as $key ) {
		wp_cache_set( $key, 'owned sentinel', 'options' );
	}
	wp_cache_set( 'owned-unrelated', 'keep', 'options' );
	adbb_check( 'saved' === adb0_run( 'save', array( $old, 'external_optional' ) ) && 2 === $last_commands, 'OFF actual single save' );
	$want = $base + array( 7 => array( 'wpvivid' => 'external_optional' ) );
	adbb_check(
		array(
			array(
				'option_value' => serialize( $want ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Source-owned primitive fixture comparison.
				'autoload'     => 'no',
			),
		) === adba_row(),
		'physical non-target bytes and autoload preserved'
	);
	foreach ( array( 'alloptions', 'notoptions', $name ) as $key ) {
		$found = null;
		wp_cache_get( $key, 'options', false, $found );
		adbb_check( false === $found, 'genuine private cache invalidated' );
	}
	adbb_check( 'keep' === wp_cache_get( 'owned-unrelated', 'options' ) && 1 === count( array_keys( $events, 'updated_option', true ) ), 'once-only success observer and unrelated cache retained' );
	++$groups;
	$stage = 'noop-stale';
	$view  = adb0_run( 'view' );
	adbb_check( 'unchanged' === adb0_run( 'save', array( $view['revision'], 'external_optional' ) ) && 1 === $last_commands && ! in_array( 'updated_option', $events, true ), 'one-read no-op' );
	adba_error( adb0_run( 'save', array( $old, 'external_optional' ) ), 'source_policy_conflict' );
	adbb_check( 1 === $last_commands, 'stale before satisfied no-op' );
	++$groups;
	$stage = 'update';
	$view  = adb0_run( 'view' );
	adbb_check( 'saved' === adb0_run( 'save', array( $view['revision'], 'required' ) ) && 2 === $last_commands, 'OFF conditional removal' );
	++$groups;
	$stage    = 'race';
	$view     = adb0_run( 'view' );
	$scenario = 'race';
	adba_error( adb0_run( 'save', array( $view['revision'], 'external_optional' ) ), 'source_policy_conflict' );
	adbb_check( 4 === $last_commands && ! in_array( 'updated_option', $events, true ), 'one failed CAS with no replay' );
	++$groups;
	$stage    = 'duplicate';
	$scenario = 'normal';
	adba_seed( null );
	$view     = adb0_run( 'view' );
	$scenario = 'duplicate';
	adba_error( adb0_run( 'save', array( $view['revision'], 'external_optional' ) ), 'source_policy_conflict' );
	adbb_check( 5 === $last_commands && ! in_array( 'added_option', $events, true ), 'definite duplicate allows one winner read not mutation replay' );
	++$groups;
	$stage    = 'transform-reentry';
	$scenario = 'normal';
	adba_seed( $base );
	$view     = adb0_run( 'view' );
	$scenario = 'transform';
	adba_error( adb0_run( 'save', array( $view['revision'], 'external_optional' ) ), 'source_policy_unavailable' );
	adbb_check( 1 === $last_commands, 'transform refused before write' );
	$scenario = 'reentry';
	adbb_check( 'saved' === adb0_run( 'save', array( $view['revision'], 'external_optional' ) ) && 2 === $last_commands, 'outer save survives refused reentry' );
	adba_error( $nested, 'source_policy_unavailable' );
	++$groups;
	$stage    = 'lock';
	$scenario = 'normal';
	adba_seed( $base );
	$view     = adb0_run( 'view' );
	$scenario = 'lock';
	try {
		adba_error( adb0_run( 'save', array( $view['revision'], 'external_optional' ) ), 'source_policy_storage_error' );
		adbb_check( 4 === $last_commands && ! in_array( 'updated_option', $events, true ), 'OFF bounded lock failure never replays' );
	} finally {
		adba_peer( 'ROLLBACK' );
	}
	++$groups;
	$stage    = 'post-change';
	$scenario = 'normal';
	$view     = adb0_run( 'view' );
	$scenario = 'post-change';
	adba_error( adb0_run( 'save', array( $view['revision'], 'external_optional' ) ), 'source_policy_write_uncertain' );
	adbb_check( 2 === $last_commands, 'post-commit changed admission stays uncertain' );
	++$groups;
	$stage    = 'errno';
	$scenario = 'normal';
	$owner    = adb0_owner();
	$table    = $request[2] . 'options';
	try {
		$before = (int) mysqli_get_client_stats()['com_query'];
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fixed owned table and fixed invalid syntax; no caller fragment.
		adbb_check(
			array(
				'affected' => false,
				'errno'    => 1064,
			) === $owner->execute( "UPDATE $table SET" ),
			'real syntax false/errno1064'
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Exact owned row, prepared fixed values only.
		$sql = $owner->prepare_checked( "INSERT INTO $table (option_name,option_value,autoload) VALUES (%s,%s,%s)", $name, 'owned', 'no' );
		adbb_check(
			array(
				'affected' => false,
				'errno'    => 1062,
			) === $owner->execute( $sql ) && true === $owner->ready,
			'real duplicate false/errno1062'
		);
		adba_peer( 'START TRANSACTION' );
		adba_peer( "SELECT option_id FROM $table FOR UPDATE" );
		try {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fixed owned row; bounded failure, no application identifier.
			$sql = $owner->prepare_checked( "UPDATE $table SET option_value=%s WHERE option_name=%s", 'owned', $name );
			adbb_check(
				array(
					'affected' => false,
					'errno'    => 1205,
				) === $owner->execute( $sql ) && true === $owner->ready,
				'real lock false/errno1205'
			);
		} finally {
			adba_peer( 'ROLLBACK' );
		}
		adbb_check( 6 === (int) mysqli_get_client_stats()['com_query'] - $before, 'fixed native errno cases no retry' );
	} finally {
		adbb_check( true === $owner->close(), 'direct errno owner release' );
		adb0_released( $owner );
	}
	++$groups;
	foreach ( $hooks as $hook ) {
		remove_filter( $hook, 'adba_hook', 10 );
	}
}
