<?php
/**
 * Confirmed Plugin Tester genuine-core context/cleanup proof; no native handles.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited -- Private library process only, never a WordPress application bootstrap.
if ( PHP_SAPI !== 'cli' || 2 !== count( $argv ) || '--confirmed-plugin-tester-zero-sql-context-proof' !== $argv[1] || defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Requires confirmed Plugin Tester zero-SQL context proof scope\n" );
	exit( 1 );
}
if ( extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' ) || function_exists( 'mysql_connect' ) ) {
	fwrite( STDERR, "Requires PHP -n without database drivers\n" );
	exit( 1 );
}
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_DEBUG', false );
define( 'AUTH_SALT', 'owned-non-secret-zero-sql-context-proof' );
$pins   = array(
	'load.php'                     => '3EFD0934BD8A454A8D7076FBBD21504EA097A753125A48E64B8A1DAD380D357A',
	'plugin.php'                   => 'D70B9E18D34AB3FE46E15548A5E8E089EA5920832F06A4D89A9D64B2F7938B0D',
	'class-wp-hook.php'            => 'B839C0E5672246BCA8DB1AB781EC8835F7732F253C375A237CBF6EC536E8D12E',
	'class-wp-filter-sentinel.php' => '68C9C675D3805A343835728D98A0EFEDF0DF9833CB904BEBF909C7E0FDA5C61C',
	'class-wp-error.php'           => 'A9E971548BF6A1D93C821F980F530EFBA985C25D5D0B13D9B63E63B378A81A94',
	'class-wpdb.php'               => 'E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD',
);
$checks = 0;
$group  = 'preflight';

/**
 * Fixed, non-secret diagnostic; never prints SQL, marker tokens or private paths.
 *
 * @param bool   $condition Required fact.
 * @param string $label Fixed assertion label.
 * @return void
 * @throws RuntimeException On assertion failure.
 */
function adbpc_check( $condition, $label ) {
	global $checks;
	++$checks;
	if ( ! $condition ) {
		throw new RuntimeException( $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed CLI assertion labels only.
	}
}

/**
 * Private manual-proof provisioning, not a production provider or session grant.
 *
 * @param object      $owned Exact handle-free connection.
 * @param object|null $ambient Exact handle-free ambient receiver.
 * @return object Private context.
 */
function adbpc_context( $owned, $ambient ) {
	$class   = new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context' );
	$context = $class->newInstanceWithoutConstructor();
	foreach ( array(
		'owned'     => $owned,
		'ambient'   => $ambient,
		'core_file' => ABSPATH . WPINC . '/class-wpdb.php',
	) as $key => $value ) {
		$property = $class->getProperty( $key );
		if ( PHP_VERSION_ID < 80100 ) {
			$property->setAccessible( true );
		}
		$property->setValue( $context, $value );
	}
	$property = new ReflectionProperty( $owned, 'query_context' );
	if ( PHP_VERSION_ID < 80100 ) {
		$property->setAccessible( true );
	}
	$property->setValue( $owned, $context );
	return $context;
}

/**
 * Verifies never-opened receiver state; does not attempt connection recovery.
 *
 * @param object $receiver Genuine handle-free wpdb or fixed owned receiver.
 * @return void
 */
function adbpc_handle_free( $receiver ) {
	adbpc_check( null === $receiver->dbh && false === $receiver->ready && 0 === $receiver->num_queries, 'receiver remains never-opened with zero SQL' );
}

try {
	foreach ( $pins as $file => $hash ) {
		adbpc_check( is_file( ABSPATH . WPINC . '/' . $file ) && strtoupper( hash_file( 'sha256', ABSPATH . WPINC . '/' . $file ) ) === $hash, 'pinned core dependency identity' );
	}
	foreach ( array( 'load.php', 'plugin.php', 'class-wp-error.php', 'class-wpdb.php' ) as $file ) {
		require_once ABSPATH . WPINC . '/' . $file;
	}
	foreach ( array( 'class-source-policy.php', 'class-source-policy-native-profile.php', 'class-source-policy-query-context.php', 'class-source-policy-no-reconnect-connection.php' ) as $file ) {
		require_once dirname( __DIR__, 2 ) . '/includes/' . $file;
	}
	$owned_class = new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' );
	$stock_class = new ReflectionClass( 'wpdb' );
	adbpc_check( ! has_filter( 'query' ) && ! isset( $GLOBALS['wpdb'] ), 'fresh process has no query pipeline or ambient owner' );
	adbpc_check( is_wp_error( Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context::create() ) && is_wp_error( Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection::create() ), 'production factories remain unavailable' );
	$groups = array( 'no-ambient', 'ambient-without-callback', 'ambient-with-callback', 'sequential', 'concurrent', 'foreign', 'third-core', 'priority', 'arguments', 'global-swap', 'source-binding', 'moved-owned' );
	foreach ( $groups as $group ) {
		$ambient = 'no-ambient' === $group ? null : $stock_class->newInstanceWithoutConstructor();
		$wpdb    = $ambient;
		$owned   = $owned_class->newInstanceWithoutConstructor();
		$context = adbpc_context( $owned, $ambient );
		$extra   = null;
		$second  = null;
		$foreign = null;
		$called  = 0;
		$present = in_array( $group, array( 'ambient-with-callback', 'sequential', 'concurrent' ), true );
		try {
			adbpc_handle_free( $owned );
			if ( $present ) {
				$ambient->placeholder_escape();
				adbpc_check( 0 === has_filter( 'query', array( $ambient, 'remove_placeholder_escape' ) ), 'ambient callback genuinely registered at priority zero' );
			}
			if ( 'source-binding' === $group ) {
				$property = new ReflectionProperty( $context, 'core_file' );
				if ( PHP_VERSION_ID < 80100 ) {
					$property->setAccessible( true );
				}
				$property->setValue( $context, __FILE__ );
				adbpc_check( ! $context->begin() && false === has_filter( 'query', array( $owned, 'remove_placeholder_escape' ) ), 'wrong genuine source binding rejects before registration' );
				continue;
			}
			adbpc_check( $context->begin() && $context->valid(), 'actual dormant context admits genuine core metadata' );
			adbpc_check( 0 === has_filter( 'query', array( $owned, 'remove_placeholder_escape' ) ), 'owned callback genuinely registered at priority zero' );
			$numeric = $owned->prepare( 'SELECT %d, %%', 7 );
			adbpc_check( is_string( $numeric ) && 'SELECT 7, %' !== $numeric && 'SELECT 7, %' === apply_filters( 'query', $numeric ), 'genuine numeric prepare and complete percent restoration' );
			$literal = "literal %s %d 100% ' quote \\ slash";
			$escaped = $owned->add_placeholder_escape( $literal );
			adbpc_check( $escaped !== $literal && apply_filters( 'query', $escaped ) === $literal && $context->valid(), 'marker-only literal bytes restored without claiming native string escaping' );
			adbpc_check( is_wp_error( $owned->admit_profile() ) && false === $owned->query( 'SELECT 7' ) && false === $owned->db_connect() && false === $owned->check_connection(), 'no profile grant or dispatch/connection opening' );
			$refused = false;
			try {
				$owned->prepare_checked( 'SELECT %d', 7 );
			} catch ( RuntimeException $error ) {
				$refused = true;
			}
			adbpc_check( $refused, 'checked preparation remains closed without native qualification' );
			if ( 'concurrent' === $group ) {
				$second = $owned_class->newInstanceWithoutConstructor();
				$other  = adbpc_context( $second, $ambient );
				adbpc_check( ! $other->begin() && $second->close() && $context->valid(), 'second owner refuses without cleaning first or ambient registration' );
			}
			if ( in_array( $group, array( 'foreign', 'priority', 'arguments' ), true ) ) {
				$foreign = static function ( $sql ) use ( &$called ) {
					++$called;
					return $sql;
				};
				add_filter( 'query', $foreign, 9 );
				if ( 'foreign' !== $group ) {
					remove_filter( 'query', $foreign, 9 );
					remove_filter( 'query', array( $owned, 'remove_placeholder_escape' ), 0 );
					add_filter( 'query', array( $owned, 'remove_placeholder_escape' ), 'priority' === $group ? 1 : 0, 'arguments' === $group ? 2 : 1 );
				}
			}
			if ( 'third-core' === $group ) {
				$extra = $stock_class->newInstanceWithoutConstructor();
				$extra->placeholder_escape();
			}
			if ( 'global-swap' === $group ) {
				$extra = $stock_class->newInstanceWithoutConstructor();
				$wpdb  = $extra;
			}
			if ( 'moved-owned' === $group ) {
				remove_filter( 'query', array( $owned, 'remove_placeholder_escape' ), 0 );
				add_filter( 'query', array( $owned, 'remove_placeholder_escape' ), 1 );
			}
			$changed = in_array( $group, array( 'foreign', 'third-core', 'priority', 'arguments', 'global-swap', 'moved-owned' ), true );
			adbpc_check( $changed ? ! $context->valid() : $context->valid(), 'sealed metadata and identity never refreshed' );
			adbpc_check( 0 === $called, 'unknown callback never invoked' );
			$clean = ! in_array( $group, array( 'foreign', 'third-core', 'priority', 'arguments', 'moved-owned' ), true );
			adbpc_check( $clean === $owned->close() && $clean === $owned->close(), 'once-only cleanup truthfully confirms complete state' );
			adbpc_check( ! $context->valid() && ! $context->begin(), 'terminal context cannot reopen' );
			adbpc_check( ( $present ? 0 : false ) === ( null === $ambient ? false : has_filter( 'query', array( $ambient, 'remove_placeholder_escape' ) ) ), 'ambient registration preserved exactly' );
			$remaining = in_array( $group, array( 'priority', 'moved-owned' ), true ) ? 1 : ( 'arguments' === $group ? 0 : false );
			adbpc_check( has_filter( 'query', array( $owned, 'remove_placeholder_escape' ) ) === $remaining, 'owned entry removed only with exact leased metadata' );
			if ( 'sequential' === $group ) {
				for ( $index = 0; $index < 4; ++$index ) {
					$second = $owned_class->newInstanceWithoutConstructor();
					$next   = adbpc_context( $second, $ambient );
					adbpc_check( $next->begin() && $next->valid() && $second->close() && false === has_filter( 'query', array( $second, 'remove_placeholder_escape' ) ), 'sequential owner releases exact registration' );
					adbpc_handle_free( $second );
				}
			}
		} finally {
			$owned->close(); // Single-attempt owner cleanup even after an assertion fails.
			foreach ( array( $owned, $ambient, $extra, $second ) as $receiver ) {
				if ( null !== $receiver ) {
					adbpc_handle_free( $receiver );
					// Exact proof-owned entries only; not a runtime cleanup repair or registry restore.
					remove_filter( 'query', array( $receiver, 'remove_placeholder_escape' ), 0 );
					remove_filter( 'query', array( $receiver, 'remove_placeholder_escape' ), 1 );
				}
			}
			if ( null !== $foreign ) {
				remove_filter( 'query', $foreign, 9 );
			}
			adbpc_check( ! has_filter( 'query' ) && 0 === $called, 'private fixture registrations removed without invocation' );
			$wpdb = null;
		}
	}
	$expected = array_map(
		static function ( $file ) {
			return realpath( ABSPATH . WPINC . '/' . $file );
		},
		array_keys( $pins )
	);
	foreach ( get_included_files() as $included ) {
		adbpc_check( 0 !== strpos( $included, realpath( ABSPATH ) . DIRECTORY_SEPARATOR ) || in_array( $included, $expected, true ), 'no application/bootstrap or unpinned core dependency loaded' );
	}
	echo json_encode(
		array(
			'checks'             => $checks,
			'groups'             => count( $groups ),
			'core_profile'       => 'installed-pinned-7.1.3',
			'native_connections' => 0,
			'sql_queries'        => 0,
			'retained_callbacks' => 0,
			'production_wiring'  => 'disabled',
		)
	) . "\n";
} catch ( Throwable $error ) {
	fwrite( STDERR, 'Core context proof failed in ' . $group . ' after ' . $checks . " checks\n" );
	exit( 1 );
}
