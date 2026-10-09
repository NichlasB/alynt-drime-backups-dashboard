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
	/**
	 * Calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $calls = array();

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
