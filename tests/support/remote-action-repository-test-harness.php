<?php
/**
 * Remote action repository test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

require_once __DIR__ . '/remote-action-repository-wpdb-query-methods.php';
require_once __DIR__ . '/remote-action-repository-wpdb-double.php';

if ( ! function_exists( 'current_time' ) ) {
	/**
	 * Test current_time shim.
	 *
	 * @param string $type Type.
	 * @param bool   $gmt GMT.
	 * @return string
	 */
	function current_time( $type, $gmt = false ) {
		unset( $type, $gmt );
		return '2099-01-01 00:00:00';
	}
}

/**
 * Shared remote action repository test fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Test_Fixtures {
	/**
	 * Creates a fresh repository under test.
	 *
	 * @return Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
	 */
	private function remote_action_repository() {
		return new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
	}

	/**
	 * Returns a stored action row with no existing redacted context.
	 *
	 * @return array<string,mixed>
	 */
	private function empty_remote_action_row() {
		return array(
			'id'                    => 321,
			'redacted_context_json' => wp_json_encode( array() ),
		);
	}
}
