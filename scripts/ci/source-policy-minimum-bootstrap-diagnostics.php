<?php
/**
 * Private harness observations only. Never admission, native IO or runtime wiring.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Accepts only the already source-owned receiver/context definitions.
 *
 * @param mixed $owner Existing owned receiver.
 * @param mixed $context Existing private context.
 * @return bool Whether fixed metadata inspection is safe.
 */
function adbm_bootstrap_observable( $owner, $context ) {
	$root         = dirname( __DIR__, 2 ) . '/includes/';
	$owner_file   = realpath( $root . 'class-source-policy-no-reconnect-connection.php' );
	$context_file = realpath( $root . 'class-source-policy-query-context.php' );
	return is_string( $owner_file ) && is_string( $context_file )
		&& is_object( $owner ) && 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' === get_class( $owner )
		&& is_object( $context ) && 'Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context' === get_class( $context )
		&& ( new ReflectionClass( $owner ) )->getFileName() === $owner_file
		&& ( new ReflectionClass( $context ) )->getFileName() === $context_file;
}

/** Captures pure prerequisite results, without mutating or admitting the owner.
 *
 * @param object $owner Existing owned receiver.
 * @param object $context Existing context.
 * @param array  $frame Existing private settings; never retained/exported.
 * @param array  $binding Existing source binding; never retained/exported.
 * @return string Settings/source/context-provenance/scope bits; u means unobserved.
 */
function adbm_bootstrap_before( $owner, $context, $frame, $binding ) {
	$bits = 'uuuu';
	if ( ! adbm_bootstrap_observable( $owner, $context ) ) {
		return $bits;
	}
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Diagnostic errors become unknown DATA, not a replacement first failure or admission decision.
	set_error_handler(
		static function () {
			throw new RuntimeException( 'Harness observation unavailable' );
		}
	);
	try {
		for ( $index = 0; $index < 4; ++$index ) {
			try {
				switch ( $index ) {
					case 0:
						$value = Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::settings( $frame );
						break;
					case 1:
						$value = Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::source( $owner, $binding );
						break;
					case 2:
						$provenance = new ReflectionMethod( $context, 'provenance' );
						if ( PHP_VERSION_ID < 80100 ) {
							$provenance->setAccessible( true );
						}
						$value = $context->owns( $owner ) && true === $provenance->invoke( $context ); // Fixed pure predicate only, never begin/finish/bootstrap.
						break;
					default:
						$value = is_array( $owner->minimum_lab_profile() );
				}
				$bits[ $index ] = true === $value ? '1' : ( false === $value ? '0' : 'u' );
			} catch ( Throwable $error ) {
				$bits[ $index ] = 'u'; // No raw message, fallback effect, or suppression in the real bootstrap.
			}
		}
	} finally {
		restore_error_handler();
	}
	return $bits;
}

/** Reads only fixed retained fields after the single invocation/internal cleanup.
 *
 * @param object $owner Existing owned receiver.
 * @param object $context Existing context.
 * @param string $before Pre-invocation observation bits.
 * @param mixed  $result Existing invocation result, never a new invocation.
 * @param bool   $threw Whether the original invocation threw.
 * @return array Bounded scalars only; never a receiver, handle, frame or payload.
 */
function adbm_bootstrap_after( $owner, $context, $before, $result, $threw = false ) {
	$row = array(
		'before'          => is_string( $before ) && preg_match( '/\A[01u]{4}\z/', $before ) ? $before : 'uuuu',
		'result'          => $threw ? 'threw' : ( true === $result ? 'true' : ( false === $result ? 'false' : ( is_object( $result ) && 'WP_Error' === get_class( $result ) ? 'error' : 'other' ) ) ),
		'attempted'       => 'u',
		'position'        => 'u',
		'credits'         => 'u',
		'native'          => 'u',
		'context'         => 'u',
		'close_attempted' => 'u',
		'closed'          => 'u',
		'ready'           => 'u',
		'dbh'             => 'u',
		'handle'          => 'u',
	);
	if ( ! adbm_bootstrap_observable( $owner, $context ) ) {
		return $row;
	}
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Isolated observation only; original handler and first failure remain intact.
	set_error_handler(
		static function () {
			throw new RuntimeException( 'Harness observation unavailable' );
		}
	);
	try {
		foreach ( array(
			'attempted'       => 'bootstrap_attempted',
			'position'        => 'bootstrap_position',
			'credits'         => 'bootstrap_credits',
			'native'          => 'native_state',
			'close_attempted' => 'close_attempted',
			'closed'          => 'closed',
			'ready'           => 'ready',
			'dbh'             => 'dbh',
			'handle'          => 'bootstrap_handle',
		) as $key => $field ) {
			try {
				$property = new ReflectionProperty( $owner, $field );
				if ( PHP_VERSION_ID < 80100 ) {
					$property->setAccessible( true );
				}
				$value = $property->getValue( $owner );
				if ( in_array( $key, array( 'position', 'credits' ), true ) ) {
					$row[ $key ] = is_int( $value ) && $value >= 0 && $value <= ( 'position' === $key ? 8 : 16 ) ? (string) $value : 'u';
				} elseif ( 'native' === $key ) {
					$row[ $key ] = in_array( $value, array( 'never_opened', 'allocating', 'allocated', 'connecting', 'connected_unqualified', 'active', 'closed', 'closure_unknown' ), true ) ? $value : 'u';
				} elseif ( in_array( $key, array( 'dbh', 'handle' ), true ) ) {
					$row[ $key ] = null === $value ? '0' : ( $value instanceof mysqli ? '1' : 'u' );
				} else {
					$row[ $key ] = true === $value ? '1' : ( false === $value ? '0' : 'u' );
				}
			} catch ( Throwable $error ) {
				$row[ $key ] = 'u';
			}
		}
		$property = new ReflectionProperty( $context, 'state' );
		if ( PHP_VERSION_ID < 80100 ) {
			$property->setAccessible( true );
		}
		$value          = $property->getValue( $context );
		$row['context'] = in_array( $value, array( 'fresh', 'opening', 'rejected', 'active', 'closing', 'closed', 'cleanup_failed' ), true ) ? $value : 'u';
	} catch ( Throwable $error ) {
		$row['context'] = 'u';
	} finally {
		restore_error_handler();
	}
	return $row;
}

/** Encodes bounded diagnostic DATA only, never an admission or success receipt.
 *
 * @param mixed $row Fixed scalar snapshot.
 * @return string Fixed ASCII line, or empty on malformed/absent DATA.
 */
function adbm_bootstrap_wire( $row ) {
	$keys = array( 'before', 'result', 'attempted', 'position', 'credits', 'native', 'context', 'close_attempted', 'closed', 'ready', 'dbh', 'handle' );
	if ( ! is_array( $row ) || array_keys( $row ) !== $keys || ! is_string( $row['before'] ) || ! preg_match( '/\A[01u]{4}\z/', $row['before'] )
		|| ! in_array( $row['result'], array( 'true', 'false', 'error', 'other', 'threw' ), true )
		|| ! is_string( $row['position'] ) || ! preg_match( '/\A(?:[0-8]|u)\z/', $row['position'] )
		|| ! is_string( $row['credits'] ) || ! preg_match( '/\A(?:[0-9]|1[0-6]|u)\z/', $row['credits'] )
		|| ! in_array( $row['native'], array( 'never_opened', 'allocating', 'allocated', 'connecting', 'connected_unqualified', 'active', 'closed', 'closure_unknown', 'u' ), true )
		|| ! in_array( $row['context'], array( 'fresh', 'opening', 'rejected', 'active', 'closing', 'closed', 'cleanup_failed', 'u' ), true ) ) {
		return '';
	}
	foreach ( array( 'attempted', 'close_attempted', 'closed', 'ready', 'dbh', 'handle' ) as $key ) {
		if ( ! in_array( $row[ $key ], array( '0', '1', 'u' ), true ) ) {
			return '';
		}
	}
	return 'ADBMBD1|' . implode( '|', $row ) . "\n";
}

/** Reads only bounded private catch metadata from the fixed source-owned owner.
 *
 * @param object $owner Existing owned receiver.
 * @param object $context Existing private context.
 * @return string Optional diagnostic line; never a success or admission receipt.
 */
function adbm_bootstrap_cause_wire( $owner, $context ) {
	if ( ! adbm_bootstrap_observable( $owner, $context ) ) {
		return '';
	}
	try {
		$field = new ReflectionProperty( $owner, 'bootstrap_failure' );
		if ( PHP_VERSION_ID < 80100 ) {
			$field->setAccessible( true );
		}
		$row = $field->getValue( $owner );
	} catch ( Throwable $error ) {
		return ''; // No messages, paths, unknown receiver methods or native reads.
	}
	if ( ! is_array( $row ) || array_keys( $row ) !== array( 'kind', 'source', 'line', 'state' )
		|| ! in_array( $row['kind'], array( 'RuntimeException', 'Error', 'TypeError', 'ArgumentCountError', 'ValueError', 'other' ), true )
		|| ! in_array( $row['source'], array( 'bootstrap', 'other' ), true )
		|| ! is_int( $row['line'] ) || $row['line'] < 0 || $row['line'] > 999999
		|| ! in_array( $row['state'], array( 'never_opened', 'allocating', 'allocated', 'connecting', 'connected_unqualified', 'active', 'closed', 'closure_unknown' ), true ) ) {
		return '';
	}
	return 'ADBMBC1|' . implode( '|', $row ) . "\n";
}
