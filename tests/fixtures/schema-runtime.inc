<?php
// Process-local WordPress API doubles. No connection, storage or installed core.
$schema_state = array(
	'version' => '7', 'writes' => array(), 'delta' => array(), 'tables' => array(),
	'failure' => '', 'hooks' => array(), 'scheduled' => 0, 'allowed' => false,
);
function get_option( $name, $default = false ) {
	global $schema_state;
	return 'alynt_drime_backups_dashboard_schema_version' === $name ? $schema_state['version'] : $default;
}
function update_option( $name, $value, $autoload = null ) {
	global $schema_state, $wpdb;
	$probes = count( array_filter( $schema_state['delta'], function ( $call ) { return ! $call['execute']; } ) );
	$schema_state['writes'][] = array( $name, $value, $autoload, $wpdb->metadata_reads, $probes );
	if ( 'write' === $schema_state['failure'] || ( 'invalidate' === $schema_state['failure'] && '' === $value ) ) return false;
	$changed = $schema_state['version'] !== $value;
	$schema_state['version'] = $value;
	return $changed;
}
function dbDelta( $query, $execute = true ) {
	global $wpdb, $schema_state;
	if ( ! preg_match( '/CREATE TABLE (\S+)/', $query, $match ) ) throw new RuntimeException( 'Unexpected DDL fixture input' );
	$table = $match[1];
	$schema_state['delta'][] = array( 'execute' => $execute, 'hash' => hash( 'sha256', $query ), 'table' => $table );
	$wpdb->last_error = '';
	if ( 'throw' === $schema_state['failure'] ) throw new RuntimeException( 'private SQL failure detail' );
	if ( $execute ) {
		if ( $schema_state['failure'] !== $table ) $schema_state['tables'][ $table ] = true;
		return array( 'Planned change, deliberately not success proof' );
	}
	if ( 'malformed' === $schema_state['failure'] ) return null;
	if ( 'probe_error' === $schema_state['failure'] ) $wpdb->last_error = 'private SQL failure detail';
	if ( 'pending' === $schema_state['failure'] ) return array( 'Still needs ALTER' );
	if ( 'pending2' === $schema_state['failure'] && false !== strpos( $table, 'snapshots' ) ) return array( 'Still needs ALTER' );
	if ( 'pending3' === $schema_state['failure'] && false !== strpos( $table, 'actions' ) ) return array( 'Still needs ALTER' );
	return isset( $schema_state['tables'][ $table ] ) ? array() : array( 'Missing table' );
}
function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	global $schema_state;
	$schema_state['hooks'][] = $hook;
}
function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	global $schema_state;
	$schema_state['hooks'][] = $hook;
}
function current_user_can( $capability ) {
	global $schema_state;
	return $schema_state['allowed'];
}
function wp_die( $message ) { throw new RuntimeException( $message ); }
function wp_next_scheduled( $hook ) { return false; }
function wp_schedule_event( $timestamp, $recurrence, $hook, $args = array(), $wp_error = false ) {
	global $schema_state;
	$schema_state['scheduled']++;
	return true;
}
class Schema_Fixture_Wpdb {
	public $prefix = 'owned_';
	public $last_error = '';
	public $metadata_reads = 0;
	public function get_charset_collate() { return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'; }
	public function esc_like( $value ) { return addcslashes( $value, '_%\\' ); }
	public function prepare( $query, $value ) { return array( $query, $value ); }
	public function get_var( $prepared ) {
		global $schema_state;
		$this->metadata_reads++;
		$this->last_error = '';
		if ( 'metadata_throw' === $schema_state['failure'] ) throw new RuntimeException( 'private SQL failure detail' );
		if ( 'metadata' === $schema_state['failure'] ) {
			$this->last_error = 'private SQL failure detail';
			return null;
		}
		if ( ! is_array( $prepared ) || 'SHOW TABLES LIKE %s' !== $prepared[0] ) throw new RuntimeException( 'Unexpected metadata query' );
		foreach ( $schema_state['tables'] as $table => $exists ) {
			if ( $this->esc_like( $table ) === $prepared[1] && $exists ) {
				if ( 'metadata_stale' === $schema_state['failure'] ) $this->last_error = 'private SQL failure detail';
				return $table;
			}
		}
		return null;
	}
}
