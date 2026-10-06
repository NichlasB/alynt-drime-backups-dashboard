<?php
/**
 * Test bootstrap placeholder.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

$alynt_drime_backups_dashboard_tests_path = dirname( __DIR__ );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $alynt_drime_backups_dashboard_tests_path . DIRECTORY_SEPARATOR );
}

if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', dirname( $alynt_drime_backups_dashboard_tests_path ) );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

require_once $alynt_drime_backups_dashboard_tests_path . '/vendor/autoload.php';
require_once $alynt_drime_backups_dashboard_tests_path . '/tests/support/wordpress-shims.php';

require_once $alynt_drime_backups_dashboard_tests_path . '/alynt-drime-backups-dashboard.php';
