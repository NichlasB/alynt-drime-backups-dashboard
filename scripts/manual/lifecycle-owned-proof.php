<?php
/**
 * Explicit owned lifecycle proof. Never invoked by CI or application requests.
 * Requires Site Operations confirmation, bundled-client baseline, byte-verified
 * private source copy and MYSQL_PWD inherited in-process, never in arguments.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Approved owned physical fixture creation/inspection/cleanup only; exact manifest, never application tables. WP6.0 lacks %i.
if ( PHP_SAPI !== 'cli' || count( $argv ) !== 5 || '--confirmed-plugin-tester-lifecycle-proof' !== $argv[1] || ! preg_match( '/^adblf_[a-f0-9]{12}_$/D', $argv[2] ) || ! in_array( $argv[3], array( 'preflight', 'preserve', 'false', 'purge' ), true ) || ! preg_match( '~^C:/Users/Captain/AppData/Local/Temp/adb-lifecycle-[a-f0-9]{12}/plugins$~D', $argv[4] ) || ! getenv( 'MYSQL_PWD' ) ) {
	fwrite( STDERR, "Requires confirmed local target, owned prefix, fixed mode and private source root\n" );
	exit( 1 );
}
define( 'ADB_LIFECYCLE_PROOF', true );
require __DIR__ . '/lifecycle-proof-sql-scope.php';
require __DIR__ . '/lifecycle-proof-core.php';
$wpdb->set_prefix( $argv[2] );
$tables       = array( $wpdb->options, $wpdb->prefix . 'alynt_drime_dashboard_sites', $wpdb->prefix . 'alynt_drime_dashboard_snapshots', $wpdb->prefix . 'alynt_drime_dashboard_actions' );
$owned_plugin = 'alynt-drime-backups-dashboard/alynt-drime-backups-dashboard.php';
$checks       = 0;
$groups       = array();
$queries      = 0;
$armed        = false;
$cleaned      = false;
set_time_limit( 40 );
if ( false === $wpdb->query( 'SET SESSION max_statement_time=3, lock_wait_timeout=3, innodb_lock_wait_timeout=3' ) ) {
	throw new RuntimeException( 'Bounded fixture session unavailable' );
}
/**
 * Counts a fixed-label behavioral proof assertion.
 *
 * @param bool   $condition Actual outcome.
 * @param string $label Fixed label.
 * @return void
 * @throws RuntimeException If the assertion fails.
 */
function adb_lifecycle_check( $condition, $label ) {
	global $checks;
	++$checks;
	if ( ! $condition ) {
		throw new RuntimeException( 'Lifecycle assertion: ' . $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed-label CLI report, no request/HTML context.
	}
}
add_filter(
	'query',
	function ( $sql ) use ( $tables, &$queries ) {
		adb_lifecycle_assert_sql_scope( $sql, $tables );
		++$queries;
		return $sql;
	}
);
$exists  = function ( $table ) {
	global $wpdb;
	$observed = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
	if ( ! empty( $wpdb->last_error ) ) {
		throw new RuntimeException( 'Owned fixture metadata read failed' );
	}
	return $table === $observed;
};
$cleanup = function () use ( &$armed, &$cleaned, $tables, $exists ) {
	global $wpdb;
	if ( ! $armed || $cleaned ) {
		return;
	}
	foreach ( array_reverse( $tables ) as $table ) {
		if ( $exists( $table ) ) {
			$preview = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table ); // Exact owned cleanup preview.
			adb_lifecycle_check( is_numeric( $preview ) && empty( $wpdb->last_error ), 'successful exact cleanup preview' );
			adb_lifecycle_check( false !== $wpdb->query( 'DROP TABLE ' . $table ), 'exact owned table dropped' );
		}
		adb_lifecycle_check( ! $exists( $table ), 'owned table absent after cleanup' );
	}
	$cleaned = true;
};
register_shutdown_function(
	function () use ( $cleanup ) {
		try {
			$cleanup();
		} catch ( Throwable $error ) {
				fwrite( STDERR, "Owned lifecycle cleanup incomplete; inspect manifest\n" ); }
	}
);
try {
	foreach ( $tables as $table ) {
		adb_lifecycle_check( ! $exists( $table ), 'all targets absent before arming cleanup' );
	}
	if ( 'preflight' === $argv[3] ) {
		$metadata = get_plugin_data( WP_PLUGIN_DIR . '/' . $owned_plugin );
		adb_lifecycle_check( 'Alynt Drime Backups Dashboard' === $metadata['Name'], 'genuine core metadata/markup dependencies' );
		plugin_sandbox_scrape( $owned_plugin );
		adb_lifecycle_check( false !== has_action( 'activate_' . $owned_plugin ), 'genuine root activation registration' );
		adb_lifecycle_check( false !== has_action( 'deactivate_' . $owned_plugin ), 'genuine root deactivation registration' );
		echo json_encode(
			array(
				'checks'           => $checks,
				'preflight'        => true,
				'queries'          => $queries,
				'fixtures_created' => 0,
			)
		) . "\n";
		exit;
	}
	$armed = true;
	adb_lifecycle_check( false !== $wpdb->query( "CREATE TABLE $wpdb->options (option_id bigint unsigned NOT NULL AUTO_INCREMENT, option_name varchar(191) NOT NULL, option_value longtext NOT NULL, autoload varchar(20) NOT NULL DEFAULT 'off', PRIMARY KEY (option_id), UNIQUE KEY option_name (option_name))" ), 'owned options created' );
	update_option( 'siteurl', 'http://plugin-tester.local', false );
	update_option( 'home', 'http://plugin-tester.local', false );
	update_option( 'active_plugins', array(), false );
	update_option( 'uninstall_plugins', array(), false );
	adb_lifecycle_check( null === activate_plugin( $owned_plugin ), 'genuine core activation succeeds without output' );
	adb_lifecycle_check( is_plugin_active( $owned_plugin ), 'physical fixture active_plugins admits plugin' );
	adb_lifecycle_check( 1 === did_action( 'activate_' . $owned_plugin ), 'real activation callback fired' );
	adb_lifecycle_check( '8' === get_option( 'alynt_drime_backups_dashboard_schema_version' ), 'installed schema marker' );
	foreach ( array_slice( $tables, 1 ) as $table ) {
		adb_lifecycle_check( $exists( $table ), 'actual activation created table' );
	}
	$poll    = 'alynt_drime_backups_dashboard_poll_sites';
	$retain  = 'alynt_drime_backups_dashboard_cleanup_snapshots';
	$cron_ok = function () use ( $poll, $retain ) {
		adb_lifecycle_check( 'alynt_drime_backups_dashboard_15_minutes' === wp_get_schedule( $poll ), 'poll recurrence' );
		adb_lifecycle_check( 'daily' === wp_get_schedule( $retain ), 'retention recurrence' );
		$count = array(
			$poll   => 0,
			$retain => 0,
		);
		foreach ( _get_cron_array() as $events ) {
			foreach ( $count as $hook => $unused ) {
				$count[ $hook ] += isset( $events[ $hook ] ) ? count( $events[ $hook ] ) : 0;
			}
		}
		adb_lifecycle_check( array( 1, 1 ) === array_values( $count ), 'exactly one of each owned maintenance event' );
	};
	$cron_ok();
	adb_lifecycle_check( null === activate_plugin( $owned_plugin ), 'repeat core activation is successful no-op' );
	adb_lifecycle_check( 1 === did_action( 'activate_' . $owned_plugin ), 'repeat active activation does not rerun hook' );
	$cron_ok();
	$groups[] = 'genuine activation and idempotency';
	adb_lifecycle_check(
		false !== $wpdb->insert(
			$tables[1],
			array(
				'public_id'                 => 'owned-site',
				'site_label'                => 'Owned lifecycle sentinel',
				'polling_secret_ciphertext' => 'owned-not-a-credential',
				'created_at'                => '2026-01-01 00:00:00',
				'updated_at'                => '2026-01-01 00:00:00',
			)
		),
		'owned pairing sentinel'
	);
	adb_lifecycle_check(
		false !== $wpdb->insert(
			$tables[2],
			array(
				'dashboard_site_id' => 1,
				'observed_at'       => '2026-01-01 00:00:00',
				'payload_json'      => '{"owned":true}',
			)
		),
		'owned snapshot sentinel'
	);
	adb_lifecycle_check(
		false !== $wpdb->insert(
			$tables[3],
			array(
				'public_id'             => 'owned-action',
				'dashboard_site_id'     => 1,
				'requested_at'          => '2026-01-01 00:00:00',
				'expires_at'            => '2026-01-02 00:00:00',
				'redacted_context_json' => '{}',
				'created_at'            => '2026-01-01 00:00:00',
				'updated_at'            => '2026-01-01 00:00:00',
			)
		),
		'owned action sentinel'
	);
	$options = array(
		'alynt_drime_backups_dashboard_schema_version'     => '8',
		'alynt_drime_backups_dashboard_source_policies'    => array( 'owned' => true ),
		'alynt_drime_backups_dashboard_diagnostics_settings' => array( 'enabled' => false ),
		'alynt_drime_backups_dashboard_diagnostics_events' => array( 'owned-event' ),
		'alynt_drime_backups_dashboard_audit_events'       => array( 'owned-audit' ),
	);
	foreach ( $options as $key => $value ) {
		update_option( $key, $value, false );
	}
	update_option( 'owned_unrelated_option', 'preserve', false );
	wp_schedule_event( time() + 120, 'daily', 'owned_unrelated_hook' );
	$unrelated_cron = wp_next_scheduled( 'owned_unrelated_hook' );
	$locks          = array( 'alynt_drime_backups_dashboard_poll_sites_lock', 'alynt_drime_backups_dashboard_poll_site_lock_123', 'alynt_drime_backups_dashboard_enroll_fail_owned' );
	foreach ( array_merge( $locks, array( 'owned_unrelated_transient' ) ) as $key ) {
		adb_lifecycle_check( set_transient( $key, 'owned-value', 3600 ), 'owned transient seeded' );
	}
	$rows_before = array();
	foreach ( array_slice( $tables, 1 ) as $table ) {
		$rows_before[ $table ] = $wpdb->get_results( 'SELECT * FROM ' . $table, ARRAY_A );
	}
	$preserved = function () use ( $options, $rows_before ) {
		global $wpdb;
		foreach ( $options as $key => $value ) {
			adb_lifecycle_check( get_option( $key ) === $value, 'persistent option preserved' );
		}
		foreach ( $rows_before as $table => $rows ) {
			adb_lifecycle_check( $rows === $wpdb->get_results( 'SELECT * FROM ' . $table, ARRAY_A ), 'exact pairing/snapshot/action rows preserved' );
		}
	};
	deactivate_plugins( $owned_plugin );
	adb_lifecycle_check( ! is_plugin_active( $owned_plugin ), 'core deactivation removes only fixture active membership' );
	adb_lifecycle_check( 1 === did_action( 'deactivate_' . $owned_plugin ), 'real deactivation callback fired' );
	adb_lifecycle_check( false === wp_next_scheduled( $poll ) && false === wp_next_scheduled( $retain ), 'deactivation removes maintenance events' );
	adb_lifecycle_check( false === get_transient( $locks[0] ), 'deactivation removes global lock' );
	$preserved();
	$groups[] = 'deactivation preserves data';
	adb_lifecycle_check( null === activate_plugin( $owned_plugin ), 'genuine reactivation succeeds' );
	$cron_ok();
	$preserved();
	$groups[] = 'reactivation retains data and restores maintenance';
	deactivate_plugins( $owned_plugin );
	// Leave explicit fixture-only scheduling/lock remnants for uninstall cleanup.
	Alynt_Drime_Backups_Dashboard_Poller::schedule_events();
	foreach ( $locks as $key ) {
		set_transient( $key, 'owned-value', 3600 ); }
	if ( 'preserve' !== $argv[3] ) {
		define( 'ALYNT_DRIME_BACKUPS_DASHBOARD_PURGE_DATA_ON_UNINSTALL', 'purge' === $argv[3] );
	}
	foreach ( $tables as $table ) {
		adb_lifecycle_check( $exists( $table ), 'exact uninstall fixture table preview' );
		adb_lifecycle_check( 0 < (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table ), 'nonempty owned uninstall preview' );
	}
	adb_lifecycle_check( true === uninstall_plugin( $owned_plugin ), 'genuine core uninstall invokes canonical handler' );
	adb_lifecycle_check( false === wp_next_scheduled( $poll ) && false === wp_next_scheduled( $retain ), 'uninstall removes maintenance remnants' );
	foreach ( $locks as $key ) {
		foreach ( array( '_transient_', '_transient_timeout_' ) as $kind ) {
			adb_lifecycle_check( null === $wpdb->get_row( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name = %s", $kind . $key ) ), 'physical transient/timeout removed' );
		}
	}
	if ( 'purge' === $argv[3] ) {
		foreach ( array_slice( $tables, 1 ) as $table ) {
			adb_lifecycle_check( ! $exists( $table ), 'opt-in purge removes owned dashboard table' ); }
		foreach ( $options as $key => $value ) {
			adb_lifecycle_check( false === get_option( $key ), 'opt-in purge removes owned persistent option' ); }
	} else {
		$preserved();
	}
	adb_lifecycle_check( 'preserve' === get_option( 'owned_unrelated_option' ), 'unrelated option preserved' );
	adb_lifecycle_check( 'owned-value' === get_transient( 'owned_unrelated_transient' ), 'unrelated transient preserved' );
	adb_lifecycle_check( wp_next_scheduled( 'owned_unrelated_hook' ) === $unrelated_cron, 'unrelated cron timestamp preserved' );
	$groups[] = 'uninstall ' . $argv[3] . ' and scoped cleanup';
	$cleanup();
	echo json_encode(
		array(
			'checks'               => $checks,
			'groups'               => $groups,
			'mode'                 => $argv[3],
			'prefix'               => $argv[2],
			'queries'              => $queries,
			'wordpress'            => $wp_version,
			'php'                  => PHP_VERSION,
			'owned_tables_removed' => 4,
			'cleanup'              => $cleaned,
		)
	) . "\n";
} catch ( Throwable $error ) {
	while ( ob_get_level() ) {
		ob_end_clean(); }
	try {
		$cleanup();
	} catch ( Throwable $cleanup_error ) {
		fwrite( STDERR, "Owned lifecycle cleanup incomplete; inspect manifest\n" ); }
	fwrite(
		STDERR,
		json_encode(
			array(
				'checks'  => $checks,
				'groups'  => $groups,
				'prefix'  => $argv[2],
				'cleanup' => $cleaned,
				'queries' => $queries,
			)
		) . "\n"
	);
	fwrite( STDERR, basename( $error->getFile() ) . ':' . $error->getLine() . ' ' . $error->getMessage() . "\n" );
	exit( 1 );
}
