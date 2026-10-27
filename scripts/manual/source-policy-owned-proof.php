<?php
/**
 * Manually approved Plugin Tester owned-option proof. No CI/application execution.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.PHP.DiscouragedPHPFunctions -- Fixed owned clone and primitive fixtures only, explicit local CLI authority, no application writes.
if ( PHP_SAPI !== 'cli' || 4 !== count( $argv ) || '--confirmed-plugin-tester-owned-policy-proof' !== $argv[1] || ! preg_match( '/\Aadbp_[a-f0-9]{12}_\z/', $argv[2] ) || ! in_array( $argv[3], array( 'preflight', 'proof' ), true ) || ! getenv( 'MYSQL_PWD' ) ) {
	fwrite( STDERR, "Requires exact target/owned fixture confirmation and inherited MYSQL_PWD\n" );
	exit( 1 );
}
define( 'ADB_POLICY_PROOF', true );
require __DIR__ . '/source-policy-proof-sql.php';
require __DIR__ . '/source-policy-proof-core.php';
require __DIR__ . '/source-policy-proof-writer.php';
require __DIR__ . '/source-policy-proof-cases.php';
require __DIR__ . '/source-policy-proof-hooks.php';
require __DIR__ . '/source-policy-proof-failures.php';
require __DIR__ . '/source-policy-proof-lock.php';
$checks            = 0;
$groups            = array();
$armed             = false;
$cleaned           = false;
$started           = microtime( true );
$owned_connections = array( (int) $wpdb->get_var( 'SELECT CONNECTION_ID()' ) );
$owned_child_pid   = null;

/**
 * Records fixed support-safe assertions, never row contents or SQL.
 *
 * @param bool   $condition Required condition.
 * @param string $label Fixed description.
 * @return void
 * @throws RuntimeException On failed proof.
 */
function adbp_check( $condition, $label ) {
	global $checks;
	++$checks;
	if ( ! $condition ) {
		throw new RuntimeException( 'Owned policy assertion: ' . $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed CLI diagnostic, not HTML.
	}
}

$cleanup = function () use ( &$armed, &$cleaned, $table ) {
	global $wpdb;
	if ( ! $armed || $cleaned ) {
		return;
	}
	$wpdb->query( 'ROLLBACK' );
	$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table );
	adbp_check( '' === $wpdb->last_error && null !== $count && (int) $count <= 3, 'owned cleanup row preview' );
	adbp_check( false !== $wpdb->query( 'DROP TABLE ' . $table ), 'owned clone removed' );
	adbp_check( null === $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ) ) && '' === $wpdb->last_error, 'successful absence witness' );
	$cleaned = true;
};
try {
	adbp_check( null === $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ) ) && '' === $wpdb->last_error, 'fresh exact clone absent' );
	adbp_check( ! wp_using_ext_object_cache() && get_class( $wp_object_cache ) === 'WP_Object_Cache', 'genuine process-local cache profile only' );
	adbp_check( 'off' === wp_determine_option_autoload_value( 'owned', array(), 'a:0:{}', false ), 'core explicit non-autoload encoding' );
	if ( 'preflight' === $argv[3] ) {
		echo json_encode(
			array(
				'checks'   => $checks,
				'queries'  => $queries,
				'fixtures' => 0,
			)
		) . "\n";
		return;
	}
	adbp_check( false !== $wpdb->query( 'CREATE TABLE ' . $table . ' LIKE wp_options' ), 'schema-only clone created' );
	$armed          = true;
	$fixture_status = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ), ARRAY_A );
	adbp_check( 'InnoDB' === $fixture_status['Engine'] && false !== strpos( $fixture_status['Collation'], '_ci' ), 'genuine case-insensitive InnoDB profile' );
	$index = $wpdb->get_results( 'SHOW INDEX FROM ' . $table, ARRAY_A );
	adbp_check( 3 === count( $index ) && 'option_name' === $index[1]['Column_name'] && '0' === $index[1]['Non_unique'] && null === $index[1]['Sub_part'], 'full unique option identity plus primary/autoload indexes' );
	adbp_storage_cases();
	adbp_hook_cases();
	adbp_lock_case();
	adbp_check( '0' === $wpdb->get_var( 'SELECT @@in_transaction' ), 'parent session has no retained transaction' );
	$cleanup();
	adbp_check( microtime( true ) - $started < 30, 'bounded whole proof' );
	echo json_encode(
		array(
			'checks'      => $checks,
			'groups'      => $groups,
			'queries'     => $queries,
			'cleaned'     => $cleaned,
			'seconds'     => round( microtime( true ) - $started, 3 ),
			'connections' => $owned_connections,
			'parent_pid'  => getmypid(),
			'child_pid'   => $owned_child_pid,
		)
	) . "\n";
} finally {
	$cleanup();
	$wpdb->close();
}
