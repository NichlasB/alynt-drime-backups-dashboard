<?php
/**
 * Snapshot and classifier doubles for admin Sites-list tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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
