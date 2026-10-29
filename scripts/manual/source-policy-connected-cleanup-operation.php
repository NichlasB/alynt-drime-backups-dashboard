<?php
/**
 * Fresh connected-owner observations, never conversion of an ADBC1 receipt.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'adbb_check' ) ) {
	exit( 1 );
}
require_once __DIR__ . '/source-policy-connected-cleanup-evidence.php';
require_once __DIR__ . '/source-policy-cleanup-native-cases.php'; // Pure shared statistics/probe helpers only; no legacy owner invocation.

/** Runs the fixed actual owner, collecting fresh terminal facts without grants.
 *
 * @param ReflectionClass $owner_class Exact closed receiver.
 * @param array           $frame Fixed owned empty-fixture settings.
 * @param array           $binding Audited declaration bindings.
 * @param array           $request Private fixed request.
 * @param string          $source Frozen loaded-image correlation.
 * @return string ADBC2 terminal DATA, still pending independent reconciliation.
 */
function adbc2_owner( ReflectionClass $owner_class, array $frame, array $binding, array $request, $source ) {
	$name = $request[5];
	adbb_check( in_array( $name, Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::cases(), true ), 'fixed connected case' );
	$started   = hrtime( true );
	$owner     = adbb_construct( $owner_class );
	$bootstrap = $owner_class->getMethod( 'bootstrap' );
	if ( PHP_VERSION_ID < 80100 ) {
		$bootstrap->setAccessible( true );
	}
	$context = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context' ) )->newInstanceWithoutConstructor();
	foreach ( array(
		'owned'     => $owner,
		'ambient'   => null,
		'core_file' => $binding['core_file'],
	) as $key => $value ) {
		adbb_bind( $context, $key, $value );
	}
	adbb_bind( $owner, 'query_context', $context );
	if ( 'missing-table' === $name ) {
		$frame['table'] = $request[2] . 'missing_options';
	}
	adbb_bind( $owner, 'bootstrap_frame', $frame );
	adbb_bind( $owner, 'bootstrap_binding', $binding );
	$read   = static function ( $key ) use ( $owner_class, $owner ) {
		$property = $owner_class->getProperty( $key );
		if ( PHP_VERSION_ID < 80100 ) {
			$property->setAccessible( true );
		}
		return $property->getValue( $owner );
	};
	$called = 0;
	$hook   = null;
	$watch  = static function ( $value ) use ( &$called ) {
		++$called;
		return $value;
	};
	try {
		$before = adbc_stats();
		adbb_check( ! $owner->ready && $owner->admit_profile() instanceof WP_Error && false === $owner->query( 'SELECT never-dispatched' ), 'closed public entry before bootstrap' );
		$refused = false;
		try {
			$owner->execute( 'SELECT never-dispatched' );
		} catch ( RuntimeException $error ) {
			$refused = true;
		}
		adbb_check( $refused, 'pre-active execution refuses' );
		$result = $bootstrap->invoke( $owner );
		$boot   = adbc_stats();
		$active = true === $owner->ready;
		adbb_check( 'positive' === $name ? true === $result && $active : $result instanceof WP_Error && ! $active, 'actual connected positive or refusal outcome' );
		$round = false;
		$seal  = false;
		if ( 'positive' === $name ) {
			$literal = "quote ' double \" slash \\ percent %s %d 100% nul\0 line\n return\r end\x1a";
			$sql     = $owner->prepare_checked( 'SELECT %s AS bytes', $literal );
			$ack     = $owner->execute( $sql );
			$round   = 1 === $ack['affected'] && 0 === $ack['errno'] && $literal === $owner->last_result[0]->bytes;
			$owner->flush(); // Actual inherited core result cleanup; isolated tests model this separately.
			$hook = 'all';
			add_filter( $hook, $watch, 1 );
			$commands = adbc_stats();
			try {
				$owner->execute( 'SELECT never-dispatched' );
			} catch ( RuntimeException $error ) {
				$seal = adbc_stats() === $commands && 0 === $called;
			}
		}
		adbb_check( $bootstrap->invoke( $owner ) instanceof WP_Error, 'bootstrap replay denied' );
		$close     = $owner->close();
		$after     = adbc_stats();
		$cached    = $owner->close() === $close && adbc_stats() === $after;
		$handle    = $read( 'bootstrap_handle' );
		$allocated = $handle instanceof mysqli;
		if ( null !== $hook ) {
			remove_filter( $hook, $watch, 1 );
			$hook = null;
		}
		$row     = array_combine(
			Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::fields(),
			array(
				'ADBC2',
				$request[3],
				$name,
				Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::PROFILE,
				$source,
				(string) getmypid(),
				$before,
				$boot,
				$after,
				$allocated ? '1' : '0',
				$active ? 'active' : 'unavailable',
				$active ? '1' : '0',
				$close ? '1' : '0',
				$allocated && $read( 'close_attempted' ) ? '1' : '0',
				$cached ? '1' : '0',
				$context->owns( $owner ) ? '1' : '0',
				isset( $GLOBALS['wpdb'] ) ? '1' : '0',
				null === $owner->dbh ? '1' : '0',
				null === $owner->profile() && null === $read( 'qualification' ) ? '1' : '0',
				null === $read( 'bootstrap_frame' ) && null === $read( 'bootstrap_binding' ) && '' === $owner->dbpassword ? '1' : '0',
				! has_filter( 'query' ) && ! has_filter( 'all' ) && ! has_filter( 'incompatible_sql_modes' ) ? '1' : '0',
				empty( $owner->last_result ) ? '1' : '0',
				$allocated && adbc_closed( $handle ) ? '1' : '0',
				$read( 'native_state' ),
				(string) $called,
				(string) $read( 'bootstrap_position' ),
				(string) $owner->num_queries,
				$round ? '1' : '0',
				$seal ? '1' : '0',
				(string) (int) ( ( hrtime( true ) - $started ) / 1000000 ),
				Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::POLICY,
			)
		);
		$summary = Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::inspect( $row, $request[3], $name, $source, getmypid() );
		adbb_check( is_array( $summary ) && 'pending-live-reconciliation' === $summary['release_evidence'] && 'not-established' === $summary['native_qualification'], 'fresh pending ADBC2 terminal facts' );
		return implode( '|', $row ) . "\n";
	} finally {
		$owner->close(); // Once-only cached attempt, including any failed assertion.
		if ( null !== $hook ) {
			remove_filter( $hook, $watch, 1 );
		}
	}
}
