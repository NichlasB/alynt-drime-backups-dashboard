<?php
/**
 * Physical SELECT cases and bounded owned fixture setup.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Fixed owned manifest; no application rows.
/**
 * Asserts fixed labels, never raw SQL or private row data.
 *
 * @param bool   $condition Observed result.
 * @param string $label Fixed description.
 * @return void
 * @throws RuntimeException On failed proof check.
 */
function adb_latest_check( $condition, $label ) {
	++$GLOBALS['checks'];
	if ( ! $condition ) {
		throw new RuntimeException( 'Latest-selection assertion: ' . $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed private CLI label, not HTML.
	}
}

/**
 * Requires physical success for a generated owned statement.
 *
 * @param string $sql Generated SQL.
 * @return void
 */
function adb_latest_query( $sql ) {
	global $wpdb;
	adb_latest_check( false !== $wpdb->query( $sql ) && '' === $wpdb->last_error, 'owned statement succeeds' );
}

/**
 * Creates only synthetic site identity/credential markers, not real credentials.
 *
 * @param int $id Owned site ID.
 * @return void
 */
function adb_latest_site( $id ) {
	global $wpdb, $tables;
	$data = array(
		'id'                            => $id,
		'public_id'                     => sprintf( '00000000-0000-4000-8000-%012d', $id ),
		'site_uuid'                     => sprintf( '11111111-1111-4111-8111-%012d', $id ),
		'site_label'                    => 'Owned latest fixture',
		'expected_origin'               => 'https://owned.example.invalid',
		'enrollment_status'             => 'active',
		'polling_key_id'                => 'owned-poll-key',
		'polling_secret_ciphertext'     => 'owned-not-encrypted-marker',
		'action_key_id'                 => 'owned-action-key',
		'action_private_key_ciphertext' => 'owned-not-a-private-key',
		'created_at'                    => '2026-08-01 00:00:00',
		'updated_at'                    => '2026-08-01 00:00:00',
	);
	adb_latest_check( 1 === $wpdb->insert( $tables[0], $data ) && '' === $wpdb->last_error, 'owned synthetic site inserted' );
}

/**
 * Inserts one bounded source-controlled payload, never application data.
 *
 * @param int    $id Snapshot ID.
 * @param int    $site Site ID.
 * @param string $time Fixture observation date.
 * @param string $json Fixture payload.
 * @return void
 */
function adb_latest_snapshot( $id, $site, $time, $json ) {
	global $wpdb, $tables;
	adb_latest_check( strlen( $json ) <= 2048, 'fixture payload size bounded' );
	adb_latest_check(
		1 === $wpdb->insert(
			$tables[1],
			array(
				'id'                => $id,
				'dashboard_site_id' => $site,
				'observed_at'       => $time,
				'payload_json'      => $json,
			)
		) && '' === $wpdb->last_error,
		'owned snapshot inserted'
	);
}

/**
 * Qualifies actual prepared current/bulk/history queries on genuine wpdb.
 *
 * @return void
 */
function adb_latest_selector_cases() {
	global $wpdb, $groups;
	$repository = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
	adb_latest_site( 99 );
	adb_latest_snapshot( 9999, 99, '2099-01-01 00:00:00', '{"marker":"foreign"}' );
	foreach ( array(
		1 => '2026-08-01 00:00:00',
		2 => '2026-08-02 00:00:00',
		3 => '2026-08-03 00:00:00',
	) as $site => $new_time ) {
		$id = 100 + $site * 10;
		adb_latest_site( $site );
		adb_latest_snapshot( $id, $site, '2026-08-02 00:00:00', '{"marker":"old"}' );
		adb_latest_snapshot( $id + 1, $site, $new_time, '{"marker":"new π"}' );
		$current = $repository->latest_for_site( (string) $site );
		adb_latest_check( '' === $wpdb->last_error && (string) ( $id + 1 ) === $current['id'], 'current is highest owned site ID' );
		adb_latest_check( false !== strpos( $wpdb->last_query, 'ORDER BY id DESC LIMIT 1' ), 'actual current query preserves bound' );
		adb_latest_check( array( 'marker' => 'new π' ) === $current['decoded_payload'], 'genuine UTF-8 JSON decoded exactly' );
		$fleet = $repository->latest_by_site_ids( array( $site, 99 ) );
		adb_latest_check( '' === $wpdb->last_error && $current === $fleet[ $site ] && '9999' === $fleet[99]['id'], 'physical single and fleet identity/payload agree per site' );
		$history = $repository->recent_for_site( $site, 2 );
		$ids     = array_column( $history, 'id' );
		$ordered = 1 === $site ? array( (string) $id, (string) ( $id + 1 ) ) : array( (string) ( $id + 1 ), (string) $id );
		adb_latest_check( '' === $wpdb->last_error && $ordered === $ids, 'actual history keeps observation order and tie break' );
		adb_latest_check( ! isset( $history[0]['payload_json'] ) && 2 === count( $history ), 'history summary projection and limit preserved' );
	}
	$groups[] = 'physical regressed, tied, advancing dates, site separation and chronological history';
	adb_latest_check( null === $repository->latest_for_site( 999 ) && '' === $wpdb->last_error, 'missing selected site has no foreign fallback' );
	adb_latest_check( null === $repository->latest_for_site( 0 ) && '' === $wpdb->last_error, 'zero site returns no observation' );
	adb_latest_check( array() === $repository->latest_by_site_ids( array( 999 ) ) && '' === $wpdb->last_error, 'missing site fleet is empty' );
	adb_latest_site( 40 );
	adb_latest_snapshot( 410, 40, '2026-08-02 00:00:00', '{"marker":"old"}' );
	adb_latest_snapshot( 411, 40, '2026-08-01 00:00:00', '{' );
	$current = $repository->latest_for_site( 40 );
	adb_latest_check( '' === $wpdb->last_error && '411' === $current['id'] && array() === $current['decoded_payload'], 'malformed newest JSON stays current but decodes empty' );
	$groups[] = 'physical missing, zero and malformed-payload reads';
}
