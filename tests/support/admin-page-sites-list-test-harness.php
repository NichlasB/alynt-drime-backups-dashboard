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
require_once __DIR__ . '/admin-page-sites-list-test-doubles.php';

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
