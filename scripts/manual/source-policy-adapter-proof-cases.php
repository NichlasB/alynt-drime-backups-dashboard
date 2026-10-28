<?php
/**
 * Actual dormant adapter cases, fixed owned rows only, never production dispatch.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Fixed approved disposable fixture and once-only peer operations.
if ( ! defined( 'ABSPATH' ) || ! function_exists( 'adbb_check' ) ) {
	exit( 1 );
}
require_once __DIR__ . '/source-policy-adapter-proof-scope.php';

/** Fixed-scope fixture SQL, no application identifier or raw payload output.
 *
 * @param string $sql Internally composed statement.
 * @return array|bool
 */
function adba_peer( $sql ) {
	global $peer, $request;
	$table = $request[2] . 'options';
	adbb_check( 1 === preg_match( '/\Aadbb_[a-f0-9]{12}_options\z/', $table ), 'owned peer table' );
	adbb_check( adba_sql_scope( $sql, $table ), 'finite owned peer SQL' );
	$result = mysqli_query( $peer, $sql );
	if ( ! $result instanceof mysqli_result ) {
		return $result;
	}
	try {
		return mysqli_fetch_all( $result, MYSQLI_ASSOC );
	} finally {
		mysqli_free_result( $result );
	}
}

/** Replaces only this proof's one owned row with primitive source-owned fixtures.
 *
 * @param array|null $map Explicit primitive map.
 * @return void
 */
function adba_seed( $map ) {
	global $request;
	$table = $request[2] . 'options';
	adba_peer( "DELETE FROM $table" );
	if ( null !== $map ) {
		$hex = bin2hex( serialize( $map ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Source-owned primitive fixtures only.
		adba_peer( "INSERT INTO $table (option_name,option_value,autoload) VALUES ('alynt_drime_backups_dashboard_source_policies',X'$hex','off')" );
	}
}

/** Actual physical row read, never application Options API/cache fallback.
 *
 * @return array
 */
function adba_row() {
	global $request;
	return adba_peer( 'SELECT option_value,autoload FROM ' . $request[2] . 'options' );
}

/** A source-owned callback registered before bootstrap, never an unknown grant.
 *
 * @param mixed $value Existing core hook argument.
 * @return mixed
 */
function adba_hook( $value ) {
	global $scenario, $adapter, $nested, $request, $events;
	$hook     = current_filter();
	$events[] = $hook;
	$name     = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
	if ( 'pre_update_option_' . $name === $hook ) {
		if ( 'race' === $scenario ) {
			adba_seed(
				array(
					9  => array( 'wpvivid' => 'external_optional' ),
					12 => array( 'wpvivid' => 'external_optional' ),
				)
			);
		} elseif ( 'transform' === $scenario ) {
			return array();
		} elseif ( 'reentry' === $scenario ) {
			$nested = $adapter->view_source_mode( 7, 'wpvivid' );
		} elseif ( 'lock' === $scenario ) {
			adba_peer( 'START TRANSACTION' );
			adba_peer( 'SELECT option_id FROM ' . $request[2] . 'options FOR UPDATE' );
		}
	} elseif ( 'add_option' === $hook && 'duplicate' === $scenario ) {
		adba_seed( array( 9 => array( 'wpvivid' => 'external_optional' ) ) );
	} elseif ( 'updated_option' === $hook && 'post-change' === $scenario ) {
		add_filter( 'all', 'adba_all', 1 );
	}
	return $value;
}

/** Private post-commit admission fault; never installed application hook state.
 *
 * @return void
 */
function adba_all() {}

/** Constructs the exact actual owner via its private audited bootstrap only.
 *
 * @param string $operation Fixed operation.
 * @param array  $args Finite case arguments.
 * @return mixed Actual adapter result.
 */
function adba_run( $operation, array $args ) {
	global $class, $frame, $binding, $owners, $adapter, $scenario, $last_commands, $events;
	$owner = $class->newInstanceWithoutConstructor();
	$class->getConstructor()->invoke( $owner );
	$context = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context' ) )->newInstanceWithoutConstructor();
	foreach ( array(
		'owned'     => $owner,
		'ambient'   => null,
		'core_file' => $binding['core_file'],
	) as $key => $value ) {
		adbb_bind( $context, $key, $value );
	}
	adbb_bind( $owner, 'query_context', $context );
	$bound = $binding;
	foreach ( Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::hook_names() as $hook ) {
		if ( isset( $GLOBALS['wp_filter'][ $hook ] ) ) {
			$bound['observers'][ $hook ] = $GLOBALS['wp_filter'][ $hook ]->callbacks;
		}
	}
	$bound['observer_files'][ __FILE__ ] = hash_file( 'sha256', __FILE__ );
	adbb_bind( $owner, 'bootstrap_frame', $frame );
	adbb_bind( $owner, 'bootstrap_binding', $bound );
	$owners[] = $owner;
	adbb_check( true === $class->getMethod( 'bootstrap' )->invoke( $owner ) && $owner->ready, 'actual adapter owner bootstrap' );
	$adapter = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter' ) )->newInstanceWithoutConstructor();
	adbb_bind( $adapter, 'connection', $owner );
	$before        = mysqli_get_client_stats()['com_query'];
	$events        = array();
	$result        = 'view' === $operation ? $adapter->view_source_mode( 7, 'wpvivid' ) : $adapter->save_source_mode_if_current( $args[0], 7, 'wpvivid', $args[1] );
	$last_commands = (int) mysqli_get_client_stats()['com_query'] - (int) $before;
	adbb_check( false === $owner->ready && null === $owner->dbh && null === $owner->profile() && true === $owner->close(), 'actual adapter terminal owned release' );
	if ( 'post-change' === $scenario ) {
		remove_filter( 'all', 'adba_all', 1 );
	}
	return $result;
}

/** Requires exact policy error DATA, never truthiness or an unavailable-only pass.
 *
 * @param mixed  $value Actual result.
 * @param string $code Exact contract.
 * @return void
 */
function adba_error( $value, $code ) {
	adbb_check( $value instanceof WP_Error && $value->get_error_code() === $code, 'exact owned adapter error' );
}

/** Ordered real cases with strict reporting unchanged; stops at first failure.
 *
 * @return void
 */
function adba_cases() {
	global $groups, $stage, $scenario, $last_commands, $nested, $events;
	$name  = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
	$hooks = array( "sanitize_option_$name", "pre_update_option_$name", 'pre_update_option', 'update_option', "update_option_$name", 'updated_option', 'add_option', "add_option_$name", 'added_option' );
	foreach ( $hooks as $hook ) {
		add_filter( $hook, 'adba_hook', 10, 3 );
	}
	$base     = array( 9 => array( 'wpvivid' => 'external_optional' ) );
	$scenario = 'normal';
	$stage    = 'positive';
	adba_seed( $base );
	$view = adba_run( 'view', array() );
	adbb_check( is_array( $view ) && array_keys( $view ) === array( 'mode', 'revision' ) && 'required' === $view['mode'], 'genuine same-observation view' );
	$old = $view['revision'];
	foreach ( array( 'alloptions', 'notoptions', $name ) as $key ) {
		wp_cache_set( $key, 'owned sentinel', 'options' );
	}
	wp_cache_set( 'owned-unrelated', 'keep', 'options' );
	adbb_check( 'saved' === adba_run( 'save', array( $old, 'external_optional' ) ) && 2 === $last_commands, 'actual positive single mutation' );
	$want = $base + array( 7 => array( 'wpvivid' => 'external_optional' ) );
	adbb_check(
		array(
			array(
				'option_value' => serialize( $want ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Primitive fixture comparison only.
				'autoload'     => 'off',
			),
		) === adba_row(),
		'physical non-target preservation and off encoding'
	); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Primitive fixture comparison.
	foreach ( array( 'alloptions', 'notoptions', $name ) as $key ) {
		$found = null;
		wp_cache_get( $key, 'options', false, $found );
		adbb_check( false === $found, 'genuine private cache absence' );
	}
	adbb_check( 'keep' === wp_cache_get( 'owned-unrelated', 'options' ) && in_array( 'updated_option', $events, true ), 'non-empty success hook and no global cache flush' );
	++$groups;
	$stage = 'noop-stale';
	$view  = adba_run( 'view', array() );
	adbb_check( 'unchanged' === adba_run( 'save', array( $view['revision'], 'external_optional' ) ) && 1 === $last_commands && ! in_array( 'updated_option', $events, true ), 'unchanged has one read and no success hook' );
	adba_error( adba_run( 'save', array( $old, 'external_optional' ) ), 'source_policy_conflict' );
	adbb_check( 1 === $last_commands, 'stale before already-satisfied no-op' );
	++$groups;
	$stage = 'update';
	$view  = adba_run( 'view', array() );
	adbb_check( 'saved' === adba_run( 'save', array( $view['revision'], 'required' ) ) && 2 === $last_commands, 'actual conditional removal' );
	++$groups;
	$stage    = 'race';
	$view     = adba_run( 'view', array() );
	$scenario = 'race';
	adba_error( adba_run( 'save', array( $view['revision'], 'external_optional' ) ), 'source_policy_conflict' );
	adbb_check( 4 === $last_commands && ! in_array( 'updated_option', $events, true ), 'contender reset insert and one failed CAS only' );
	++$groups;
	$stage    = 'duplicate';
	$scenario = 'normal';
	adba_seed( null );
	$view     = adba_run( 'view', array() );
	$scenario = 'duplicate';
	adba_error( adba_run( 'save', array( $view['revision'], 'external_optional' ) ), 'source_policy_write_uncertain' );
	adbb_check( 4 === $last_commands, 'strict native exception has no retry or follow-up read' );
	++$groups;
	$stage    = 'transform-reentry';
	$scenario = 'normal';
	adba_seed( $base );
	$view     = adba_run( 'view', array() );
	$scenario = 'transform';
	adba_error( adba_run( 'save', array( $view['revision'], 'external_optional' ) ), 'source_policy_unavailable' );
	adbb_check( 1 === $last_commands, 'changed candidate refuses before mutation' );
	$scenario = 'reentry';
	adbb_check( 'saved' === adba_run( 'save', array( $view['revision'], 'external_optional' ) ) && 2 === $last_commands, 'outer once-only save survives refused reentry' );
	adba_error( $nested, 'source_policy_unavailable' );
	++$groups;
	$stage    = 'lock';
	$scenario = 'normal';
	adba_seed( $base );
	$view     = adba_run( 'view', array() );
	$scenario = 'lock';
	try {
		adba_error( adba_run( 'save', array( $view['revision'], 'external_optional' ) ), 'source_policy_write_uncertain' );
		adbb_check( 4 === $last_commands, 'strict bounded lock exception has one mutation attempt' );
	} finally {
		adba_peer( 'ROLLBACK' );
	}
	++$groups;
	$stage    = 'post-change';
	$scenario = 'normal';
	$view     = adba_run( 'view', array() );
	$scenario = 'post-change';
	adba_error( adba_run( 'save', array( $view['revision'], 'external_optional' ) ), 'source_policy_write_uncertain' );
	adbb_check( 2 === $last_commands, 'post-commit admission fault never retries' );
	++$groups;
	foreach ( $hooks as $hook ) {
		remove_filter( $hook, 'adba_hook', 10 );
	}
}
