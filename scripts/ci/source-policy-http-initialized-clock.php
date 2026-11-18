<?php
/**
 * Original-job shared setup clock; definitions only, no environment or IO.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Closed private profile selection; neither label is runtime authority.
 *
 * @param mixed $context Source-owned private context.
 * @param mixed $approval Explicit native v2 child flag.
 * @return string|false
 */
function adbh_initialized_model( $context, $approval ) {
	if ( ! is_array( $context ) ) {
		return false;
	}
	$model = $context['model'] ?? 'owned-http-l714-v1';
	return 'owned-http-l714-v1' === $model || ( 'owned-http-l714-initialized-v2' === $model && 'true' === $approval ) ? $model : false;
}

/**
 * Bind both setup children to the frozen parent's clock, never a fresh timeout.
 *
 * @param array        $context Frozen private job DATA.
 * @param Closure|null $read Isolated clock double; native callers omit it.
 * @return Closure
 * @throws RuntimeException On malformed, reversed or expired credit.
 */
function adbh_initialized_clock( $context, $read = null ) {
	foreach ( array( 'origin_ns', 'setup_started', 'setup_deadline' ) as $key ) {
		if ( ! isset( $context[ $key ] ) || ! is_string( $context[ $key ] ) || ! preg_match( '/\A(?:0|[1-9][0-9]{0,18})\z/', $context[ $key ] ) || (string) (int) $context[ $key ] !== $context[ $key ] ) {
			throw new RuntimeException( 'Initialized clock DATA refused' );
		}
	}
	$origin   = (int) $context['origin_ns'];
	$started  = (int) $context['setup_started'];
	$deadline = (int) $context['setup_deadline'];
	if ( $origin <= 0 || $started < 0 || $started > 450000 || 60000 !== $deadline - $started || ( null !== $read && ! $read instanceof Closure ) ) {
		throw new RuntimeException( 'Initialized clock credit refused' );
	}
	$read = $read ?? static function () {
		return hrtime( true );
	};
	$last = $started;
	return static function () use ( $origin, $deadline, $read, &$last ) {
		$raw = $read();
		if ( ! is_int( $raw ) || $raw < $origin ) {
			throw new RuntimeException( 'Initialized clock origin refused' );
		}
		$at = intdiv( $raw - $origin, 1000000 );
		if ( $at < $last || $at >= $deadline ) {
			throw new RuntimeException( 'Initialized setup deadline refused' );
		}
		$last = $at;
		return $at;
	};
}

/** Pre-execution installer clock fence; the existing SQL/count observer stays.
 *
 * @param string $sql Original core bytes.
 * @return string
 */
function adbh_initialized_setup_deadline( $sql ) {
	( $GLOBALS['adbh_initialized_clock'] )();
	return $sql;
}
