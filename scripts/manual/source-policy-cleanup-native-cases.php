<?php
/**
 * Private genuine native observations; writes and production wiring remain absent.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Explicit approved owned proof, never a production provider.
if ( ! defined( 'ABSPATH' ) || ! function_exists( 'adbb_check' ) ) {
	exit( 1 );
}

/** Computes loaded source/ABI correlation, not distribution authentication.
 *
 * @return string
 */
function adbc_source() {
	$files   = get_included_files();
	$files[] = PHP_BINARY;
	$files[] = dirname( PHP_BINARY ) . '/php8.dll';
	$files[] = ini_get( 'extension_dir' ) . '/php_mysqli.dll';
	foreach ( array( 'source-policy-cleanup-cohort.php', 'source-policy-cleanup-bridge.php', 'source-policy-cleanup-controller.ps1', 'source-policy-cleanup-process.ps1' ) as $file ) {
		$files[] = __DIR__ . '/' . $file;
	}
	$files = array_unique( array_map( 'realpath', $files ) );
	sort( $files, SORT_STRING );
	$rows = array();
	foreach ( $files as $file ) {
		adbb_check( is_string( $file ) && is_file( $file ), 'finite source image file exists' );
		$rows[] = str_replace( '\\', '/', $file ) . ':' . hash_file( 'sha256', $file );
	}
	return hash( 'sha256', implode( "\n", $rows ) );
}

/** Captures canonical native string counters without casting/repair.
 *
 * @return string
 */
function adbc_stats() {
	$native = mysqli_get_client_stats();
	$values = array();
	foreach ( Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::counters() as $key ) {
		adbb_check( isset( $native[ $key ] ) && is_string( $native[ $key ] ) && 1 === preg_match( '/\A(?:0|[1-9][0-9]{0,19})\z/', $native[ $key ] ), 'exact native counter bytes: ' . $key );
		$values[] = $native[ $key ];
	}
	return implode( ',', $values );
}

/** Closed-object probe is supporting only, never an acknowledgement.
 *
 * @param mysqli $handle Exact already-released object.
 * @return bool
 */
function adbc_closed( mysqli $handle ) {
	try {
		mysqli_errno( $handle );
		return false;
	} catch ( Error $error ) {
		return true;
	}
}

/** Fresh raw reference, deliberately separate from owner qualification.
 *
 * @param array  $frame Fixed absent-target composition.
 * @param array  $request Fixed CLI bindings.
 * @param string $source Source correlation.
 * @return string
 */
function adbc_raw( array $frame, array $request, $source ) {
	$before = adbc_stats();
	adbb_check( implode( ',', array_fill( 0, 11, '0' ) ) === $before, 'fresh raw counter baseline' );
	$handle    = mysqli_init();
	$attempted = false;
	$expected  = false;
	$ack       = false;
	try {
		adbb_check( $handle instanceof mysqli, 'raw exact owned allocation' );
		foreach ( array(
			MYSQLI_OPT_CONNECT_TIMEOUT => 2,
			MYSQLI_OPT_READ_TIMEOUT    => 3,
			MYSQLI_OPT_LOCAL_INFILE    => 0,
		) as $option => $value ) {
			adbb_check( true === mysqli_options( $handle, $option, $value ), 'raw bounded option' );
		}
		try {
			mysqli_real_connect( $handle, $frame['host'], $frame['user'], $frame['password'], $request[2] . 'absent', $frame['port'], null, 0 );
		} catch ( mysqli_sql_exception $error ) {
			$expected = 'mysqli_sql_exception' === get_class( $error ) && 1049 === $error->getCode();
		}
	} finally {
		if ( $handle instanceof mysqli && ! $attempted ) {
			$attempted = true; // Captured before the primitive; no retry even if cleanup throws.
			$ack       = true === mysqli_close( $handle );
		}
	}
	adbb_check( $expected && $ack && adbc_closed( $handle ), 'raw exception1049 and independent acknowledged owned release' );
	$after = adbc_stats();
	$parts = explode( ',', $after );
	adbb_check( array( '0', '1', '0', '0', '0', '0', '0', '0', '0', '0' ) === array_slice( $parts, 0, 10 ), 'raw one failed connect and no SQL/reuse/reconnect/results' );
	// Final raw active counter remains a diagnostic observation, not an owner grant.
	return implode( '|', array( 'ADBR1', $request[3], $source, getmypid(), '1049', '1', '1', $before, $after ) ) . "\n";
}

/** One actual dormant owner case; never stamps active/ready/facts/handles.
 *
 * @param ReflectionClass $owner_class Fixed receiver.
 * @param array           $frame Frozen fixture settings.
 * @param array           $binding Audited source/context bindings.
 * @param array           $request Fixed CLI bindings.
 * @param string          $source Correlation.
 * @return string
 */
function adbc_owner( ReflectionClass $owner_class, array $frame, array $binding, array $request, $source ) {
	$started = hrtime( true );
	$name    = $request[5];
	$pre     = in_array( $name, array_slice( Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::cases(), 0, 4 ), true );
	$owner   = $owner_class->newInstanceWithoutConstructor();
	$owner_class->getConstructor()->invoke( $owner );
	$context = ( new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context' ) )->newInstanceWithoutConstructor();
	foreach ( array(
		'owned'     => $owner,
		'ambient'   => null,
		'core_file' => $binding['core_file'],
	) as $key => $value ) {
		adbb_bind( $context, $key, $value );
	}
	adbb_bind( $owner, 'query_context', $context );
	$called   = 0;
	$hook     = null;
	$observer = static function ( $value ) use ( &$called ) {
		++$called;
		return $value;
	};
	$settings = $frame;
	if ( 'default' === $name ) {
		$settings = null;
	} elseif ( 'wrong-source' === $name ) {
		$binding['core_file'] = __FILE__;
	} elseif ( in_array( $name, array( 'all', 'incompatible_sql_modes' ), true ) ) {
		$hook = $name;
		add_filter( $hook, $observer, 1 );
		$binding['observers'][ $hook ]         = $GLOBALS['wp_filter'][ $hook ]->callbacks;
		$binding['observer_files'][ __FILE__ ] = hash_file( 'sha256', __FILE__ );
	} elseif ( 'missing-database' === $name ) {
		$settings['database'] = $request[2] . 'absent';
	} elseif ( 'missing-table' === $name ) {
		$settings['table'] = $request[2] . 'missing_options';
	}
	adbb_bind( $owner, 'bootstrap_frame', $settings );
	adbb_bind( $owner, 'bootstrap_binding', $binding );
	$read = static function ( $key ) use ( $owner_class, $owner ) {
		return $owner_class->getProperty( $key )->getValue( $owner );
	};
	try {
		$before = adbc_stats();
		adbb_check( ! $owner->ready && $owner->admit_profile() instanceof WP_Error && false === $owner->query( 'SELECT never-dispatched' ), 'pre-active public guards' );
		$refused = false;
		try {
			$owner->execute( 'SELECT never-dispatched' );
		} catch ( RuntimeException $error ) {
			$refused = true;
		}
		adbb_check( $refused, 'pre-active execution refuses before SQL' );
		$result = $owner_class->getMethod( 'bootstrap' )->invoke( $owner );
		$boot   = adbc_stats();
		$active = true === $owner->ready;
		$round  = false;
		$seal   = false;
		adbb_check( 'positive' === $name ? true === $result && $active : $result instanceof WP_Error && ! $active, 'actual positive or refusal outcome' );
		if ( 'positive' === $name ) {
			$literal = "quote ' double \" slash \\ percent %s %d 100% nul\0 line\n return\r end\x1a";
			$sql     = $owner->prepare_checked( 'SELECT %s AS bytes', $literal );
			$ack     = $owner->execute( $sql );
			$round   = 1 === $ack['affected'] && 0 === $ack['errno'] && $literal === $owner->last_result[0]->bytes;
			$owner->flush();
			$hook = 'all';
			add_filter( $hook, $observer, 1 );
			$commands = mysqli_get_client_stats()['com_query'];
			try {
				$owner->execute( 'SELECT never-dispatched' );
			} catch ( RuntimeException $error ) {
				$seal = mysqli_get_client_stats()['com_query'] === $commands && 0 === $called;
			}
		}
		adbb_check( $owner_class->getMethod( 'bootstrap' )->invoke( $owner ) instanceof WP_Error, 'no bootstrap replay' );
		$close     = $owner->close();
		$after     = adbc_stats();
		$cached    = $owner->close() === $close && adbc_stats() === $after;
		$handle    = $read( 'bootstrap_handle' );
		$allocated = $handle instanceof mysqli;
		if ( null !== $hook ) {
			remove_filter( $hook, $observer, 1 );
			$hook = null; }
		$row      = array_combine(
			Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::fields(),
			array(
				'ADBC1',
				$request[3],
				$name,
				Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::PROFILE,
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
				$pre ? 'preflight' : $read( 'native_state' ),
				(string) $called,
				(string) $read( 'bootstrap_position' ),
				(string) $owner->num_queries,
				$round ? '1' : '0',
				$seal ? '1' : '0',
				(string) (int) ( ( hrtime( true ) - $started ) / 1000000 ),
			)
		);
		$accepted = Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches( $row, $request[3], $name, $source, getmypid() );
		if ( ! $accepted ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed diagnostic schema validates every scalar, numeric vector and enum; never success/SQL/messages.
			fwrite( STDERR, Alynt_Drime_Backups_Dashboard_Cleanup_Diagnostic::format( $row, $request[3], $name, $source, getmypid() ) );
		}
		adbb_check( $accepted, 'actual native terminal evidence contract' );
		return implode( '|', $row ) . "\n";
	} finally {
		$owner->close(); // Cached if already attempted; preserves cleanup on failed assertions.
		if ( null !== $hook ) {
			remove_filter( $hook, $observer, 1 ); }
	}
}
