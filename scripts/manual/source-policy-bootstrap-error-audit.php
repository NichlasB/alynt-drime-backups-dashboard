<?php
/**
 * Genuine core error-hook diagnosis only; no native driver, session or fixture.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( PHP_SAPI !== 'cli' || 2 !== count( $argv ) || '--confirmed-plugin-tester-zero-sql-error-hook-audit' !== $argv[1] || defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Requires confirmed Plugin Tester zero-SQL error-hook audit scope\n" );
	exit( 1 );
}
if ( extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' ) ) {
	fwrite( STDERR, "Requires driver-free PHP -n\n" );
	exit( 1 );
}
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_DEBUG', false );
$pins   = array(
	'load.php'                     => '3EFD0934BD8A454A8D7076FBBD21504EA097A753125A48E64B8A1DAD380D357A',
	'plugin.php'                   => 'D70B9E18D34AB3FE46E15548A5E8E089EA5920832F06A4D89A9D64B2F7938B0D',
	'class-wp-hook.php'            => 'B839C0E5672246BCA8DB1AB781EC8835F7732F253C375A237CBF6EC536E8D12E',
	'class-wp-filter-sentinel.php' => '68C9C675D3805A343835728D98A0EFEDF0DF9833CB904BEBF909C7E0FDA5C61C',
	'class-wp-error.php'           => 'A9E971548BF6A1D93C821F980F530EFBA985C25D5D0B13D9B63E63B378A81A94',
	'class-wpdb.php'               => 'E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD',
);
$checks = 0;
$events = array();
$all    = static function ( $hook ) use ( &$events ) {
	$events[] = $hook;
};
$group  = 'source';
$armed  = false;
try {
	foreach ( $pins as $file => $hash ) {
		++$checks;
		if ( ! is_file( ABSPATH . WPINC . '/' . $file ) || strtoupper( hash_file( 'sha256', ABSPATH . WPINC . '/' . $file ) ) !== $hash ) {
			throw new RuntimeException( 'Changed source' );
		}
	}
	foreach ( array( 'load.php', 'plugin.php', 'class-wp-error.php', 'class-wpdb.php' ) as $file ) {
		require_once ABSPATH . WPINC . '/' . $file;
	}
	foreach ( array( 'class-source-policy.php', 'class-source-policy-native-profile.php', 'class-source-policy-query-context.php', 'class-source-policy-no-reconnect-connection.php' ) as $file ) {
		require_once dirname( __DIR__, 2 ) . '/includes/' . $file;
	}
	require_once __DIR__ . '/source-policy-bootstrap-error-evidence.php';
	add_action( 'all', $all, 1 );
	$armed  = true;
	$report = array();
	foreach ( array( 'core-construction', 'core-classification', 'factory', 'admission', 'public-query', 'private-bootstrap' ) as $group ) {
		$events = array();
		$class  = new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' );
		$owner  = $class->newInstanceWithoutConstructor();
		$class->getConstructor()->invoke( $owner );
		if ( 'core-construction' === $group ) {
			$result = new WP_Error( 'source_policy_unavailable' );
		} elseif ( 'core-classification' === $group ) {
			$result = is_wp_error( new WP_Error() );
		} elseif ( 'factory' === $group ) {
			$result = $owner::create();
		} elseif ( 'admission' === $group ) {
			$result = $owner->admit_profile();
		} elseif ( 'public-query' === $group ) {
			$result = $owner->query( 'SELECT never-dispatched' );
		} else {
			$result = $class->getMethod( 'bootstrap' )->invoke( $owner );
		}
		$expected = 'core-construction' === $group ? array( 'wp_error_added' ) : ( 'core-classification' === $group ? array( 'is_wp_error_instance' ) : array() );
		++$checks;
		if ( $expected !== $events ) {
			throw new RuntimeException( 'Unexpected error-hook transcript' ); }
		++$checks;
		if ( ! Alynt_Drime_Backups_Dashboard_Bootstrap_Error_Evidence::result_matches( $group, $result ) ) {
			throw new RuntimeException( 'Changed refusal result' ); }
		++$checks;
		if ( null !== $owner->dbh || $owner->ready || 0 !== $owner->num_queries || ! $owner->close() ) {
			throw new RuntimeException( 'Handle-free lifecycle changed' ); }
		$report[ $group ] = $events;
	}
	remove_action( 'all', $all, 1 );
	$armed = false;
	++$checks;
	if ( has_filter( 'all' ) || has_filter( 'query' ) || isset( $GLOBALS['wpdb'] ) ) {
		throw new RuntimeException( 'Retained private registration' ); }
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed CLI labels, integer counts and fixed observed hook names only.
	echo 'error-hook audit checks:', $checks, ' groups:', count( $report ), ' events:', json_encode( $report ), " native_connections:0 sql_queries:0 fixtures:0\n";
} catch ( Throwable $error ) {
	fwrite( STDERR, 'Error-hook audit failed in ' . $group . ' after ' . $checks . " checks\n" );
	exit( 1 );
} finally {
	if ( $armed ) {
		remove_action( 'all', $all, 1 ); }
}
