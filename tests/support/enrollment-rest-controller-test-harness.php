<?php
/**
 * Enrollment REST controller test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! function_exists( 'get_transient' ) ) {
	/**
	 * Test transient getter.
	 *
	 * @param string $key Transient key.
	 * @return mixed
	 */
	function get_transient( $key ) {
		return isset( $GLOBALS['alynt_drime_backups_dashboard_test_transients'][ $key ] )
			? $GLOBALS['alynt_drime_backups_dashboard_test_transients'][ $key ]
			: false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	/**
	 * Test transient setter.
	 *
	 * @param string $key Transient key.
	 * @param mixed  $value Value.
	 * @param int    $expiration Expiration.
	 * @return bool
	 */
	function set_transient( $key, $value, $expiration = 0 ) {
		unset( $expiration );
		$GLOBALS['alynt_drime_backups_dashboard_test_transients'][ $key ] = $value;

		return true;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	/**
	 * Test transient deleter.
	 *
	 * @param string $key Transient key.
	 * @return bool
	 */
	function delete_transient( $key ) {
		unset( $GLOBALS['alynt_drime_backups_dashboard_test_transients'][ $key ] );

		return true;
	}
}

/**
 * Fake repository for enrollment REST tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Pending site.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site;

	/**
	 * Stored enrollment data.
	 *
	 * @var array<string,mixed>
	 */
	public $stored = array();

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed>|null $site Site.
	 */
	public function __construct( $site = null ) {
		$this->site = $site;
	}

	/**
	 * Gets a pending site by public ID.
	 *
	 * @param string $public_id Public ID.
	 * @return array<string,mixed>|null
	 */
	public function get_pending_by_public_id( $public_id ) {
		if ( ! $this->site || $public_id !== $this->site['public_id'] || 'pending' !== $this->site['enrollment_status'] ) {
			return null;
		}

		return $this->site;
	}

	/**
	 * Stores enrollment state.
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $data Data.
	 * @return bool
	 */
	public function complete_enrollment_pending_first_poll( $site_id, array $data ) {
		$this->stored = array_merge(
			array(
				'site_id' => $site_id,
			),
			$data
		);

		return true;
	}
}

/**
 * Shared enrollment REST controller fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller_Test_Fixtures {
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

	/**
	 * Creates a pending site row.
	 *
	 * @param string $secret Plain pairing secret.
	 * @param string $expires_at Expiry.
	 * @return array<string,mixed>
	 */
	private function pending_site( $secret, $expires_at = '2099-01-01 00:15:00' ) {
		return array(
			'id'                  => 77,
			'public_id'           => '00000000-0000-4000-8000-000000000000',
			'expected_origin'     => 'https://client.example.com',
			'enrollment_status'   => 'pending',
			'pairing_secret_hash' => Alynt_Drime_Backups_Dashboard_Pairing_Tokens::hash_secret( $secret ),
			'pairing_expires_at'  => $expires_at,
		);
	}

	/**
	 * Creates an enrollment payload.
	 *
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function payload( array $overrides = array() ) {
		return array_merge(
			array(
				'protocol_version'      => 1,
				'enrollment_id'        => '00000000-0000-4000-8000-000000000000',
				'site_uuid'            => '11111111-1111-4111-8111-111111111111',
				'home_url'             => 'https://client.example.com',
				'status_endpoint'      => 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v1/status',
				'uploader_version'     => '0.5.3',
				'status_schema_version' => 1,
			),
			$overrides
		);
	}

	/**
	 * Creates a minimal REST request test double.
	 *
	 * @param string $authorization Authorization header.
	 * @return object
	 */
	private function request_with_authorization( $authorization ) {
		return new class( $authorization ) {
			/**
			 * Authorization header.
			 *
			 * @var string
			 */
			private $authorization;

			/**
			 * Constructor.
			 *
			 * @param string $authorization Authorization header.
			 */
			public function __construct( $authorization ) {
				$this->authorization = (string) $authorization;
			}

			/**
			 * Gets a header.
			 *
			 * @param string $name Header name.
			 * @return string
			 */
			public function get_header( $name ) {
				return 'authorization' === strtolower( (string) $name ) ? $this->authorization : '';
			}
		};
	}
}
