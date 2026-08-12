<?php
/**
 * Admin page site rendering.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Composes the dashboard site-section rendering traits.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Sites {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Add_Site;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Attention;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Site_Detail;
}
