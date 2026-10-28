<?php
/**
 * Actual private bootstrap cases; only an externally owned empty schema clone.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Private mysqlnd observations only; no candidate construction bypass, SQL write or application bootstrap.
if ( PHP_SAPI !== 'cli' || ! function_exists( 'adbb_check' ) || ! isset( $frame, $binding, $class ) ) {
	exit( 1 );
}

/**
 * Runs finite cases; no ready/active/facts/handle injection or subclass.
 *
 * @param ReflectionClass $owner_class Exact fixed receiver class.
 * @param array           $frame Frozen owned-fixture settings.
 * @param array           $binding Audited executable metadata.
 * @return array Support-safe outcome DATA.
 */
function adbb_native_cases( ReflectionClass $owner_class, array $frame, array $binding ) {
	global $group;
	$report  = array();
	$started = hrtime( true );
	foreach ( array( 'default', 'wrong-source', 'all', 'incompatible_sql_modes', 'missing-database', 'missing-table', 'positive' ) as $group ) {
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
		$settings = $frame;
		$sources  = $binding;
		$called   = 0;
		$hook     = null;
		$observer = static function ( $value ) use ( &$called ) {
			++$called;
			return $value;
		};
		if ( 'default' === $group ) {
			$settings = null;
		} elseif ( 'wrong-source' === $group ) {
			$sources['core_file'] = __FILE__;
		} elseif ( in_array( $group, array( 'all', 'incompatible_sql_modes' ), true ) ) {
			$hook = $group;
			add_filter( $hook, $observer, 1 );
			$sources['observers'][ $hook ]         = $GLOBALS['wp_filter'][ $hook ]->callbacks;
			$sources['observer_files'][ __FILE__ ] = hash_file( 'sha256', __FILE__ );
		} elseif ( 'missing-database' === $group ) {
			$settings['database'] = str_replace( 'options', 'absent', $frame['table'] );
		} elseif ( 'missing-table' === $group ) {
			$settings['table'] = str_replace( 'options', 'missing_options', $frame['table'] );
		}
		adbb_bind( $owner, 'bootstrap_frame', $settings );
		adbb_bind( $owner, 'bootstrap_binding', $sources );
		$before = mysqli_get_client_stats();
		try {
			adbb_check( ! $owner->ready && $owner->admit_profile() instanceof WP_Error && false === $owner->query( 'SELECT never-dispatched' ), 'pre-active public dispatch refuses' );
			$refused = false;
			try {
				$owner->execute( 'SELECT never-dispatched' );
			} catch ( RuntimeException $error ) {
				$refused = true; }
			adbb_check( $refused, 'pre-active execute refuses' );
			$result = $owner_class->getMethod( 'bootstrap' )->invoke( $owner );
			$after  = mysqli_get_client_stats();
			$delta  = array();
			foreach ( array( 'connect_success', 'connect_failure', 'reconnect', 'connection_reused', 'pconnect_success', 'com_query', 'result_set_queries', 'explicit_free_result', 'implicit_free_result', 'unbuffered_sets' ) as $key ) {
				$delta[ $key ] = (int) $after[ $key ] - (int) $before[ $key ];
			}
			$positive = 'positive' === $group;
			adbb_check( $positive ? true === $result : $result instanceof WP_Error, 'actual private initializer outcome' );
			adbb_check( $positive === $owner->ready && 0 === $called, 'active-last and no forbidden observer invocation' );
			adbb_check( 0 === $delta['reconnect'] && 0 === $delta['connection_reused'] && 0 === $delta['pconnect_success'], 'no reconnect, reuse or persistent connection' );
			adbb_check( $delta['com_query'] <= 16, 'physical mysqlnd bootstrap command envelope' );
			adbb_check( null === $owner_class->getProperty( 'bootstrap_frame' )->getValue( $owner ) && null === $owner_class->getProperty( 'bootstrap_binding' )->getValue( $owner ) && '' === $owner->dbpassword, 'terminal settings and password fields cleared' );
			if ( in_array( $group, array( 'default', 'wrong-source', 'all', 'incompatible_sql_modes' ), true ) ) {
				adbb_check( 0 === $delta['connect_success'] && 0 === $delta['connect_failure'] && 0 === $delta['com_query'], 'preflight refuses before physical connect or SQL' );
			} elseif ( 'missing-database' === $group ) {
				adbb_check( 0 === $delta['connect_success'] && 1 === $delta['connect_failure'] && 0 === $delta['com_query'], 'one actual failed initial database selection, no SQL/retry' );
			} else {
				adbb_check( 1 === $delta['connect_success'] && 0 === $delta['connect_failure'] && $delta['com_query'] >= 7, 'one real initial connection and complete metadata steps' );
				adbb_check( 8 === $owner_class->getProperty( 'bootstrap_position' )->getValue( $owner ), 'all ordered private steps consumed' );
				adbb_check( 6 === $delta['result_set_queries'] && 6 === $delta['explicit_free_result'] && 0 === $delta['implicit_free_result'] && 0 === $delta['unbuffered_sets'], 'six bounded metadata results explicitly released' );
			}
			if ( $positive ) {
				adbb_check( is_array( $owner->admit_profile() ) && 0 === has_filter( 'query', array( $owner, 'remove_placeholder_escape' ) ), 'genuine active eligibility and owned callback' );
				$literal = "quote ' double \" slash \\ percent %s %d 100% nul\0 line\n return\r end\x1a";
				$sql     = $owner->prepare_checked( 'SELECT %s AS bytes', $literal );
				$ack     = $owner->execute( $sql );
				adbb_check( 1 === $ack['affected'] && 0 === $ack['errno'] && $literal === $owner->last_result[0]->bytes, 'genuine core string escaping and marker pipeline round trip' );
				$owner->flush(); // Free the proof's own post-bootstrap buffered result.
				$hook = 'all';
				add_filter( $hook, $observer, 1 );
				$refused     = false;
				$read_before = mysqli_get_client_stats()['com_query'];
				try {
					$owner->execute( 'SELECT never-dispatched' );
				} catch ( RuntimeException $error ) {
					$refused = true; }
				adbb_check( $refused && mysqli_get_client_stats()['com_query'] === $read_before && 0 === $called, 'changed seal refuses without SQL or observer invocation' );
			}
			adbb_check( $owner_class->getMethod( 'bootstrap' )->invoke( $owner ) instanceof WP_Error, 'single-use bootstrap cannot reopen' );
			$closed = $owner->close();
			adbb_check( $closed === $owner->close() && false === has_filter( 'query', array( $owner, 'remove_placeholder_escape' ) ), 'once-only independent callback cleanup' );
			adbb_check( '0' === mysqli_get_client_stats()['active_connections'], 'no retained native session' );
			$preflight = in_array( $group, array( 'default', 'wrong-source', 'all', 'incompatible_sql_modes' ), true );
			adbb_check( $preflight ? ! $closed : $closed, 'unestablished callback context stays unconfirmed; native release must acknowledge' );
			$report[ $group ] = array(
				'driver_commands' => $delta['com_query'],
				'connected'       => $delta['connect_success'],
				'failed_connects' => $delta['connect_failure'],
				'closed'          => $closed,
			);
		} finally {
			$owner->close();
			if ( null !== $hook ) {
				remove_filter( $hook, $observer, 1 ); }
		}
		adbb_check( ! has_filter( 'query' ) && 0 === $called && ! has_filter( 'all' ) && ! has_filter( 'incompatible_sql_modes' ), 'only owned private registrations removed' );
	}
	adbb_check( ( hrtime( true ) - $started ) < 25000000000, 'observed whole-proof envelope, not a hard interrupt deadline' );
	return $report;
}
