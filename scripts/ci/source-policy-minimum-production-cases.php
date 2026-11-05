<?php
/**
 * Nine distinct production-origin join groups, not the old hooked lab body.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Source-owned primitive fixture comparisons only, never untrusted objects or deserialization.
if ( ! defined( 'ABSPATH' ) || ! function_exists( 'adbb_check' ) ) {
	exit( 1 );
}
/** One ordered body, with no non-query observer or synthetic active admission.
 *
 * @return void
 */
function adbp_cases() {
	global $groups, $stage, $last_commands;
	$base  = array( 9 => array( 'wpvivid' => 'external_optional' ) );
	$stage = 'positive';
	adba_seed( $base );
	$view = adbp_run( 'view' );
	adbb_check( is_array( $view ) && array_keys( $view ) === array( 'mode', 'revision' ) && 'required' === $view['mode'] && 1 === $last_commands, 'production join positive view' );
	$old = $view['revision'];
	++$groups;
	adbb_check( 'saved' === adbp_run( 'save', array( $old, 'external_optional' ) ) && 2 === $last_commands, 'production join positive conditional update' );
	$want = $base + array( 7 => array( 'wpvivid' => 'external_optional' ) );
	adbb_check(
		array(
			array(
				'option_value' => serialize( $want ),
				'autoload'     => 'no',
			),
		) === adba_row(),
		'physical non-target bytes and autoload preserved'
	);
	++$groups;
	$stage = 'noop-stale';
	$view  = adbp_run( 'view' );
	adbb_check( 'unchanged' === adbp_run( 'save', array( $view['revision'], 'external_optional' ) ) && 1 === $last_commands, 'genuine production no-op' );
	++$groups;
	adba_error( adbp_run( 'save', array( $old, 'external_optional' ) ), 'source_policy_conflict' );
	adbb_check(
		1 === $last_commands && array(
			array(
				'option_value' => serialize( $want ),
				'autoload'     => 'no',
			),
		) === adba_row(),
		'stale revision refuses before satisfied no-op'
	);
	++$groups;
	$stage = 'update';
	adbb_check( 'saved' === adbp_run( 'save', array( $view['revision'], 'required' ) ) && 2 === $last_commands, 'conditional target removal' );
	adbb_check(
		array(
			array(
				'option_value' => serialize( $base ),
				'autoload'     => 'no',
			),
		) === adba_row(),
		'non-target policy survives removal'
	);
	++$groups;
	$stage = 'duplicate';
	adba_seed( null );
	$absent = adbp_run( 'view' );
	adbb_check( 'saved' === adbp_run( 'save', array( $absent['revision'], 'external_optional' ) ) && 2 === $last_commands, 'strict first-row insert' );
	++$groups;
	$stage      = 'race';
	$before     = adbp_run( 'view' );
	$competitor = $want + array( 12 => array( 'wpvivid' => 'external_optional' ) );
	adba_seed( $competitor );
	adba_error( adbp_run( 'save', array( $before['revision'], 'required' ) ), 'source_policy_conflict' );
	adbb_check(
		1 === $last_commands && array(
			array(
				'option_value' => serialize( $competitor ),
				'autoload'     => 'no',
			),
		) === adba_row(),
		'intervening physical writer is not overwritten'
	);
	++$groups;
	$stage   = 'post-change';
	$owner   = adbp_owner();
	$context = adbp_read( $owner, 'production_context' );
	adbb_check( true === $owner->close() && false === $context->valid( $owner ) && false === $owner->query( 'SELECT never-dispatched' ), 'closed genuine owner revokes active context and query dispatch' );
	adb0_released( $owner );
	++$groups;
	$stage = 'transform-reentry';
	$last  = adbp_run( 'view' ); // adbp_run always asserts no-query provider replay after release.
	adbb_check( is_array( $last ) && 'external_optional' === $last['mode'] && 1 === $last_commands, 'fresh positive availability after revoked predecessor and replay refusals' );
	++$groups;
}
