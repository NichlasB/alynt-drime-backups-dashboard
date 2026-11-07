<?php
/**
 * Fixed owned fixture operations. Definitions only until the exact manual proof.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.SlowDBQuery, WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- At most twelve owned metadata rows, source-owned primitive fixtures only; not an application query path.
// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL -- Fixed owned proof identifiers; no installed/application storage.
if ( ! defined( 'ADB_M_REQUEST_PROOF' ) ) {
	exit( 1 );
}

/**
 * Reject any identifier outside this run before proof SQL.
 *
 * @return string Owned prefix.
 */
function adbg_prefix() {
	$prefix = $GLOBALS['wpdb']->prefix;
	adbg_check( is_string( $prefix ) && 1 === preg_match( '/\Aadbm_[a-f0-9]{12}_\z/', $prefix ), 'exact owned prefix' );
	return $prefix;
}

/**
 * Initialize three users, two roles, three options and one inert site only.
 *
 * @return void
 */
function adbg_seed() {
	global $wpdb;
	$prefix = adbg_prefix();
	foreach ( array(
		1 => 'one',
		2 => 'two',
		3 => 'low',
	) as $id => $name ) {
		adbg_check(
			1 === $wpdb->insert(
				$prefix . 'users',
				array(
					'ID'                  => $id,
					'user_login'          => 'owned_' . $name,
					'user_pass'           => str_repeat( (string) $id, 64 ),
					'user_nicename'       => 'owned_' . $name,
					'user_email'          => $name . '@invalid.example',
					'user_url'            => '',
					'user_registered'     => '2026-01-01 00:00:00',
					'user_activation_key' => '',
					'user_status'         => 0,
					'display_name'        => 'Owned ' . $name,
				)
			),
			'owned user seed'
		);
		$role = 3 === $id ? 'incapable' : 'capable';
		adbg_check(
			1 === $wpdb->insert(
				$prefix . 'usermeta',
				array(
					'user_id'    => $id,
					'meta_key'   => $prefix . 'capabilities',
					'meta_value' => serialize( array( $role => true ) ),
				)
			),
			'owned capability seed'
		);
	}
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
	adbg_check(
		1 === $wpdb->insert(
			$prefix . 'options',
			array(
				'option_name'  => $prefix . 'user_roles',
				'option_value' => serialize( $roles ),
				'autoload'     => 'yes',
			)
		),
		'owned role seed'
	);
	adbg_check(
		1 === $wpdb->insert(
			$prefix . 'options',
			array(
				'option_name'  => 'owned_non_target',
				'option_value' => 'preserve-exact-bytes',
				'autoload'     => 'no',
			)
		),
		'non-target seed'
	);
	adbg_check( 1 === $wpdb->insert( $prefix . 'alynt_drime_dashboard_sites', array( 'id' => 7 ) ), 'inert owned site' );
}

/**
 * Replace ONLY the policy key, never the coexisting roles/non-target rows.
 *
 * @return void
 */
function adbg_seed_policy() {
	global $wpdb;
	$table = adbg_prefix() . 'options';
	$name  = 'alynt_drime_backups_dashboard_source_policies';
	adbg_check( false !== $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE BINARY option_name=BINARY %s", $name ) ), 'owned policy-key delete' );
	adbg_check(
		1 === $wpdb->insert(
			$table,
			array(
				'option_name'  => $name,
				'option_value' => serialize( array( 9 => array( 'wpvivid' => 'external_optional' ) ) ),
				'autoload'     => 'no',
			)
		),
		'owned policy-key seed'
	);
}

/**
 * Independently observe non-target bytes/autoload with finite owned rows.
 *
 * @return array
 */
function adbg_non_target() {
	global $wpdb;
	$table = adbg_prefix() . 'options';
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT option_name,option_value,autoload FROM $table WHERE BINARY option_name<>BINARY %s ORDER BY option_name", 'alynt_drime_backups_dashboard_source_policies' ), ARRAY_A );
	adbg_check( '' === $wpdb->last_error && is_array( $rows ) && 2 === count( $rows ), 'two non-target rows' );
	foreach ( $rows as $row ) {
		adbg_check( is_string( $row['option_value'] ) && strlen( $row['option_value'] ) <= 4096, 'bounded non-target value' );
	}
	return $rows;
}

/**
 * Bound every owned fixture before a fresh request process, never trim it.
 *
 * @param string $expected_autoload Exact fixed phase representation; off only after a W/L save.
 * @return void
 */
function adbg_preflight( $expected_autoload = 'no' ) {
	global $wpdb;
	adbg_check( 'no' === $expected_autoload || ( 'off' === $expected_autoload && defined( 'ADB_WL_REQUEST_PROOF' ) ), 'fixed non-autoloaded proof phase' );
	$prefix = adbg_prefix();
	foreach ( array(
		'users'                       => 3,
		'usermeta'                    => 12,
		'options'                     => 4,
		'alynt_drime_dashboard_sites' => 1,
	) as $suffix => $max ) {
		$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $prefix . $suffix );
		adbg_check( '' === $wpdb->last_error && is_string( $count ) && 1 === preg_match( '/\A[0-9]+\z/', $count ) && (int) $count <= $max, 'bounded fixture cardinality' );
	}
	$large = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $prefix . 'usermeta WHERE OCTET_LENGTH(meta_value)>4096' );
	adbg_check( '' === $wpdb->last_error && '0' === $large, 'bounded metadata values, not a failed verifier read' );
	$groups = $wpdb->get_results( 'SELECT user_id FROM ' . $prefix . 'usermeta GROUP BY user_id HAVING COUNT(*)>4', ARRAY_A );
	adbg_check( '' === $wpdb->last_error && array() === $groups, 'per-user metadata bound' );
	$sessions = $wpdb->get_results( "SELECT user_id,meta_value FROM {$prefix}usermeta WHERE meta_key='session_tokens'", ARRAY_A );
	adbg_check( '' === $wpdb->last_error && is_array( $sessions ) && count( $sessions ) <= 3, 'bounded session rows' );
	foreach ( $sessions as $row ) {
		adbg_check( is_string( $row['meta_value'] ) && strlen( $row['meta_value'] ) <= 4096, 'bounded session encoding before decode' );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- Independent bounded owned-fixture verifier, no classes or callbacks.
		$value = unserialize(
			$row['meta_value'],
			array(
				'allowed_classes' => false,
				'max_depth'       => 8,
			)
		);
		adbg_check( in_array( (string) $row['user_id'], array( '1', '2', '3' ), true ) && is_array( $value ) && count( $value ) <= 2, 'two owned sessions per user' );
		foreach ( $value as $verifier => $session ) {
			adbg_check( is_string( $verifier ) && 1 === preg_match( '/\A[a-f0-9]{64}\z/', $verifier ) && is_array( $session ) && array( 'expiration', 'login' ) === array_keys( $session ) && is_int( $session['expiration'] ) && is_int( $session['login'] ), 'primitive finite session payload' );
		}
	}
	$policy = $wpdb->get_row( $wpdb->prepare( 'SELECT OCTET_LENGTH(option_value) AS bytes,autoload FROM ' . $prefix . 'options WHERE BINARY option_name=BINARY %s', 'alynt_drime_backups_dashboard_source_policies' ), ARRAY_A );
	adbg_check( '' === $wpdb->last_error && is_array( $policy ) && is_string( $policy['bytes'] ) && 1 === preg_match( '/\A[0-9]+\z/', $policy['bytes'] ) && (int) $policy['bytes'] <= 16384 && $expected_autoload === $policy['autoload'], 'bounded non-autoloaded policy metadata' );
}

/**
 * Clear only owned sessions before a fresh child; role/capability rows stay intact.
 *
 * @param string $scenario Fixed case.
 * @return void
 */
function adbg_reset_case( $scenario ) {
	global $wpdb;
	$prefix = adbg_prefix();
	adbg_check( false !== $wpdb->query( "DELETE FROM {$prefix}usermeta WHERE meta_key='session_tokens' AND user_id IN (1,2,3)" ), 'owned session reset' );
	adbg_seed_policy();
	adbg_check( false !== $wpdb->query( "DELETE FROM {$prefix}alynt_drime_dashboard_sites WHERE id IN (7,8)" ), 'owned site reset' );
	if ( 'record-missing' !== $scenario ) {
		adbg_check( 1 === $wpdb->insert( $prefix . 'alynt_drime_dashboard_sites', array( 'id' => 'record-wrong' === $scenario ? 8 : 7 ) ), 'case record' );
	}
	adbg_preflight();
}
