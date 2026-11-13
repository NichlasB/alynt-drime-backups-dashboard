<?php
/**
 * One explicit owned installation; never installed-site lifecycle operations.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL, WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Fixed owned fixture setup and exact physical readback only.
if ( 'cli' !== PHP_SAPI || array( __FILE__, '--owned-http-setup' ) !== $argv ) {
	exit( 1 ); }
require __DIR__ . '/source-policy-http-owned-worker.php';
require_once __DIR__ . '/source-policy-http-owned-diagnostic.php';
try {
	$_SERVER['HTTP_HOST']      = '127.0.0.1:18777';
	$_SERVER['SERVER_NAME']    = '127.0.0.1';
	$_SERVER['SERVER_PORT']    = '18777';
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI']    = '/wp-admin/install.php';
	$_SERVER['PHP_SELF']       = '/wp-admin/install.php';
	adbh_bootstrap( 'setup' );
	require ABSPATH . 'wp-admin/includes/upgrade.php';
	$GLOBALS['adbh_containment']->begin_install();
	$p                     = $GLOBALS['adbh_private'];
	$GLOBALS['adbh_stage'] = 'install';
	$result                = wp_install( 'Owned HTTP proof', 'owned_one', 'owned-one@example.invalid', 0, '', $p['password'] );
	adbh_check( is_array( $result ) && 1 === $result['user_id'], 'genuine-install' );
	unset( $result );
	wp_installing( false );
	$GLOBALS['adbh_stage'] = 'seed';
	foreach ( array( array( 'owned_two', 'owned-two@example.invalid', 'administrator' ), array( 'owned_low', 'owned-low@example.invalid', 'subscriber' ) ) as $index => $row ) {
		$owned_user_id = wp_create_user( $row[0], $p['password'], $row[1] );
		adbh_check( $index + 2 === $owned_user_id, 'owned-user-id' );
		( new WP_User( $owned_user_id ) )->set_role( $row[2] );
	}
	adbh_check( array() === get_option( 'active_plugins' ) && 'twentytwentyfive' === get_option( 'template' ) && 'twentytwentyfive' === get_option( 'stylesheet' ), 'fresh-content-graph' );
	adbh_check( add_option( 'alynt_drime_backups_dashboard_source_policies', array( 9 => array( 'wpvivid' => 'external_optional' ) ), '', false ) && add_option( 'owned_non_target', 'preserve-exact-bytes', '', false ), 'owned-policy-seed' );
	global $wpdb;
	adbh_check( false !== $wpdb->query( 'CREATE TABLE ' . $p['prefix'] . 'alynt_drime_dashboard_sites (id bigint unsigned NOT NULL,PRIMARY KEY(id)) ENGINE=InnoDB' ) && 1 === $wpdb->insert( $p['prefix'] . 'alynt_drime_dashboard_sites', array( 'id' => 7 ) ), 'inert-record' );
	$owned_post = $wpdb->get_row( 'SELECT post_date,post_name FROM ' . $p['prefix'] . 'posts WHERE ID=1', ARRAY_A );
	register_shutdown_function(
		static function () use ( $owned_post ) {
			try {
				$GLOBALS['adbh_stage'] = 'finalize';
				$containment           = adbh_finish_packet( $owned_post );
				echo json_encode(
					array(
						'ledger'      => adbh_ledger_packet( 'setup' ),
						'containment' => $containment,
					)
				) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Private bounded count-only stdout, no HTTP response.
			} catch ( Throwable $error ) {
				fwrite( STDERR, json_encode( adbh_setup_diagnostic( $error, dirname( __DIR__, 3 ) ) ) . "\n" );
				exit( 1 ); }
		}
	);
} catch ( Throwable $error ) {
	fwrite( STDERR, json_encode( adbh_setup_diagnostic( $error, dirname( __DIR__, 3 ) ) ) . "\n" );
	exit( 1 );
}
