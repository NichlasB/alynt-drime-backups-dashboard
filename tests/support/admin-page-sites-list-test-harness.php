<?php
/**
 * Admin Sites-list test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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

require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-sites-list-context.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-sites-list.php';

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
