<?php
/**
 * Confirmed Plugin Tester bootstrap qualification; never an application entry.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.DB.RestrictedFunctions -- Explicit private native proof only; no application bootstrap or imported candidate handle.
if ( PHP_SAPI !== 'cli' || 4 !== count( $argv ) || '--confirmed-plugin-tester-owned-bootstrap-proof' !== $argv[1]
	|| ! preg_match( '/\Aadbb_[a-f0-9]{12}_\z/', $argv[2] ) || ! in_array( $argv[3], array( 'audit', 'proof' ), true ) || defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Requires confirmed Plugin Tester owned bootstrap proof scope\n" );
	exit( 1 );
}
if ( 'Windows' !== PHP_OS_FAMILY || '8.5.12' !== PHP_VERSION || ! extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' )
	|| ( 'proof' === $argv[3] && ! getenv( 'MYSQL_PWD' ) ) ) {
	fwrite( STDERR, "Requires the audited bundled PHP8.5.12 mysqli-only profile\n" );
	exit( 1 );
}
define( 'ABSPATH', 'C:/Users/Captain/Local Sites/plugin-tester/app/public/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_DEBUG', false );
define( 'MULTISITE', false );
define( 'SAVEQUERIES', false );
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'DB_CHARSET', 'utf8mb4' ); // Private owned-fixture profile, not wp-config compatibility.
define( 'DB_COLLATE', '' );
define( 'AUTH_SALT', 'owned-non-secret-native-bootstrap-proof' );
$pins   = array(
	'load.php'                     => '3EFD0934BD8A454A8D7076FBBD21504EA097A753125A48E64B8A1DAD380D357A',
	'plugin.php'                   => 'D70B9E18D34AB3FE46E15548A5E8E089EA5920832F06A4D89A9D64B2F7938B0D',
	'class-wp-hook.php'            => 'B839C0E5672246BCA8DB1AB781EC8835F7732F253C375A237CBF6EC536E8D12E',
	'class-wp-filter-sentinel.php' => '68C9C675D3805A343835728D98A0EFEDF0DF9833CB904BEBF909C7E0FDA5C61C',
	'class-wp-error.php'           => 'A9E971548BF6A1D93C821F980F530EFBA985C25D5D0B13D9B63E63B378A81A94',
	'class-wpdb.php'               => 'E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD',
	'cache.php'                    => 'A219C2B57CC9990B560C6B497C877DB443B7E29239645A6A79B2D76839C46E19',
	'class-wp-object-cache.php'    => '1857B774BD9CFBAFBD50A1B1424F7DF637D202CF3F2253CDA9798D4AD56F3CF0',
);
$checks = 0;
$group  = 'source-audit';

/**
 * Checks a fixed non-secret fact; no native diagnostic or credential is printed.
 *
 * @param bool   $condition Required fact.
 * @param string $label Fixed assertion label.
 * @return void
 * @throws RuntimeException On a failed assertion.
 */
function adbb_check( $condition, $label ) {
	global $checks;
	++$checks;
	$GLOBALS['adbb_last_check'] = $label;
	if ( ! $condition ) {
		throw new RuntimeException( $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed CLI label only.
	}
}

/**
 * Private proof composition only, never an active state/handle injection.
 *
 * @param object $receiver Exact owner/context.
 * @param string $name Private composition field.
 * @param mixed  $value Fixed proof binding.
 * @return void
 */
function adbb_bind( $receiver, $name, $value ) {
	( new ReflectionProperty( $receiver, $name ) )->setValue( $receiver, $value );
}

try {
	foreach ( $pins as $file => $hash ) {
		adbb_check( is_file( ABSPATH . WPINC . '/' . $file ) && strtoupper( hash_file( 'sha256', ABSPATH . WPINC . '/' . $file ) ) === $hash, 'pinned genuine source' );
	}
	foreach ( array( 'load.php', 'plugin.php', 'class-wp-error.php', 'cache.php', 'class-wpdb.php' ) as $file ) {
		require_once ABSPATH . WPINC . '/' . $file;
	}
	foreach ( array( 'class-source-policy.php', 'class-source-policy-native-profile.php', 'class-source-policy-query-context.php', 'class-source-policy-no-reconnect-connection.php' ) as $file ) {
		require_once dirname( __DIR__, 2 ) . '/includes/' . $file;
	}
	wp_using_ext_object_cache( false ); // Initialize this private process's core-cache posture, not site configuration.
	wp_cache_init();
	$wpdb    = null;
	$binding = array(
		'core_file'      => ABSPATH . WPINC . '/class-wpdb.php',
		'driver_file'    => null,
		'cache_file'     => ABSPATH . WPINC . '/class-wp-object-cache.php',
		'cache_api_file' => ABSPATH . WPINC . '/load.php',
		'observers'      => array(),
		'observer_files' => array(),
	);
	$class   = new ReflectionClass( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' );
	$owner   = $class->newInstanceWithoutConstructor();
	$class->getConstructor()->invoke( $owner );
	$frame = array(
		'core'     => '7.1.3',
		'database' => 'local',
		'table'    => $argv[2] . 'options',
		'host'     => '127.0.0.1',
		'port'     => 10004,
		'user'     => 'root',
		'password' => 'audit' === $argv[3] ? 'inert-audit-only' : getenv( 'MYSQL_PWD' ),
		'charset'  => DB_CHARSET,
		'collate'  => DB_COLLATE,
	);
	adbb_check( Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::settings( $frame ), 'finite private fixture settings' );
	adbb_check( Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::source( $owner, $binding ), 'genuine executable closure and empty observer eligibility' );
	adbb_check( null === $owner->dbh && ! $owner->ready && 0 === $owner->num_queries, 'own constructor remains handle-free' );
	adbb_check( is_wp_error( $owner::create() ) && false === $owner->db_connect() && false === $owner->check_connection(), 'factories and reconnect remain closed' );
	$stats = mysqli_get_client_stats();
	adbb_check( '0' === $stats['connect_success'] && '0' === $stats['connect_failure'] && '0' === $stats['com_query'], 'audit opens no native session or SQL' );
	$expected = array_map(
		static function ( $file ) {
			return realpath( ABSPATH . WPINC . '/' . $file );
		},
		array_keys( $pins )
	);
	foreach ( get_included_files() as $included ) {
		adbb_check( 0 !== strpos( $included, realpath( ABSPATH ) . DIRECTORY_SEPARATOR ) || in_array( $included, $expected, true ), 'only pinned core libraries, never application bootstrap' );
	}
	if ( 'audit' === $argv[3] ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed CLI text and integer assertion count only.
		echo 'audit checks:', $checks, " native_connections:0 sql_queries:0 fixtures:0\n";
		return;
	}
	require __DIR__ . '/source-policy-bootstrap-native-cases.php';
	$report = adbb_native_cases( $class, $frame, $binding );
	echo json_encode(
		array(
			'checks'            => $checks,
			'cases'             => $report,
			'production_wiring' => 'disabled',
		)
	), "\n";
} catch ( Throwable $error ) {
	fwrite( STDERR, 'Owned bootstrap proof failed in ' . $group . ' after ' . $checks . ' checks: ' . ( $GLOBALS['adbb_last_check'] ?? 'before assertion' ) . "\n" );
	exit( 1 );
}
