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
require_once __DIR__ . '/source-policy-minimum-peer-scope.php';

/** Fixed-scope fixture SQL, no application identifier or raw payload output.
 *
 * @param string $sql Internally composed statement.
 * @return array|bool
 */
function adba_peer( $sql ) {
	global $peer, $request;
	$table = $request[2] . 'options';
	adbb_check( 1 === preg_match( '/\Aadbm_[a-f0-9]{12}_options\z/', $table ), 'owned peer table' );
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
		adba_peer( "INSERT INTO $table (option_name,option_value,autoload) VALUES ('alynt_drime_backups_dashboard_source_policies',X'$hex','no')" );
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

/** Fixed error classification, never truthiness.
 *
 * @param mixed  $value Actual result.
 * @param string $code Fixed expected code.
 * @return void
 */
function adba_error( $value, $code ) {
	adbb_check( $value instanceof WP_Error && $value->get_error_code() === $code, 'exact owned adapter error' );}
