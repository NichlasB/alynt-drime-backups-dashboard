<?php
/**
 * One fixed ephemeral Linux genuine-core/OFF proof, never application bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// Refuse before core/files/credentials/native IO outside the exact owned job.
if ( PHP_SAPI !== 'cli' || 3 !== count( $argv ) || '--owned-linux-qualification' !== $argv[1] || ! in_array( $argv[2], array( 'audit', 'proof' ), true )
	|| defined( 'ABSPATH' ) || 'Linux' !== PHP_OS_FAMILY || '8.5.12' !== PHP_VERSION || 'x86_64' !== php_uname( 'm' ) || PHP_ZTS || 8 !== PHP_INT_SIZE
	|| 'true' !== getenv( 'GITHUB_ACTIONS' ) || 'workflow_dispatch' !== getenv( 'GITHUB_EVENT_NAME' )
	|| 'NichlasB/alynt-drime-backups-dashboard' !== getenv( 'GITHUB_REPOSITORY' ) || 'refs/heads/master' !== getenv( 'GITHUB_REF' )
	|| ! preg_match( '/\A[a-f0-9]{40}\z/', (string) getenv( 'GITHUB_SHA' ) ) || getenv( 'GITHUB_SHA' ) !== getenv( 'EXPECTED_SHA' )
	|| ! preg_match( '/\A[0-9]+\z/', (string) getenv( 'GITHUB_RUN_ID' ) ) || ! preg_match( '/\A[0-9]+\z/', (string) getenv( 'GITHUB_RUN_ATTEMPT' ) )
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
	$GLOBALS['adbb_last_check'] = $label;
	if ( ! $condition ) {
		throw new RuntimeException( 'Owned qualification refused' );
	}
}
/** Bind private composition, never active state or a native handle.
 *
 * @param object $receiver Owned receiver.
 * @param string $name Fixed field name.
 * @param mixed  $value Source-owned composition.
 * @return void
 */
function adbb_bind( $receiver, $name, $value ) {
	( new ReflectionProperty( $receiver, $name ) )->setValue( $receiver, $value );
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
		throw new RuntimeException( 'Owned proof diagnostic' );
	}
);
try {
	adbb_check( 'mysqlnd 8.5.12' === mysqli_get_client_info() && '3' === ini_get( 'default_socket_timeout' ) && '3' === ini_get( 'mysqlnd.net_read_timeout' ), 'bounded exact runtime' );
	adbb_check( '' === ini_get( 'auto_prepend_file' ) && '' === ini_get( 'auto_append_file' ) && 1 === count( get_included_files() ), 'no injected entry' );
	$phase = 'core-pins';
	$core  = realpath( __DIR__ . '/../../.adb-linux-preflight-core' );
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
	$pins = json_decode( file_get_contents( __DIR__ . '/source-policy-linux-core-pins.json' ), true );
	adbb_check( is_array( $pins ) && 11 === count( $pins ), 'complete core pin set' );
	foreach ( $pins as $file => $hash ) {
		adbb_check( preg_match( '/\A[a-z-]+\.php\z/', $file ) && hash_equals( $hash, hash_file( 'sha256', ABSPATH . WPINC . '/' . $file ) ), 'pinned genuine core bytes' );
	}
	$phase = 'core-load';
	foreach ( array( 'load.php', 'plugin.php', 'class-wp-error.php', 'cache.php', 'class-wpdb.php', 'formatting.php', 'functions.php' ) as $file ) {
		require_once ABSPATH . WPINC . '/' . $file;
	}
	$phase = 'owned-load';
	foreach ( array( 'class-source-policy.php', 'class-source-policy-native-profile.php', 'class-source-policy-query-context.php', 'class-source-policy-no-reconnect-connection.php', 'class-source-policy-save-contract.php', 'class-source-policy-stored-map.php', 'class-source-policy-native-adapter.php' ) as $file ) {
		require_once dirname( __DIR__, 2 ) . '/includes/' . $file;
	}
	require __DIR__ . '/../manual/source-policy-adapter-proof-cases.php';
	require __DIR__ . '/../manual/source-policy-ambient-proof-contract.php';
	require __DIR__ . '/../manual/source-policy-ambient-proof-operation.php';
	require __DIR__ . '/../manual/source-policy-ambient-proof-cases.php';
	require __DIR__ . '/source-policy-linux-qualification-contract.php';
	$phase = 'cache';
	wp_using_ext_object_cache( false );
	wp_cache_init();
	$wpdb    = null;
	$run     = substr( hash( 'sha256', getenv( 'GITHUB_RUN_ID' ) . ':' . getenv( 'GITHUB_RUN_ATTEMPT' ) ), 0, 32 );
	$prefix  = 'adbb_' . substr( $run, 0, 12 ) . '_';
	$socket  = getenv( 'RUNNER_TEMP' ) . '/adb-linux-qualification-' . getenv( 'GITHUB_RUN_ID' ) . '-' . getenv( 'GITHUB_RUN_ATTEMPT' ) . '/socket/native.sock';
	$frame   = array(
		'core'      => '7.1.4',
		'database'  => 'adb_owned',
		'table'     => $prefix . 'options',
		'host'      => 'localhost',
		'port'      => null,
		'user'      => 'adbp',
		'password'  => 'inert-audit-only',
		'charset'   => DB_CHARSET,
		'collate'   => DB_COLLATE,
		'transport' => 'unix_socket',
		'socket'    => $socket,
		'db_host'   => 'localhost:' . $socket,
	);
	$binding = array(
		'core_file'       => ABSPATH . WPINC . '/class-wpdb.php',
		'driver_file'     => null,
		'cache_file'      => ABSPATH . WPINC . '/class-wp-object-cache.php',
		'cache_api_file'  => ABSPATH . WPINC . '/load.php',
		'parser_api_file' => ABSPATH . WPINC . '/functions.php',
		'observers'       => array(),
		'observer_files'  => array(),
	);
	$phase   = 'profile';
	$class   = new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' );
	$owner   = $class->newInstanceWithoutConstructor();
	$class->getConstructor()->invoke( $owner );
	adbb_check( Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::settings( $frame ) && Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::source( $owner, $binding ), 'genuine fixed source/endpoint admission' );
	adbb_check( null === $owner->dbh && false === $owner->ready && false === $owner->db_connect() && false === $owner->check_connection(), 'no public connection path' );
	adbb_check( '0' === mysqli_get_client_stats()['connect_success'] && '0' === mysqli_get_client_stats()['connect_failure'] && '0' === mysqli_get_client_stats()['com_query'] && 3 === ( new mysqli_driver() )->report_mode, 'audit zero native activity unchanged strict mode' );
	$phase = 'image';
	$files = array_merge( get_included_files(), array( PHP_BINARY, ini_get( 'extension_dir' ) . '/mysqlnd.so', ini_get( 'extension_dir' ) . '/mysqli.so', __DIR__ . '/source-policy-linux-core-pins.json', __DIR__ . '/source-policy-linux-qualification.sh', __DIR__ . '/source-policy-linux-qualification-controller.mjs', dirname( __DIR__, 2 ) . '/.github/workflows/source-policy-linux-qualification.yml' ) );
	sort( $files, SORT_STRING );
	foreach ( array_unique( $files ) as $file ) {
		if ( 0 === strpos( $file, ABSPATH ) ) {
			adbb_check( in_array( substr( $file, strlen( ABSPATH . WPINC . '/' ) ), array_keys( $pins ), true ), 'finite genuine libraries only' );
		}
		$rows[ $file ] = hash_file( 'sha256', $file );
	}
	$image = hash( 'sha256', json_encode( $rows ) );
	if ( 'audit' === $argv[2] ) {
		echo json_encode(
			array(
				'event'              => 'audit',
				'image'              => $image,
				'run'                => $run,
				'checks'             => $checks,
				'native_connections' => 0,
				'sql_queries'        => 0,
			)
		), "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed bounded DATA only.
		return;
	}
	$phase = 'native';
	adbb_check( getenv( 'QUALIFICATION_IMAGE' ) === $image && 1 === preg_match( '/\A[a-f0-9]{64}\z/', (string) getenv( 'QUALIFICATION_PASSWORD' ) ), 'frozen parent source and owned credential' );
	$frame['password'] = getenv( 'QUALIFICATION_PASSWORD' );
	$request           = array( __FILE__, '--owned-linux-qualification', $prefix, $run, $image, 'proof' );
	$stage             = 'ambient';
	$started           = hrtime( true );
	$ambient           = ( new ReflectionClass( 'wpdb' ) )->newInstanceWithoutConstructor();
	foreach ( array(
		'dbuser'     => 'adbp',
		'dbpassword' => $frame['password'],
		'dbname'     => 'adb_owned',
		'dbhost'     => $frame['db_host'],
	) as $key => $value ) {
		adbb_bind( $ambient, $key, $value );
	}
	$wpdb = $ambient;
	adbb_check( true === $ambient->db_connect( false ) && true === $ambient->ready && $ambient->dbh instanceof mysqli, 'genuine core creates ambient handle' );
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
	$stage        = 'escape';
	$escape_owner = adb0_owner();
	try {
		$needle = "owned'\\\0%\xf0\x9f\x8c\x8d";
		$sql    = $escape_owner->prepare_checked( 'SELECT %s AS escaped', $needle );
		adbb_check(
			array(
				'affected' => 1,
				'errno'    => 0,
			) === $escape_owner->execute( $sql ) && 1 === count( $escape_owner->last_result ) && $needle === $escape_owner->last_result[0]->escaped,
			'genuine owned quote/backslash/NUL/percent/utf8mb4 round trip'
		);
	} finally {
		adbb_check( true === $escape_owner->close(), 'escape owner release' );
		adb0_released( $escape_owner );
	}
	adb0_cases();
	adbb_check( 9 === $groups && hrtime( true ) - $started < 20000000000, 'original body deadline and complete groups' );
} catch ( Throwable $error ) {
	$failed    = true;
	$exception = get_class( $error );
	if ( ! in_array( $exception, array( 'RuntimeException', 'Error', 'TypeError', 'ValueError', 'ParseError', 'ArgumentCountError', 'ReflectionException' ), true ) ) {
		$exception = 'other';
	}
	fwrite(
		STDERR,
		json_encode(
			array(
				'event'      => 'failure',
				'stage'      => $stage,
				'phase'      => $phase,
				'checks'     => $checks,
				'groups'     => $groups,
				'assertion'  => hash( 'sha256', $GLOBALS['adbb_last_check'] ?? 'none' ),
				'exception'  => $exception,
				'diagnostic' => $GLOBALS['qualification_diagnostic'],
			)
		) . "\n"
	);
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
$terminal = array(
	'event'  => 'closed',
	'image'  => $image,
	'run'    => $run,
	'pid'    => getmypid(),
	'checks' => $checks,
	'groups' => $groups,
	'mode'   => ( new mysqli_driver() )->report_mode,
	'closed' => true,
);
adbb_check( Alynt_Drime_Backups_Dashboard_Linux_Qualification_Contract::terminal( $terminal, $image, $run, getmypid() ), 'fixed closed terminal' );
echo json_encode( $terminal ), "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Validated fixed terminal only.
fflush( STDOUT );
adbb_check( hash_equals( 'ACK:' . $run . ':' . $image . "\n", (string) fgets( STDIN, 160 ) ), 'parent release only after independent live reconciliation' );
