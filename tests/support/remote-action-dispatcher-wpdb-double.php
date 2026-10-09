<?php
/**
 * Fake wpdb for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-dispatcher-wpdb-write-methods.php';

/**
 * Fake wpdb for dispatcher tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB {
	use Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB_Write_Methods;

	/**
	 * Prefix.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * Insert ID.
	 *
	 * @var int
	 */
	public $insert_id = 44;

	/**
	 * Site row.
	 *
	 * @var array<string,mixed>
	 */
	public $site = array();

	/**
	 * Snapshot row.
	 *
	 * @var array<string,mixed>
	 */
	public $snapshot = array();

	/**
	 * Prepares SQL.
	 *
	 * @param string $query Query.
	 * @param mixed  ...$args Args.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		unset( $args );
		return $query;
	}

	/**
	 * Gets a row.
	 *
	 * @param string $query Query.
	 * @param string $output Output.
	 * @return array<string,mixed>|null
	 */
	public function get_row( $query, $output = ARRAY_A ) {
		unset( $output );

		if ( false !== strpos( $query, 'alynt_drime_dashboard_snapshots' ) ) {
			return empty( $this->snapshot ) ? null : $this->snapshot;
		}

		return empty( $this->site ) ? null : $this->site;
	}
}
