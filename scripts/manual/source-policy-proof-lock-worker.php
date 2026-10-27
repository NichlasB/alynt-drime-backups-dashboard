<?php
/**
 * Owned bounded row-lock holder; never terminates a service or another session.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Exact owned fixture lock only, no application statement.
if ( PHP_SAPI !== 'cli' || 4 !== count( $argv ) || '--confirmed-plugin-tester-owned-policy-proof' !== $argv[1] || ! preg_match( '/\Aadbp_[a-f0-9]{12}_\z/', $argv[2] ) || ! getenv( 'MYSQL_PWD' ) || realpath( dirname( $argv[3] ) ) !== realpath( sys_get_temp_dir() ) || ! preg_match( '/\A' . preg_quote( $argv[2], '/' ) . 'lock_[a-f0-9]{12}\z/', basename( $argv[3] ) ) || is_link( $argv[3] ) || realpath( $argv[3] ) !== $argv[3] ) {
	exit( 1 );
}
define( 'ADB_POLICY_PROOF', true );
require __DIR__ . '/source-policy-proof-sql.php';
require __DIR__ . '/source-policy-proof-core.php';
try {
	if ( false === $wpdb->query( 'START TRANSACTION' ) || ! $wpdb->get_row( $wpdb->prepare( "SELECT option_id FROM $wpdb->options WHERE option_name=%s FOR UPDATE", Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES ) ) ) {
		throw new RuntimeException( 'Owned blocker setup failed' );
	}
	$connection = (int) $wpdb->get_var( 'SELECT CONNECTION_ID()' );
	if ( $connection <= 0 || false === file_put_contents( $argv[3] . '/ready', 'locked:' . $connection, LOCK_EX ) ) {
		throw new RuntimeException( 'Owned blocker marker failed' );
	}
	$deadline = microtime( true ) + 6;
	do {
		clearstatcache( true, $argv[3] . '/release' );
		if ( is_file( $argv[3] . '/release' ) ) {
			break;
		}
		usleep( 10000 );
	} while ( microtime( true ) < $deadline );
} finally {
	if ( false === $wpdb->query( 'ROLLBACK' ) || '0' !== $wpdb->get_var( 'SELECT @@in_transaction' ) ) {
		throw new RuntimeException( 'Owned blocker rollback unconfirmed' );
	}
	$wpdb->close();
}
echo "rolled-back\n";
