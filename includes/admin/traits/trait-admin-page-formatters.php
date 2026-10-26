<?php
/**
 * Admin page scalar formatters.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Composes scalar, status, time, and diagnostic formatter traits.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Formatters {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Status_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Site_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostic_Formatters;
}
