<?php
/**
 * Admin page diagnostics rendering.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Composes diagnostics rendering traits for the admin page.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Overview;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Settings;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Event_Log;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Tables;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Support_Output;
}
