<?php
/**
 * Isolated source/API worker, not WordPress or database execution.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( realpath( isset( $_SERVER['SCRIPT_FILENAME'] ) ? $_SERVER['SCRIPT_FILENAME'] : '' ) !== __FILE__ ) {
	return;
}
define( 'ABSPATH', dirname( __DIR__ ) . '/fixtures/schema-core/' );
require dirname( __DIR__ ) . '/fixtures/schema-runtime.inc';
require __DIR__ . '/../bootstrap.php';
$wpdb = new Schema_Fixture_Wpdb();
$scenario = isset( $argv[1] ) ? $argv[1] : '';
$schema_state['failure'] = $scenario;
if ( in_array( $scenario, array( 'noop', 'reinstall', 'invalidate', 'bootstrap_ok', 'reinstall_failed' ), true ) ) $schema_state['version'] = '8';
if ( 'numeric_reinstall' === $scenario ) $schema_state['version'] = 8;
if ( 'corrupt' === $scenario ) $schema_state['version'] = array( 'invalid' );
if ( 'reinstall_failed' === $scenario ) $schema_state['failure'] = 'owned_alynt_drime_dashboard_sites';
if ( 'noop' === $scenario || 'retry' === $scenario ) {
	if ( 'retry' === $scenario ) $schema_state['failure'] = 'owned_alynt_drime_dashboard_sites';
	$result = Alynt_Drime_Backups_Dashboard_Storage::maybe_upgrade();
	if ( 'retry' === $scenario ) {
		$first = array( 'error' => is_wp_error( $result ), 'version' => $schema_state['version'] );
		$schema_state['failure'] = '';
		$result = Alynt_Drime_Backups_Dashboard_Storage::maybe_upgrade();
	}
} elseif ( in_array( $scenario, array( 'bootstrap', 'activation', 'bootstrap_ok', 'activation_ok' ), true ) ) {
	$schema_state['failure'] = false === strpos( $scenario, '_ok' ) ? 'owned_alynt_drime_dashboard_sites' : '';
	$schema_state['hooks'] = array();
	if ( 0 === strpos( $scenario, 'bootstrap' ) ) {
		$plugin = new Alynt_Drime_Backups_Dashboard_Plugin();
		$property = new ReflectionProperty( $plugin, 'admin_page' );
		if ( PHP_VERSION_ID < 80100 ) $property->setAccessible( true );
		$initialized = null !== $property->getValue( $plugin );
		ob_start();
		$plugin->storage_error_notice();
		$unauthorized_notice = ob_get_clean();
		$schema_state['allowed'] = true;
		ob_start();
		$plugin->storage_error_notice();
		$notice = ob_get_clean();
		$result = true;
	} else {
		try {
			Alynt_Drime_Backups_Dashboard_Activator::activate();
			$result = true;
		} catch ( RuntimeException $error ) {
			$result = new WP_Error( 'activation_failed', $error->getMessage() );
		}
	}
} else {
	$result = Alynt_Drime_Backups_Dashboard_Storage::maybe_install();
}
echo json_encode( array(
	'error' => is_wp_error( $result ), 'success' => true === $result,
	'message' => is_wp_error( $result ) ? $result->get_error_message() : '',
	'state' => $schema_state, 'metadata_reads' => $wpdb->metadata_reads,
	'first' => isset( $first ) ? $first : null,
	'initialized' => isset( $initialized ) ? $initialized : null,
	'notice' => isset( $notice ) ? $notice : '',
	'unauthorized_notice' => isset( $unauthorized_notice ) ? $unauthorized_notice : '',
) );
