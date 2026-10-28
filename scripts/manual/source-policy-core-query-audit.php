<?php
/**
 * Confirmed Plugin Tester read-only core query admission audit, never native proof.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( PHP_SAPI !== 'cli' || 2 !== count( $argv ) || '--confirmed-plugin-tester-read-only-query-audit' !== $argv[1] || defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Requires the confirmed Plugin Tester read-only audit scope\n" );
	exit( 1 );
}
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_DEBUG', false );
define( 'MULTISITE', false );
define( 'AUTH_SALT', 'owned-non-secret-source-policy-audit' );
if ( 'E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD' !== strtoupper( hash_file( 'sha256', ABSPATH . WPINC . '/class-wpdb.php' ) ) ) {
	throw new RuntimeException( 'Core query source changed; audit first' );
}
foreach ( array( 'load.php', 'plugin.php', 'class-wp-error.php', 'cache.php', 'class-wpdb.php' ) as $file ) {
	require_once ABSPATH . WPINC . '/' . $file;
}
require dirname( __DIR__, 2 ) . '/includes/class-source-policy.php';
require dirname( __DIR__, 2 ) . '/includes/class-source-policy-native-profile.php';
wp_cache_init();
wp_using_ext_object_cache( false ); // Private library process only, not an application cache change.
$checks = 0;

/**
 * Asserts fixed audit facts without disclosing SQL, credentials or application DATA.
 *
 * @param bool   $condition Required condition.
 * @param string $label Fixed diagnostic.
 * @return void
 * @throws RuntimeException On failure.
 */
function adbn_query_audit_check( $condition, $label ) {
	global $checks;
	++$checks;
	if ( ! $condition ) {
		throw new RuntimeException( 'Core query audit: ' . $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed CLI-only diagnostic labels.
	}
}

$profile = 'Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile';
// Synthetic descriptor DATA isolates this validator conflict; it grants no native authority.
$facts = array(
	'core'            => '7.1.3',
	'server'          => '10.6.23-MariaDB',
	'database'        => 'local',
	'table'           => 'wp_options',
	'schema'          => 'options4-unique-name-InnoDB',
	'autocommit'      => true,
	'single_site'     => true,
	'dropins'         => false,
	'cache'           => 'core-process',
	'bounded_session' => true,
	'hooks'           => $profile::fingerprint(),
);
adbn_query_audit_check( ! has_filter( 'query' ), 'initial query registry empty' );
adbn_query_audit_check( is_array( $profile::admit( $facts, 'wp_options' ) ), 'positive empty-query control' );
$database = ( new ReflectionClass( 'wpdb' ) )->newInstanceWithoutConstructor();
adbn_query_audit_check( 0 === $database->num_queries && null === $database->dbh, 'no native constructor/connection' );
try {
	adbn_query_audit_check( 'SELECT 7' === $database->prepare( 'SELECT %d', 7 ), 'genuine numeric prepare without driver IO' );
	adbn_query_audit_check( 0 === has_filter( 'query', array( $database, 'remove_placeholder_escape' ) ), 'core placeholder callback at priority zero' );
	$fingerprint = $profile::fingerprint();
	adbn_query_audit_check( is_string( $fingerprint ) && $fingerprint !== $facts['hooks'], 'core callback changes fingerprint' );
	$facts['hooks'] = $fingerprint;
	$rejected       = $profile::admit( $facts, 'wp_options' );
	adbn_query_audit_check( is_wp_error( $rejected ) && 'source_policy_unavailable' === $rejected->get_error_code(), 'even matching core callback profile rejected' );
	adbn_query_audit_check( 0 === $database->num_queries && null === $database->dbh, 'zero SQL and native connections retained' );
} finally {
	remove_filter( 'query', array( $database, 'remove_placeholder_escape' ), 0 );
}
adbn_query_audit_check( ! has_filter( 'query' ), 'exact private callback removed' );
echo json_encode(
	array(
		'checks'                         => $checks,
		'native_connections'             => 0,
		'sql_queries'                    => $database->num_queries,
		'core_callback_priority'         => 0,
		'matching_fingerprint_admission' => 'source_policy_unavailable',
		'callback_removed'               => true,
	)
) . "\n";
