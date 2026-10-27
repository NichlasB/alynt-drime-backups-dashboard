<?php
/**
 * Bounded owned fixture helpers and result observations for the manual proof.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Exact owned table manifest, no application data.
/**
 * Counts fixed-label assertions, never raw SQL or fixture payloads.
 *
 * @param bool   $condition Observed condition.
 * @param string $label Fixed description.
 * @return void
 * @throws RuntimeException For an assertion failure.
 */
function adb_retention_check( $condition, $label ) {
	++$GLOBALS['checks'];
	if ( ! $condition ) {
		throw new RuntimeException( 'Retention assertion: ' . $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed-label private CLI, not HTML.
	}
}

/**
 * Executes a generated owned statement requiring physical success.
 *
 * @param string $sql Fixed generated statement.
 * @return int|bool Actual query result.
 */
function adb_retention_query( $sql ) {
	global $wpdb;
	$result = $wpdb->query( $sql );
	adb_retention_check( false !== $result && '' === $wpdb->last_error, 'owned statement succeeds' );
	return $result;
}

/**
 * Resets owned fixture rows/cache, never application options or global state.
 *
 * @return void
 */
function adb_retention_reset() {
	global $tables, $faults, $trace, $deletes, $before_delete;
	$faults        = array();
	$before_delete = null;
	foreach ( $tables as $table ) {
		adb_retention_query( 'DELETE FROM ' . $table );
	}
	wp_cache_flush();
	update_option( 'blog_charset', 'UTF-8', false );
	update_option( Alynt_Drime_Backups_Dashboard_Storage::OPTION_SCHEMA_VERSION, '8', false );
	update_option( 'owned_retention_canary', 'untouched', false );
	$trace   = array();
	$deletes = array();
}

/**
 * Inserts bounded fixture rows, bypassing no production cleanup implementation.
 *
 * @param string      $kind Snapshot or action fixture.
 * @param int         $id Fixed row ID.
 * @param int         $site Fixed site ID.
 * @param string|null $date Fixture age/completion.
 * @return void
 */
function adb_retention_insert( $kind, $id, $site, $date ) {
	global $wpdb, $tables;
	$row = 'snapshots' === $kind
		? array(
			'id'                => $id,
			'dashboard_site_id' => $site,
			'observed_at'       => $date,
			'payload_json'      => '{"owned":true}',
		)
		: array(
			'id'                    => $id,
			'public_id'             => 'owned-' . $id,
			'dashboard_site_id'     => $site,
			'requested_at'          => '2000-01-01 00:00:00',
			'completed_at'          => $date,
			'expires_at'            => '2099-01-01 00:00:00',
			'redacted_context_json' => '{}',
			'created_at'            => '2000-01-01 00:00:00',
			'updated_at'            => '2000-01-01 00:00:00',
		);
	adb_retention_check( 1 === $wpdb->insert( $tables[ 'snapshots' === $kind ? 1 : 2 ], $row ), 'owned fixture inserted' );
}

/**
 * Reads actual remaining IDs in deterministic order.
 *
 * @param int $index Owned manifest index.
 * @return array<int,int> Remaining IDs.
 */
function adb_retention_ids( $index ) {
	global $wpdb, $tables;
	$ids = $wpdb->get_col( 'SELECT id FROM ' . $tables[ $index ] . ' ORDER BY id ASC' );
	adb_retention_check( '' === $wpdb->last_error, 'physical remaining-row read succeeds' );
	return array_map( 'intval', $ids );
}

/**
 * Reads physical option bytes instead of treating a cache value as persistence.
 *
 * @return array Physical event buffer, empty if absent.
 */
function adb_retention_events() {
	global $wpdb;
	$raw = $wpdb->get_var( $wpdb->prepare( 'SELECT option_value FROM ' . $wpdb->options . ' WHERE option_name = %s', Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_EVENTS ) );
	adb_retention_check( '' === $wpdb->last_error, 'physical diagnostic read succeeds' );
	return null === $raw ? array() : maybe_unserialize( $raw );
}
