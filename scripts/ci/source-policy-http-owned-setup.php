<?php
/**
 * One explicit owned installation; never installed-site lifecycle operations.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL, WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Fixed owned fixture setup and exact physical readback only.
if ( 'cli' !== PHP_SAPI || array( __FILE__, '--owned-http-setup' ) !== $argv ) {
	exit( 1 ); }
require __DIR__ . '/source-policy-http-owned-worker.php';
require_once __DIR__ . '/source-policy-http-owned-diagnostic.php';

/** Prepare install-skipped core events in owned setup, never in request scope. */
function adbh_prepare_core_events() {
	adbh_check( 'setup' === ADB_HTTP_WORKER && 'seed' === $GLOBALS['adbh_stage'] && ! wp_installing() && true === DISABLE_WP_CRON, 'core-events-context' );
	// Pinned recovery mode, Site Health and privacy bootstrap callbacks schedule
	// these only outside installation. DISABLE_WP_CRON prevents spawning, not seeding.
	$events = array(
		'recovery_mode_clean_expired_keys'          => array( 'daily', 86400, 0 ),
		'wp_site_health_scheduled_check'            => array( 'weekly', 604800, 86400 ),
		'wp_privacy_delete_old_export_files'        => array( 'hourly', 3600, 0 ),
		'wp_privacy_personal_data_cleanup_requests' => array( 'daily', 86400, 0 ),
	);
	$now    = time();
	foreach ( $events as $hook => $spec ) {
		adbh_check( false === wp_next_scheduled( $hook ), 'core-event-absence' );
		$timestamp = $now + $spec[2];
		adbh_check( true === wp_schedule_event( $timestamp, $spec[0], $hook, array(), true ), 'core-event-schedule' );
		$event = wp_get_scheduled_event( $hook );
		adbh_check(
			is_object( $event ) && 'stdClass' === get_class( $event )
			&& $event->timestamp === $timestamp && $event->schedule === $spec[0]
			&& array() === $event->args && $event->interval === $spec[1],
			'core-event-readback'
		);
	}
}

/** Complete only the pinned default-widget initialization skipped before install. */
function adbh_prepare_core_widgets() {
	global $wp_widget_factory;
	adbh_check( 'setup' === ADB_HTTP_WORKER && 'seed' === $GLOBALS['adbh_stage'] && ! wp_installing() && true === DISABLE_WP_CRON, 'core-widgets-context' );
	adbh_check( is_object( $wp_widget_factory ) && 'WP_Widget_Factory' === get_class( $wp_widget_factory ) && array() === $wp_widget_factory->widgets, 'core-widgets-before' );
	// wp_widgets_init returned before registration when the database was absent.
	// Its fixed core callbacks now prepare missing settings before the seal.
	wp_widgets_init();
	$classes = array(
		'WP_Widget_Pages',
		'WP_Widget_Calendar',
		'WP_Widget_Archives',
		'WP_Widget_Media_Audio',
		'WP_Widget_Media_Image',
		'WP_Widget_Media_Gallery',
		'WP_Widget_Media_Video',
		'WP_Widget_Meta',
		'WP_Widget_Search',
		'WP_Widget_Text',
		'WP_Widget_Categories',
		'WP_Widget_Recent_Posts',
		'WP_Widget_Recent_Comments',
		'WP_Widget_RSS',
		'WP_Widget_Tag_Cloud',
		'WP_Nav_Menu_Widget',
		'WP_Widget_Custom_HTML',
		'WP_Widget_Block',
	);
	adbh_check( array_keys( $wp_widget_factory->widgets ) === $classes, 'core-widgets-registry' );
	foreach ( $classes as $class ) {
		$widget = $wp_widget_factory->widgets[ $class ];
		adbh_check( is_object( $widget ) && get_class( $widget ) === $class && is_array( get_option( $widget->option_name ) ), 'core-widget-readback' );
	}
}

/** Seed the pinned theme's genuine pattern cache, not a synthetic request result. */
function adbh_prepare_core_patterns() {
	adbh_check( 'setup' === ADB_HTTP_WORKER && 'seed' === $GLOBALS['adbh_stage'] && ! wp_installing() && true === DISABLE_WP_CRON, 'core-patterns-context' );
	adbh_check( false === wp_is_development_mode( 'theme' ), 'core-patterns-mode' );
	$theme = wp_get_theme();
	$root  = $GLOBALS['adbh_private']['content_path'] . '/themes';
	adbh_check(
		is_object( $theme ) && 'WP_Theme' === get_class( $theme ) && $theme->exists()
		&& 'twentytwentyfive' === $theme->get_stylesheet() && false === $theme->parent()
		&& $theme->get_theme_root() === $root,
		'core-patterns-theme'
	);
	$now      = time();
	$patterns = $theme->get_block_patterns();
	$key      = 'wp_theme_files_patterns-' . md5( $root . '/twentytwentyfive' );
	$cached   = get_site_transient( $key );
	$expires  = get_option( '_site_transient_timeout_' . $key );
	adbh_check(
		is_array( $patterns ) && count( $patterns ) > 0 && is_array( $cached )
		&& array( 'version', 'patterns' ) === array_keys( $cached )
		&& $cached['version'] === $theme->get( 'Version' ) && $cached['patterns'] === $patterns
		&& is_int( $expires ) && $expires >= $now + 600,
		'core-patterns-readback'
	); // Cache must outlive the unchanged ten-minute outer job ceiling.
}

try {
	$_SERVER['HTTP_HOST']      = '127.0.0.1:18777';
	$_SERVER['SERVER_NAME']    = '127.0.0.1';
	$_SERVER['SERVER_PORT']    = '18777';
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI']    = '/wp-admin/install.php';
	$_SERVER['PHP_SELF']       = '/wp-admin/install.php';
	adbh_bootstrap( 'setup' );
	require ABSPATH . 'wp-admin/includes/upgrade.php';
	$GLOBALS['adbh_containment']->begin_install();
	$p                     = $GLOBALS['adbh_private'];
	$GLOBALS['adbh_stage'] = 'install';
	$result                = wp_install( 'Owned HTTP proof', 'owned_one', 'owned-one@example.invalid', 0, '', $p['password'] );
	adbh_check( is_array( $result ) && 1 === $result['user_id'], 'genuine-install' );
	unset( $result );
	wp_installing( false );
	$GLOBALS['adbh_stage'] = 'seed';
	adbh_prepare_core_events();
	adbh_prepare_core_widgets();
	adbh_prepare_core_patterns();
	foreach ( array( array( 'owned_two', 'owned-two@example.invalid', 'administrator' ), array( 'owned_low', 'owned-low@example.invalid', 'subscriber' ) ) as $index => $row ) {
		$owned_user_id = wp_create_user( $row[0], $p['password'], $row[1] );
		adbh_check( $index + 2 === $owned_user_id, 'owned-user-id' );
		( new WP_User( $owned_user_id ) )->set_role( $row[2] );
	}
	adbh_check( array() === get_option( 'active_plugins' ) && 'twentytwentyfive' === get_option( 'template' ) && 'twentytwentyfive' === get_option( 'stylesheet' ), 'fresh-content-graph' );
	adbh_check( add_option( 'alynt_drime_backups_dashboard_source_policies', array( 9 => array( 'wpvivid' => 'external_optional' ) ), '', false ) && add_option( 'owned_non_target', 'preserve-exact-bytes', '', false ), 'owned-policy-seed' );
	global $wpdb;
	adbh_check( false !== $wpdb->query( 'CREATE TABLE ' . $p['prefix'] . 'alynt_drime_dashboard_sites (id bigint unsigned NOT NULL,PRIMARY KEY(id)) ENGINE=InnoDB' ) && 1 === $wpdb->insert( $p['prefix'] . 'alynt_drime_dashboard_sites', array( 'id' => 7 ) ), 'inert-record' );
	$owned_post = $wpdb->get_row( 'SELECT post_date,post_name FROM ' . $p['prefix'] . 'posts WHERE ID=1', ARRAY_A );
	register_shutdown_function(
		static function () use ( $owned_post ) {
			try {
				$GLOBALS['adbh_stage'] = 'finalize';
				$containment           = adbh_finish_packet( $owned_post );
				echo json_encode(
					array(
						'ledger'      => adbh_ledger_packet( 'setup' ),
						'containment' => $containment,
					)
				) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Private bounded count-only stdout, no HTTP response.
			} catch ( Throwable $error ) {
				fwrite( STDERR, json_encode( adbh_setup_diagnostic( $error, dirname( __DIR__, 3 ) ) ) . "\n" );
				exit( 1 ); }
		}
	);
} catch ( Throwable $error ) {
	fwrite( STDERR, json_encode( adbh_setup_diagnostic( $error, dirname( __DIR__, 3 ) ) ) . "\n" );
	exit( 1 );
}
