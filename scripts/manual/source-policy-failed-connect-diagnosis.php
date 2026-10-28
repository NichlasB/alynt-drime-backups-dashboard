<?php
/**
 * Owned failed-connect observations only; no qualification, SQL or fixture DDL.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Explicit bounded private driver diagnosis; no successful connection or application bootstrap is intended.
if ( PHP_SAPI !== 'cli' || ! function_exists( 'adbb_check' ) || ! isset( $argv[3], $frame, $binding, $class )
	|| ! in_array( $argv[3], array( 'diagnose-raw', 'diagnose-owner' ), true ) ) {
	exit( 1 );
}

/**
 * Retains exact counter bytes/types, including signed or wrapped observations.
 *
 * @param string|null $phase Fixed diagnostic stage, null for socket-free tests.
 * @return array No handle or native error message.
 */
function adbb_diagnostic_stats( $phase = null ) {
	$stats = mysqli_get_client_stats();
	$fixed = array();
	$types = array();
	foreach ( array( 'connect_success', 'connect_failure', 'active_connections', 'explicit_close', 'implicit_close', 'reconnect', 'pconnect_success', 'com_query' ) as $key ) {
		adbb_check( isset( $stats[ $key ] ) && ( is_string( $stats[ $key ] ) || is_int( $stats[ $key ] ) || is_float( $stats[ $key ] ) ) && 1 === preg_match( '/\A-?[0-9]{1,20}(?:\.[0-9]{1,20})?(?:[Ee][+-]?[0-9]{1,3})?\z/', (string) $stats[ $key ] ), 'bounded counter DATA: ' . $key );
		$fixed[ $key ] = $stats[ $key ];
		$types[ $key ] = gettype( $stats[ $key ] );
	}
	$fixed['active_type']   = gettype( $stats['active_connections'] );
	$fixed['counter_types'] = $types;
	if ( in_array( $phase, array( 'before', 'allocated', 'failed', 'closed', 'released', 'cached-close' ), true ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Allowlisted phase and bounded numeric DATA only, never native error text.
		echo $phase, ':', json_encode( $fixed ), "\n";
	}
	return $fixed;
}

/**
 * A closed-object read probe only, never SQL or an exported handle.
 *
 * @param mysqli $handle Exact owned native object.
 * @return bool Whether the native API rejects the already-closed object.
 */
function adbb_diagnostic_closed( mysqli $handle ) {
	try {
		mysqli_errno( $handle );
		return false;
	} catch ( Error $error ) {
		return true;
	}
}

/**
 * Keeps an exception's actual stage/class, never its private message or trace.
 *
 * @param Throwable $error Actual diagnostic exception.
 * @param string    $stage Fixed native operation name.
 * @return string Fixed support-safe observation.
 */
function adbb_diagnostic_failure( Throwable $error, $stage ) {
	$stage = in_array( $stage, array( 'raw-allocate', 'raw-connect', 'raw-errno', 'raw-failed-snapshot', 'raw-close', 'raw-closed-snapshot', 'raw-probe', 'raw-cleanup' ), true ) ? $stage : 'unknown';
	$kind  = get_class( $error );
	$kind  = in_array( $kind, array( 'Error', 'RuntimeException', 'mysqli_sql_exception' ), true ) ? $kind : 'Throwable';
	return 'diagnostic-exception stage:' . $stage . ' class:' . $kind . "\n";
}

/**
 * One failed connect in each separately invoked process, without a table.
 *
 * @param ReflectionClass $owner_class Fixed dormant owner.
 * @param array           $frame Fixed private settings, never application config.
 * @param array           $binding Pinned source/observer metadata.
 * @param string          $mode Exact diagnostic case.
 * @return array Support-safe observations, never native eligibility.
 * @throws Throwable On failed observation or unconfirmed native cleanup.
 */
function adbb_failed_connect_diagnosis( ReflectionClass $owner_class, array $frame, array $binding, $mode ) {
	$started           = hrtime( true );
	$frame['database'] = str_replace( 'options', 'absent', $frame['table'] );
	$report            = array(
		'mode'   => $mode,
		'before' => adbb_diagnostic_stats( 'before' ),
	);
	adbb_check( '0' === $report['before']['active_connections'], 'fresh diagnostic counter baseline' );
	if ( 'diagnose-raw' === $mode ) {
		$handle    = mysqli_init();
		$attempted = false;
		$stage     = 'raw-allocate';
		try {
			adbb_check( $handle instanceof mysqli, 'owned raw allocation' );
			foreach ( array(
				MYSQLI_OPT_CONNECT_TIMEOUT => 2,
				MYSQLI_OPT_READ_TIMEOUT    => 3,
				MYSQLI_OPT_LOCAL_INFILE    => 0,
			) as $option => $value ) {
				adbb_check( true === mysqli_options( $handle, $option, $value ), 'bounded raw driver option' );
			}
			$report['allocated'] = adbb_diagnostic_stats( 'allocated' );
			$failed              = false;
			$stage               = 'raw-connect';
			try {
				mysqli_real_connect( $handle, $frame['host'], $frame['user'], $frame['password'], $frame['database'], $frame['port'], null, 0 );
			} catch ( mysqli_sql_exception $error ) {
				$failed = 1049 === $error->getCode();
			}
			$stage = 'raw-errno';
			adbb_check( $failed && 1049 === mysqli_errno( $handle ), 'raw missing-database refusal' );
			$stage                         = 'raw-failed-snapshot';
			$report['failed']              = adbb_diagnostic_stats( 'failed' );
			$attempted                     = true;
			$stage                         = 'raw-close';
			$report['close_ack']           = mysqli_close( $handle );
			$stage                         = 'raw-closed-snapshot';
			$report['closed']              = adbb_diagnostic_stats( 'closed' );
			$stage                         = 'raw-probe';
			$report['closed_handle_probe'] = adbb_diagnostic_closed( $handle );
			unset( $handle );
			$report['released'] = adbb_diagnostic_stats( 'released' );
		} catch ( Throwable $error ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed stage/class, never exception message/trace.
			fwrite( STDERR, adbb_diagnostic_failure( $error, $stage ) );
			throw $error;
		} finally {
			if ( ! $attempted && $handle instanceof mysqli ) {
				$attempted = true;
				try {
					mysqli_close( $handle );
				} catch ( Throwable $cleanup_error ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Preserve secondary cleanup class without hiding the already-recorded primary stage.
					fwrite( STDERR, adbb_diagnostic_failure( $cleanup_error, 'raw-cleanup' ) );
					throw $cleanup_error;
				}
			}
		}
	} else {
		$owner   = $owner_class->newInstanceWithoutConstructor();
		$context = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context' ) )->newInstanceWithoutConstructor();
		$owner_class->getConstructor()->invoke( $owner );
		foreach ( array(
			'owned'     => $owner,
			'ambient'   => null,
			'core_file' => $binding['core_file'],
		) as $key => $value ) {
			adbb_bind( $context, $key, $value );
		}
		adbb_bind( $owner, 'query_context', $context );
		adbb_bind( $owner, 'bootstrap_frame', $frame );
		adbb_bind( $owner, 'bootstrap_binding', $binding );
		try {
			$result = $owner_class->getMethod( 'bootstrap' )->invoke( $owner );
			adbb_check( $result instanceof WP_Error && 'source_policy_unavailable' === $result->get_error_code(), 'actual owner refuses failed initialization' );
			$report['closed']    = adbb_diagnostic_stats( 'closed' );
			$report['close_ack'] = $owner->close();
			adbb_check( adbb_diagnostic_stats( 'cached-close' ) === $report['closed'], 'cached close performs no second driver cleanup' );
			$handle = $owner_class->getProperty( 'bootstrap_handle' )->getValue( $owner );
			adbb_check( $handle instanceof mysqli, 'observe only internally allocated object' );
			$report['closed_handle_probe'] = adbb_diagnostic_closed( $handle );
			$report['owner_state']         = $owner_class->getProperty( 'native_state' )->getValue( $owner );
			adbb_check( null === $owner->dbh && ! $owner->ready && 0 === $owner->num_queries && null === $owner->profile() && ! has_filter( 'query' ), 'terminal owner fields and owned callback cleanup' );
		} finally {
			$owner->close();
		}
	}
	adbb_check( '0' === $report['closed']['connect_success'] && '1' === $report['closed']['connect_failure'] && '0' === $report['closed']['com_query'] && '0' === $report['closed']['reconnect'] && '0' === $report['closed']['pconnect_success'], 'one failed connect, no SQL/reconnect/persistent session' );
	adbb_check( hrtime( true ) - $started < 10000000000, 'observed diagnostic envelope, not hard interruption' );
	return $report;
}
