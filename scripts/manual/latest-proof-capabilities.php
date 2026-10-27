<?php
/**
 * Physical report reads feeding isolated, non-network capability consumers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Builds source-controlled capability DATA, never a signed intent.
 *
 * @param array $overrides Fixture overrides.
 * @return string
 */
function adb_latest_payload( array $overrides = array() ) {
	return wp_json_encode(
		array(
			'remote_actions' => array_merge(
				array(
					'protocol_version'            => 2,
					'enabled'                     => true,
					'key_id'                      => 'owned-action-key',
					'allowed_actions'             => array( 'scan_upload_now' ),
					'sodium_available'            => true,
					'min_interval_seconds'        => 3600,
					'one_running_action_per_site' => true,
				),
				$overrides
			),
		)
	);
}

/**
 * Exercises genuine repositories; never signs, persists or dispatches an action.
 *
 * @return void
 */
function adb_latest_capability_cases() {
	global $wpdb, $groups, $queries, $http;
	$dispatcher = new Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher(
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		function () {
			throw new RuntimeException( 'No dispatcher HTTP permitted in owned proof' );
		},
		function () {
			throw new RuntimeException( 'No DNS permitted in owned proof' );
		}
	);
	$cases      = array(
		array( array( 'enabled' => false ), null, 'remote_action_capability_missing' ),
		array( array( 'key_id' => 'different-owned-key' ), null, 'remote_action_key_mismatch' ),
		array( array( 'allowed_actions' => array() ), null, 'remote_action_capability_missing' ),
		array( array(), '{', 'remote_action_capability_missing' ),
		array( array(), 'false', 'remote_action_capability_missing' ),
	);
	foreach ( $cases as $index => $case ) {
		$site = 10 + $index;
		$id   = 1000 + 2 * $index;
		adb_latest_site( $site );
		adb_latest_snapshot( $id, $site, '2026-08-02 00:00:00', adb_latest_payload() );
		adb_latest_snapshot( $id + 1, $site, '2026-08-01 00:00:00', null === $case[1] ? adb_latest_payload( $case[0] ) : $case[1] );
		$before = $queries;
		$result = $dispatcher->request_scan_upload_now( $site, 0 );
		adb_latest_check( '' === $wpdb->last_error && is_wp_error( $result ) && $case[2] === $result->get_error_code(), 'actual public consumer rejects latest closed/rotated/corrupt report' );
		adb_latest_check( 2 === $queries - $before && 0 === $http, 'public rejection performs only site and snapshot SELECTs' );
	}
	$groups[] = 'public consumer closed, rotated, absent-actions and corrupt-report rejection without IO';
	adb_latest_site( 20 );
	adb_latest_snapshot( 2000, 20, '2026-08-02 00:00:00', adb_latest_payload( array( 'enabled' => false ) ) );
	adb_latest_snapshot( 2001, 20, '2026-08-01 00:00:00', adb_latest_payload() );
	$lookup = new ReflectionMethod( Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher::class, 'latest_capabilities' );
	if ( PHP_VERSION_ID < 80100 ) {
		$lookup->setAccessible( true );
	}
	$before = $queries;
	$result = $lookup->invoke( $dispatcher, 20 );
	adb_latest_check( '' === $wpdb->last_error && is_array( $result ) && true === $result['enabled'] && 'owned-action-key' === $result['key_id'], 'positive private capability DATA uses newer enabled insertion' );
	adb_latest_check( 1 === $queries - $before && 0 === $http, 'positive private DATA lookup performs only one SELECT' );
	adb_latest_check( array() === array_intersect( array( 'signature', 'private_key', 'intent' ), array_keys( $result ) ), 'positive DATA is not a signed action handle' );
	$groups[] = 'positive private capability DATA lookup, no signing/public dispatch acceptance claim';
}
