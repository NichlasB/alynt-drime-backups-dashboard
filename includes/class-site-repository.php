<?php
/**
 * Dashboard site repository.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and writes dashboard-owned client site records.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Site_Repository {
	use Alynt_Drime_Backups_Dashboard_Site_Repository_Reads;
	use Alynt_Drime_Backups_Dashboard_Site_Repository_Writes;
}
