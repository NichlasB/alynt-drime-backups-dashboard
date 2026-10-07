<?php
/**
 * Fake collaborators for admin Sites-list tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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

/**
 * Fake snapshots repository.
 */
class Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Snapshots {
	/**
	 * Returns empty snapshots keyed by site ID.
	 *
	 * @param array<int,int> $site_ids Site IDs.
	 * @return array<int,array<string,mixed>>
	 */
	public function latest_by_site_ids( array $site_ids ) {
		return array_fill_keys( $site_ids, array() );
	}
}

/**
 * Fake classifier.
 */
class Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Classifier {
	/**
	 * Returns fixture category.
	 *
	 * @param array<string,mixed>      $site Site.
	 * @param array<string,mixed>|null $snapshot Snapshot.
	 * @return array<string,string>
	 */
	public function classify( array $site, $snapshot ) {
		return array(
			'category' => isset( $site['category'] ) ? $site['category'] : 'working',
		);
	}
}
