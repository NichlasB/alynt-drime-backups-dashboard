<?php
/**
 * Genuine core hook parity and controlled fixture interleavings, not plugin bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Only fixed owned clone writes through separate bounded wpdb connection.
if ( ! defined( 'ADB_POLICY_PROOF' ) ) {
	exit( 1 );
}

/**
 * Registers exact owned trace callbacks and returns them for precise removal.
 *
 * @param array $events Owned trace sink.
 * @return array Exact callback manifest.
 */
function adbp_trace( &$events ) {
	$name     = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
	$manifest = array();
	foreach ( array( "sanitize_option_$name", "pre_update_option_$name", 'pre_update_option', 'update_option', "update_option_$name", 'updated_option', 'add_option', "add_option_$name", 'added_option' ) as $hook ) {
		$callback = function ( ...$args ) use ( &$events, $hook ) {
			$events[] = array( $hook, $args );
			return $args[0];
		};
		add_filter( $hook, $callback, 10, 3 );
		$manifest[ $hook ] = $callback;
	}
	return $manifest;
}

/**
 * Qualifies primitive hook argument/order parity, rejection and post-commit failure.
 *
 * @return void
 */
function adbp_hook_cases() {
	global $wpdb, $groups, $queries, $core_add_control;
	$name = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
	$base = array( 9 => array( 'wpvivid' => 'external_optional' ) );
	$next = $base + array( 7 => $base[9] );
	foreach ( array( 'insert', 'update', 'noop' ) as $operation ) {
		adbp_seed( 'insert' === $operation ? null : $base );
		$core_events = array();
		$manifest    = adbp_trace( $core_events );
		try {
			$core_add_control = 'insert' === $operation;
			$control          = 'insert' === $operation ? add_option( $name, array( 7 => $base[9] ), '', false ) : update_option( $name, 'noop' === $operation ? $base : $next, false );
			adbp_check( ( 'noop' !== $operation ) === $control, 'positive/unchanged genuine Options API control' );
		} finally {
			$core_add_control = false;
			foreach ( $manifest as $hook => $callback ) {
				remove_filter( $hook, $callback );
			}
		}
		adbp_seed( 'insert' === $operation ? null : $base );
		$before           = adbp_observe();
		$candidate_events = array();
		$manifest         = adbp_trace( $candidate_events );
		try {
			adbp_check( array( 'status' => 'noop' === $operation ? 'unchanged' : 'saved' ) === adbp_save( $before['model']->revision(), 'noop' === $operation ? 9 : 7, 'external_optional' ), 'native candidate positive outcome' );
			adbp_check( $core_events === $candidate_events && count( $core_events ) >= 3, 'non-empty exact core callback order and arguments: ' . $operation );
		} finally {
			foreach ( $manifest as $hook => $callback ) {
				remove_filter( $hook, $callback );
			}
		}
	}
	$groups[] = 'core-sanitize-prewrite-success-hook-parity';
	adbp_seed( $base );
	$before = adbp_observe();
	$deny   = function () {
		return array();
	};
	add_filter( "pre_update_option_$name", $deny );
	try {
		$count = $queries;
		adbp_error( adbp_save( $before['model']->revision(), 7, 'external_optional' ), 'source_policy_unavailable' );
		adbp_check( 1 === $queries - $count, 'transforming filter rejects before mutation' );
	} finally {
		remove_filter( "pre_update_option_$name", $deny );
	}
	add_filter( "pre_option_$name", $deny );
	try {
		$count = $queries;
		adbp_error( adbp_save( $before['model']->revision(), 7, 'external_optional' ), 'source_policy_unavailable' );
		adbp_check( $count === $queries, 'unqualified read projection rejects before raw read' );
	} finally {
		remove_filter( "pre_option_$name", $deny );
	}
	$reentry = function ( $value ) use ( $before, &$queries ) {
		$count = $queries;
		adbp_error( adbp_save( $before['model']->revision(), 8, 'external_optional' ), 'source_policy_unavailable' );
		adbp_check( $queries === $count, 'owner reentry has no protected read' );
		return $value;
	};
	add_filter( "pre_update_option_$name", $reentry );
	try {
		adbp_check( array( 'status' => 'saved' ) === adbp_save( $before['model']->revision(), 7, 'external_optional' ), 'outer save remains genuine positive' );
	} finally {
		remove_filter( "pre_update_option_$name", $reentry );
	}
	$throw = function () {
		throw new RuntimeException( 'Owned observer failure' );
	};
	add_action( "update_option_$name", $throw );
	try {
		$before = adbp_observe();
		adbp_error( adbp_save( $before['model']->revision(), 7, 'required' ), 'source_policy_write_uncertain' );
		adbp_check( adbp_observe()['map'] === $base, 'genuine committed row survives observer exception without compensation' );
	} finally {
		remove_action( "update_option_$name", $throw );
	}
	$groups[] = 'filter-reentry-postcommit-failure';
	adbp_failure_cases( $base );
	adbp_interleavings( $base );
}

/**
 * Mutates between admission and CAS using a separate genuine connection.
 *
 * @param array $base Owned fixture map.
 * @return void
 */
function adbp_interleavings( array $base ) {
	global $wpdb, $groups, $owned_connections;
	$name = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
	$peer = new wpdb( 'root', getenv( 'MYSQL_PWD' ), 'local', '127.0.0.1:10004' );
	$peer->hide_errors();
	$peer->suppress_errors( true );
	$peer->set_prefix( $wpdb->prefix );
	adbp_check( $peer->ready && false !== $peer->query( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1' ), 'bounded independent peer session' );
	$parent_id           = $wpdb->get_var( 'SELECT CONNECTION_ID()' );
	$peer_id             = $peer->get_var( 'SELECT CONNECTION_ID()' );
	$owned_connections[] = (int) $peer_id;
	adbp_check( $parent_id !== $peer_id, 'actors have distinct native connections' );
	try {
		foreach ( array( 'same', 'different', 'first', 'autoload-case', 'autoload-space', 'value-case', 'value-space' ) as $case ) {
			adbp_seed( 'first' === $case ? null : $base );
			$before = adbp_observe();
			$hook   = 'first' === $case ? 'add_option' : 'update_option';
			$winner = $base + array( 'same' === $case ? 7 : 8 => $base[9] );
			$actor  = function () use ( $peer, $name, $winner, $base, $case ) {
				$values = array(
					'option_value' => maybe_serialize( $winner ),
					'autoload'     => 'off',
				);
				if ( 'autoload-case' === $case || 'autoload-space' === $case ) {
					$values = array( 'autoload' => 'autoload-case' === $case ? 'OFF' : 'off ' );
				}
				if ( 'value-case' === $case || 'value-space' === $case ) {
					$values = array( 'option_value' => 'value-case' === $case ? str_replace( 'external_optional', 'EXTERNAL_OPTIONAL', maybe_serialize( $base ) ) : maybe_serialize( $base ) . ' ' );
				}
				$written = 'first' === $case ? $peer->insert( $peer->options, array( 'option_name' => $name ) + $values ) : $peer->update( $peer->options, $values, array( 'option_name' => $name ) );
				adbp_check( 1 === $written, 'independent contender acknowledged' );
			};
			add_action( $hook, $actor );
			try {
				$contender = adbp_save( $before['model']->revision(), 7, 'external_optional' );
				adbp_check( is_wp_error( $contender ) && 'source_policy_conflict' === $contender->get_error_code(), 'conditional interleaving conflict: ' . $case );
				$row            = $peer->get_row( $peer->prepare( "SELECT option_value,autoload FROM $peer->options WHERE option_name=%s", $name ), ARRAY_A );
				$expected_bytes = maybe_serialize( in_array( $case, array( 'autoload-case', 'autoload-space', 'value-case', 'value-space' ), true ) ? $base : $winner );
				if ( 'value-case' === $case || 'value-space' === $case ) {
					$expected_bytes = 'value-case' === $case ? str_replace( 'external_optional', 'EXTERNAL_OPTIONAL', $expected_bytes ) : $expected_bytes . ' ';
				}
				adbp_check( $expected_bytes === $row['option_value'], 'loser never overwrites winner' );
				adbp_check( ( 'autoload-case' === $case ? 'OFF' : ( 'autoload-space' === $case ? 'off ' : 'off' ) ) === $row['autoload'], 'exact contender autoload bytes retained' );
			} finally {
				remove_action( $hook, $actor );
			}
		}
	} finally {
		$peer->close();
	}
	$groups[] = 'two-session-stale-cas-strict-create-binary-autoload';
}
