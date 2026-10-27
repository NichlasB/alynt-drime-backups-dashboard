<?php
/**
 * Query-sensitive snapshot row model; not a SQL engine or database proof.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-dispatcher-wpdb-double.php';

/**
 * Models the existing selectors while retaining isolated dispatcher support.
 */
class Alynt_Drime_Backups_Dashboard_Test_Snapshot_Selection_WPDB extends Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB {

	/** @var array<int,array<string,mixed>> Unordered stored rows. */
	public $rows = array();
	/** @var array<int,mixed> Bound values from the actual repository. */
	public $prepared_args = array();
	/** @var string Last snapshot query. */
	public $last_query = '';

	/**
	 * Captures values without interpolating or executing SQL.
	 *
	 * @param string $query Query template.
	 * @param mixed  ...$args Bound values.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		$this->prepared_args = isset( $args[0] ) && is_array( $args[0] ) ? $args[0] : $args;
		return $query;
	}

	/**
	 * Models only the snapshot selector, delegating unrelated fake site reads.
	 *
	 * @param string $query Query template.
	 * @param string $output Output format.
	 * @return array<string,mixed>|null
	 */
	public function get_row( $query, $output = ARRAY_A ) {
		if ( false === strpos( $query, 'alynt_drime_dashboard_snapshots' ) ) {
			return parent::get_row( $query, $output );
		}
		$rows = $this->selected_rows( $query );
		return empty( $rows ) ? null : $rows[0];
	}

	/**
	 * Models bulk current rows or chronological summary history.
	 *
	 * @param string $query Query template.
	 * @param string $output Output format.
	 * @return array<int,array<string,mixed>>
	 */
	public function get_results( $query, $output = ARRAY_A ) {
		unset( $output );
		if ( false !== strpos( $query, 'MAX(id) AS latest_id' ) ) {
			$this->last_query = $query;
			$latest           = array();
			foreach ( $this->rows as $row ) {
				$site_id = $row['dashboard_site_id'];
				if ( in_array( $site_id, $this->prepared_args, true ) && ( ! isset( $latest[ $site_id ] ) || $row['id'] > $latest[ $site_id ]['id'] ) ) {
					$latest[ $site_id ] = $row;
				}
			}
			return array_values( $latest );
		}
		$rows = array_slice( $this->selected_rows( $query ), 0, $this->prepared_args[1] );
		return array_map(
			function ( $row ) {
				unset( $row['payload_json'] );
				return $row;
			},
			$rows
		);
	}

	/**
	 * Honors the actual equality predicate and ordering, including the old bug.
	 *
	 * @param string $query Query template.
	 * @return array<int,array<string,mixed>>
	 */
	private function selected_rows( $query ) {
		$this->last_query = $query;
		if ( false === strpos( $query, 'WHERE dashboard_site_id = %d' ) || ! preg_match( '/ORDER BY (observed_at DESC, )?id DESC/', $query ) ) {
			throw new UnexpectedValueException( 'Unsupported snapshot selector in isolated row model.' );
		}
		$rows = array_values(
			array_filter(
				$this->rows,
				function ( $row ) {
					return $row['dashboard_site_id'] === $this->prepared_args[0];
				}
			)
		);
		$by_observed_at = false !== strpos( $query, 'ORDER BY observed_at DESC' );
		usort(
			$rows,
			function ( $left, $right ) use ( $by_observed_at ) {
				$time_order = $by_observed_at ? strcmp( $right['observed_at'], $left['observed_at'] ) : 0;
				return 0 !== $time_order ? $time_order : $right['id'] <=> $left['id'];
			}
		);
		return $rows;
	}
}
