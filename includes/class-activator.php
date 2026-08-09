<?php
/**
 * Activation tasks.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation handler.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Activator {
	/**
	 * Runs on activation.
	 *
	 * @return void
	 */
	public static function activate() {
		Alynt_Drime_Backups_Dashboard_Storage::maybe_install();
	}
}
