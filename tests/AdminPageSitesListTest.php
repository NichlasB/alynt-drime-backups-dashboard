<?php
/**
 * Admin Sites-list context tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-sites-list.php';

if ( ! function_exists( 'wp_list_pluck' ) ) {
	/**
	 * Minimal wp_list_pluck() test double.
	 *
	 * @param array<int,array<string,mixed>> $list List.
	 * @param string                         $field Field name.
	 * @return array<int,mixed>
	 */
	function wp_list_pluck( $list, $field ) {
		return array_map(
			static function ( $item ) use ( $field ) {
				return isset( $item[ $field ] ) ? $item[ $field ] : null;
			},
			$list
		);
	}
}

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

	/**
	 * Site contexts request explicit archive visibility modes.
	 *
	 * @return void
	 */
	public function test_site_contexts_filter_archived_visibility() {
		$harness = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness();

		$visible = $harness->context_for( 'visible' );
		$this->assertSame( 'exclude', $harness->sites->calls[0]['archived'] );
		$this->assertSame(
			array( 1, 2 ),
			array_map(
				static function ( $site ) {
					return $site['id'];
				},
				$visible['sites']
			)
		);
		$this->assertSame( 1, $visible['attention_count'] );

		$archived = $harness->context_for( 'archived' );
		$this->assertSame( 'only', $harness->sites->calls[1]['archived'] );
		$this->assertSame(
			array( 3 ),
			array_map(
				static function ( $site ) {
					return $site['id'];
				},
				$archived['sites']
			)
		);
		$this->assertSame( 1, $harness->attention_count_for_test() );
	}
}

/**
 * Harness exposing private Sites-list helpers.
 */
class Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List;

	/**
	 * Fake sites repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Sites
	 */
	public $sites;

	/**
	 * Fake snapshots repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Snapshots
	 */
	public $snapshots;

	/**
	 * Fake classifier.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Classifier
	 */
	public $classifier;

	/**
	 * Request-local Sites-list cache.
	 *
	 * @var array<string,array<string,mixed>>
	 */
	private $site_status_context = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->sites      = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Sites();
		$this->snapshots  = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Snapshots();
		$this->classifier = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Classifier();
	}

	/**
	 * Exposes visible Sites-list rows.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @return array<int,array<string,mixed>>
	 */
	public function visible_sites( array $sites ) {
		return $this->without_superseded_revoked_sites( $sites );
	}

	/**
	 * Exposes request-local site context.
	 *
	 * @param string $visibility Visibility.
	 * @return array<string,mixed>
	 */
	public function context_for( $visibility ) {
		return $this->site_status_context( $visibility );
	}

	/**
	 * Exposes default Attention count.
	 *
	 * @return int
	 */
	public function attention_count_for_test() {
		return $this->attention_count();
	}
}

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
