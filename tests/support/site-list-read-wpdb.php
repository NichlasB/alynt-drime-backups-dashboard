<?php
/**
 * Socket-free wpdb collaborator for real site-list repository reads.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/site-repository-wordpress-shims.php';

if ( ! function_exists( 'wp_parse_args' ) ) {
	/** Array-only fixture adapter; not a proof of WordPress argument parsing. */
	function wp_parse_args( $args, $defaults = array() ) {
		return array_merge( $defaults, $args );
	}
}

class Alynt_Drime_Backups_Dashboard_Test_Site_List_Read_WPDB {
	public $prefix = 'tenant_42_';
	public $last_error = '';
	public $responses = array();
	public $queries = array();

	public function prepare( $query, ...$args ) {
		return $query;
	}

	public function get_results( $query, $output ) {
		$this->queries[] = $query;
		if ( empty( $this->responses ) ) {
			throw new LogicException( 'Unexpected site-list query in isolated test.' );
		}
		$response = array_shift( $this->responses );
		$this->last_error = $response[1];
		return $response[0];
	}
}
