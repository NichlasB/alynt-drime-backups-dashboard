<?php
/**
 * Case setup/verification confined to the explicit W/L run's four owned tables.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL, WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Fixed run-owned identifiers/primitive seed maps and independent uncached readback, not an application query surface.
if ( ! defined( 'ADB_WL_REQUEST_PROOF' ) ) {
	exit( 1 ); }

/** A controller owns each direct child; no PHP descendants are launched.
 *
 * @param string $scenario Source-owned case.
 * @return array Non-target rows.
 */
function adbwl_prepare_case( $scenario ) {
	global $wpdb;
	$prefix = adbg_prefix();
	if ( '0' === $GLOBALS['argv'][2] ) {
		foreach ( array( 'users', 'usermeta', 'options', 'alynt_drime_dashboard_sites' ) as $suffix ) {
			adbg_check( '0' === $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $prefix . $suffix ) && '' === $wpdb->last_error, 'owned tables empty before first seed' );
		}
		adbg_seed();
	}
	// Every later case independently checks the invariant rows against fixed seed DATA.
	$roles = array(
		'capable'   => array(
			'name'         => 'Owned capable',
			'capabilities' => array(
				'manage_options' => true,
				'read'           => true,
			),
		),
		'incapable' => array(
			'name'         => 'Owned incapable',
			'capabilities' => array( 'read' => true ),
		),
	);
	$rows  = adbg_non_target();
	adbg_check(
		array(
			array(
				'option_name'  => $prefix . 'user_roles',
				'option_value' => serialize( $roles ),
				'autoload'     => 'yes',
			),
			array(
				'option_name'  => 'owned_non_target',
				'option_value' => 'preserve-exact-bytes',
				'autoload'     => 'no',
			),
		) === $rows,
		'fixed role/non-target bytes before request'
	);
	adbg_reset_case( $scenario );
	return $rows;
}

/** Exact physical result after guard/counter assertions; not a mocked success.
 *
 * @param string $scenario Fixed case.
 * @param array  $before Fixed non-target rows.
 * @return void
 */
function adbwl_verify_case( $scenario, $before ) {
	global $wpdb;
	adbg_preflight();
	adbg_check( adbg_non_target() === $before, 'non-target invariant after request' );
	$expected = array( 9 => array( 'wpvivid' => 'external_optional' ) );
	if ( 'save' === $scenario ) {
		$expected[7] = array( 'wpvivid' => 'external_optional' );
	}
	$row = $wpdb->get_row( $wpdb->prepare( 'SELECT option_value,autoload FROM ' . adbg_prefix() . 'options WHERE BINARY option_name=BINARY %s', 'alynt_drime_backups_dashboard_source_policies' ), ARRAY_A );
	adbg_check( '' === $wpdb->last_error && is_array( $row ) && serialize( $expected ) === $row['option_value'] && 'no' === $row['autoload'], 'physical exact request result' );
}
