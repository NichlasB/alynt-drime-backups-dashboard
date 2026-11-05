<?php
/**
 * Owned genuine-source production-origin join fixtures, never application wiring.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'adbb_check' ) ) {
	exit( 1 );
}
/** Read proof-owned private state only; never supplies ready/facts/policy.
 *
 * @param object $receiver Owned object.
 * @param string $key Fixed private field.
 * @return mixed
 */
function adbp_read( $receiver, $key ) {
	$property = new ReflectionProperty( $receiver, $key );
	if ( PHP_VERSION_ID < 80100 ) {
		$property->setAccessible( true );
	}
	return $property->getValue( $receiver );
}
/** Compose handle-free owned fixtures; genuine context selects all source/runtime policy.
 *
 * @return array Private proof fixtures only.
 */
function adbp_fixture() {
	global $class, $frame, $binding, $owners, $ambient;
	$owner    = adbb_construct( $class );
	$owners[] = $owner;
	$query    = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context' ) )->newInstanceWithoutConstructor();
	foreach ( array(
		'owned'     => $owner,
		'ambient'   => $ambient,
		'core_file' => $binding['core_file'],
	) as $key => $value ) {
		adbb_bind( $query, $key, $value );
	}
	foreach ( array(
		'query_context'     => $query,
		'bootstrap_frame'   => $frame,
		'bootstrap_binding' => $binding,
	) as $key => $value ) {
		adbb_bind( $owner, $key, $value );
	}
	$context = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context' ) )->newInstanceWithoutConstructor();
	adbb_bind( $context, 'receiver', $owner );
	adbb_bind( $context, 'production_query', $query );
	$adapter = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter' ) )->newInstanceWithoutConstructor();
	adbb_bind( $adapter, 'connection', $owner );
	$provider = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Lazy_Provider' ) )->newInstanceWithoutConstructor();
	foreach ( array(
		'owner'     => $adapter,
		'context'   => $context,
		'bootstrap' => true,
	) as $key => $value ) {
		adbb_bind( $provider, $key, $value );
	}
	adbb_check( null === $owner->profile() && false === $owner->ready && null === $owner->dbh && null === adbp_read( $owner, 'minimum_lab_scope' ), 'fresh production owner without imported native facts or lab scope' );
	adbb_check( null === adbp_read( $context, 'policy' ) && null === adbp_read( $context, 'production_runtime' ), 'no reflected source/runtime policy' );
	adbb_check( false === $context->valid( $owner ) && false === $context->activate( $owner, $query ), 'pre-connect state grants no active admission' );
	return array( $owner, $query, $context, $adapter, $provider );
}
/** Actual capture and initializer, also used by the existing genuine byte probe.
 *
 * @return object Initialized proof-owned receiver, never exported outside worker.
 */
function adbp_owner() {
	list( $owner, $query, $context, $adapter ) = adbp_fixture();
	adbb_check( true === $context->capture_bootstrap(), 'genuine production source/runtime/config capture' );
	adbb_check( false === $context->valid( $owner ) && false === $owner->query( 'SELECT never-dispatched' ), 'capture does not authorize query' );
	adbb_check( true === $adapter->initialize_context( $context ), 'actual production-bound initializer' );
	adbb_check( $context->valid( $owner ) && $owner->production_completed( $context, $query ), 'actual initializer-owned active admission' );
	adbb_check( '5.5.5-10.6.23-MariaDB' === $owner->profile()['server'] && 'utf8mb4_unicode_ci' === $owner->collate, 'exact genuine greeting/helper result' );
	adbb_check( ! array_key_exists( 'password', adbp_read( $context, 'frame' ) ) && '' === adbp_read( $owner, 'dbpassword' ), 'active context drops credential snapshots' );
	return $owner;
}
/** Execute actual lazy capture->initialize->active adapter dispatch and cleanup.
 *
 * @param string $operation Fixed operation.
 * @param array  $args Fixed fixture arguments.
 * @return mixed
 */
function adbp_run( $operation, array $args = array() ) {
	global $last_commands;
	list( $owner, $query, $context, $adapter, $provider ) = adbp_fixture();
	$result        = 'view' === $operation ? $provider->view_source_mode( 7, 'wpvivid' ) : $provider->save_source_mode_if_current( $args[0], 7, 'wpvivid', $args[1] );
	$last_commands = $owner->num_queries;
	adbb_check( 8 === adbp_read( $owner, 'bootstrap_position' ) && 12 === adbp_read( $owner, 'bootstrap_credits' ), 'unchanged eight steps and twelve credits' );
	foreach ( array( 'frame', 'binding', 'policy', 'receiver', 'production_query', 'production_expected', 'production_runtime' ) as $key ) {
		adbb_check( null === adbp_read( $context, $key ), 'terminal context references cleared' );
	}
	adbb_check( null === adbp_read( $owner, 'bootstrap_frame' ) && null === adbp_read( $owner, 'production_context' ) && null === adbp_read( $owner, 'minimum_lab_scope' ), 'terminal owner references cleared without lab origin' );
	$commands = $owner->num_queries;
	adba_error( $provider->view_source_mode( 7, 'wpvivid' ), 'source_policy_unavailable' );
	adbb_check( $commands === $owner->num_queries && false === $context->capture_bootstrap(), 'single-use provider and closed context cannot replay' );
	adb0_released( $owner );
	return $result;
}
