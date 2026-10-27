<?php
/**
 * Fake collaborators for admin Sites-list tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-sites-list-test-collaborators.php';

/**
 * Fake Sites repository.
 */
class Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Sites {
	/** This row-only collaborator never simulates a database failure. */
	public function last_list_read_failed() {
		return false;
	}

	/**
	 * Calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $calls = array();

	/**
	 * Visible rows override.
	 *
	 * @var array<int,array<string,mixed>>|null
	 */
	public $visible_rows = null;

	/**
	 * Archived rows override.
	 *
	 * @var array<int,array<string,mixed>>|null
	 */
	public $archived_rows = null;

	/**
	 * Returns rows by archive visibility mode.
	 *
	 * @param array<string,mixed> $args Arguments.
	 * @return array<int,array<string,mixed>>
	 */
	public function all( $args = array() ) {
		$args          = is_array( $args ) ? $args : array();
		$this->calls[] = $args;
		$archived      = isset( $args['archived'] ) ? $args['archived'] : 'include';

		if ( 'only' === $archived ) {
			if ( is_array( $this->archived_rows ) ) {
				return $this->archived_rows;
			}

			return array(
				array(
					'id'                => 3,
					'expected_origin'   => 'https://archived.example.test',
					'enrollment_status' => 'revoked',
					'category'          => 'needs_attention',
					'archived_at'       => '2026-09-19 18:30:00',
				),
			);
		}

		if ( is_array( $this->visible_rows ) ) {
			return $this->visible_rows;
		}

		return array(
			array(
				'id'                => 1,
				'expected_origin'   => 'https://active.example.test',
				'enrollment_status' => 'active',
				'category'          => 'working',
				'archived_at'       => '',
			),
			array(
				'id'                => 2,
				'expected_origin'   => 'https://attention.example.test',
				'enrollment_status' => 'active',
				'category'          => 'needs_attention',
				'archived_at'       => '',
			),
		);
	}
}
