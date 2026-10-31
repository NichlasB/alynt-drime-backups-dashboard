<?php
/**
 * One fixed ephemeral Linux genuine-core/OFF proof, never application bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// Refuse before core/files/credentials/native IO outside the exact owned job.
if ( PHP_SAPI !== 'cli' || 3 !== count( $argv ) || '--owned-minimum-qualification' !== $argv[1] || ! in_array( $argv[2], array( 'audit', 'proof' ), true )
	|| defined( 'ABSPATH' ) || 'Linux' !== PHP_OS_FAMILY || '7.4.33' !== PHP_VERSION || 'x86_64' !== php_uname( 'm' ) || PHP_ZTS || 8 !== PHP_INT_SIZE
	|| 'true' !== getenv( 'GITHUB_ACTIONS' ) || 'workflow_dispatch' !== getenv( 'GITHUB_EVENT_NAME' )
	|| 'NichlasB/alynt-drime-backups-dashboard' !== getenv( 'GITHUB_REPOSITORY' ) || 'refs/heads/master' !== getenv( 'GITHUB_REF' )
	|| ! preg_match( '/\A[a-f0-9]{40}\z/', (string) getenv( 'GITHUB_SHA' ) ) || getenv( 'GITHUB_SHA' ) !== getenv( 'EXPECTED_SHA' )
	|| ! preg_match( '/\A[0-9]+\z/', (string) getenv( 'GITHUB_RUN_ID' ) ) || ! preg_match( '/\A[0-9]+\z/', (string) getenv( 'GITHUB_RUN_ATTEMPT' ) )
	|| 1 !== getmypid() || 'trusted-lab-functional-v1' !== getenv( 'MINIMUM_MODEL' )
	|| ! preg_match( '/\A[a-f0-9]{64}\z/', (string) getenv( 'MINIMUM_SERVER_CID' ) )
	|| ( 'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-minimum-audit.yml@refs/heads/master' === getenv( 'GITHUB_WORKFLOW_REF' ) && 'audit-only-v1' !== getenv( 'MINIMUM_SCOPE' ) )
	|| ( false !== getenv( 'MINIMUM_SCOPE' ) && ( 'audit-only-v1' !== getenv( 'MINIMUM_SCOPE' ) || 'audit' !== $argv[2] || false !== getenv( 'QUALIFICATION_PASSWORD' )
		|| 'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-minimum-audit.yml@refs/heads/master' !== getenv( 'GITHUB_WORKFLOW_REF' ) ) )
	|| ! extension_loaded( 'mysqli' ) || ! extension_loaded( 'mysqlnd' ) || extension_loaded( 'pdo_mysql' ) ) {
	fwrite( STDERR, "Requires exact owned Linux qualification job\n" );
	exit( 1 );
}
// phpcs:disable WordPress.DB.RestrictedFunctions, WordPress.WP.GlobalVariablesOverride.Prohibited -- Approved fresh private lab only; no installed WordPress or existing database.
$checks = 0;
/** Fixed non-secret assertion labels only.
 *
 * @param bool   $condition Required fact.
 * @param string $label Fixed source-owned label.
 * @return void
 * @throws RuntimeException On a failed assertion.
 */
function adbb_check( $condition, $label ) {
	global $checks;
	++$checks;
	if ( ! $condition ) {
		$error = new RuntimeException( 'Owned qualification refused' );
		adbb_capture( $error, $label );
		throw $error;
	}
}
/** Snapshots the first failure before any finally checks can replace it.
 *
 * @param Throwable $error Failure type only, never its message, trace or path.
 * @param string    $label Fixed failed assertion, or none for a thrown operation.
 * @return void
 */
function adbb_capture( $error, $label = 'none' ) {
	global $checks, $groups, $stage, $phase;
	if ( null !== ( $GLOBALS['adbb_first_failure'] ?? null ) ) {
		return;
	}
	$exception = get_class( $error );
	if ( ! in_array( $exception, array( 'RuntimeException', 'Error', 'TypeError', 'ValueError', 'ParseError', 'ArgumentCountError', 'ReflectionException' ), true ) ) {
		$exception = 'other';
	}
	$GLOBALS['adbb_first_failure'] = array(
		'event'      => 'failure',
		'stage'      => $stage,
		'phase'      => $phase,
		'checks'     => $checks,
		'groups'     => $groups,
		'assertion'  => hash( 'sha256', $label ),
		'exception'  => $exception,
		'diagnostic' => $GLOBALS['qualification_diagnostic'] ?? null,
		'escape'     => $GLOBALS['adbb_escape'] ?? null,
	);
}
/** Classifies existing acknowledgement DATA without retaining arbitrary payloads.
 *
 * @param mixed $ack Existing execute return, never another dispatch.
 * @return array Fixed diagnostic categories only.
 */
function adbb_ack( $ack ) {
	$shape = is_array( $ack ) && array_keys( $ack ) === array( 'affected', 'errno' );
	if ( ! $shape ) {
		return array(
			'shape'    => false,
			'affected' => 'other',
			'errno'    => 'other',
		);
	}
	$affected = 'other';
	foreach ( array(
		'one'   => 1,
		'zero'  => 0,
		'false' => false,
		'null'  => null,
	) as $name => $value ) {
		if ( $ack['affected'] === $value ) {
			$affected = $name;
			break;
		}
	}
	$errno = null === $ack['errno'] ? 'null' : ( is_int( $ack['errno'] ) && in_array( $ack['errno'], array( 0, 1062, 1064, 1142, 1146, 1205, 1213, 1366, 2006, 2013 ), true ) ? (string) $ack['errno'] : 'other' );
	return array(
		'shape'    => true,
		'affected' => $affected,
		'errno'    => $errno,
	);
}
$owners                              = array();
$ambient                             = null;
$groups                              = 0;
$stage                               = 'source-audit';
$phase                               = 'runtime';
$failed                              = false;
$rows                                = array();
$frame                               = array();
$GLOBALS['qualification_diagnostic'] = null;
$GLOBALS['adbb_first_failure']       = null;
$GLOBALS['adbb_escape']              = null;
$GLOBALS['adbb_escape_release']      = null;
// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Proof diagnostics fail closed; no suppress-and-continue or raw output.
set_error_handler(
	static function ( $severity, $message, $file, $line ) {
		unset( $message ); // Never retain a PHP diagnostic's raw text or path.
		$source                              = __FILE__ === $file ? 'worker' : ( defined( 'ABSPATH' ) && defined( 'WPINC' ) && 0 === strpos( $file, ABSPATH . WPINC . '/' ) ? 'core' : 'other' );
		$GLOBALS['qualification_diagnostic'] = array(
			'severity' => $severity,
			'source'   => $source,
			'line'     => $line,
		);
		$error                               = new RuntimeException( 'Owned proof diagnostic' );
		adbb_capture( $error );
		throw $error;
	}
);
try {
	adbb_check( 'mysqlnd 7.4.33' === mysqli_get_client_info() && '3' === ini_get( 'default_socket_timeout' ) && '3' === ini_get( 'mysqlnd.net_read_timeout' ), 'bounded exact runtime' );
	adbb_check( '' === ini_get( 'auto_prepend_file' ) && '' === ini_get( 'auto_append_file' ) && 1 === count( get_included_files() ), 'no injected entry' );
	require_once __DIR__ . '/../manual/source-policy-proof-reflection.php'; // Definitions only, frozen with the loaded image below.
	$phase = 'core-pins';
	$core  = realpath( __DIR__ . '/../../core' );
	adbb_check( false !== $core && dirname( $core ) === realpath( __DIR__ . '/../..' ), 'exact pinned core child' );
	define( 'ABSPATH', $core . '/' );
	define( 'WPINC', 'wp-includes' );
	define( 'WP_DEBUG', false );
	define( 'MULTISITE', false );
	define( 'SAVEQUERIES', false );
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	define( 'DB_CHARSET', 'utf8' );
	define( 'DB_COLLATE', '' );
	define( 'AUTH_SALT', 'owned-linux-qualification-non-secret' );
	$catalog = require __DIR__ . '/../manual/source-policy-supported-core-pins.php';
	$pins    = $catalog['6.0']['pins'];
	adbb_check( is_array( $pins ) && 11 === count( $pins ), 'complete core pin set' );
	foreach ( $pins as $file => $hash ) {
		adbb_check( preg_match( '/\A[a-z-]+\.php\z/', $file ) && hash_equals( $hash, hash_file( 'sha256', ABSPATH . WPINC . '/' . $file ) ), 'pinned genuine core bytes' );
	}
	$phase = 'core-load';
	foreach ( array( 'version.php', 'load.php', 'plugin.php', 'class-wp-error.php', 'cache.php', 'wp-db.php', 'formatting.php', 'functions.php' ) as $file ) {
		require_once ABSPATH . WPINC . '/' . $file;
	}
	$phase = 'owned-load';
	foreach ( array( 'class-source-policy.php', 'class-source-policy-native-profile.php', 'class-source-policy-query-context.php', 'class-source-policy-no-reconnect-connection.php', 'class-source-policy-save-contract.php', 'class-source-policy-stored-map.php', 'class-source-policy-native-adapter.php' ) as $file ) {
		require_once dirname( __DIR__, 2 ) . '/includes/' . $file;
	}
	require __DIR__ . '/source-policy-minimum-adapter-cases.php';
	require __DIR__ . '/source-policy-minimum-native-contract.php';
	require __DIR__ . '/source-policy-minimum-native-operation.php';
	require __DIR__ . '/source-policy-minimum-native-cases.php';
	require __DIR__ . '/source-policy-minimum-failure-evidence.php';
	require __DIR__ . '/source-policy-minimum-worker-source.php';
	$phase = 'cache';
	wp_using_ext_object_cache( false );
	wp_cache_init();
	$wpdb    = null;
	$run     = substr( hash( 'sha256', getenv( 'GITHUB_RUN_ID' ) . ':' . getenv( 'GITHUB_RUN_ATTEMPT' ) ), 0, 32 );
	$prefix  = 'adbm_' . substr( $run, 0, 12 ) . '_';
	$frame   = array(
		'core'      => '6.0',
		'database'  => 'adb_minimum',
		'table'     => $prefix . 'options',
		'host'      => '127.0.0.1',
		'port'      => 3306,
		'user'      => 'adbm',
		'password'  => 'inert-audit-only',
		'charset'   => DB_CHARSET,
		'collate'   => DB_COLLATE,
		'transport' => 'tcp_loopback',
		'socket'    => null,
		'db_host'   => '127.0.0.1:3306',
	);
	$binding = array(
		'core_file'       => ABSPATH . WPINC . '/wp-db.php',
		'driver_file'     => null,
		'cache_file'      => ABSPATH . WPINC . '/class-wp-object-cache.php',
		'cache_api_file'  => ABSPATH . WPINC . '/load.php',
		'parser_api_file' => ABSPATH . WPINC . '/functions.php',
		'observers'       => array(),
		'observer_files'  => array(),
	);
	$phase   = 'profile';
	$class   = new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' );
	$owner   = adbb_construct( $class );
	adbb_check( Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::settings( $frame ) && Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::source( $owner, $binding ), 'genuine fixed source/endpoint admission' );
	adbb_check( null === $owner->dbh && false === $owner->ready && false === $owner->db_connect() && false === $owner->check_connection(), 'no public connection path' );
	adbb_check( '0' === mysqli_get_client_stats()['connect_success'] && '0' === mysqli_get_client_stats()['connect_failure'] && '0' === mysqli_get_client_stats()['com_query'] && 0 === ( new mysqli_driver() )->report_mode, 'audit zero native activity unchanged initial OFF' );
	$phase = 'image';

	$rows  = adbm_source_rows();
	$image = hash_file( 'sha256', dirname( __DIR__, 2 ) . '/cohort.sha256' );
	adbb_check( hash_equals( $image, (string) getenv( 'MINIMUM_IMAGE' ) ), 'parent frozen source' );
	$modules = adbm_modules();
	adbb_check( 0 === ( new mysqli_driver() )->report_mode, 'initial report OFF' );
	$evidence = array(
		'model'         => 'trusted-lab-functional-v1',
		'platform'      => 'Linux',
		'arch'          => 'x86_64',
		'php'           => '7.4.33',
		'client'        => 'mysqlnd 7.4.33',
		'core_commit'   => 'cc101b64012b16d087780657a2b828ccd7794a63',
		'runtime_image' => 'sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4',
		'server_image'  => 'sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5',
		'initial_mode'  => 0,
		'head'          => getenv( 'GITHUB_SHA' ),
		'image'         => $image,
		'run'           => $run,
	);
	if ( 'audit' === $argv[2] ) {
		echo 'ADBMA1|' . $run . '|' . $image . '|' . $modules . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed bounded private ASCII transcript, not HTML.
		fflush( STDOUT );
		adbb_check( hash_equals( 'ACK:' . $run . ':' . $image . "\n", (string) fgets( STDIN, 160 ) ), 'held audit identity acknowledgement' );
		return;}
	$phase = 'native';
	adbb_check( getenv( 'MINIMUM_IMAGE' ) === $image && 1 === preg_match( '/\A[a-f0-9]{64}\z/', (string) getenv( 'QUALIFICATION_PASSWORD' ) ), 'frozen parent source and owned credential' );
	$frame['password'] = getenv( 'QUALIFICATION_PASSWORD' );
	$request           = array( __FILE__, '--owned-minimum-qualification', $prefix, $run, $image, 'proof' );
	$stage             = 'ambient';
	$started           = hrtime( true );
	$ambient           = ( new ReflectionClass( 'wpdb' ) )->newInstanceWithoutConstructor();
	$wpdb              = $ambient;
	// Retain the owned instance for finally cleanup even if genuine initialization throws.
	$ambient->__construct( $frame['user'], $frame['password'], $frame['database'], $frame['db_host'] );
	adbb_check( true === $ambient->ready && $ambient->dbh instanceof mysqli, 'genuine core creates ambient handle' );
	adbb_check( 0 === ( new mysqli_driver() )->report_mode && '1' === mysqli_get_client_stats()['connect_success'], 'genuine core selected OFF' );
	$ambient->options = $prefix . 'options';
	$peer             = $ambient->dbh;
	$ambient_handle   = $peer;
	$ambient_id       = mysqli_thread_id( $peer );
	adbb_check( false === mysqli_query( $peer, 'SELECT User FROM mysql.global_priv LIMIT 1' ) && 1142 === mysqli_errno( $peer ), 'no privilege outside owned fixture' );
	adba_peer( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1' );
	$before = mysqli_get_client_stats()['com_query'];
	adbb_check( is_string( $ambient->prepare( 'SELECT %d', 1 ) ) && mysqli_get_client_stats()['com_query'] === $before, 'genuine prepare with zero SQL' );
	$ambient_query = $GLOBALS['wp_filter']['query']->callbacks;
	adbb_check( 0 === has_filter( 'query', array( $ambient, 'remove_placeholder_escape' ) ) && 1 === count( $ambient_query[0] ), 'owned ambient callback' );
	$stage                  = 'escape';
	$escape_owner           = adb0_owner();
	$GLOBALS['adbb_escape'] = array(
		'step'        => 'prepare',
		'prepared'    => null,
		'ack_exact'   => null,
		'row_exact'   => null,
		'bytes_equal' => null,
		'ack'         => null,
		'query'       => null,
	);
	try {
		$needle                             = "owned'\\\0%\xf0\x9f\x8c\x8d";
		$sql                                = $escape_owner->prepare_checked( 'SELECT %s AS `escaped`', $needle );
		$GLOBALS['adbb_escape']['prepared'] = is_string( $sql );
		$GLOBALS['adbb_escape']['step']     = 'execute';
		$GLOBALS['adbb_escape']['query']    = array(
			'ascii'       => 0 === preg_match( '/[^\x00-\x7F]/', $sql ),
			'mb_strlen'   => function_exists( 'mb_strlen' ),
			'is_wp_error' => function_exists( 'is_wp_error' ),
			'l10n'        => function_exists( '__' ) && function_exists( 'wp_load_translations_early' ),
			'commands'    => 'unobserved',
			'ready'       => null,
		);
		$queries_before                     = $escape_owner->num_queries;
		$ack                                = $escape_owner->execute( $sql );
		$GLOBALS['adbb_escape']['ack']      = adbb_ack( $ack );
		$queries_after                      = $escape_owner->num_queries;
		$delta                              = is_int( $queries_before ) && is_int( $queries_after ) && $queries_after >= $queries_before ? $queries_after - $queries_before : null;
		$GLOBALS['adbb_escape']['query']['commands'] = null === $delta ? 'unknown' : ( array( 'zero', 'one', 'two' )[ $delta ] ?? 'many' );
		$GLOBALS['adbb_escape']['query']['ready']    = true === $escape_owner->ready;
		$GLOBALS['adbb_escape']['ack_exact']         = array(
			'affected' => 1,
			'errno'    => 0,
		) === $ack;
		if ( $GLOBALS['adbb_escape']['ack_exact'] ) {
			$GLOBALS['adbb_escape']['step']      = 'rows';
			$GLOBALS['adbb_escape']['row_exact'] = 1 === count( $escape_owner->last_result );
			if ( $GLOBALS['adbb_escape']['row_exact'] ) {
				$GLOBALS['adbb_escape']['step']        = 'bytes';
				$GLOBALS['adbb_escape']['bytes_equal'] = $needle === $escape_owner->last_result[0]->escaped;
			}
		}
		adbb_check(
			true === $GLOBALS['adbb_escape']['ack_exact'] && true === $GLOBALS['adbb_escape']['row_exact'] && true === $GLOBALS['adbb_escape']['bytes_equal'],
			'genuine owned quote/backslash/NUL/percent/utf8mb4 round trip'
		);
	} catch ( Throwable $error ) {
		adbb_capture( $error );
		throw $error;
	} finally {
		$GLOBALS['adbb_escape']['step'] = 'release';
		$GLOBALS['adbb_escape_release'] = false;
		adbb_check( true === $escape_owner->close(), 'escape owner release' );
		adb0_released( $escape_owner );
		$GLOBALS['adbb_escape_release'] = true;
	}
	adb0_cases();
	adbb_check( 9 === $groups && hrtime( true ) - $started < 20000000000, 'original body deadline and complete groups' );
} catch ( Throwable $error ) {
	$failed = true;
	adbb_capture( $error );
	$first = $GLOBALS['adbb_first_failure'];
	$wire  = function_exists( 'adbm_failure_wire' ) ? adbm_failure_wire( $first, $GLOBALS['adbm_inventory'] ?? null ) : '';
	// Early failures retain the original finite envelope; never substitute raw diagnostics.
	fwrite( STDERR, '' !== $wire ? $wire : 'ADBMF1|' . $first['stage'] . '|' . $first['phase'] . '|' . $first['checks'] . '|' . $first['groups'] . "\n" );
} finally {
	foreach ( $owners as $owned ) {
		try {
			$failed = true !== $owned->close() || $failed;
			$owned->flush();
		} catch ( Throwable $cleanup_error ) {
			$failed = true;
		}
	}
	if ( $ambient instanceof wpdb ) {
		try {
			$failed = ( null !== $ambient->dbh && true !== $ambient->close() ) || $failed;
			remove_filter( 'query', array( $ambient, 'remove_placeholder_escape' ), 0 );
			$ambient->flush();
			adbb_bind( $ambient, 'dbpassword', '' );
		} catch ( Throwable $cleanup_error ) {
			$failed = true;
		}
	}
	$frame['password'] = '';
	$wpdb              = null;
	restore_error_handler();
}
if ( $failed ) {
	exit( 1 );
}
foreach ( $rows as $file => $hash ) {
	adbb_check( hash_equals( $hash, hash_file( 'sha256', $file ) ), 'image unchanged through cleanup' );
}

adbb_check( adbm_modules() === $modules, 'module inventory unchanged' );
adbb_check( adbm_source_rows() === $rows, 'no late loaded source' );
echo 'ADBMC1|' . $run . '|' . $image . '|' . getenv( 'MINIMUM_SERVER_CID' ) . '|1|' . $modules . '|' . $checks . '|9|0|closed|trusted-lab-functional-v1|false|not-established|false|false' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed bounded private ASCII transcript, not HTML.
fflush( STDOUT );
adbb_check( hash_equals( 'ACK:' . $run . ':' . $image . "\n", (string) fgets( STDIN, 160 ) ), 'independent live release acknowledgement' );
