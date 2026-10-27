<?php
/**
 * Genuine private hook dispatch and physical diagnostic option failure cases.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Exact owned fixtures only, never application options.
/**
 * Constructs the actual owner/logger/repositories after fixture settings are stored.
 *
 * @param array $settings Owned diagnostic settings.
 * @return Alynt_Drime_Backups_Dashboard_Plugin Actual owner.
 */
function adb_retention_owner( array $settings ) {
	update_option( Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_SETTINGS, $settings, false );
	wp_cache_flush();
	remove_all_actions( Alynt_Drime_Backups_Dashboard_Poller::CLEANUP_HOOK );
	$owner = new Alynt_Drime_Backups_Dashboard_Plugin();
	adb_retention_check( false !== has_action( Alynt_Drime_Backups_Dashboard_Poller::CLEANUP_HOOK, array( $owner, 'cleanup_retention' ) ), 'genuine core hook owns actual callback' );
	return $owner;
}

/**
 * Runs current callback/logging code, never a stubbed cleanup or logger.
 *
 * @return void
 */
function adb_retention_reporting_cases() {
	global $wpdb, $tables, $trace, $deletes, $faults, $groups;
	$event_option = Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_EVENTS;
	adb_retention_reset();
	adb_retention_bulk( 'snapshots', 501 );
	adb_retention_insert( 'snapshots', 6000, 1, '2099-01-01 00:00:00' );
	adb_retention_bulk( 'actions', 501 );
	$owner   = adb_retention_owner( array( 'enabled' => true ) );
	$trace   = array();
	$deletes = array();
	do_action( Alynt_Drime_Backups_Dashboard_Poller::CLEANUP_HOOK );
	adb_retention_check( array( 'snapshots', 'actions' ) === $trace, 'real private hook invokes each original batch once without success noise' );
	adb_retention_check( array( 501, 6000 ) === adb_retention_ids( 1 ) && array( 501 ) === adb_retention_ids( 2 ), 'callback preserves original500-row defaults physically' );
	foreach ( $deletes as $delete ) {
		$days = 'snapshots' === $delete[0] ? 30 : 90;
		adb_retention_check( 1 === preg_match( "/< '([0-9 :-]+)'/", $delete[1], $match ) && abs( time() - strtotime( $match[1] . ' UTC' ) - $days * 86400 ) <= 2, 'callback preserves repository day defaults' );
	}
	adb_retention_check( array() === adb_retention_events(), 'healthy cleanup writes no diagnostic buffer' );
	$groups[] = 'genuine private core hook, unchanged defaults and healthy silence';
	foreach ( array( array( true, false ), array( false, true ), array( true, true ) ) as $case ) {
		adb_retention_reset();
		adb_retention_insert( 'snapshots', 1, 1, '2000-01-01 00:00:00' );
		adb_retention_insert( 'snapshots', 2, 1, '2099-01-01 00:00:00' );
		adb_retention_insert( 'actions', 1, 1, '2000-01-01 00:00:00' );
		$owner  = adb_retention_owner( array( 'enabled' => true ) );
		$faults = array(
			'snapshots' => $case[0],
			'actions'   => $case[1],
		);
		$trace  = array();
		adb_retention_check( null === $owner->cleanup_retention(), 'actual callback retains void contract on physical failure' );
		adb_retention_check( array( 'snapshots', 'actions' ) === array_slice( $trace, 0, 2 ), 'both deletes precede physical event storage' );
		adb_retention_check( 1 === count( array_keys( $trace, 'snapshots', true ) ) && 1 === count( array_keys( $trace, 'actions', true ) ), 'physical failures never repeat either batch' );
		adb_retention_check( ( $case[0] ? array( 1, 2 ) : array( 2 ) ) === adb_retention_ids( 1 ), 'snapshot failure or success reflected in physical rows' );
		adb_retention_check( ( $case[1] ? array( 1 ) : array() ) === adb_retention_ids( 2 ), 'other repository is still attempted despite first failure' );
		$events = array_reverse( adb_retention_events() );
		$codes  = array();
		if ( $case[0] ) {
			$codes[] = 'snapshot_cleanup_failed';
		}
		if ( $case[1] ) {
			$codes[] = 'remote_action_cleanup_failed';
		}
		adb_retention_check( array_column( $events, 'code' ) === $codes, 'exact physical failure diagnostic codes/order' );
		foreach ( $events as $event ) {
			$message = 'snapshot_cleanup_failed' === $event['code'] ? 'The dashboard could not clean up old status snapshots.' : 'The dashboard could not clean up old remote action records.';
			adb_retention_check( 'error' === $event['level'] && 'storage' === $event['category'] && array() === $event['context'] && $message === $event['message'], 'safe fixed fields, no database error payload' );
		}
		$encoded = json_encode( $events );
		adb_retention_check( false === strpos( $encoded, $GLOBALS['argv'][2] ) && false === strpos( $encoded, 'ADB_OWNED_INVALID_SYNTAX' ), 'no raw physical SQL fault or fixture identifier persisted' );
		$autoload = $wpdb->get_var( $wpdb->prepare( 'SELECT autoload FROM ' . $tables[0] . ' WHERE option_name = %s', $event_option ) );
		adb_retention_check( '' === $wpdb->last_error && in_array( $autoload, array( 'no', 'off', 'auto-off' ), true ), 'genuine option buffer is not autoloaded' );
		$faults = array();
		$owner->cleanup_retention();
		adb_retention_check( array_reverse( adb_retention_events() ) === $events, 'healthy recovery retains exact physical diagnostic history without success noise' );
	}
	$groups[] = 'independent and combined physical cleanup failures, persistence and recovery';
	foreach ( array(
		array( 'enabled' => false ),
		array(
			'enabled'       => true,
			'minimum_level' => 'critical',
		),
	) as $settings ) {
		adb_retention_reset();
		$owner  = adb_retention_owner( $settings );
		$faults = array(
			'snapshots' => true,
			'actions'   => true,
		);
		$trace  = array();
		$before = $wpdb->get_results( 'SELECT option_name,option_value,autoload FROM ' . $tables[0] . ' ORDER BY option_name', ARRAY_A );
		$owner->cleanup_retention();
		adb_retention_check( array( 'snapshots', 'actions' ) === $trace && array() === adb_retention_events(), 'opt-in/threshold quiet path never bypassed after physical failure' );
		adb_retention_check( $before === $wpdb->get_results( 'SELECT option_name,option_value,autoload FROM ' . $tables[0] . ' ORDER BY option_name', ARRAY_A ) && '' === $wpdb->last_error, 'policy refusal leaves physical options byte-identical' );
	}
	$groups[] = 'physical opt-in and severity-policy preservation';
	adb_retention_reset();
	$owner  = adb_retention_owner( array( 'enabled' => true ) );
	$faults = array(
		'snapshots' => true,
		'actions'   => true,
		'events'    => true,
	);
	$trace  = array();
	adb_retention_check( null === $owner->cleanup_retention(), 'genuine core option-storage failure is nonfatal to callback' );
	adb_retention_check( array( 'snapshots', 'actions', 'event_write', 'event_write' ) === $trace, 'one logging attempt per error after both deletes, no recursive fallback/retry' );
	adb_retention_check( array() === adb_retention_events(), 'failed genuine event write is not mistaken for persistence' );
	adb_retention_check( 'untouched' === get_option( 'owned_retention_canary' ), 'owned unrelated option preserved' );
	$groups[] = 'physical option failure without retry or fabricated durability';
}
