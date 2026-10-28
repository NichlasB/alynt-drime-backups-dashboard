<?php
/**
 * Exact private raw operation, never an owner, provider or application bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Explicit approved private raw reference; only fixed loopback connection, options, counters and one close, no SQL.
if ( ! defined( 'ADCR_PRIVATE' ) ) {
	exit( 1 );
}
require_once __DIR__ . '/source-policy-connected-raw-record.php';

/**
 * Fixed non-secret condition, never a collaborator message.
 *
 * @param bool   $condition Required fact.
 * @param string $stage Fixed internal stage.
 * @return void
 * @throws RuntimeException If unavailable.
 */
function adcr_check( $condition, $stage ) {
	if ( ! $condition ) {
		throw new RuntimeException( $stage ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed private stage only.
	}
}

/** Reads exact named native counter bytes, never coerced.
 *
 * @return string Canonical vector.
 */
function adcr_stats() {
	$stats  = mysqli_get_client_stats();
	$values = array();
	foreach ( Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::counters() as $name ) {
		adcr_check( isset( $stats[ $name ] ) && is_string( $stats[ $name ] ), 'counter-type' );
		$values[] = $stats[ $name ];
	}
	$wire = implode( ',', $values );
	adcr_check( false !== Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::vector( $wire ), 'counter-shape' );
	return $wire;
}

/**
 * Two actual holds around one direct close. Source function is fixed in private entry.
 *
 * @param string $run Bound run.
 * @param string $source Frozen source image.
 * @return void
 * @throws Throwable Preserves a primary failure independently of cleanup.
 */
function adcr_raw( $run, $source ) {
	$pid = getmypid();
	adcr_check( ! isset( $GLOBALS['wpdb'] ) && hash_equals( $source, adcr_image() ), 'pre-effect-source' );
	$before = adcr_stats();
	adcr_check( implode( ',', array_fill( 0, 15, '0' ) ) === $before, 'fresh-baseline' );
	$handle      = null;
	$owned       = null;
	$attempted   = false;
	$ack         = false;
	$close_error = 'none';
	try {
		$handle = mysqli_init();
		$owned  = $handle;
		adcr_check( $handle instanceof mysqli, 'allocation' );
		$initialized = adcr_stats();
		adcr_check( $before === $initialized, 'initialized-baseline' );
		foreach ( array(
			MYSQLI_OPT_CONNECT_TIMEOUT => 2,
			MYSQLI_OPT_READ_TIMEOUT    => 3,
			MYSQLI_OPT_LOCAL_INFILE    => 0,
		) as $option => $value ) {
			adcr_check( true === mysqli_options( $handle, $option, $value ), 'bounded-option' );
		}
		adcr_check( true === mysqli_real_connect( $handle, '127.0.0.1', 'root', getenv( 'MYSQL_PWD' ), 'local', 10004, null, 0 ), 'connected' );
		adcr_check( '10.6.23-MariaDB' === mysqli_get_server_info( $handle ), 'server-profile' );
		$connected = adcr_stats();
		$wire      = implode( '|', array( 'ADCR1', $run, $source, $pid, Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::PROFILE, 'connected', $before, $initialized, $connected, '1', $handle === $owned ? '1' : '0', isset( $GLOBALS['wpdb'] ) ? '1' : '0' ) ) . "\n";
		adcr_check( false !== Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::frame( $wire, 'connected', $run, $source, $pid ), 'connected-frame' );
		fwrite( STDOUT, $wire ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Strict bounded scalar frame above.
		fflush( STDOUT );
		adcr_check( Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::token( 'CLOSE', $run, $source, $pid ) === fgets( STDIN, 128 ), 'close-token' );
	} catch ( Throwable $error ) {
		$GLOBALS['adcr_primary_kind'] = in_array( get_class( $error ), array( 'RuntimeException', 'mysqli_sql_exception', 'Error' ), true ) ? get_class( $error ) : 'Throwable';
		throw $error;
	} finally {
		if ( $owned instanceof mysqli && ! $attempted ) {
			$attempted = true; // Immediately before the sole literal close, never retry after false/throw.
			try {
				$ack         = true === mysqli_close( $owned );
				$close_error = $ack ? 'none' : 'false';
			} catch ( Throwable $error ) {
				$close_error = 'Error' === get_class( $error ) ? 'Error' : 'Throwable';
			}
		}
		$GLOBALS['adcr_cleanup_kind'] = $close_error;
	}
	$after = adcr_stats();
	adcr_check( hash_equals( $source, adcr_image() ), 'terminal-source' );
	$wire = implode( '|', array( 'ADCR1', $run, $source, $pid, Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::PROFILE, 'closed', $after, $handle === $owned ? '1' : '0', $attempted ? '1' : '0', $ack ? '1' : '0', $close_error ) ) . "\n";
	adcr_check( false !== Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::frame( $wire, 'closed', $run, $source, $pid ), 'closed-frame' );
	fwrite( STDOUT, $wire ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Strict bounded scalar frame above.
	fflush( STDOUT );
	adcr_check( Alynt_Drime_Backups_Dashboard_Raw_Connected_Record::token( 'EXIT', $run, $source, $pid ) === fgets( STDIN, 128 ), 'exit-token' );
}
