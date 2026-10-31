<?php
/**
 * Fixed native owner construction with an independently core-created ambient.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Approved private owned proof only.
if ( ! defined( 'ABSPATH' ) || ! function_exists( 'adbb_check' ) ) {
	exit( 1 );
}

/** Constructs the actual closed owner; no ready/facts/handle injection.
 *
 * @return object Actual bootstrapped owner.
 */
function adb0_owner() {
	global $class, $frame, $binding, $owners, $ambient, $evidence;
	$scope   = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Minimum_Lab_Scope' ) )->newInstanceWithoutConstructor();
	$owner   = adbb_construct( $class );
	$context = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context' ) )->newInstanceWithoutConstructor();
	foreach ( array(
		'owned'     => $owner,
		'ambient'   => $ambient,
		'core_file' => $binding['core_file'],
	) as $key => $value ) {
		adbb_bind( $context, $key, $value );
	}
	adbb_bind( $owner, 'query_context', $context );
	$constructor = new ReflectionMethod( $scope, '__construct' );
	$constructor->setAccessible( true );
	$constructor->invoke( $scope, $owner, $context, $frame, $evidence );
	adbb_bind( $owner, 'minimum_lab_scope', $scope );
	$bound = $binding;
	foreach ( Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::hook_names() as $hook ) {
		if ( isset( $GLOBALS['wp_filter'][ $hook ] ) ) {
			$bound['observers'][ $hook ] = $GLOBALS['wp_filter'][ $hook ]->callbacks;
		}
	}
	$bound['observer_files'] = adb0_observer_binding();
	adbb_bind( $owner, 'bootstrap_frame', $frame );
	adbb_bind( $owner, 'bootstrap_binding', $bound );
	$owners[] = $owner; // Retain before the first fallible native bootstrap.
	adbb_check( 0 === ( new mysqli_driver() )->report_mode, 'core-selected OFF before owner allocation' );
	$bootstrap = $class->getMethod( 'bootstrap' );
	$bootstrap->setAccessible( true );
	adbb_check( true === $bootstrap->invoke( $owner ) && $owner->ready, 'actual ambient-bound bootstrap' );
	adbb_check( 0 === ( new mysqli_driver() )->report_mode, 'owned initializer preserves OFF' );
	return $owner;
}

/** Verifies release without closing or rewriting the ambient registration/handle.
 *
 * @param object $owner Exact owned connection.
 * @return void
 */
function adb0_released( $owner ) {
	global $ambient, $ambient_handle, $ambient_id, $ambient_query;
	adbb_check( false === $owner->ready && null === $owner->dbh && null === $owner->profile() && true === $owner->close(), 'owned terminal release' );
	adbb_check(
		$GLOBALS['wpdb'] === $ambient && $ambient->dbh === $ambient_handle && true === $ambient->ready
		&& mysqli_thread_id( $ambient_handle ) === $ambient_id,
		'ambient identity and handle preserved'
	);
	adbb_check( $ambient_query === $GLOBALS['wp_filter']['query']->callbacks, 'exact ambient callback registry preserved' );
	adbb_check( 0 === ( new mysqli_driver() )->report_mode, 'no report-mode restoration or toggle' );
}

/** Executes one actual adapter operation with a fresh owned connection.
 *
 * @param string $operation Fixed operation.
 * @param array  $args Fixed fixture arguments.
 * @return mixed Actual result.
 */
function adb0_run( $operation, array $args = array() ) {
	global $adapter, $scenario, $last_commands, $events;
	$owner   = adb0_owner();
	$adapter = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter' ) )->newInstanceWithoutConstructor();
	adbb_bind( $adapter, 'connection', $owner );
	$before        = (int) mysqli_get_client_stats()['com_query'];
	$events        = array();
	$result        = 'view' === $operation ? $adapter->view_source_mode( 7, 'wpvivid' ) : $adapter->save_source_mode_if_current( $args[0], 7, 'wpvivid', $args[1] );
	$last_commands = (int) mysqli_get_client_stats()['com_query'] - $before;
	adb0_released( $owner );
	if ( 'post-change' === $scenario ) {
		remove_filter( 'all', 'adba_all', 1 );
	}
	return $result;
}
