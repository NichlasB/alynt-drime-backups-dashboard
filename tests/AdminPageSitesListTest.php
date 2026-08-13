<?php
/**
 * Admin Sites-list context tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-sites-list.php';

/**
 * Tests Sites-list row filtering helpers.
 */
class AdminPageSitesListTest extends TestCase {
	/**
	 * Revoked duplicate rows are hidden when an active row exists for the same origin.
	 *
	 * @return void
	 */
	public function test_superseded_revoked_duplicate_is_hidden() {
		$harness = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness();
		$sites   = array(
			array(
				'id'                => 10,
				'expected_origin'   => 'https://internationalschoolofthehealingarts.com',
				'enrollment_status' => 'revoked',
			),
			array(
				'id'                => 11,
				'expected_origin'   => 'https://internationalschoolofthehealingarts.com/',
				'enrollment_status' => 'active',
			),
			array(
				'id'                => 12,
				'expected_origin'   => 'https://classes.internationalschoolofthehealingarts.com',
				'enrollment_status' => 'active',
			),
			array(
				'id'                => 13,
				'expected_origin'   => 'https://legacy.example.test',
				'enrollment_status' => 'revoked',
			),
		);

		$filtered = $harness->visible_sites( $sites );

		$this->assertSame(
			array( 11, 12, 13 ),
			array_map(
				static function ( $site ) {
					return $site['id'];
				},
				$filtered
			)
		);
	}
}

/**
 * Harness exposing private Sites-list helpers.
 */
class Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List;

	/**
	 * Exposes visible Sites-list rows.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @return array<int,array<string,mixed>>
	 */
	public function visible_sites( array $sites ) {
		return $this->without_superseded_revoked_sites( $sites );
	}
}
