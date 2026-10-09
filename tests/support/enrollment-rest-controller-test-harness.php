<?php
/**
 * Enrollment REST controller test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/enrollment-rest-controller-repository-double.php';
require_once __DIR__ . '/enrollment-rest-controller-transient-shims.php';
require_once __DIR__ . '/enrollment-rest-controller-request-fixtures.php';
require_once __DIR__ . '/enrollment-rest-controller-site-payload-fixtures.php';

/**
 * Shared enrollment REST controller fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller_Test_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Enrollment_REST_Request_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Enrollment_REST_Site_Payload_Fixtures;

	/**
	 * Creates the controller.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository $repository Repository.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault|null          $vault Vault.
	 * @return Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller
	 */
	private function controller( $repository, $vault = null ) {
		return new Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller(
			$repository,
			new Alynt_Drime_Backups_Dashboard_Origin_Validator(),
			$vault instanceof Alynt_Drime_Backups_Dashboard_Credential_Vault ? $vault : new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) )
		);
	}
}
